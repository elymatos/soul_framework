<?php

namespace App\Data\Annotation\Dynamic;

use Spatie\LaravelData\Data;

class ObjectSearchData extends Data
{
    public function __construct(
        public ?int $idDynamicObject = 0,
        public ?int $idDocument = 0,
        public ?int $searchIdLayerType = 0,
        public ?string $frame = '',
        public ?string $lu = '',
        public string $_token = '',
    ) {
        $this->_token = csrf_token();
    }
}
