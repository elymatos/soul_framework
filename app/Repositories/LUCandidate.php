<?php

namespace App\Repositories;

use App\Database\Criteria;

class LUCandidate
{
    public static function byId(int $id): object
    {
        $lu = Criteria::byFilterLanguage('view_lucandidate', ['idLU', '=', $id])->first();
        if ($lu->idFrame) {
            $lu->frame = Frame::byId($lu->idFrame);
        } else {
            $lu->frame = null;
        }

        return $lu;
    }
}
