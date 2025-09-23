<div class="sidebar-section">
    <h3>Create Neuron</h3>

    <form hx-post="/cortical-network/neuron" hx-target="#notification-area" hx-swap="innerHTML">
        <div class="form-group">
            <label class="form-label" for="neuron_column_id">Parent Column *</label>
            <select id="neuron_column_id" name="column_id" class="form-control" required
                    hx-get="/cortical-network/columns-list"
                    hx-trigger="load, reload-cortical-network from:body"
                    hx-target="this"
                    hx-swap="innerHTML">
                <option value="">Select a column...</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="neuron_name">Neuron Name *</label>
            <input type="text" id="neuron_name" name="name" class="form-control" placeholder="e.g., Input_0, Cardinal_Main" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="neuron_layer">Layer *</label>
            <select id="neuron_layer" name="layer" class="form-control" required>
                <option value="4">Layer 4 (Input)</option>
                <option value="23">Layers 2/3 (Processing)</option>
                <option value="5">Layer 5 (Output/Cardinal)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="neuron_type">Neuron Type *</label>
            <select id="neuron_type" name="neuron_type" class="form-control" required>
                <option value="input">Input</option>
                <option value="processing">Processing</option>
                <option value="output">Output</option>
                <option value="cardinal">Cardinal</option>
            </select>
        </div>

        <div class="layer-config-row" style="margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="activation_level">Activation</label>
                <input type="number" id="activation_level" name="activation_level" class="form-control" value="0.0" min="0" max="1" step="0.1">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="threshold">Threshold</label>
                <input type="number" id="threshold" name="threshold" class="form-control" value="0.5" min="0" max="1" step="0.1">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Create Neuron
        </button>
    </form>
</div>