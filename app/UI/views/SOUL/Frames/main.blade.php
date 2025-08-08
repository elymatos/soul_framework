<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/soul','SOUL Framework'],['','Frame Workbench']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="frame-workbench" 
                         x-data="frameWorkbench()" 
                         x-init="init()">
                        
                        <!-- Header Section -->
                        <div class="workbench-header">
                            <div class="header-info">
                                <h2 class="ui header">
                                    <i class="sitemap icon"></i>
                                    Frame Semantics Workbench
                                    <div class="sub header">Comprehensive frame-based cognitive processing tools</div>
                                </h2>
                            </div>
                            <div class="header-stats" x-show="frameStats.total_frames > 0">
                                <div class="ui mini statistics">
                                    <div class="statistic">
                                        <div class="value" x-text="frameStats.total_frames"></div>
                                        <div class="label">Total Frames</div>
                                    </div>
                                    <div class="statistic">
                                        <div class="value" x-text="frameStats.commercial_frames"></div>
                                        <div class="label">Commercial</div>
                                    </div>
                                    <div class="statistic">
                                        <div class="value" x-text="frameStats.image_schema_frames"></div>
                                        <div class="label">Image Schema</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="ui pointing secondary menu workbench-tabs">
                            <a class="item" 
               :class="{ active: activeTab === 'library' }" 
               @click="switchTab('library')">
                                <i class="book icon"></i>
                                Frame Library
                            </a>
                            <a class="item" 
               :class="{ active: activeTab === 'structure' }" 
               @click="switchTab('structure')">
                                <i class="project diagram icon"></i>
                                Structure Viewer
                            </a>
                            <a class="item" 
               :class="{ active: activeTab === 'matching' }" 
               @click="switchTab('matching')">
                                <i class="search icon"></i>
                                Pattern Matching
                            </a>
                            <a class="item" 
               :class="{ active: activeTab === 'instances' }" 
               @click="switchTab('instances')">
                                <i class="clone icon"></i>
                                Frame Instances
                            </a>
                            <a class="item" 
               :class="{ active: activeTab === 'commercial' }" 
               @click="switchTab('commercial')">
                                <i class="dollar icon"></i>
                                Commercial Analyzer
                            </a>
                        </div>

                        <!-- Frame Library Tab -->
                        <div class="tab-content" x-show="activeTab === 'library'" x-transition>
                            <div class="frame-library">
                                <!-- Library Controls -->
                                <div class="library-controls">
                                    <div class="ui form">
                                        <div class="fields">
                                            <div class="eight wide field">
                                                <div class="ui left icon input">
                                                    <i class="search icon"></i>
                                                    <input type="text" 
                                           x-model="librarySearch" 
                                           @input="searchFrames()"
                                           placeholder="Search frames...">
                                                </div>
                                            </div>
                                            <div class="four wide field">
                                                <select class="ui dropdown" 
                                        x-model="libraryFilter" 
                                        @change="filterFrames()">
                                                    <option value="">All Types</option>
                                                    <option value="frame">Frame</option>
                                                    <option value="image_schema">Image Schema</option>
                                                    <option value="meta_schema">Meta-schema</option>
                                                </select>
                                            </div>
                                            <div class="four wide field">
                                                <button class="ui primary button" @click="refreshFrameLibrary()">
                                                    <i class="refresh icon"></i>
                                                    Refresh
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Frame Grid -->
                                <div class="frame-grid" x-show="frames.length > 0">
                                    <template x-for="frame in filteredFrames" :key="frame.name">
                                        <div class="ui card frame-card" 
                             :class="'frame-type-' + frame.type"
                             @click="selectFrame(frame)">
                                            <div class="content">
                                                <div class="header">
                                                    <span x-text="frame.name"></span>
                                                    <div class="ui mini label" 
                                         :class="getFrameTypeClass(frame.type)"
                                         x-text="frame.type"></div>
                                                </div>
                                                <div class="meta">
                                                    <span x-text="frame.domain || 'General'"></span>
                                                </div>
                                                <div class="description" x-text="frame.description || 'No description available'"></div>
                                                <template x-if="frame.frame_elements && Object.keys(frame.frame_elements).length > 0">
                                                    <div class="extra content">
                                                        <div class="ui mini labels">
                                                            <template x-for="element in Object.keys(frame.frame_elements).slice(0, 4)" :key="element">
                                                                <div class="ui label" x-text="element"></div>
                                                            </template>
                                                            <template x-if="Object.keys(frame.frame_elements).length > 4">
                                                                <div class="ui label">...</div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Empty State -->
                                <div class="empty-state" x-show="frames.length === 0 && !loading">
                                    <i class="sitemap icon empty-icon"></i>
                                    <h3>No frames available</h3>
                                    <p>Initialize SOUL primitives or import frame definitions to get started.</p>
                                    <button class="ui primary button" @click="initializePrimitives()">
                                        <i class="plus icon"></i>
                                        Initialize Primitives
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Structure Viewer Tab -->
                        <div class="tab-content" x-show="activeTab === 'structure'" x-transition>
                            <div class="structure-viewer">
                                <div class="viewer-controls">
                                    <div class="ui form">
                                        <div class="fields">
                                            <div class="eight wide field">
                                                <label>Select Frame</label>
                                                <select class="ui search dropdown" 
                                        x-model="selectedFrameName" 
                                        @change="loadFrameStructure()">
                                                    <option value="">Choose a frame...</option>
                                                    <template x-for="frame in frames" :key="frame.name">
                                                        <option :value="frame.name" x-text="frame.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="four wide field">
                                                <label>Layout</label>
                                                <select class="ui dropdown" x-model="structureLayout">
                                                    <option value="hierarchical">Hierarchical</option>
                                                    <option value="circular">Circular</option>
                                                    <option value="force">Force-directed</option>
                                                </select>
                                            </div>
                                            <div class="four wide field">
                                                <label>&nbsp;</label>
                                                <button class="ui button" @click="exportStructure()">
                                                    <i class="download icon"></i>
                                                    Export
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Structure Visualization -->
                                <div class="structure-container">
                                    <div id="frame-structure-viz" class="visualization-area"></div>
                                    
                                    <!-- Frame Details Panel -->
                                    <div class="frame-details-panel" x-show="selectedFrame">
                                        <div class="ui segment">
                                            <h4 class="ui header">Frame Details</h4>
                                            <template x-if="selectedFrame">
                                                <div class="frame-info">
                                                    <div class="ui definition list">
                                                        <div class="item">
                                                            <div class="term">Name</div>
                                                            <div class="definition" x-text="selectedFrame.name"></div>
                                                        </div>
                                                        <div class="item">
                                                            <div class="term">Type</div>
                                                            <div class="definition" x-text="selectedFrame.type"></div>
                                                        </div>
                                                        <div class="item">
                                                            <div class="term">Domain</div>
                                                            <div class="definition" x-text="selectedFrame.domain || 'General'"></div>
                                                        </div>
                                                        <div class="item">
                                                            <div class="term">Description</div>
                                                            <div class="definition" x-text="selectedFrame.description || 'No description available'"></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <template x-if="selectedFrame.elements && selectedFrame.elements.length > 0">
                                                        <div class="frame-elements">
                                                            <h5 class="ui header">Frame Elements</h5>
                                                            <div class="ui relaxed list">
                                                                <template x-for="element in selectedFrame.elements" :key="element.name">
                                                                    <div class="item">
                                                                        <div class="content">
                                                                            <div class="header" x-text="element.name"></div>
                                                                            <div class="description" x-text="element.description || 'No description'"></div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pattern Matching Tab -->
                        <div class="tab-content" x-show="activeTab === 'matching'" x-transition>
                            <div class="pattern-matching">
                                <div class="ui grid">
                                    <div class="ten wide column">
                                        <div class="matching-input">
                                            <div class="ui form">
                                                <div class="field">
                                                    <label>Input Text</label>
                                                    <textarea x-model="matchingInput" 
                                              placeholder="Enter text to analyze for frame patterns..."
                                              rows="4"></textarea>
                                                </div>
                                                <div class="fields">
                                                    <div class="eight wide field">
                                                        <label>Frame Candidates</label>
                                                        <div class="ui multiple search selection dropdown" 
                                             x-ref="candidateDropdown">
                                                            <input type="hidden" name="frame_candidates">
                                                            <div class="default text">Select frames to test against</div>
                                                            <div class="menu">
                                                                <template x-for="frame in frames" :key="frame.name">
                                                                    <div class="item" :data-value="frame.name" x-text="frame.name"></div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="four wide field">
                                                        <label>Threshold</label>
                                                        <input type="number" 
                                               x-model="matchingThreshold" 
                                               min="0" max="1" step="0.1" 
                                               placeholder="0.5">
                                                    </div>
                                                    <div class="four wide field">
                                                        <label>&nbsp;</label>
                                                        <button class="ui primary button" 
                                                @click="performMatching()"
                                                :disabled="!matchingInput">
                                                            <i class="play icon"></i>
                                                            Match Frames
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Matching Results -->
                                        <div class="matching-results" x-show="matchingResults.length > 0">
                                            <h4 class="ui header">Matching Results</h4>
                                            <div class="ui relaxed divided list">
                                                <template x-for="result in matchingResults" :key="result.frame_type">
                                                    <div class="item">
                                                        <div class="content">
                                                            <div class="header">
                                                                <span x-text="result.frame_type"></span>
                                                                <div class="ui mini label" 
                                                     :class="getConfidenceClass(result.confidence)">
                                                                    <span x-text="Math.round(result.confidence * 100)"></span>%
                                                                </div>
                                                            </div>
                                                            <div class="description">
                                                                <template x-if="result.matched_elements && result.matched_elements.length > 0">
                                                                    <div class="matched-elements">
                                                                        <strong>Matched Elements:</strong>
                                                                        <div class="ui mini labels">
                                                                            <template x-for="element in result.matched_elements" :key="element">
                                                                                <div class="ui green label" x-text="element"></div>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                                <template x-if="result.missing_elements && result.missing_elements.length > 0">
                                                                    <div class="missing-elements">
                                                                        <strong>Missing Elements:</strong>
                                                                        <div class="ui mini labels">
                                                                            <template x-for="element in result.missing_elements" :key="element">
                                                                                <div class="ui red label" x-text="element"></div>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="six wide column">
                                        <!-- Quick Examples -->
                                        <div class="ui segment">
                                            <h4 class="ui header">Quick Examples</h4>
                                            <div class="ui relaxed list">
                                                <template x-for="example in quickExamples" :key="example">
                                                    <div class="item">
                                                        <div class="content">
                                                            <a href="#" 
                               @click.prevent="matchingInput = example"
                               x-text="example"></a>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Frame Instances Tab -->
                        <div class="tab-content" x-show="activeTab === 'instances'" x-transition>
                            <div class="frame-instances">
                                <div class="instances-controls">
                                    <div class="ui form">
                                        <div class="fields">
                                            <div class="six wide field">
                                                <label>Session ID</label>
                                                <input type="text" 
                                       x-model="instanceSessionId" 
                                       placeholder="Filter by session ID">
                                            </div>
                                            <div class="six wide field">
                                                <label>Frame Type</label>
                                                <select class="ui dropdown" x-model="instanceFrameType">
                                                    <option value="">All Types</option>
                                                    <template x-for="frame in frames" :key="frame.name">
                                                        <option :value="frame.name" x-text="frame.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="four wide field">
                                                <label>&nbsp;</label>
                                                <button class="ui button" @click="refreshInstances()">
                                                    <i class="refresh icon"></i>
                                                    Refresh
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instances Grid -->
                                <div class="instances-grid" x-show="frameInstances.length > 0">
                                    <template x-for="instance in frameInstances" :key="instance.id">
                                        <div class="ui card instance-card">
                                            <div class="content">
                                                <div class="header">
                                                    <span x-text="instance.name"></span>
                                                    <div class="ui mini label" x-text="instance.frame_type"></div>
                                                </div>
                                                <div class="meta">
                                                    <span>Session: </span><span x-text="instance.session_id"></span><br>
                                                    <span>Created: </span><span x-text="formatDate(instance.instantiated_at)"></span>
                                                </div>
                                                <div class="description">
                                                    <template x-if="instance.elements && instance.elements.length > 0">
                                                        <div class="instance-elements">
                                                            <strong>Element Bindings:</strong>
                                                            <div class="ui mini list">
                                                                <template x-for="element in instance.elements" :key="element.name">
                                                                    <div class="item">
                                                                        <strong x-text="element.name"></strong>: 
                                                                        <span x-text="JSON.stringify(element.value)"></span>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Empty State -->
                                <div class="empty-state" x-show="frameInstances.length === 0 && !loading">
                                    <i class="clone icon empty-icon"></i>
                                    <h3>No frame instances found</h3>
                                    <p>Create frame instances through pattern matching or direct instantiation.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Commercial Analyzer Tab -->
                        <div class="tab-content" x-show="activeTab === 'commercial'" x-transition>
                            <div class="commercial-analyzer">
                                <p class="ui message">
                                    <i class="info circle icon"></i>
                                    This is a specialized interface for commercial transaction analysis. 
                                    <a href="/soul/frames/commercial">Visit the dedicated Commercial Analyzer</a> for the full experience.
                                </p>
                                
                                <div class="ui form">
                                    <div class="field">
                                        <label>Commercial Transaction Text</label>
                                        <textarea x-model="commercialText" 
                                          placeholder="Enter text describing a commercial transaction..."
                                          rows="4"></textarea>
                                    </div>
                                    <button class="ui primary button" 
                            @click="analyzeCommercialTransaction()"
                            :disabled="!commercialText">
                                        <i class="dollar icon"></i>
                                        Analyze Transaction
                                    </button>
                                </div>

                                <!-- Commercial Analysis Results -->
                                <div class="commercial-results" x-show="commercialAnalysis">
                                    <h4 class="ui header">Analysis Results</h4>
                                    <template x-if="commercialAnalysis">
                                        <div class="ui segments">
                                            <div class="ui segment">
                                                <h5 class="ui header">Best Frame Match</h5>
                                                <template x-if="commercialAnalysis.best_match">
                                                    <div class="ui label" 
                                         :class="getConfidenceClass(commercialAnalysis.best_match.confidence)">
                                                        <span x-text="commercialAnalysis.best_match.frame_type"></span>
                                                        (<span x-text="Math.round(commercialAnalysis.best_match.confidence * 100)"></span>%)
                                                    </div>
                                                </template>
                                                <template x-if="!commercialAnalysis.best_match">
                                                    <p>No suitable frame match found.</p>
                                                </template>
                                            </div>
                                            
                                            <template x-if="commercialAnalysis.entities && Object.keys(commercialAnalysis.entities).length > 0">
                                                <div class="ui segment">
                                                    <h5 class="ui header">Extracted Entities</h5>
                                                    <template x-for="(entities, type) in commercialAnalysis.entities" :key="type">
                                                        <div class="entity-group">
                                                            <strong x-text="type.toUpperCase()"></strong>:
                                                            <div class="ui mini labels">
                                                                <template x-for="entity in entities" :key="entity">
                                                                    <div class="ui label" x-text="entity"></div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Overlay -->
                        <div class="ui active dimmer" x-show="loading">
                            <div class="ui loader"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function frameWorkbench() {
            return {
                // State
                activeTab: 'library',
                loading: false,
                
                // Frame Library
                frames: [],
                filteredFrames: [],
                librarySearch: '',
                libraryFilter: '',
                frameStats: @json($frameStats ?? []),
                
                // Structure Viewer
                selectedFrame: null,
                selectedFrameName: '',
                structureLayout: 'hierarchical',
                frameVisualization: null,
                
                // Pattern Matching
                matchingInput: '',
                matchingThreshold: 0.5,
                matchingResults: [],
                frameCandidates: [],
                
                // Frame Instances
                frameInstances: [],
                instanceSessionId: '',
                instanceFrameType: '',
                
                // Commercial Analyzer
                commercialText: '',
                commercialAnalysis: null,
                
                // Sample Data
                quickExamples: [
                    'John buys a book from Mary',
                    'The customer purchased three items',
                    'Alice sold her car to Bob',
                    'The box contains several items',
                    'She walked from the house to the store'
                ],
                
                // Initialization
                async init() {
                    await this.loadFrames();
                    this.filteredFrames = this.frames;
                    this.initializeUI();
                },
                
                initializeUI() {
                    this.$nextTick(() => {
                        $('.ui.dropdown').dropdown();
                        $('.ui.checkbox').checkbox();
                    });
                },
                
                // Tab Management
                switchTab(tab) {
                    this.activeTab = tab;
                    
                    // Load tab-specific data
                    switch(tab) {
                        case 'instances':
                            this.loadFrameInstances();
                            break;
                        case 'structure':
                            if (this.selectedFrameName && !this.frameVisualization) {
                                this.loadFrameStructure();
                            }
                            break;
                    }
                },
                
                // Frame Library Methods
                async loadFrames() {
                    this.loading = true;
                    try {
                        const response = await fetch('/soul/frames/api/frames');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.frames = data.data.frames;
                            this.filteredFrames = this.frames;
                        } else {
                            this.showError('Failed to load frames: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Failed to load frames:', error);
                        this.showError('Failed to load frames: ' + error.message);
                        
                        // Load sample frames as fallback
                        this.frames = @json($sampleFrames ?? []);
                        this.filteredFrames = this.frames;
                    } finally {
                        this.loading = false;
                    }
                },
                
                searchFrames() {
                    this.filterFrames();
                },
                
                filterFrames() {
                    let filtered = this.frames;
                    
                    if (this.librarySearch) {
                        const search = this.librarySearch.toLowerCase();
                        filtered = filtered.filter(frame => 
                            frame.name.toLowerCase().includes(search) ||
                            (frame.description && frame.description.toLowerCase().includes(search))
                        );
                    }
                    
                    if (this.libraryFilter) {
                        filtered = filtered.filter(frame => frame.type === this.libraryFilter);
                    }
                    
                    this.filteredFrames = filtered;
                },
                
                refreshFrameLibrary() {
                    this.loadFrames();
                },
                
                selectFrame(frame) {
                    this.selectedFrame = frame;
                    this.selectedFrameName = frame.name;
                    this.switchTab('structure');
                    this.loadFrameStructure();
                },
                
                // Structure Viewer Methods
                async loadFrameStructure() {
                    if (!this.selectedFrameName) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch(`/soul/frames/api/frames/${encodeURIComponent(this.selectedFrameName)}/structure`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.selectedFrame = data.data.frame;
                            this.selectedFrame.elements = data.data.elements;
                            this.selectedFrame.relationships = data.data.relationships;
                            
                            await this.initializeFrameVisualization();
                        } else {
                            this.showError('Failed to load frame structure: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Failed to load frame structure:', error);
                        this.showError('Failed to load frame structure: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async initializeFrameVisualization() {
                    await this.$nextTick();
                    
                    try {
                        if (this.frameVisualization) {
                            this.frameVisualization.destroy();
                        }
                        
                        this.frameVisualization = new window.FrameVisualization('frame-structure-viz', {
                            layout: this.structureLayout,
                            interactive: true
                        });
                        
                        if (this.selectedFrame) {
                            this.frameVisualization.renderFrameStructure(this.selectedFrame);
                        }
                    } catch (error) {
                        console.error('Failed to initialize frame visualization:', error);
                    }
                },
                
                exportStructure() {
                    if (this.frameVisualization) {
                        this.frameVisualization.exportAsImage('png');
                    }
                },
                
                // Pattern Matching Methods
                async performMatching() {
                    if (!this.matchingInput) return;
                    
                    this.loading = true;
                    try {
                        // Get selected candidates from dropdown
                        const candidateValues = $(this.$refs.candidateDropdown).dropdown('get value');
                        const candidates = candidateValues ? candidateValues.split(',') : this.frames.map(f => f.name);
                        
                        const response = await fetch('/soul/frames/api/match', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                input: { text: this.matchingInput },
                                frame_candidates: candidates,
                                threshold: this.matchingThreshold,
                                context: []
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.matchingResults = data.data.matches || [];
                        } else {
                            this.showError('Frame matching failed: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Frame matching failed:', error);
                        this.showError('Frame matching failed: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                // Frame Instances Methods
                async loadFrameInstances() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.instanceSessionId) params.set('session_id', this.instanceSessionId);
                        if (this.instanceFrameType) params.set('frame_type', this.instanceFrameType);
                        
                        const response = await fetch(`/soul/frames/api/instances?${params}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.frameInstances = data.data.instances || [];
                        } else {
                            this.showError('Failed to load frame instances: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Failed to load frame instances:', error);
                        this.showError('Failed to load frame instances: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                refreshInstances() {
                    this.loadFrameInstances();
                },
                
                // Commercial Analyzer Methods
                async analyzeCommercialTransaction() {
                    if (!this.commercialText) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch('/soul/frames/api/commercial/analyze', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                text: this.commercialText,
                                context: ['commercial', 'transaction']
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.commercialAnalysis = data.data;
                        } else {
                            this.showError('Commercial analysis failed: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Commercial analysis failed:', error);
                        this.showError('Commercial analysis failed: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                // Utility Methods
                getFrameTypeClass(type) {
                    const typeClasses = {
                        'frame': 'blue',
                        'image_schema': 'green',
                        'meta_schema': 'purple',
                        'primitive': 'orange'
                    };
                    return typeClasses[type] || 'grey';
                },
                
                getConfidenceClass(confidence) {
                    if (confidence >= 0.8) return 'green';
                    if (confidence >= 0.6) return 'yellow';
                    if (confidence >= 0.4) return 'orange';
                    return 'red';
                },
                
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleString();
                },
                
                async initializePrimitives() {
                    this.loading = true;
                    try {
                        const response = await fetch('/soul/initialize', { method: 'POST' });
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showSuccess('SOUL primitives initialized successfully');
                            await this.loadFrames();
                        } else {
                            this.showError('Failed to initialize primitives: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Failed to initialize primitives:', error);
                        this.showError('Failed to initialize primitives: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                showError(message) {
                    $("body").toast({
                        message: message,
                        class: "error",
                        showIcon: "exclamation triangle",
                        displayTime: 5000,
                        position: "top center"
                    });
                },
                
                showSuccess(message) {
                    $("body").toast({
                        message: message,
                        class: "success",
                        showIcon: "check",
                        displayTime: 3000,
                        position: "top center"
                    });
                }
            }
        }
        
        $(function() {
            $('.ui.dropdown').dropdown();
            $('.ui.checkbox').checkbox();
        });
    </script>
</x-layout::index>