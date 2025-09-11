<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Background Theories Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the FOL-to-executable Background Theories system
    | implementing Gordon & Hobbs' formal theory of commonsense psychology.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    */
    
    'system' => [
        'enable_execution_trace' => env('BT_ENABLE_TRACE', true),
        'max_trace_entries' => env('BT_MAX_TRACE_ENTRIES', 1000),
        'enable_axiom_caching' => env('BT_ENABLE_CACHING', true),
        'cache_ttl' => env('BT_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Reasoning Engine Settings
    |--------------------------------------------------------------------------
    */
    
    'reasoning' => [
        'max_execution_depth' => env('BT_MAX_EXECUTION_DEPTH', 10),
        'execution_timeout' => env('BT_EXECUTION_TIMEOUT', 30), // seconds
        'enable_parallel_execution' => env('BT_PARALLEL_EXECUTION', false),
        'max_concurrent_axioms' => env('BT_MAX_CONCURRENT_AXIOMS', 5),
        'convergence_threshold' => env('BT_CONVERGENCE_THRESHOLD', 0.001),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity and Predicate Settings
    |--------------------------------------------------------------------------
    */
    
    'entities' => [
        'auto_realize' => env('BT_AUTO_REALIZE_ENTITIES', false),
        'validate_on_creation' => env('BT_VALIDATE_ENTITIES', true),
        'enable_entity_caching' => env('BT_CACHE_ENTITIES', true),
        'default_attributes' => [],
    ],

    'predicates' => [
        'auto_assert' => env('BT_AUTO_ASSERT_PREDICATES', false),
        'validate_on_creation' => env('BT_VALIDATE_PREDICATES', true),
        'enable_defeasible_reasoning' => env('BT_DEFEASIBLE_REASONING', true),
        'default_confidence' => env('BT_DEFAULT_CONFIDENCE', 1.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Axiom Execution Settings
    |--------------------------------------------------------------------------
    */
    
    'axioms' => [
        'auto_register_executors' => env('BT_AUTO_REGISTER_AXIOMS', true),
        'execution_retry_attempts' => env('BT_AXIOM_RETRY_ATTEMPTS', 2),
        'log_axiom_executions' => env('BT_LOG_AXIOM_EXECUTIONS', true),
        'enable_axiom_dependencies' => env('BT_AXIOM_DEPENDENCIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Axiom Executors
    |--------------------------------------------------------------------------
    |
    | Pre-registered axiom executors that will be automatically loaded
    | when the service boots. Format: 'axiom_id' => 'ClassName'
    |
    */
    
    'axiom_executors' => [
        '5.1' => \App\Domain\BackgroundTheories\AxiomExecutors\Axiom5_1Executor::class,
        '6.13' => \App\Domain\BackgroundTheories\AxiomExecutors\Axiom6_13Executor::class,
        // Add more axiom executors as they are created
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON Processing Settings
    |--------------------------------------------------------------------------
    */
    
    'json' => [
        'auto_load_yaml' => env('BT_AUTO_LOAD_YAML', false),
        'yaml_directory' => env('BT_YAML_DIRECTORY', storage_path('background_theories/yaml')),
        'validation_strict' => env('BT_VALIDATION_STRICT', true),
        'generate_missing_classes' => env('BT_GENERATE_MISSING_CLASSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    
    'database' => [
        'connection' => env('BT_DB_CONNECTION', 'mysql'),
        'enable_foreign_keys' => env('BT_DB_FOREIGN_KEYS', true),
        'enable_indexes' => env('BT_DB_INDEXES', true),
        'batch_size' => env('BT_DB_BATCH_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Debugging
    |--------------------------------------------------------------------------
    */
    
    'monitoring' => [
        'enable_performance_tracking' => env('BT_PERFORMANCE_TRACKING', true),
        'log_slow_queries' => env('BT_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('BT_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'enable_memory_monitoring' => env('BT_MEMORY_MONITORING', true),
    ],

    'debugging' => [
        'log_level' => env('BT_LOG_LEVEL', 'info'),
        'enable_debug_trace' => env('BT_DEBUG_TRACE', false),
        'max_debug_entries' => env('BT_MAX_DEBUG_ENTRIES', 500),
        'include_stack_traces' => env('BT_INCLUDE_STACK_TRACES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cross-Theory Integration
    |--------------------------------------------------------------------------
    */
    
    'integration' => [
        'soul_framework' => [
            'enabled' => env('BT_SOUL_INTEGRATION', true),
            'sync_entities' => env('BT_SOUL_SYNC_ENTITIES', false),
            'sync_predicates' => env('BT_SOUL_SYNC_PREDICATES', false),
        ],
        
        'psychology_theories' => [
            'enabled' => env('BT_PSYCHOLOGY_INTEGRATION', false),
            'namespace' => 'App\\Domain\\PsychologyTheories',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    
    'security' => [
        'enable_axiom_sandboxing' => env('BT_AXIOM_SANDBOXING', true),
        'max_memory_usage' => env('BT_MAX_MEMORY_USAGE', '256M'),
        'allowed_functions' => [
            // List of allowed PHP functions during axiom execution
            'array_*',
            'count',
            'in_array',
            'json_*',
            'serialize',
            'unserialize',
        ],
        'forbidden_functions' => [
            'exec',
            'shell_exec',
            'system',
            'passthru',
            'file_get_contents',
            'file_put_contents',
            'fopen',
            'fwrite',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chapter-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to individual chapters of Gordon & Hobbs' theory
    |
    */
    
    'chapters' => [
        'chapter_5' => [
            'name' => 'Eventualities and Their Structure',
            'enable_eventuality_generation' => true,
            'auto_create_rexist_predicates' => true,
            'eventuality_validation_strict' => true,
        ],
        
        'chapter_6' => [
            'name' => 'Traditional Set Theory',
            'enable_set_operations' => true,
            'auto_create_unions' => false,
            'validate_set_membership' => true,
        ],
        
        'chapter_9' => [
            'name' => 'Functions and Sequences',
            'enable_function_creation' => false,
            'validate_function_domains' => true,
            'sequence_max_length' => 10000,
        ],
        
        // Add more chapters as implemented
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    
    'api' => [
        'enable_rest_api' => env('BT_ENABLE_API', true),
        'api_prefix' => env('BT_API_PREFIX', 'background-theories'),
        'rate_limiting' => env('BT_API_RATE_LIMITING', true),
        'max_requests_per_minute' => env('BT_API_MAX_REQUESTS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export/Import Settings
    |--------------------------------------------------------------------------
    */
    
    'export' => [
        'default_format' => env('BT_EXPORT_FORMAT', 'json'),
        'include_metadata' => env('BT_EXPORT_METADATA', true),
        'compress_exports' => env('BT_COMPRESS_EXPORTS', false),
        'max_export_size' => env('BT_MAX_EXPORT_SIZE', '100M'),
    ],

    'import' => [
        'validate_imports' => env('BT_VALIDATE_IMPORTS', true),
        'allow_overwrite' => env('BT_ALLOW_OVERWRITE', false),
        'max_import_size' => env('BT_MAX_IMPORT_SIZE', '100M'),
        'backup_before_import' => env('BT_BACKUP_BEFORE_IMPORT', true),
    ],
];