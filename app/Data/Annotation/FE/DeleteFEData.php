<?php

namespace App\Data\Annotation\FE;

use Spatie\LaravelData\Data;
class DeleteFEData extends Data
{
    public function __construct(
        public int          $idAnnotationSet,
        public int          $idFrameElement,
        public ?string        $token = '',
        public ?string        $_token = '',
    )
    {
        $this->_token = csrf_token();
    }
}
