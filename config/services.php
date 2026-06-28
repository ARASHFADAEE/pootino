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
    'admin' => [
        'phones' => env('ADMIN_PHONES', ''),
    ],
    'ai_kar' => [
        'url' => env('AI_KAR_URL', 'https://api.ai-kar.com/v1/chat/completions'),
        'api_key' => env('AI_KAR_API_KEY'),
        'model' => env('AI_KAR_MODEL', 'google/gemini-2.5-flash'),
        'fallback_models' => array_filter(array_map('trim', explode(',', env('AI_KAR_FALLBACK_MODELS', 'gemini-2.5-flash,google/gemini-2.5-pro,gemini-2.5-pro')))),
        'timeout' => env('AI_KAR_TIMEOUT', 30),
        'auto_approve_in_local' => env('AI_KAR_AUTO_APPROVE_IN_LOCAL', false),
    ],
];
