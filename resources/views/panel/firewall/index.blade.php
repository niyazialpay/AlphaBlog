@extends('panel.base')
@section('title', __('firewall.firewall'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('firewall.firewall')
        </li>
    </ol>
@endsection

@section('content')
    @php
        $selectedProvider = old('ai_provider', $firewall->ai_provider);
        $selectedModel = old('ai_model', $firewall->ai_model);
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('firewall.rules')</h3>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="row" action="{{ route('admin.firewall.save') }}" method="post">
                @csrf

                <div class="col-12 mb-3">
                    <label for="is_active">@lang('ip_filter.status')</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" @selected((string) old('is_active', (int) $firewall->is_active) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('is_active', (int) $firewall->is_active) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="blacklist_rule_id">@lang('firewall.blacklist_rule_id')</label>
                    <select name="blacklist_rule_id" id="blacklist_rule_id" class="form-control">
                        @foreach($ipFilters as $ipFilter)
                            <option value="{{ $ipFilter->id }}" @selected((string) old('blacklist_rule_id', $firewall->blacklist_rule_id) === (string) $ipFilter->id)>
                                {{ $ipFilter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="whitelist_rule_id">@lang('firewall.whitelist_rule_id')</label>
                    <select name="whitelist_rule_id" id="whitelist_rule_id" class="form-control">
                        <option value="">@lang('firewall.select_rule')</option>
                        @foreach($ipFilters as $ipFilter)
                            <option value="{{ $ipFilter->id }}" @selected((string) old('whitelist_rule_id', $firewall->whitelist_rule_id) === (string) $ipFilter->id)>
                                {{ $ipFilter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_referer">@lang('firewall.check_referer')</label>
                    <select name="check_referer" id="check_referer" class="form-control">
                        <option value="1" @selected((string) old('check_referer', (int) $firewall->check_referer) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_referer', (int) $firewall->check_referer) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-4 mb-3">
                    <label for="check_bots">@lang('firewall.check_bots')</label>
                    <select name="check_bots" id="check_bots" class="form-control">
                        <option value="1" @selected((string) old('check_bots', (int) $firewall->check_bots) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_bots', (int) $firewall->check_bots) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-8 mb-3">
                    <label for="bad_bots">@lang('firewall.bad_bots')</label>
                    <input
                        type="text"
                        name="bad_bots"
                        id="bad_bots"
                        class="form-control"
                        value="{{ old('bad_bots', $firewall->bad_bots) }}"
                    >
                </div>

                <div class="col-12 mb-3">
                    <label for="check_request_method">@lang('firewall.check_request_method')</label>
                    <select name="check_request_method" id="check_request_method" class="form-control">
                        <option value="1" @selected((string) old('check_request_method', (int) $firewall->check_request_method) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_request_method', (int) $firewall->check_request_method) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_dos">@lang('firewall.check_dos')</label>
                    <select name="check_dos" id="check_dos" class="form-control">
                        <option value="1" @selected((string) old('check_dos', (int) $firewall->check_dos) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_dos', (int) $firewall->check_dos) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_union_sql">@lang('firewall.check_union_sql')</label>
                    <select name="check_union_sql" id="check_union_sql" class="form-control">
                        <option value="1" @selected((string) old('check_union_sql', (int) $firewall->check_union_sql) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_union_sql', (int) $firewall->check_union_sql) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_click_attack">@lang('firewall.check_click_attack')</label>
                    <select name="check_click_attack" id="check_click_attack" class="form-control">
                        <option value="1" @selected((string) old('check_click_attack', (int) $firewall->check_click_attack) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_click_attack', (int) $firewall->check_click_attack) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_xss">@lang('firewall.check_xss')</label>
                    <select name="check_xss" id="check_xss" class="form-control">
                        <option value="1" @selected((string) old('check_xss', (int) $firewall->check_xss) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_xss', (int) $firewall->check_xss) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="check_cookie_injection">@lang('firewall.check_cookie_injection')</label>
                    <select name="check_cookie_injection" id="check_cookie_injection" class="form-control">
                        <option value="1" @selected((string) old('check_cookie_injection', (int) $firewall->check_cookie_injection) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('check_cookie_injection', (int) $firewall->check_cookie_injection) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-12 mt-2 mb-3">
                    <hr>
                    <h5 class="mb-1">@lang('firewall.ai_section_title')</h5>
                    <p class="text-muted mb-0">@lang('firewall.ai_section_description')</p>
                </div>

                @if($chatProviders === [])
                    <div class="col-12 mb-3">
                        <div class="alert alert-warning mb-0">@lang('chatbot.no_provider_configured')</div>
                    </div>
                @endif

                <div class="col-md-6 mb-3">
                    <label for="ai_review_enabled">@lang('firewall.ai_review_enabled')</label>
                    <select name="ai_review_enabled" id="ai_review_enabled" class="form-control">
                        <option value="1" @selected((string) old('ai_review_enabled', (int) $firewall->ai_review_enabled) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('ai_review_enabled', (int) $firewall->ai_review_enabled) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ai_enforcement_enabled">@lang('firewall.ai_enforcement_enabled')</label>
                    <select name="ai_enforcement_enabled" id="ai_enforcement_enabled" class="form-control">
                        <option value="1" @selected((string) old('ai_enforcement_enabled', (int) $firewall->ai_enforcement_enabled) === '1')>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @selected((string) old('ai_enforcement_enabled', (int) $firewall->ai_enforcement_enabled) === '0')>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ai_provider">@lang('firewall.ai_provider')</label>
                    <select name="ai_provider" id="ai_provider" class="form-control">
                        <option value="">@lang('firewall.ai_provider_default')</option>
                        @foreach($chatProviders as $providerKey => $provider)
                            <option value="{{ $providerKey }}" @selected((string) $selectedProvider === (string) $providerKey)>
                                {{ $provider['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ai_model">@lang('firewall.ai_model')</label>
                    <select
                        name="ai_model"
                        id="ai_model"
                        class="form-control"
                        data-selected-model="{{ (string) $selectedModel }}"
                    >
                        <option value="">@lang('firewall.ai_model_default')</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="ai_confidence_threshold">@lang('firewall.ai_confidence_threshold')</label>
                    <input
                        type="number"
                        min="1"
                        max="100"
                        name="ai_confidence_threshold"
                        id="ai_confidence_threshold"
                        class="form-control"
                        value="{{ old('ai_confidence_threshold', $firewall->ai_confidence_threshold ?? 85) }}"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label for="ai_sample_rate">@lang('firewall.ai_sample_rate')</label>
                    <input
                        type="number"
                        min="0"
                        max="100"
                        name="ai_sample_rate"
                        id="ai_sample_rate"
                        class="form-control"
                        value="{{ old('ai_sample_rate', $firewall->ai_sample_rate ?? 0) }}"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label for="ai_timeout_seconds">@lang('firewall.ai_timeout_seconds')</label>
                    <input
                        type="number"
                        min="1"
                        max="30"
                        name="ai_timeout_seconds"
                        id="ai_timeout_seconds"
                        class="form-control"
                        value="{{ old('ai_timeout_seconds', $firewall->ai_timeout_seconds ?? 6) }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ai_cache_ttl_seconds">@lang('firewall.ai_cache_ttl_seconds')</label>
                    <input
                        type="number"
                        min="60"
                        max="86400"
                        name="ai_cache_ttl_seconds"
                        id="ai_cache_ttl_seconds"
                        class="form-control"
                        value="{{ old('ai_cache_ttl_seconds', $firewall->ai_cache_ttl_seconds ?? 900) }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ai_max_payload_chars">@lang('firewall.ai_max_payload_chars')</label>
                    <input
                        type="number"
                        min="500"
                        max="12000"
                        name="ai_max_payload_chars"
                        id="ai_max_payload_chars"
                        class="form-control"
                        value="{{ old('ai_max_payload_chars', $firewall->ai_max_payload_chars ?? 3000) }}"
                    >
                </div>

                <div class="col-12 mb-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const providers = @json($chatProviders);
            const providerSelect = document.getElementById('ai_provider');
            const modelSelect = document.getElementById('ai_model');
            const reviewToggleSelect = document.getElementById('ai_review_enabled');
            const providersAvailable = Object.keys(providers).length > 0;

            if (!providerSelect || !modelSelect || !reviewToggleSelect) {
                return;
            }

            const initiallySelectedModel = modelSelect.dataset.selectedModel || '';

            const updateAiControls = () => {
                const shouldDisableModelSelection = reviewToggleSelect.value !== '1' || !providersAvailable;
                providerSelect.disabled = shouldDisableModelSelection;
                modelSelect.disabled = shouldDisableModelSelection;
            };

            const renderModelOptions = () => {
                const provider = providerSelect.value;
                const providerConfig = providers[provider] || null;
                const availableModels = providerConfig && Array.isArray(providerConfig.models)
                    ? providerConfig.models
                    : [];

                const previousValue = modelSelect.value || initiallySelectedModel;
                modelSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = @json(__('firewall.ai_model_default'));
                modelSelect.appendChild(defaultOption);

                availableModels.forEach((modelDefinition) => {
                    const option = document.createElement('option');
                    option.value = modelDefinition.name;
                    option.textContent = modelDefinition.name;
                    modelSelect.appendChild(option);
                });

                if (availableModels.some((modelDefinition) => modelDefinition.name === previousValue)) {
                    modelSelect.value = previousValue;
                } else {
                    modelSelect.value = '';
                }

                updateAiControls();
            };

            providerSelect.addEventListener('change', renderModelOptions);
            reviewToggleSelect.addEventListener('change', updateAiControls);

            renderModelOptions();
        });
    </script>
@endsection
