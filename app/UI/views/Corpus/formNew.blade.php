<x-layout.page>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['/','Home'],['/corpus','Corpus'],['','New']]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <div class="ui container h-full">
            <x-form id="formNewCorpus" title="New Corpus" :center="false" hx-post="/corpus/new" class="p-2">
                <x-slot:fields>
                    <div class="field">
                        <x-text-field
                            label="Name"
                            id="name"
                            value=""
                        ></x-text-field>
                    </div>
                </x-slot:fields>
                <x-slot:buttons>
                    <x-submit label="Save"></x-submit>
                </x-slot:buttons>
            </x-form>
        </div>
    </x-slot:main>
</x-layout.page>
