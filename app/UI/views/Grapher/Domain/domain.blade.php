<x-layout.grapher>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['/','Home'],['','Domain Grapher']]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <form>
            <div class="flex flex-row gap-2 pl-2 pt-2">
                <x-combobox.frame-domain
                    id="idSemanticType"
                    label=""
                    placeholder="Domain"
                    value=""
                ></x-combobox.frame-domain>
                <x-checkbox.relation
                    id="frameRelation"
                    label="Relations to show"
                    :relations="$relations"
                ></x-checkbox.relation>
                <div>
                    <x-button
                        id="btnSubmit"
                        label="Submit"
                        hx-target="#graph"
                        hx-post="/grapher/domain/graph"
                    ></x-button>
                </div>
                <div>
                    <x-button
                        id="btnClear"
                        label="Clear"
                        color="secondary"
                        hx-target="#graph"
                        hx-post="/grapher/domain/graph/0"
                    ></x-button>
                </div>
                <div>
                    <x-button
                        id="btnToogle"
                        type="button"
                        label="Grapher options"
                        color="secondary"
                        hx-on:click="$('#graph-drawer').flyout('toggle');"
                    ></x-button>
                </div>
            </div>
        </form>
    </x-slot:main>
</x-layout.grapher>
