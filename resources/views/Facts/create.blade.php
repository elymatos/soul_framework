@extends('Layout.main')

@section('title', 'Create New Fact')

@section('content')
<div class="ui container" style="margin-top: 20px;">
    <!-- Header -->
    <div class="ui grid">
        <div class="twelve wide column">
            <h2 class="ui header">
                <i class="plus icon"></i>
                <div class="content">
                    Create New Fact
                    <div class="sub header">Build a new triplet-based fact relationship</div>
                </div>
            </h2>
        </div>
        <div class="four wide column">
            <div class="ui right floated buttons">
                <a href="/facts" class="ui button">
                    <i class="arrow left icon"></i>
                    Back to Facts
                </a>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="ui two column grid">
        <!-- Form Column -->
        <div class="ten wide column">
            <form class="ui form" id="create-fact-form" action="/facts" method="POST">
                @csrf
                
                <!-- Core Triplet Section -->
                <div class="ui segment">
                    <h3 class="ui header">
                        <i class="linkify icon"></i>
                        Core Triplet
                        <div class="sub header">Define the subject-predicate-object relationship</div>
                    </h3>

                    <div class="three fields">
                        <!-- Subject -->
                        <div class="field required">
                            <label>Subject</label>
                            <div id="subject-selector" class="concept-selector role-subject"></div>
                            <div class="ui pointing red basic label" id="subject-error" style="display: none;"></div>
                        </div>

                        <!-- Predicate -->
                        <div class="field required">
                            <label>Predicate</label>
                            <div id="predicate-selector" class="concept-selector role-predicate"></div>
                            <div class="ui pointing red basic label" id="predicate-error" style="display: none;"></div>
                        </div>

                        <!-- Object -->
                        <div class="field required">
                            <label>Object</label>
                            <div id="object-selector" class="concept-selector role-object"></div>
                            <div class="ui pointing red basic label" id="object-error" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Triplet Validation -->
                    <div class="ui info message" id="triplet-validation" style="display: none;">
                        <div class="header">Triplet Validation</div>
                        <div id="validation-content"></div>
                    </div>

                    <!-- Generated Statement -->
                    <div class="field">
                        <label>Generated Statement</label>
                        <div class="ui fluid input">
                            <input type="text" name="statement" id="statement-input" placeholder="Statement will be generated from triplet...">
                        </div>
                        <div class="ui pointing label">
                            This will be auto-generated from your triplet selection, but you can customize it.
                        </div>
                    </div>
                </div>

                <!-- Extended Concepts Section -->
                <div class="ui segment">
                    <h3 class="ui header">
                        <i class="plus circle icon"></i>
                        Extended Concepts
                        <div class="sub header">Add modifiers, temporal, spatial, and causal context (optional)</div>
                    </h3>

                    <div class="ui grid">
                        <div class="eight wide column">
                            <!-- Modifiers -->
                            <div class="field">
                                <label>Modifiers</label>
                                <div id="modifier-selector" class="concept-selector role-modifier"></div>
                                <div class="ui pointing label">
                                    Add concepts that modify the core relationship
                                </div>
                            </div>

                            <!-- Temporal Context -->
                            <div class="field">
                                <label>Temporal Context</label>
                                <div id="temporal-selector" class="concept-selector role-temporal"></div>
                                <div class="ui pointing label">
                                    Add time-related concepts (past, present, future, etc.)
                                </div>
                            </div>
                        </div>
                        <div class="eight wide column">
                            <!-- Spatial Context -->
                            <div class="field">
                                <label>Spatial Context</label>
                                <div id="spatial-selector" class="concept-selector role-spatial"></div>
                                <div class="ui pointing label">
                                    Add location or spatial concepts
                                </div>
                            </div>

                            <!-- Causal Context -->
                            <div class="field">
                                <label>Causal Context</label>
                                <div id="causal-selector" class="concept-selector role-causal"></div>
                                <div class="ui pointing label">
                                    Add cause-effect relationships
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fact Properties Section -->
                <div class="ui segment">
                    <h3 class="ui header">
                        <i class="settings icon"></i>
                        Fact Properties
                        <div class="sub header">Configure the fact's metadata and properties</div>
                    </h3>

                    <div class="two fields">
                        <!-- Confidence -->
                        <div class="field">
                            <label>Confidence Level</label>
                            <div class="ui labeled input">
                                <div class="ui label">0</div>
                                <input type="range" name="confidence" min="0" max="1" step="0.1" value="1.0" id="confidence-slider">
                                <div class="ui label" id="confidence-value">1.0</div>
                            </div>
                            <div class="ui pointing label">
                                How confident are you in this fact? (0.0 = uncertain, 1.0 = certain)
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="field">
                            <label>Priority</label>
                            <select class="ui dropdown" name="priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>

                    <div class="two fields">
                        <!-- Domain -->
                        <div class="field">
                            <label>Domain</label>
                            <div class="ui search selection dropdown" name="domain">
                                <input type="hidden" name="domain">
                                <i class="dropdown icon"></i>
                                <div class="default text">Select or create domain</div>
                                <div class="menu">
                                    <div class="item" data-value="general">General</div>
                                    <div class="item" data-value="science">Science</div>
                                    <div class="item" data-value="social">Social</div>
                                    <div class="item" data-value="business">Business</div>
                                    <div class="item" data-value="personal">Personal</div>
                                </div>
                            </div>
                        </div>

                        <!-- Fact Type -->
                        <div class="field">
                            <label>Fact Type</label>
                            <select class="ui dropdown" name="fact_type">
                                <option value="fact">Fact</option>
                                <option value="hypothesis">Hypothesis</option>
                                <option value="rule">Rule</option>
                                <option value="constraint">Constraint</option>
                            </select>
                        </div>
                    </div>

                    <!-- Source -->
                    <div class="field">
                        <label>Source</label>
                        <div class="ui input">
                            <input type="text" name="source" placeholder="Source of this fact (URL, book, person, etc.)">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="field">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Additional description or context for this fact..."></textarea>
                    </div>

                    <!-- Tags -->
                    <div class="field">
                        <label>Tags</label>
                        <div class="ui multiple search selection dropdown" id="tags-dropdown">
                            <input type="hidden" name="tags">
                            <i class="dropdown icon"></i>
                            <div class="default text">Add tags...</div>
                            <div class="menu">
                                <!-- Tags loaded dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- Verification -->
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="verified" value="1">
                            <label>Mark as verified</label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="ui segment">
                    <div class="ui buttons">
                        <button type="submit" class="ui primary button" id="create-button">
                            <i class="save icon"></i>
                            Create Fact
                        </button>
                        <div class="or"></div>
                        <button type="button" class="ui button" onclick="previewFact()">
                            <i class="eye icon"></i>
                            Preview
                        </button>
                        <button type="button" class="ui button" onclick="validateTriplet()">
                            <i class="checkmark icon"></i>
                            Validate
                        </button>
                        <button type="reset" class="ui button">
                            <i class="eraser icon"></i>
                            Clear Form
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview Column -->
        <div class="six wide column">
            <div class="ui sticky" id="preview-panel">
                <div class="ui segment">
                    <h3 class="ui header">
                        <i class="eye icon"></i>
                        Live Preview
                    </h3>
                    
                    <div id="fact-preview">
                        <div class="ui placeholder">
                            <div class="header">
                                <div class="line"></div>
                                <div class="line"></div>
                            </div>
                            <div class="paragraph">
                                <div class="line"></div>
                                <div class="line"></div>
                                <div class="line"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Panel -->
                <div class="ui segment">
                    <h4 class="ui header">
                        <i class="lightbulb outline icon"></i>
                        Tips
                    </h4>
                    <div class="ui list">
                        <div class="item">
                            <i class="arrow right icon"></i>
                            <div class="content">Start with the core triplet: subject-predicate-object</div>
                        </div>
                        <div class="item">
                            <i class="arrow right icon"></i>
                            <div class="content">Use primitive concepts when possible</div>
                        </div>
                        <div class="item">
                            <i class="arrow right icon"></i>
                            <div class="content">Add modifiers to provide more context</div>
                        </div>
                        <div class="item">
                            <i class="arrow right icon"></i>
                            <div class="content">Set appropriate confidence levels</div>
                        </div>
                        <div class="item">
                            <i class="arrow right icon"></i>
                            <div class="content">Tag your facts for better organization</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fact Preview Modal -->
<div id="fact-preview-modal" class="ui modal">
    <i class="close icon"></i>
    <div class="header">
        <i class="eye icon"></i>
        Fact Preview
    </div>
    <div class="content">
        <div id="modal-preview-content">
            <!-- Preview content loaded here -->
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Close</div>
        <div class="ui primary button" onclick="submitForm()">
            <i class="save icon"></i>
            Create Fact
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="module">
    import ConceptSelector from '/scripts/facts/ConceptSelector.js';

    // Initialize concept selectors
    let conceptSelectors = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all concept selectors
        initializeConceptSelectors();
        
        // Initialize other form elements
        initializeFormElements();
        
        // Setup form validation
        setupFormValidation();
        
        // Setup live preview
        setupLivePreview();
    });

    function initializeConceptSelectors() {
        const roles = ['subject', 'predicate', 'object', 'modifier', 'temporal', 'spatial', 'causal'];
        
        roles.forEach(role => {
            const isRequired = ['subject', 'predicate', 'object'].includes(role);
            const multiSelect = !['subject', 'predicate', 'object'].includes(role);
            
            conceptSelectors[role] = new ConceptSelector(`${role}-selector`, {
                role: role,
                required: isRequired,
                multiSelect: multiSelect,
                apiBaseUrl: '/facts',
                placeholder: `Select ${role} concept${multiSelect ? 's' : ''}...`,
                allowCreate: true,
                showFrequency: true,
                showPrimitiveStatus: true
            });

            // Setup event handlers
            conceptSelectors[role].on('conceptSelect', (concept, text, allConcepts) => {
                updateExcludeLists();
                generateStatement();
                updatePreview();
                if (isRequired) {
                    validateTriplet();
                }
            });

            conceptSelectors[role].on('conceptRemove', (concept, text, allConcepts) => {
                updateExcludeLists();
                generateStatement();
                updatePreview();
                if (isRequired) {
                    validateTriplet();
                }
            });

            conceptSelectors[role].on('validationChange', (errors, isValid) => {
                updateFieldValidation(role, errors, isValid);
            });
        });
    }

    function initializeFormElements() {
        // Initialize dropdowns
        $('.ui.dropdown').dropdown({
            allowAdditions: true
        });

        // Initialize checkboxes
        $('.ui.checkbox').checkbox();

        // Setup confidence slider
        const confidenceSlider = document.getElementById('confidence-slider');
        const confidenceValue = document.getElementById('confidence-value');
        
        confidenceSlider.addEventListener('input', function() {
            confidenceValue.textContent = this.value;
            updatePreview();
        });

        // Setup tags dropdown
        $('#tags-dropdown').dropdown({
            allowAdditions: true,
            hideAdditions: false
        });

        // Initialize sticky preview panel
        $('#preview-panel').sticky({
            context: '.ui.grid',
            offset: 20
        });
    }

    function setupFormValidation() {
        $('#create-fact-form').form({
            fields: {
                statement: {
                    identifier: 'statement',
                    rules: [{
                        type: 'empty',
                        prompt: 'Statement is required'
                    }]
                },
                confidence: {
                    identifier: 'confidence',
                    rules: [{
                        type: 'number',
                        prompt: 'Confidence must be a number between 0 and 1'
                    }]
                }
            },
            onSuccess: function(event, fields) {
                event.preventDefault();
                submitForm();
            }
        });
    }

    function setupLivePreview() {
        // Update preview when any input changes
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', updatePreview);
            element.addEventListener('input', updatePreview);
        });

        // Initial preview update
        updatePreview();
    }

    function updateExcludeLists() {
        // Get all selected concepts
        const allSelected = new Set();
        Object.values(conceptSelectors).forEach(selector => {
            selector.getSelectedConcepts().forEach(concept => allSelected.add(concept));
        });

        // Update exclude lists for core triplet selectors
        ['subject', 'predicate', 'object'].forEach(role => {
            const others = new Set(allSelected);
            conceptSelectors[role].getSelectedConcepts().forEach(concept => others.delete(concept));
            conceptSelectors[role].setExcludeConcepts(Array.from(others));
        });
    }

    function generateStatement() {
        const subject = conceptSelectors.subject.getSelectedConcepts()[0];
        const predicate = conceptSelectors.predicate.getSelectedConcepts()[0];
        const object = conceptSelectors.object.getSelectedConcepts()[0];

        if (subject && predicate && object) {
            let statement = `${subject} ${predicate} ${object}`;
            
            // Add modifiers
            const modifiers = conceptSelectors.modifier.getSelectedConcepts();
            if (modifiers.length > 0) {
                statement += ` [${modifiers.join(', ')}]`;
            }

            document.getElementById('statement-input').value = statement;
        }
    }

    function validateTriplet() {
        const subject = conceptSelectors.subject.getSelectedConcepts()[0];
        const predicate = conceptSelectors.predicate.getSelectedConcepts()[0];
        const object = conceptSelectors.object.getSelectedConcepts()[0];

        if (!subject || !predicate || !object) {
            return;
        }

        fetch('/facts/validate-triplet', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                subject: subject,
                predicate: predicate,
                object: object
            })
        })
        .then(response => response.json())
        .then(data => {
            updateTripletValidation(data);
        })
        .catch(error => {
            console.error('Validation error:', error);
        });
    }

    function updateTripletValidation(validation) {
        const panel = document.getElementById('triplet-validation');
        const content = document.getElementById('validation-content');

        if (validation.valid) {
            panel.className = 'ui positive message';
            content.innerHTML = `
                <p><i class="checkmark icon"></i> Triplet is valid</p>
                <p>Concepts checked: ${validation.concepts_checked}</p>
            `;
        } else {
            panel.className = 'ui negative message';
            content.innerHTML = `
                <p><i class="times icon"></i> Triplet validation failed</p>
                <ul class="list">
                    ${validation.errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            `;
        }

        panel.style.display = 'block';
    }

    function updateFieldValidation(role, errors, isValid) {
        const errorElement = document.getElementById(`${role}-error`);
        
        if (errors.length > 0) {
            errorElement.textContent = errors[0];
            errorElement.style.display = 'block';
        } else {
            errorElement.style.display = 'none';
        }
    }

    function updatePreview() {
        const previewElement = document.getElementById('fact-preview');
        
        // Get form data
        const formData = getFormData();
        
        // Generate preview HTML
        const previewHTML = generatePreviewHTML(formData);
        previewElement.innerHTML = previewHTML;
    }

    function getFormData() {
        return {
            statement: document.getElementById('statement-input').value,
            subject: conceptSelectors.subject.getSelectedConcepts()[0],
            predicate: conceptSelectors.predicate.getSelectedConcepts()[0],
            object: conceptSelectors.object.getSelectedConcepts()[0],
            modifiers: conceptSelectors.modifier.getSelectedConcepts(),
            temporal: conceptSelectors.temporal.getSelectedConcepts(),
            spatial: conceptSelectors.spatial.getSelectedConcepts(),
            causal: conceptSelectors.causal.getSelectedConcepts(),
            confidence: document.getElementById('confidence-slider').value,
            priority: document.querySelector('select[name="priority"]').value,
            domain: document.querySelector('input[name="domain"]').value,
            fact_type: document.querySelector('select[name="fact_type"]').value,
            source: document.querySelector('input[name="source"]').value,
            description: document.querySelector('textarea[name="description"]').value,
            verified: document.querySelector('input[name="verified"]').checked
        };
    }

    function generatePreviewHTML(data) {
        if (!data.subject || !data.predicate || !data.object) {
            return `
                <div class="ui placeholder">
                    <div class="header">
                        <div class="line"></div>
                    </div>
                    <div class="paragraph">
                        <div class="line"></div>
                        <div class="line"></div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="ui card" style="width: 100%;">
                <div class="content">
                    <div class="header">${data.statement}</div>
                    <div class="meta">
                        <span class="category">${data.fact_type}</span>
                        ${data.domain ? `• ${data.domain}` : ''}
                    </div>
                    <div class="description">
                        <div class="triplet-display">
                            <div><span class="triplet-role role-subject">SUBJ:</span> <strong>${data.subject}</strong></div>
                            <div><span class="triplet-role role-predicate">PRED:</span> <strong>${data.predicate}</strong></div>
                            <div><span class="triplet-role role-object">OBJ:</span> <strong>${data.object}</strong></div>
                            ${data.modifiers.length > 0 ? `<div><span class="triplet-role role-modifier">MOD:</span> ${data.modifiers.join(', ')}</div>` : ''}
                        </div>
                        ${data.description ? `<p>${data.description}</p>` : ''}
                    </div>
                </div>
                <div class="extra content">
                    <div class="ui labels">
                        ${data.verified ? '<div class="ui green label"><i class="checkmark icon"></i> Verified</div>' : ''}
                        <div class="ui label">Confidence: ${data.confidence}</div>
                        <div class="ui label">Priority: ${data.priority}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function previewFact() {
        const formData = getFormData();
        const previewHTML = generatePreviewHTML(formData);
        
        document.getElementById('modal-preview-content').innerHTML = previewHTML;
        $('#fact-preview-modal').modal('show');
    }

    function submitForm() {
        const form = document.getElementById('create-fact-form');
        const formData = new FormData(form);

        // Add concept data
        formData.set('subject_concept', conceptSelectors.subject.getSelectedConcepts()[0] || '');
        formData.set('predicate_concept', conceptSelectors.predicate.getSelectedConcepts()[0] || '');
        formData.set('object_concept', conceptSelectors.object.getSelectedConcepts()[0] || '');
        
        if (conceptSelectors.modifier.getSelectedConcepts().length > 0) {
            formData.set('modifier_concepts', JSON.stringify(conceptSelectors.modifier.getSelectedConcepts()));
        }
        if (conceptSelectors.temporal.getSelectedConcepts().length > 0) {
            formData.set('temporal_concepts', JSON.stringify(conceptSelectors.temporal.getSelectedConcepts()));
        }
        if (conceptSelectors.spatial.getSelectedConcepts().length > 0) {
            formData.set('spatial_concepts', JSON.stringify(conceptSelectors.spatial.getSelectedConcepts()));
        }
        if (conceptSelectors.causal.getSelectedConcepts().length > 0) {
            formData.set('causal_concepts', JSON.stringify(conceptSelectors.causal.getSelectedConcepts()));
        }

        // Show loading state
        const submitButton = document.getElementById('create-button');
        submitButton.classList.add('loading');

        fetch('/facts', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            submitButton.classList.remove('loading');
            
            if (data.success) {
                showSuccessMessage('Fact created successfully!');
                setTimeout(() => {
                    window.location.href = `/facts/${data.data.fact_id}`;
                }, 1500);
            } else {
                showErrorMessage(data.message || 'Failed to create fact');
            }
        })
        .catch(error => {
            submitButton.classList.remove('loading');
            showErrorMessage('Failed to create fact: ' + error.message);
        });
    }

    // Export functions for global access
    window.previewFact = previewFact;
    window.validateTriplet = validateTriplet;
    window.submitForm = submitForm;
</script>
@endsection

@section('styles')
<style>
    .triplet-display {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        border-left: 4px solid #3498db;
        margin: 10px 0;
        font-family: 'Courier New', monospace;
    }

    .triplet-role {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.8em;
        margin-right: 8px;
        display: inline-block;
        width: 50px;
    }

    .role-subject { color: #e74c3c; }
    .role-predicate { color: #2ecc71; }
    .role-object { color: #3498db; }
    .role-modifier { color: #f39c12; }

    .concept-selector {
        margin-bottom: 10px;
    }

    .confidence-bar {
        height: 4px;
        border-radius: 2px;
        margin-top: 5px;
    }

    #preview-panel .ui.sticky {
        width: 100%;
    }

    .field .pointing.label {
        font-size: 0.85em;
        margin-top: 5px;
    }

    .concept-tag {
        margin: 2px !important;
    }
</style>
@endsection