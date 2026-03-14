<?php
return [
    // Shared application defaults for ChikinTayo
    'default_password' => env('CHIKIN_TAYO_DEFAULT_PASSWORD', 'Chikintayo_123'),
    // Financial defaults
    'vat_percent' => env('CHIKIN_TAYO_VAT_PERCENT', 0.12),
    'pwd_discount_percent' => env('CHIKIN_TAYO_PWD_DISCOUNT_PERCENT', 0.20),
    'senior_discount_percent' => env('CHIKIN_TAYO_SENIOR_DISCOUNT_PERCENT', 0.20),
];
