<x-form
    title="New Lexeme"
    hx-post="/lexicon/lexeme/new"
    hx-target="#editArea"
    hx-swap="innerHTML"
>
    <x-slot:fields>
        <div class="two fields">
            <div class="field">
                <x-text-field
                    label="Lexeme"
                    id="name"
                    value=""
                ></x-text-field>
            </div>
            <div class="field">
                <x-combobox.pos
                    id="idPOS"
                    label="POS"
                    value=""
                ></x-combobox.pos>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
