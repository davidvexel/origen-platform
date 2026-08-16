<?php

return [
    'location_id' => env('LOYALTY_LOCATION_ID', 'origen-playa'),
    'redemption_expiration_hours' => (int) env('LOYALTY_REDEMPTION_EXPIRATION_HOURS', 8),
    'points_payment_method' => env('LOYALTY_POINTS_PAYMENT_METHOD', 'ORIGENPOINTS'),
];
