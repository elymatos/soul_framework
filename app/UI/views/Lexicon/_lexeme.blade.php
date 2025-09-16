<x-layout.page>
    <div class="ui card h-full w-full">
        <div class="flex-grow-0 content h-4rem bg-white">
            @fragment('content')
                <x-layout.object>
                    <x-slot:name>
                        <span>{{$lexeme->name}}</span>
                        <div class="ui label">
                            Lexeme
                        </div>
                    </x-slot:name>
                    <x-slot:detail>
                        <div class="ui label wt-tag-id">
                            #{{$lexeme->idLexeme}}
                        </div>
                        <x-button
                            label="Delete"
                            color="danger"
                            onclick="messenger.confirmDelete(`Removing Lexeme '{{$lexeme->name}}'.`, '/lexicon/lexeme/{{$lexeme->idLexeme}}')"
                        ></x-button>
                    </x-slot:detail>
                    <x-slot:description>

                    </x-slot:description>
                    <x-slot:main>
                        <div id="lexiconEditWrapper">
                            <x-form
                                title="Edit"
                                center="true"
                                onsubmit="return false;"
                            >
                                <x-slot:fields>
                                    <x-hidden-field id="idLexeme" :value="$lexeme->idLexeme"></x-hidden-field>
                                    <div class="fields">
                                        <div class="field">
                                            <x-text-field
                                                label="Lexeme"
                                                id="name"
                                                :value="$lexeme->name"
                                            ></x-text-field>
                                        </div>
                                        <div class="field">
                                            <x-combobox.pos
                                                id="idPOS"
                                                label="POS"
                                                :value="$lexeme->idPOS"
                                            ></x-combobox.pos>
                                        </div>
                                    </div>
                                </x-slot:fields>
                                <x-slot:buttons>
                                    <x-submit label="Update" hx-put="/lexicon/lexeme/{{$lexeme->idLexeme}}"></x-submit>
                                </x-slot:buttons>
                            </x-form>
                            <x-form
                                title="Add Wordform"
                                center="true"
                            >
                                <x-slot:fields>
                                    <x-hidden-field id="idLexemeWordform" :value="$lexeme->idLexeme"></x-hidden-field>
                                    <div class="field">
                                    <x-text-field
                                        label="Wordform"
                                        id="form"
                                        value=""
                                    ></x-text-field>
                                    </div>
                                </x-slot:fields>
                                <x-slot:buttons>
                                    <x-submit label="Add" hx-post="/lexicon/wordform/new"></x-submit>
                                </x-slot:buttons>
                            </x-form>
                            <h3 class="ui header">Wordforms</h3>
                            @include("Lexicon.wordforms")
                        </div>
                    </x-slot:main>
                </x-layout.object>

            @endfragment
        </div>
    </div>
</x-layout.page>

