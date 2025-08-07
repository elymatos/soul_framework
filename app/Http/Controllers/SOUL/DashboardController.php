<?php

namespace App\Http\Controllers\SOUL;

use App\Http\Controllers\Controller;
use App\Soul\Services\MindService;
use App\Soul\Contracts\GraphServiceInterface;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

#[Middleware(name: 'web')]
class DashboardController extends Controller
{
    protected MindService $mindService;
    protected GraphServiceInterface $graphService;

    public function __construct(MindService $mindService, GraphServiceInterface $graphService)
    {
        $this->mindService = $mindService;
        $this->graphService = $graphService;
    }

    /**
     * Main dashboard view
     */
    #[Get(path: '/soul/dashboard')]
    public function index()
    {
        $lang = AppService::getCurrentLanguageCode();
        app()->setLocale($lang);
        
        return view('SOUL.Dashboard.main');
    }

    /**
     * Get comprehensive dashboard data
     */
    #[Get(path: '/soul/dashboard/data')]
    public function getData(): JsonResponse
    {
        try {
            $systemStats = $this->getSystemStatistics();
            $activeSessions = $this->getActiveSessionsData();
            $klineStats = $this->getKlineStatistics();
            $agentActivity = $this->getRecentAgentActivity();

            return response()->json([
                'systemStats' => $systemStats,
                'activeSessions' => $activeSessions,
                'klineStats' => $klineStats,
                'recentAgentActivity' => $agentActivity,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to get dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Real-time status updates for HTMX polling
     */
    #[Get(path: '/soul/dashboard/status')]
    public function getStatus()
    {
        try {
            // Get lightweight status updates
            $systemStats = $this->getSystemStatistics();
            $activeAgents = $this->getCurrentActiveAgents();
            $activeSessions = $this->getActiveSessionsData();
            $recentActivity = Cache::get('soul.dashboard.recent_activity', []);
            $klineStats = $this->getKlineStatistics();
            $topKlines = $klineStats['topPatterns'] ?? [];
            
            // Get current pipeline stage if any session is active
            $pipelineStage = $this->getCurrentPipelineStage();
            
            $data = [
                'systemStats' => $systemStats,
                'activeAgents' => $activeAgents,
                'activeSessions' => $activeSessions,
                'recentActivity' => array_slice($recentActivity, 0, 10),
                'klineStats' => $klineStats,
                'topKlines' => $topKlines,
                'pipelineStage' => $pipelineStage,
                'connectionStatus' => 'connected',
                'lastUpdate' => now()->toISOString()
            ];
            
            // Return as view fragment for HTMX
            return view('SOUL.Dashboard.status-fragment', $data);
            
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to get status', [
                'error' => $e->getMessage()
            ]);

            // Return error fragment
            return view('SOUL.Dashboard.status-fragment', [
                'systemStats' => [
                    'activeSessions' => 0,
                    'agentServices' => 0,
                    'totalConcepts' => 0,
                    'totalRelationships' => 0,
                    'kLinesCount' => 0,
                    'avgProcessingTime' => 0
                ],
                'activeAgents' => [],
                'activeSessions' => [],
                'recentActivity' => [],
                'connectionStatus' => 'error',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get processing status for a specific session
     */
    #[Get(path: '/soul/dashboard/session/{sessionId}')]
    public function getSessionStatus(string $sessionId): JsonResponse
    {
        try {
            $sessionData = $this->mindService->getSessionStatus($sessionId);
            
            if (!$sessionData) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            return response()->json([
                'session' => $sessionData,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to get session status', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get session status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start a test cognitive processing session
     */
    #[Post(path: '/soul/dashboard/test-session')]
    public function startTestSession(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'text' => 'required|string|max:1000',
                'concepts' => 'array|max:10',
                'context' => 'array|max:5'
            ]);

            // Start a new processing session
            $sessionId = $this->mindService->startProcessingSession($input);
            
            // Process the input asynchronously in the background
            $this->processSessionAsync($sessionId, $input);

            // Cache the activity
            $this->cacheActivity([
                'type' => 'session_started',
                'sessionId' => $sessionId,
                'input' => $input['text'] ?? 'Test session',
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'sessionId' => $sessionId,
                'message' => 'Test session started successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to start test session', [
                'input' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to start test session',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate a processing session
     */
    #[Post(path: '/soul/dashboard/session/{sessionId}/terminate')]
    public function terminateSession(string $sessionId): JsonResponse
    {
        try {
            $result = $this->mindService->endProcessingSession($sessionId);

            $this->cacheActivity([
                'type' => 'session_terminated',
                'sessionId' => $sessionId,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully',
                'sessionData' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to terminate session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to terminate session',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get spreading activation data for visualization
     */
    #[Get(path: '/soul/dashboard/activation/{concept}')]
    public function getActivationData(string $concept, Request $request): JsonResponse
    {
        try {
            $options = [
                'max_depth' => $request->input('depth', 3),
                'activation_threshold' => $request->input('threshold', 0.3),
                'include_procedural_agents' => true
            ];

            $activationResult = $this->graphService->runSpreadingActivation([$concept], $options);
            
            // Transform for visualization
            $visualizationData = $this->transformActivationForVisualization($activationResult);

            return response()->json([
                'concept' => $concept,
                'activationData' => $visualizationData,
                'metadata' => [
                    'totalNodes' => $activationResult['total_nodes'] ?? 0,
                    'maxDepth' => $options['max_depth'],
                    'threshold' => $options['activation_threshold']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to get activation data', [
                'concept' => $concept,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get activation data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get K-line learning statistics
     */
    #[Get(path: '/soul/dashboard/klines')]
    public function getKlineStats(Request $request): JsonResponse
    {
        try {
            $timeRange = $request->input('range', '24h');
            $klineStats = $this->getKlineStatistics($timeRange);

            return response()->json($klineStats);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to get K-line stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get K-line statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data
     */
    #[Get(path: '/soul/dashboard/export')]
    public function exportData(Request $request): JsonResponse
    {
        try {
            $format = $request->input('format', 'json');
            $systemStats = $this->mindService->getSystemStatistics();
            $graphStats = $this->graphService->getGraphStatistics();

            $exportData = [
                'exported_at' => now()->toISOString(),
                'system_statistics' => $systemStats,
                'graph_statistics' => $graphStats,
                'active_sessions' => $this->getActiveSessionsData(),
                'recent_activity' => Cache::get('soul.dashboard.recent_activity', [])
            ];

            if ($format === 'csv') {
                // Convert to CSV format if needed
                return $this->exportAsCsv($exportData);
            }

            return response()->json($exportData);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Failed to export data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to export dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ===========================================
    // PROTECTED HELPER METHODS
    // ===========================================

    /**
     * Get comprehensive system statistics
     */
    protected function getSystemStatistics(): array
    {
        $systemStats = $this->mindService->getSystemStatistics();
        $graphStats = $this->graphService->getGraphStatistics();

        return [
            'activeSessions' => $systemStats['active_sessions'] ?? 0,
            'agentServices' => $systemStats['registered_agent_services'] ?? 0,
            'totalConcepts' => $graphStats['total_nodes'] ?? 0,
            'totalRelationships' => $graphStats['total_relationships'] ?? 0,
            'kLinesCount' => $graphStats['klines_count'] ?? 0,
            'avgProcessingTime' => $this->getAverageProcessingTime(),
            'lastUpdated' => now()->toISOString()
        ];
    }

    /**
     * Get active sessions data for dashboard display
     */
    protected function getActiveSessionsData(): array
    {
        // This would typically query session storage or database
        // For now, return mock data structure
        return [];
    }

    /**
     * Get K-line learning statistics
     */
    protected function getKlineStatistics(string $timeRange = '24h'): array
    {
        try {
            // Calculate time range
            $startTime = $this->parseTimeRange($timeRange);
            
            // This would query K-line data from Neo4j
            // For now, return mock data structure
            return [
                'newPatterns' => 0,
                'strengthenedPatterns' => 0,
                'totalUsage' => 0,
                'topPatterns' => [],
                'chartData' => [
                    'labels' => [],
                    'datasets' => []
                ],
                'timeRange' => $timeRange
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get K-line statistics', ['error' => $e->getMessage()]);
            return [
                'newPatterns' => 0,
                'strengthenedPatterns' => 0,
                'totalUsage' => 0,
                'topPatterns' => []
            ];
        }
    }

    /**
     * Get recent agent activity
     */
    protected function getRecentAgentActivity(): array
    {
        return Cache::get('soul.dashboard.recent_activity', []);
    }

    /**
     * Get currently active agents
     */
    protected function getCurrentActiveAgents(): array
    {
        // In a real implementation, this would track active agent executions
        // For now, return mock data or empty array
        return Cache::get('soul.dashboard.active_agents', []);
    }

    /**
     * Get current pipeline stage
     */
    protected function getCurrentPipelineStage(): ?array
    {
        // Check if any session is currently processing
        $activeSessions = $this->mindService->getActiveSessionsCount();
        
        if ($activeSessions > 0) {
            // In a real implementation, this would track the current processing stage
            // For now, return a mock stage or null
            return Cache::get('soul.dashboard.current_stage');
        }
        
        return null;
    }

    /**
     * Get average processing time from recent sessions
     */
    protected function getAverageProcessingTime(): int
    {
        // This would calculate from stored session data
        // For now, return a reasonable default
        return rand(500, 2000); // 500-2000ms
    }

    /**
     * Process session asynchronously
     */
    protected function processSessionAsync(string $sessionId, array $input): void
    {
        // In a real implementation, this would be queued
        try {
            $result = $this->mindService->processInput($input, $sessionId);
            
            $this->cacheActivity([
                'type' => 'session_completed',
                'sessionId' => $sessionId,
                'processingTime' => $result['processing_time_ms'] ?? 0,
                'success' => true,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('SOUL Dashboard: Session processing failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            $this->cacheActivity([
                'type' => 'session_failed',
                'sessionId' => $sessionId,
                'error' => $e->getMessage(),
                'success' => false,
                'timestamp' => now()
            ]);
        }
    }

    /**
     * Transform activation results for visualization
     */
    protected function transformActivationForVisualization(array $activationResult): array
    {
        $nodes = [];
        $edges = [];
        
        foreach ($activationResult['activated_nodes'] ?? [] as $node) {
            $nodes[] = [
                'id' => $node['name'] ?? $node['id'],
                'label' => $node['name'] ?? $node['id'],
                'activation' => $node['activation_strength'] ?? 0.5,
                'type' => $node['type'] ?? 'concept',
                'x' => rand(0, 800),
                'y' => rand(0, 600)
            ];
        }

        // Generate edges from relationships
        // This would be extracted from the activation result
        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'totalNodes' => count($nodes),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Cache dashboard activity
     */
    protected function cacheActivity(array $activity): void
    {
        $activities = Cache::get('soul.dashboard.recent_activity', []);
        array_unshift($activities, $activity);
        
        // Keep only last 50 activities
        $activities = array_slice($activities, 0, 50);
        
        Cache::put('soul.dashboard.recent_activity', $activities, now()->addHours(24));
    }

    /**
     * Parse time range string to Carbon instance
     */
    protected function parseTimeRange(string $range): Carbon
    {
        switch ($range) {
            case '1h':
                return now()->subHour();
            case '24h':
                return now()->subDay();
            case '7d':
                return now()->subWeek();
            case '30d':
                return now()->subMonth();
            default:
                return now()->subDay();
        }
    }

    /**
     * Export data as CSV
     */
    protected function exportAsCsv(array $data): \Illuminate\Http\Response
    {
        $filename = 'soul_dashboard_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write system stats
            fputcsv($file, ['System Statistics']);
            foreach ($data['system_statistics'] as $key => $value) {
                fputcsv($file, [$key, $value]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}