<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'smsir' => [
        'api_key' => env('SMSIR_API_KEY'),
        'template_id' => env('SMSIR_TEMPLATE_ID'),
    ],
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
    ],
    'sehatsanji' => [
        'url' => env('SEHATSANJI_URL', 'https://sehatsanji.ir/API'),
        'token' => env('SEHATSANJI_TOKEN'),
        'timeout' => env('SEHATSANJI_TIMEOUT', 30),
    ],
    'finnotech' => [
        'address' => env('FINNOTECH_ADDRESS', 'https://sandboxapi.finnotech.ir'),
        'client_id' => env('FINNOTECH_CLIENT_ID'),
        'token' => env('FINNOTECH_TOKEN'),
    ],
    'shahkar' => [
        'timeout' => env('SHAHKAR_TIMEOUT', 20),
        'enforce_in_local' => env('SHAHKAR_ENFORCE_IN_LOCAL', false),
    ],
];
