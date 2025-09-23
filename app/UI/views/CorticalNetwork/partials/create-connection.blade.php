<div class="sidebar-section">
    <h3>Create Connection</h3>

    <form hx-post="/cortical-network/connection" hx-target="#notification-area" hx-swap="innerHTML">
        <div class="form-group">
            <label class="form-label" for="from_neuron_id">From Neuron ID *</label>
            <input type="number" id="from_neuron_id" name="from_neuron_id" class="form-control" placeholder="Click neuron in graph or enter ID" required>
            <small style="color: #718096; font-size: 0.75rem;">Tip: Click neurons in visualization to select</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="to_neuron_id">To Neuron ID *</label>
            <input type="number" id="to_neuron_id" name="to_neuron_id" class="form-control" placeholder="Click second neuron in graph" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="connection_type">Connection Type *</label>
            <select id="connection_type" name="connection_type" class="form-control" required>
                <option value="CONNECTS_TO">CONNECTS_TO (General)</option>
                <option value="ACTIVATES">ACTIVATES (Excitatory)</option>
                <option value="INHIBITS">INHIBITS (Inhibitory)</option>
            </select>
        </div>

        <div class="layer-config-row" style="margin-bottom: 1rem;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="weight">Weight</label>
                <input type="number" id="weight" name="weight" class="form-control" value="0.5" min="0" max="1" step="0.1">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="strength">Strength</label>
                <input type="number" id="strength" name="strength" class="form-control" value="1.0" min="0" max="1" step="0.1">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Create Connection
        </button>
    </form>
</div>