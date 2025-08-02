<?php

namespace App\Data\ComboBox;

use Spatie\LaravelData\Data;

class QData extends Data
{
    public function __construct(
        public ?string $q = '',
        public ?int $idLanguage = 0
    )
    {
    }
}
