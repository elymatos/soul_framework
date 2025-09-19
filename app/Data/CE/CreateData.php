<?php

namespace App\Data\CE;

use App\Services\AppService;
use Spatie\LaravelData\Data;

class CreateData extends Data
{
    public function __construct(
        public ?int $idConstruction,
        public ?string $nameEn,
        public ?int $idColor,
        public ?int $head,
        public ?int $optional,
        public ?int $multiple,
        public ?int $idUser,
        public string $_token = '',
    ) {
        $this->idUser = AppService::getCurrentIdUser();
        if (is_null($this->head)) {
            $this->head = 0;
        }
        if (is_null($this->optional)) {
            $this->optional = 0;
        }
        if (is_null($this->multiple)) {
            $this->multiple = 0;
        }
        $this->_token = csrf_token();
    }
}
