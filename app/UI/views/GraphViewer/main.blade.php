<x-layout::index>
    @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush
    <style>
        .graph-browser-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .browser-header {
            background: #1b1c1d;
            color: white;
            padding: 1rem;
            flex-shrink: 0;
        }

        .browser-content {
            flex: 1;
            display: flex;
            min-height: 0;
            padding: 2rem;
            background: #f8f9fa;
        }

        .graphs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            width: 100%;
        }

        .graph-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .graph-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .graph-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .graph-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .graph-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            flex: 1;
        }

        .stat-value {
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .search-box {
            margin-bottom: 2rem;
            max-width: 400px;
        }

        .filter-buttons {
            margin-bottom: 1.5rem;
        }
    </style>

    <div class="graph-browser-container">
        <!-- Header -->
        <div class="browser-header">
            <div class="ui container">
                <h2 class="ui header" style="color: white; margin: 0;">
                    <i class="eye icon"></i>
                    <div class="content">
                        Graph Viewer
                        <div class="sub header" style="color: #ccc;">Browse and view SOUL Framework conceptual graphs</div>
                    </div>
                </h2>
            </div>
        </div>

        <!-- Main Content -->
        <div class="browser-content">
            <div style="width: 100%;">
                <!-- Search and Filters -->
                <div class="search-box">
                    <div class="ui icon input fluid">
                        <input type="text" id="graph-search" placeholder="Search graphs by name or chapter...">
                        <i class="search icon"></i>
                    </div>
                </div>

                <div class="filter-buttons">
                    <div class="ui buttons">
                        <button class="ui button active" data-filter="all">All Graphs</button>
                        <button class="ui button" data-filter="chapter">Chapters</button>
                        <button class="ui button" data-filter="custom">Custom</button>
                    </div>
                    <a href="/graph-editor" class="ui right floated orange button">
                        <i class="edit icon"></i>
                        Open Graph Editor
                    </a>
                </div>

                <!-- Loading State -->
                <div id="loading-container" class="loading-container">
                    <div class="ui active loader"></div>
                </div>

                <!-- Graphs Grid -->
                <div id="graphs-grid" class="graphs-grid" style="display: none;">
                    <!-- Graph cards will be populated here -->
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="empty-state" style="display: none;">
                    <i class="folder open outline icon" style="font-size: 3rem; color: #ccc;"></i>
                    <h3>No graphs found</h3>
                    <p>No graphs match your current search or filter criteria.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let allGraphs = [];
        let filteredGraphs = [];
        let currentFilter = 'all';

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            loadGraphsList();
            setupEventHandlers();
        });

        function setupEventHandlers() {
            // Search functionality
            const searchInput = document.getElementById('graph-search');
            searchInput.addEventListener('input', function() {
                filterAndDisplayGraphs();
            });

            // Filter buttons
            const filterButtons = document.querySelectorAll('[data-filter]');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    currentFilter = this.dataset.filter;
                    filterAndDisplayGraphs();
                });
            });
        }

        function loadGraphsList() {
            fetch('/graph-viewer/list')
                .then(response => response.json())
                .then(graphs => {
                    allGraphs = graphs;
                    filteredGraphs = [...graphs];

                    document.getElementById('loading-container').style.display = 'none';
                    displayGraphs();
                })
                .catch(error => {
                    console.error('Error loading graphs:', error);
                    document.getElementById('loading-container').style.display = 'none';
                    showEmptyState();
                });
        }

        function filterAndDisplayGraphs() {
            const searchTerm = document.getElementById('graph-search').value.toLowerCase();

            // Start with all graphs
            filteredGraphs = allGraphs.filter(graph => {
                // Apply category filter
                let categoryMatch = true;
                if (currentFilter === 'chapter') {
                    categoryMatch = graph.filename.includes('chapter_');
                } else if (currentFilter === 'custom') {
                    categoryMatch = !graph.filename.includes('chapter_');
                }

                // Apply search filter
                const searchMatch = searchTerm === '' ||
                    graph.name.toLowerCase().includes(searchTerm) ||
                    graph.filename.toLowerCase().includes(searchTerm);

                return categoryMatch && searchMatch;
            });

            displayGraphs();
        }

        function displayGraphs() {
            const grid = document.getElementById('graphs-grid');
            const emptyState = document.getElementById('empty-state');

            if (filteredGraphs.length === 0) {
                grid.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            grid.style.display = 'grid';
            grid.innerHTML = '';

            filteredGraphs.forEach(graph => {
                const card = createGraphCard(graph);
                grid.appendChild(card);
            });
        }

        function createGraphCard(graph) {
            const card = document.createElement('div');
            card.className = 'graph-card';

            // Extract chapter info if available
            const isChapter = graph.filename.includes('chapter_');
            const chapterMatch = graph.filename.match(/chapter_(\d+)/);
            const chapterNumber = chapterMatch ? chapterMatch[1] : null;

            card.innerHTML = `
                <div class="graph-title">${formatGraphName(graph.name)}</div>
                <div class="graph-meta">
                    ${isChapter && chapterNumber ? `Chapter ${chapterNumber} • ` : ''}
                    ${formatFileSize(graph.size)} • ${formatDate(graph.modified)}
                </div>
                <div class="graph-stats">
                    <div class="stat-item">
                        <div class="stat-value">-</div>
                        <div class="stat-label">Nodes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">-</div>
                        <div class="stat-label">Edges</div>
                    </div>
                </div>
                <div class="ui fluid button primary" onclick="viewGraph('${graph.filename}')">
                    <i class="eye icon"></i>
                    View Graph
                </div>
            `;

            return card;
        }

        function formatGraphName(name) {
            // Clean up the graph name for display
            return name
                .replace(/chapter_\d+_/, '')
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleDateString();
        }

        function viewGraph(filename) {
            window.location.href = `/graph-viewer/view/${encodeURIComponent(filename)}`;
        }

        function showEmptyState() {
            document.getElementById('graphs-grid').style.display = 'none';
            document.getElementById('empty-state').style.display = 'block';
        }
    </script>
</x-layout::index>