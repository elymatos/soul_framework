<?php

namespace App\Soul\Contracts;

interface LanguageServiceInterface extends AgentServiceInterface
{
    public function parseSentence(array $parameters): array;
    public function conceptualizeWord(array $parameters): array;
    public function generateSentenceFromFrame(array $parameters): string;
    public function extractSemanticRoles(array $parameters): array;
}