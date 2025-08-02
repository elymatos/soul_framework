<?php

namespace App\Data\Lexicon;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?string $lemma = '',
        public ?string $lexeme = '',
        public ?string $wordform = '',
        public ?string $id = '',
        public ?int $idLemma = 0,
        public ?int $idLexeme = 0,
        public ?int $idLexicon = 0,
        public string  $_token = '',
    )
    {
        if ($this->id != '') {
            if ($this->id[0] == 'l') {
                $this->idLemma = substr($this->id, 1);
                $this->idLexicon = substr($this->id, 1);
            }
            if ($this->id[0] == 'x') {
                $this->idLexeme = substr($this->id, 1);
            }
            if ($this->id[0] == 'f') {
                $this->idLexicon = substr($this->id, 1);
            }
        }
        $this->_token = csrf_token();
    }

}
