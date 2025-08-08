/**
 * SOUL Framework - YAML File Manager Component
 * 
 * Comprehensive file management component for the YAML Knowledge Manager.
 * Handles file tree navigation, file operations, and integration with the 
 * editor and dependency graph components.
 * 
 * @requires AlpineJS (for reactive state management)
 */

class YamlFileManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/soul/knowledge',
            autoRefresh: true,
            refreshInterval: 30000, // 30 seconds
            maxFileSize: 5 * 1024 * 1024, // 5MB
            supportedExtensions: ['.yml', '.yaml'],
            ...options
        };
        
        // State
        this.fileTree = [];
        this.filteredFileTree = [];
        this.currentFile = null;
        this.fileFilter = '';
        this.loading = false;
        this.loadingMessage = '';
        
        // Statistics
        this.statistics = {
            totalFiles: 0,
            totalConcepts: 0,
            totalRelationships: 0,
            totalAgents: 0
        };
        
        // Event handlers
        this.eventHandlers = new Map();
        
        this.initialize();
    }
    
    initialize() {
        this.setupEventListeners();
        this.loadFileTree();
        
        if (this.options.autoRefresh) {
            this.setupAutoRefresh();
        }
    }
    
    setupEventListeners() {
        // Register global event handlers for file operations
        document.addEventListener('yaml-file-saved', (event) => {
            this.handleFileSaved(event.detail);
        });
        
        document.addEventListener('yaml-file-deleted', (event) => {
            this.handleFileDeleted(event.detail);
        });
        
        document.addEventListener('yaml-files-uploaded', (event) => {
            this.handleFilesUploaded(event.detail);
        });
    }
    
    setupAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.refreshFileTree();
        }, this.options.refreshInterval);
    }
    
    // File Tree Operations
    
    async loadFileTree() {
        this.loading = true;
        this.loadingMessage = 'Loading file tree...';
        
        try {
            const response = await fetch(`${this.options.baseUrl}/file-tree`);
            
            if (!response.ok) {
                throw new Error('Failed to load file tree');
            }
            
            const data = await response.json();
            this.fileTree = Array.isArray(data) ? data : [];
            this.filteredFileTree = [...this.fileTree];
            
            // Calculate statistics
            this.updateStatistics();
            
            this.emit('file-tree-loaded', { fileTree: this.fileTree });
            
        } catch (error) {
            console.error('Failed to load file tree:', error);
            this.emit('error', { message: 'Failed to load file tree: ' + error.message });
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async refreshFileTree() {
        try {
            const response = await fetch(`${this.options.baseUrl}/file-tree`);
            
            if (response.ok) {
                const data = await response.json();
                const newFileTree = Array.isArray(data) ? data : [];
                
                // Only update if there are actual changes
                if (JSON.stringify(newFileTree) !== JSON.stringify(this.fileTree)) {
                    this.fileTree = newFileTree;
                    this.applyFilter();
                    this.updateStatistics();
                    this.emit('file-tree-updated', { fileTree: this.fileTree });
                }
            }
        } catch (error) {
            console.warn('Failed to refresh file tree:', error);
        }
    }
    
    updateStatistics() {
        let totalFiles = 0;
        let totalConcepts = 0;
        let totalRelationships = 0;
        let totalAgents = 0;
        
        const processNode = (node) => {
            if (node.type === 'file') {
                totalFiles++;
                if (node.metadata) {
                    totalConcepts += node.metadata.concepts || 0;
                    totalRelationships += node.metadata.relationships || 0;
                    totalAgents += node.metadata.agents || 0;
                }
            } else if (node.type === 'directory' && node.children) {
                node.children.forEach(processNode);
            }
        };
        
        this.fileTree.forEach(processNode);
        
        this.statistics = {
            totalFiles,
            totalConcepts,
            totalRelationships,
            totalAgents
        };
        
        this.emit('statistics-updated', this.statistics);
    }
    
    // File Operations
    
    async loadFile(filepath) {
        if (!filepath) return null;
        
        this.loading = true;
        this.loadingMessage = 'Loading file...';
        
        try {
            const response = await fetch(`${this.options.baseUrl}/file/${encodeURIComponent(filepath)}`);
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to load file');
            }
            
            const fileData = await response.json();
            this.currentFile = fileData;
            
            this.emit('file-loaded', fileData);
            return fileData;
            
        } catch (error) {
            console.error('Failed to load file:', error);
            this.emit('error', { message: 'Failed to load file: ' + error.message });
            return null;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async saveFile(filename, content, options = {}) {
        if (!filename || !content) {
            throw new Error('Filename and content are required');
        }
        
        this.loading = true;
        this.loadingMessage = 'Saving file...';
        
        try {
            const response = await fetch(`${this.options.baseUrl}/file`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    filename,
                    content,
                    createBackup: options.createBackup !== false,
                    validate: options.validate !== false
                })
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Failed to save file');
            }
            
            // Update current file data
            if (this.currentFile && this.currentFile.filename === filename) {
                this.currentFile.lastModified = result.lastModified;
                this.currentFile.content = content;
            }
            
            this.emit('file-saved', { filename, result });
            this.refreshFileTree(); // Refresh to get updated metadata
            
            return result;
            
        } catch (error) {
            console.error('Failed to save file:', error);
            this.emit('error', { message: 'Failed to save file: ' + error.message });
            throw error;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async deleteFile(filename) {
        if (!filename) {
            throw new Error('Filename is required');
        }
        
        if (!confirm(`Are you sure you want to delete "${filename}"? This action cannot be undone.`)) {
            return false;
        }
        
        this.loading = true;
        this.loadingMessage = 'Deleting file...';
        
        try {
            const response = await fetch(`${this.options.baseUrl}/file/${encodeURIComponent(filename)}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken()
                }
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Failed to delete file');
            }
            
            // Clear current file if it's the one being deleted
            if (this.currentFile && this.currentFile.filename === filename) {
                this.currentFile = null;
            }
            
            this.emit('file-deleted', { filename, result });
            this.refreshFileTree();
            
            return result;
            
        } catch (error) {
            console.error('Failed to delete file:', error);
            this.emit('error', { message: 'Failed to delete file: ' + error.message });
            throw error;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async createFile(filename, template = null) {
        if (!filename) {
            throw new Error('Filename is required');
        }
        
        // Ensure proper extension
        if (!this.hasValidExtension(filename)) {
            filename += '.yml';
        }
        
        const content = template || this.getDefaultTemplate();
        
        try {
            const result = await this.saveFile(filename, content);
            
            // Load the newly created file
            await this.loadFile(filename);
            
            this.emit('file-created', { filename, result });
            return result;
            
        } catch (error) {
            console.error('Failed to create file:', error);
            throw error;
        }
    }
    
    async uploadFiles(files, options = {}) {
        if (!files || files.length === 0) {
            throw new Error('No files to upload');
        }
        
        // Validate files
        for (const file of files) {
            if (file.size > this.options.maxFileSize) {
                throw new Error(`File "${file.name}" exceeds maximum size limit`);
            }
            
            if (!this.hasValidExtension(file.name)) {
                throw new Error(`File "${file.name}" has unsupported extension`);
            }
        }
        
        this.loading = true;
        this.loadingMessage = `Uploading ${files.length} file(s)...`;
        
        try {
            const formData = new FormData();
            
            for (const file of files) {
                formData.append('files[]', file);
            }
            
            formData.append('overwrite', options.overwrite ? '1' : '0');
            formData.append('validate', options.validate !== false ? '1' : '0');
            
            const response = await fetch(`${this.options.baseUrl}/upload`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Failed to upload files');
            }
            
            this.emit('files-uploaded', { files: Array.from(files), result });
            this.refreshFileTree();
            
            return result;
            
        } catch (error) {
            console.error('Failed to upload files:', error);
            this.emit('error', { message: 'Failed to upload files: ' + error.message });
            throw error;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async exportFiles(fileList = null) {
        this.loading = true;
        this.loadingMessage = 'Preparing export...';
        
        try {
            const url = new URL(`${this.options.baseUrl}/export`, window.location.origin);
            
            if (fileList && fileList.length > 0) {
                fileList.forEach(file => url.searchParams.append('files[]', file));
            }
            
            // Create a temporary link to trigger download
            const a = document.createElement('a');
            a.href = url.toString();
            a.download = 'soul_knowledge_export.zip';
            a.style.display = 'none';
            
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            this.emit('files-exported', { files: fileList });
            
        } catch (error) {
            console.error('Failed to export files:', error);
            this.emit('error', { message: 'Failed to export files: ' + error.message });
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    // YAML Operations
    
    async loadYamlFiles(fileList = null, force = false) {
        this.loading = true;
        this.loadingMessage = 'Loading YAML files into graph...';
        
        try {
            const response = await fetch(`${this.options.baseUrl}/load-yaml`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    files: fileList,
                    force
                })
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Failed to load YAML files');
            }
            
            this.emit('yaml-loaded', { files: fileList, result });
            return result;
            
        } catch (error) {
            console.error('Failed to load YAML files:', error);
            this.emit('error', { message: 'Failed to load YAML files: ' + error.message });
            throw error;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    async validateFile(content, strict = true) {
        try {
            const response = await fetch(`${this.options.baseUrl}/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    content,
                    strict
                })
            });
            
            return await response.json();
            
        } catch (error) {
            console.error('Validation failed:', error);
            return {
                valid: false,
                errors: ['Validation request failed: ' + error.message],
                warnings: []
            };
        }
    }
    
    async previewChanges(filename, newContent) {
        try {
            const response = await fetch(`${this.options.baseUrl}/preview-changes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({
                    filename,
                    newContent
                })
            });
            
            return await response.json();
            
        } catch (error) {
            console.error('Failed to preview changes:', error);
            throw error;
        }
    }
    
    // Filter and Search Operations
    
    applyFilter(filter = null) {
        const filterText = (filter !== null ? filter : this.fileFilter).toLowerCase();
        
        if (!filterText) {
            this.filteredFileTree = [...this.fileTree];
        } else {
            this.filteredFileTree = this.filterTreeItems(this.fileTree, filterText);
        }
        
        this.emit('filter-applied', { filter: filterText, results: this.filteredFileTree });
    }
    
    filterTreeItems(items, filter) {
        return items.filter(item => {
            if (item.type === 'directory') {
                const filteredChildren = this.filterTreeItems(item.children || [], filter);
                return filteredChildren.length > 0 || item.name.toLowerCase().includes(filter);
            } else {
                return item.name.toLowerCase().includes(filter) ||
                       (item.path && item.path.toLowerCase().includes(filter));
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
    }
    
    searchInFiles(query, options = {}) {
        // This would implement content search across files
        // For now, it's a placeholder for future implementation
        console.log('Search functionality not yet implemented');
        this.emit('search-requested', { query, options });
    }
    
    // Utility Methods
    
    getDefaultTemplate() {
        return `metadata:
  title: "New Knowledge File"
  version: "1.0"
  description: "Description of this knowledge file"
  author: ""
  domain: "general"

concepts:
  - name: "EXAMPLE_CONCEPT"
    labels: ["Concept"]
    properties:
      type: "concept"
      description: "Example concept description"
      domain: "general"

relationships:
  - from: "EXAMPLE_CONCEPT"
    to: "ANOTHER_CONCEPT"
    type: "IS_A"
    properties:
      strength: 1.0

procedural_agents:
  - name: "ExampleAgent"
    code_reference: "ExampleService::exampleMethod"
    description: "Example procedural agent"
    priority: 1
`;
    }
    
    hasValidExtension(filename) {
        return this.options.supportedExtensions.some(ext => 
            filename.toLowerCase().endsWith(ext)
        );
    }
    
    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.content : '';
    }
    
    formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    formatDate(timestamp) {
        if (!timestamp) return 'Unknown';
        
        const date = new Date(timestamp * 1000);
        return date.toLocaleString();
    }
    
    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        
        switch (ext) {
            case 'yml':
            case 'yaml':
                return 'file code outline';
            default:
                return 'file outline';
        }
    }
    
    // Event System
    
    on(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(handler);
    }
    
    off(event, handler) {
        if (this.eventHandlers.has(event)) {
            const handlers = this.eventHandlers.get(event);
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        }
    }
    
    emit(event, data = null) {
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in event handler for "${event}":`, error);
                }
            });
        }
        
        // Also emit as DOM event for AlpineJS components
        document.dispatchEvent(new CustomEvent(`yaml-file-manager:${event}`, {
            detail: data
        }));
    }
    
    // Batch Operations
    
    async batchOperation(files, operation, options = {}) {
        if (!files || files.length === 0) {
            return [];
        }
        
        this.loading = true;
        this.loadingMessage = `Processing ${files.length} files...`;
        
        const results = [];
        
        try {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                try {
                    let result;
                    
                    switch (operation) {
                        case 'validate':
                            result = await this.validateFile(file.content);
                            break;
                        case 'load':
                            result = await this.loadFile(file.path);
                            break;
                        case 'delete':
                            result = await this.deleteFile(file.path);
                            break;
                        default:
                            throw new Error(`Unknown operation: ${operation}`);
                    }
                    
                    results.push({ file, success: true, result });
                    
                } catch (error) {
                    results.push({ file, success: false, error: error.message });
                }
                
                // Update progress
                this.loadingMessage = `Processing ${i + 1}/${files.length} files...`;
            }
            
            this.emit('batch-operation-complete', { operation, results });
            return results;
            
        } catch (error) {
            console.error('Batch operation failed:', error);
            this.emit('error', { message: 'Batch operation failed: ' + error.message });
            throw error;
        } finally {
            this.loading = false;
            this.loadingMessage = '';
        }
    }
    
    // Event Handlers for External Events
    
    handleFileSaved(data) {
        // Handle file saved event from editor
        if (data.filename) {
            this.refreshFileTree();
        }
    }
    
    handleFileDeleted(data) {
        // Handle file deleted event
        this.refreshFileTree();
    }
    
    handleFilesUploaded(data) {
        // Handle files uploaded event
        this.refreshFileTree();
    }
    
    // Cleanup
    
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        this.eventHandlers.clear();
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = YamlFileManager;
}

// Make available globally
if (typeof window !== 'undefined') {
    window.YamlFileManager = YamlFileManager;
}

// AlpineJS integration helper
// Export for ES6 modules and CommonJS
export default YamlFileManager;

if (typeof window !== 'undefined' && window.Alpine) {
    window.createYamlFileManager = function(options = {}) {
        return new YamlFileManager(options);
    };
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = YamlFileManager;
}