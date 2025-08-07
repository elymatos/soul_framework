/**
 * SOUL Framework Activation Visualizer
 * 
 * Real-time visualization component for cognitive processing activation flows.
 * Extends the base SoulGraphVisualization with activation-specific features:
 * - Animated spreading activation with timing controls
 * - Color-coded activation levels with gradients
 * - Real-time updates during processing sessions
 * - Interactive activation flow controls
 * - Session-based activation tracking and replay
 */
class ActivationVisualizer extends SoulGraphVisualization {
    constructor(containerId, options = {}) {
        const activationOptions = {
            speed: 1.0,
            threshold: 0.3,
            mode: 'live', // 'live' or 'replay'
            animationDuration: 2000,
            pulseInterval: 500,
            maxActivationLevel: 1.0,
            ...options
        };
        
        super(containerId, activationOptions);
        
        // Activation-specific state
        this.activationState = {
            isAnimating: false,
            currentWave: 0,
            activationLevels: new Map(),
            activationHistory: [],
            sessionId: null,
            startTime: null
        };
        
        // Animation control
        this.animationFrame = null;
        this.activationTimer = null;
        
        // WebSocket connection for real-time updates
        this.websocket = null;
        
        this.initializeActivationFeatures();
    }

    /**
     * Initialize activation-specific features
     */
    initializeActivationFeatures() {
        this.setupActivationStyles();
        this.setupRealTimeConnection();
        this.createActivationControls();
    }

    /**
     * Setup activation-specific node and edge styles
     */
    setupActivationStyles() {
        // Override network options for activation visualization
        const activationOptions = {
            ...this.getNetworkOptions(),
            nodes: {
                ...this.getNetworkOptions().nodes,
                scaling: {
                    min: 10,
                    max: 50
                },
                borderWidth: 3,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.2)',
                    size: 10,
                    x: 2,
                    y: 2
                }
            },
            edges: {
                ...this.getNetworkOptions().edges,
                scaling: {
                    min: 1,
                    max: 8
                },
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 5
                }
            }
        };

        if (this.network) {
            this.network.setOptions(activationOptions);
        }
    }

    /**
     * Create activation control interface
     */
    createActivationControls() {
        // This would be integrated with the dashboard controls
        // For now, we just ensure the methods are available
    }

    /**
     * Setup real-time WebSocket connection
     */
    setupRealTimeConnection() {
        if (this.options.mode === 'live' && window.Echo) {
            // Setup Laravel Echo WebSocket connection for real-time updates
            this.setupEchoConnection();
        }
    }

    /**
     * Setup Laravel Echo connection
     */
    setupEchoConnection() {
        try {
            window.Echo.channel('soul.activation')
                .listen('ActivationUpdate', (event) => {
                    this.handleActivationUpdate(event);
                })
                .listen('SessionStarted', (event) => {
                    this.handleSessionStarted(event);
                })
                .listen('SessionCompleted', (event) => {
                    this.handleSessionCompleted(event);
                });
        } catch (error) {
            console.warn('Failed to setup Echo connection:', error);
        }
    }

    /**
     * Start activation visualization for a session
     */
    startActivationSession(sessionId, initialConcepts = []) {
        this.activationState.sessionId = sessionId;
        this.activationState.startTime = Date.now();
        this.activationState.currentWave = 0;
        this.activationState.activationHistory = [];
        
        // Clear existing visualization
        this.clear();
        
        // Load initial concepts if provided
        if (initialConcepts.length > 0) {
            this.loadInitialConcepts(initialConcepts);
        }
        
        // Start animation loop
        this.startActivationAnimation();
    }

    /**
     * Load initial concepts for activation
     */
    async loadInitialConcepts(concepts) {
        try {
            const conceptNodes = concepts.map((concept, index) => ({
                id: concept,
                label: concept,
                originalData: { name: concept, type: 'initial_concept' },
                x: Math.cos(index * 2 * Math.PI / concepts.length) * 100,
                y: Math.sin(index * 2 * Math.PI / concepts.length) * 100
            }));

            this.nodes.add(conceptNodes.map(node => this.transformNodeForActivation(node, 1.0)));
            
            // Set initial activation levels
            concepts.forEach(concept => {
                this.activationState.activationLevels.set(concept, 1.0);
            });

            this.network.fit();
        } catch (error) {
            console.error('Failed to load initial concepts:', error);
        }
    }

    /**
     * Handle real-time activation update
     */
    handleActivationUpdate(event) {
        const { sessionId, activationData, stage, timestamp } = event;
        
        if (sessionId !== this.activationState.sessionId) {
            return; // Not our session
        }

        // Store in history
        this.activationState.activationHistory.push({
            ...activationData,
            stage,
            timestamp
        });

        // Update visualization
        this.updateActivationVisualization(activationData);
        
        // Trigger stage-specific animations
        this.triggerStageAnimation(stage);
    }

    /**
     * Update activation visualization with new data
     */
    updateActivationVisualization(activationData) {
        const { activated_nodes = [], relationships = [] } = activationData;
        
        // Update existing nodes and add new ones
        const existingNodeIds = new Set(this.nodes.getIds());
        const newNodes = [];
        const nodeUpdates = [];

        activated_nodes.forEach(nodeData => {
            const nodeId = nodeData.name || nodeData.id;
            const activation = nodeData.activation_strength || 0;
            
            // Store activation level
            this.activationState.activationLevels.set(nodeId, activation);
            
            if (existingNodeIds.has(nodeId)) {
                // Update existing node
                nodeUpdates.push({
                    id: nodeId,
                    ...this.getActivationNodeStyle(activation, nodeData)
                });
            } else {
                // Add new node
                newNodes.push(this.transformNodeForActivation(nodeData, activation));
            }
        });

        // Apply updates
        if (newNodes.length > 0) {
            this.nodes.add(newNodes);
        }
        if (nodeUpdates.length > 0) {
            this.nodes.update(nodeUpdates);
        }

        // Update relationships
        this.updateActivationEdges(relationships);
        
        // Trigger activation wave animation
        this.triggerActivationWave(activated_nodes);
    }

    /**
     * Transform node for activation visualization
     */
    transformNodeForActivation(nodeData, activation) {
        const baseNode = this.transformNodeForVis(nodeData);
        const activationStyle = this.getActivationNodeStyle(activation, nodeData);
        
        return {
            ...baseNode,
            ...activationStyle,
            activation: activation
        };
    }

    /**
     * Get node style based on activation level
     */
    getActivationNodeStyle(activation, nodeData) {
        // Base color from node type
        const baseStyle = this.getNodeStyle(nodeData.type, nodeData.isPrimitive, nodeData.labels);
        
        // Modify based on activation level
        const intensity = Math.max(0, Math.min(1, activation / this.options.maxActivationLevel));
        
        // Color gradient based on activation
        let activationColor;
        if (intensity >= 0.8) {
            activationColor = { background: '#ff4757', border: '#ff3742' }; // High activation - Red
        } else if (intensity >= 0.6) {
            activationColor = { background: '#ffa502', border: '#ff9500' }; // Medium-high - Orange
        } else if (intensity >= 0.4) {
            activationColor = { background: '#2ed573', border: '#20bf6b' }; // Medium - Green
        } else if (intensity >= 0.2) {
            activationColor = { background: '#3742fa', border: '#2f3542' }; // Low-medium - Blue
        } else {
            activationColor = baseStyle.color; // Very low - Base color
        }
        
        // Size scaling based on activation
        const sizeMultiplier = 1 + (intensity * 1.5);
        const activatedSize = Math.round((baseStyle.size || 20) * sizeMultiplier);
        
        return {
            color: {
                ...activationColor,
                highlight: {
                    background: this.lightenColor(activationColor.background, 20),
                    border: activationColor.border
                }
            },
            size: activatedSize,
            borderWidth: intensity > 0.5 ? 4 : 2,
            shadow: {
                enabled: intensity > 0.3,
                color: `rgba(${this.hexToRgb(activationColor.background).join(',')}, 0.4)`,
                size: Math.round(10 * intensity),
                x: 3,
                y: 3
            }
        };
    }

    /**
     * Update activation edges
     */
    updateActivationEdges(relationships) {
        const newEdges = [];
        const existingEdgeIds = new Set(this.edges.getIds());

        relationships.forEach(rel => {
            const edgeId = `${rel.source}_${rel.target}_${rel.type}`;
            
            if (!existingEdgeIds.has(edgeId)) {
                const edge = this.transformEdgeForVis(rel);
                
                // Add activation animation properties
                edge.color = {
                    ...edge.color,
                    opacity: 0.8
                };
                
                // Make edges wider during activation
                edge.width = Math.max(edge.width || 2, 3);
                
                newEdges.push(edge);
            }
        });

        if (newEdges.length > 0) {
            this.edges.add(newEdges);
        }
    }

    /**
     * Trigger activation wave animation
     */
    triggerActivationWave(activatedNodes) {
        this.activationState.currentWave++;
        
        // Create ripple effect from most activated nodes
        const sortedNodes = activatedNodes
            .sort((a, b) => (b.activation_strength || 0) - (a.activation_strength || 0))
            .slice(0, 3); // Top 3 most activated
            
        sortedNodes.forEach((node, index) => {
            setTimeout(() => {
                this.createActivationRipple(node.name || node.id);
            }, index * 200); // Stagger the ripples
        });
    }

    /**
     * Create ripple animation for a node
     */
    createActivationRipple(nodeId) {
        try {
            const position = this.network.getPositions(nodeId)[nodeId];
            if (!position) return;

            const canvas = this.network.getCanvas();
            const ctx = canvas.getContext('2d');
            const canvasPosition = this.network.canvasToDOM(position);
            
            // Create ripple effect
            let radius = 0;
            const maxRadius = 100;
            const rippleInterval = setInterval(() => {
                radius += 5;
                
                if (radius <= maxRadius) {
                    // Draw ripple
                    ctx.beginPath();
                    ctx.arc(canvasPosition.x, canvasPosition.y, radius, 0, 2 * Math.PI);
                    ctx.strokeStyle = `rgba(33, 133, 208, ${1 - (radius / maxRadius)})`;
                    ctx.lineWidth = 2;
                    ctx.stroke();
                } else {
                    clearInterval(rippleInterval);
                }
            }, 50);
        } catch (error) {
            console.warn('Failed to create ripple animation:', error);
        }
    }

    /**
     * Trigger stage-specific animation
     */
    triggerStageAnimation(stage) {
        switch (stage) {
            case 'concept-extraction':
                this.animateConceptExtraction();
                break;
            case 'spreading-activation':
                this.animateSpreadingActivation();
                break;
            case 'agent-discovery':
                this.animateAgentDiscovery();
                break;
            case 'processing-rounds':
                this.animateProcessingRounds();
                break;
            case 'response-generation':
                this.animateResponseGeneration();
                break;
        }
    }

    /**
     * Start activation animation loop
     */
    startActivationAnimation() {
        if (this.activationState.isAnimating) return;
        
        this.activationState.isAnimating = true;
        
        const animate = () => {
            if (!this.activationState.isAnimating) return;
            
            // Update node pulsing animation
            this.updateNodePulsing();
            
            // Schedule next frame
            this.animationFrame = requestAnimationFrame(animate);
        };
        
        animate();
        
        // Set up pulse timer
        this.activationTimer = setInterval(() => {
            this.pulseActivatedNodes();
        }, this.options.pulseInterval);
    }

    /**
     * Stop activation animation
     */
    stopActivationAnimation() {
        this.activationState.isAnimating = false;
        
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
            this.animationFrame = null;
        }
        
        if (this.activationTimer) {
            clearInterval(this.activationTimer);
            this.activationTimer = null;
        }
    }

    /**
     * Update node pulsing animation
     */
    updateNodePulsing() {
        const time = Date.now();
        const nodeUpdates = [];
        
        this.activationState.activationLevels.forEach((activation, nodeId) => {
            if (activation > this.options.threshold) {
                const pulsePhase = (time / 1000) % (2 * Math.PI);
                const pulseFactor = 1 + (Math.sin(pulsePhase) * 0.2 * activation);
                
                const node = this.nodes.get(nodeId);
                if (node) {
                    nodeUpdates.push({
                        id: nodeId,
                        size: Math.round((node.originalSize || 20) * pulseFactor)
                    });
                }
            }
        });
        
        if (nodeUpdates.length > 0) {
            this.nodes.update(nodeUpdates);
        }
    }

    /**
     * Pulse activated nodes
     */
    pulseActivatedNodes() {
        const activatedNodes = [];
        
        this.activationState.activationLevels.forEach((activation, nodeId) => {
            if (activation > this.options.threshold) {
                activatedNodes.push(nodeId);
            }
        });
        
        if (activatedNodes.length > 0) {
            // Highlight activated nodes briefly
            this.network.selectNodes(activatedNodes);
            setTimeout(() => {
                this.network.unselectAll();
            }, 200);
        }
    }

    /**
     * Set visualization speed
     */
    setSpeed(speed) {
        this.options.speed = Math.max(0.1, Math.min(5.0, speed));
        this.options.animationDuration = Math.round(2000 / this.options.speed);
        this.options.pulseInterval = Math.round(500 / this.options.speed);
        
        // Restart timer with new interval
        if (this.activationTimer) {
            clearInterval(this.activationTimer);
            this.activationTimer = setInterval(() => {
                this.pulseActivatedNodes();
            }, this.options.pulseInterval);
        }
    }

    /**
     * Set activation threshold
     */
    setThreshold(threshold) {
        this.options.threshold = Math.max(0.0, Math.min(1.0, threshold));
        
        // Update node visibility based on new threshold
        this.updateNodeVisibilityByThreshold();
    }

    /**
     * Set visualization mode
     */
    setMode(mode) {
        this.options.mode = mode;
        
        if (mode === 'replay') {
            this.stopActivationAnimation();
            this.setupReplayMode();
        } else {
            this.setupLiveMode();
        }
    }

    /**
     * Setup replay mode for reviewing activation history
     */
    setupReplayMode() {
        // Implementation for replaying stored activation patterns
        console.log('Replay mode activated');
    }

    /**
     * Setup live mode for real-time visualization
     */
    setupLiveMode() {
        this.startActivationAnimation();
        console.log('Live mode activated');
    }

    /**
     * Update node visibility based on threshold
     */
    updateNodeVisibilityByThreshold() {
        const nodeUpdates = [];
        
        this.activationState.activationLevels.forEach((activation, nodeId) => {
            const node = this.nodes.get(nodeId);
            if (node) {
                const opacity = activation >= this.options.threshold ? 1.0 : 0.3;
                nodeUpdates.push({
                    id: nodeId,
                    opacity: opacity
                });
            }
        });
        
        if (nodeUpdates.length > 0) {
            this.nodes.update(nodeUpdates);
        }
    }

    // Stage-specific animation methods
    animateConceptExtraction() {
        console.log('Animating concept extraction stage');
    }

    animateSpreadingActivation() {
        console.log('Animating spreading activation stage');
    }

    animateAgentDiscovery() {
        console.log('Animating agent discovery stage');
    }

    animateProcessingRounds() {
        console.log('Animating processing rounds stage');
    }

    animateResponseGeneration() {
        console.log('Animating response generation stage');
    }

    // Utility methods
    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? [
            parseInt(result[1], 16),
            parseInt(result[2], 16),
            parseInt(result[3], 16)
        ] : [0, 0, 0];
    }

    lightenColor(color, percent) {
        const rgb = this.hexToRgb(color);
        const factor = percent / 100;
        const newRgb = rgb.map(c => Math.min(255, Math.round(c + (255 - c) * factor)));
        return `rgb(${newRgb.join(',')})`;
    }

    /**
     * Handle session events
     */
    handleSessionStarted(event) {
        this.startActivationSession(event.sessionId, event.initialConcepts);
    }

    handleSessionCompleted(event) {
        this.stopActivationAnimation();
        console.log('Session completed:', event);
    }

    /**
     * Clean up when destroying the visualizer
     */
    destroy() {
        this.stopActivationAnimation();
        
        if (this.websocket) {
            this.websocket.close();
        }
        
        if (window.Echo) {
            window.Echo.leave('soul.activation');
        }
        
        super.destroy();
    }
}

// Export for use in other modules
export default ActivationVisualizer;

// Make available globally
window.ActivationVisualizer = ActivationVisualizer;