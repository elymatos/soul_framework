<?php

namespace App\Data\Comment;

use App\Services\AppService;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommentData extends Data
{
    public function __construct(
        public ?int   $idDynamicObject = null,
        public ?int   $idStaticObject = null,
        public ?int   $idAnnotationSet = null,
        public ?int   $order = null,
        public ?int   $idDocument = null,
        public ?string $comment = '',
        public ?int $idUser = null,
        public ?string $createdAt = '',
        public ?string $updatedAt = '',
        public string $_token = '',
    )
    {
        $this->idUser = AppService::getCurrentIdUser();
        if ($this->createdAt == '') {
            $this->createdAt = Carbon::now();
        }
        $this->updatedAt = Carbon::now();
        $this->_token = csrf_token();
    }

}
