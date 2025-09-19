<?php

namespace App\Data\Lexicon;

use Spatie\LaravelData\Data;

class CreateFeatureData extends Data
{
    public function __construct(
        public ?int $idLexiconBase,
        public ?int $idUDFeature,
        public string $_token = '',
    ) {}

}
