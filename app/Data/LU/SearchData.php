<?php

namespace App\Data\LU;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?string $lu = '',
        public string $_token = '',
    ) {
        $this->_token = csrf_token();
    }
}
