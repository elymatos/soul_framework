<!-- File Upload Modal -->
<div x-show="modals.fileUpload.show" class="ui modal active" x-transition>
    <div class="header">
        <i class="upload icon"></i>
        Upload YAML Files
    </div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>Select YAML files to upload:</label>
                <div class="ui action input">
                    <input type="file" 
                           x-ref="fileInput"
                           multiple 
                           accept=".yml,.yaml"
                           @change="handleFileSelection($event)">
                    <button class="ui icon button" @click="$refs.fileInput.click()">
                        <i class="folder open icon"></i>
                        Browse
                    </button>
                </div>
            </div>
            
            <div class="two fields">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" x-model="modals.fileUpload.overwrite">
                        <label>Overwrite existing files</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" x-model="modals.fileUpload.validate" checked>
                        <label>Validate files before upload</label>
                    </div>
                </div>
            </div>
            
            <!-- File List -->
            <div x-show="modals.fileUpload.files.length > 0" class="field">
                <label>Files to upload:</label>
                <div class="ui divided list">
                    <template x-for="(file, index) in modals.fileUpload.files" :key="index">
                        <div class="item">
                            <div class="right floated content">
                                <button class="ui icon button mini" @click="removeUploadFile(index)">
                                    <i class="times icon"></i>
                                </button>
                            </div>
                            <div class="content">
                                <div class="header" x-text="file.name"></div>
                                <div class="description" x-text="formatFileSize(file.size)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button class="ui button" @click="modals.fileUpload.show = false">Cancel</button>
        <button class="ui primary button" 
                :disabled="modals.fileUpload.files.length === 0"
                @click="uploadFiles()">
            <i class="upload icon"></i>
            Upload Files
        </button>
    </div>
</div>

<!-- Create New File Modal -->
<div x-show="modals.createFile.show" class="ui modal active" x-transition>
    <div class="header">
        <i class="plus icon"></i>
        Create New YAML File
    </div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>File Name:</label>
                <div class="ui action input">
                    <input type="text" 
                           x-model="modals.createFile.filename"
                           placeholder="my-concepts.yml"
                           @input="validateFilename()">
                    <div class="ui label">.yml</div>
                </div>
                <div x-show="modals.createFile.filenameError" class="ui red text" x-text="modals.createFile.filenameError"></div>
            </div>
            
            <div class="field">
                <label>Directory:</label>
                <select x-model="modals.createFile.directory">
                    <option value="">Root Directory</option>
                    <option value="primitives">primitives/</option>
                    <option value="frames">frames/</option>
                    <option value="domains">domains/</option>
                    <option value="agents">agents/</option>
                </select>
            </div>
            
            <div class="field">
                <label>Template:</label>
                <select x-model="modals.createFile.template" @change="loadTemplate()">
                    <option value="">Empty File</option>
                    <option value="basic-concepts">Basic Concepts</option>
                    <option value="frame-definitions">Frame Definitions</option>
                    <option value="image-schemas">Image Schemas</option>
                    <option value="procedural-agents">Procedural Agents</option>
                    <option value="domain-specific">Domain Specific</option>
                </select>
            </div>
            
            <!-- Template Preview -->
            <div x-show="modals.createFile.templateContent" class="field">
                <label>Template Preview:</label>
                <div class="ui segment">
                    <pre x-text="modals.createFile.templateContent" style="max-height: 200px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button class="ui button" @click="modals.createFile.show = false">Cancel</button>
        <button class="ui primary button" 
                :disabled="!modals.createFile.filename || modals.createFile.filenameError"
                @click="createNewFile()">
            <i class="plus icon"></i>
            Create File
        </button>
    </div>
</div>

<!-- Export Files Modal -->
<div x-show="modals.exportFiles.show" class="ui modal active" x-transition>
    <div class="header">
        <i class="download icon"></i>
        Export YAML Files
    </div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>Export Options:</label>
                <div class="ui toggle checkbox">
                    <input type="checkbox" x-model="modals.exportFiles.exportAll" checked>
                    <label>Export all files</label>
                </div>
            </div>
            
            <!-- File Selection -->
            <div x-show="!modals.exportFiles.exportAll" class="field">
                <label>Select files to export:</label>
                <div class="ui list" style="max-height: 300px; overflow-y: auto;">
                    <template x-for="file in getAllFiles()" :key="file.path">
                        <div class="item">
                            <div class="ui checkbox">
                                <input type="checkbox" :value="file.path" x-model="modals.exportFiles.selectedFiles">
                                <label x-text="file.path"></label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="field">
                <label>Export Format:</label>
                <select x-model="modals.exportFiles.format">
                    <option value="zip">ZIP Archive</option>
                    <option value="tar">TAR Archive</option>
                    <option value="individual">Individual Files</option>
                </select>
            </div>
            
            <div class="field">
                <div class="ui toggle checkbox">
                    <input type="checkbox" x-model="modals.exportFiles.includeValidation">
                    <label>Include validation report</label>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button class="ui button" @click="modals.exportFiles.show = false">Cancel</button>
        <button class="ui primary button" @click="performExport()">
            <i class="download icon"></i>
            Export Files
        </button>
    </div>
</div>

<!-- Load YAML Modal -->
<div x-show="modals.loadYaml.show" class="ui modal active" x-transition>
    <div class="header">
        <i class="sync icon"></i>
        Load YAML Files into Graph
    </div>
    <div class="content">
        <div class="ui info message">
            <div class="header">Load YAML files into the SOUL graph database</div>
            <p>This will process the YAML files and create/update concepts, relationships, and procedural agents in the Neo4j database.</p>
        </div>
        
        <div class="ui form">
            <div class="field">
                <label>Loading Options:</label>
                <div class="ui toggle checkbox">
                    <input type="checkbox" x-model="modals.loadYaml.loadAll" checked>
                    <label>Load all YAML files</label>
                </div>
            </div>
            
            <!-- File Selection -->
            <div x-show="!modals.loadYaml.loadAll" class="field">
                <label>Select files to load:</label>
                <div class="ui list" style="max-height: 300px; overflow-y: auto;">
                    <template x-for="file in getAllFiles()" :key="file.path">
                        <div class="item">
                            <div class="ui checkbox">
                                <input type="checkbox" :value="file.path" x-model="modals.loadYaml.selectedFiles">
                                <label x-text="file.path"></label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="field">
                <div class="ui toggle checkbox">
                    <input type="checkbox" x-model="modals.loadYaml.force">
                    <label>Force reload (overwrite existing data)</label>
                </div>
            </div>
            
            <!-- Progress -->
            <div x-show="modals.loadYaml.inProgress" class="ui progress" :class="{ 'success': modals.loadYaml.progress === 100 }">
                <div class="bar" :style="`width: ${modals.loadYaml.progress}%`">
                    <div class="progress" x-text="`${modals.loadYaml.progress}%`"></div>
                </div>
                <div class="label" x-text="modals.loadYaml.status"></div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button class="ui button" 
                @click="modals.loadYaml.show = false"
                :disabled="modals.loadYaml.inProgress">Cancel</button>
        <button class="ui primary button" 
                @click="performLoadYaml()"
                :disabled="modals.loadYaml.inProgress">
            <i class="sync icon"></i>
            Load Files
        </button>
    </div>
</div>

<!-- Preview Changes Modal -->
<div x-show="modals.previewChanges.show" class="ui modal active large" x-transition>
    <div class="header">
        <i class="eye icon"></i>
        Preview Changes
    </div>
    <div class="content">
        <div class="ui tabular menu">
            <div class="item" :class="{ 'active': modals.previewChanges.activeTab === 'concepts' }" 
                 @click="modals.previewChanges.activeTab = 'concepts'">
                Concepts
            </div>
            <div class="item" :class="{ 'active': modals.previewChanges.activeTab === 'relationships' }" 
                 @click="modals.previewChanges.activeTab = 'relationships'">
                Relationships
            </div>
            <div class="item" :class="{ 'active': modals.previewChanges.activeTab === 'agents' }" 
                 @click="modals.previewChanges.activeTab = 'agents'">
                Agents
            </div>
        </div>
        
        <!-- Concepts Changes -->
        <div x-show="modals.previewChanges.activeTab === 'concepts'" class="tab-content">
            <!-- Added Concepts -->
            <div x-show="modals.previewChanges.changes.concepts?.added?.length > 0">
                <h4 class="ui header green">
                    <i class="plus icon"></i>
                    Added Concepts (<span x-text="modals.previewChanges.changes.concepts.added.length"></span>)
                </h4>
                <div class="ui divided list">
                    <template x-for="concept in modals.previewChanges.changes.concepts.added" :key="concept.name">
                        <div class="item">
                            <div class="content">
                                <div class="header" x-text="concept.name"></div>
                                <div class="description" x-text="concept.properties?.description"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Modified Concepts -->
            <div x-show="modals.previewChanges.changes.concepts?.modified?.length > 0">
                <h4 class="ui header orange">
                    <i class="edit icon"></i>
                    Modified Concepts (<span x-text="modals.previewChanges.changes.concepts.modified.length"></span>)
                </h4>
                <div class="ui divided list">
                    <template x-for="change in modals.previewChanges.changes.concepts.modified" :key="change.name">
                        <div class="item">
                            <div class="content">
                                <div class="header" x-text="change.name"></div>
                                <div class="description">Changes detected</div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Removed Concepts -->
            <div x-show="modals.previewChanges.changes.concepts?.removed?.length > 0">
                <h4 class="ui header red">
                    <i class="trash icon"></i>
                    Removed Concepts (<span x-text="modals.previewChanges.changes.concepts.removed.length"></span>)
                </h4>
                <div class="ui divided list">
                    <template x-for="concept in modals.previewChanges.changes.concepts.removed" :key="concept.name">
                        <div class="item">
                            <div class="content">
                                <div class="header" x-text="concept.name"></div>
                                <div class="description" x-text="concept.properties?.description"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Similar sections for relationships and agents... -->
        <div x-show="modals.previewChanges.activeTab === 'relationships'" class="tab-content">
            <!-- Relationship changes would be displayed here -->
        </div>
        
        <div x-show="modals.previewChanges.activeTab === 'agents'" class="tab-content">
            <!-- Agent changes would be displayed here -->
        </div>
    </div>
    <div class="actions">
        <button class="ui button" @click="modals.previewChanges.show = false">Close</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div x-show="modals.confirm.show" class="ui modal active" x-transition>
    <div class="header" x-text="modals.confirm.title"></div>
    <div class="content">
        <p x-text="modals.confirm.message"></p>
    </div>
    <div class="actions">
        <button class="ui button" @click="modals.confirm.show = false">Cancel</button>
        <button class="ui primary button" 
                :class="modals.confirm.type === 'danger' ? 'red' : 'primary'"
                @click="confirmAction()">
            <span x-text="modals.confirm.confirmText || 'Confirm'"></span>
        </button>
    </div>
</div>

<!-- Notification Toast -->
<div x-show="notification.show" 
     class="ui message toast"
     :class="notification.type"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-full"
     x-transition:enter-end="opacity-100 transform translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-x-0"
     x-transition:leave-end="opacity-0 transform translate-x-full">
    <i class="icon" :class="notification.icon"></i>
    <div class="content">
        <div class="header" x-text="notification.title"></div>
        <p x-text="notification.message"></p>
    </div>
    <i class="close icon" @click="notification.show = false"></i>
</div>

<style>
.ui.modal.active {
    display: flex !important;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 1001;
}

.ui.modal.active > .content,
.ui.modal.active > .header,
.ui.modal.active > .actions {
    background: white;
    padding: 1rem;
}

.ui.modal.active .content {
    max-width: 600px;
    max-height: 70vh;
    overflow-y: auto;
}

.ui.modal.large.active .content {
    max-width: 900px;
}

.ui.modal.active .header {
    border-bottom: 1px solid #ddd;
    font-weight: bold;
    display: flex;
    align-items: center;
}

.ui.modal.active .header .icon {
    margin-right: 0.5rem;
}

.ui.modal.active .actions {
    border-top: 1px solid #ddd;
    text-align: right;
}

.ui.modal.active .actions .button {
    margin-left: 0.5rem;
}

.ui.message.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 2000;
    min-width: 300px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.tab-content {
    padding: 1rem 0;
    min-height: 200px;
}

.tab-content:empty::after {
    content: "No changes in this category";
    color: #999;
    font-style: italic;
    text-align: center;
    display: block;
    padding: 2rem;
}

.progress {
    margin: 1rem 0;
}
</style>

<script>
// Modal management functions (these would be part of the parent component)
function initializeModals() {
    return {
        // File Upload Modal
        fileUpload: {
            show: false,
            files: [],
            overwrite: false,
            validate: true
        },
        
        // Create File Modal
        createFile: {
            show: false,
            filename: '',
            directory: '',
            template: '',
            templateContent: '',
            filenameError: ''
        },
        
        // Export Files Modal
        exportFiles: {
            show: false,
            exportAll: true,
            selectedFiles: [],
            format: 'zip',
            includeValidation: false
        },
        
        // Load YAML Modal
        loadYaml: {
            show: false,
            loadAll: true,
            selectedFiles: [],
            force: false,
            inProgress: false,
            progress: 0,
            status: ''
        },
        
        // Preview Changes Modal
        previewChanges: {
            show: false,
            activeTab: 'concepts',
            changes: {}
        },
        
        // Confirmation Modal
        confirm: {
            show: false,
            title: '',
            message: '',
            confirmText: 'Confirm',
            type: 'primary',
            callback: null
        }
    };
}

function initializeNotification() {
    return {
        show: false,
        type: 'info',
        title: '',
        message: '',
        icon: 'info circle'
    };
}

// These functions would be added to the main knowledgeManager component:
/*
showNotification(title, message, type = 'info') {
    const icons = {
        success: 'check circle',
        error: 'times circle',
        warning: 'warning sign',
        info: 'info circle'
    };
    
    this.notification = {
        show: true,
        type: type,
        title: title,
        message: message,
        icon: icons[type] || icons.info
    };
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        this.notification.show = false;
    }, 5000);
},

showConfirmation(title, message, callback, type = 'primary', confirmText = 'Confirm') {
    this.modals.confirm = {
        show: true,
        title: title,
        message: message,
        confirmText: confirmText,
        type: type,
        callback: callback
    };
},

confirmAction() {
    if (this.modals.confirm.callback) {
        this.modals.confirm.callback();
    }
    this.modals.confirm.show = false;
}
*/
</script>