<?php

namespace App\Data\Annotation\Deixis;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?string $corpus = '',
        public ?string $document = '',
        public ?string $id = '',
        public ?string $type = '',
        public ?int $idCorpus = null,
        public ?int $idDocument = null,
        public ?string $annotation = 'dynamic',
        public string $_token = '',
    )
    {
        if ($type == 'corpus') {
            $this->idCorpus = $id;
        } else if ($type == 'document') {
            $this->idDocument = $id;
        }
        $this->_token = csrf_token();
    }
}
