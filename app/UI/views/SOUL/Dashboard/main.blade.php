<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/soul','SOUL Framework'],['','Cognitive Dashboard']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="soul-dashboard" 
                         x-data="soulDashboard()" 
                         x-init="initializeDashboard()"
                         hx-get="/soul/dashboard/status"
                         hx-trigger="every 5s"
                         hx-target="#dashboard-status-updates"
                         hx-swap="outerHTML"
                    >
                        <!-- Dashboard Header -->
                        <div class="dashboard-header">
                            <div class="dashboard-title">
                                <h1 class="ui header">
                                    <i class="brain icon"></i>
                                    <div class="content">
                                        Cognitive Processing Dashboard
                                        <div class="sub header">Real-time SOUL Framework Monitoring & Visualization</div>
                                    </div>
                                </h1>
                            </div>
                            <div class="dashboard-controls">
                                <div class="ui buttons">
                                    <button class="ui button" 
                                            :class="{ 'active primary': autoRefresh }" 
                                            @click="toggleAutoRefresh()">
                                        <i class="sync icon" :class="{ 'loading': autoRefresh }"></i>
                                        Auto Refresh
                                    </button>
                                    <button class="ui button" @click="refreshAll()">
                                        <i class="refresh icon"></i>
                                        Refresh Now
                                    </button>
                                    <button class="ui button" @click="exportDashboard()">
                                        <i class="download icon"></i>
                                        Export
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Status Updates (Hidden, HTMX target) -->
                        <div id="dashboard-status-updates" style="display: none;" 
                             x-on:htmx:after-swap="updateDashboardData($event)">
                        </div>

                        <!-- Dashboard Grid -->
                        <div class="dashboard-grid">
                            <!-- System Overview Panel -->
                            <div class="dashboard-panel system-overview">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="cogs icon"></i>
                                        System Overview
                                    </h3>
                                    <div class="panel-controls">
                                        <i class="info circle icon" title="System health and statistics"></i>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.activeSessions">-</div>
                                            <div class="stat-label">Active Sessions</div>
                                            <div class="stat-indicator" 
                                                 :class="systemStats.activeSessions > 5 ? 'warning' : 'healthy'"></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.agentServices">-</div>
                                            <div class="stat-label">Agent Services</div>
                                            <div class="stat-indicator healthy"></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.totalConcepts">-</div>
                                            <div class="stat-label">Total Concepts</div>
                                            <div class="stat-indicator healthy"></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.totalRelationships">-</div>
                                            <div class="stat-label">Relationships</div>
                                            <div class="stat-indicator healthy"></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.kLinesCount">-</div>
                                            <div class="stat-label">K-Lines Learned</div>
                                            <div class="stat-indicator" 
                                                 :class="systemStats.kLinesCount > 0 ? 'healthy' : 'warning'"></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value" x-text="systemStats.avgProcessingTime + 'ms'">-</div>
                                            <div class="stat-label">Avg Processing Time</div>
                                            <div class="stat-indicator" 
                                                 :class="systemStats.avgProcessingTime > 5000 ? 'critical' : 'healthy'"></div>
                                        </div>
                                    </div>
                                    <div class="system-health">
                                        <div class="health-indicator">
                                            <i class="circle icon" :class="getSystemHealthClass()"></i>
                                            <span class="health-status" x-text="getSystemHealthStatus()">Checking...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time Processing Visualizer -->
                            <div class="dashboard-panel activation-visualizer">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="project diagram icon"></i>
                                        Real-time Activation Flow
                                    </h3>
                                    <div class="panel-controls">
                                        <div class="ui buttons mini">
                                            <button class="ui button" :class="{ 'active primary': activationMode === 'live' }" 
                                                    @click="setActivationMode('live')">Live</button>
                                            <button class="ui button" :class="{ 'active': activationMode === 'replay' }" 
                                                    @click="setActivationMode('replay')">Replay</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="activation-controls">
                                        <div class="ui form mini">
                                            <div class="inline fields">
                                                <div class="field">
                                                    <label>Speed:</label>
                                                    <input type="range" min="0.5" max="3.0" step="0.1" 
                                                           x-model="visualizationSpeed" @input="updateVisualizationSpeed()">
                                                    <span x-text="visualizationSpeed + 'x'">1.0x</span>
                                                </div>
                                                <div class="field">
                                                    <label>Threshold:</label>
                                                    <input type="range" min="0.1" max="1.0" step="0.05" 
                                                           x-model="activationThreshold" @input="updateActivationThreshold()">
                                                    <span x-text="activationThreshold">0.3</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="activation-visualization" style="height: 350px; position: relative;">
                                        <!-- Activation visualization will be rendered here -->
                                        <div class="visualization-placeholder" x-show="!activationVisualizationReady">
                                            <i class="spinner loading icon big"></i>
                                            <p>Initializing activation visualization...</p>
                                        </div>
                                    </div>
                                    <div class="activation-legend">
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: #2185d0;"></div>
                                            <span>High Activation</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: #21ba45;"></div>
                                            <span>Medium Activation</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: #fbbd08;"></div>
                                            <span>Low Activation</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: #a333c8;"></div>
                                            <span>Procedural Agent</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Agent Execution Monitor -->
                            <div class="dashboard-panel agent-monitor">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="users icon"></i>
                                        Agent Execution Monitor
                                    </h3>
                                    <div class="panel-controls">
                                        <span class="agent-count-badge" x-text="activeAgents.length + ' active'">0 active</span>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="agent-list" x-show="activeAgents.length > 0">
                                        <template x-for="agent in activeAgents" :key="agent.id">
                                            <div class="agent-item" :class="agent.status">
                                                <div class="agent-info">
                                                    <div class="agent-name" x-text="agent.name">Agent Name</div>
                                                    <div class="agent-method" x-text="agent.method">method</div>
                                                </div>
                                                <div class="agent-status">
                                                    <div class="status-indicator" :class="agent.status"></div>
                                                    <div class="execution-time" x-text="agent.executionTime + 'ms'">0ms</div>
                                                </div>
                                                <div class="agent-progress">
                                                    <div class="ui mini progress" :class="agent.status">
                                                        <div class="bar" :style="'width: ' + agent.progress + '%'"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="empty-state" x-show="activeAgents.length === 0">
                                        <i class="robot icon big"></i>
                                        <p>No agents currently executing</p>
                                    </div>
                                    
                                    <!-- Recent Agent Activity -->
                                    <div class="recent-activity" x-show="recentAgentActivity.length > 0">
                                        <h4>Recent Activity</h4>
                                        <div class="activity-list">
                                            <template x-for="activity in recentAgentActivity" :key="activity.id">
                                                <div class="activity-item" :class="activity.result">
                                                    <div class="activity-time" x-text="formatTime(activity.timestamp)">now</div>
                                                    <div class="activity-agent" x-text="activity.agent">Agent</div>
                                                    <div class="activity-result" :class="activity.result">
                                                        <i class="icon" :class="activity.result === 'success' ? 'check green' : 'times red'"></i>
                                                        <span x-text="activity.executionTime + 'ms'">0ms</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- K-line Learning Tracker -->
                            <div class="dashboard-panel kline-tracker">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="lightbulb icon"></i>
                                        K-line Learning Tracker
                                    </h3>
                                    <div class="panel-controls">
                                        <div class="ui dropdown mini">
                                            <div class="text">Last 24h</div>
                                            <i class="dropdown icon"></i>
                                            <div class="menu">
                                                <div class="item" data-value="1h">Last Hour</div>
                                                <div class="item" data-value="24h">Last 24 Hours</div>
                                                <div class="item" data-value="7d">Last Week</div>
                                                <div class="item" data-value="30d">Last Month</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="kline-stats">
                                        <div class="stat-row">
                                            <div class="stat-item">
                                                <span class="stat-value" x-text="klineStats.newPatterns">0</span>
                                                <span class="stat-label">New Patterns</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-value" x-text="klineStats.strengthenedPatterns">0</span>
                                                <span class="stat-label">Strengthened</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-value" x-text="klineStats.totalUsage">0</span>
                                                <span class="stat-label">Total Usage</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- K-line Patterns Chart -->
                                    <div class="kline-chart-container">
                                        <canvas id="kline-usage-chart" width="300" height="200"></canvas>
                                    </div>
                                    
                                    <!-- Top K-lines -->
                                    <div class="top-klines" x-show="topKlines.length > 0">
                                        <h4>Most Used Patterns</h4>
                                        <template x-for="kline in topKlines" :key="kline.id">
                                            <div class="kline-item">
                                                <div class="kline-context" x-text="kline.context">Context</div>
                                                <div class="kline-stats">
                                                    <span class="usage-count" x-text="kline.usageCount">0</span>
                                                    <span class="success-rate" x-text="Math.round(kline.successRate * 100) + '%'">100%</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Processing Pipeline Visualizer -->
                            <div class="dashboard-panel pipeline-visualizer">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="sitemap icon"></i>
                                        Processing Pipeline
                                    </h3>
                                </div>
                                <div class="panel-content">
                                    <div class="pipeline-stages">
                                        <div class="pipeline-stage" 
                                             :class="{ 'active': currentStage === 'concept-extraction', 'completed': isStageCompleted('concept-extraction') }">
                                            <div class="stage-icon">
                                                <i class="search icon"></i>
                                            </div>
                                            <div class="stage-info">
                                                <div class="stage-name">Concept Extraction</div>
                                                <div class="stage-status" x-text="getStageStatus('concept-extraction')">Idle</div>
                                            </div>
                                        </div>
                                        <div class="pipeline-arrow"><i class="arrow right icon"></i></div>
                                        
                                        <div class="pipeline-stage" 
                                             :class="{ 'active': currentStage === 'spreading-activation', 'completed': isStageCompleted('spreading-activation') }">
                                            <div class="stage-icon">
                                                <i class="share alternate icon"></i>
                                            </div>
                                            <div class="stage-info">
                                                <div class="stage-name">Spreading Activation</div>
                                                <div class="stage-status" x-text="getStageStatus('spreading-activation')">Idle</div>
                                            </div>
                                        </div>
                                        <div class="pipeline-arrow"><i class="arrow right icon"></i></div>
                                        
                                        <div class="pipeline-stage" 
                                             :class="{ 'active': currentStage === 'agent-discovery', 'completed': isStageCompleted('agent-discovery') }">
                                            <div class="stage-icon">
                                                <i class="users icon"></i>
                                            </div>
                                            <div class="stage-info">
                                                <div class="stage-name">Agent Discovery</div>
                                                <div class="stage-status" x-text="getStageStatus('agent-discovery')">Idle</div>
                                            </div>
                                        </div>
                                        <div class="pipeline-arrow"><i class="arrow right icon"></i></div>
                                        
                                        <div class="pipeline-stage" 
                                             :class="{ 'active': currentStage === 'processing-rounds', 'completed': isStageCompleted('processing-rounds') }">
                                            <div class="stage-icon">
                                                <i class="sync icon"></i>
                                            </div>
                                            <div class="stage-info">
                                                <div class="stage-name">Processing Rounds</div>
                                                <div class="stage-status" x-text="getStageStatus('processing-rounds')">Idle</div>
                                            </div>
                                        </div>
                                        <div class="pipeline-arrow"><i class="arrow right icon"></i></div>
                                        
                                        <div class="pipeline-stage" 
                                             :class="{ 'active': currentStage === 'response-generation', 'completed': isStageCompleted('response-generation') }">
                                            <div class="stage-icon">
                                                <i class="lightbulb icon"></i>
                                            </div>
                                            <div class="stage-info">
                                                <div class="stage-name">Response Generation</div>
                                                <div class="stage-status" x-text="getStageStatus('response-generation')">Idle</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Management Panel -->
                            <div class="dashboard-panel session-management">
                                <div class="panel-header">
                                    <h3 class="panel-title">
                                        <i class="tasks icon"></i>
                                        Processing Sessions
                                    </h3>
                                    <div class="panel-controls">
                                        <button class="ui button mini" @click="startTestSession()">
                                            <i class="play icon"></i>
                                            Start Test Session
                                        </button>
                                    </div>
                                </div>
                                <div class="panel-content">
                                    <div class="session-list" x-show="activeSessions.length > 0">
                                        <template x-for="session in activeSessions" :key="session.id">
                                            <div class="session-item" :class="session.status">
                                                <div class="session-info">
                                                    <div class="session-id" x-text="session.id">Session ID</div>
                                                    <div class="session-details">
                                                        <span class="session-status" x-text="session.status">Status</span>
                                                        <span class="session-time" x-text="formatDuration(session.startedAt)">0s ago</span>
                                                    </div>
                                                </div>
                                                <div class="session-stats">
                                                    <span class="nodes-activated" x-text="session.nodesActivated + ' nodes'">0 nodes</span>
                                                    <span class="agents-executed" x-text="session.agentsExecuted + ' agents'">0 agents</span>
                                                </div>
                                                <div class="session-actions">
                                                    <button class="ui button mini" @click="viewSession(session.id)">
                                                        <i class="eye icon"></i>
                                                        View
                                                    </button>
                                                    <button class="ui button mini red" 
                                                            x-show="session.status === 'processing'" 
                                                            @click="terminateSession(session.id)">
                                                        <i class="stop icon"></i>
                                                        Stop
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="empty-state" x-show="activeSessions.length === 0">
                                        <i class="calendar outline icon big"></i>
                                        <p>No active processing sessions</p>
                                        <button class="ui button primary small" @click="startTestSession()">
                                            Start Test Session
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function soulDashboard() {
            return {
                // State
                autoRefresh: true,
                activationVisualizationReady: false,
                visualizationSpeed: 1.0,
                activationThreshold: 0.3,
                activationMode: 'live',
                currentStage: null,
                
                // System Statistics
                systemStats: {
                    activeSessions: 0,
                    agentServices: 0,
                    totalConcepts: 0,
                    totalRelationships: 0,
                    kLinesCount: 0,
                    avgProcessingTime: 0
                },
                
                // Active entities
                activeAgents: [],
                activeSessions: [],
                recentAgentActivity: [],
                
                // K-line data
                klineStats: {
                    newPatterns: 0,
                    strengthenedPatterns: 0,
                    totalUsage: 0
                },
                topKlines: [],
                
                // Pipeline stages
                pipelineStages: {
                    'concept-extraction': { status: 'idle', completed: false },
                    'spreading-activation': { status: 'idle', completed: false },
                    'agent-discovery': { status: 'idle', completed: false },
                    'processing-rounds': { status: 'idle', completed: false },
                    'response-generation': { status: 'idle', completed: false }
                },
                
                // Initialization
                initializeDashboard() {
                    this.loadInitialData();
                    this.initializeActivationVisualization();
                    this.initializeKlineChart();
                    this.setupEventListeners();
                    
                    // Initialize Fomantic UI components
                    this.$nextTick(() => {
                        $('.ui.dropdown').dropdown();
                    });
                },
                
                async loadInitialData() {
                    try {
                        const response = await fetch('/soul/dashboard/data');
                        const data = await response.json();
                        this.updateSystemStats(data.systemStats);
                        this.updateActiveSessions(data.activeSessions);
                        this.updateKlineStats(data.klineStats);
                    } catch (error) {
                        console.error('Failed to load dashboard data:', error);
                        this.showError('Failed to load dashboard data');
                    }
                },
                
                // System health and statistics
                updateSystemStats(stats) {
                    this.systemStats = { ...this.systemStats, ...stats };
                },
                
                getSystemHealthClass() {
                    const { activeSessions, avgProcessingTime } = this.systemStats;
                    if (avgProcessingTime > 10000 || activeSessions > 8) return 'red';
                    if (avgProcessingTime > 5000 || activeSessions > 5) return 'yellow';
                    return 'green';
                },
                
                getSystemHealthStatus() {
                    const healthClass = this.getSystemHealthClass();
                    return {
                        'green': 'Healthy',
                        'yellow': 'Warning', 
                        'red': 'Critical'
                    }[healthClass] || 'Unknown';
                },
                
                // Activation visualization
                initializeActivationVisualization() {
                    this.$nextTick(() => {
                        try {
                            if (window.ActivationVisualizer) {
                                this.activationVisualizer = new window.ActivationVisualizer('activation-visualization', {
                                    speed: this.visualizationSpeed,
                                    threshold: this.activationThreshold,
                                    mode: this.activationMode
                                });
                                this.activationVisualizationReady = true;
                            } else {
                                console.warn('ActivationVisualizer not available');
                            }
                        } catch (error) {
                            console.error('Failed to initialize activation visualization:', error);
                        }
                    });
                },
                
                setActivationMode(mode) {
                    this.activationMode = mode;
                    if (this.activationVisualizer) {
                        this.activationVisualizer.setMode(mode);
                    }
                },
                
                updateVisualizationSpeed() {
                    if (this.activationVisualizer) {
                        this.activationVisualizer.setSpeed(this.visualizationSpeed);
                    }
                },
                
                updateActivationThreshold() {
                    if (this.activationVisualizer) {
                        this.activationVisualizer.setThreshold(this.activationThreshold);
                    }
                },
                
                // Agent monitoring
                updateActiveAgents(agents) {
                    this.activeAgents = agents || [];
                },
                
                updateActiveSessions(sessions) {
                    this.activeSessions = sessions || [];
                },
                
                // K-line tracking
                initializeKlineChart() {
                    this.$nextTick(() => {
                        const canvas = document.getElementById('kline-usage-chart');
                        if (canvas && window.Chart) {
                            this.klineChart = new Chart(canvas, {
                                type: 'line',
                                data: {
                                    labels: [],
                                    datasets: [{
                                        label: 'K-line Usage',
                                        data: [],
                                        borderColor: '#2185d0',
                                        backgroundColor: 'rgba(33, 133, 208, 0.1)',
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        }
                    });
                },
                
                updateKlineStats(stats) {
                    this.klineStats = { ...this.klineStats, ...stats };
                    this.topKlines = stats.topPatterns || [];
                    
                    if (this.klineChart && stats.chartData) {
                        this.klineChart.data = stats.chartData;
                        this.klineChart.update();
                    }
                },
                
                // Pipeline stages
                getStageStatus(stage) {
                    return this.pipelineStages[stage]?.status || 'idle';
                },
                
                isStageCompleted(stage) {
                    return this.pipelineStages[stage]?.completed || false;
                },
                
                updatePipelineStage(stage, status, completed = false) {
                    if (this.pipelineStages[stage]) {
                        this.pipelineStages[stage].status = status;
                        this.pipelineStages[stage].completed = completed;
                        this.currentStage = status === 'active' ? stage : null;
                    }
                },
                
                // Dashboard controls
                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                },
                
                refreshAll() {
                    this.loadInitialData();
                    // Trigger HTMX refresh
                    htmx.trigger(document.body, 'refreshDashboard');
                },
                
                exportDashboard() {
                    // Implementation for dashboard export
                    console.log('Exporting dashboard data...');
                },
                
                // Session management
                async startTestSession() {
                    try {
                        const response = await fetch('/soul/dashboard/test-session', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                text: 'Test cognitive processing session',
                                concepts: ['TEST', 'PROCESSING']
                            })
                        });
                        const result = await response.json();
                        this.showSuccess('Test session started: ' + result.sessionId);
                    } catch (error) {
                        console.error('Failed to start test session:', error);
                        this.showError('Failed to start test session');
                    }
                },
                
                viewSession(sessionId) {
                    window.location.href = `/soul/dashboard/session/${sessionId}`;
                },
                
                async terminateSession(sessionId) {
                    try {
                        const response = await fetch(`/soul/dashboard/session/${sessionId}/terminate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const result = await response.json();
                        this.showSuccess('Session terminated');
                        this.loadInitialData();
                    } catch (error) {
                        console.error('Failed to terminate session:', error);
                        this.showError('Failed to terminate session');
                    }
                },
                
                // Event handling
                setupEventListeners() {
                    // Listen for real-time updates
                    document.addEventListener('soulProcessingUpdate', (event) => {
                        this.handleProcessingUpdate(event.detail);
                    });
                },
                
                handleProcessingUpdate(data) {
                    if (data.systemStats) this.updateSystemStats(data.systemStats);
                    if (data.activeAgents) this.updateActiveAgents(data.activeAgents);
                    if (data.activeSessions) this.updateActiveSessions(data.activeSessions);
                    if (data.klineStats) this.updateKlineStats(data.klineStats);
                    if (data.pipelineStage) {
                        this.updatePipelineStage(data.pipelineStage.stage, data.pipelineStage.status, data.pipelineStage.completed);
                    }
                },
                
                updateDashboardData(event) {
                    try {
                        const data = JSON.parse(event.detail.xhr.response);
                        this.handleProcessingUpdate(data);
                    } catch (error) {
                        console.error('Failed to parse dashboard update:', error);
                    }
                },
                
                // Utility functions
                formatTime(timestamp) {
                    return new Date(timestamp).toLocaleTimeString();
                },
                
                formatDuration(startTime) {
                    const elapsed = Date.now() - new Date(startTime).getTime();
                    if (elapsed < 60000) return Math.floor(elapsed / 1000) + 's ago';
                    if (elapsed < 3600000) return Math.floor(elapsed / 60000) + 'm ago';
                    return Math.floor(elapsed / 3600000) + 'h ago';
                },
                
                showSuccess(message) {
                    $('body').toast({
                        message: message,
                        class: 'success',
                        displayTime: 3000,
                        position: 'top center'
                    });
                },
                
                showError(message) {
                    $('body').toast({
                        message: message,
                        class: 'error',
                        displayTime: 5000,
                        position: 'top center'
                    });
                }
            }
        }
    </script>
</x-layout::index>