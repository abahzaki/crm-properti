<?php namespace App\Libraries\AI;

interface AIInterface {
 public function chat(string $systemPrompt, string $userMessage, float $temperature = 0.5): string;
}