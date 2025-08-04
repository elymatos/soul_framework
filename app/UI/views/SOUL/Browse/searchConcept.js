// Search form component for handling SOUL concept input and HTMX events
function searchConceptForm() {
    return {
        searchQuery: "",
        useSpreadingActivation: false,
        currentToast: null,

        onSearchStart(event) {
            // Store the current query for later use
            window.currentSearchQuery = this.searchQuery;

            // Show Fomantic UI toast
            this.showSearchToast();
        },

        onSearchComplete(event) {
            console.log("Concept search completed");

            // Hide the search toast
            this.hideSearchToast();
        },

        onResultsUpdated(event) {
            // Re-initialize the grid component on the new content
            const gridArea = document.getElementById("gridArea");
            if (gridArea) {
                Alpine.initTree(gridArea);
            }

            // Update query display in the new content
            this.updateQueryDisplay();
            
            // Re-initialize UI components
            this.initializeUIComponents();
        },

        showSearchToast() {
            // Close any existing toast first
            this.hideSearchToast();
            // Create and show the search toast
            this.currentToast = $("body").toast({
                message: "Searching concepts...",
                class: "info",
                showIcon: "brain",
                displayTime: 0, // Don't auto-hide
                position: "top center",
                showProgress: false,
                closeIcon: false,
                silent: true
            });
        },

        hideSearchToast() {
            // Remove the search toast
            if (this.currentToast) {
                $(".ui.toast").toast("close");
                this.currentToast = null;
            }
        },

        updateQueryDisplay() {
            const queryDisplay = document.getElementById("queryDisplay");
            const query = window.currentSearchQuery || this.searchQuery;
            if (queryDisplay && query && query.trim() !== "") {
                queryDisplay.textContent = `Results for: "${query}"`;
            } else if (queryDisplay) {
                queryDisplay.textContent = "";
            }
        },

        initializeUIComponents() {
            // Re-initialize Fomantic UI components after HTMX swap
            setTimeout(() => {
                $('.ui.dropdown').dropdown();
                $('.ui.checkbox').checkbox();
                $('.ui.accordion').accordion();
            }, 100);
        },

        // Filter management
        toggleSpreadingActivation() {
            this.useSpreadingActivation = !this.useSpreadingActivation;
            if (!this.useSpreadingActivation) {
                // Clear spreading activation fields when disabled
                const form = document.querySelector('form');
                const startConceptInput = form.querySelector('input[name="startConcept"]');
                if (startConceptInput) {
                    startConceptInput.value = '';
                }
            }
        },

        clearAllFilters() {
            // Reset all form fields
            const form = document.querySelector('form');
            if (form) {
                // Clear text inputs
                form.querySelectorAll('input[type="text"], input[type="search"], input[type="number"]').forEach(input => {
                    input.value = '';
                });
                
                // Clear checkboxes
                form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Clear dropdowns
                form.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });
                
                // Reset component state
                this.searchQuery = '';
                this.useSpreadingActivation = false;
                
                // Re-initialize UI components
                $('.ui.dropdown').dropdown('clear');
                $('.ui.checkbox').checkbox('uncheck');
                
                // Trigger search with cleared filters
                form.dispatchEvent(new Event('submit'));
            }
        },

        // Concept card interactions
        highlightConcept(conceptName) {
            // Remove existing highlights
            document.querySelectorAll('.result-card.highlighted').forEach(card => {
                card.classList.remove('highlighted');
            });
            
            // Highlight the selected concept
            const card = document.querySelector(`[data-name="${conceptName}"]`);
            if (card) {
                card.classList.add('highlighted');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        // Statistics and info
        showConceptStats() {
            const resultsCount = document.getElementById('resultsCount');
            if (resultsCount) {
                const count = resultsCount.textContent.match(/\d+/);
                if (count && count[0] > 0) {
                    $("body").toast({
                        message: `Found ${count[0]} concepts matching your criteria`,
                        class: "success",
                        showIcon: "check",
                        displayTime: 3000,
                        position: "top center"
                    });
                }
            }
        }
    };
}

// Global utility functions for SOUL concept browsing
window.soulBrowseUtils = {
    // Navigate to concept details
    goToConcept(conceptName) {
        window.location.href = `/soul/browse/${encodeURIComponent(conceptName)}`;
    },

    // Copy concept name to clipboard
    copyConceptName(conceptName) {
        navigator.clipboard.writeText(conceptName).then(() => {
            $("body").toast({
                message: `Copied "${conceptName}" to clipboard`,
                class: "info",
                showIcon: "copy",
                displayTime: 2000,
                position: "bottom right"
            });
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    },

    // Share concept URL
    shareConcept(conceptName) {
        const url = `${window.location.origin}/soul/browse/${encodeURIComponent(conceptName)}`;
        if (navigator.share) {
            navigator.share({
                title: `SOUL Concept: ${conceptName}`,
                url: url
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                $("body").toast({
                    message: "Concept URL copied to clipboard",
                    class: "info",
                    showIcon: "linkify",
                    displayTime: 3000,
                    position: "bottom right"
                });
            });
        }
    },

    // Quick search for related concepts
    searchRelatedConcepts(conceptName) {
        const form = document.querySelector('form');
        const conceptInput = form.querySelector('input[name="concept"]');
        const spreadingCheckbox = form.querySelector('input[name="spreadingActivation"]');
        const startConceptInput = form.querySelector('input[name="startConcept"]');
        
        if (conceptInput && spreadingCheckbox && startConceptInput) {
            // Clear concept name search
            conceptInput.value = '';
            
            // Enable spreading activation
            spreadingCheckbox.checked = true;
            $('.ui.checkbox').checkbox('set checked');
            
            // Set start concept
            startConceptInput.value = conceptName;
            
            // Trigger search
            form.dispatchEvent(new Event('submit'));
        }
    },

    // Initialize SOUL primitives
    initializePrimitives() {
        $("body").toast({
            message: "Initializing SOUL primitives...",
            class: "info",
            showIcon: "cog",
            displayTime: 0,
            position: "top center"
        });
        
        window.location.href = '/soul/browse/initialize';
    },

    // Toggle view mode (cards/list)
    toggleViewMode() {
        const gridArea = document.querySelector('.search-results-grid');
        if (gridArea) {
            gridArea.classList.toggle('list-view');
            gridArea.classList.toggle('card-view');
            
            // Store preference
            const isListView = gridArea.classList.contains('list-view');
            localStorage.setItem('soul-browse-view-mode', isListView ? 'list' : 'cards');
        }
    }
};

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    // Restore view mode preference
    const savedViewMode = localStorage.getItem('soul-browse-view-mode');
    if (savedViewMode === 'list') {
        const gridArea = document.querySelector('.search-results-grid');
        if (gridArea) {
            gridArea.classList.remove('card-view');
            gridArea.classList.add('list-view');
        }
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="concept"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape: Clear search
        if (e.key === 'Escape') {
            const searchInput = document.querySelector('input[name="concept"]');
            if (searchInput && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.blur();
            }
        }
    });
});