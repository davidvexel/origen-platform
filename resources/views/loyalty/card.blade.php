<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Origen Rewards - {{ $customer->name }}</title>
    <style>
        :root { --green:#003f22; --terracotta:#D06223; --chili:#AF431D; --lettuce:#8B8635; --dawn:#E9C892; }
        * { box-sizing:border-box; }
        body { min-height:100vh; margin:0; padding:24px 16px; display:grid; place-items:center; background:linear-gradient(145deg,#002d19,var(--green)); color:#fff; font-family:Arial,sans-serif; }
        .shell { width:min(100%,430px); }
        .card { position:relative; overflow:hidden; padding:28px; border:1px solid rgba(233,200,146,.45); border-radius:28px; background:linear-gradient(145deg,var(--terracotta),var(--chili)); box-shadow:0 24px 70px rgba(0,0,0,.35); }
        .card:after { content:""; position:absolute; width:220px; height:220px; right:-100px; top:-110px; border-radius:50%; background:rgba(233,200,146,.18); }
        .brand { position:relative; z-index:1; display:flex; align-items:center; gap:14px; }
        .brand img { width:78px; height:50px; object-fit:contain; filter:brightness(0) invert(1); }
        h1,h2 { font-family:Baskerville,Georgia,serif; }
        h1 { margin:0; font-size:26px; } .tagline { margin:2px 0 0; opacity:.85; font-size:13px; }
        .balance { margin:42px 0 28px; } .balance span { display:block; font-size:12px; letter-spacing:.12em; text-transform:uppercase; }
        .balance strong { font:700 52px/1 Baskerville,Georgia,serif; } .balance small { font-size:16px; }
        .details { display:flex; justify-content:space-between; gap:14px; font-size:12px; }
        .details strong { display:block; margin-top:3px; font-size:15px; }
        .qr-panel { margin-top:18px; padding:24px; border-radius:24px; background:#fff; color:#123; text-align:center; }
        .qr { width:min(100%,280px); margin:auto; } .qr svg { display:block; width:100%; height:auto; }
        .member { margin:10px 0 0; font-weight:700; letter-spacing:.13em; }
        .notice { margin:16px 8px 0; color:rgba(255,255,255,.78); text-align:center; font-size:12px; line-height:1.45; }
        .admin { margin-bottom:14px; padding:10px 14px; border-radius:12px; background:var(--dawn); color:#3a240e; font-size:13px; }
        @media print { body { background:#fff; } .shell { width:90mm; } .admin,.notice { display:none; } }
    </style>
</head>
<body>
<main class="shell">
    @if ($adminPreview)<div class="admin">Vista administrativa. Puedes imprimirla o mostrar el QR al cliente.</div>@endif
    <section class="card">
        <div class="brand">
            <img src="{{ asset('images/origen-natural-logo.svg') }}" alt="Origen Natural">
            <div><h1>Origen Rewards</h1><p class="tagline">Tus compras te recompensan</p></div>
        </div>
        <div class="balance"><span>Saldo disponible</span><strong>{{ number_format((float) $customer->points_balance, 2) }}</strong> <small>puntos</small></div>
        <div class="details">
            <div>Cliente<strong>{{ $customer->name }}</strong></div>
            <div>Clave SR<strong>{{ $customer->external_id }}</strong></div>
        </div>
    </section>
    <section class="qr-panel">
        <div class="qr">{!! $qrSvg !!}</div>
        <p class="member">{{ $customer->external_id }}</p>
    </section>
    <p class="notice">Presenta este QR antes de pagar. El QR identifica tu cuenta, pero no autoriza por sí solo el uso de puntos.</p>
    <!-- TODO: Agregar botones Apple Wallet y Google Wallet cuando existan las credenciales de emisor. -->
</main>
</body>
</html>
