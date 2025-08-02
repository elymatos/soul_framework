<?php

namespace App\Data\Annotation\FullText;

use Spatie\LaravelData\Data;
class DeleteLabelData extends Data
{
    public function __construct(
        public int          $idAnnotationSet,
        public int          $idEntity,
        public ?string        $token = '',
        public ?string        $_token = '',
    )
    {
        $this->_token = csrf_token();
    }
}
