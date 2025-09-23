<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOUL Framework - Graph Interface</title>
    @vite(['resources/css/app.less', 'resources/css/graph.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-8" x-data="graphInterface()">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Graph Database Interface
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Cortical Column-Based Cognitive Framework - Phase 2.1
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6 flex flex-wrap gap-4">
            <button
                @click="showCreateNeuronModal = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
            >
                Create Neuron
            </button>
            <button
                @click="showCreateRelationshipModal = true"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
            >
                Create Relationship
            </button>
            <button
                @click="loadNeurons()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
            >
                Refresh
            </button>
        </div>

        <!-- Neurons Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Neurons List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Neurons (<span x-text="neurons.length"></span>)
                </h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <template x-for="neuron in neurons" :key="neuron.n?.id || neuron.id">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white" x-text="neuron.n?.properties?.name || neuron.properties?.name || 'Unknown'"></h3>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-1">
                                        <div>ID: <span x-text="neuron.n?.id || neuron.id"></span></div>
                                        <div>Layer: <span x-text="neuron.n?.properties?.layer || neuron.properties?.layer || 'N/A'"></span></div>
                                        <div>Activation: <span x-text="(neuron.n?.properties?.activation_level || neuron.properties?.activation_level || 0).toFixed(2)"></span></div>
                                        <div>Threshold: <span x-text="(neuron.n?.properties?.threshold || neuron.properties?.threshold || 0).toFixed(2)"></span></div>
                                    </div>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <button
                                        @click="editNeuron(neuron)"
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteNeuron(neuron.n?.id || neuron.id)"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="neurons.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8">
                        No neurons found. Create your first neuron to get started.
                    </div>
                </div>
            </div>

            <!-- Graph Visualization Preview -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Network Preview
                </h2>
                <div id="graph-preview" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg h-80 flex items-center justify-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        Graph visualization will appear here<br>
                        <span class="text-sm">(Coming in Phase 2.2)</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Status Messages -->
        <div x-show="message" x-transition class="mb-4">
            <div
                :class="messageType === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'"
                class="border px-4 py-3 rounded relative"
            >
                <span x-text="message"></span>
                <button @click="message = ''" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <span class="text-xl">&times;</span>
                </button>
            </div>
        </div>

        <!-- Create Neuron Modal -->
        <div x-show="showCreateNeuronModal" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create New Neuron</h3>
                    <form @submit.prevent="createNeuron()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                            <input
                                type="text"
                                x-model="newNeuron.name"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Layer</label>
                            <select
                                x-model="newNeuron.layer"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select Layer</option>
                                <option value="4">Layer 4 (Input)</option>
                                <option value="2">Layer 2 (Processing)</option>
                                <option value="3">Layer 3 (Processing)</option>
                                <option value="5">Layer 5 (Output)</option>
                                <option value="6">Layer 6 (Feedback)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Activation Level</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                x-model="newNeuron.activation_level"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Threshold</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                x-model="newNeuron.threshold"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showCreateNeuronModal = false"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Relationship Modal -->
        <div x-show="showCreateRelationshipModal" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create Relationship</h3>
                    <form @submit.prevent="createRelationship()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Neuron</label>
                            <select
                                x-model="newRelationship.from_node_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select Source Neuron</option>
                                <template x-for="neuron in neurons" :key="neuron.n?.id || neuron.id">
                                    <option
                                        :value="neuron.n?.id || neuron.id"
                                        x-text="`${neuron.n?.properties?.name || neuron.properties?.name} (ID: ${neuron.n?.id || neuron.id})`"
                                    ></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Neuron</label>
                            <select
                                x-model="newRelationship.to_node_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select Target Neuron</option>
                                <template x-for="neuron in neurons" :key="neuron.n?.id || neuron.id">
                                    <option
                                        :value="neuron.n?.id || neuron.id"
                                        x-text="`${neuron.n?.properties?.name || neuron.properties?.name} (ID: ${neuron.n?.id || neuron.id})`"
                                    ></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Relationship Type</label>
                            <select
                                x-model="newRelationship.relationship_type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select Type</option>
                                <option value="CONNECTS_TO">Connects To</option>
                                <option value="ACTIVATES">Activates</option>
                                <option value="INHIBITS">Inhibits</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                x-model="newRelationship.weight"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Strength</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                x-model="newRelationship.strength"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showCreateRelationshipModal = false"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                            >
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function graphInterface() {
            return {
                neurons: [],
                message: '',
                messageType: 'success',
                showCreateNeuronModal: false,
                showCreateRelationshipModal: false,
                newNeuron: {
                    name: '',
                    layer: '',
                    activation_level: 0.0,
                    threshold: 0.5
                },
                newRelationship: {
                    from_node_id: '',
                    to_node_id: '',
                    relationship_type: '',
                    weight: 0.5,
                    strength: 0.5
                },

                init() {
                    this.loadNeurons();
                },

                async loadNeurons() {
                    try {
                        const response = await fetch('/graph/neurons', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            this.neurons = await response.json();
                            console.log('Loaded neurons:', this.neurons);
                        } else {
                            this.showMessage('Failed to load neurons', 'error');
                        }
                    } catch (error) {
                        console.error('Error loading neurons:', error);
                        this.showMessage('Error loading neurons', 'error');
                    }
                },

                async createNeuron() {
                    try {
                        const response = await fetch('/graph/neurons', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.newNeuron)
                        });

                        if (response.ok) {
                            this.showMessage('Neuron created successfully!', 'success');
                            this.showCreateNeuronModal = false;
                            this.resetNewNeuron();
                            this.loadNeurons();
                        } else {
                            const error = await response.json();
                            this.showMessage(error.message || 'Failed to create neuron', 'error');
                        }
                    } catch (error) {
                        console.error('Error creating neuron:', error);
                        this.showMessage('Error creating neuron', 'error');
                    }
                },

                async createRelationship() {
                    try {
                        const response = await fetch('/graph/relationships', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.newRelationship)
                        });

                        if (response.ok) {
                            this.showMessage('Relationship created successfully!', 'success');
                            this.showCreateRelationshipModal = false;
                            this.resetNewRelationship();
                        } else {
                            const error = await response.json();
                            this.showMessage(error.message || 'Failed to create relationship', 'error');
                        }
                    } catch (error) {
                        console.error('Error creating relationship:', error);
                        this.showMessage('Error creating relationship', 'error');
                    }
                },

                async deleteNeuron(id) {
                    if (!confirm('Are you sure you want to delete this neuron?')) return;

                    try {
                        const response = await fetch(`/graph/neurons/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            this.showMessage('Neuron deleted successfully!', 'success');
                            this.loadNeurons();
                        } else {
                            const error = await response.json();
                            this.showMessage(error.message || 'Failed to delete neuron', 'error');
                        }
                    } catch (error) {
                        console.error('Error deleting neuron:', error);
                        this.showMessage('Error deleting neuron', 'error');
                    }
                },

                editNeuron(neuron) {
                    // TODO: Implement edit functionality
                    this.showMessage('Edit functionality coming soon!', 'success');
                },

                resetNewNeuron() {
                    this.newNeuron = {
                        name: '',
                        layer: '',
                        activation_level: 0.0,
                        threshold: 0.5
                    };
                },

                resetNewRelationship() {
                    this.newRelationship = {
                        from_node_id: '',
                        to_node_id: '',
                        relationship_type: '',
                        weight: 0.5,
                        strength: 0.5
                    };
                },

                showMessage(text, type = 'success') {
                    this.message = text;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>