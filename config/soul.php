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
        'max_processing_rounds' => env('SOUL_MAX_PROCESSING_ROUNDS', 5),
        'convergence_threshold' => env('SOUL_CONVERGENCE_THRESHOLD', 0.1),
        'cleanup_frequency' => env('SOUL_CLEANUP_FREQUENCY', 3600), // 1 hour
        'archive_sessions' => env('SOUL_ARCHIVE_SESSIONS', true),
    ],
    
    'dashboard' => [
        'refresh_interval' => env('SOUL_DASHBOARD_REFRESH_INTERVAL', 5), // seconds
        'max_recent_activity' => env('SOUL_DASHBOARD_MAX_ACTIVITY', 50),
        'cache_duration' => env('SOUL_DASHBOARD_CACHE_DURATION', 300), // 5 minutes
        'enable_real_time' => env('SOUL_DASHBOARD_REAL_TIME', true),
        'visualization' => [
            'max_nodes' => env('SOUL_VIZ_MAX_NODES', 100),
            'animation_speed' => env('SOUL_VIZ_ANIMATION_SPEED', 1.0),
            'default_threshold' => env('SOUL_VIZ_DEFAULT_THRESHOLD', 0.3),
        ],
    ],
    
    'fol' => [
        'axiom_processing' => [
            'batch_size' => env('SOUL_FOL_BATCH_SIZE', 50),
            'max_complexity_level' => env('SOUL_FOL_MAX_COMPLEXITY', 3),
            'enable_validation' => env('SOUL_FOL_ENABLE_VALIDATION', true),
            'timeout_per_axiom' => env('SOUL_FOL_AXIOM_TIMEOUT', 30), // seconds
        ],
        
        'reasoning_agents' => [
            'enable_auto_generation' => env('SOUL_FOL_AUTO_AGENT_GEN', true),
            'agent_timeout' => env('SOUL_FOL_AGENT_TIMEOUT', 45), // seconds
            'max_parallel_agents' => env('SOUL_FOL_MAX_PARALLEL', 3),
            'retry_failed_agents' => env('SOUL_FOL_RETRY_AGENTS', true),
            'agent_priority_threshold' => env('SOUL_FOL_PRIORITY_THRESHOLD', 0.7),
        ],
        
        'defeasible_handling' => [
            'enable_exception_checking' => env('SOUL_FOL_EXCEPTION_CHECK', true),
            'default_confidence_reduction' => env('SOUL_FOL_DEFEASIBLE_REDUCTION', 0.1),
            'exception_memory_limit' => env('SOUL_FOL_EXCEPTION_LIMIT', 100),
            'learning_from_exceptions' => env('SOUL_FOL_LEARN_EXCEPTIONS', true),
        ],
        
        'frame_construction' => [
            'enable_frame_building' => env('SOUL_FOL_ENABLE_FRAMES', true),
            'min_complexity_for_frames' => env('SOUL_FOL_FRAME_MIN_COMPLEXITY', 2),
            'max_frame_elements' => env('SOUL_FOL_MAX_FRAME_ELEMENTS', 10),
            'frame_validation_strict' => env('SOUL_FOL_FRAME_VALIDATION', true),
        ],
        
        'psychological_reasoning' => [
            'enable_goal_reasoning' => env('SOUL_FOL_GOAL_REASONING', true),
            'enable_belief_reasoning' => env('SOUL_FOL_BELIEF_REASONING', true),
            'enable_emotion_reasoning' => env('SOUL_FOL_EMOTION_REASONING', true),
            'confidence_threshold' => env('SOUL_FOL_PSYCH_THRESHOLD', 0.6),
            'max_reasoning_depth' => env('SOUL_FOL_REASONING_DEPTH', 5),
        ],
        
        'performance' => [
            'enable_caching' => env('SOUL_FOL_ENABLE_CACHE', true),
            'cache_duration' => env('SOUL_FOL_CACHE_DURATION', 1800), // 30 minutes
            'enable_metrics' => env('SOUL_FOL_ENABLE_METRICS', true),
            'log_performance' => env('SOUL_FOL_LOG_PERFORMANCE', false),
            'optimization_level' => env('SOUL_FOL_OPTIMIZATION', 'balanced'), // minimal, balanced, aggressive
        ],
        
        'integration' => [
            'auto_register_agents' => env('SOUL_FOL_AUTO_REGISTER', true),
            'extend_yaml_loader' => env('SOUL_FOL_EXTEND_YAML', true),
            'neo4j_batch_operations' => env('SOUL_FOL_NEO4J_BATCH', true),
            'enable_kline_learning' => env('SOUL_FOL_KLINE_LEARNING', true),
        ]
    ],
];