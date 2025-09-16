<x-form
    hx-post="/project/{{$idProject}}/users/new"
>
    <x-slot:fields>
        <x-hidden-field id="idProject" value="{{$idProject}}"></x-hidden-field>
        <div class="field">
            <x-combobox.user
                id="idUser"
                label="User"
                value="0"
            >
            </x-combobox.user>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Add"></x-submit>
    </x-slot:buttons>
</x-form>
