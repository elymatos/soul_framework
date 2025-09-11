<div class="ui grid">
    <!-- Filters Sidebar -->
    <div class="four wide column">
        <div class="ui segment">
            <h4 class="ui header">
                <i class="filter icon"></i>
                Filters
            </h4>
            
            <form class="ui form" hx-post="/facts/browse" hx-target="#facts-grid" hx-trigger="change, submit">
                <!-- Search -->
                <div class="field">
                    <label>Search</label>
                    <div class="ui icon input">
                        <input type="text" name="search" placeholder="Search facts or concepts..." value="{{ old('search') }}">
                        <i class="search icon"></i>
                    </div>
                </div>

                <!-- Domain Filter -->
                <div class="field">
                    <label>Domain</label>
                    <select class="ui dropdown" name="domain">
                        <option value="all">All Domains</option>
                        <option value="general">General</option>
                        <option value="science">Science</option>
                        <option value="social">Social</option>
                        <option value="business">Business</option>
                        <option value="personal">Personal</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="field">
                    <label>Fact Type</label>
                    <select class="ui dropdown" name="type">
                        <option value="all">All Types</option>
                        <option value="fact">Facts</option>
                        <option value="hypothesis">Hypotheses</option>
                        <option value="rule">Rules</option>
                        <option value="constraint">Constraints</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="field">
                    <label>Priority</label>
                    <select class="ui dropdown" name="priority">
                        <option value="all">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <!-- Verification Filter -->
                <div class="field">
                    <label>Verification</label>
                    <select class="ui dropdown" name="verification">
                        <option value="all">All Facts</option>
                        <option value="verified">Verified Only</option>
                        <option value="unverified">Unverified Only</option>
                    </select>
                </div>

                <!-- Modifiers Filter -->
                <div class="field">
                    <label>Modifiers</label>
                    <select class="ui dropdown" name="modifiers">
                        <option value="all">All Facts</option>
                        <option value="with_modifiers">With Modifiers</option>
                        <option value="without_modifiers">Without Modifiers</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="field">
                    <label>Sort By</label>
                    <select class="ui dropdown" name="sort">
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest First</option>
                        <option value="confidence_high">High Confidence</option>
                        <option value="confidence_low">Low Confidence</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                </div>

                <!-- View Mode -->
                <div class="field">
                    <label>View Mode</label>
                    <select class="ui dropdown" name="view_mode">
                        <option value="list">List View</option>
                        <option value="grid">Grid View</option>
                        <option value="network">Network View</option>
                    </select>
                </div>

                <!-- Results per page -->
                <div class="field">
                    <label>Results per Page</label>
                    <select class="ui dropdown" name="limit">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <button type="submit" class="ui primary button fluid">
                    <i class="search icon"></i>
                    Apply Filters
                </button>
            </form>

            <div class="ui divider"></div>

            <!-- Quick Actions -->
            <div class="ui vertical fluid menu">
                <div class="header item">Quick Actions</div>
                <a class="item" href="/facts/create">
                    <i class="plus icon"></i>
                    Create New Fact
                </a>
                <a class="item" onclick="clearAllFilters()">
                    <i class="eraser icon"></i>
                    Clear Filters
                </a>
                <a class="item" onclick="loadRandomFacts()">
                    <i class="random icon"></i>
                    Random Facts
                </a>
            </div>
        </div>

        <!-- Statistics Panel -->
        <div class="ui segment" id="browse-statistics">
            <h4 class="ui header">
                <i class="chart bar icon"></i>
                Statistics
            </h4>
            <div class="ui placeholder">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="twelve wide column">
        <!-- Results Header -->
        <div class="ui secondary segment">
            <div class="ui grid">
                <div class="ten wide column">
                    <div class="ui breadcrumb">
                        <a class="section" href="/facts">Facts</a>
                        <i class="right angle icon divider"></i>
                        <div class="active section">Browse</div>
                    </div>
                </div>
                <div class="six wide column">
                    <div class="ui right floated buttons">
                        <div class="ui button" onclick="refreshResults()">
                            <i class="refresh icon"></i>
                            Refresh
                        </div>
                        <div class="ui dropdown button">
                            <i class="download icon"></i>
                            Export
                            <div class="menu">
                                <a class="item" href="/facts/export?format=json">JSON</a>
                                <a class="item" href="/facts/export?format=csv">CSV</a>
                                <a class="item" href="/facts/export?format=cypher">Cypher</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facts Grid -->
        <div id="facts-grid">
            @include('Facts.Browse.grid', ['facts' => [], 'pagination' => null])
        </div>
    </div>
</div>

<script>
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-load initial results
        document.querySelector('form[hx-post="/facts/browse"]').dispatchEvent(new Event('submit'));
        
        // Load statistics
        loadBrowseStatistics();
    });

    function clearAllFilters() {
        const form = document.querySelector('form[hx-post="/facts/browse"]');
        form.reset();
        $('.ui.dropdown').dropdown('restore defaults');
        form.dispatchEvent(new Event('submit'));
    }

    function refreshResults() {
        const form = document.querySelector('form[hx-post="/facts/browse"]');
        form.dispatchEvent(new Event('submit'));
        loadBrowseStatistics();
    }

    function loadRandomFacts() {
        // Clear filters and set random sort
        clearAllFilters();
        setTimeout(() => {
            document.querySelector('select[name="sort"]').value = 'random';
            document.querySelector('form[hx-post="/facts/browse"]').dispatchEvent(new Event('submit'));
        }, 100);
    }

    function loadBrowseStatistics() {
        fetch('/facts/statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatisticsDisplay(data.data);
                }
            })
            .catch(error => {
                console.error('Failed to load statistics:', error);
            });
    }

    function updateStatisticsDisplay(stats) {
        const container = document.getElementById('browse-statistics');
        const content = `
            <h4 class="ui header">
                <i class="chart bar icon"></i>
                Statistics
            </h4>
            <div class="ui mini statistics">
                <div class="statistic">
                    <div class="value">${stats.total_facts}</div>
                    <div class="label">Total Facts</div>
                </div>
                <div class="statistic">
                    <div class="value">${stats.concepts_involved}</div>
                    <div class="label">Concepts</div>
                </div>
            </div>
            <div class="ui tiny statistics">
                <div class="statistic">
                    <div class="value">${stats.avg_confidence}</div>
                    <div class="label">Avg Confidence</div>
                </div>
                <div class="statistic">
                    <div class="value">${stats.verification_rate}%</div>
                    <div class="label">Verified</div>
                </div>
            </div>
            <div class="ui divider"></div>
            <div class="ui relaxed list">
                <div class="item">
                    <i class="folder icon"></i>
                    <div class="content">
                        <div class="header">Domains</div>
                        <div class="description">${stats.domains.length} active domains</div>
                    </div>
                </div>
                <div class="item">
                    <i class="tag icon"></i>
                    <div class="content">
                        <div class="header">Types</div>
                        <div class="description">${stats.fact_types.join(', ')}</div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML = content;
    }
</script>