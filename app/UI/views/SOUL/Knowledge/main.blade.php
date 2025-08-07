@extends('UI.layouts.index')

@section('title')
    SOUL Knowledge Manager
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('build/css/webtool/pages/soul/knowledge-manager.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/lint/lint.min.css">
@endsection

@section('content')
    <div id="knowledge-manager" class="ui container fluid" x-data="knowledgeManager()">
        <!-- Header Bar -->
        <div class="ui top attached menu">
            <div class="item">
                <i class="book icon"></i>
                <strong>SOUL Knowledge Manager</strong>
            </div>
            
            <div class="right menu">
                <!-- File Operations -->
                <div class="ui dropdown item" x-data="{ open: false }" @click.outside="open = false">
                    <i class="file icon"></i>
                    File
                    <i class="dropdown icon"></i>
                    <div class="menu" :class="{ 'visible': open }" x-show="open" x-transition>
                        <div class="item" @click="createNewFile()">
                            <i class="plus icon"></i>New File
                        </div>
                        <div class="item" @click="uploadFiles()">
                            <i class="upload icon"></i>Upload Files
                        </div>
                        <div class="item" @click="exportFiles()">
                            <i class="download icon"></i>Export Files
                        </div>
                        <div class="divider"></div>
                        <div class="item" @click="loadAllYaml()">
                            <i class="sync icon"></i>Load All YAML
                        </div>
                    </div>
                </div>
                
                <!-- View Options -->
                <div class="ui dropdown item" x-data="{ open: false }" @click.outside="open = false">
                    <i class="eye icon"></i>
                    View
                    <i class="dropdown icon"></i>
                    <div class="menu" :class="{ 'visible': open }" x-show="open" x-transition>
                        <div class="item" @click="activeView = 'editor'">
                            <i class="edit icon"></i>Editor View
                        </div>
                        <div class="item" @click="activeView = 'dependency'">
                            <i class="sitemap icon"></i>Dependency Graph
                        </div>
                        <div class="item" @click="activeView = 'validation'">
                            <i class="checkmark icon"></i>Validation View
                        </div>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="item">
                    <div class="ui tiny statistics">
                        <div class="statistic">
                            <div class="value" x-text="statistics.totalFiles"></div>
                            <div class="label">Files</div>
                        </div>
                        <div class="statistic">
                            <div class="value" x-text="statistics.totalConcepts"></div>
                            <div class="label">Concepts</div>
                        </div>
                        <div class="statistic">
                            <div class="value" x-text="statistics.totalRelationships"></div>
                            <div class="label">Relations</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui attached segment" style="min-height: 80vh;">
            <div class="knowledge-manager-layout">
                <!-- File Browser -->
                <div class="file-browser-pane">
                    <div class="ui segment">
                        <h4 class="ui header">
                            <i class="folder open icon"></i>
                            YAML Files
                        </h4>
                        
                        <!-- Search Filter -->
                        <div class="ui fluid icon input">
                            <input type="text" placeholder="Filter files..." 
                                   x-model="fileFilter" 
                                   @input="filterFiles()">
                            <i class="search icon"></i>
                        </div>
                        
                        <!-- File Tree -->
                        <div class="file-tree" x-html="renderedFileTree"></div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="main-content-pane">
                    <!-- Editor View -->
                    <div x-show="activeView === 'editor'" class="editor-container">
                        <div x-show="!currentFile" class="ui placeholder segment">
                            <div class="ui icon header">
                                <i class="file outline icon"></i>
                                Select a file to start editing
                            </div>
                            <div class="ui primary button" @click="createNewFile()">
                                <i class="plus icon"></i>Create New File
                            </div>
                        </div>
                        
                        <div x-show="currentFile" class="editor-workspace">
                            <!-- Editor Toolbar -->
                            <div class="ui secondary menu">
                                <div class="item">
                                    <strong x-text="currentFile?.filename || 'Untitled'"></strong>
                                </div>
                                
                                <div class="right menu">
                                    <div class="item">
                                        <div class="ui toggle checkbox">
                                            <input type="checkbox" x-model="editorSettings.liveValidation">
                                            <label>Live Validation</label>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <button class="ui button" @click="previewChanges()" 
                                                :disabled="!hasUnsavedChanges">
                                            <i class="eye icon"></i>Preview
                                        </button>
                                    </div>
                                    <div class="item">
                                        <button class="ui primary button" @click="saveCurrentFile()" 
                                                :disabled="!hasUnsavedChanges">
                                            <i class="save icon"></i>Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Split Layout: Visual Editor + Code Editor -->
                            <div class="editor-split-layout">
                                <!-- Visual Editor -->
                                <div class="visual-editor-pane" x-show="editorSettings.showVisualEditor">
                                    <div class="ui tabular menu">
                                        <div class="item" :class="{ 'active': editorTab === 'concepts' }" 
                                             @click="editorTab = 'concepts'">
                                            Concepts
                                        </div>
                                        <div class="item" :class="{ 'active': editorTab === 'relationships' }" 
                                             @click="editorTab = 'relationships'">
                                            Relationships
                                        </div>
                                        <div class="item" :class="{ 'active': editorTab === 'agents' }" 
                                             @click="editorTab = 'agents'">
                                            Agents
                                        </div>
                                    </div>
                                    
                                    <div class="tab-content">
                                        <!-- Concepts Tab -->
                                        <div x-show="editorTab === 'concepts'" class="concepts-editor">
                                            <template x-for="(concept, index) in parsedYaml.concepts || []" :key="index">
                                                <div class="ui card fluid">
                                                    <div class="content">
                                                        <div class="header">
                                                            <input type="text" x-model="concept.name" 
                                                                   placeholder="Concept Name" 
                                                                   @input="markAsChanged()">
                                                        </div>
                                                        <div class="meta">
                                                            <div class="ui labels">
                                                                <template x-for="label in concept.labels || []">
                                                                    <div class="ui label" x-text="label"></div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="description">
                                                            <textarea x-model="concept.properties.description" 
                                                                     placeholder="Description" 
                                                                     @input="markAsChanged()"></textarea>
                                                        </div>
                                                        <div class="extra content">
                                                            <button class="ui red button mini" 
                                                                    @click="removeConcept(index)">
                                                                <i class="trash icon"></i>Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button class="ui button primary fluid" @click="addConcept()">
                                                <i class="plus icon"></i>Add Concept
                                            </button>
                                        </div>
                                        
                                        <!-- Relationships Tab -->
                                        <div x-show="editorTab === 'relationships'" class="relationships-editor">
                                            <template x-for="(relationship, index) in parsedYaml.relationships || []" :key="index">
                                                <div class="ui segment">
                                                    <div class="ui grid">
                                                        <div class="four wide column">
                                                            <label>From:</label>
                                                            <select x-model="relationship.from" @change="markAsChanged()">
                                                                <option value="">Select concept</option>
                                                                <template x-for="concept in availableConcepts">
                                                                    <option :value="concept" x-text="concept"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div class="four wide column">
                                                            <label>Type:</label>
                                                            <select x-model="relationship.type" @change="markAsChanged()">
                                                                <option value="">Select type</option>
                                                                <option value="IS_A">IS_A</option>
                                                                <option value="PART_OF">PART_OF</option>
                                                                <option value="CAUSES">CAUSES</option>
                                                                <option value="ACTIVATES">ACTIVATES</option>
                                                                <option value="SCHEMA_ACTIVATES">SCHEMA_ACTIVATES</option>
                                                            </select>
                                                        </div>
                                                        <div class="four wide column">
                                                            <label>To:</label>
                                                            <select x-model="relationship.to" @change="markAsChanged()">
                                                                <option value="">Select concept</option>
                                                                <template x-for="concept in availableConcepts">
                                                                    <option :value="concept" x-text="concept"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div class="four wide column">
                                                            <label>Actions:</label>
                                                            <button class="ui red button mini" @click="removeRelationship(index)">
                                                                <i class="trash icon"></i>Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button class="ui button primary fluid" @click="addRelationship()">
                                                <i class="plus icon"></i>Add Relationship
                                            </button>
                                        </div>
                                        
                                        <!-- Agents Tab -->
                                        <div x-show="editorTab === 'agents'" class="agents-editor">
                                            <template x-for="(agent, index) in parsedYaml.procedural_agents || []" :key="index">
                                                <div class="ui segment">
                                                    <div class="ui form">
                                                        <div class="two fields">
                                                            <div class="field">
                                                                <label>Name</label>
                                                                <input type="text" x-model="agent.name" 
                                                                       @input="markAsChanged()">
                                                            </div>
                                                            <div class="field">
                                                                <label>Code Reference</label>
                                                                <input type="text" x-model="agent.code_reference" 
                                                                       @input="markAsChanged()">
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label>Description</label>
                                                            <textarea x-model="agent.description" 
                                                                     @input="markAsChanged()"></textarea>
                                                        </div>
                                                        <div class="field">
                                                            <label>Priority</label>
                                                            <input type="number" x-model="agent.priority" 
                                                                   @input="markAsChanged()">
                                                        </div>
                                                        <button class="ui red button mini" @click="removeAgent(index)">
                                                            <i class="trash icon"></i>Remove Agent
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button class="ui button primary fluid" @click="addAgent()">
                                                <i class="plus icon"></i>Add Agent
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Code Editor -->
                                <div class="code-editor-pane">
                                    <div class="ui secondary menu">
                                        <div class="item">
                                            <div class="ui toggle checkbox">
                                                <input type="checkbox" x-model="editorSettings.showVisualEditor">
                                                <label>Visual Editor</label>
                                            </div>
                                        </div>
                                        <div class="right menu">
                                            <div class="item">
                                                <div class="ui mini circular label" 
                                                     :class="validation.valid ? 'green' : 'red'"
                                                     x-show="validation.valid !== null">
                                                    <i class="icon" :class="validation.valid ? 'checkmark' : 'times'"></i>
                                                    <span x-text="validation.valid ? 'Valid' : 'Invalid'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="code-editor-container">
                                        <textarea id="yaml-editor" x-ref="yamlEditor"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dependency Graph View -->
                    <div x-show="activeView === 'dependency'" class="dependency-container">
                        @include('SOUL.Knowledge.dependency')
                    </div>
                    
                    <!-- Validation View -->
                    <div x-show="activeView === 'validation'" class="validation-container">
                        @include('SOUL.Knowledge.validation')
                    </div>
                </div>

                <!-- Properties Panel -->
                <div class="properties-pane">
                    <div class="ui segment">
                        <h4 class="ui header">
                            <i class="info circle icon"></i>
                            Properties
                        </h4>
                        
                        <!-- File Information -->
                        <div x-show="currentFile" class="ui list">
                            <div class="item">
                                <strong>File:</strong>
                                <span x-text="currentFile?.filename"></span>
                            </div>
                            <div class="item">
                                <strong>Size:</strong>
                                <span x-text="formatFileSize(currentFile?.size)"></span>
                            </div>
                            <div class="item">
                                <strong>Modified:</strong>
                                <span x-text="formatDate(currentFile?.lastModified)"></span>
                            </div>
                            <div class="item" x-show="hasUnsavedChanges">
                                <div class="ui orange label">
                                    <i class="warning icon"></i>
                                    Unsaved changes
                                </div>
                            </div>
                        </div>
                        
                        <!-- Validation Results -->
                        <div x-show="validation.errors?.length > 0 || validation.warnings?.length > 0">
                            <h5 class="ui header">Validation</h5>
                            
                            <div x-show="validation.errors?.length > 0">
                                <div class="ui red message">
                                    <div class="header">Errors</div>
                                    <ul>
                                        <template x-for="error in validation.errors">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            
                            <div x-show="validation.warnings?.length > 0">
                                <div class="ui yellow message">
                                    <div class="header">Warnings</div>
                                    <ul>
                                        <template x-for="warning in validation.warnings">
                                            <li x-text="warning"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="ui divided list">
                            <div class="item">
                                <button class="ui button tiny fluid" @click="validateCurrentFile()">
                                    <i class="checkmark icon"></i>Validate File
                                </button>
                            </div>
                            <div class="item" x-show="currentFile">
                                <button class="ui button tiny fluid" @click="formatYaml()">
                                    <i class="magic icon"></i>Format YAML
                                </button>
                            </div>
                            <div class="item" x-show="currentFile">
                                <button class="ui button tiny fluid red" @click="deleteCurrentFile()">
                                    <i class="trash icon"></i>Delete File
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div x-show="loading" class="ui active dimmer">
            <div class="ui large text loader" x-text="loadingMessage"></div>
        </div>

        <!-- Modals and Dialogs -->
        @include('SOUL.Knowledge.modals')
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/yaml/yaml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/lint/lint.min.js"></script>
    <script src="{{ asset('build/js/soul/components/YamlEditor.js') }}"></script>
    <script src="{{ asset('build/js/soul/components/DependencyGraphVisualization.js') }}"></script>
    
    <script>
        function knowledgeManager() {
            return {
                // State
                activeView: 'editor',
                currentFile: null,
                parsedYaml: {},
                validation: { valid: null, errors: [], warnings: [] },
                fileTree: @json($fileTree ?? []),
                filteredFileTree: [],
                statistics: @json($statistics ?? []),
                fileFilter: '',
                loading: false,
                loadingMessage: '',
                hasUnsavedChanges: false,
                
                // Editor state
                editorTab: 'concepts',
                editorSettings: {
                    showVisualEditor: true,
                    liveValidation: true,
                    autoSave: false
                },
                
                // CodeMirror instance
                yamlEditor: null,
                
                init() {
                    this.filteredFileTree = this.fileTree;
                    this.initializeCodeMirror();
                },
                
                initializeCodeMirror() {
                    this.$nextTick(() => {
                        if (this.$refs.yamlEditor) {
                            this.yamlEditor = CodeMirror.fromTextArea(this.$refs.yamlEditor, {
                                mode: 'yaml',
                                theme: 'material',
                                lineNumbers: true,
                                lineWrapping: true,
                                gutters: ['CodeMirror-lint-markers'],
                                lint: true,
                                extraKeys: {
                                    'Ctrl-S': (cm) => {
                                        this.saveCurrentFile();
                                    },
                                    'Ctrl-Shift-F': (cm) => {
                                        this.formatYaml();
                                    }
                                }
                            });
                            
                            this.yamlEditor.on('change', () => {
                                this.markAsChanged();
                                if (this.editorSettings.liveValidation) {
                                    this.debounceValidation();
                                }
                            });
                        }
                    });
                },
                
                // File operations
                async loadFile(filepath) {
                    this.loading = true;
                    this.loadingMessage = 'Loading file...';
                    
                    try {
                        const response = await fetch(`/soul/knowledge/file/${encodeURIComponent(filepath)}`);
                        const data = await response.json();
                        
                        if (!response.ok) {
                            throw new Error(data.error || 'Failed to load file');
                        }
                        
                        this.currentFile = data;
                        this.parsedYaml = data.parsed || {};
                        this.validation = data.validation || { valid: null, errors: [], warnings: [] };
                        this.hasUnsavedChanges = false;
                        
                        if (this.yamlEditor) {
                            this.yamlEditor.setValue(data.content || '');
                            this.yamlEditor.clearHistory();
                        }
                        
                        // Switch to editor view
                        this.activeView = 'editor';
                        
                    } catch (error) {
                        console.error('Failed to load file:', error);
                        alert('Failed to load file: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async saveCurrentFile() {
                    if (!this.currentFile || !this.hasUnsavedChanges) return;
                    
                    this.loading = true;
                    this.loadingMessage = 'Saving file...';
                    
                    try {
                        const content = this.yamlEditor ? this.yamlEditor.getValue() : '';
                        
                        const response = await fetch('/soul/knowledge/file', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                filename: this.currentFile.filename,
                                content: content,
                                createBackup: true
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (!response.ok) {
                            throw new Error(data.error || 'Failed to save file');
                        }
                        
                        this.hasUnsavedChanges = false;
                        this.validation = data.validation;
                        this.currentFile.lastModified = data.lastModified;
                        
                        // Show success message
                        this.showNotification('File saved successfully', 'success');
                        
                    } catch (error) {
                        console.error('Failed to save file:', error);
                        alert('Failed to save file: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async validateCurrentFile() {
                    if (!this.yamlEditor) return;
                    
                    const content = this.yamlEditor.getValue();
                    
                    try {
                        const response = await fetch('/soul/knowledge/validate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                content: content,
                                strict: true
                            })
                        });
                        
                        const data = await response.json();
                        this.validation = data;
                        
                    } catch (error) {
                        console.error('Validation failed:', error);
                        this.validation = { valid: false, errors: ['Validation request failed'], warnings: [] };
                    }
                },
                
                // Visual editor operations
                addConcept() {
                    if (!this.parsedYaml.concepts) {
                        this.parsedYaml.concepts = [];
                    }
                    
                    this.parsedYaml.concepts.push({
                        name: '',
                        labels: ['Concept'],
                        properties: {
                            type: 'concept',
                            description: '',
                            domain: 'general'
                        }
                    });
                    
                    this.syncVisualToCode();
                    this.markAsChanged();
                },
                
                removeConcept(index) {
                    if (this.parsedYaml.concepts) {
                        this.parsedYaml.concepts.splice(index, 1);
                        this.syncVisualToCode();
                        this.markAsChanged();
                    }
                },
                
                addRelationship() {
                    if (!this.parsedYaml.relationships) {
                        this.parsedYaml.relationships = [];
                    }
                    
                    this.parsedYaml.relationships.push({
                        from: '',
                        to: '',
                        type: '',
                        properties: {
                            strength: 1.0
                        }
                    });
                    
                    this.syncVisualToCode();
                    this.markAsChanged();
                },
                
                removeRelationship(index) {
                    if (this.parsedYaml.relationships) {
                        this.parsedYaml.relationships.splice(index, 1);
                        this.syncVisualToCode();
                        this.markAsChanged();
                    }
                },
                
                addAgent() {
                    if (!this.parsedYaml.procedural_agents) {
                        this.parsedYaml.procedural_agents = [];
                    }
                    
                    this.parsedYaml.procedural_agents.push({
                        name: '',
                        code_reference: '',
                        description: '',
                        priority: 1
                    });
                    
                    this.syncVisualToCode();
                    this.markAsChanged();
                },
                
                removeAgent(index) {
                    if (this.parsedYaml.procedural_agents) {
                        this.parsedYaml.procedural_agents.splice(index, 1);
                        this.syncVisualToCode();
                        this.markAsChanged();
                    }
                },
                
                syncVisualToCode() {
                    if (this.yamlEditor) {
                        const yamlContent = this.generateYamlFromParsed();
                        this.yamlEditor.setValue(yamlContent);
                    }
                },
                
                generateYamlFromParsed() {
                    // Simple YAML generation - in production, use a proper YAML library
                    let yaml = '';
                    
                    if (this.parsedYaml.metadata) {
                        yaml += 'metadata:\n';
                        yaml += `  title: "${this.parsedYaml.metadata.title || ''}"\n`;
                        yaml += `  version: "${this.parsedYaml.metadata.version || '1.0'}"\n`;
                        yaml += `  description: "${this.parsedYaml.metadata.description || ''}"\n\n`;
                    }
                    
                    if (this.parsedYaml.concepts?.length) {
                        yaml += 'concepts:\n';
                        this.parsedYaml.concepts.forEach(concept => {
                            yaml += `  - name: "${concept.name}"\n`;
                            yaml += `    labels: [${concept.labels?.map(l => `"${l}"`).join(', ')}]\n`;
                            yaml += `    properties:\n`;
                            Object.entries(concept.properties || {}).forEach(([key, value]) => {
                                yaml += `      ${key}: "${value}"\n`;
                            });
                            yaml += '\n';
                        });
                    }
                    
                    // Add relationships and agents similarly...
                    
                    return yaml;
                },
                
                // Computed properties
                get availableConcepts() {
                    return (this.parsedYaml.concepts || []).map(c => c.name).filter(n => n);
                },
                
                get renderedFileTree() {
                    return this.renderFileTreeHtml(this.filteredFileTree);
                },
                
                // Utility methods
                filterFiles() {
                    if (!this.fileFilter) {
                        this.filteredFileTree = this.fileTree;
                        return;
                    }
                    
                    const filter = this.fileFilter.toLowerCase();
                    this.filteredFileTree = this.filterTreeItems(this.fileTree, filter);
                },
                
                filterTreeItems(items, filter) {
                    return items.filter(item => {
                        if (item.type === 'directory') {
                            const filteredChildren = this.filterTreeItems(item.children || [], filter);
                            return filteredChildren.length > 0 || item.name.toLowerCase().includes(filter);
                        } else {
                            return item.name.toLowerCase().includes(filter);
                        }
                    }).map(item => {
                        if (item.type === 'directory') {
                            return {
                                ...item,
                                children: this.filterTreeItems(item.children || [], filter)
                            };
                        }
                        return item;
                    });
                },
                
                renderFileTreeHtml(items, level = 0) {
                    let html = '<ul class="file-tree-list">';
                    
                    items.forEach(item => {
                        if (item.type === 'directory') {
                            html += `<li class="directory-item">
                                <div class="directory-header" style="padding-left: ${level * 20}px">
                                    <i class="folder icon"></i>
                                    <span>${item.name}</span>
                                </div>
                                ${this.renderFileTreeHtml(item.children || [], level + 1)}
                            </li>`;
                        } else {
                            html += `<li class="file-item">
                                <div class="file-header" style="padding-left: ${level * 20}px" @click="loadFile('${item.path}')">
                                    <i class="file outline icon"></i>
                                    <span>${item.name}</span>
                                    <div class="file-meta">
                                        <small>${this.formatFileSize(item.size)}</small>
                                    </div>
                                </div>
                            </li>`;
                        }
                    });
                    
                    html += '</ul>';
                    return html;
                },
                
                formatFileSize(bytes) {
                    if (!bytes) return '0 B';
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
                },
                
                formatDate(timestamp) {
                    if (!timestamp) return 'Unknown';
                    return new Date(timestamp * 1000).toLocaleString();
                },
                
                markAsChanged() {
                    this.hasUnsavedChanges = true;
                },
                
                showNotification(message, type = 'info') {
                    // Implementation for showing notifications
                    console.log(`${type}: ${message}`);
                },
                
                // Debounced validation
                debounceValidation() {
                    clearTimeout(this.validationTimeout);
                    this.validationTimeout = setTimeout(() => {
                        this.validateCurrentFile();
                    }, 1000);
                },
                
                // Placeholder methods for menu actions
                createNewFile() {
                    console.log('Create new file');
                },
                
                uploadFiles() {
                    console.log('Upload files');
                },
                
                exportFiles() {
                    console.log('Export files');
                },
                
                loadAllYaml() {
                    console.log('Load all YAML');
                },
                
                previewChanges() {
                    console.log('Preview changes');
                },
                
                formatYaml() {
                    console.log('Format YAML');
                },
                
                deleteCurrentFile() {
                    if (confirm('Are you sure you want to delete this file?')) {
                        console.log('Delete current file');
                    }
                }
            };
        }
    </script>
@endsection