<?php

namespace App\Data\Annotation\Dynamic;

use Spatie\LaravelData\Data;

class GetBBoxData extends Data
{
    public function __construct(
        public ?int   $idDynamicObject = null,
        public ?int   $frameNumber = null,
        public ?int   $isTracking = null,
        public string $_token = '',
    )
    {
    }

}
