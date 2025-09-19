<?php

namespace App\Data\Layers;

use Spatie\LaravelData\Data;

class UpdateGenericLabelData extends Data
{
    public function __construct(
        public ?int $idGenericLabel = null,
        public ?string $name = null,
        public ?int $idLanguage = null,
        public ?int $idLayerType = null,
        public ?int $idColor = null,
        public ?string $definition = '',
    ) {}
}
