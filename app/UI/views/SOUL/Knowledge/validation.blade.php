<div class="validation-view" x-data="validationView()">
    <!-- Validation Controls -->
    <div class="ui secondary menu">
        <div class="item">
            <div class="ui form">
                <div class="inline fields">
                    <div class="field">
                        <label>Validation Mode:</label>
                        <select x-model="validationMode" @change="updateValidationMode()">
                            <option value="strict">Strict</option>
                            <option value="lenient">Lenient</option>
                            <option value="syntax-only">Syntax Only</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Scope:</label>
                        <select x-model="validationScope" @change="updateValidationScope()">
                            <option value="current">Current File</option>
                            <option value="all">All Files</option>
                            <option value="directory">Current Directory</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right menu">
            <div class="item">
                <button class="ui primary button" @click="runValidation()" :disabled="validating">
                    <i class="checkmark icon" :class="{ 'loading': validating }"></i>
                    <span x-show="!validating">Validate</span>
                    <span x-show="validating">Validating...</span>
                </button>
            </div>
            <div class="item">
                <button class="ui button" @click="clearResults()">
                    <i class="eraser icon"></i>
                    Clear Results
                </button>
            </div>
            <div class="item">
                <button class="ui button" @click="exportResults()" :disabled="!hasResults">
                    <i class="download icon"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Validation Summary -->
    <div class="validation-summary" x-show="hasResults">
        <div class="ui statistics">
            <div class="ui statistic">
                <div class="value" :class="{ 'green': validationResults.filesValidated > 0 }" x-text="validationResults.filesValidated || 0"></div>
                <div class="label">Files Validated</div>
            </div>
            <div class="ui statistic">
                <div class="value" :class="{ 'red': validationResults.totalErrors > 0 }" x-text="validationResults.totalErrors || 0"></div>
                <div class="label">Errors</div>
            </div>
            <div class="ui statistic">
                <div class="value" :class="{ 'yellow': validationResults.totalWarnings > 0 }" x-text="validationResults.totalWarnings || 0"></div>
                <div class="label">Warnings</div>
            </div>
            <div class="ui statistic">
                <div class="value" :class="{ 'green': validationResults.validFiles > 0 }" x-text="validationResults.validFiles || 0"></div>
                <div class="label">Valid Files</div>
            </div>
        </div>
    </div>

    <!-- Overall Status -->
    <div x-show="hasResults" class="ui message" 
         :class="{ 
             'success': validationResults.overallStatus === 'success',
             'warning': validationResults.overallStatus === 'warning', 
             'error': validationResults.overallStatus === 'error'
         }">
        <div class="header">
            <i class="icon" :class="{
                'checkmark': validationResults.overallStatus === 'success',
                'warning sign': validationResults.overallStatus === 'warning',
                'times circle': validationResults.overallStatus === 'error'
            }"></i>
            <span x-text="getStatusMessage()"></span>
        </div>
        <p x-text="getStatusDescription()"></p>
    </div>

    <!-- Validation Results -->
    <div class="validation-results" x-show="hasResults">
        <!-- File Results -->
        <div class="ui styled accordion" x-show="fileResults.length > 0">
            <template x-for="(file, index) in fileResults" :key="file.filename">
                <div>
                    <div class="title" :class="{ 'active': file.expanded }" @click="toggleFile(index)">
                        <i class="dropdown icon"></i>
                        <i class="file outline icon" :class="{
                            'green': file.status === 'valid',
                            'yellow': file.status === 'warning',
                            'red': file.status === 'error'
                        }"></i>
                        <span x-text="file.filename"></span>
                        <div class="ui labels" style="float: right;">
                            <div x-show="file.errors?.length > 0" class="ui red label">
                                <span x-text="file.errors.length"></span> errors
                            </div>
                            <div x-show="file.warnings?.length > 0" class="ui yellow label">
                                <span x-text="file.warnings.length"></span> warnings
                            </div>
                            <div x-show="file.status === 'valid'" class="ui green label">
                                Valid
                            </div>
                        </div>
                    </div>
                    <div class="content" :class="{ 'active': file.expanded }">
                        <!-- File Metadata -->
                        <div class="ui list">
                            <div class="item">
                                <strong>Size:</strong> <span x-text="formatFileSize(file.size)"></span>
                            </div>
                            <div class="item">
                                <strong>Last Modified:</strong> <span x-text="formatDate(file.lastModified)"></span>
                            </div>
                            <div class="item" x-show="file.concepts">
                                <strong>Concepts:</strong> <span x-text="file.concepts"></span>
                            </div>
                            <div class="item" x-show="file.relationships">
                                <strong>Relationships:</strong> <span x-text="file.relationships"></span>
                            </div>
                            <div class="item" x-show="file.agents">
                                <strong>Agents:</strong> <span x-text="file.agents"></span>
                            </div>
                        </div>

                        <!-- Errors -->
                        <div x-show="file.errors?.length > 0" class="ui red message">
                            <div class="header">
                                <i class="times circle icon"></i>
                                Errors (<span x-text="file.errors.length"></span>)
                            </div>
                            <ul class="list">
                                <template x-for="error in file.errors" :key="error.id">
                                    <li>
                                        <div class="error-details">
                                            <strong x-text="error.type || 'Validation Error'"></strong>
                                            <span x-show="error.line" class="error-location">
                                                Line <span x-text="error.line"></span>
                                                <span x-show="error.column">:<span x-text="error.column"></span></span>
                                            </span>
                                        </div>
                                        <div x-text="error.message"></div>
                                        <code x-show="error.context" x-text="error.context"></code>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Warnings -->
                        <div x-show="file.warnings?.length > 0" class="ui yellow message">
                            <div class="header">
                                <i class="warning sign icon"></i>
                                Warnings (<span x-text="file.warnings.length"></span>)
                            </div>
                            <ul class="list">
                                <template x-for="warning in file.warnings" :key="warning.id">
                                    <li>
                                        <div class="warning-details">
                                            <strong x-text="warning.type || 'Validation Warning'"></strong>
                                            <span x-show="warning.line" class="error-location">
                                                Line <span x-text="warning.line"></span>
                                                <span x-show="warning.column">:<span x-text="warning.column"></span></span>
                                            </span>
                                        </div>
                                        <div x-text="warning.message"></div>
                                        <code x-show="warning.context" x-text="warning.context"></code>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Success State -->
                        <div x-show="file.status === 'valid'" class="ui success message">
                            <div class="header">
                                <i class="checkmark icon"></i>
                                Valid YAML Structure
                            </div>
                            <p>This file passes all validation checks.</p>
                        </div>

                        <!-- File Actions -->
                        <div class="ui buttons">
                            <button class="ui button" @click="openFile(file.filename)">
                                <i class="edit icon"></i>
                                Edit File
                            </button>
                            <button class="ui button" @click="validateSingleFile(file.filename)" :disabled="validating">
                                <i class="redo icon"></i>
                                Re-validate
                            </button>
                            <button x-show="file.errors?.length > 0 || file.warnings?.length > 0" 
                                    class="ui button" @click="showFixSuggestions(file)">
                                <i class="wrench icon"></i>
                                Fix Suggestions
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Cross-File Issues -->
    <div x-show="crossFileIssues.length > 0" class="ui warning message">
        <div class="header">
            <i class="exclamation triangle icon"></i>
            Cross-File Issues Found
        </div>
        <ul class="list">
            <template x-for="issue in crossFileIssues" :key="issue.id">
                <li>
                    <strong x-text="issue.type"></strong>: <span x-text="issue.description"></span>
                    <div x-show="issue.affectedFiles?.length > 0">
                        Affected files: 
                        <template x-for="file in issue.affectedFiles" :key="file">
                            <code x-text="file"></code>
                        </template>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    <!-- Schema Validation Rules -->
    <div class="ui segment" x-show="showValidationRules">
        <h4 class="ui header">
            <i class="list icon"></i>
            Validation Rules
        </h4>
        <div class="ui list">
            <div class="item">
                <i class="checkmark icon green"></i>
                <div class="content">
                    <div class="header">Required Metadata</div>
                    <div class="description">Every YAML file must contain metadata with title, version, and description</div>
                </div>
            </div>
            <div class="item">
                <i class="checkmark icon green"></i>
                <div class="content">
                    <div class="header">Concept Structure</div>
                    <div class="description">Concepts must have name, labels, and properties fields</div>
                </div>
            </div>
            <div class="item">
                <i class="checkmark icon green"></i>
                <div class="content">
                    <div class="header">Relationship Validation</div>
                    <div class="description">Relationships must specify from, to, and type fields</div>
                </div>
            </div>
            <div class="item">
                <i class="checkmark icon green"></i>
                <div class="content">
                    <div class="header">Agent References</div>
                    <div class="description">Procedural agents must have valid code_reference pointing to existing methods</div>
                </div>
            </div>
            <div class="item">
                <i class="checkmark icon yellow"></i>
                <div class="content">
                    <div class="header">Circular Dependencies</div>
                    <div class="description">Warning issued for potential circular references in relationships</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="!hasResults && !validating" class="ui placeholder segment">
        <div class="ui icon header">
            <i class="search icon"></i>
            No Validation Results
        </div>
        <div class="inline">
            <div class="ui primary button" @click="runValidation()">
                <i class="checkmark icon"></i>
                Start Validation
            </div>
            <div class="ui button" @click="showValidationRules = !showValidationRules">
                <i class="question circle icon"></i>
                View Rules
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="validating" class="ui active dimmer">
        <div class="ui large text loader">
            <span x-text="validationProgress.message || 'Validating files...'"></span>
            <div x-show="validationProgress.current" class="progress-text">
                <small>
                    Processing <span x-text="validationProgress.current"></span> 
                    of <span x-text="validationProgress.total"></span> files
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function validationView() {
    return {
        // State
        validationMode: 'strict',
        validationScope: 'current',
        validating: false,
        hasResults: false,
        showValidationRules: false,
        
        // Results
        validationResults: {
            filesValidated: 0,
            totalErrors: 0,
            totalWarnings: 0,
            validFiles: 0,
            overallStatus: 'pending'
        },
        fileResults: [],
        crossFileIssues: [],
        validationProgress: {
            current: 0,
            total: 0,
            message: ''
        },
        
        // Methods
        async runValidation() {
            this.validating = true;
            this.validationProgress = { current: 0, total: 0, message: 'Starting validation...' };
            
            try {
                const endpoint = this.getValidationEndpoint();
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        mode: this.validationMode,
                        scope: this.validationScope,
                        include_cross_file_analysis: true
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Validation request failed');
                }
                
                const results = await response.json();
                this.processValidationResults(results);
                
            } catch (error) {
                console.error('Validation failed:', error);
                this.showError('Validation failed: ' + error.message);
            } finally {
                this.validating = false;
                this.validationProgress = { current: 0, total: 0, message: '' };
            }
        },
        
        getValidationEndpoint() {
            switch (this.validationScope) {
                case 'all':
                    return '/soul/knowledge/validate-all';
                case 'directory':
                    return '/soul/knowledge/validate-directory';
                case 'current':
                default:
                    return '/soul/knowledge/validate';
            }
        },
        
        processValidationResults(results) {
            this.validationResults = {
                filesValidated: results.files?.length || 0,
                totalErrors: results.totalErrors || 0,
                totalWarnings: results.totalWarnings || 0,
                validFiles: results.validFiles || 0,
                overallStatus: this.determineOverallStatus(results)
            };
            
            this.fileResults = (results.files || []).map((file, index) => ({
                ...file,
                expanded: false,
                id: index
            }));
            
            this.crossFileIssues = results.crossFileIssues || [];
            this.hasResults = true;
        },
        
        determineOverallStatus(results) {
            if (results.totalErrors > 0) return 'error';
            if (results.totalWarnings > 0) return 'warning';
            return 'success';
        },
        
        getStatusMessage() {
            switch (this.validationResults.overallStatus) {
                case 'success':
                    return 'All files passed validation';
                case 'warning':
                    return 'Validation completed with warnings';
                case 'error':
                    return 'Validation failed with errors';
                default:
                    return 'Validation status unknown';
            }
        },
        
        getStatusDescription() {
            const { filesValidated, totalErrors, totalWarnings, validFiles } = this.validationResults;
            
            if (totalErrors > 0) {
                return `${totalErrors} error${totalErrors > 1 ? 's' : ''} found across ${filesValidated} file${filesValidated > 1 ? 's' : ''}. Please fix these issues before proceeding.`;
            }
            
            if (totalWarnings > 0) {
                return `${totalWarnings} warning${totalWarnings > 1 ? 's' : ''} found. Consider addressing these for better code quality.`;
            }
            
            return `All ${validFiles} file${validFiles > 1 ? 's' : ''} passed validation successfully.`;
        },
        
        toggleFile(index) {
            this.fileResults[index].expanded = !this.fileResults[index].expanded;
        },
        
        async validateSingleFile(filename) {
            this.validating = true;
            
            try {
                const response = await fetch('/soul/knowledge/validate-file', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        filename: filename,
                        mode: this.validationMode
                    })
                });
                
                const result = await response.json();
                
                // Update the specific file result
                const fileIndex = this.fileResults.findIndex(f => f.filename === filename);
                if (fileIndex >= 0) {
                    this.fileResults[fileIndex] = { ...result, expanded: this.fileResults[fileIndex].expanded };
                }
                
                this.recalculateResults();
                
            } catch (error) {
                console.error('Single file validation failed:', error);
                this.showError('File validation failed: ' + error.message);
            } finally {
                this.validating = false;
            }
        },
        
        recalculateResults() {
            const totalErrors = this.fileResults.reduce((sum, file) => sum + (file.errors?.length || 0), 0);
            const totalWarnings = this.fileResults.reduce((sum, file) => sum + (file.warnings?.length || 0), 0);
            const validFiles = this.fileResults.filter(file => file.status === 'valid').length;
            
            this.validationResults = {
                ...this.validationResults,
                totalErrors,
                totalWarnings,
                validFiles,
                overallStatus: this.determineOverallStatus({ totalErrors, totalWarnings })
            };
        },
        
        clearResults() {
            this.hasResults = false;
            this.validationResults = {
                filesValidated: 0,
                totalErrors: 0,
                totalWarnings: 0,
                validFiles: 0,
                overallStatus: 'pending'
            };
            this.fileResults = [];
            this.crossFileIssues = [];
        },
        
        async exportResults() {
            if (!this.hasResults) return;
            
            const exportData = {
                timestamp: new Date().toISOString(),
                validationMode: this.validationMode,
                validationScope: this.validationScope,
                summary: this.validationResults,
                fileResults: this.fileResults,
                crossFileIssues: this.crossFileIssues
            };
            
            const blob = new Blob([JSON.stringify(exportData, null, 2)], { 
                type: 'application/json' 
            });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `soul-validation-report-${new Date().toISOString().split('T')[0]}.json`;
            a.click();
            
            URL.revokeObjectURL(url);
        },
        
        openFile(filename) {
            // Delegate to parent component
            this.$dispatch('load-file', { filename });
        },
        
        showFixSuggestions(file) {
            // Open modal with fix suggestions
            console.log('Fix suggestions for:', file.filename);
            // This would typically open a modal with automated fix suggestions
        },
        
        updateValidationMode() {
            // Re-run validation if results exist
            if (this.hasResults) {
                this.runValidation();
            }
        },
        
        updateValidationScope() {
            // Clear results when scope changes
            this.clearResults();
        },
        
        // Utility methods
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
        
        showError(message) {
            // Show error notification
            console.error(message);
            // In a real implementation, this would show a toast or modal
        }
    };
}
</script>

<style>
.validation-view {
    padding: 1rem;
    height: 100%;
}

.validation-summary {
    margin-bottom: 2rem;
}

.validation-results {
    .ui.accordion {
        .title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            
            .ui.labels {
                margin-left: auto;
            }
        }
        
        .content {
            padding: 1.5rem;
            
            .error-details,
            .warning-details {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 0.5rem;
                
                .error-location {
                    background: rgba(0, 0, 0, 0.1);
                    padding: 0.2rem 0.5rem;
                    border-radius: 0.25rem;
                    font-family: monospace;
                    font-size: 0.9em;
                }
            }
            
            code {
                display: block;
                margin-top: 0.5rem;
                padding: 0.5rem;
                background: #f8f9fa;
                border-radius: 0.25rem;
                font-size: 0.9em;
                white-space: pre-wrap;
            }
        }
    }
}

.progress-text {
    margin-top: 0.5rem;
    opacity: 0.8;
}
</style>