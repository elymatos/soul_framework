<?php

namespace App\Soul\Relations;

class SharedSlotRelation extends RelationFrame
{
    public function __construct()
    {
        parent::__construct('SHARED_SLOT', 'Shared Slot Relationship');
    }

    public function evaluate(): bool
    {
        // Minsky's shared slots are always valid if established
        return true;
    }
}
