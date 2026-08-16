<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Redención {{ $redemption->code }}</title>
    <style>
        @page { size: 48mm auto; margin: 2mm; }
        * { box-sizing: border-box; }
        body { width: 44mm; margin: 0 auto; color: #000; font: 10px/1.35 Arial, sans-serif; }
        h1 { margin: 0; font-size: 15px; text-align: center; }
        .center { text-align: center; }
        .rule { margin: 7px 0; border-top: 1px dashed #000; }
        .row { display: flex; justify-content: space-between; gap: 4px; margin: 3px 0; }
        .row strong:last-child { text-align: right; }
        .amount { margin: 8px 0; font-size: 15px; text-align: center; }
        .signature { margin-top: 24mm; border-top: 1px solid #000; padding-top: 2px; text-align: center; }
        .instructions { margin-top: 8px; font-size: 8px; text-align: center; }
        button { width: 100%; margin: 12px 0; padding: 8px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <h1>ORIGEN NATURAL</h1>
    <div class="center">COMPROBANTE DE REDENCIÓN</div>
    <div class="rule"></div>
    <div class="row"><span>Código</span><strong>{{ $redemption->code }}</strong></div>
    <div class="row"><span>Fecha</span><strong>{{ $redemption->requested_at->format('d/m/Y H:i') }}</strong></div>
    <div class="row"><span>Sucursal</span><strong>{{ $redemption->location_id }}</strong></div>
    <div class="row"><span>Folio SR</span><strong>{{ $redemption->sr_folio }}</strong></div>
    <div class="row"><span>Cliente</span><strong>{{ $redemption->customer->name }}</strong></div>
    <div class="row"><span>Cajero</span><strong>{{ $redemption->cashier->name }}</strong></div>
    <div class="rule"></div>
    <div class="row"><span>Puntos</span><strong>{{ number_format((float) $redemption->points, 2) }}</strong></div>
    <div class="row"><span>Total compra</span><strong>${{ number_format((float) $redemption->purchase_total_mxn, 2) }}</strong></div>
    <div class="amount"><strong>ORIGENPOINTS<br>${{ number_format((float) $redemption->value_mxn, 2) }} MXN</strong></div>
    <div class="signature">Firma del cliente<br>{{ $redemption->customer->name }}</div>
    <p class="instructions">Conservo este comprobante como autorización de la redención indicada. Este documento no es una factura.</p>
    <button type="button" onclick="window.print()">Imprimir</button>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
