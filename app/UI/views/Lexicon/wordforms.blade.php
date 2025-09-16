        <div
            id="gridWordforms"
            class="grid"
            hx-trigger="reload-gridWordforms from:body"
            hx-target="this"
            hx-swap="outerHTML"
            hx-get="/lexicon/lexeme/{{$lexeme->idLexeme}}/wordforms"
        >
            @foreach($wordforms as $wordform)
                <div class="col-3">
                    <div class="ui card w-full">
                        <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Wordform"
                            onclick="messenger.confirmDelete(`Removing Wordform '{{$wordform->form}}'.`, '/lexicon/wordform/{{$wordform->idWordForm}}')"
                        ></x-delete>
                    </span>
                            <div
                                class="header"
                            >
                                <div
                                    class=""
                                >
                                    {{$wordform->form}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
