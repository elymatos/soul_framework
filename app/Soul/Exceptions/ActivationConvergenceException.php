<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when spreading activation fails to converge
 */
class ActivationConvergenceException extends CognitiveProcessingException
{
    protected int $maxRounds;
    protected int $currentRound;

    public function __construct(
        int $maxRounds,
        int $currentRound,
        string $sessionId = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->maxRounds = $maxRounds;
        $this->currentRound = $currentRound;

        $message = "Spreading activation failed to converge after {$maxRounds} rounds";

        $context = [
            'max_rounds' => $maxRounds,
            'current_round' => $currentRound,
            'session_id' => $sessionId
        ];

        parent::__construct($message, 'spreading_activation', $sessionId, $code, $previous);
        $this->setContext($context);
    }

    public function getMaxRounds(): int
    {
        return $this->maxRounds;
    }

    public function getCurrentRound(): int
    {
        return $this->currentRound;
    }
}