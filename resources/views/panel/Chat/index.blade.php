@extends('panel.base')
@section('title', 'AI ChatBot - '.__('chatbot.title'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">AI ChatBot</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            @if (! $hasAvailableProvider)
                <div class="alert alert-warning mb-0" role="alert">
                    @lang('chatbot.no_provider_configured')
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>@lang('chatbot.previous_conversations')</strong>
                    <button type="button" id="btn-new-conversation" class="btn btn-sm btn-primary" @disabled(! $hasAvailableProvider)>
                        @lang('chatbot.new_conversation')
                    </button>
                </div>
                <div id="conversation-list" class="list-group list-group-flush"></div>
            </div>
        </div>

        <div class="col-lg-9 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="provider-select" class="form-label mb-1">@lang('chatbot.provider')</label>
                            <select id="provider-select" class="form-control" @disabled(! $hasAvailableProvider)></select>
                        </div>
                        <div class="col-md-5">
                            <label for="model-select" class="form-label mb-1">@lang('chatbot.model')</label>
                            <select id="model-select" class="form-control" @disabled(! $hasAvailableProvider)></select>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <small id="active-conversation-title" class="text-muted d-block pt-2">@lang('chatbot.new_conversation')</small>
                        </div>
                    </div>
                </div>
                <div id="chat-list" class="card-body" style="height: calc(100dvh - 425px); overflow-y: auto;"></div>
                <div class="card-footer">
                    <div class="input-group input-group-flat">
                        <textarea
                            rows="1"
                            id="input-message"
                            class="form-control"
                            autocomplete="off"
                            aria-label="@lang('chatbot.type_your_message')"
                            placeholder="@lang('chatbot.type_your_message')"
                            @disabled(! $hasAvailableProvider)
                        ></textarea>
                        <button id="btn-send" class="btn btn-primary" @disabled(! $hasAvailableProvider)>@lang('chatbot.send')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://unpkg.com/marked@12.0.1/marked.min.js"></script>
    <script>
        const chatState = {
            providers: @json($chatProviders),
            conversations: @json($conversations),
            initialConversation: @json($initialConversation),
            initialMessages: @json($initialMessages),
            defaultProvider: @json($defaultProvider),
            defaultModel: @json($defaultModel),
            user: {
                name: @json(auth()->user()->nickname),
                avatar: @json(replaceCDN(auth()->user()->profile_image).'&s=128'),
            },
            routes: {
                message: @json(route('chatbot.message')),
                conversationTemplate: @json(route('chatbot.conversation', ['conversationId' => '__CONVERSATION__'])),
            },
            labels: {
                assistant: @json(__('chatbot.assistant')),
                user: @json(auth()->user()->nickname),
                loading: @json(__('chatbot.typing')),
                calculating: @json(__('chatbot.calculating')),
                emptyConversation: @json(__('chatbot.empty_conversation')),
                selectConversation: @json(__('chatbot.select_conversation')),
                newConversation: @json(__('chatbot.new_conversation')),
                messageFailed: @json(__('chatbot.errors.request_failed')),
                noProvider: @json(__('chatbot.errors.no_provider_configured')),
            },
            kinds: {
                default: @json(__('chatbot.model_kind_default')),
                cheapest: @json(__('chatbot.model_kind_cheapest')),
                smartest: @json(__('chatbot.model_kind_smartest')),
            },
        };

        let activeConversationId = chatState.initialConversation?.id ?? null;
        let isSending = false;
        let pendingIndicatorElement = null;
        let pendingIndicatorInterval = null;
        const canChat = Object.keys(chatState.providers).length > 0;

        const conversationListElement = document.getElementById('conversation-list');
        const chatListElement = document.getElementById('chat-list');
        const providerSelectElement = document.getElementById('provider-select');
        const modelSelectElement = document.getElementById('model-select');
        const inputMessageElement = document.getElementById('input-message');
        const sendButtonElement = document.getElementById('btn-send');
        const newConversationButton = document.getElementById('btn-new-conversation');
        const activeConversationTitleElement = document.getElementById('active-conversation-title');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        function escapeHtml(content) {
            return String(content ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function markdownToHtml(content) {
            if (!content) {
                return '';
            }

            return marked.parse(escapeHtml(content));
        }

        function buildConversationRoute(conversationId) {
            return chatState.routes.conversationTemplate.replace('__CONVERSATION__', conversationId);
        }

        function byLatest(first, second) {
            return new Date(second.updated_at).getTime() - new Date(first.updated_at).getTime();
        }

        function renderConversationList() {
            conversationListElement.innerHTML = '';

            if (chatState.conversations.length === 0) {
                conversationListElement.innerHTML = `
                    <div class="p-3 text-muted small">${chatState.labels.selectConversation}</div>
                `;

                return;
            }

            chatState.conversations.sort(byLatest);

            chatState.conversations.forEach((conversation) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = `list-group-item list-group-item-action ${activeConversationId === conversation.id ? 'active' : ''}`;
                item.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <strong class="mb-1">${conversation.title}</strong>
                    </div>
                    <small>${conversation.updated_at_human}</small>
                `;

                item.addEventListener('click', () => {
                    loadConversation(conversation.id);
                });

                conversationListElement.appendChild(item);
            });
        }

        function updateActiveConversationTitle(title) {
            activeConversationTitleElement.textContent = title || chatState.labels.selectConversation;
        }

        function renderEmptyConversation() {
            chatListElement.innerHTML = `
                <div class="chat-empty-state d-flex justify-content-center align-items-center h-100 text-muted">
                    <div class="text-center">
                        <i class="fa-solid fa-comments fa-2x mb-2"></i>
                        <div>${chatState.labels.emptyConversation}</div>
                    </div>
                </div>
            `;
        }

        function removeEmptyStateIfNeeded() {
            const emptyState = chatListElement.querySelector('.chat-empty-state');

            if (emptyState) {
                chatListElement.innerHTML = '';
            }
        }

        function messageMetaLabel(message) {
            if (!message.provider || !message.model) {
                return chatState.labels.assistant;
            }

            return `${message.provider} • ${message.model}`;
        }

        function renderMessages(messages) {
            if (!messages || messages.length === 0) {
                renderEmptyConversation();

                return;
            }

            chatListElement.innerHTML = '';

            messages.forEach((message) => {
                const wrapper = document.createElement('div');
                const isUser = message.role === 'user';
                const bubbleClass = isUser ? 'bg-primary text-white' : 'bg-light';
                const label = isUser ? chatState.labels.user : messageMetaLabel(message);
                const contentHtml = isUser
                    ? `<div>${escapeHtml(message.content).replace(/\\n/g, '<br>')}</div>`
                    : `<div>${markdownToHtml(message.content)}</div>`;

                wrapper.className = `d-flex mb-3 ${isUser ? 'justify-content-end' : 'justify-content-start'}`;
                wrapper.innerHTML = `
                    <div class="p-3 rounded ${bubbleClass}" style="max-width: 88%;">
                        <div class="small mb-1 ${isUser ? 'text-white-50' : 'text-muted'}">${label}</div>
                        <div class="chat-message-content">${contentHtml}</div>
                    </div>
                `;

                chatListElement.appendChild(wrapper);
            });

            chatListElement.scrollTop = chatListElement.scrollHeight;
        }

        function appendMessageBubble(role, content, label, isMarkdown = false) {
            removeEmptyStateIfNeeded();

            const wrapper = document.createElement('div');
            const isUser = role === 'user';
            const bubbleClass = isUser ? 'bg-primary text-white' : 'bg-light';
            const contentHtml = isMarkdown
                ? markdownToHtml(content)
                : escapeHtml(content).replace(/\n/g, '<br>');

            wrapper.className = `d-flex mb-3 ${isUser ? 'justify-content-end' : 'justify-content-start'}`;
            wrapper.innerHTML = `
                <div class="p-3 rounded ${bubbleClass}" style="max-width: 88%;">
                    <div class="small mb-1 ${isUser ? 'text-white-50' : 'text-muted'}">${escapeHtml(label)}</div>
                    <div class="chat-message-content">${contentHtml}</div>
                </div>
            `;

            chatListElement.appendChild(wrapper);
            chatListElement.scrollTop = chatListElement.scrollHeight;
        }

        function startPendingIndicator() {
            removeEmptyStateIfNeeded();

            stopPendingIndicator();

            const statuses = [chatState.labels.loading, chatState.labels.calculating];
            let statusIndex = 0;
            let dots = 0;

            pendingIndicatorElement = document.createElement('div');
            pendingIndicatorElement.className = 'd-flex mb-3 justify-content-start';
            pendingIndicatorElement.innerHTML = `
                <div class="p-3 rounded bg-light" style="max-width: 88%;">
                    <div class="small mb-1 text-muted">${escapeHtml(chatState.labels.assistant)}</div>
                    <div class="chat-message-content">
                        <span class="pending-status">${escapeHtml(statuses[0])}</span><span class="pending-dots"></span>
                    </div>
                </div>
            `;

            chatListElement.appendChild(pendingIndicatorElement);
            chatListElement.scrollTop = chatListElement.scrollHeight;

            const statusElement = pendingIndicatorElement.querySelector('.pending-status');
            const dotsElement = pendingIndicatorElement.querySelector('.pending-dots');

            pendingIndicatorInterval = window.setInterval(() => {
                dots = (dots + 1) % 4;

                if (dots === 0) {
                    statusIndex = (statusIndex + 1) % statuses.length;
                }

                statusElement.textContent = statuses[statusIndex];
                dotsElement.textContent = '.'.repeat(dots);
            }, 350);
        }

        function stopPendingIndicator() {
            if (pendingIndicatorInterval) {
                clearInterval(pendingIndicatorInterval);
                pendingIndicatorInterval = null;
            }

            if (pendingIndicatorElement && pendingIndicatorElement.parentElement) {
                pendingIndicatorElement.parentElement.removeChild(pendingIndicatorElement);
            }

            pendingIndicatorElement = null;
        }

        function upsertConversation(conversation) {
            const existingIndex = chatState.conversations.findIndex((item) => item.id === conversation.id);

            if (existingIndex >= 0) {
                chatState.conversations[existingIndex] = conversation;
            } else {
                chatState.conversations.unshift(conversation);
            }

            renderConversationList();
        }

        function setProviders() {
            providerSelectElement.innerHTML = '';

            Object.entries(chatState.providers).forEach(([providerKey, provider]) => {
                const option = document.createElement('option');
                option.value = providerKey;
                option.textContent = provider.label;
                providerSelectElement.appendChild(option);
            });

            if (chatState.defaultProvider && chatState.providers[chatState.defaultProvider]) {
                providerSelectElement.value = chatState.defaultProvider;
            }

            refreshModelOptions();
        }

        function modelKindsLabel(kinds) {
            const translated = kinds
                .map((kind) => chatState.kinds[kind] ?? kind)
                .join(', ');

            return translated ? ` (${translated})` : '';
        }

        function refreshModelOptions() {
            modelSelectElement.innerHTML = '';

            const provider = chatState.providers[providerSelectElement.value];

            if (!provider) {
                return;
            }

            provider.models.forEach((model) => {
                const option = document.createElement('option');
                option.value = model.name;
                option.textContent = `${model.name}${modelKindsLabel(model.kinds)}`;
                modelSelectElement.appendChild(option);
            });

            if (provider.default_model) {
                modelSelectElement.value = provider.default_model;
            }

            if (chatState.defaultModel && provider.models.some((item) => item.name === chatState.defaultModel)) {
                modelSelectElement.value = chatState.defaultModel;
            }

            chatState.defaultModel = null;
        }

        async function loadConversation(conversationId) {
            const response = await fetch(buildConversationRoute(conversationId), {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                toastr.error(chatState.labels.messageFailed);

                return;
            }

            const payload = await response.json();

            activeConversationId = payload.conversation.id;
            updateActiveConversationTitle(payload.conversation.title);
            renderMessages(payload.messages);
            upsertConversation(payload.conversation);
        }

        function disableForm(isDisabled) {
            sendButtonElement.disabled = isDisabled || !canChat;
            providerSelectElement.disabled = isDisabled || !canChat;
            modelSelectElement.disabled = isDisabled || !canChat;
            newConversationButton.disabled = isDisabled || !canChat;
        }

        async function sendMessage() {
            if (isSending) {
                return;
            }

            const message = inputMessageElement.value.trim();

            if (!message) {
                return;
            }

            if (!providerSelectElement.value || !modelSelectElement.value) {
                toastr.error(chatState.labels.noProvider);

                return;
            }

            isSending = true;
            disableForm(true);

            inputMessageElement.value = '';
            appendMessageBubble('user', message, chatState.labels.user);
            startPendingIndicator();

            try {
                const response = await fetch(chatState.routes.message, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        message,
                        provider: providerSelectElement.value,
                        model: modelSelectElement.value,
                        conversation_id: activeConversationId,
                    }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || chatState.labels.messageFailed);
                }

                stopPendingIndicator();
                activeConversationId = payload.conversation.id;
                updateActiveConversationTitle(payload.conversation.title);
                renderMessages(payload.messages);
                upsertConversation(payload.conversation);
            } catch (error) {
                stopPendingIndicator();
                appendMessageBubble('assistant', chatState.labels.messageFailed, chatState.labels.assistant);
                toastr.error(error.message || chatState.labels.messageFailed);
            } finally {
                isSending = false;
                disableForm(false);
                inputMessageElement.focus();
            }
        }

        function startNewConversation() {
            activeConversationId = null;
            stopPendingIndicator();
            updateActiveConversationTitle(chatState.labels.newConversation);
            renderEmptyConversation();
            renderConversationList();
            inputMessageElement.focus();
        }

        providerSelectElement.addEventListener('change', refreshModelOptions);
        sendButtonElement.addEventListener('click', sendMessage);
        newConversationButton.addEventListener('click', startNewConversation);

        inputMessageElement.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            setProviders();
            disableForm(false);
            renderConversationList();

            if (chatState.initialConversation) {
                activeConversationId = chatState.initialConversation.id;
                updateActiveConversationTitle(chatState.initialConversation.title);
                renderMessages(chatState.initialMessages);
                return;
            }

            if (chatState.conversations.length > 0) {
                loadConversation(chatState.conversations[0].id);
                return;
            }

            renderEmptyConversation();
        });
    </script>
@endsection
