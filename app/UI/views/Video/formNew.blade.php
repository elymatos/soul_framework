<x-form
    id="formNewVideo"
    title="New video"
    hx-encoding='multipart/form-data'
    hx-post="/video/new"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                label="Title"
                id="title"
                value=""
            ></x-text-field>
        </div>
        <div class="field">
            <x-combobox.language
                label="Language"
                id="idLanguage"
                value=""
            ></x-combobox.language>
        </div>
        <div class="field">
            <x-file-field
                label="File"
                id="file"
                value=""
            ></x-file-field>
        </div>
        <div class="field">
            <progress id='progress' value='0' max='100'></progress>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
<script>
    htmx.on("#formNewVideo", "htmx:xhr:progress", function(evt) {
        htmx.find("#progress").setAttribute("value", evt.detail.loaded / evt.detail.total * 100);
    });
</script>
