<?php

namespace App\Data\LUCandidate;

use App\Repositories\Lemma;
use App\Repositories\Lexicon;
use Spatie\LaravelData\Data;

class UpdateData extends Data
{
    public function __construct(
        public ?int $idLUCandidate = null,
        public ?string $name = '',
        public ?string $senseDescription = '',
        public ?string $discussion = '',
        public ?int $idLemma = null,
        public ?int $idLexicon = null,
        public ?int $idFrame = null,
        public ?int $idDocumentSentence = null,
        public ?int $idDocument = null,
        public ?int $idBoundingBox = null,
        public ?int $incorporatedFE = null,
        public ?string $frameCandidate = '',
    )
    {
        if ($this->idFrame == 0) {
            $this->idFrame = null;
        }
        if (is_null($this->senseDescription)) {
            $this->senseDescription = '';
        }
        if (is_null($this->discussion)) {
            $this->discussion = '';
        }
        $lemma = Lexicon::lemmabyId($this->idLexicon);
        $this->name = $lemma->shortName;
    }


}
