<div class="ui grid">
    <!-- Network Controls -->
    <div class="four wide column">
        <div class="ui segment">
            <h4 class="ui header">
                <i class="settings icon"></i>
                Network Controls
            </h4>

            <!-- Load Fact Form -->
            <form class="ui form" id="load-fact-form">
                <div class="field">
                    <label>Load Fact by ID</label>
                    <div class="ui action input">
                        <input type="text" name="fact_id" placeholder="Enter fact ID..." id="fact-id-input">
                        <button type="submit" class="ui primary button">Load</button>
                    </div>
                </div>
            </form>

            <div class="ui divider"></div>

            <!-- Network Settings -->
            <div class="field">
                <label>Network Depth</label>
                <div class="ui selection dropdown" id="network-depth">
                    <input type="hidden" name="depth" value="2">
                    <i class="dropdown icon"></i>
                    <div class="default text">Select depth</div>
                    <div class="menu">
                        <div class="item" data-value="1">1 level</div>
                        <div class="item" data-value="2">2 levels</div>
                        <div class="item" data-value="3">3 levels</div>
                        <div class="item" data-value="4">4 levels</div>
                        <div class="item" data-value="5">5 levels</div>
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="ui toggle checkbox" id="show-labels-toggle">
                    <input type="checkbox" name="show_labels" checked>
                    <label>Show Role Labels</label>
                </div>
            </div>

            <div class="field">
                <div class="ui toggle checkbox" id="physics-toggle">
                    <input type="checkbox" name="physics" checked>
                    <label>Enable Physics</label>
                </div>
            </div>

            <div class="field">
                <div class="ui toggle checkbox" id="highlight-triplets-toggle">
                    <input type="checkbox" name="highlight_triplets" checked>
                    <label>Highlight Triplets</label>
                </div>
            </div>

            <div class="ui divider"></div>

            <!-- Quick Actions -->
            <div class="ui vertical fluid menu">
                <div class="header item">Quick Actions</div>
                <a class="item" onclick="loadRandomFact()">
                    <i class="random icon"></i>
                    Load Random Fact
                </a>
                <a class="item" onclick="fitNetworkToScreen()">
                    <i class="expand arrows alternate icon"></i>
                    Fit to Screen
                </a>
                <a class="item" onclick="clearNetwork()">
                    <i class="eraser icon"></i>
                    Clear Network
                </a>
                <a class="item" onclick="exportNetwork()">
                    <i class="download icon"></i>
                    Export as Image
                </a>
            </div>

            <div class="ui divider"></div>

            <!-- Network Statistics -->
            <div class="ui segment" id="network-stats">
                <h5 class="ui header">Network Statistics</h5>
                <div class="ui mini statistics">
                    <div class="statistic">
                        <div class="value" id="node-count">0</div>
                        <div class="label">Nodes</div>
                    </div>
                    <div class="statistic">
                        <div class="value" id="edge-count">0</div>
                        <div class="label">Edges</div>
                    </div>
                </div>
                <div class="ui tiny statistics">
                    <div class="statistic">
                        <div class="value" id="fact-count">0</div>
                        <div class="label">Facts</div>
                    </div>
                    <div class="statistic">
                        <div class="value" id="concept-count">0</div>
                        <div class="label">Concepts</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="ui segment">
            <h4 class="ui header">
                <i class="info circle icon"></i>
                Legend
            </h4>

            <div class="ui relaxed list">
                <div class="item">
                    <div class="ui green horizontal label">Fact Node</div>
                    <div class="content">
                        <div class="description">Verified facts</div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui blue horizontal label">Fact Node</div>
                    <div class="content">
                        <div class="description">Unverified facts</div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui red horizontal label">Concept</div>
                    <div class="content">
                        <div class="description">Primitive concepts</div>
                    </div>
                </div>
                <div class="item">
                    <div class="ui grey horizontal label">Concept</div>
                    <div class="content">
                        <div class="description">Derived concepts</div>
                    </div>
                </div>
            </div>

            <div class="ui divider"></div>

            <h5 class="ui header">Relationship Types</h5>
            <div class="ui relaxed list">
                <div class="item">
                    <div style="width: 20px; height: 3px; background-color: #e74c3c; display: inline-block; margin-right: 8px;"></div>
                    Subject
                </div>
                <div class="item">
                    <div style="width: 20px; height: 4px; background-color: #2ecc71; display: inline-block; margin-right: 8px;"></div>
                    Predicate
                </div>
                <div class="item">
                    <div style="width: 20px; height: 3px; background-color: #3498db; display: inline-block; margin-right: 8px;"></div>
                    Object
                </div>
                <div class="item">
                    <div style="width: 20px; height: 2px; background-color: #f39c12; display: inline-block; margin-right: 8px; border-style: dashed;"></div>
                    Modifier
                </div>
            </div>
        </div>
    </div>

    <!-- Network Visualization -->
    <div class="twelve wide column">
        <div class="ui segment" style="padding: 0; height: 700px;">
            <div id="network-container" class="triplet-network-container" style="width: 100%; height: 100%;"></div>
        </div>

        <!-- Selection Info Panel -->
        <div class="ui segment" id="selection-info" style="display: none;">
            <h4 class="ui header">
                <i class="info icon"></i>
                Selection Details
            </h4>
            <div id="selection-content">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Fact Search Modal -->
<div id="fact-search-modal" class="ui modal">
    <i class="close icon"></i>
    <div class="header">
        <i class="search icon"></i>
        Search Facts
    </div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>Search Facts</label>
                <div class="ui search">
                    <div class="ui icon input">
                        <input class="prompt" type="text" placeholder="Search facts by statement or concepts...">
                        <i class="search icon"></i>
                    </div>
                    <div class="results"></div>
                </div>
            </div>
        </div>
        <div class="ui divider"></div>
        <div id="search-results">
            <!-- Search results loaded here -->
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Cancel</div>
        <div class="ui primary button" onclick="loadSelectedFacts()">Load Selected</div>
    </div>
</div>

<script>
    let networkVisualization = null;
    let selectedFactsForLoad = new Set();

    // Initialize network when tab becomes active
    function initializeNetwork() {
        if (!networkVisualization) {
            networkVisualization = new TripletNetworkVisualization('network-container', {
                height: '700px',
                physics: true,
                showRoleLabels: true,
                highlightTriplets: true,
                apiBaseUrl: '/facts'
            });

            // Setup event handlers
            networkVisualization.on('selectFact', handleFactSelection);
            networkVisualization.on('selectConcept', handleConceptSelection);
            networkVisualization.on('deselectAll', handleDeselection);
            networkVisualization.on('networkStabilized', updateNetworkStats);

            // Update stats initially
            updateNetworkStats();
        }
    }

    // Initialize on tab activation
    document.querySelector('.menu .item[data-tab="network"]').addEventListener('click', function() {
        setTimeout(initializeNetwork, 100);
    });

    // Initialize dropdowns and checkboxes
    $('#network-depth').dropdown({
        onChange: function(value) {
            // Depth change logic if needed
        }
    });

    $('.ui.checkbox').checkbox();

    // Form handlers
    document.getElementById('load-fact-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const factId = document.getElementById('fact-id-input').value.trim();
        if (factId && networkVisualization) {
            const depth = parseInt($('#network-depth').dropdown('get value')) || 2;
            networkVisualization.loadFactNetwork(factId, depth);
        }
    });

    // Toggle handlers
    document.getElementById('show-labels-toggle').addEventListener('change', function(e) {
        if (networkVisualization) {
            networkVisualization.options.showRoleLabels = e.target.checked;
            networkVisualization.updateEdgeLabels();
        }
    });

    document.getElementById('physics-toggle').addEventListener('change', function(e) {
        if (networkVisualization) {
            networkVisualization.options.physics = e.target.checked;
            networkVisualization.network.setOptions({ physics: { enabled: e.target.checked } });
        }
    });

    document.getElementById('highlight-triplets-toggle').addEventListener('change', function(e) {
        if (networkVisualization) {
            networkVisualization.options.highlightTriplets = e.target.checked;
        }
    });

    // Quick action functions
    function loadRandomFact() {
        fetch('/facts/statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.total_facts > 0) {
                    // This would need an endpoint to get a random fact ID
                    // For now, just show the search modal
                    showFactSearchModal();
                }
            })
            .catch(error => {
                showErrorMessage('Failed to load random fact');
            });
    }

    function fitNetworkToScreen() {
        if (networkVisualization) {
            networkVisualization.network.fit({
                animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
            });
        }
    }

    function clearNetwork() {
        if (networkVisualization) {
            networkVisualization.clear();
            updateNetworkStats();
            hideSelectionInfo();
        }
    }

    function exportNetwork() {
        if (networkVisualization) {
            networkVisualization.exportAsImage('png');
        }
    }

    function showFactSearchModal() {
        $('#fact-search-modal').modal('show');
    }

    function loadSelectedFacts() {
        if (selectedFactsForLoad.size > 0 && networkVisualization) {
            networkVisualization.loadMultipleFacts(Array.from(selectedFactsForLoad));
            $('#fact-search-modal').modal('hide');
            selectedFactsForLoad.clear();
        }
    }

    // Event handlers
    function handleFactSelection(fact, factId) {
        showSelectionInfo('fact', fact, factId);
    }

    function handleConceptSelection(concept, conceptId) {
        showSelectionInfo('concept', concept, conceptId);
    }

    function handleDeselection() {
        hideSelectionInfo();
    }

    function showSelectionInfo(type, data, id) {
        const panel = document.getElementById('selection-info');
        const content = document.getElementById('selection-content');
        
        if (type === 'fact') {
            content.innerHTML = `
                <div class="ui grid">
                    <div class="eight wide column">
                        <h5 class="ui header">Fact Details</h5>
                        <div class="ui list">
                            <div class="item">
                                <div class="content">
                                    <div class="header">Statement</div>
                                    <div class="description">${data.statement}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="content">
                                    <div class="header">Confidence</div>
                                    <div class="description">${(data.confidence || 1.0).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="content">
                                    <div class="header">Verified</div>
                                    <div class="description">${data.verified ? 'Yes' : 'No'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="eight wide column">
                        <h5 class="ui header">Actions</h5>
                        <div class="ui vertical buttons">
                            <button class="ui button" onclick="showFactDetails('${id}')">
                                <i class="eye icon"></i>
                                View Details
                            </button>
                            <button class="ui button" onclick="expandFactNetwork('${id}')">
                                <i class="sitemap icon"></i>
                                Expand Network
                            </button>
                            <button class="ui button" onclick="window.open('/facts/${id}', '_blank')">
                                <i class="external alternate icon"></i>
                                Open in New Tab
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="ui grid">
                    <div class="eight wide column">
                        <h5 class="ui header">Concept Details</h5>
                        <div class="ui list">
                            <div class="item">
                                <div class="content">
                                    <div class="header">Name</div>
                                    <div class="description">${data.name}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="content">
                                    <div class="header">Type</div>
                                    <div class="description">${data.is_primitive ? 'Primitive' : 'Derived'}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="content">
                                    <div class="header">Usage</div>
                                    <div class="description">${data.fact_frequency || 0} facts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="eight wide column">
                        <h5 class="ui header">Actions</h5>
                        <div class="ui vertical buttons">
                            <button class="ui button" onclick="showConceptFacts('${id}')">
                                <i class="list icon"></i>
                                Show Related Facts
                            </button>
                            <button class="ui button" onclick="focusOnConcept('${id}')">
                                <i class="crosshairs icon"></i>
                                Focus on Concept
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        panel.style.display = 'block';
    }

    function hideSelectionInfo() {
        document.getElementById('selection-info').style.display = 'none';
    }

    function updateNetworkStats() {
        if (networkVisualization) {
            const stats = networkVisualization.getNetworkStatistics();
            document.getElementById('node-count').textContent = stats.total_nodes;
            document.getElementById('edge-count').textContent = stats.total_edges;
            document.getElementById('fact-count').textContent = stats.fact_nodes;
            document.getElementById('concept-count').textContent = stats.concept_nodes;
        }
    }

    function expandFactNetwork(factId) {
        if (networkVisualization) {
            networkVisualization.expandFactNetwork(factId, 1);
        }
    }

    function focusOnConcept(conceptId) {
        if (networkVisualization) {
            networkVisualization.focusOnConcept(conceptId);
        }
    }

    function showConceptFacts(conceptId) {
        // This would need an API endpoint to get facts by concept
        console.log('Show facts for concept:', conceptId);
    }
</script>