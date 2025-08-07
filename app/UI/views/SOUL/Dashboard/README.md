# SOUL Framework Cognitive Processing Dashboard

A comprehensive real-time dashboard for monitoring and visualizing SOUL Framework cognitive processing activities.

## Features

### 🎛️ System Overview Panel
- Real-time system statistics (active sessions, agent services, concepts, relationships)
- K-line learning counter and average processing time
- System health indicators with color-coded status
- Performance metrics monitoring

### 🌐 Real-time Activation Visualizer  
- Live spreading activation visualization using Vis.js
- Color-coded activation levels with gradients
- Interactive controls for speed, threshold, and visualization mode
- Progressive activation spreading with timing controls
- Session-based activation tracking and replay capabilities

### 🤖 Agent Execution Monitor
- Real-time display of active agent executions
- Agent status tracking (executing, completed, failed)
- Execution time monitoring and progress indicators
- Recent activity feed with success/failure indicators
- Agent method and service information display

### 🧠 K-line Learning Tracker
- Learning pattern statistics and usage metrics  
- Interactive chart showing K-line usage over time
- Top performing patterns with success rates
- Configurable time range filtering (1h, 24h, 7d, 30d)
- New pattern discovery tracking

### 🔄 Processing Pipeline Visualizer
- Visual representation of cognitive processing stages
- Real-time stage progress tracking
- Stage completion indicators and timing
- Pipeline flow animations and transitions
- Current processing state visualization

### 📋 Session Management Panel
- Active processing session monitoring
- Session statistics (nodes activated, agents executed)
- Session start/stop controls and status tracking
- Test session creation capabilities
- Session performance metrics and duration tracking

## Technical Implementation

### Backend Components
- **DashboardController**: Main REST API endpoints for dashboard data
- **MindService Integration**: Direct integration with SOUL cognitive services
- **GraphService Integration**: Real-time spreading activation data
- **Caching Layer**: Performance optimization for frequent updates
- **HTMX Integration**: Server-side rendering with real-time updates

### Frontend Components
- **ActivationVisualizer**: Extended visualization component for activation flows
- **Real-time Updates**: HTMX-powered 5-second polling for live data
- **AlpineJS State Management**: Reactive dashboard state handling
- **Chart.js Integration**: K-line usage charts and metrics visualization
- **Responsive Design**: Mobile-friendly layout with Fomantic UI

### Styling & Design
- **Fomantic UI Framework**: Consistent with existing application design
- **LESS Preprocessing**: Modular styling with design system integration
- **Grid Layout**: Responsive 12-column layout with adaptive breakpoints
- **Animation System**: CSS animations for status changes and updates
- **Color Coding**: Semantic colors for health, status, and activation levels

## API Endpoints

```
GET  /soul/dashboard                    # Main dashboard view
GET  /soul/dashboard/data               # Comprehensive dashboard data
GET  /soul/dashboard/status             # Real-time status updates (HTMX)
GET  /soul/dashboard/session/{id}       # Specific session status
POST /soul/dashboard/test-session       # Start test cognitive session
POST /soul/dashboard/session/{id}/terminate  # Terminate session
GET  /soul/dashboard/activation/{concept}     # Activation visualization data
GET  /soul/dashboard/klines             # K-line learning statistics
GET  /soul/dashboard/export             # Export dashboard data
```

## Configuration

Dashboard behavior can be configured in `config/soul.php`:

```php
'dashboard' => [
    'refresh_interval' => 5,              // Real-time update frequency (seconds)
    'max_recent_activity' => 50,          // Maximum cached activity items
    'cache_duration' => 300,              // Cache duration (seconds)
    'enable_real_time' => true,           // Enable real-time updates
    'visualization' => [
        'max_nodes' => 100,               // Maximum nodes in visualization
        'animation_speed' => 1.0,         // Animation speed multiplier
        'default_threshold' => 0.3,       // Default activation threshold
    ],
],
```

## Usage

1. **Access the Dashboard**: Navigate to `/soul/dashboard`
2. **Monitor System Health**: Check the system overview panel for current status
3. **Visualize Activation**: Use the activation visualizer for real-time processing
4. **Track Learning**: Monitor K-line patterns and learning progress  
5. **Manage Sessions**: Start test sessions and monitor processing pipeline
6. **Export Data**: Use the export functionality for analysis and reporting

## Real-time Features

The dashboard provides several real-time capabilities:

- **HTMX Polling**: Automatic updates every 5 seconds
- **WebSocket Support**: Real-time activation updates (if configured)
- **Event-driven Updates**: Reactive updates based on system events
- **Performance Monitoring**: Live tracking of processing metrics
- **Health Monitoring**: Continuous system health assessment

## Responsive Design

The dashboard is fully responsive and adapts to different screen sizes:

- **Desktop**: Full 12-column grid layout with all panels visible
- **Tablet**: 8-column layout with stacked panels
- **Mobile**: Single-column layout with collapsible panels
- **Touch Support**: Mobile-friendly interactions and controls

## Integration Points

The dashboard integrates with core SOUL Framework services:

- **MindService**: Session management and system statistics
- **GraphService**: Spreading activation and graph operations
- **FrameService**: Frame-based processing monitoring
- **ImageSchemaService**: Spatial reasoning visualization
- **YamlLoaderService**: Knowledge base status and updates

## Performance Considerations

- **Efficient Caching**: Strategic caching to reduce database queries
- **Lazy Loading**: Components load only when needed
- **Update Batching**: Batched updates for better performance
- **Memory Management**: Proper cleanup of visualization components
- **Network Optimization**: Optimized API calls and data transfer

This dashboard provides a comprehensive view into the SOUL Framework's cognitive processing capabilities, enabling researchers and developers to monitor, analyze, and optimize the system's performance in real-time.