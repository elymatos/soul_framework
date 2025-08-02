@props([
    'idFrame' => 'idFrame',
    'idFrameElement' => 'idFrameElement',
    'object' => null
])

<div class="two fields">
    <div class="field">
        <label for="{{$idFrame}}">Frame</label>
        <div
            hx-get="/components/fesByFrame"
            hx-trigger="search-component-change"
            hx-target="#fes"
            hx-include="input[name=idFrame]"
            hx-swap="innerHTML"
        >
            <x-form::search.frame
                name="{{$idFrame}}"
                value="{{ old($idFrame, $object?->idFrame ?? '') }}"
                display-value="{{ old('frame', $object->frame ?? '') }}"
                modal-title="Search Frame"
            />
        </div>
    </div>
    <div id="fes" class="field">
        <x-ui::fesByFrame
            name="{{$idFrameElement}}"
            label="FE"
            :value="$object?->idFrameElement ?? 0"
            :idFrame="$object?->idFrame ?? 0"
            :hasNull="false"
        ></x-ui::fesByFrame>
    </div>
</div>

