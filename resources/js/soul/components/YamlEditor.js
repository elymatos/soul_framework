/**
 * SOUL Framework - YAML Editor Component
 * 
 * A sophisticated YAML editor specifically designed for SOUL Framework knowledge files.
 * Provides both visual form-based editing and code editor with syntax highlighting,
 * validation, auto-completion, and real-time preview capabilities.
 * 
 * @requires CodeMirror
 * @requires js-yaml (for parsing)
 */

class YamlEditor {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with ID '${containerId}' not found`);
        }
        
        // Configuration options
        this.options = {
            mode: 'visual', // 'visual', 'code', 'split'
            theme: 'material',
            autoValidate: true,
            autoFormat: true,
            autosave: false,
            autosaveInterval: 30000, // 30 seconds
            livePreview: true,
            showLineNumbers: true,
            enableFolding: true,
            enableLinting: true,
            enableAutocompletion: true,
            ...options
        };
        
        // State
        this.currentFile = null;
        this.content = '';
        this.parsedYaml = {};
        this.hasUnsavedChanges = false;
        this.validationResults = { valid: true, errors: [], warnings: [] };
        this.autocompleteSuggestions = [];
        
        // Editor instances
        this.codeEditor = null;
        this.visualEditor = null;
        
        // Event listeners
        this.eventListeners = {};
        
        this.initialize();
    }
    
    initialize() {
        this.createLayout();
        this.initializeCodeEditor();
        this.initializeVisualEditor();
        this.setupEventListeners();
        this.loadAutocompleteSuggestions();
        
        if (this.options.autosave) {
            this.setupAutosave();
        }
    }
    
    createLayout() {
        this.container.innerHTML = `
            <div class="yaml-editor-container">
                <!-- Editor Toolbar -->
                <div class="yaml-editor-toolbar">
                    <div class="toolbar-left">
                        <div class="editor-mode-selector">
                            <button class="mode-btn active" data-mode="visual">
                                <i class="icon edit"></i> Visual
                            </button>
                            <button class="mode-btn" data-mode="code">
                                <i class="icon code"></i> Code
                            </button>
                            <button class="mode-btn" data-mode="split">
                                <i class="icon columns"></i> Split
                            </button>
                        </div>
                    </div>
                    
                    <div class="toolbar-right">
                        <div class="editor-status">
                            <span class="validation-status" id="validation-status">
                                <i class="icon checkmark green"></i> Valid
                            </span>
                            <span class="changes-status" id="changes-status" style="display: none;">
                                <i class="icon warning orange"></i> Unsaved
                            </span>
                        </div>
                        
                        <div class="editor-actions">
                            <button class="action-btn" id="format-btn" title="Format YAML">
                                <i class="icon magic"></i>
                            </button>
                            <button class="action-btn" id="validate-btn" title="Validate">
                                <i class="icon checkmark"></i>
                            </button>
                            <button class="action-btn" id="preview-btn" title="Preview Changes">
                                <i class="icon eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Editor Content -->
                <div class="yaml-editor-content">
                    <!-- Visual Editor -->
                    <div class="visual-editor-pane" id="visual-editor-pane">
                        <div class="visual-editor-tabs">
                            <button class="tab-btn active" data-tab="metadata">Metadata</button>
                            <button class="tab-btn" data-tab="concepts">Concepts</button>
                            <button class="tab-btn" data-tab="relationships">Relationships</button>
                            <button class="tab-btn" data-tab="agents">Agents</button>
                        </div>
                        
                        <div class="visual-editor-content">
                            <!-- Metadata Tab -->
                            <div class="tab-content active" id="metadata-tab">
                                <div class="form-section">
                                    <h4 class="section-title">File Metadata</h4>
                                    <div class="form-grid">
                                        <div class="form-field">
                                            <label>Title</label>
                                            <input type="text" id="metadata-title" placeholder="Knowledge file title">
                                        </div>
                                        <div class="form-field">
                                            <label>Version</label>
                                            <input type="text" id="metadata-version" placeholder="1.0" value="1.0">
                                        </div>
                                        <div class="form-field full-width">
                                            <label>Description</label>
                                            <textarea id="metadata-description" placeholder="Describe the content of this file"></textarea>
                                        </div>
                                        <div class="form-field">
                                            <label>Author</label>
                                            <input type="text" id="metadata-author" placeholder="Author name">
                                        </div>
                                        <div class="form-field">
                                            <label>Domain</label>
                                            <select id="metadata-domain">
                                                <option value="">Select domain</option>
                                                <option value="general">General</option>
                                                <option value="spatial">Spatial</option>
                                                <option value="social">Social</option>
                                                <option value="commerce">Commerce</option>
                                                <option value="language">Language</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Concepts Tab -->
                            <div class="tab-content" id="concepts-tab">
                                <div class="section-header">
                                    <h4 class="section-title">Concepts</h4>
                                    <button class="add-btn" id="add-concept-btn">
                                        <i class="icon plus"></i> Add Concept
                                    </button>
                                </div>
                                <div class="concepts-list" id="concepts-list">
                                    <!-- Concepts will be rendered here -->
                                </div>
                            </div>
                            
                            <!-- Relationships Tab -->
                            <div class="tab-content" id="relationships-tab">
                                <div class="section-header">
                                    <h4 class="section-title">Relationships</h4>
                                    <button class="add-btn" id="add-relationship-btn">
                                        <i class="icon plus"></i> Add Relationship
                                    </button>
                                </div>
                                <div class="relationships-list" id="relationships-list">
                                    <!-- Relationships will be rendered here -->
                                </div>
                            </div>
                            
                            <!-- Agents Tab -->
                            <div class="tab-content" id="agents-tab">
                                <div class="section-header">
                                    <h4 class="section-title">Procedural Agents</h4>
                                    <button class="add-btn" id="add-agent-btn">
                                        <i class="icon plus"></i> Add Agent
                                    </button>
                                </div>
                                <div class="agents-list" id="agents-list">
                                    <!-- Agents will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Code Editor -->
                    <div class="code-editor-pane" id="code-editor-pane">
                        <textarea id="code-editor-textarea"></textarea>
                    </div>
                </div>
            </div>
        `;
    }
    
    initializeCodeEditor() {
        const textarea = this.container.querySelector('#code-editor-textarea');
        
        this.codeEditor = CodeMirror.fromTextArea(textarea, {
            mode: 'yaml',
            theme: this.options.theme,
            lineNumbers: this.options.showLineNumbers,
            lineWrapping: true,
            foldGutter: this.options.enableFolding,
            gutters: this.options.enableFolding ? 
                ['CodeMirror-linenumbers', 'CodeMirror-foldgutter', 'CodeMirror-lint-markers'] :
                ['CodeMirror-linenumbers', 'CodeMirror-lint-markers'],
            lint: this.options.enableLinting,
            autoCloseBrackets: true,
            matchBrackets: true,
            showCursorWhenSelecting: true,
            extraKeys: {
                'Ctrl-Space': 'autocomplete',
                'Ctrl-S': () => this.save(),
                'Ctrl-Shift-F': () => this.format(),
                'Ctrl-/': 'toggleComment',
                'F11': () => this.toggleFullscreen(),
                'Esc': () => this.exitFullscreen()
            }
        });
        
        // Setup auto-completion
        if (this.options.enableAutocompletion) {
            this.setupAutocompletion();
        }
        
        // Listen for changes
        this.codeEditor.on('change', (editor, change) => {
            if (change.origin !== 'setValue') {
                this.handleContentChange();
            }
        });
        
        // Setup real-time validation
        if (this.options.autoValidate) {
            this.codeEditor.on('change', () => {
                clearTimeout(this.validationTimeout);
                this.validationTimeout = setTimeout(() => {
                    this.validateContent();
                }, 1000);
            });
        }
    }
    
    initializeVisualEditor() {
        this.visualEditor = {
            metadata: {},
            concepts: [],
            relationships: [],
            agents: []
        };
        
        this.setupVisualEditorTabs();
        this.setupVisualEditorForms();
    }
    
    setupVisualEditorTabs() {
        const tabButtons = this.container.querySelectorAll('.tab-btn');
        const tabContents = this.container.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.dataset.tab;
                
                // Update active states
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                button.classList.add('active');
                this.container.querySelector(`#${tabName}-tab`).classList.add('active');
                
                // Sync visual to code if needed
                if (this.options.livePreview) {
                    this.syncVisualToCode();
                }
            });
        });
    }
    
    setupVisualEditorForms() {
        // Metadata form listeners
        const metadataFields = ['title', 'version', 'description', 'author', 'domain'];
        metadataFields.forEach(field => {
            const element = this.container.querySelector(`#metadata-${field}`);
            if (element) {
                element.addEventListener('input', () => {
                    this.visualEditor.metadata[field] = element.value;
                    this.handleContentChange();
                    if (this.options.livePreview) {
                        this.syncVisualToCode();
                    }
                });
            }
        });
        
        // Add button listeners
        this.container.querySelector('#add-concept-btn')?.addEventListener('click', () => {
            this.addConcept();
        });
        
        this.container.querySelector('#add-relationship-btn')?.addEventListener('click', () => {
            this.addRelationship();
        });
        
        this.container.querySelector('#add-agent-btn')?.addEventListener('click', () => {
            this.addAgent();
        });
    }
    
    setupEventListeners() {
        // Mode selector
        this.container.querySelectorAll('.mode-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                this.setMode(mode);
            });
        });
        
        // Toolbar actions
        this.container.querySelector('#format-btn')?.addEventListener('click', () => {
            this.format();
        });
        
        this.container.querySelector('#validate-btn')?.addEventListener('click', () => {
            this.validateContent();
        });
        
        this.container.querySelector('#preview-btn')?.addEventListener('click', () => {
            this.previewChanges();
        });
    }
    
    setupAutocompletion() {
        // Custom autocomplete hint function for YAML
        CodeMirror.registerHelper('hint', 'yaml', (editor, options) => {
            const cursor = editor.getCursor();
            const line = editor.getLine(cursor.line);
            const start = cursor.ch;
            const end = cursor.ch;
            
            // Get current context (what we're completing)
            const context = this.getCompletionContext(line, cursor.ch);
            
            let suggestions = [];
            
            switch (context.type) {
                case 'concept-name':
                    suggestions = this.autocompleteSuggestions.concepts;
                    break;
                case 'relationship-type':
                    suggestions = this.autocompleteSuggestions.relationshipTypes;
                    break;
                case 'concept-type':
                    suggestions = this.autocompleteSuggestions.conceptTypes;
                    break;
                case 'domain':
                    suggestions = this.autocompleteSuggestions.domains;
                    break;
                case 'service-reference':
                    suggestions = this.autocompleteSuggestions.serviceReferences;
                    break;
                default:
                    suggestions = this.autocompleteSuggestions.general;
            }
            
            return {
                list: suggestions.filter(s => s.toLowerCase().includes(context.partial.toLowerCase())),
                from: CodeMirror.Pos(cursor.line, start - context.partial.length),
                to: CodeMirror.Pos(cursor.line, end)
            };
        });
    }
    
    getCompletionContext(line, ch) {
        // Analyze the current line to determine what we're completing
        const beforeCursor = line.substring(0, ch);
        const words = beforeCursor.split(/\s+/);
        const lastWord = words[words.length - 1] || '';
        
        // Determine context based on YAML structure
        if (beforeCursor.includes('name:')) {
            return { type: 'concept-name', partial: lastWord };
        } else if (beforeCursor.includes('type:')) {
            if (beforeCursor.includes('relationships:')) {
                return { type: 'relationship-type', partial: lastWord };
            } else {
                return { type: 'concept-type', partial: lastWord };
            }
        } else if (beforeCursor.includes('domain:')) {
            return { type: 'domain', partial: lastWord };
        } else if (beforeCursor.includes('code_reference:')) {
            return { type: 'service-reference', partial: lastWord };
        } else {
            return { type: 'general', partial: lastWord };
        }
    }
    
    loadAutocompleteSuggestions() {
        // Load autocomplete suggestions from the server or define static ones
        this.autocompleteSuggestions = {
            concepts: [], // Will be loaded dynamically
            relationshipTypes: ['IS_A', 'PART_OF', 'CAUSES', 'ACTIVATES', 'SCHEMA_ACTIVATES'],
            conceptTypes: ['concept', 'frame', 'image_schema', 'entity', 'primitive'],
            domains: ['general', 'spatial', 'social', 'commerce', 'language', 'causal'],
            serviceReferences: [
                'ImageSchemaService::activateContainerSchema',
                'ImageSchemaService::activatePathSchema',
                'ImageSchemaService::activateForceSchema',
                'FrameService::matchFrame',
                'FrameService::instantiateFrame'
            ],
            general: [
                'metadata:', 'concepts:', 'relationships:', 'procedural_agents:',
                'name:', 'labels:', 'properties:', 'description:', 'type:', 'domain:',
                'from:', 'to:', 'strength:', 'code_reference:', 'priority:'
            ]
        };
        
        // Load existing concepts for autocomplete
        this.loadExistingConcepts();
    }
    
    async loadExistingConcepts() {
        try {
            // This would fetch existing concepts from the server
            // For now, we'll use a placeholder
            const response = await fetch('/soul/knowledge/file-tree');
            if (response.ok) {
                // Process the response to extract concept names
                // Implementation depends on your API structure
            }
        } catch (error) {
            console.warn('Failed to load existing concepts for autocomplete:', error);
        }
    }
    
    // Content Management Methods
    
    loadContent(content, filename = null) {
        this.currentFile = filename;
        this.content = content || this.getDefaultYamlTemplate();
        this.hasUnsavedChanges = false;
        
        // Load into code editor
        this.codeEditor.setValue(this.content);
        
        // Parse and load into visual editor
        this.parseYaml();
        this.updateVisualEditor();
        this.updateUI();
    }
    
    getDefaultYamlTemplate() {
        return `metadata:
  title: ""
  version: "1.0"
  description: ""
  author: ""
  domain: "general"

concepts:
  - name: ""
    labels: ["Concept"]
    properties:
      type: "concept"
      description: ""
      domain: "general"

relationships:
  - from: ""
    to: ""
    type: ""
    properties:
      strength: 1.0

procedural_agents:
  - name: ""
    code_reference: ""
    description: ""
    priority: 1
`;
    }
    
    parseYaml() {
        try {
            this.parsedYaml = jsyaml.load(this.content) || {};
            this.validationResults = { valid: true, errors: [], warnings: [] };
        } catch (error) {
            this.parsedYaml = {};
            this.validationResults = {
                valid: false,
                errors: [`YAML parsing error: ${error.message}`],
                warnings: []
            };
        }
        
        this.updateValidationStatus();
    }
    
    updateVisualEditor() {
        if (!this.parsedYaml) return;
        
        // Update metadata
        this.visualEditor.metadata = this.parsedYaml.metadata || {};
        this.updateMetadataForm();
        
        // Update concepts
        this.visualEditor.concepts = this.parsedYaml.concepts || [];
        this.renderConcepts();
        
        // Update relationships
        this.visualEditor.relationships = this.parsedYaml.relationships || [];
        this.renderRelationships();
        
        // Update agents
        this.visualEditor.agents = this.parsedYaml.procedural_agents || [];
        this.renderAgents();
    }
    
    updateMetadataForm() {
        const fields = ['title', 'version', 'description', 'author', 'domain'];
        fields.forEach(field => {
            const element = this.container.querySelector(`#metadata-${field}`);
            if (element) {
                element.value = this.visualEditor.metadata[field] || '';
            }
        });
    }
    
    renderConcepts() {
        const container = this.container.querySelector('#concepts-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.visualEditor.concepts.forEach((concept, index) => {
            const conceptElement = this.createConceptElement(concept, index);
            container.appendChild(conceptElement);
        });
    }
    
    createConceptElement(concept, index) {
        const element = document.createElement('div');
        element.className = 'concept-item';
        element.innerHTML = `
            <div class="item-header">
                <input type="text" class="concept-name" value="${concept.name || ''}" placeholder="Concept Name" data-index="${index}">
                <button class="remove-btn" data-index="${index}">
                    <i class="icon trash"></i>
                </button>
            </div>
            <div class="item-details">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Type</label>
                        <select class="concept-type" data-index="${index}">
                            <option value="concept" ${concept.properties?.type === 'concept' ? 'selected' : ''}>Concept</option>
                            <option value="frame" ${concept.properties?.type === 'frame' ? 'selected' : ''}>Frame</option>
                            <option value="image_schema" ${concept.properties?.type === 'image_schema' ? 'selected' : ''}>Image Schema</option>
                            <option value="entity" ${concept.properties?.type === 'entity' ? 'selected' : ''}>Entity</option>
                            <option value="primitive" ${concept.properties?.type === 'primitive' ? 'selected' : ''}>Primitive</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Domain</label>
                        <select class="concept-domain" data-index="${index}">
                            <option value="general" ${concept.properties?.domain === 'general' ? 'selected' : ''}>General</option>
                            <option value="spatial" ${concept.properties?.domain === 'spatial' ? 'selected' : ''}>Spatial</option>
                            <option value="social" ${concept.properties?.domain === 'social' ? 'selected' : ''}>Social</option>
                            <option value="commerce" ${concept.properties?.domain === 'commerce' ? 'selected' : ''}>Commerce</option>
                            <option value="language" ${concept.properties?.domain === 'language' ? 'selected' : ''}>Language</option>
                        </select>
                    </div>
                </div>
                <div class="form-field full-width">
                    <label>Description</label>
                    <textarea class="concept-description" data-index="${index}" placeholder="Concept description">${concept.properties?.description || ''}</textarea>
                </div>
                <div class="form-field full-width">
                    <label>Labels</label>
                    <input type="text" class="concept-labels" data-index="${index}" placeholder="Label1, Label2, Label3" value="${(concept.labels || []).join(', ')}">
                </div>
            </div>
        `;
        
        // Add event listeners
        this.addConceptEventListeners(element, index);
        
        return element;
    }
    
    addConceptEventListeners(element, index) {
        element.querySelector('.concept-name').addEventListener('input', (e) => {
            this.visualEditor.concepts[index].name = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.concept-type').addEventListener('change', (e) => {
            if (!this.visualEditor.concepts[index].properties) {
                this.visualEditor.concepts[index].properties = {};
            }
            this.visualEditor.concepts[index].properties.type = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.concept-domain').addEventListener('change', (e) => {
            if (!this.visualEditor.concepts[index].properties) {
                this.visualEditor.concepts[index].properties = {};
            }
            this.visualEditor.concepts[index].properties.domain = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.concept-description').addEventListener('input', (e) => {
            if (!this.visualEditor.concepts[index].properties) {
                this.visualEditor.concepts[index].properties = {};
            }
            this.visualEditor.concepts[index].properties.description = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.concept-labels').addEventListener('input', (e) => {
            const labels = e.target.value.split(',').map(l => l.trim()).filter(l => l);
            this.visualEditor.concepts[index].labels = labels;
            this.handleContentChange();
        });
        
        element.querySelector('.remove-btn').addEventListener('click', () => {
            this.removeConcept(index);
        });
    }
    
    renderRelationships() {
        const container = this.container.querySelector('#relationships-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.visualEditor.relationships.forEach((relationship, index) => {
            const relationshipElement = this.createRelationshipElement(relationship, index);
            container.appendChild(relationshipElement);
        });
    }
    
    createRelationshipElement(relationship, index) {
        const element = document.createElement('div');
        element.className = 'relationship-item';
        
        // Get available concepts for dropdowns
        const conceptNames = this.visualEditor.concepts.map(c => c.name).filter(n => n);
        const conceptOptions = conceptNames.map(name => 
            `<option value="${name}" ${relationship.from === name || relationship.to === name ? 'selected' : ''}>${name}</option>`
        ).join('');
        
        element.innerHTML = `
            <div class="item-header">
                <span>Relationship</span>
                <button class="remove-btn" data-index="${index}">
                    <i class="icon trash"></i>
                </button>
            </div>
            <div class="item-details">
                <div class="form-grid">
                    <div class="form-field">
                        <label>From</label>
                        <select class="relationship-from" data-index="${index}">
                            <option value="">Select concept</option>
                            ${conceptOptions}
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Type</label>
                        <select class="relationship-type" data-index="${index}">
                            <option value="">Select type</option>
                            <option value="IS_A" ${relationship.type === 'IS_A' ? 'selected' : ''}>IS_A</option>
                            <option value="PART_OF" ${relationship.type === 'PART_OF' ? 'selected' : ''}>PART_OF</option>
                            <option value="CAUSES" ${relationship.type === 'CAUSES' ? 'selected' : ''}>CAUSES</option>
                            <option value="ACTIVATES" ${relationship.type === 'ACTIVATES' ? 'selected' : ''}>ACTIVATES</option>
                            <option value="SCHEMA_ACTIVATES" ${relationship.type === 'SCHEMA_ACTIVATES' ? 'selected' : ''}>SCHEMA_ACTIVATES</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>To</label>
                        <select class="relationship-to" data-index="${index}">
                            <option value="">Select concept</option>
                            ${conceptOptions}
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Strength</label>
                        <input type="number" class="relationship-strength" data-index="${index}" min="0" max="1" step="0.1" value="${relationship.properties?.strength || 1.0}">
                    </div>
                </div>
            </div>
        `;
        
        // Set selected values
        element.querySelector('.relationship-from').value = relationship.from || '';
        element.querySelector('.relationship-to').value = relationship.to || '';
        
        // Add event listeners
        this.addRelationshipEventListeners(element, index);
        
        return element;
    }
    
    addRelationshipEventListeners(element, index) {
        element.querySelector('.relationship-from').addEventListener('change', (e) => {
            this.visualEditor.relationships[index].from = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.relationship-type').addEventListener('change', (e) => {
            this.visualEditor.relationships[index].type = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.relationship-to').addEventListener('change', (e) => {
            this.visualEditor.relationships[index].to = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.relationship-strength').addEventListener('input', (e) => {
            if (!this.visualEditor.relationships[index].properties) {
                this.visualEditor.relationships[index].properties = {};
            }
            this.visualEditor.relationships[index].properties.strength = parseFloat(e.target.value);
            this.handleContentChange();
        });
        
        element.querySelector('.remove-btn').addEventListener('click', () => {
            this.removeRelationship(index);
        });
    }
    
    renderAgents() {
        const container = this.container.querySelector('#agents-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.visualEditor.agents.forEach((agent, index) => {
            const agentElement = this.createAgentElement(agent, index);
            container.appendChild(agentElement);
        });
    }
    
    createAgentElement(agent, index) {
        const element = document.createElement('div');
        element.className = 'agent-item';
        element.innerHTML = `
            <div class="item-header">
                <input type="text" class="agent-name" value="${agent.name || ''}" placeholder="Agent Name" data-index="${index}">
                <button class="remove-btn" data-index="${index}">
                    <i class="icon trash"></i>
                </button>
            </div>
            <div class="item-details">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Code Reference</label>
                        <input type="text" class="agent-code-reference" data-index="${index}" placeholder="ServiceClass::methodName" value="${agent.code_reference || ''}">
                    </div>
                    <div class="form-field">
                        <label>Priority</label>
                        <input type="number" class="agent-priority" data-index="${index}" min="1" max="10" value="${agent.priority || 1}">
                    </div>
                </div>
                <div class="form-field full-width">
                    <label>Description</label>
                    <textarea class="agent-description" data-index="${index}" placeholder="Agent description">${agent.description || ''}</textarea>
                </div>
            </div>
        `;
        
        // Add event listeners
        this.addAgentEventListeners(element, index);
        
        return element;
    }
    
    addAgentEventListeners(element, index) {
        element.querySelector('.agent-name').addEventListener('input', (e) => {
            this.visualEditor.agents[index].name = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.agent-code-reference').addEventListener('input', (e) => {
            this.visualEditor.agents[index].code_reference = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.agent-priority').addEventListener('input', (e) => {
            this.visualEditor.agents[index].priority = parseInt(e.target.value);
            this.handleContentChange();
        });
        
        element.querySelector('.agent-description').addEventListener('input', (e) => {
            this.visualEditor.agents[index].description = e.target.value;
            this.handleContentChange();
        });
        
        element.querySelector('.remove-btn').addEventListener('click', () => {
            this.removeAgent(index);
        });
    }
    
    // Visual Editor Actions
    
    addConcept() {
        const newConcept = {
            name: '',
            labels: ['Concept'],
            properties: {
                type: 'concept',
                description: '',
                domain: 'general'
            }
        };
        
        this.visualEditor.concepts.push(newConcept);
        this.renderConcepts();
        this.handleContentChange();
        
        if (this.options.livePreview) {
            this.syncVisualToCode();
        }
    }
    
    removeConcept(index) {
        if (confirm('Are you sure you want to remove this concept?')) {
            this.visualEditor.concepts.splice(index, 1);
            this.renderConcepts();
            this.renderRelationships(); // Re-render relationships to update dropdowns
            this.handleContentChange();
            
            if (this.options.livePreview) {
                this.syncVisualToCode();
            }
        }
    }
    
    addRelationship() {
        const newRelationship = {
            from: '',
            to: '',
            type: '',
            properties: {
                strength: 1.0
            }
        };
        
        this.visualEditor.relationships.push(newRelationship);
        this.renderRelationships();
        this.handleContentChange();
        
        if (this.options.livePreview) {
            this.syncVisualToCode();
        }
    }
    
    removeRelationship(index) {
        if (confirm('Are you sure you want to remove this relationship?')) {
            this.visualEditor.relationships.splice(index, 1);
            this.renderRelationships();
            this.handleContentChange();
            
            if (this.options.livePreview) {
                this.syncVisualToCode();
            }
        }
    }
    
    addAgent() {
        const newAgent = {
            name: '',
            code_reference: '',
            description: '',
            priority: 1
        };
        
        this.visualEditor.agents.push(newAgent);
        this.renderAgents();
        this.handleContentChange();
        
        if (this.options.livePreview) {
            this.syncVisualToCode();
        }
    }
    
    removeAgent(index) {
        if (confirm('Are you sure you want to remove this agent?')) {
            this.visualEditor.agents.splice(index, 1);
            this.renderAgents();
            this.handleContentChange();
            
            if (this.options.livePreview) {
                this.syncVisualToCode();
            }
        }
    }
    
    // Synchronization Methods
    
    syncVisualToCode() {
        const yamlData = {
            metadata: this.visualEditor.metadata,
            concepts: this.visualEditor.concepts,
            relationships: this.visualEditor.relationships,
            procedural_agents: this.visualEditor.agents
        };
        
        try {
            const yamlContent = jsyaml.dump(yamlData, {
                indent: 2,
                lineWidth: 80,
                noRefs: true,
                sortKeys: false
            });
            
            this.codeEditor.setValue(yamlContent);
            this.content = yamlContent;
        } catch (error) {
            console.error('Failed to sync visual editor to code:', error);
        }
    }
    
    syncCodeToVisual() {
        this.content = this.codeEditor.getValue();
        this.parseYaml();
        
        if (this.parsedYaml && Object.keys(this.parsedYaml).length > 0) {
            this.updateVisualEditor();
        }
    }
    
    // Editor Mode Management
    
    setMode(mode) {
        const modeButtons = this.container.querySelectorAll('.mode-btn');
        const visualPane = this.container.querySelector('#visual-editor-pane');
        const codePane = this.container.querySelector('#code-editor-pane');
        
        // Update button states
        modeButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
        
        // Update pane visibility
        switch (mode) {
            case 'visual':
                visualPane.style.display = 'block';
                codePane.style.display = 'none';
                this.syncCodeToVisual();
                break;
            case 'code':
                visualPane.style.display = 'none';
                codePane.style.display = 'block';
                this.codeEditor.refresh();
                break;
            case 'split':
                visualPane.style.display = 'block';
                codePane.style.display = 'block';
                visualPane.style.width = '50%';
                codePane.style.width = '50%';
                this.codeEditor.refresh();
                break;
        }
        
        this.options.mode = mode;
    }
    
    // Content Actions
    
    handleContentChange() {
        this.hasUnsavedChanges = true;
        this.updateUI();
        
        // Emit change event
        this.emit('content-changed', {
            content: this.getContent(),
            hasUnsavedChanges: this.hasUnsavedChanges
        });
    }
    
    getContent() {
        if (this.options.mode === 'visual') {
            this.syncVisualToCode();
        }
        
        return this.codeEditor.getValue();
    }
    
    async validateContent() {
        const content = this.getContent();
        
        try {
            const response = await fetch('/soul/knowledge/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });
            
            this.validationResults = await response.json();
        } catch (error) {
            this.validationResults = {
                valid: false,
                errors: ['Validation request failed: ' + error.message],
                warnings: []
            };
        }
        
        this.updateValidationStatus();
        
        // Emit validation event
        this.emit('validation-complete', this.validationResults);
    }
    
    format() {
        try {
            const content = this.getContent();
            const parsed = jsyaml.load(content);
            const formatted = jsyaml.dump(parsed, {
                indent: 2,
                lineWidth: 80,
                noRefs: true,
                sortKeys: false
            });
            
            this.codeEditor.setValue(formatted);
            this.handleContentChange();
            
        } catch (error) {
            alert('Cannot format invalid YAML: ' + error.message);
        }
    }
    
    save() {
        // Emit save event - parent component will handle the actual saving
        this.emit('save-requested', {
            content: this.getContent(),
            filename: this.currentFile
        });
    }
    
    previewChanges() {
        // Emit preview event
        this.emit('preview-requested', {
            content: this.getContent(),
            filename: this.currentFile
        });
    }
    
    // UI Update Methods
    
    updateUI() {
        this.updateValidationStatus();
        this.updateChangesStatus();
    }
    
    updateValidationStatus() {
        const statusElement = this.container.querySelector('#validation-status');
        if (!statusElement) return;
        
        if (this.validationResults.valid) {
            statusElement.innerHTML = '<i class="icon checkmark green"></i> Valid';
            statusElement.className = 'validation-status valid';
        } else {
            statusElement.innerHTML = '<i class="icon times red"></i> Invalid';
            statusElement.className = 'validation-status invalid';
        }
    }
    
    updateChangesStatus() {
        const statusElement = this.container.querySelector('#changes-status');
        if (!statusElement) return;
        
        if (this.hasUnsavedChanges) {
            statusElement.style.display = 'inline';
        } else {
            statusElement.style.display = 'none';
        }
    }
    
    // Auto-save functionality
    
    setupAutosave() {
        this.autosaveInterval = setInterval(() => {
            if (this.hasUnsavedChanges) {
                this.emit('autosave-requested', {
                    content: this.getContent(),
                    filename: this.currentFile
                });
            }
        }, this.options.autosaveInterval);
    }
    
    // Event System
    
    on(event, callback) {
        if (!this.eventListeners[event]) {
            this.eventListeners[event] = [];
        }
        this.eventListeners[event].push(callback);
    }
    
    off(event, callback) {
        if (this.eventListeners[event]) {
            const index = this.eventListeners[event].indexOf(callback);
            if (index > -1) {
                this.eventListeners[event].splice(index, 1);
            }
        }
    }
    
    emit(event, data) {
        if (this.eventListeners[event]) {
            this.eventListeners[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error in event listener:', error);
                }
            });
        }
    }
    
    // Utility Methods
    
    toggleFullscreen() {
        this.container.classList.toggle('fullscreen');
        this.codeEditor.refresh();
    }
    
    exitFullscreen() {
        this.container.classList.remove('fullscreen');
        this.codeEditor.refresh();
    }
    
    // Cleanup
    
    destroy() {
        if (this.autosaveInterval) {
            clearInterval(this.autosaveInterval);
        }
        
        if (this.validationTimeout) {
            clearTimeout(this.validationTimeout);
        }
        
        this.eventListeners = {};
        this.container.innerHTML = '';
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = YamlEditor;
}

// Make available globally
if (typeof window !== 'undefined') {
    window.YamlEditor = YamlEditor;
}