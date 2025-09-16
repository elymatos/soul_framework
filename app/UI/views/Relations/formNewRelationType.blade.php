<x-form
    title="New RelationType"
    hx-post="/relations/relationtype/new"
>
    <x-slot:fields>
        <div class="fields">
            <div class="field">
                <x-combobox.relation-group
                    label="RelationGroup"
                    id="idRelationGroup"
                    :value="0"
                ></x-combobox.relation-group>
            </div>
        </div>
        <div class="field">
            <x-text-field
                id="nameCanonical"
                label="Canonical Name"
                value="">

            </x-text-field>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Add RelationType"></x-submit>
    </x-slot:buttons>
</x-form>
