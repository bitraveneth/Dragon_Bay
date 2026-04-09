{{-- Lightweight modal wrapper for use as <x-ui.modal>. --}}

<div {{ $attributes->class('fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/40 p-5') }}>
    <div @click.stop
         class="relative w-full max-w-xl rounded-3xl bg-white shadow-xl">
        <div class="p-6 lg:p-8">
            {{ $slot }}
        </div>
    </div>
</div>
