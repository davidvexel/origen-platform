# Origen Platform

Aplicación web Laravel para recibir las ventas de Origen SR Connector. Esta primera versión implementa únicamente el módulo `Sales`: autenticación de instalaciones, validación, idempotencia y persistencia de ventas, productos y pagos.

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

## Verificación

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list --path=api
```

Las pruebas cubren autenticación, persistencia completa, cliente opcional, modificadores, pagos múltiples, duplicados, conflictos y aislamiento por ubicación.

## Próximos módulos

1. Integrar `HttpLoyaltyApiClient` en el conector .NET.
2. Añadir eventos posteriores al commit para Loyalty.
3. Construir cuentas, movimientos y reglas de recompensas.
4. Incorporar el módulo aislado de facturación y su proveedor/PAC.
