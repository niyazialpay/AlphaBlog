<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class FirewallRequestReviewAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a web application firewall analyst.

You receive summarized HTTP request metadata and must classify risk conservatively:
- "malicious" only when concrete attack indicators are present.
- "benign" only when the request looks normal.
- "uncertain" when evidence is insufficient.

Do not invent missing data. Base the verdict only on provided fields.
Keep reasons short and technical.
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'decision' => $schema->string()->enum(['malicious', 'benign', 'uncertain'])->required(),
            'confidence' => $schema->integer()->min(0)->max(100)->required(),
            'attack_type' => $schema->string()
                ->enum([
                    'sql_injection',
                    'xss',
                    'path_traversal',
                    'command_injection',
                    'credential_stuffing',
                    'bot_abuse',
                    'probe',
                    'unknown',
                ])->required(),
            'reason' => $schema->string()->max(280)->required(),
        ];
    }
}
