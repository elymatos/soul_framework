<x-form
    title="New User"
    hx-post="/user/new"
>
    <x-slot:fields>
        <div class="three fields">
            <div class="field">
                <x-text-field
                    label="Login"
                    id="login"
                    value=""
                ></x-text-field>
            </div>
            <div class="field">
                <x-text-field
                    label="Email"
                    id="email"
                    value=""
                ></x-text-field>
            </div>
            <div class="field">
                <x-text-field
                    label="Name"
                    id="name"
                    value=""
                ></x-text-field>
            </div>
        </div>
        <div class="field">
            <x-combobox.group
                id="idGroup"
                label="Group"
                value="0"
            ></x-combobox.group>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
