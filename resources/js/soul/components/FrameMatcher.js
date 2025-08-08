/**
 * Frame Matcher Component
 * 
 * Provides interactive frame pattern matching capabilities with real-time
 * text analysis, confidence scoring, and visual feedback for element bindings.
 */

class FrameMatcher {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with ID ${containerId} not found`);
        }
        
        // Configuration options
        this.options = {
            threshold: 0.5,
            maxCandidates: 10,
            realTimeMatching: true,
            highlightMatches: true,
            showConfidence: true,
            autoExpand: true,
            debounceDelay: 500,
            ...options
        };
        
        // State
        this.frames = [];
        this.currentInput = '';
        this.currentResults = [];
        this.selectedCandidates = [];
        this.isMatching = false;
        this.debounceTimer = null;
        
        // Event handlers
        this.eventHandlers = new Map();
        
        // UI Elements
        this.elements = {};
        
        this.initialize();
    }
    
    initialize() {
        try {
            this.createUI();
            this.bindEvents();
            this.loadAvailableFrames();
            
        } catch (error) {
            console.error('Failed to initialize FrameMatcher:', error);
            this.showError('Failed to initialize frame matcher: ' + error.message);
        }
    }
    
    createUI() {
        this.container.innerHTML = `
            <div class="frame-matcher-container">
                <!-- Input Section -->
                <div class="matcher-input-section">
                    <div class="ui form">
                        <div class="field">
                            <label>Input Text</label>
                            <div class="ui labeled input">
                                <textarea id="matcher-input" 
                                          placeholder="Enter text to analyze for frame patterns..."
                                          rows="4"></textarea>
                                <div class="ui right pointing label matcher-char-count">
                                    0 characters
                                </div>
                            </div>
                        </div>
                        
                        <div class="fields">
                            <div class="eight wide field">
                                <label>Frame Candidates</label>
                                <div id="matcher-candidates" class="ui multiple search selection dropdown">
                                    <input type="hidden" name="frame_candidates">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Select frames to test against...</div>
                                    <div class="menu"></div>
                                </div>
                            </div>
                            
                            <div class="three wide field">
                                <label>Threshold</label>
                                <div class="ui labeled input">
                                    <input type="number" id="matcher-threshold" 
                                           min="0" max="1" step="0.1" 
                                           value="${this.options.threshold}">
                                    <div class="ui basic label">%</div>
                                </div>
                            </div>
                            
                            <div class="three wide field">
                                <label>Max Results</label>
                                <input type="number" id="matcher-max-results" 
                                       min="1" max="20" 
                                       value="${this.options.maxCandidates}">
                            </div>
                            
                            <div class="two wide field">
                                <label>&nbsp;</label>
                                <button id="matcher-analyze-btn" class="ui primary button">
                                    <i class="play icon"></i>
                                    Match
                                </button>
                            </div>
                        </div>
                        
                        <!-- Options -->
                        <div class="field">
                            <div class="ui checkboxes">
                                <div class="inline field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" id="real-time-matching" 
                                               ${this.options.realTimeMatching ? 'checked' : ''}>
                                        <label for="real-time-matching">Real-time matching</label>
                                    </div>
                                </div>
                                <div class="inline field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" id="highlight-matches" 
                                               ${this.options.highlightMatches ? 'checked' : ''}>
                                        <label for="highlight-matches">Highlight matches</label>
                                    </div>
                                </div>
                                <div class="inline field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" id="auto-expand" 
                                               ${this.options.autoExpand ? 'checked' : ''}>
                                        <label for="auto-expand">Auto-expand results</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Results Section -->
                <div id="matcher-results-section" class="matcher-results-section" style="display: none;">
                    <div class="ui divider"></div>
                    
                    <div class="results-header">
                        <h4 class="ui header">
                            <i class="search icon"></i>
                            Matching Results
                            <div class="sub header" id="results-summary">No matches found</div>
                        </h4>
                        <div class="results-controls">
                            <div class="ui buttons">
                                <button id="export-results-btn" class="ui button">
                                    <i class="download icon"></i>
                                    Export
                                </button>
                                <button id="clear-results-btn" class="ui button">
                                    <i class="trash icon"></i>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results List -->
                    <div id="matcher-results-list" class="results-list">
                        <!-- Results will be populated here -->
                    </div>
                </div>
                
                <!-- Quick Examples -->
                <div class="matcher-examples">
                    <div class="ui segment">
                        <h5 class="ui header">Quick Examples</h5>
                        <div class="ui relaxed list">
                            <div class="item">
                                <a href="#" class="example-text" data-text="John buys a book from Mary for twenty dollars">
                                    Commercial transaction example
                                </a>
                            </div>
                            <div class="item">
                                <a href="#" class="example-text" data-text="The box contains several items including tools and documents">
                                    Container schema example
                                </a>
                            </div>
                            <div class="item">
                                <a href="#" class="example-text" data-text="She walked from the house to the store along the main street">
                                    Motion frame example
                                </a>
                            </div>
                            <div class="item">
                                <a href="#" class="example-text" data-text="The company acquired new software licenses from the vendor">
                                    Acquisition frame example
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loading overlay -->
                <div id="matcher-loading" class="ui active dimmer" style="display: none;">
                    <div class="ui text loader">Analyzing frames...</div>
                </div>
            </div>
        `;
        
        // Store references to key elements
        this.elements = {
            input: this.container.querySelector('#matcher-input'),
            candidates: this.container.querySelector('#matcher-candidates'),
            threshold: this.container.querySelector('#matcher-threshold'),
            maxResults: this.container.querySelector('#matcher-max-results'),
            analyzeBtn: this.container.querySelector('#matcher-analyze-btn'),
            resultsSection: this.container.querySelector('#matcher-results-section'),
            resultsList: this.container.querySelector('#matcher-results-list'),
            resultsSummary: this.container.querySelector('#results-summary'),
            charCount: this.container.querySelector('.matcher-char-count'),
            loading: this.container.querySelector('#matcher-loading'),
            realTimeCheckbox: this.container.querySelector('#real-time-matching'),
            highlightCheckbox: this.container.querySelector('#highlight-matches'),
            autoExpandCheckbox: this.container.querySelector('#auto-expand'),
            exportBtn: this.container.querySelector('#export-results-btn'),
            clearBtn: this.container.querySelector('#clear-results-btn')
        };
    }
    
    bindEvents() {
        // Input text changes
        this.elements.input.addEventListener('input', (e) => {
            this.currentInput = e.target.value;
            this.updateCharCount();
            
            if (this.options.realTimeMatching && this.currentInput.trim()) {
                this.debouncedMatch();
            }
        });
        
        // Threshold changes
        this.elements.threshold.addEventListener('change', (e) => {
            this.options.threshold = parseFloat(e.target.value);
            if (this.currentResults.length > 0) {
                this.filterResults();
            }
        });
        
        // Options checkboxes
        this.elements.realTimeCheckbox.addEventListener('change', (e) => {
            this.options.realTimeMatching = e.target.checked;
        });
        
        this.elements.highlightCheckbox.addEventListener('change', (e) => {
            this.options.highlightMatches = e.target.checked;
            if (this.currentResults.length > 0) {
                this.renderResults();
            }
        });
        
        this.elements.autoExpandCheckbox.addEventListener('change', (e) => {
            this.options.autoExpand = e.target.checked;
        });
        
        // Analyze button
        this.elements.analyzeBtn.addEventListener('click', () => {
            this.performMatching();
        });
        
        // Export and clear buttons
        this.elements.exportBtn.addEventListener('click', () => {
            this.exportResults();
        });
        
        this.elements.clearBtn.addEventListener('click', () => {
            this.clearResults();
        });
        
        // Quick examples
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('example-text')) {
                e.preventDefault();
                const text = e.target.getAttribute('data-text');
                this.elements.input.value = text;
                this.currentInput = text;
                this.updateCharCount();
                
                if (this.options.realTimeMatching) {
                    this.debouncedMatch();
                }
            }
        });
    }
    
    async loadAvailableFrames() {
        try {
            const response = await fetch('/soul/frames/api/frames');
            const data = await response.json();
            
            if (data.success) {
                this.frames = data.data.frames;
                this.populateCandidateDropdown();
            } else {
                console.error('Failed to load frames:', data.message);
            }
        } catch (error) {
            console.error('Error loading frames:', error);
        }
    }
    
    populateCandidateDropdown() {
        const menu = this.elements.candidates.querySelector('.menu');
        menu.innerHTML = '';
        
        this.frames.forEach(frame => {
            const item = document.createElement('div');
            item.className = 'item';
            item.setAttribute('data-value', frame.name);
            item.innerHTML = `
                <div class="content">
                    <div class="header">${frame.name}</div>
                    <div class="description">${frame.description || 'No description'}</div>
                </div>
            `;
            menu.appendChild(item);
        });
        
        // Initialize Fomantic UI dropdown
        $(this.elements.candidates).dropdown({
            placeholder: 'Select frames to test against...',
            fullTextSearch: true,
            forceSelection: false,
            onChange: (value, text, $selectedItem) => {
                this.selectedCandidates = value ? value.split(',') : [];
                if (this.options.realTimeMatching && this.currentInput.trim()) {
                    this.debouncedMatch();
                }
            }
        });
    }
    
    updateCharCount() {
        const count = this.currentInput.length;
        this.elements.charCount.textContent = `${count} character${count !== 1 ? 's' : ''}`;
    }
    
    debouncedMatch() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.performMatching();
        }, this.options.debounceDelay);
    }
    
    async performMatching() {
        if (!this.currentInput.trim()) {
            this.showError('Please enter some text to analyze');
            return;
        }
        
        this.isMatching = true;
        this.showLoading(true);
        this.elements.analyzeBtn.classList.add('loading');
        
        try {
            const candidates = this.selectedCandidates.length > 0 ? 
                this.selectedCandidates : 
                this.frames.map(f => f.name);
            
            const response = await fetch('/soul/frames/api/match', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    input: { text: this.currentInput },
                    frame_candidates: candidates,
                    threshold: parseFloat(this.elements.threshold.value),
                    context: this.extractContext()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.currentResults = data.data.matches || [];
                this.renderResults();
                this.emit('matchingComplete', {
                    input: this.currentInput,
                    results: this.currentResults
                });
            } else {
                this.showError('Frame matching failed: ' + data.message);
            }
            
        } catch (error) {
            console.error('Frame matching error:', error);
            this.showError('Frame matching failed: ' + error.message);
        } finally {
            this.isMatching = false;
            this.showLoading(false);
            this.elements.analyzeBtn.classList.remove('loading');
        }
    }
    
    renderResults() {
        if (this.currentResults.length === 0) {
            this.elements.resultsSection.style.display = 'none';
            return;
        }
        
        // Filter results by threshold
        const filteredResults = this.currentResults.filter(
            result => result.confidence >= this.options.threshold
        );
        
        // Sort by confidence descending
        filteredResults.sort((a, b) => b.confidence - a.confidence);
        
        // Update summary
        this.elements.resultsSummary.textContent = 
            `${filteredResults.length} frame${filteredResults.length !== 1 ? 's' : ''} matched (${this.currentResults.length} total)`;
        
        // Render results list
        this.elements.resultsList.innerHTML = '';
        
        filteredResults.forEach((result, index) => {
            const resultCard = this.createResultCard(result, index);
            this.elements.resultsList.appendChild(resultCard);
        });
        
        this.elements.resultsSection.style.display = 'block';
        
        // Auto-expand first result if enabled
        if (this.options.autoExpand && filteredResults.length > 0) {
            setTimeout(() => {
                const firstAccordion = this.elements.resultsList.querySelector('.ui.accordion');
                if (firstAccordion) {
                    $(firstAccordion).accordion('open', 0);
                }
            }, 100);
        }
    }
    
    createResultCard(result, index) {
        const card = document.createElement('div');
        card.className = 'ui card result-card';
        
        const confidenceClass = this.getConfidenceClass(result.confidence);
        const confidencePercent = Math.round(result.confidence * 100);
        
        card.innerHTML = `
            <div class="content">
                <div class="header">
                    <div class="result-header">
                        <div class="frame-name">
                            ${result.frame_type}
                            <div class="ui mini ${confidenceClass} label confidence-label">
                                ${confidencePercent}%
                            </div>
                        </div>
                        <div class="result-actions">
                            <button class="ui mini button visualize-btn" 
                                    data-frame="${result.frame_type}">
                                <i class="eye icon"></i>
                                Visualize
                            </button>
                            <button class="ui mini button instantiate-btn" 
                                    data-frame="${result.frame_type}">
                                <i class="clone icon"></i>
                                Instantiate
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="description">
                    <div class="ui accordion">
                        <div class="title">
                            <i class="dropdown icon"></i>
                            Match Details
                        </div>
                        <div class="content">
                            ${this.createMatchDetails(result)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Bind events for this card
        this.bindResultCardEvents(card, result);
        
        return card;
    }
    
    createMatchDetails(result) {
        let details = '';
        
        // Matched elements
        if (result.matched_elements && result.matched_elements.length > 0) {
            details += `
                <div class="match-section">
                    <h5 class="ui green header">
                        <i class="check icon"></i>
                        Matched Elements
                    </h5>
                    <div class="ui green labels">
                        ${result.matched_elements.map(element => 
                            `<div class="ui label">${element}</div>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        // Missing elements
        if (result.missing_elements && result.missing_elements.length > 0) {
            details += `
                <div class="match-section">
                    <h5 class="ui red header">
                        <i class="times icon"></i>
                        Missing Elements
                    </h5>
                    <div class="ui red labels">
                        ${result.missing_elements.map(element => 
                            `<div class="ui label">${element}</div>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        // Confidence breakdown
        details += `
            <div class="match-section">
                <h5 class="ui header">
                    <i class="chart bar icon"></i>
                    Confidence Breakdown
                </h5>
                <div class="ui small progress" data-percent="${Math.round(result.confidence * 100)}">
                    <div class="bar"></div>
                    <div class="label">Overall Match: ${Math.round(result.confidence * 100)}%</div>
                </div>
            </div>
        `;
        
        // Highlighted text (if enabled)
        if (this.options.highlightMatches) {
            details += `
                <div class="match-section">
                    <h5 class="ui header">
                        <i class="paint brush icon"></i>
                        Highlighted Text
                    </h5>
                    <div class="highlighted-text">
                        ${this.highlightTextMatches(this.currentInput, result)}
                    </div>
                </div>
            `;
        }
        
        return details;
    }
    
    bindResultCardEvents(card, result) {
        // Visualize button
        const visualizeBtn = card.querySelector('.visualize-btn');
        if (visualizeBtn) {
            visualizeBtn.addEventListener('click', () => {
                this.emit('visualizeFrame', result);
            });
        }
        
        // Instantiate button  
        const instantiateBtn = card.querySelector('.instantiate-btn');
        if (instantiateBtn) {
            instantiateBtn.addEventListener('click', () => {
                this.emit('instantiateFrame', result);
            });
        }
        
        // Initialize accordion and progress bar
        $(card).find('.ui.accordion').accordion();
        $(card).find('.ui.progress').progress();
    }
    
    highlightTextMatches(text, result) {
        if (!result.matched_elements || result.matched_elements.length === 0) {
            return text;
        }
        
        let highlightedText = text;
        
        // Simple highlighting - in practice this would be more sophisticated
        result.matched_elements.forEach(element => {
            const regex = new RegExp(`\\b${element}\\b`, 'gi');
            highlightedText = highlightedText.replace(regex, 
                `<mark class="ui yellow">$&</mark>`);
        });
        
        return highlightedText;
    }
    
    getConfidenceClass(confidence) {
        if (confidence >= 0.8) return 'green';
        if (confidence >= 0.6) return 'yellow'; 
        if (confidence >= 0.4) return 'orange';
        return 'red';
    }
    
    filterResults() {
        this.renderResults();
    }
    
    extractContext() {
        // Extract context hints from the input text
        const context = [];
        
        // Commercial terms
        if (/\b(buy|sell|purchase|pay|money|price|cost|dollar)\b/i.test(this.currentInput)) {
            context.push('commercial');
        }
        
        // Motion terms
        if (/\b(walk|run|move|go|come|travel)\b/i.test(this.currentInput)) {
            context.push('motion');
        }
        
        // Container terms
        if (/\b(box|container|hold|inside|contain)\b/i.test(this.currentInput)) {
            context.push('spatial');
        }
        
        return context;
    }
    
    exportResults() {
        if (this.currentResults.length === 0) {
            this.showError('No results to export');
            return;
        }
        
        const exportData = {
            input_text: this.currentInput,
            threshold: this.options.threshold,
            timestamp: new Date().toISOString(),
            results: this.currentResults
        };
        
        const dataStr = JSON.stringify(exportData, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `frame_matching_results_${Date.now()}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    clearResults() {
        this.currentResults = [];
        this.elements.resultsSection.style.display = 'none';
        this.elements.resultsList.innerHTML = '';
        this.emit('resultsCleared');
    }
    
    showLoading(show) {
        this.elements.loading.style.display = show ? 'block' : 'none';
    }
    
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    
    // Event handling
    on(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(handler);
    }
    
    emit(event, data = null) {
        const handlers = this.eventHandlers.get(event);
        if (handlers) {
            handlers.forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in event handler for ${event}:`, error);
                }
            });
        }
    }
    
    showError(message) {
        console.error('FrameMatcher Error:', message);
        
        if (typeof $ !== 'undefined' && $.fn.toast) {
            $("body").toast({
                message: message,
                class: "error",
                showIcon: "exclamation triangle",
                displayTime: 5000,
                position: "top center"
            });
        } else {
            alert(message);
        }
    }
    
    // Public API methods
    setInput(text) {
        this.elements.input.value = text;
        this.currentInput = text;
        this.updateCharCount();
    }
    
    setThreshold(threshold) {
        this.options.threshold = threshold;
        this.elements.threshold.value = threshold;
    }
    
    setCandidates(candidates) {
        this.selectedCandidates = candidates;
        $(this.elements.candidates).dropdown('set selected', candidates);
    }
    
    getResults() {
        return this.currentResults;
    }
    
    destroy() {
        clearTimeout(this.debounceTimer);
        this.eventHandlers.clear();
        
        // Destroy Fomantic UI components
        $(this.elements.candidates).dropdown('destroy');
        this.container.querySelectorAll('.ui.accordion').forEach(accordion => {
            $(accordion).accordion('destroy');
        });
    }
}

// Make FrameMatcher available globally
if (typeof window !== 'undefined') {
    window.FrameMatcher = FrameMatcher;
}

// Export for ES6 modules and CommonJS
export default FrameMatcher;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FrameMatcher;
}