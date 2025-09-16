<?php

namespace App\Data\Task;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?string $task = '',
        public ?string $user = '',
    )
    {
    }

}
