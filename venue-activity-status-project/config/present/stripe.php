<?php

declare(strict_types=1);

return [
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_countryid_no' => env('STRIPE_COUNTRYID_NO'),
    'stripe_countryid_dk' => env('STRIPE_COUNTRYID_DK'),
    'stripe_staff_management_product_id_no' => env('STRIPE_STAFF_MANAGEMENT_PRODUCT_ID_NO'),
    'stripe_staff_management_product_id_dk' => env('STRIPE_STAFF_MANAGEMENT_PRODUCT_ID_DK'),
];
