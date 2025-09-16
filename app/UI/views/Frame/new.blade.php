<x-layout.edit>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['/','Home'],['','New Frame']]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <x-form
            title="New Frame"
            hx-post="/frame"
        >
            <x-slot:fields>
                <div class="field">
                     <x-text-field
                         id="nameEn"
                         label="English Name"
                         value="">

                     </x-text-field>
                </div>
            </x-slot:fields>
            <x-slot:buttons>
                <x-submit label="Add Frame"></x-submit>
            </x-slot:buttons>
        </x-form>
    </x-slot:main>
</x-layout.edit>
