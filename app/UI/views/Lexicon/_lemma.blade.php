<x-layout.page>
    <div class="ui card h-full w-full">
        <div class="flex-grow-0 content h-4rem bg-white">
            @fragment('content')
                <x-layout.object>
                    <x-slot:name>
                        <span>{{$lemma->name}}</span>
                        <div class="ui label">
                            Lemma
                        </div>
                    </x-slot:name>
                    <x-slot:detail>
                        <div class="ui label wt-tag-id">
                            #{{$lemma->idLemma}}
                        </div>
                        <x-button
                            label="Delete"
                            color="danger"
                            onclick="messenger.confirmDelete(`Removing Lemma '{{$lemma->name}}'.`, '/lexicon/lemma/{{$lemma->idLemma}}')"
                        ></x-button>
                    </x-slot:detail>
                    <x-slot:description>

                    </x-slot:description>
                    <x-slot:main>
                        <div id="lexiconEditWrapper">
                            <x-form
                                title="Edit"
                                onsubmit="return false;"
                            >
                                <x-slot:fields>
                                    <x-hidden-field id="idLemma" :value="$lemma->idLemma"></x-hidden-field>
                                    <div class="two fields">
                                        <div class="field">
                                            <x-text-field
                                                label="Lemma"
                                                id="name"
                                                :value="$lemma->name"
                                            ></x-text-field>
                                        </div>
                                        <div class="field">
                                            <x-combobox.pos
                                                id="idPOS"
                                                label="POS"
                                                :value="$lemma->idPOS"
                                            ></x-combobox.pos>
                                        </div>
                                    </div>
                                </x-slot:fields>
                                <x-slot:buttons>
                                    <x-submit label="Update" hx-put="/lexicon/lemma/{{$lemma->idLemma}}"></x-submit>
                                </x-slot:buttons>
                            </x-form>
                            <x-form
                                title="Add Lexeme"
                            >
                                <x-slot:fields>
                                    <x-hidden-field id="idLemmaEntry" :value="$lemma->idLemma"></x-hidden-field>
                                    <div class="two fields">
                                        <div class="field">
                                            <x-text-field
                                                label="Lexeme"
                                                id="lexeme"
                                                value=""
                                            ></x-text-field>
                                        </div>
                                        <div class="field">
                                            <x-combobox.pos
                                                id="idPOSLexeme"
                                                label="POS"
                                                :value="$lemma->idPOS"
                                            ></x-combobox.pos>
                                        </div>
                                    </div>
                                    <div class="fields">
                                        <div class="field">
                                            <x-text-field
                                                label="Order"
                                                id="lexemeOrder"
                                                :value="count($lexemeentries) + 1"
                                            ></x-text-field>
                                        </div>
                                        <div class="field">
                                            <x-checkbox
                                                id="headWord"
                                                label="Head word"
                                                :active="true"
                                            ></x-checkbox>
                                        </div>
                                        <div class="field">
                                            <x-checkbox
                                                id="breakBefore"
                                                label="Break before"
                                                :active="false"
                                            ></x-checkbox>
                                        </div>
                                    </div>
                                </x-slot:fields>
                                <x-slot:buttons>
                                    <x-submit label="Add" hx-post="/lexicon/lexemeentry/new"></x-submit>
                                </x-slot:buttons>
                            </x-form>
                            <h3 class="ui header">Lexemes</h3>
                            @include("Lexicon.lexemeentries")
                        </div>
                    </x-slot:main>
                </x-layout.object>

            @endfragment
        </div>
    </div>
</x-layout.page>
