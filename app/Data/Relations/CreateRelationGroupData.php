<?php

namespace App\Data\Relations;

use Spatie\LaravelData\Data;

class CreateRelationGroupData extends Data
{
    public function __construct(
        public ?string $nameEn = '',
        public string $_token = '',
    ) {}
}
