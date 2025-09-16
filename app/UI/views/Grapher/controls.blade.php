<div class="ui right flyout" id="graph-drawer">
    <i class="close icon"></i>
    <div class="ui header">
        <div class="content">
            Grapher Options
        </div>
    </div>
    <div class="content">
        <div class="flex flex-column ui form">
            <div class="field">
                <x-combobox.options
                    id="ranker"
                    label="Ranker"
                    value="network-simplex"
                    :options="['network-simplex'=>'network-simplex','tight-tree' => 'tight-tree','longest-path'=>'longest-path']"
                ></x-combobox.options>
            </div>
            <div class="field">
                <x-combobox.options
                    id="rankdir"
                    label="RankDir"
                    value="BT"
                    :options="['TB'=>'Top-Bottom','BT' => 'Bottom-Top','RL'=>'Right-Left','LR' => 'Left-Right']"
                ></x-combobox.options>
            </div>
            <div class="field">
                <x-combobox.options
                    id="align"
                    label="Align"
                    value="DL"
                    :options="['DL'=>'Down-Left','DR' => 'Down-Right','UL'=>'Up-Left','UR' => 'Up-Right']"
                ></x-combobox.options>
            </div>
            <div class="field">
                <x-combobox.options
                    id="connector"
                    label="Connector"
                    value="normal"
                    :options="['normal'=>'Normal','smooth' => 'Smooth','jumpover'=>'Jumpover','curve' => 'Curve']"
                ></x-combobox.options>
            </div>
            <div class="field">
                <div class="ui checkbox">
                    <input type=checkbox checked id="vertices">
                    <label for="vertices">Vertices:</label>
                </div>
            </div>
            <div class="field">
                <label for="ranksep">RankSep:</label>
                <input id="ranksep" type="range" min="1" max="100" value="50" />
            </div>
            <div class="field">
                <label for="edgesep">EdgeSep:</label>
                <input id="edgesep" type="range" min="1" max="100" value="50" />
            </div>
            <div class="field">
                <label for="nodesep">NodeSep:</label>
                <input id="nodesep" type="range" min="1" max="100" value="50" />
            </div>

        </div>
    </div>
    <div class="actions">
    </div>
</div>
