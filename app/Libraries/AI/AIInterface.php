<?php namespace App\Libraries\AI;

interface AIInterface {
    public function chat($systemPrompt, $userMessage);
}