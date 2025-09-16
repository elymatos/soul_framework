@php
    use App\Database\Criteria;
    $limit = 300;
    $idLanguage = \App\Services\AppService::getCurrentIdLanguage();
    $data = [];
    if ($search->lexeme == '') {
        if ($search->lemma == '') {
            $search->lemma = '--none';
        }
        $lemmas = Criteria::table("lemma as lm")
            ->leftJoin("lexemeentry as le", "lm.idLemma","=","le.idLemma")
            ->leftJoin("lexeme as lx","le.idLexeme","=","lx.idLexeme")
            ->leftJoin("pos","lx.idPOS","=","pos.idPOS")
            ->where("lm.idLanguage", $idLanguage)
            ->whereRaw("lm.name LIKE '{$search->lemma}%' collate 'utf8mb4_bin'")
            ->select("lm.idLemma", "lm.name as lemma","lx.idLexeme","lx.name as lexeme","pos.pos as posLX")
            ->distinct()
            ->limit($limit)
            ->orderBy("lemma")->get()->groupBy(["idLemma","lemma"])->toArray();
        $data = [];
        foreach($lemmas as $idLemma => $lemma) {
            debug($lemma);
            $name = array_key_first($lemma);
            $children = [];
            if ($lemma[$name][0]->idLexeme) {
                $children = array_map(fn($item) => [
                    'id'=> $item->idLexeme,
                    'text' => $item->lexeme . " [{$item->posLX}]",
                    'state' => 'closed',
                    'type' => 'lexeme',
                    'children' => []
                ], $lemma[$name] ?? []);
            }
            $data[] = [
                'id' => $idLemma,
                'text' => $name,
                'state' => 'closed',
                'type' => 'lemma',
                'children' => $children
            ];
        }
    } else {
        $lexemes = Criteria::byFilter("view_lexicon", [
            ["lexeme", "startswith", $search->lexeme],
            ['idLanguageLX', "=", $idLanguage]
        ])->select('idLexeme', 'lexeme', 'lemma', "posLX")
            ->distinct()
            ->limit($limit)
            ->orderBy("lemma")->orderBy("lexeme")->all();
        foreach($lexemes as $lexeme) {
            $data[] = [
                'id' => $lexeme->idLexeme,
                'text' => $lexeme->lexeme . " [{$lexeme->posLX}]",
                'state' => 'closed',
                'type' => 'lexeme',
            ];
        }
    }
    if (empty($data)) {
         $data[] = [
            'id' => 0,
            'text' => "No results",
            'state' => 'closed',
            'type' => 'result',
        ];
    }
    $id = uniqid("lexiconTree");
@endphp
<div
    class="h-full"
    hx-trigger="reload-gridLexicon from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-post="/lexicon/grid"
>
    <div class="relative h-full overflow-auto">
        <div id="lexiconTreeWrapper" class="ui striped small compact table absolute top-0 left-0 bottom-0 right-0">
            @fragment('search')
                <ul id="{{$id}}">
                </ul>
                <script>
                    $(function() {
                        $("#{{$id}}").treegrid({
                            data: {{Js::from($data)}},
                            fit: true,
                            showHeader: false,
                            rowNumbers: false,
                            idField: "id",
                            treeField: "text",
                            showFooter: false,
                            border: false,
                            columns: [[
                                {
                                    field: "text",
                                    width: "100%",
                                }
                            ]],
                            onClickRow: (row) => {
                                if (row.type === "lemma") {
                                    htmx.ajax("GET", `/lexicon/lemma/${row.id}/content`, "#editArea");
                                }
                                if (row.type === "lexeme") {
                                    htmx.ajax("GET", `/lexicon/lexeme/${row.id}/content`, "#editArea");
                                }
                            }
                        });
                    });
                </script>
            @endfragment
        </div>
    </div>
</div>
