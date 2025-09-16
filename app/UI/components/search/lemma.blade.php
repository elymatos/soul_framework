@if(isset($label))
<label for="{{$id}}">{{$label}}</label>
@endif
<x-search::base
    name="{{$id}}"
    placeholder="{{$placeholder ?? 'Search Lemma'}}"
    search-url="/lexicon3/lemma/listForSearch"
{{--    display-formatter="displayFormaterLUSearch"--}}
    display-field="name"
    value="{{$value ?? 0}}"
    display-value="{{ $displayValue ?? '' }}"
    value-field="idLexicon"
    modal-title="{{$modalTitle ?? 'Search Lemma'}}"
/>
<script>
    function displayFormaterLUSearch(lu) {
        return `<div class="result"><span class="color_frame">${lu.frameName}</span>.${lu.name}</span></div>`;
    };
</script>
