<div class="dependency-graph-container" x-data="dependencyGraph()">
    <!-- Graph Controls -->
    <div class="ui secondary menu">
        <div class="item">
            <div class="ui form">
                <div class="inline fields">
                    <div class="field">
                        <label>Layout:</label>
                        <select x-model="layoutType" @change="updateLayout()">
                            <option value="hierarchical">Hierarchical</option>
                            <option value="force">Force-Directed</option>
                            <option value="circular">Circular</option>
                            <option value="radial">Radial</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Filter:</label>
                        <select x-model="filterType" @change="applyFilter()">
                            <option value="all">All Concepts</option>
                            <option value="frames">Frames Only</option>
                            <option value="image_schema">Image Schemas</option>
                            <option value="primitives">Primitives</option>
                            <option value="agents">With Agents</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>File Group:</label>
                        <select x-model="selectedFileGroup" @change="filterByFileGroup()">
                            <option value="">All Files</option>
                            <template x-for="group in fileGroups" :key="group.name">
                                <option :value="group.name" x-text="group.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right menu">
            <div class="item">
                <button class="ui button" @click="centerGraph()">
                    <i class="expand arrows alternate icon"></i>
                    Center
                </button>
            </div>
            <div class="item">
                <button class="ui button" @click="exportGraph()">
                    <i class="download icon"></i>
                    Export
                </button>
            </div>
            <div class="item">
                <button class="ui button" @click="refreshGraph()">
                    <i class="refresh icon"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Graph Visualization -->
    <div class="graph-container">
        <div id="dependency-graph" x-ref="graphContainer" class="graph-canvas"></div>
        
        <!-- Graph Legend -->
        <div class="graph-legend">
            <div class="ui compact menu vertical">
                <div class="header item">Legend</div>
                <div class="item">
                    <div class="ui horizontal list">
                        <div class="item">
                            <div class="node-sample concept"></div>
                            <div class="content">Concepts</div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui horizontal list">
                        <div class="item">
                            <div class="node-sample frame"></div>
                            <div class="content">Frames</div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui horizontal list">
                        <div class="item">
                            <div class="node-sample image-schema"></div>
                            <div class="content">Image Schemas</div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui horizontal list">
                        <div class="item">
                            <div class="node-sample agent"></div>
                            <div class="content">Procedural Agents</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div x-show="loading" class="ui active dimmer">
            <div class="ui text loader">Loading dependency graph...</div>
        </div>
    </div>

    <!-- Graph Statistics -->
    <div class="ui statistics">
        <div class="statistic">
            <div class="value" x-text="graphData.nodes?.length || 0"></div>
            <div class="label">Nodes</div>
        </div>
        <div class="statistic">
            <div class="value" x-text="graphData.links?.length || 0"></div>
            <div class="label">Relationships</div>
        </div>
        <div class="statistic">
            <div class="value" x-text="fileGroups.length || 0"></div>
            <div class="label">File Groups</div>
        </div>
        <div class="statistic" :class="{ 'red': circularDependencies.length > 0 }">
            <div class="value" x-text="circularDependencies.length || 0"></div>
            <div class="label">Circular Dependencies</div>
        </div>
    </div>

    <!-- Circular Dependencies Warning -->
    <div x-show="circularDependencies.length > 0" class="ui warning message">
        <div class="header">
            <i class="warning sign icon"></i>
            Circular Dependencies Detected
        </div>
        <div class="content">
            <p>The following circular dependencies were found in your knowledge graph:</p>
            <ul>
                <template x-for="cycle in circularDependencies" :key="cycle.join('→')">
                    <li>
                        <code x-text="cycle.join(' → ')"></code>
                    </li>
                </template>
            </ul>
            <p>Circular dependencies can cause infinite loops in spreading activation. Consider restructuring your relationships.</p>
        </div>
    </div>

    <!-- Node Details Panel -->
    <div x-show="selectedNode" class="ui segment" id="node-details">
        <div class="ui header">
            <i class="info circle icon"></i>
            Node Details
            <div class="sub header" x-text="selectedNode?.name"></div>
        </div>
        
        <div x-show="selectedNode" class="ui list">
            <div class="item">
                <strong>Type:</strong>
                <span x-text="selectedNode?.type"></span>
            </div>
            <div class="item">
                <strong>Domain:</strong>
                <span x-text="selectedNode?.domain"></span>
            </div>
            <div class="item">
                <strong>File:</strong>
                <a href="#" @click="loadFile(selectedNode?.file)" x-text="selectedNode?.file"></a>
            </div>
            <div class="item" x-show="selectedNode?.description">
                <strong>Description:</strong>
                <p x-text="selectedNode?.description"></p>
            </div>
            <div class="item" x-show="selectedNode?.labels?.length">
                <strong>Labels:</strong>
                <div class="ui labels">
                    <template x-for="label in selectedNode?.labels || []" :key="label">
                        <div class="ui label" x-text="label"></div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Connected Nodes -->
        <div x-show="connectedNodes.length > 0">
            <h5>Connected Nodes</h5>
            <div class="ui divided list">
                <template x-for="connection in connectedNodes" :key="connection.id">
                    <div class="item">
                        <div class="content">
                            <div class="header" x-text="connection.name"></div>
                            <div class="description">
                                <span x-text="connection.relationshipType"></span>
                                <div class="ui mini circular label" :class="getRelationshipColor(connection.relationshipType)">
                                    <span x-text="connection.direction"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="ui divider"></div>
        <button class="ui button" @click="selectedNode = null">
            <i class="close icon"></i>
            Close
        </button>
    </div>
</div>

<style>
.dependency-graph-container {
    position: relative;
    height: 100%;
}

.graph-container {
    position: relative;
    height: 70vh;
    border: 1px solid #ddd;
    background: #fafafa;
    overflow: hidden;
}

.graph-canvas {
    width: 100%;
    height: 100%;
    position: relative;
}

.graph-legend {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
    padding: 10px;
}

.node-sample {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.node-sample.concept {
    background: #2185d0;
}

.node-sample.frame {
    background: #21ba45;
}

.node-sample.image-schema {
    background: #f2711c;
}

.node-sample.agent {
    background: #a333c8;
}

#node-details {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 300px;
    max-height: 60vh;
    overflow-y: auto;
    z-index: 20;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.graph-controls {
    margin-bottom: 1rem;
}

/* Graph node styles */
.graph-node {
    cursor: pointer;
    stroke-width: 2px;
    fill: #2185d0;
    transition: all 0.3s ease;
}

.graph-node:hover {
    stroke-width: 3px;
    filter: brightness(1.2);
}

.graph-node.selected {
    stroke: #ff6b35;
    stroke-width: 3px;
}

.graph-node.concept {
    fill: #2185d0;
}

.graph-node.frame {
    fill: #21ba45;
}

.graph-node.image_schema {
    fill: #f2711c;
}

.graph-node.agent {
    fill: #a333c8;
}

.graph-link {
    stroke: #999;
    stroke-opacity: 0.8;
    stroke-width: 2px;
    fill: none;
    marker-end: url(#arrowhead);
}

.graph-link.is_a {
    stroke: #2185d0;
    stroke-dasharray: none;
}

.graph-link.part_of {
    stroke: #21ba45;
    stroke-dasharray: 5,5;
}

.graph-link.causes {
    stroke: #e03997;
    stroke-dasharray: 10,5;
}

.graph-link.activates {
    stroke: #f2711c;
    stroke-dasharray: 2,2;
}

.graph-text {
    font-family: 'Lato', 'Helvetica Neue', Arial, Helvetica, sans-serif;
    font-size: 12px;
    text-anchor: middle;
    fill: #333;
    pointer-events: none;
}

.graph-text.selected {
    font-weight: bold;
    fill: #ff6b35;
}

/* Circular dependency highlighting */
.circular-dependency {
    stroke: #db2828 !important;
    stroke-width: 3px !important;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { stroke-opacity: 1; }
    50% { stroke-opacity: 0.5; }
    100% { stroke-opacity: 1; }
}
</style>

<script>
function dependencyGraph() {
    return {
        // State
        graphData: { nodes: [], links: [] },
        fileGroups: [],
        circularDependencies: [],
        selectedNode: null,
        connectedNodes: [],
        loading: false,
        
        // Visualization settings
        layoutType: 'hierarchical',
        filterType: 'all',
        selectedFileGroup: '',
        
        // D3/Vis.js instance
        network: null,
        
        init() {
            this.loadDependencyGraph();
        },
        
        async loadDependencyGraph() {
            this.loading = true;
            
            try {
                const response = await fetch('/soul/knowledge/dependency-graph');
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load dependency graph');
                }
                
                this.graphData = data;
                this.fileGroups = Object.values(data.fileGroups || {});
                this.circularDependencies = data.circularDependencies || [];
                
                this.initializeGraph();
                
            } catch (error) {
                console.error('Failed to load dependency graph:', error);
                alert('Failed to load dependency graph: ' + error.message);
            } finally {
                this.loading = false;
            }
        },
        
        initializeGraph() {
            // Initialize the graph visualization
            // This is a placeholder - you would integrate with D3.js, Vis.js, or another graph library
            this.$nextTick(() => {
                this.renderGraph();
            });
        },
        
        renderGraph() {
            const container = this.$refs.graphContainer;
            if (!container) return;
            
            // Clear previous graph
            container.innerHTML = '';
            
            // Create SVG
            const svg = d3.select(container)
                .append('svg')
                .attr('width', '100%')
                .attr('height', '100%');
            
            // Define arrowhead marker
            svg.append('defs').append('marker')
                .attr('id', 'arrowhead')
                .attr('viewBox', '0 -5 10 10')
                .attr('refX', 15)
                .attr('refY', -1.5)
                .attr('markerWidth', 6)
                .attr('markerHeight', 6)
                .attr('orient', 'auto')
                .append('path')
                .attr('d', 'M0,-5L10,0L0,5')
                .attr('fill', '#999');
            
            this.setupForceSimulation(svg);
        },
        
        setupForceSimulation(svg) {
            const width = this.$refs.graphContainer.clientWidth;
            const height = this.$refs.graphContainer.clientHeight;
            
            // Create force simulation
            const simulation = d3.forceSimulation(this.graphData.nodes)
                .force('link', d3.forceLink(this.graphData.links).id(d => d.id).distance(100))
                .force('charge', d3.forceManyBody().strength(-300))
                .force('center', d3.forceCenter(width / 2, height / 2));
            
            // Create links
            const link = svg.append('g')
                .selectAll('line')
                .data(this.graphData.links)
                .enter().append('line')
                .attr('class', d => `graph-link ${d.type.toLowerCase()}`)
                .attr('stroke-width', d => Math.sqrt(d.strength || 1) * 2);
            
            // Create nodes
            const node = svg.append('g')
                .selectAll('circle')
                .data(this.graphData.nodes)
                .enter().append('circle')
                .attr('class', d => `graph-node ${d.type}`)
                .attr('r', d => this.getNodeSize(d))
                .call(this.drag(simulation))
                .on('click', (event, d) => {
                    this.selectNode(d);
                })
                .on('dblclick', (event, d) => {
                    this.loadFile(d.file);
                });
            
            // Add labels
            const text = svg.append('g')
                .selectAll('text')
                .data(this.graphData.nodes)
                .enter().append('text')
                .attr('class', 'graph-text')
                .attr('dy', '.35em')
                .text(d => d.name);
            
            // Update positions on tick
            simulation.on('tick', () => {
                link
                    .attr('x1', d => d.source.x)
                    .attr('y1', d => d.source.y)
                    .attr('x2', d => d.target.x)
                    .attr('y2', d => d.target.y);
                
                node
                    .attr('cx', d => d.x)
                    .attr('cy', d => d.y);
                
                text
                    .attr('x', d => d.x)
                    .attr('y', d => d.y);
            });
            
            this.network = { simulation, svg };
        },
        
        getNodeSize(node) {
            const baseSize = 8;
            if (node.type === 'frame') return baseSize + 4;
            if (node.type === 'image_schema') return baseSize + 2;
            if (node.type === 'agent') return baseSize + 6;
            return baseSize;
        },
        
        selectNode(node) {
            this.selectedNode = node;
            this.connectedNodes = this.getConnectedNodes(node);
            
            // Update visual selection
            this.updateNodeSelection(node);
        },
        
        getConnectedNodes(node) {
            const connected = [];
            
            this.graphData.links.forEach(link => {
                if (link.source === node.id || link.source.id === node.id) {
                    const target = typeof link.target === 'object' ? link.target : 
                                  this.graphData.nodes.find(n => n.id === link.target);
                    if (target) {
                        connected.push({
                            ...target,
                            relationshipType: link.type,
                            direction: 'outgoing'
                        });
                    }
                }
                
                if (link.target === node.id || link.target.id === node.id) {
                    const source = typeof link.source === 'object' ? link.source : 
                                  this.graphData.nodes.find(n => n.id === link.source);
                    if (source) {
                        connected.push({
                            ...source,
                            relationshipType: link.type,
                            direction: 'incoming'
                        });
                    }
                }
            });
            
            return connected;
        },
        
        updateNodeSelection(selectedNode) {
            if (!this.network) return;
            
            this.network.svg.selectAll('.graph-node')
                .classed('selected', d => d.id === selectedNode.id);
            
            this.network.svg.selectAll('.graph-text')
                .classed('selected', d => d.id === selectedNode.id);
        },
        
        drag(simulation) {
            function dragstarted(event, d) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                d.fx = d.x;
                d.fy = d.y;
            }
            
            function dragged(event, d) {
                d.fx = event.x;
                d.fy = event.y;
            }
            
            function dragended(event, d) {
                if (!event.active) simulation.alphaTarget(0);
                d.fx = null;
                d.fy = null;
            }
            
            return d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended);
        },
        
        // Control methods
        updateLayout() {
            if (!this.network) return;
            
            // Update force simulation based on layout type
            const { simulation } = this.network;
            
            switch (this.layoutType) {
                case 'hierarchical':
                    simulation.force('y', d3.forceY().strength(0.1));
                    break;
                case 'circular':
                    simulation.force('radial', d3.forceRadial(200));
                    break;
                case 'force':
                default:
                    simulation.force('y', null);
                    simulation.force('radial', null);
                    break;
            }
            
            simulation.alpha(1).restart();
        },
        
        applyFilter() {
            // Filter nodes and links based on filterType
            let filteredNodes = this.graphData.nodes;
            let filteredLinks = this.graphData.links;
            
            if (this.filterType !== 'all') {
                switch (this.filterType) {
                    case 'frames':
                        filteredNodes = this.graphData.nodes.filter(n => n.type === 'frame');
                        break;
                    case 'image_schema':
                        filteredNodes = this.graphData.nodes.filter(n => n.type === 'image_schema');
                        break;
                    case 'primitives':
                        filteredNodes = this.graphData.nodes.filter(n => n.primitive === true);
                        break;
                    case 'agents':
                        filteredNodes = this.graphData.nodes.filter(n => n.hasAgents === true);
                        break;
                }
                
                const nodeIds = new Set(filteredNodes.map(n => n.id));
                filteredLinks = this.graphData.links.filter(l => 
                    nodeIds.has(l.source.id || l.source) && nodeIds.has(l.target.id || l.target)
                );
            }
            
            this.renderFilteredGraph(filteredNodes, filteredLinks);
        },
        
        filterByFileGroup() {
            if (!this.selectedFileGroup) {
                this.applyFilter();
                return;
            }
            
            const filteredNodes = this.graphData.nodes.filter(n => 
                n.fileGroup === this.selectedFileGroup
            );
            
            const nodeIds = new Set(filteredNodes.map(n => n.id));
            const filteredLinks = this.graphData.links.filter(l => 
                nodeIds.has(l.source.id || l.source) && nodeIds.has(l.target.id || l.target)
            );
            
            this.renderFilteredGraph(filteredNodes, filteredLinks);
        },
        
        renderFilteredGraph(nodes, links) {
            // Update the current graph data for rendering
            const currentData = { nodes, links };
            
            if (this.network) {
                this.network.simulation.nodes(nodes);
                this.network.simulation.force('link').links(links);
                this.network.simulation.alpha(1).restart();
                
                // Update DOM elements
                this.updateGraphElements(currentData);
            }
        },
        
        updateGraphElements(data) {
            const { svg } = this.network;
            
            // Update links
            const link = svg.select('g').selectAll('line')
                .data(data.links, d => d.source.id + '-' + d.target.id);
            
            link.enter().append('line')
                .attr('class', d => `graph-link ${d.type.toLowerCase()}`)
                .merge(link);
            
            link.exit().remove();
            
            // Update nodes
            const node = svg.select('g').selectAll('circle')
                .data(data.nodes, d => d.id);
            
            node.enter().append('circle')
                .attr('class', d => `graph-node ${d.type}`)
                .attr('r', d => this.getNodeSize(d))
                .merge(node);
            
            node.exit().remove();
            
            // Update labels
            const text = svg.select('g').selectAll('text')
                .data(data.nodes, d => d.id);
            
            text.enter().append('text')
                .attr('class', 'graph-text')
                .text(d => d.name)
                .merge(text);
            
            text.exit().remove();
        },
        
        centerGraph() {
            if (!this.network) return;
            
            const width = this.$refs.graphContainer.clientWidth;
            const height = this.$refs.graphContainer.clientHeight;
            
            this.network.simulation
                .force('center', d3.forceCenter(width / 2, height / 2))
                .alpha(1)
                .restart();
        },
        
        refreshGraph() {
            this.loadDependencyGraph();
        },
        
        exportGraph() {
            // Export graph as SVG or PNG
            const svg = this.network.svg.node();
            const serializer = new XMLSerializer();
            const source = serializer.serializeToString(svg);
            
            const blob = new Blob([source], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = 'soul-dependency-graph.svg';
            a.click();
            
            URL.revokeObjectURL(url);
        },
        
        getRelationshipColor(type) {
            switch (type.toLowerCase()) {
                case 'is_a': return 'blue';
                case 'part_of': return 'green';
                case 'causes': return 'pink';
                case 'activates': return 'orange';
                default: return 'grey';
            }
        },
        
        loadFile(filename) {
            // Delegate to parent component
            this.$dispatch('load-file', { filename });
        }
    };
}
</script>