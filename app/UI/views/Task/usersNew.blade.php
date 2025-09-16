<x-form
    title="Add User to Task"
    hx-post="/task/{{$idTask}}/users/new"
>
    <x-slot:fields>
        <x-hidden-field id="idTask" value="{{$idTask}}"></x-hidden-field>
        <div class="field">
            <x-combobox.user
                id="idUser"
                label="User"
                value="0"
            >
            </x-combobox.user>
        </div>
        <div class="two fields">
            <div class="field">
                <div class="ui checkbox">
                    <input type="checkbox" name="isActive" value="1">
                    <label for="isActive">Is Active?</label>
                </div>
            </div>
            <div class="field">
                <div class="ui checkbox">
                    <input type="checkbox" name="isIgnore" value="1">
                    <label for="isIgnore">Is Ignore?</label>
                </div>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Add"></x-submit>
    </x-slot:buttons>
</x-form>
