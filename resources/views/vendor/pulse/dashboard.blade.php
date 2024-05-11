<link rel="stylesheet" href="{{config('app.url')}}/themes/fontawesome/css/all.min.css">
<x-pulse>
    <section class="@container flex flex-col shadow-sm default:col-span-full default:lg:col-span-3 text-center">
        <a class="text-gray-600 dark:text-gray-300 p-3 sm:p-6 rounded-xl  ring-1" href="{{route('admin.index')}}">
            <i class="fa-duotone fa-turn-left"></i>
            @lang('dashboard.back_to_dashboard')
        </a>
    </section>
    <livewire:pulse.servers cols="full"/>

    <livewire:pulse.usage cols="4" rows="2"/>

    <livewire:pulse.queues cols="4"/>

    <livewire:pulse.cache cols="4"/>

    <livewire:pulse.slow-queries cols="8"/>

    <livewire:pulse.exceptions cols="6"/>

    <livewire:pulse.slow-requests cols="6"/>

    <livewire:pulse.slow-jobs cols="6"/>

    <livewire:pulse.slow-outgoing-requests cols="6"/>
</x-pulse>
