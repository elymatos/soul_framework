<x-layout.edit>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['/','Home'],['/frame','Frames'],['/frame/' . $lu->frame->idFrame,$lu->frame->name],['',$lu->frame->name.'.'.$lu?->name]]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <x-layout.object>
            <x-slot:name>
                <x-element.lu
                    name="{{$lu?->name}}"
                ></x-element.lu>
            </x-slot:name>
            <x-slot:detail>
                <div class="ui label wt-tag-id">
                    #{{$lu->idLU}}
                </div>
                @if($mode== 'edit')
                    <div class="ui label wt-tag-en">
                        {{$lu->frame->name}}
                    </div>
                @endif
                <div>
                    <x-combobox.lu-frame
                        id="idLUFrame"
                        :idFrame="$lu->frame->idFrame"
                        :value="'Change LU'"
                    ></x-combobox.lu-frame>
                    <script>
                        $(function() {
                            $('#idLUFrame_dropdown').dropdown({
                                onChange: (value) => {
                                    window.location.href= `/lu/${value}/edit`;
                                }
                            });
                        });
                    </script>
                </div>
            </x-slot:detail>
            <x-slot:description>
                #{{$lu->senseDescription}}
            </x-slot:description>
            <x-slot:main>
                @include("LU.menu")
            </x-slot:main>
        </x-layout.object>
    </x-slot:main>
</x-layout.edit>
