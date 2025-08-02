<?php

namespace App\Data\Annotation\FullText;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\JsonNormalizer;

class SelectionData extends Data
{
    public function __construct(
        public ?string $type = '',
        public ?string $id = '',
        public ?int $start = -1,
        public ?int $end = -1,
    )
    {
    }

}
