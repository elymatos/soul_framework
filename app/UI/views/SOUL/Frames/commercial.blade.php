<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/soul','SOUL Framework'],['/soul/frames','Frame Workbench'],['','Commercial Analyzer']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="commercial-analyzer" 
                         x-data="commercialAnalyzer()" 
                         x-init="init()">
                        
                        <!-- Header Section -->
                        <div class="analyzer-header">
                            <div class="header-info">
                                <h2 class="ui header">
                                    <i class="dollar icon"></i>
                                    Commercial Transaction Analyzer
                                    <div class="sub header">Specialized frame-based analysis for commercial transactions and business interactions</div>
                                </h2>
                            </div>
                            <div class="header-actions">
                                <a href="/soul/frames" class="ui button">
                                    <i class="arrow left icon"></i>
                                    Back to Frame Workbench
                                </a>
                            </div>
                        </div>

                        <!-- Main Analysis Interface -->
                        <div class="ui grid">
                            <!-- Input Column -->
                            <div class="ten wide column">
                                <div class="ui segment analysis-input">
                                    <h3 class="ui header">
                                        <i class="edit icon"></i>
                                        Transaction Text Analysis
                                    </h3>
                                    
                                    <!-- Text Input -->
                                    <div class="ui form">
                                        <div class="field">
                                            <label>Transaction Description</label>
                                            <textarea x-model="transactionText" 
                                                      @input="onTextChange()"
                                                      placeholder="Enter a description of a commercial transaction..."
                                                      rows="6"></textarea>
                                            <div class="ui mini message" x-show="transactionText.length > 0">
                                                <span x-text="transactionText.length"></span> characters, 
                                                <span x-text="wordCount"></span> words
                                            </div>
                                        </div>
                                        
                                        <!-- Analysis Options -->
                                        <div class="fields">
                                            <div class="four wide field">
                                                <label>Analysis Depth</label>
                                                <select class="ui dropdown" x-model="analysisDepth">
                                                    <option value="basic">Basic</option>
                                                    <option value="detailed" selected>Detailed</option>
                                                    <option value="comprehensive">Comprehensive</option>
                                                </select>
                                            </div>
                                            <div class="four wide field">
                                                <label>Confidence Threshold</label>
                                                <input type="number" x-model="confidenceThreshold" 
                                                       min="0" max="1" step="0.1" value="0.3">
                                            </div>
                                            <div class="four wide field">
                                                <label>Context</label>
                                                <div class="ui multiple selection dropdown" x-ref="contextDropdown">
                                                    <input type="hidden" name="context">
                                                    <div class="default text">Select context...</div>
                                                    <div class="menu">
                                                        <div class="item" data-value="retail">Retail</div>
                                                        <div class="item" data-value="b2b">B2B</div>
                                                        <div class="item" data-value="online">Online</div>
                                                        <div class="item" data-value="finance">Finance</div>
                                                        <div class="item" data-value="legal">Legal</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="four wide field">
                                                <label>&nbsp;</label>
                                                <button class="ui primary button fluid" 
                                                        @click="analyzeTransaction()"
                                                        :disabled="!transactionText.trim() || analyzing">
                                                    <i class="search icon" :class="{ 'spinner loading': analyzing }"></i>
                                                    Analyze Transaction
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Real-time Analysis Toggle -->
                                        <div class="field">
                                            <div class="ui checkbox">
                                                <input type="checkbox" x-model="realTimeAnalysis" 
                                                       id="real-time-analysis">
                                                <label for="real-time-analysis">Enable real-time analysis</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Analysis Results -->
                                <div class="ui segment analysis-results" 
                                     x-show="analysisResults" 
                                     x-transition>
                                    <h3 class="ui header">
                                        <i class="chart line icon"></i>
                                        Analysis Results
                                    </h3>
                                    
                                    <template x-if="analysisResults">
                                        <div class="results-content">
                                            <!-- Best Frame Match -->
                                            <div class="ui segment best-match-section" 
                                                 x-show="analysisResults.best_match">
                                                <h4 class="ui header">
                                                    <i class="trophy icon"></i>
                                                    Best Frame Match
                                                </h4>
                                                <template x-if="analysisResults.best_match">
                                                    <div class="frame-match-card">
                                                        <div class="ui large label" 
                                                             :class="getConfidenceClass(analysisResults.best_match.confidence)">
                                                            <i class="sitemap icon"></i>
                                                            <span x-text="analysisResults.best_match.frame_type"></span>
                                                            <div class="detail" 
                                                                 x-text="Math.round(analysisResults.best_match.confidence * 100) + '%'"></div>
                                                        </div>
                                                        
                                                        <div class="match-details" x-show="analysisResults.best_match.matched_elements">
                                                            <div class="ui mini labels">
                                                                <template x-for="element in (analysisResults.best_match.matched_elements || [])" :key="element">
                                                                    <div class="ui green label" x-text="element"></div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <!-- All Frame Matches -->
                                            <div class="ui segment matches-section" 
                                                 x-show="analysisResults.frame_matches && analysisResults.frame_matches.length > 0">
                                                <h4 class="ui header">
                                                    <i class="list icon"></i>
                                                    All Frame Matches
                                                </h4>
                                                <div class="ui relaxed divided list">
                                                    <template x-for="match in (analysisResults.frame_matches || [])" :key="match.frame_type">
                                                        <div class="item">
                                                            <div class="content">
                                                                <div class="header">
                                                                    <span x-text="match.frame_type"></span>
                                                                    <div class="ui mini label" 
                                                                         :class="getConfidenceClass(match.confidence)">
                                                                        <span x-text="Math.round(match.confidence * 100)"></span>%
                                                                    </div>
                                                                </div>
                                                                <div class="description">
                                                                    <template x-if="match.matched_elements && match.matched_elements.length > 0">
                                                                        <div>
                                                                            <strong>Matched:</strong>
                                                                            <span x-text="match.matched_elements.join(', ')"></span>
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="match.missing_elements && match.missing_elements.length > 0">
                                                                        <div class="missing-elements">
                                                                            <strong>Missing:</strong>
                                                                            <span x-text="match.missing_elements.join(', ')"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            
                                            <!-- Extracted Entities -->
                                            <div class="ui segment entities-section" 
                                                 x-show="analysisResults.entities && Object.keys(analysisResults.entities).length > 0">
                                                <h4 class="ui header">
                                                    <i class="tags icon"></i>
                                                    Extracted Entities
                                                </h4>
                                                <template x-for="(entities, type) in (analysisResults.entities || {})" :key="type">
                                                    <div class="entity-group">
                                                        <h5 class="ui header" x-text="type.toUpperCase()"></h5>
                                                        <div class="ui labels">
                                                            <template x-for="entity in entities" :key="entity">
                                                                <div class="ui label" 
                                                                     :class="getEntityTypeClass(type)" 
                                                                     x-text="entity"></div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <!-- Commercial Relationships -->
                                            <div class="ui segment relationships-section" 
                                                 x-show="analysisResults.relationships && analysisResults.relationships.length > 0">
                                                <h4 class="ui header">
                                                    <i class="share alternate icon"></i>
                                                    Commercial Relationships
                                                </h4>
                                                <div class="ui relaxed list">
                                                    <template x-for="relationship in (analysisResults.relationships || [])" :key="relationship.type">
                                                        <div class="item">
                                                            <i class="arrow right icon"></i>
                                                            <div class="content">
                                                                <div class="header" x-text="relationship.type"></div>
                                                                <div class="description">
                                                                    <template x-if="relationship.buyer">
                                                                        <span><strong>Buyer:</strong> <span x-text="relationship.buyer"></span></span>
                                                                    </template>
                                                                    <template x-if="relationship.seller">
                                                                        <span> | <strong>Seller:</strong> <span x-text="relationship.seller"></span></span>
                                                                    </template>
                                                                    <template x-if="relationship.item">
                                                                        <span> | <strong>Item:</strong> <span x-text="relationship.item"></span></span>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            
                                            <!-- Export Options -->
                                            <div class="ui segment export-section">
                                                <div class="ui buttons">
                                                    <button class="ui button" @click="exportAnalysis()">
                                                        <i class="download icon"></i>
                                                        Export Results
                                                    </button>
                                                    <button class="ui button" @click="saveToInstances()">
                                                        <i class="save icon"></i>
                                                        Save Instance
                                                    </button>
                                                    <button class="ui button" @click="visualizeRelationships()">
                                                        <i class="project diagram icon"></i>
                                                        Visualize
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Sidebar Column -->
                            <div class="six wide column">
                                <!-- Commercial Frame Library -->
                                <div class="ui segment frame-library">
                                    <h3 class="ui header">
                                        <i class="book icon"></i>
                                        Commercial Frames
                                    </h3>
                                    
                                    <div class="ui relaxed list" x-show="commercialFrames.length > 0">
                                        <template x-for="frame in commercialFrames" :key="frame.name">
                                            <div class="item frame-item" @click="selectFrame(frame)">
                                                <i class="sitemap icon"></i>
                                                <div class="content">
                                                    <div class="header" x-text="frame.name"></div>
                                                    <div class="description" x-text="frame.description || 'No description available'"></div>
                                                    <template x-if="frame.frame_elements && Object.keys(frame.frame_elements).length > 0">
                                                        <div class="frame-elements">
                                                            <div class="ui mini labels">
                                                                <template x-for="element in Object.keys(frame.frame_elements).slice(0, 3)" :key="element">
                                                                    <div class="ui mini label" x-text="element"></div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <div class="empty-state" x-show="commercialFrames.length === 0">
                                        <p>No commercial frames available</p>
                                        <button class="ui mini primary button" @click="loadCommercialFrames()">
                                            <i class="refresh icon"></i>
                                            Refresh
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Quick Examples -->
                                <div class="ui segment examples-section">
                                    <h3 class="ui header">
                                        <i class="lightning icon"></i>
                                        Quick Examples
                                    </h3>
                                    
                                    <div class="ui relaxed list">
                                        <template x-for="example in quickExamples" :key="example">
                                            <div class="item">
                                                <a href="#" 
                                                   @click.prevent="loadExample(example)"
                                                   x-text="example"></a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Analysis History -->
                                <div class="ui segment history-section">
                                    <h3 class="ui header">
                                        <i class="history icon"></i>
                                        Analysis History
                                    </h3>
                                    
                                    <div class="ui mini list" x-show="analysisHistory.length > 0">
                                        <template x-for="(entry, index) in analysisHistory.slice(0, 5)" :key="index">
                                            <div class="item">
                                                <div class="content">
                                                    <div class="header" 
                                                         x-text="entry.text.substring(0, 30) + (entry.text.length > 30 ? '...' : '')"></div>
                                                    <div class="meta" x-text="formatTimestamp(entry.timestamp)"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <div class="empty-state" x-show="analysisHistory.length === 0">
                                        <p>No analysis history yet</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading overlay -->
                        <div class="ui active dimmer" x-show="analyzing" x-transition>
                            <div class="ui text loader">Analyzing commercial transaction...</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function commercialAnalyzer() {
            return {
                // State
                transactionText: '',
                analysisResults: null,
                analyzing: false,
                analysisDepth: 'detailed',
                confidenceThreshold: 0.3,
                realTimeAnalysis: false,
                
                // Data
                commercialFrames: @json($commercialFrames['frames'] ?? []),
                analysisHistory: JSON.parse(localStorage.getItem('commercial-analysis-history') || '[]'),
                
                // UI State
                selectedFrame: null,
                debounceTimer: null,
                
                // Constants
                quickExamples: [
                    'John buys a book from Mary for $20',
                    'The customer purchased three laptops from the electronics store',
                    'Alice sold her car to Bob for fifteen thousand dollars',
                    'The company acquired new software licenses from Microsoft',
                    'She paid the vendor for the catering services at the wedding',
                    'The bank approved a loan of $50,000 for the small business',
                    'Amazon charged my credit card for the monthly Prime subscription'
                ],
                
                // Computed
                get wordCount() {
                    return this.transactionText.trim().split(/\s+/).filter(word => word.length > 0).length;
                },
                
                // Initialization
                init() {
                    this.initializeUI();
                    this.loadCommercialFrames();
                    
                    // Watch for real-time analysis
                    this.$watch('realTimeAnalysis', (enabled) => {
                        if (enabled && this.transactionText.trim()) {
                            this.debouncedAnalyze();
                        }
                    });
                },
                
                initializeUI() {
                    this.$nextTick(() => {
                        $('.ui.dropdown').dropdown();
                        $('.ui.checkbox').checkbox();
                    });
                },
                
                // Text Analysis Methods
                onTextChange() {
                    if (this.realTimeAnalysis && this.transactionText.trim()) {
                        this.debouncedAnalyze();
                    }
                },
                
                debouncedAnalyze() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.analyzeTransaction();
                    }, 1500);
                },
                
                async analyzeTransaction() {
                    if (!this.transactionText.trim()) {
                        this.showError('Please enter transaction text to analyze');
                        return;
                    }
                    
                    this.analyzing = true;
                    
                    try {
                        const response = await fetch('/soul/frames/api/commercial/analyze', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                text: this.transactionText,
                                context: this.getSelectedContext(),
                                depth: this.analysisDepth,
                                threshold: this.confidenceThreshold
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.analysisResults = data.data;
                            this.addToHistory();
                            this.showSuccess('Transaction analysis completed');
                        } else {
                            this.showError('Analysis failed: ' + data.message);
                        }
                        
                    } catch (error) {
                        console.error('Analysis error:', error);
                        this.showError('Analysis failed: ' + error.message);
                    } finally {
                        this.analyzing = false;
                    }
                },
                
                // Frame Library Methods
                async loadCommercialFrames() {
                    try {
                        const response = await fetch('/soul/frames/api/commercial/frames');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.commercialFrames = data.data.frames;
                        } else {
                            console.error('Failed to load commercial frames:', data.message);
                        }
                    } catch (error) {
                        console.error('Error loading commercial frames:', error);
                    }
                },
                
                selectFrame(frame) {
                    this.selectedFrame = frame;
                    // Could trigger frame-specific analysis or visualization
                },
                
                // Example Methods
                loadExample(exampleText) {
                    this.transactionText = exampleText;
                    if (this.realTimeAnalysis) {
                        this.debouncedAnalyze();
                    }
                },
                
                // Export and Save Methods
                exportAnalysis() {
                    if (!this.analysisResults) {
                        this.showError('No analysis results to export');
                        return;
                    }
                    
                    const exportData = {
                        input_text: this.transactionText,
                        analysis_results: this.analysisResults,
                        timestamp: new Date().toISOString(),
                        analysis_settings: {
                            depth: this.analysisDepth,
                            threshold: this.confidenceThreshold,
                            context: this.getSelectedContext()
                        }
                    };
                    
                    this.downloadJSON(exportData, `commercial_analysis_${Date.now()}.json`);
                },
                
                async saveToInstances() {
                    if (!this.analysisResults || !this.analysisResults.best_match) {
                        this.showError('No frame match to save as instance');
                        return;
                    }
                    
                    try {
                        const entities = this.analysisResults.entities || {};
                        const initialElements = {};
                        
                        // Map entities to frame elements
                        if (entities.people) {
                            initialElements.buyer = entities.people[0];
                            initialElements.seller = entities.people[1] || entities.people[0];
                        }
                        if (entities.money) {
                            initialElements.money = entities.money[0];
                        }
                        if (entities.actions) {
                            initialElements.action = entities.actions[0];
                        }
                        
                        const response = await fetch('/soul/frames/api/instantiate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                frame_type: this.analysisResults.best_match.frame_type,
                                initial_elements: initialElements,
                                context: {
                                    source_text: this.transactionText,
                                    analysis_timestamp: new Date().toISOString(),
                                    commercial_analysis: true
                                },
                                session_id: 'commercial_analyzer_' + Date.now()
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showSuccess('Frame instance saved successfully');
                        } else {
                            this.showError('Failed to save frame instance: ' + data.message);
                        }
                        
                    } catch (error) {
                        console.error('Save instance error:', error);
                        this.showError('Failed to save frame instance: ' + error.message);
                    }
                },
                
                visualizeRelationships() {
                    if (!this.analysisResults) {
                        this.showError('No analysis results to visualize');
                        return;
                    }
                    
                    // Navigate to the frame workbench with visualization
                    const params = new URLSearchParams({
                        tab: 'structure',
                        frame: this.analysisResults.best_match?.frame_type || 'COMMERCIAL_TRANSACTION'
                    });
                    
                    window.location.href = `/soul/frames?${params.toString()}`;
                },
                
                // History Methods
                addToHistory() {
                    const entry = {
                        text: this.transactionText,
                        results: this.analysisResults,
                        timestamp: Date.now()
                    };
                    
                    this.analysisHistory.unshift(entry);
                    
                    // Keep only last 50 entries
                    if (this.analysisHistory.length > 50) {
                        this.analysisHistory = this.analysisHistory.slice(0, 50);
                    }
                    
                    localStorage.setItem('commercial-analysis-history', JSON.stringify(this.analysisHistory));
                },
                
                // Utility Methods
                getSelectedContext() {
                    const dropdown = this.$refs.contextDropdown;
                    if (dropdown) {
                        const values = $(dropdown).dropdown('get value');
                        return values ? values.split(',') : ['commercial'];
                    }
                    return ['commercial'];
                },
                
                getConfidenceClass(confidence) {
                    if (confidence >= 0.8) return 'green';
                    if (confidence >= 0.6) return 'yellow';
                    if (confidence >= 0.4) return 'orange';
                    return 'red';
                },
                
                getEntityTypeClass(type) {
                    const classes = {
                        'people': 'blue',
                        'money': 'green',
                        'actions': 'orange',
                        'items': 'purple'
                    };
                    return classes[type] || 'grey';
                },
                
                formatTimestamp(timestamp) {
                    return new Date(timestamp).toLocaleString();
                },
                
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },
                
                downloadJSON(data, filename) {
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
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