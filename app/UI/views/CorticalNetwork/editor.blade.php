<x-layout::index>
    @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/vis-network@9.1.2/dist/vis-network.min.js"></script>
    @endpush

    <style>
        .cortical-editor-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .editor-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .editor-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .editor-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .editor-content {
            flex: 1;
            display: flex;
            min-height: 0;
            background: #f5f7fa;
        }

        .sidebar {
            width: 380px;
            background: white;
            border-right: 1px solid #e1e8ed;
            overflow-y: auto;
            flex-shrink: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        .graph-container {
            flex: 1;
            position: relative;
            overflow: hidden;
            min-height: 0;
        }

        #cortical-visualization {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background: #fafbfc;
        }

        .sidebar-section {
            border-bottom: 1px solid #e1e8ed;
            padding: 1.25rem;
        }

        .sidebar-section h3 {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-section h3::before {
            content: '';
            width: 3px;
            height: 1.25rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .stats-panel {
            background: linear-gradient(135deg, #e0e7ff 0%, #e9d5ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-weight: 500;
            color: #4c51bf;
        }

        .stat-value {
            font-weight: 600;
            color: #5a67d8;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-block {
            width: 100%;
        }

        .layer-config-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .legend {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .legend-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .legend-color.layer-4 {
            background: #3b82f6;
        }

        .legend-color.layer-23 {
            background: #10b981;
        }

        .legend-color.layer-5 {
            background: #ef4444;
        }

        .legend-color.column {
            background: #8b5cf6;
        }

        .legend-label {
            font-size: 0.8125rem;
            color: #4a5568;
        }

        .context-menu {
            position: absolute;
            background: white;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            min-width: 160px;
            padding: 0.25rem 0;
        }

        .context-menu-item {
            padding: 0.625rem 1rem;
            cursor: pointer;
            transition: background 0.15s;
            font-size: 0.875rem;
            color: #2d3748;
        }

        .context-menu-item:hover {
            background: #f7fafc;
        }

        .context-menu-item.danger {
            color: #e53e3e;
        }

        .context-menu-item.danger:hover {
            background: #fed7d7;
        }
    </style>

    <div class="cortical-editor-container">
        <div class="editor-header">
            <h1>Cortical Network Editor</h1>
            <p>Create and manage cortical columns, neurons, and connections</p>
        </div>

        <div class="editor-content">
            <div class="sidebar">
                <div class="sidebar-section">
                    <h3>Network Statistics</h3>
                    <div class="stats-panel" id="stats-panel" hx-get="/cortical-network/stats" hx-trigger="load, reload-cortical-network from:body" hx-swap="innerHTML">
                        <div class="stat-row">
                            <span class="stat-label">Loading...</span>
                            <span class="stat-value">-</span>
                        </div>
                    </div>
                </div>

                @include('CorticalNetwork.partials.create-column')
                @include('CorticalNetwork.partials.create-neuron')
                @include('CorticalNetwork.partials.create-connection')

                <div class="sidebar-section">
                    <div class="legend">
                        <div class="legend-title">Legend</div>
                        <div class="legend-item">
                            <div class="legend-color column"></div>
                            <span class="legend-label">Cortical Column</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color layer-4"></div>
                            <span class="legend-label">Layer 4 (Input)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color layer-23"></div>
                            <span class="legend-label">Layers 2/3 (Processing)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color layer-5"></div>
                            <span class="legend-label">Layer 5 (Output/Cardinal)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="graph-container">
                <div id="cortical-visualization"></div>
                <div class="context-menu" id="context-menu">
                    <div class="context-menu-item" id="menu-delete">Delete Node</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let network = null;
        let selectedFromNeuron = null;
        let selectedToNeuron = null;

        function initNetwork() {
            const container = document.getElementById('cortical-visualization');

            fetch('/cortical-network/data')
                .then(response => response.json())
                .then(data => {
                    const nodes = new vis.DataSet(data.nodes.map(node => ({
                        id: node.id,
                        label: node.label,
                        group: node.group,
                        title: `Type: ${node.type}\nLayer: ${node.layer || 'N/A'}\nActivation: ${node.activation_level || 'N/A'}`,
                        color: getNodeColor(node),
                        shape: node.type === 'column' ? 'box' : 'dot',
                        size: node.type === 'column' ? 30 : 20,
                        font: {
                            color: '#2d3748',
                            size: 14,
                            face: 'Inter, system-ui, sans-serif'
                        }
                    })));

                    const edges = new vis.DataSet(data.edges.map(edge => ({
                        id: edge.id,
                        from: edge.from,
                        to: edge.to,
                        label: edge.label,
                        arrows: edge.arrows || 'to',
                        color: getEdgeColor(edge.type),
                        width: edge.type === 'HAS_NEURON' ? 1 : 2,
                        dashes: edge.type === 'HAS_NEURON',
                        smooth: { type: 'cubicBezier' }
                    })));

                    const options = {
                        nodes: {
                            borderWidth: 2,
                            borderWidthSelected: 3
                        },
                        edges: {
                            smooth: true
                        },
                        physics: {
                            enabled: true,
                            barnesHut: {
                                gravitationalConstant: -8000,
                                centralGravity: 0.3,
                                springLength: 150,
                                springConstant: 0.04
                            }
                        },
                        interaction: {
                            hover: true
                        }
                    };

                    network = new vis.Network(container, { nodes, edges }, options);

                    network.on('click', handleNodeClick);
                    network.on('oncontext', handleRightClick);
                })
                .catch(err => console.error('Failed to load network:', err));
        }

        function getNodeColor(node) {
            if (node.type === 'column') return { background: '#8b5cf6', border: '#7c3aed' };
            if (node.layer === 4) return { background: '#3b82f6', border: '#2563eb' };
            if (node.layer === 23) return { background: '#10b981', border: '#059669' };
            if (node.layer === 5) return { background: '#ef4444', border: '#dc2626' };
            return { background: '#6b7280', border: '#4b5563' };
        }

        function getEdgeColor(type) {
            if (type === 'HAS_NEURON') return { color: '#cbd5e0', highlight: '#a0aec0' };
            if (type === 'ACTIVATES') return { color: '#10b981', highlight: '#059669' };
            if (type === 'INHIBITS') return { color: '#ef4444', highlight: '#dc2626' };
            return { color: '#6b7280', highlight: '#4b5563' };
        }

        function handleNodeClick(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = network.body.data.nodes.get(nodeId);

                if (node && node.id.startsWith('neuron_')) {
                    if (!selectedFromNeuron) {
                        selectedFromNeuron = node.id.replace('neuron_', '');
                        document.getElementById('from_neuron_id').value = selectedFromNeuron;
                        alert('From neuron selected. Click another neuron to create connection.');
                    } else if (!selectedToNeuron) {
                        selectedToNeuron = node.id.replace('neuron_', '');
                        document.getElementById('to_neuron_id').value = selectedToNeuron;
                        alert('To neuron selected. You can now submit the connection form.');
                        selectedFromNeuron = null;
                        selectedToNeuron = null;
                    }
                }
            }
        }

        function handleRightClick(params) {
            params.event.preventDefault();

            if (params.nodes.length > 0) {
                const contextMenu = document.getElementById('context-menu');
                contextMenu.style.display = 'block';
                contextMenu.style.left = params.pointer.DOM.x + 'px';
                contextMenu.style.top = params.pointer.DOM.y + 'px';

                const nodeId = params.nodes[0];
                document.getElementById('menu-delete').onclick = () => deleteNode(nodeId);
            }
        }

        function deleteNode(nodeId) {
            if (confirm('Are you sure you want to delete this node?')) {
                const endpoint = nodeId.startsWith('col_')
                    ? `/cortical-network/column/${nodeId.replace('col_', '')}`
                    : `/cortical-network/neuron/${nodeId.replace('neuron_', '')}`;

                fetch(endpoint, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        initNetwork();
                    }
                });
            }

            document.getElementById('context-menu').style.display = 'none';
        }

        document.addEventListener('click', () => {
            document.getElementById('context-menu').style.display = 'none';
        });

        document.body.addEventListener('reload-cortical-network', () => {
            initNetwork();
        });

        initNetwork();
    </script>
</x-layout::index>