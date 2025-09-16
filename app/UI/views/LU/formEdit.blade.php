<x-form>
    <x-slot:fields>
        <x-hidden-field id="idLU" :value="$lu->idLU"></x-hidden-field>
        <div class="field">
            <x-multiline-field
                label="Sense Description"
                id="senseDescription"
                :value="$lu->senseDescription"
            ></x-multiline-field>
        </div>
        <div class="two fields">
            <div class="field">
                <x-combobox.fe-frame
                    id="incorporatedFE"
                    label="Incorporated FE"
                    :value="$lu->incorporatedFE"
                    :idFrame="$lu->idFrame"
                    :hasNull="true"
                ></x-combobox.fe-frame>
            </div>
            <div class="field">
                <x-combobox.frame
                    id="idFrame"
                    label="Change Frame to (min 3 chars)"
                    :placeholder="$lu->frame->name"
                ></x-combobox.frame>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save" hx-put="/lu/{{$lu->idLU}}"></x-submit>
    </x-slot:buttons>
</x-form>
