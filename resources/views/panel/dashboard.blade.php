@extends('panel.base')
@section('title', __('dashboard.dashboard'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item active">@lang('dashboard.dashboard')</li>
    </ol>
@endsection
@section('content')
    @can('admin', 'App\Models\User')
    {{-- Toolbar --}}
    <div class="row mb-2">
        <div class="col-12 d-flex justify-content-end" style="gap:8px">
            @if($widgets->isNotEmpty())
            <button id="edit-toggle-btn" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit me-1"></i> Düzenle
            </button>
            @endif
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#widgetModal">
                <i class="fas fa-plus me-1"></i> Widget Ekle
            </button>
        </div>
    </div>

    @if($widgets->isEmpty())
    {{-- Empty State --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-th-large fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-3">Dashboard henüz boş. Widget ekleyerek özelleştirin.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#widgetModal">
                        <i class="fas fa-plus me-1"></i> Widget Ekle
                    </button>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Gridstack Container --}}
    <div class="grid-stack">
        @foreach($widgets as $widget)
        <div class="grid-stack-item"
             gs-x="{{ $widget->gs_x }}"
             gs-y="{{ $widget->gs_y }}"
             gs-w="{{ $widget->gs_w }}"
             gs-h="{{ $widget->gs_h }}"
             data-widget-type="{{ $widget->widget_type }}"
             data-widget-id="{{ $widget->id }}">
            <div class="grid-stack-item-content" style="overflow:hidden">
                {{-- Remove button (hidden by default, shown in edit mode) --}}
                <button class="widget-remove-btn btn btn-xs btn-danger"
                        style="position:absolute;top:4px;right:4px;z-index:10;display:none">
                    <i class="fas fa-times"></i>
                </button>
                @php $allowedWidgets = \App\Services\DashboardWidgetService::allWidgets(); @endphp
                @if(isset($allowedWidgets[$widget->widget_type]) && str_contains($widget->widget_type, '::'))
                    @include($widget->widget_type, ['widgetData' => $widgetData, 'widget' => $widget])
                @elseif(isset($allowedWidgets[$widget->widget_type]))
                    @include('panel.widgets.'.$widget->widget_type, ['widgetData' => $widgetData, 'widget' => $widget])
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Widget Library Modal --}}
    <div class="modal fade" id="widgetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-th-large me-2"></i>Widget Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach($widgetGroups as $group => $items)
                    <h6 class="text-uppercase text-muted small font-weight-bold mb-2 mt-3">{{ $group }}</h6>
                    <div class="row">
                        @foreach($items as $type => $config)
                        <div class="col-6 col-md-4 mb-2">
                            <button class="btn btn-outline-secondary btn-block text-left add-widget-btn"
                                    data-type="{{ $type }}"
                                    data-w="{{ $config['w'] }}"
                                    data-h="{{ $config['h'] }}"
                                    data-bs-dismiss="modal">
                                <small>{{ $config['label'] }}</small>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection
@section('script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack/dist/gridstack.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/gridstack/dist/gridstack-all.js"></script>

    <script>
        @can('admin', 'App\Models\User')
        $(document).ready(function () {
            @if($widgets->isNotEmpty())
            // Initialize gridstack in static (locked) mode
            var grid = GridStack.init({
                staticGrid: true,
                float: false,
                cellHeight: 80,
            });

            var editMode = false;
            var saveTimeout = null;

            // Edit mode toggle
            $('#edit-toggle-btn').on('click', function () {
                editMode = !editMode;
                if (editMode) {
                    grid.setStatic(false);
                    $('.widget-remove-btn').show();
                    $(this).removeClass('btn-secondary').addClass('btn-warning').html('<i class="fas fa-check me-1"></i> Bitti');
                } else {
                    grid.setStatic(true);
                    $('.widget-remove-btn').hide();
                    $(this).removeClass('btn-warning').addClass('btn-secondary').html('<i class="fas fa-edit me-1"></i> Düzenle');
                    saveLayout();
                }
            });

            // Remove widget
            $(document).on('click', '.widget-remove-btn', function () {
                var el = $(this).closest('.grid-stack-item')[0];
                grid.removeWidget(el);
                scheduleAutoSave();
            });

            // Gridstack change event
            grid.on('change', function (event, items) {
                scheduleAutoSave();
            });

            function scheduleAutoSave() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveLayout, 500);
            }

            function saveLayout() {
                var items = grid.getGridItems();
                var layout = [];
                items.forEach(function (el) {
                    var node = el.gridstackNode;
                    if (node) {
                        layout.push({
                            type: el.getAttribute('data-widget-type'),
                            x: node.x,
                            y: node.y,
                            w: node.w,
                            h: node.h
                        });
                    }
                });
                $.ajax({
                    url: '{{ route('admin.dashboard.widgets.save') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        layout: layout
                    },
                    error: function () {
                        console.warn('Dashboard layout save failed');
                    }
                });
            }
            @endif

            // Add widget from modal: POST new widget then reload
            $('.add-widget-btn').on('click', function () {
                var type = $(this).data('type');
                var w = parseInt($(this).data('w'));
                var h = parseInt($(this).data('h'));

                // Append to current layout and save
                var layout = [];
                var nextY = 0;
                @if($widgets->isNotEmpty())
                var items = grid.getGridItems();
                items.forEach(function (el) {
                    var node = el.gridstackNode;
                    if (node) {
                        layout.push({ type: el.getAttribute('data-widget-type'), x: node.x, y: node.y, w: node.w, h: node.h });
                        nextY = Math.max(nextY, node.y + node.h);
                    }
                });
                @endif
                layout.push({ type: type, x: 0, y: nextY, w: w, h: h });

                $.ajax({
                    url: '{{ route('admin.dashboard.widgets.save') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        layout: layout
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function () {
                        window.location.reload();
                    }
                });
            });
        });
        @endcan
    </script>
@endsection
