<x-form>
    <x-slot:fields>
        <x-hidden-field
            id="idFrame"
            :value="$idFrame"
        ></x-hidden-field>
        <div class="three fields">
            <div class="field">
                <x-combobox.lexicon-lemma
                    id="idLexicon"
                    label="Lemma [min: 3 chars]"
                    value="0"
                ></x-combobox.lexicon-lemma>
            </div>
            <div class="field">
                <x-text-field
                    label="Sense Description"
                    id="senseDescription"
                    value=""
                ></x-text-field>
            </div>
            <div class="field">
                <x-combobox.fe-frame
                    id="incorporatedFE"
                    label="Incorporated FE"
                    :value="-1"
                    :idFrame="$idFrame"
                    nullName="No incorporated FE"
                    :hasNull="true"
                ></x-combobox.fe-frame>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-button
            label="Add LU"
            hx-post="/lu"
        ></x-button>
    </x-slot:buttons>
</x-form>

