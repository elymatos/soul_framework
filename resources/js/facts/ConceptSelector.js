/**
 * Concept Selector Component
 * 
 * A smart concept selection component for building triplet facts.
 * Provides autocomplete, validation, and role-based concept selection
 * with support for primitive concept highlighting and frequency-based sorting.
 */
class ConceptSelector {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with id '${containerId}' not found`);
        }

        // Default options
        this.options = {
            apiBaseUrl: '/facts',
            multiSelect: false,
            showFrequency: true,
            showPrimitiveStatus: true,
            placeholder: 'Search concepts...',
            minSearchLength: 2,
            maxResults: 20,
            allowCreate: true,
            role: null, // subject, predicate, object, modifier, etc.
            excludeConcepts: [],
            ...options
        };

        // Component state
        this.selectedConcepts = new Set();
        this.availableConcepts = [];
        this.searchCache = new Map();
        this.isLoading = false;

        // Event callbacks
        this.eventCallbacks = {
            onConceptSelect: null,
            onConceptRemove: null,
            onSearchChange: null,
            onValidationChange: null
        };

        this.initialize();
    }

    /**
     * Initialize the component
     */
    initialize() {
        this.createInterface();
        this.setupEventHandlers();
        this.loadInitialConcepts();
    }

    /**
     * Create the component interface
     */
    createInterface() {
        this.container.className = 'concept-selector';
        this.container.innerHTML = `
            <div class="ui fluid search selection dropdown ${this.options.role ? this.options.role + '-selector' : ''}">
                <input type="hidden" name="selected-concepts">
                <i class="dropdown icon"></i>
                <div class="default text">${this.options.placeholder}</div>
                <div class="menu">
                    <div class="message">
                        <div class="header">Search for concepts</div>
                        <p>Type to search available concepts or create new ones</p>
                    </div>
                </div>
            </div>
            <div class="concept-selector-tags" style="margin-top: 8px;"></div>
            <div class="concept-selector-validation" style="margin-top: 5px;"></div>
        `;

        // Add role-specific styling
        if (this.options.role) {
            this.container.classList.add(`role-${this.options.role}`);
            this.addRoleStyling();
        }
    }

    /**
     * Add role-specific styling
     */
    addRoleStyling() {
        const roleColors = {
            subject: '#e74c3c',
            predicate: '#2ecc71', 
            object: '#3498db',
            modifier: '#f39c12',
            temporal: '#9b59b6',
            spatial: '#1abc9c',
            causal: '#e67e22'
        };

        const color = roleColors[this.options.role] || '#95a5a6';
        
        const style = document.createElement('style');
        style.textContent = `
            .concept-selector.role-${this.options.role} .ui.dropdown {
                border-left: 4px solid ${color};
            }
            .concept-selector.role-${this.options.role} .concept-tag {
                background-color: ${color};
                color: white;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        const searchInput = this.container.querySelector('input[type="hidden"]');

        // Initialize Fomantic UI dropdown with custom settings
        $(dropdown).dropdown({
            clearable: true,
            search: true,
            selectOnKeydown: false,
            allowAdditions: this.options.allowCreate,
            hideAdditions: false,
            maxSelections: this.options.multiSelect ? false : 1,
            message: {
                addResult: 'Add <b>{term}</b> as new concept',
                count: '{count} concepts selected',
                maxSelections: 'Max {maxCount} concepts allowed',
                noResults: 'No concepts found. Type to create new concept.'
            },
            apiSettings: {
                url: `${this.options.apiBaseUrl}/concepts/available?search={query}&limit=${this.options.maxResults}`,
                cache: false,
                beforeSend: (settings) => {
                    this.isLoading = true;
                    this.showLoading();
                    return settings;
                },
                onResponse: (response) => {
                    this.isLoading = false;
                    this.hideLoading();
                    return this.formatApiResponse(response);
                },
                onError: () => {
                    this.isLoading = false;
                    this.hideLoading();
                }
            },
            onChange: (value, text, $choice) => {
                this.handleConceptChange(value, text, $choice);
            },
            onAdd: (addedValue, addedText, $addedChoice) => {
                this.handleConceptAdd(addedValue, addedText, $addedChoice);
            },
            onRemove: (removedValue, removedText, $removedChoice) => {
                this.handleConceptRemove(removedValue, removedText, $removedChoice);
            }
        });

        // Custom search handling for better UX
        const searchField = dropdown.querySelector('.search');
        if (searchField) {
            let searchTimeout;
            searchField.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearchInput(e.target.value);
                }, 300);
            });
        }
    }

    /**
     * Load initial concepts for quick selection
     */
    async loadInitialConcepts() {
        try {
            const response = await window.ky.get(`${this.options.apiBaseUrl}/concepts/available`, {
                searchParams: {
                    limit: this.options.maxResults,
                    type: 'primitive' // Start with primitive concepts
                }
            }).json();

            if (response.success) {
                this.availableConcepts = response.data;
                this.updateDropdownOptions(this.availableConcepts);
            }
        } catch (error) {
            console.error('Failed to load initial concepts:', error);
        }
    }

    /**
     * Format API response for Fomantic UI dropdown
     */
    formatApiResponse(response) {
        if (!response.success || !response.data) {
            return { results: [] };
        }

        const results = response.data
            .filter(concept => !this.options.excludeConcepts.includes(concept.name))
            .map(concept => ({
                name: concept.name,
                value: concept.name,
                text: this.formatConceptText(concept),
                description: this.formatConceptDescription(concept)
            }));

        return { results: results };
    }

    /**
     * Format concept text for display
     */
    formatConceptText(concept) {
        let text = concept.name;
        
        if (this.options.showPrimitiveStatus && concept.is_primitive) {
            text += ' ⚡'; // Lightning bolt for primitive concepts
        }
        
        if (this.options.showFrequency && concept.frequency > 0) {
            text += ` (${concept.frequency})`;
        }
        
        return text;
    }

    /**
     * Format concept description
     */
    formatConceptDescription(concept) {
        const parts = [];
        
        if (concept.category) {
            parts.push(concept.category);
        }
        
        if (concept.is_primitive) {
            parts.push('Primitive');
        }
        
        if (concept.frequency > 0) {
            parts.push(`Used in ${concept.frequency} facts`);
        }
        
        return parts.join(' • ');
    }

    /**
     * Update dropdown options
     */
    updateDropdownOptions(concepts) {
        const dropdown = this.container.querySelector('.ui.dropdown');
        const menu = dropdown.querySelector('.menu');
        
        // Clear existing options
        menu.innerHTML = '';
        
        if (concepts.length === 0) {
            menu.innerHTML = `
                <div class="message">
                    <div class="header">No concepts found</div>
                    <p>Type to create a new concept</p>
                </div>
            `;
            return;
        }

        // Add concept options
        concepts.forEach(concept => {
            const item = document.createElement('div');
            item.className = 'item';
            item.setAttribute('data-value', concept.name);
            item.innerHTML = `
                <div class="content">
                    <div class="title">
                        ${concept.name}
                        ${concept.is_primitive ? '<i class="lightning bolt yellow icon" title="Primitive Concept"></i>' : ''}
                        ${concept.frequency > 0 ? `<span class="ui mini circular label">${concept.frequency}</span>` : ''}
                    </div>
                    ${concept.category ? `<div class="description">${concept.category}</div>` : ''}
                </div>
            `;
            menu.appendChild(item);
        });
    }

    /**
     * Handle concept change
     */
    handleConceptChange(value, text, $choice) {
        this.updateSelectedConcepts();
        this.updateTagsDisplay();
        this.validateSelection();
        
        if (this.eventCallbacks.onConceptSelect) {
            this.eventCallbacks.onConceptSelect(value, text, this.getSelectedConcepts());
        }
    }

    /**
     * Handle concept addition
     */
    handleConceptAdd(addedValue, addedText, $addedChoice) {
        this.selectedConcepts.add(addedValue);
        this.updateTagsDisplay();
        this.validateSelection();
        
        if (this.eventCallbacks.onConceptSelect) {
            this.eventCallbacks.onConceptSelect(addedValue, addedText, this.getSelectedConcepts());
        }
    }

    /**
     * Handle concept removal
     */
    handleConceptRemove(removedValue, removedText, $removedChoice) {
        this.selectedConcepts.delete(removedValue);
        this.updateTagsDisplay();
        this.validateSelection();
        
        if (this.eventCallbacks.onConceptRemove) {
            this.eventCallbacks.onConceptRemove(removedValue, removedText, this.getSelectedConcepts());
        }
    }

    /**
     * Handle search input
     */
    handleSearchInput(searchTerm) {
        if (this.eventCallbacks.onSearchChange) {
            this.eventCallbacks.onSearchChange(searchTerm, this.options.role);
        }
    }

    /**
     * Update selected concepts set
     */
    updateSelectedConcepts() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        const values = $(dropdown).dropdown('get values');
        
        this.selectedConcepts.clear();
        if (Array.isArray(values)) {
            values.forEach(value => this.selectedConcepts.add(value));
        } else if (values) {
            this.selectedConcepts.add(values);
        }
    }

    /**
     * Update tags display for selected concepts
     */
    updateTagsDisplay() {
        const tagsContainer = this.container.querySelector('.concept-selector-tags');
        
        if (this.selectedConcepts.size === 0) {
            tagsContainer.innerHTML = '';
            return;
        }

        const tags = Array.from(this.selectedConcepts).map(concept => {
            const conceptData = this.availableConcepts.find(c => c.name === concept);
            const isPrimitive = conceptData?.is_primitive || false;
            const frequency = conceptData?.frequency || 0;
            
            return `
                <div class="ui label concept-tag ${isPrimitive ? 'primitive' : 'derived'}" data-concept="${concept}">
                    ${concept}
                    ${isPrimitive ? '<i class="lightning bolt icon"></i>' : ''}
                    ${frequency > 0 ? `<div class="detail">${frequency}</div>` : ''}
                    <i class="delete icon" onclick="conceptSelector_${this.containerId}.removeConcept('${concept}')"></i>
                </div>
            `;
        }).join('');
        
        tagsContainer.innerHTML = tags;
    }

    /**
     * Validate current selection
     */
    validateSelection() {
        const validationContainer = this.container.querySelector('.concept-selector-validation');
        const errors = this.getValidationErrors();
        
        if (errors.length === 0) {
            validationContainer.innerHTML = '';
            validationContainer.className = 'concept-selector-validation';
        } else {
            validationContainer.className = 'concept-selector-validation ui negative message';
            validationContainer.innerHTML = `
                <div class="header">Validation Errors</div>
                <ul class="list">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            `;
        }
        
        if (this.eventCallbacks.onValidationChange) {
            this.eventCallbacks.onValidationChange(errors, this.isValid());
        }
    }

    /**
     * Get validation errors
     */
    getValidationErrors() {
        const errors = [];
        
        // Check required selection
        if (this.options.required && this.selectedConcepts.size === 0) {
            errors.push(`${this.options.role || 'Concept'} selection is required`);
        }
        
        // Check single selection for core triplet roles
        if (['subject', 'predicate', 'object'].includes(this.options.role) && this.selectedConcepts.size > 1) {
            errors.push(`Only one ${this.options.role} concept is allowed`);
        }
        
        // Check for excluded concepts
        const excluded = Array.from(this.selectedConcepts).filter(concept => 
            this.options.excludeConcepts.includes(concept)
        );
        if (excluded.length > 0) {
            errors.push(`Concepts not allowed: ${excluded.join(', ')}`);
        }
        
        return errors;
    }

    /**
     * Check if current selection is valid
     */
    isValid() {
        return this.getValidationErrors().length === 0;
    }

    /**
     * Get selected concepts
     */
    getSelectedConcepts() {
        return Array.from(this.selectedConcepts);
    }

    /**
     * Set selected concepts
     */
    setSelectedConcepts(concepts) {
        const dropdown = this.container.querySelector('.ui.dropdown');
        
        if (Array.isArray(concepts)) {
            $(dropdown).dropdown('set exactly', concepts);
        } else {
            $(dropdown).dropdown('set selected', concepts);
        }
        
        this.updateSelectedConcepts();
        this.updateTagsDisplay();
        this.validateSelection();
    }

    /**
     * Remove a specific concept
     */
    removeConcept(concept) {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).dropdown('remove selected', concept);
        
        this.selectedConcepts.delete(concept);
        this.updateTagsDisplay();
        this.validateSelection();
        
        if (this.eventCallbacks.onConceptRemove) {
            this.eventCallbacks.onConceptRemove(concept, concept, this.getSelectedConcepts());
        }
    }

    /**
     * Clear all selections
     */
    clear() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).dropdown('clear');
        
        this.selectedConcepts.clear();
        this.updateTagsDisplay();
        this.validateSelection();
    }

    /**
     * Set exclude list
     */
    setExcludeConcepts(concepts) {
        this.options.excludeConcepts = concepts || [];
        
        // Remove any currently selected concepts that are now excluded
        const toRemove = Array.from(this.selectedConcepts).filter(concept => 
            this.options.excludeConcepts.includes(concept)
        );
        
        toRemove.forEach(concept => this.removeConcept(concept));
    }

    /**
     * Enable/disable the component
     */
    setEnabled(enabled) {
        const dropdown = this.container.querySelector('.ui.dropdown');
        
        if (enabled) {
            $(dropdown).removeClass('disabled');
        } else {
            $(dropdown).addClass('disabled');
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).addClass('loading');
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).removeClass('loading');
    }

    /**
     * Focus the component
     */
    focus() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).dropdown('show');
    }

    /**
     * Register event callback
     */
    on(eventName, callback) {
        const callbackName = `on${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`;
        if (this.eventCallbacks.hasOwnProperty(callbackName)) {
            this.eventCallbacks[callbackName] = callback;
        }
    }

    /**
     * Destroy the component
     */
    destroy() {
        const dropdown = this.container.querySelector('.ui.dropdown');
        $(dropdown).dropdown('destroy');
        this.container.innerHTML = '';
    }
}

// Factory function for creating concept selectors
window.createConceptSelector = function(containerId, options = {}) {
    const selector = new ConceptSelector(containerId, options);
    // Store reference for global access (needed for tag removal)
    window[`conceptSelector_${containerId}`] = selector;
    return selector;
};

// Export for use in other modules
export default ConceptSelector;