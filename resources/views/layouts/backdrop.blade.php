{{-- Simple backdrop overlay for mobile sidebar (TailAdmin style) --}}
<div
    class="fixed inset-0 z-30 bg-black/40 backdrop-blur-sm xl:hidden"
    x-show="$store.sidebar.isMobileOpen"
    x-transition.opacity
    @click="$store.sidebar.setMobileOpen(false)">
</div>

