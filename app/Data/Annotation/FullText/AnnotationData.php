<?php

namespace App\Data\Annotation\FullText;

use Spatie\LaravelData\Data;
class AnnotationData extends Data
{
    public function __construct(
        public int          $idAnnotationSet,
        public int          $idEntity,
        public ?SelectionData $range = null,
        public ?string        $layerType = '',
        public string        $token = '',
        public ?string        $_token = '',
    )
    {
        $this->_token = csrf_token();
    }
}
