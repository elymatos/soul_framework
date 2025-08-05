<?php

namespace App\Soul;

/**
 * Relation Frame - represents relationships between frame elements
 * Follows the Figure/Ground pattern you specified
 */
abstract class RelationFrame extends Frame
{
    public function __construct(string $id, string $label)
    {
        parent::__construct($id, $label, 'relation');

        // All relation frames have Figure and Ground FEs
        $this->addFrameElement(new FrameElement('figure', 'entity', 'The primary element in the relation'));
        $this->addFrameElement(new FrameElement('ground', 'entity', 'The secondary element in the relation'));
    }

    /**
     * Evaluate the relation between figure and ground
     */
    abstract public function evaluate(): bool;
}
