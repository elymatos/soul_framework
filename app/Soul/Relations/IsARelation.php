<?php

namespace App\Soul\Relations;

class IsARelation extends RelationFrame
{
    public function __construct()
    {
        parent::__construct('IS_A', 'Is-A Relationship');
    }

    public function evaluate(): bool
    {
        // Implementation for IS_A evaluation
        return true;
    }
}
