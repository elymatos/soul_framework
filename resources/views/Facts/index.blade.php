@extends('Layout.main')

@section('title', 'Triplet Fact Network')

@section('content')
<div class="ui container" style="margin-top: 20px;">
    <!-- Header with navigation -->
    <div class="ui grid">
        <div class="twelve wide column">
            <h2 class="ui header">
                <i class="sitemap icon"></i>
                <div class="content">
                    Triplet Fact Network
                    <div class="sub header">Semantic network of facts as triplet relationships</div>
                </div>
            </h2>
        </div>
        <div class="four wide column">
            <div class="ui right floated buttons">
                <a href="/facts/create" class="ui primary button">
                    <i class="plus icon"></i>
                    Create Fact
                </a>
                <div class="ui dropdown button">
                    <i class="dropdown icon"></i>
                    <div class="text">More</div>
                    <div class="menu">
                        <a class="item" href="/facts/export">
                            <i class="download icon"></i>
                            Export Data
                        </a>
                        <a class="item" href="/facts/statistics">
                            <i class="chart bar icon"></i>
                            Statistics
                        </a>
                        <div class="divider"></div>
                        <a class="item" href="/soul/concepts">
                            <i class="cube icon"></i>
                            Manage Concepts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="ui pointing secondary menu">
        <a class="item {{ $active_tab === 'browse' ? 'active' : '' }}" data-tab="browse">
            <i class="list icon"></i>
            Browse Facts
        </a>
        <a class="item {{ $active_tab === 'network' ? 'active' : '' }}" data-tab="network">
            <i class="project diagram icon"></i>
            Network View
        </a>
        <a class="item {{ $active_tab === 'search' ? 'active' : '' }}" data-tab="search">
            <i class="search icon"></i>
            Advanced Search
        </a>
        <a class="item {{ $active_tab === 'concepts' ? 'active' : '' }}" data-tab="concepts">
            <i class="cube icon"></i>
            Concepts
        </a>
    </div>

    <!-- Tab Contents -->
    <div class="ui tab segment {{ $active_tab === 'browse' ? 'active' : '' }}" data-tab="browse">
        @include('Facts.Browse.main')
    </div>

    <div class="ui tab segment {{ $active_tab === 'network' ? 'active' : '' }}" data-tab="network">
        @include('Facts.Network.main')
    </div>

    <div class="ui tab segment {{ $active_tab === 'search' ? 'active' : '' }}" data-tab="search">
        @include('Facts.Search.main')
    </div>

    <div class="ui tab segment {{ $active_tab === 'concepts' ? 'active' : '' }}" data-tab="concepts">
        @include('Facts.Concepts.main')
    </div>
</div>

<!-- Modals -->
<div id="fact-details-modal" class="ui modal">
    <i class="close icon"></i>
    <div class="header">
        Fact Details
    </div>
    <div class="content">
        <!-- Content loaded via HTMX -->
    </div>
    <div class="actions">
        <div class="ui cancel button">Close</div>
        <div class="ui primary button">Edit</div>
    </div>
</div>

<div id="delete-fact-modal" class="ui small modal">
    <div class="header">
        <i class="trash icon"></i>
        Delete Fact
    </div>
    <div class="content">
        <p>Are you sure you want to delete this fact? This action cannot be undone.</p>
        <div class="ui warning message">
            <div class="header">Warning</div>
            <p>Deleting this fact will also remove all its relationships and update concept statistics.</p>
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Cancel</div>
        <div class="ui red approve button">
            <i class="trash icon"></i>
            Delete
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="module">
    import TripletNetworkVisualization from '/scripts/facts/TripletNetworkVisualization.js';
    import ConceptSelector from '/scripts/facts/ConceptSelector.js';

    // Initialize tab behavior
    $('.menu .item').tab();

    // Initialize dropdowns
    $('.ui.dropdown').dropdown();

    // Global variables for components
    window.tripletNetwork = null;
    window.conceptSelectors = {};

    // Initialize network visualization when tab is activated
    $('.menu .item[data-tab="network"]').on('click', function() {
        if (!window.tripletNetwork) {
            setTimeout(() => {
                window.tripletNetwork = new TripletNetworkVisualization('network-container', {
                    height: '600px',
                    physics: true,
                    showRoleLabels: true,
                    highlightTriplets: true
                });

                // Setup event handlers
                window.tripletNetwork.on('selectFact', (fact, factId) => {
                    console.log('Selected fact:', fact);
                });

                window.tripletNetwork.on('selectConcept', (concept, conceptId) => {
                    console.log('Selected concept:', concept);
                });
            }, 100);
        }
    });

    // Setup HTMX event handlers
    document.addEventListener('htmx:afterSwap', function(event) {
        // Re-initialize Fomantic UI components after HTMX swaps
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.popup').popup();
    });

    document.addEventListener('htmx:responseError', function(event) {
        // Show error message
        showErrorMessage('Request failed: ' + event.detail.xhr.statusText);
    });

    // Utility functions
    window.showSuccessMessage = function(message) {
        const toast = `
            <div class="ui success message">
                <i class="close icon"></i>
                <div class="header">Success</div>
                <p>${message}</p>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', toast);
        
        setTimeout(() => {
            $('.ui.success.message .close').click(() => {
                $(this).closest('.message').remove();
            });
        }, 100);
    };

    window.showErrorMessage = function(message) {
        const toast = `
            <div class="ui error message">
                <i class="close icon"></i>
                <div class="header">Error</div>
                <p>${message}</p>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', toast);
        
        setTimeout(() => {
            $('.ui.error.message .close').click(() => {
                $(this).closest('.message').remove();
            });
        }, 100);
    };

    window.showFactDetails = function(factId) {
        fetch(`/facts/${factId}`)
            .then(response => response.text())
            .then(html => {
                document.querySelector('#fact-details-modal .content').innerHTML = html;
                $('#fact-details-modal').modal('show');
            })
            .catch(error => {
                showErrorMessage('Failed to load fact details');
            });
    };

    window.confirmDeleteFact = function(factId, statement) {
        $('#delete-fact-modal')
            .modal({
                onApprove: function() {
                    deleteFact(factId);
                }
            })
            .modal('show');
    };

    window.deleteFact = function(factId) {
        fetch(`/facts/${factId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Fact deleted successfully');
                // Refresh the current view
                document.querySelector('[hx-get]')?.dispatchEvent(new Event('click'));
            } else {
                showErrorMessage(data.message || 'Failed to delete fact');
            }
        })
        .catch(error => {
            showErrorMessage('Failed to delete fact');
        });
    };
</script>
@endsection

@section('styles')
<style>
    .concept-tag.primitive {
        background-color: #e74c3c !important;
        color: white !important;
    }

    .concept-tag.derived {
        background-color: #95a5a6 !important;
        color: white !important;
    }

    .role-subject .ui.dropdown {
        border-left: 4px solid #e74c3c;
    }

    .role-predicate .ui.dropdown {
        border-left: 4px solid #2ecc71;
    }

    .role-object .ui.dropdown {
        border-left: 4px solid #3498db;
    }

    .triplet-network-container {
        border: 1px solid #e1e1e1;
        border-radius: 4px;
        background-color: #fafafa;
        position: relative;
    }

    .fact-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .fact-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .confidence-bar {
        height: 4px;
        border-radius: 2px;
        margin-top: 5px;
    }

    .confidence-high { background-color: #27ae60; }
    .confidence-medium { background-color: #f39c12; }
    .confidence-low { background-color: #e74c3c; }

    .triplet-display {
        font-family: 'Courier New', monospace;
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-radius: 4px;
        border-left: 4px solid #3498db;
        margin: 10px 0;
    }

    .triplet-role {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.8em;
        margin-right: 5px;
    }

    .role-subject { color: #e74c3c; }
    .role-predicate { color: #2ecc71; }
    .role-object { color: #3498db; }
    .role-modifier { color: #f39c12; }
</style>
@endsection