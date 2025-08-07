{{-- 
SOUL Dashboard Real-time Status Fragment
This fragment is loaded via HTMX every few seconds to provide real-time updates
--}}

<div id="dashboard-status-updates" style="display: none;">
    <script>
        // Real-time status update data
        window.soulDashboardUpdate = {
            systemStats: {
                activeSessions: {{ $systemStats['activeSessions'] ?? 0 }},
                agentServices: {{ $systemStats['agentServices'] ?? 0 }},
                totalConcepts: {{ $systemStats['totalConcepts'] ?? 0 }},
                totalRelationships: {{ $systemStats['totalRelationships'] ?? 0 }},
                kLinesCount: {{ $systemStats['kLinesCount'] ?? 0 }},
                avgProcessingTime: {{ $systemStats['avgProcessingTime'] ?? 0 }}
            },
            
            @if(isset($activeAgents) && count($activeAgents) > 0)
            activeAgents: [
                @foreach($activeAgents as $agent)
                {
                    id: '{{ $agent['id'] ?? uniqid() }}',
                    name: '{{ $agent['name'] ?? 'Unknown Agent' }}',
                    method: '{{ $agent['method'] ?? 'executeAgent' }}',
                    status: '{{ $agent['status'] ?? 'executing' }}',
                    executionTime: {{ $agent['executionTime'] ?? 0 }},
                    progress: {{ $agent['progress'] ?? 50 }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ],
            @else
            activeAgents: [],
            @endif
            
            @if(isset($activeSessions) && count($activeSessions) > 0)
            activeSessions: [
                @foreach($activeSessions as $session)
                {
                    id: '{{ $session['id'] ?? 'session_' . uniqid() }}',
                    status: '{{ $session['status'] ?? 'processing' }}',
                    startedAt: '{{ $session['startedAt'] ?? now()->toISOString() }}',
                    nodesActivated: {{ $session['nodesActivated'] ?? 0 }},
                    agentsExecuted: {{ $session['agentsExecuted'] ?? 0 }},
                    processingTime: {{ $session['processingTime'] ?? 0 }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ],
            @else
            activeSessions: [],
            @endif
            
            @if(isset($recentActivity) && count($recentActivity) > 0)
            recentAgentActivity: [
                @foreach(array_slice($recentActivity, 0, 10) as $activity)
                {
                    id: '{{ $activity['id'] ?? uniqid() }}',
                    type: '{{ $activity['type'] ?? 'unknown' }}',
                    agent: '{{ $activity['agent'] ?? 'Unknown' }}',
                    result: '{{ $activity['result'] ?? 'success' }}',
                    executionTime: {{ $activity['executionTime'] ?? 0 }},
                    timestamp: '{{ $activity['timestamp'] ?? now()->toISOString() }}'
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ],
            @else
            recentAgentActivity: [],
            @endif
            
            @if(isset($klineStats))
            klineStats: {
                newPatterns: {{ $klineStats['newPatterns'] ?? 0 }},
                strengthenedPatterns: {{ $klineStats['strengthenedPatterns'] ?? 0 }},
                totalUsage: {{ $klineStats['totalUsage'] ?? 0 }},
                @if(isset($klineStats['chartData']))
                chartData: {!! json_encode($klineStats['chartData']) !!}
                @else
                chartData: null
                @endif
            },
            @endif
            
            @if(isset($topKlines) && count($topKlines) > 0)
            topKlines: [
                @foreach(array_slice($topKlines, 0, 5) as $kline)
                {
                    id: '{{ $kline['id'] ?? uniqid() }}',
                    context: '{{ $kline['context'] ?? 'Unknown Context' }}',
                    usageCount: {{ $kline['usageCount'] ?? 0 }},
                    successRate: {{ $kline['successRate'] ?? 1.0 }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ],
            @else
            topKlines: [],
            @endif
            
            @if(isset($pipelineStage))
            pipelineStage: {
                stage: '{{ $pipelineStage['stage'] ?? null }}',
                status: '{{ $pipelineStage['status'] ?? 'idle' }}',
                completed: {{ $pipelineStage['completed'] ? 'true' : 'false' }}
            },
            @endif
            
            // Connection and health status
            connectionStatus: '{{ $connectionStatus ?? 'connected' }}',
            lastUpdate: '{{ $lastUpdate ?? now()->toISOString() }}',
            serverTime: '{{ now()->toISOString() }}'
        };
        
        // Dispatch custom event to update the dashboard
        document.dispatchEvent(new CustomEvent('soulProcessingUpdate', {
            detail: window.soulDashboardUpdate
        }));
    </script>
</div>

{{-- Health check indicator (visible for debugging) --}}
@if(config('app.debug'))
<div class="ui mini message" style="position: fixed; bottom: 10px; right: 10px; z-index: 1000; opacity: 0.7;">
    <div class="content">
        <div class="header">Dashboard Update</div>
        <p>Last update: {{ now()->format('H:i:s') }}</p>
        <div class="ui mini labels">
            <div class="ui green label">Sessions: {{ $systemStats['activeSessions'] ?? 0 }}</div>
            <div class="ui blue label">Agents: {{ count($activeAgents ?? []) }}</div>
            <div class="ui purple label">K-lines: {{ $systemStats['kLinesCount'] ?? 0 }}</div>
        </div>
    </div>
</div>
@endif