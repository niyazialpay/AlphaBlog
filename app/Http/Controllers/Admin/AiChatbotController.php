<?php

namespace App\Http\Controllers\Admin;

use App\Ai\AdminPanelChatAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiChatbotMessageRequest;
use App\Support\AiChatModelCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AiChatbotController extends Controller
{
    public function __construct(protected AiChatModelCatalog $modelCatalog) {}

    public function index(Request $request): View
    {
        $providers = $this->modelCatalog->getAvailableTextProviders();
        $conversations = $this->getConversationList($request->user()->id);
        $selectedConversationId = $request->query('conversation');
        $initialConversation = null;
        $initialMessages = [];

        if (is_string($selectedConversationId) && $selectedConversationId !== '') {
            $initialConversation = $this->findConversationForUser(
                $request->user()->id,
                $selectedConversationId
            );

            if ($initialConversation) {
                $initialMessages = $this->getConversationMessages(
                    $request->user()->id,
                    $selectedConversationId
                );
            }
        }

        $defaultProvider = $this->modelCatalog->getDefaultProviderName($providers);
        $defaultModel = $this->modelCatalog->getDefaultModelName($providers, $defaultProvider);

        return view('panel.Chat.index', [
            'chatProviders' => $providers,
            'conversations' => $conversations,
            'initialConversation' => $initialConversation ? $this->transformConversation($initialConversation) : null,
            'initialMessages' => $initialMessages,
            'defaultProvider' => $defaultProvider,
            'defaultModel' => $defaultModel,
            'hasAvailableProvider' => $providers !== [],
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        return response()->json([
            'conversations' => $this->getConversationList($request->user()->id),
        ]);
    }

    public function conversation(Request $request, string $conversationId): JsonResponse
    {
        $conversation = $this->findConversationForUser($request->user()->id, $conversationId);

        if (! $conversation) {
            return response()->json([
                'message' => __('chatbot.errors.conversation_not_found'),
            ], 404);
        }

        return response()->json([
            'conversation' => $this->transformConversation($conversation),
            'messages' => $this->getConversationMessages($request->user()->id, $conversationId),
        ]);
    }

    public function message(AiChatbotMessageRequest $request): JsonResponse
    {
        $providerCatalog = $this->modelCatalog->getAvailableTextProviders();

        if ($providerCatalog === []) {
            return response()->json([
                'message' => __('chatbot.errors.no_provider_configured'),
            ], 422);
        }

        $provider = $request->string('provider')->toString();
        $model = $request->string('model')->toString();
        $message = $request->string('message')->toString();
        $conversationId = $request->input('conversation_id');
        $createdConversation = false;

        if (! isset($providerCatalog[$provider])) {
            return response()->json([
                'message' => __('chatbot.errors.invalid_provider'),
            ], 422);
        }

        $availableModels = collect($providerCatalog[$provider]['models'])
            ->pluck('name')
            ->all();

        if (! in_array($model, $availableModels, true)) {
            return response()->json([
                'message' => __('chatbot.errors.invalid_model'),
            ], 422);
        }

        $user = $request->user();

        if (is_string($conversationId) && $conversationId !== '') {
            $conversation = $this->findConversationForUser($user->id, $conversationId);

            if (! $conversation) {
                return response()->json([
                    'message' => __('chatbot.errors.conversation_not_found'),
                ], 404);
            }
        } else {
            $conversationId = $this->createConversation($user->id, $message);
            $createdConversation = true;
        }

        try {
            $agent = AdminPanelChatAgent::make()
                ->continue($conversationId, $user);

            $response = $agent->prompt(
                prompt: $message,
                provider: $provider,
                model: $model,
            );
        } catch (Throwable $e) {
            if ($createdConversation) {
                DB::table('agent_conversations')
                    ->where('id', $conversationId)
                    ->where('user_id', $user->id)
                    ->delete();
            }

            return response()->json([
                'message' => __('chatbot.errors.request_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }

        DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->where('user_id', $user->id)
            ->update([
                'updated_at' => now(),
            ]);

        $conversation = $this->findConversationForUser($user->id, $conversationId);

        if (! $conversation) {
            return response()->json([
                'message' => __('chatbot.errors.conversation_not_found'),
            ], 404);
        }

        return response()->json([
            'conversation' => $this->transformConversation($conversation),
            'messages' => $this->getConversationMessages($user->id, $conversationId),
            'meta' => $response->meta->toArray(),
        ]);
    }

    protected function createConversation(int $userId, string $prompt): string
    {
        $conversationId = (string) Str::uuid7();

        DB::table('agent_conversations')->insert([
            'id' => $conversationId,
            'user_id' => $userId,
            'title' => Str::limit($prompt, 100, preserveWords: true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $conversationId;
    }

    protected function getConversationList(int $userId): array
    {
        return DB::table('agent_conversations')
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(fn ($conversation) => $this->transformConversation($conversation))
            ->all();
    }

    protected function findConversationForUser(int $userId, string $conversationId): ?object
    {
        return DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->where('user_id', $userId)
            ->first();
    }

    protected function getConversationMessages(int $userId, string $conversationId): array
    {
        return DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                $meta = $this->decodeJsonColumn($message->meta);

                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'provider' => $meta['provider'] ?? null,
                    'model' => $meta['model'] ?? null,
                    'created_at' => Carbon::parse($message->created_at)->toIso8601String(),
                ];
            })
            ->all();
    }

    protected function transformConversation(object $conversation): array
    {
        $updatedAt = Carbon::parse($conversation->updated_at);

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'updated_at' => $updatedAt->toIso8601String(),
            'updated_at_human' => $updatedAt->diffForHumans(),
        ];
    }

    protected function decodeJsonColumn(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
