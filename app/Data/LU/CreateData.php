<?php

namespace App\Data\LU;

use App\Repositories\Lemma;
use App\Repositories\Lexicon;
use App\Services\AppService;
use Spatie\LaravelData\Data;

class CreateData extends Data
{
    public function __construct(
        public int $idFrame,
        public ?int $idLemma,
        public int $idLexicon,
        public ?string $name,
        public ?string $senseDescription = '',
        public ?int $incorporatedFE = -1,
        public ?int $active = 1,
        public ?int $idUser = 1,
        public ?int $idEntity = null
    ) {
        // $lemma = Lemma::byId($this->idLemma);
        $lemma = Lexicon::lemmaById($this->idLexicon);
        $this->name = strtolower($lemma->name.'.'.$lemma->udPOS);
        $this->incorporatedFE = ($this->incorporatedFE < 0) ? null : $this->incorporatedFE;
        $this->idUser = AppService::getCurrentIdUser();
        $this->senseDescription = $this->senseDescription ?? '';
    }
}
