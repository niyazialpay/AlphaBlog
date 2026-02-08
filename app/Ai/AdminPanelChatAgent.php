<?php

namespace App\Ai;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class AdminPanelChatAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function instructions(): Stringable|string
    {
        return __('chatbot.agent_instructions');
    }

    public function tools(): iterable
    {
        return [];
    }

    protected function maxConversationMessages(): int
    {
        return 100;
    }
}
