<?php

namespace App\Data\Task;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class UpdateData extends Data
{
    public function __construct(
        public ?int $idTask = null,
        public ?string $name = '',
        public ?string $description = '',
        public ?int $isActive = null,
        public ?int $size = null,
//        public ?int $idProject = null,
        public ?string $type = '',
        public ?string $createdAt = null
    )
    {
        $this->createdAt = Carbon::now();
    }


}
