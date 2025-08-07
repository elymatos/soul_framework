<?php

return [
    'graph' => [
        'spreading_activation' => [
            'max_depth' => env('SOUL_MAX_ACTIVATION_DEPTH', 3),
            'threshold' => env('SOUL_ACTIVATION_THRESHOLD', 0.1),
            'decay_factor' => env('SOUL_ACTIVATION_DECAY', 0.8),
        ],
        
        'klines' => [
            'min_usage_for_strengthening' => env('SOUL_KLINE_MIN_USAGE', 3),
            'strength_increment' => env('SOUL_KLINE_STRENGTH_INCREMENT', 0.1),
        ],
    ],
    
    'agents' => [
        'execution_timeout' => env('SOUL_AGENT_TIMEOUT', 30),
        'max_parallel_agents' => env('SOUL_MAX_PARALLEL_AGENTS', 5),
        'retry_attempts' => env('SOUL_AGENT_RETRY_ATTEMPTS', 2),
    ],
    
    'yaml' => [
        'base_directory' => storage_path('soul/yaml'),
        'auto_load_on_boot' => env('SOUL_AUTO_LOAD_YAML', false),
        'validation_strict' => env('SOUL_YAML_VALIDATION_STRICT', true),
    ],
    
    'processing' => [
        'session_timeout' => env('SOUL_SESSION_TIMEOUT', 300), // 5 minutes
        'max_concurrent_sessions' => env('SOUL_MAX_CONCURRENT_SESSIONS', 10),
        'cleanup_frequency' => env('SOUL_CLEANUP_FREQUENCY', 3600), // 1 hour
    ],
];