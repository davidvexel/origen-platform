# Origen Platform

Aplicación web Laravel para recibir las ventas de Origen SR Connector y operar el inicio del programa Loyalty. Incluye API autenticada, persistencia de ventas y un panel interno construido con Filament 5.

## Arquitectura inicial

```text
Origen SR Connector
        │
        │ POST /api/v1/sales
        │ Authorization: Bearer <token>
        ▼
Origen Platform
        ├── Integrations: instalaciones y tokens
        └── Sales
            ├── sales
            ├── sale_items
            └── sale_payments
```

`GET /api/v1/connector` permite validar de forma autenticada el token y la sucursal sin crear una venta.

La sucursal no se acepta desde el payload. Se obtiene del token autenticado para impedir que una instalación atribuya ventas a otra ubicación.

La clave idempotente es:

```text
source + location_id + ticket
```

- Primera recepción: `201 Created`.
- Repetición con el mismo payload: `200 OK` y `duplicate: true`.
- Misma identidad con datos distintos: `409 Conflict`.

## Requisitos

- PHP 8.3 o posterior.
- Composer 2.
- SQLite para desarrollo; MySQL o PostgreSQL para producción.

El proyecto fue creado con Laravel 13.

## Panel interno

El panel está disponible en:

```text
/admin
```

Incluye:

- dashboard con ventas del día, total y clientes pendientes;
- listado y detalle de ventas, productos y pagos en modo de sólo lectura;
- registro y consulta de clientes Loyalty;
- bandeja para que cajeros capturen manualmente clientes pendientes en SoftRestaurant;
- captura de la Clave de SR y vinculación de ventas por `external_id`;
- pantalla de redención con validación de saldo y movimientos auditables;
- reglas de recompensas editables por administradores;
- acumulación idempotente por venta, vencimiento por lotes y redención FIFO;
- administración de usuarios internos con roles `admin` y `cashier`.

El panel nunca crea ni modifica clientes directamente en SoftRestaurant. La bandeja pendiente es exclusivamente un flujo manual de copiar, capturar en SR y confirmar la Clave obtenida.

Las reglas iniciales instaladas son:

```text
Cashback: 5%
Valor: 1 punto = $1 MXN
Propina: no genera puntos
Redención mínima: 20 puntos
Vencimiento: 6 meses
Máximo redimible: 100% de la compra
Ventas con descuento: sí generan puntos
```

El cashback se calcula sobre `sale.total`. Este importe ya refleja los descuentos de la venta; la propina sólo se suma a la base cuando la opción correspondiente está activa. Con las reglas iniciales, una venta elegible de `$330.00` genera `16.50` puntos.

Los cambios hechos en **Reglas de recompensas** aplican a ventas y redenciones nuevas. Cada movimiento conserva una fotografía de las reglas utilizadas y los movimientos anteriores no se recalculan automáticamente.

Los clientes tienen un tipo (`Persona`, `Canal / agregador` o `Empresa`) y una bandera independiente **Participa en recompensas**. Cuentas compartidas como Rappi deben configurarse como `Canal / agregador` y con recompensas desactivadas.

Los puntos acumulados se guardan en lotes con fecha de vencimiento. Las redenciones consumen primero los lotes que vencen antes. El comando `loyalty:expire-points` registra los vencimientos como movimientos auditables; también se ejecuta al seleccionar un cliente para redimir.

### Crear el primer administrador

```bash
php artisan panel-user:create "Administrador" admin@origennatural.mx --role=admin
```

El comando solicita la contraseña de forma oculta. Los administradores pueden crear cajeros desde **Equipo** dentro del panel.

La pestaña Commands de Laravel Cloud no acepta prompts interactivos. En Cloud debe generarse una contraseña temporal:

```bash
php artisan panel-user:create "Administrador" admin@origennatural.mx --role=admin --generate-password
```

Copie la contraseña mostrada una sola vez, inicie sesión y cámbiela desde **Equipo**.

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan test
```

En Windows PowerShell, los equivalentes para crear el entorno y SQLite son:

```powershell
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File -Force
php artisan migrate
php artisan test
```

No suba `.env`, tokens ni credenciales a Git.

## Crear un token para un conector

```bash
php artisan api-client:create "SoftRestaurant Playa" origen-playa
```

El comando muestra un token con prefijo `orp_` una sola vez. Guárdelo en un gestor de secretos. La base de datos conserva solamente su hash SHA-256.

Ejemplo conceptual de salida:

```text
API client 1 created for origen-playa.
Copy this token now. It will not be shown again:
orp_TOKEN_GENERADO
```

## Ejecutar durante desarrollo

```bash
php artisan serve
```

La API estará disponible normalmente en:

```text
http://127.0.0.1:8000/api/v1/sales
```

En producción debe publicarse exclusivamente mediante HTTPS.

## Enviar una venta

```bash
curl -X POST http://127.0.0.1:8000/api/v1/sales \
  -H "Authorization: Bearer orp_TOKEN_GENERADO" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "source": "softrestaurant",
    "folio": 11,
    "ticket": 1737,
    "opened_at": "2026-08-15T15:21:42",
    "closed_at": "2026-08-15T15:22:55",
    "station": "SERVIDOR",
    "customer": {
      "external_id": "DASDASDSAD2323",
      "name": "PRUEBA 2"
    },
    "totals": {
      "subtotal": 340.5172,
      "tax": 54.4828,
      "total": 395.0000,
      "tip": 25.0000,
      "total_with_tip": 395.0000
    },
    "items": [
      {
        "product_id": "04002",
        "name": "DETOX VERDE",
        "quantity": 1,
        "unit_price": 160.00,
        "discount": 0,
        "modifier": false,
        "compound_id": "_XYLGXPK6R",
        "compound_main": true
      }
    ],
    "payments": [
      {
        "method": "EF",
        "amount": 395.00,
        "tip": 25.00,
        "reference": null
      }
    ]
  }'
```

Respuesta inicial:

```json
{
  "sale_id": 1,
  "ticket": 1737,
  "duplicate": false
}
```

## Tablas

### `api_clients`

Representa una instalación autorizada. Contiene nombre, `location_id`, prefijo visible, hash del token, estado y último uso.

### `sales`

Guarda encabezado, cliente opcional, totales, payload validado original y hash de idempotencia.

### `sale_items`

Conserva todos los renglones en orden, incluidos productos principales y modificadores.

### `sale_payments`

Conserva múltiples formas de pago por venta.

## Seguridad

- Los tokens completos nunca se almacenan.
- Un token desactivado no puede autenticar.
- Todas las escrituras de una venta ocurren en una transacción.
- La sucursal proviene del token, no del cliente HTTP.
- Los errores de validación y autenticación siempre responden JSON.
- Producción requiere HTTPS, `APP_DEBUG=false` y credenciales fuera de Git.

## Preservar una venta manual como prueba

Si una venta creada mediante cURL ocupa por accidente una identidad real, puede conservarse como dato de prueba y liberar la identidad sin borrarla:

```bash
php artisan sales:mark-test 1 --dry-run
php artisan sales:mark-test 1 --force
```

Primero use `--dry-run` para revisar ID, origen, sucursal, ticket, total y conteos sin modificar nada. Después de confirmar el objetivo, `--force` marca `is_test = true` y mueve el registro a un origen de pruebas; sus productos y pagos se conservan.

## Verificación

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list --path=api
php artisan route:list --path=admin
```

Las pruebas cubren autenticación, persistencia completa, cliente opcional, modificadores, pagos múltiples, duplicados, conflictos, aislamiento por ubicación, acceso al panel y redenciones atómicas.

## Flujo de redención en caja

1. Antes de cerrar la cuenta, el cajero abre **Redimir puntos** en el panel.
2. Busca al cliente e ingresa el folio visible de la cuenta abierta, el total y los puntos.
3. El sistema reserva los puntos y genera un comprobante de 48 mm.
4. El cajero imprime el comprobante y solicita la firma del cliente.
5. En SoftRestaurant registra exactamente el importe indicado usando el método `ORIGENPOINTS` y después cierra la cuenta.
6. Cuando el conector envía la venta, la plataforma concilia sucursal, folio, cliente e importe y completa la redención.

Una reserva equivocada puede cancelarse antes del cierre y los puntos se devuelven. Las reservas que no se concilian vencen conforme a `LOYALTY_REDEMPTION_EXPIRATION_HOURS` y también devuelven el saldo. La porción pagada con `ORIGENPOINTS` no genera cashback nuevo.

Para imprimir, configure en Windows la impresora térmica con papel de **48 mm** y márgenes mínimos. El navegador abre el diálogo de impresión; seleccione esa impresora y desactive encabezados y pies de página. El comprobante incluye folio, cliente, cajero, importe y espacio para la firma.

## Tarjeta Origen Rewards y QR

Todo cliente nuevo recibe automáticamente una Clave de SoftRestaurant con formato `ON-XXXXXX`. En **Pendientes en SR**, el cajero copia esa clave al crear manualmente al cliente en SoftRestaurant y después marca la sincronización como terminada.

Al abrir un cliente en Filament, el botón **Ver tarjeta y QR** emite su credencial si todavía no existe. El QR contiene exclusivamente la Clave SR `ON-XXXXXX`; nunca incluye teléfono, correo o ID interno. Un lector QR USB puede escribir esa clave directamente en el campo activo de SoftRestaurant porque funciona como teclado.

El cajero usa **Escanear tarjeta** para leer el QR con la cámara y abrir al cliente directamente en la pantalla de redención. Chrome o Edge recientes pueden usar la cámara; también existe captura manual como respaldo. El QR identifica al cliente pero no autoriza por sí solo una redención. El enlace privado de la tarjeta web utiliza adicionalmente un token aleatorio cifrado y revocable.

TODO para wallets:

- Apple Wallet: configurar Pass Type ID, certificados, firma de `.pkpass` y servicio de actualizaciones.
- Google Wallet: configurar Issuer ID, Service Account, Loyalty Class/Object y actualizaciones de saldo.

## Despliegue en Laravel Cloud

Después de desplegar el código, ejecutar una vez en el ambiente de producción:

```bash
php artisan migrate --force
php artisan panel-user:create "Administrador" admin@origennatural.mx --role=admin --generate-password
php artisan filament:optimize
```

El segundo comando sólo es necesario si todavía no existe un administrador. No ejecutar `migrate:fresh` en producción porque elimina todos los datos.

Para que los vencimientos se procesen aunque ningún cajero abra la pantalla de redención, la instancia de Laravel Cloud debe tener habilitado el scheduler. La tarea está programada diariamente a las `02:15`:

```bash
php artisan loyalty:expire-points
```

## Próximos módulos

1. Construir el registro/autenticación de clientes en la app pública con verificación de contacto y consentimiento.
2. Añadir ajustes de puntos protegidos para administradores.
3. Incorporar el módulo aislado de facturación y su proveedor/PAC.
