<div class="sidebar-section">
    <h3>Create Cortical Column</h3>

    <form hx-post="/cortical-network/column" hx-target="#notification-area" hx-swap="innerHTML">
        <div class="form-group">
            <label class="form-label" for="column_name">Column Name *</label>
            <input type="text" id="column_name" name="name" class="form-control" placeholder="e.g., CONTAINER, MOVEMENT" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="column_type">Column Type</label>
            <select id="column_type" name="column_type" class="form-control">
                <option value="concept">Concept</option>
                <option value="frame">Frame</option>
                <option value="schema">Image Schema</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Layer Configuration</label>
            <div class="layer-config-row">
                <div>
                    <label class="form-label" style="font-size: 0.75rem; color: #3b82f6;">Layer 4</label>
                    <input type="number" name="layer_4_count" class="form-control" value="10" min="1" max="50">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.75rem; color: #10b981;">Layers 2/3</label>
                    <input type="number" name="layer_23_count" class="form-control" value="20" min="1" max="100">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.75rem; color: #ef4444;">Layer 5</label>
                    <input type="number" name="layer_5_count" class="form-control" value="5" min="1" max="20">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Create Column
        </button>
    </form>
</div>

<div id="notification-area"></div>