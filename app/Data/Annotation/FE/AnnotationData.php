<?php

namespace App\Data\Annotation\FE;

use Spatie\LaravelData\Data;
class AnnotationData extends Data
{
    public function __construct(
        public int          $idAnnotationSet,
        public int          $idFrameElement,
        public ?SelectionData $range = null,
        public string        $token = '',
        public ?string        $_token = '',
    )
    {
        //debug(request("selection"));
        //$this->selection = SelectionData::from(request("selection"));
        $this->_token = csrf_token();
    }
}
