<?php

return [
    'demo' => [
        'enabled' => (bool) env('CBT_DEMO_ACCOUNTS_ENABLED', false),
        'password' => env('CBT_DEMO_PASSWORD'),
        'accounts' => [
            'admin' => ['email' => env('CBT_DEMO_ADMIN_EMAIL', 'admin@eduka.test'), 'name' => 'Admin Eduka'],
            'teacher' => ['email' => env('CBT_DEMO_TEACHER_EMAIL', 'pengajar@eduka.test'), 'name' => 'Pengajar Eduka'],
            'student' => ['email' => env('CBT_DEMO_STUDENT_EMAIL', 'siswa@eduka.test'), 'name' => 'Siswa Eduka'],
        ],
    ],
    'ai' => [
        'enabled' => (bool) env('AI_ENABLED', false),
        'provider' => env('AI_PROVIDER', 'unavailable'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'openai/gpt-oss-20b'),
        'base_url' => rtrim((string) env('AI_BASE_URL', 'https://api.groq.com/openai/v1'), '/'),
        'timeout' => (int) env('AI_TIMEOUT', 60),
        'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 10),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 8192),
        'max_questions' => (int) env('AI_MAX_QUESTIONS', 20),
        'max_material_chars' => (int) env('AI_MAX_MATERIAL_CHARS', 10000),
        'rate_limit' => (int) env('AI_RATE_LIMIT', 10),
        'structured_outputs' => (bool) env('AI_STRUCTURED_OUTPUTS', true),
        'strict_models' => [
            'openai/gpt-oss-20b',
            'openai/gpt-oss-120b',
            'qwen/qwen3.8-27b',
            'meta-models/muse-spark-open',
        ],
    ],
];
