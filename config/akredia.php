<?php

return [
    // MFA (aplikasi autentikator). Rancangan mewajibkan MFA untuk Administrator, Manajer, dan Pimpinan.
    'mfa_required' => env('AKREDIA_MFA_REQUIRED', false),
    'mfa_required_roles' => ['admin', 'manajer', 'pimpinan'],

    'upload_max_kb' => (int) env('AKREDIA_UPLOAD_MAX_KB', 25600),
    'word_limit' => (int) env('AKREDIA_WORD_LIMIT', 1500),
    'evidence_expiry_warning_days' => 60,
    'task_escalation_days' => 7,

    'admin' => [
        'email' => env('AKREDIA_ADMIN_EMAIL'),
        'password' => env('AKREDIA_ADMIN_PASSWORD'),
    ],

    'demo' => [
        'enabled' => env('AKREDIA_DEMO', false),
        'password' => env('AKREDIA_DEMO_PASSWORD', 'Demo#2026akredia'),
        // Tombol "Masuk sebagai tamu" muncul hanya bila akun tamu demo UNJ ada (php artisan akredia:demo unj).
        'guest' => env('AKREDIA_DEMO_GUEST', true),
    ],

    // Asisten AI (opsional). Kosongkan kunci untuk mematikan; pemeriksaan lokal tetap berjalan.
    'ai' => [
        'key' => env('AKREDIA_AI_KEY'),
        'model' => env('AKREDIA_AI_MODEL', 'claude-opus-5-5'),
    ],

    'weekly_report_day' => env('AKREDIA_WEEKLY_REPORT_DAY', 'monday'),
];
