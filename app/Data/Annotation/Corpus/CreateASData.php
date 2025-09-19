<?php

namespace App\Data\Annotation\Corpus;

use Spatie\LaravelData\Data;

class CreateASData extends Data
{
    public function __construct(
        public ?int $idDocumentSentence,
        public ?int $idLU,
        public ?string $corpusAnnotationType,
        public mixed $wordList,
    ) {
        $this->wordList = json_decode($this->wordList);
    }

}
