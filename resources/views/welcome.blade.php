@extends('layouts.guest')

@section('title', $appBrandName ?? config('app.name'))

@push('styles')
<style>
    .landing-wrap {
        width: 100%;
        max-width: 1140px;
        margin: 0 auto;
    }

    .landing-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .landing-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 700;
        color: #1a2a24;
    }

    .brand-dot {
        width: 2rem;
        height: 2rem;
        border-radius: 0.6rem;
        background: linear-gradient(145deg, #0f766e, #14b8a6);
        box-shadow: 0 10px 24px rgba(15, 118, 110, 0.28);
    }

    .landing-nav {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .btn {
        border: 1px solid transparent;
        border-radius: 0.8rem;
        padding: 0.6rem 0.95rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background: #0f766e;
        color: #fff;
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.25);
    }

    .btn-ghost {
        border-color: #d9e0dc;
        background: #fff;
        color: #1a2a24;
    }

    .landing-hero {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1rem;
    }

    .hero-main,
    .hero-side,
    .feature-card,
    .status-strip {
        background: #fff;
        border: 1px solid #d9e0dc;
        border-radius: 1.1rem;
    }

    .hero-main {
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .hero-main::before {
        content: "";
        position: absolute;
        right: -3rem;
        top: -3rem;
        width: 12rem;
        height: 12rem;
        border-radius: 999px;
        background: radial-gradient(circle, #99f6e4 0%, transparent 70%);
        pointer-events: none;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f766e;
        background: #ccfbf1;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
    }

    .hero-main h1 {
        font-size: clamp(1.7rem, 2.5vw, 2.8rem);
        margin: 0.8rem 0 0.7rem;
        line-height: 1.12;
        color: #1a2a24;
    }

    .hero-copy {
        margin: 0;
        color: #5f6f67;
        max-width: 56ch;
    }

    .hero-actions {
        margin-top: 1.25rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .hero-meta {
        margin-top: 1.2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        color: #5f6f67;
        font-size: 0.92rem;
    }

    .hero-side {
        padding: 1.2rem;
    }

    .hero-side h2 {
        margin: 0 0 0.8rem;
        font-size: 1rem;
        color: #1a2a24;
    }

    .module-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.6rem;
    }

    .module-list li {
        border: 1px solid #d9e0dc;
        border-radius: 0.8rem;
        padding: 0.65rem 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fbfcfb;
        color: #1a2a24;
    }

    .module-tag {
        font-size: 0.72rem;
        padding: 0.16rem 0.48rem;
        border-radius: 999px;
        background: #fff5e8;
        color: #d97706;
        border: 1px solid #fde4c2;
    }

    .status-strip {
        margin-top: 1rem;
        padding: 0.85rem 1rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .status-pill {
        font-size: 0.82rem;
        color: #5f6f67;
    }

    .status-pill strong {
        color: #1a2a24;
        margin-right: 0.3rem;
    }

    .feature-grid {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .feature-card {
        padding: 1rem;
    }

    .feature-title {
        margin: 0;
        font-size: 0.98rem;
        color: #1a2a24;
    }

    .feature-copy {
        margin: 0.45rem 0 0;
        color: #5f6f67;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        display: inline-block;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse 2s infinite;
        vertical-align: middle;
        margin-right: 0.35rem;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    @media (max-width: 980px) {
        .landing-hero,
        .feature-grid {
            grid-template-columns: 1fr;
        }

        .hero-main,
        .hero-side {
            padding: 1.1rem;
        }

        .landing-top {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    html.dark .landing-brand {
        color: #d7eee8;
    }

    html.dark .btn-primary {
        background: #14b8a6;
        color: #042f2e;
        box-shadow: 0 12px 24px rgba(20, 184, 166, 0.25);
    }

    html.dark .btn-ghost {
        border-color: #2d3f4b;
        background: #0f172a;
        color: #dbe9f3;
    }

    html.dark .hero-main,
    html.dark .hero-side,
    html.dark .feature-card,
    html.dark .status-strip {
        background: #0f172a;
        border-color: #2b3b4a;
    }

    html.dark .hero-main::before {
        background: radial-gradient(circle, rgba(20, 184, 166, 0.28) 0%, transparent 70%);
    }

    html.dark .hero-main h1,
    html.dark .hero-side h2,
    html.dark .feature-title,
    html.dark .status-pill strong,
    html.dark .module-list li {
        color: #e5f0f8;
    }

    html.dark .hero-copy,
    html.dark .hero-meta,
    html.dark .feature-copy,
    html.dark .status-pill {
        color: #9db1c2;
    }

    html.dark .module-list li {
        border-color: #2b3b4a;
        background: #111b2e;
    }

    html.dark .module-tag {
        background: rgba(217, 119, 6, 0.14);
        color: #f7c56b;
        border-color: rgba(217, 119, 6, 0.35);
    }

    html.dark .hero-kicker {
        background: rgba(20, 184, 166, 0.2);
        color: #5eead4;
    }
</style>
@endpush

@section('content')
<div class="landing-wrap">
    <header class="landing-top">
        <div class="landing-brand">
            <span class="brand-dot" aria-hidden="true"></span>
            <span>{{ $appBrandName }} Control Hub</span>
        </div>

        <nav class="landing-nav">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Admin Sign In</a>
                <a href="{{ route('login') }}" class="btn btn-ghost">Staff Access</a>
            @endauth
        </nav>
    </header>

    <section class="landing-hero">
        <article class="hero-main">
            <span class="hero-kicker"><span class="dot"></span>Operational ERP Portal</span>
            <h1>Run production, stock, sales, and finance from one clean workflow.</h1>
            <p class="hero-copy">
                This ERP workspace keeps your full order lifecycle in one place, from batch creation and warehouse movement to delivery proof and accounting visibility.
            </p>

            <div class="hero-actions">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Open Control Panel</a>
                    <a href="{{ route('admin.help') }}" class="btn btn-ghost">View Help Guide</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Sign In to Continue</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost">Staff Access</a>
                @endauth
            </div>

            <div class="hero-meta">
                <span><strong>Security:</strong> Role & permission based access</span>
                <span><strong>Availability:</strong> Web + API workflows</span>
                <span><strong>Scope:</strong> Sales, Manufacturing, Inventory, Accounts</span>
            </div>
        </article>

        <aside class="hero-side">
            <h2>Core Modules</h2>
            <ul class="module-list">
                <li><span>Sales Orders & Invoicing</span><span class="module-tag">Live</span></li>
                <li><span>Manufacturing & QC</span><span class="module-tag">Tracked</span></li>
                <li><span>Inventory & Warehouses</span><span class="module-tag">Realtime</span></li>
                <li><span>Deliveries & POD</span><span class="module-tag">Mobile</span></li>
                <li><span>Accounts & Reconciliation</span><span class="module-tag">Audit</span></li>
            </ul>
        </aside>
    </section>

    <section class="status-strip" aria-label="Platform status">
        <span class="status-pill"><strong>Access:</strong> Authorized staff only</span>
        <span class="status-pill"><strong>Environment:</strong> {{ app()->environment() }}</span>
        <span class="status-pill"><strong>Year:</strong> {{ date('Y') }}</span>
    </section>

    <section class="feature-grid" aria-label="Feature highlights">
        <article class="feature-card">
            <h3 class="feature-title">Operational Traceability</h3>
            <p class="feature-copy">Track movement from raw material intake to delivered order with inventory-linked state transitions.</p>
        </article>
        <article class="feature-card">
            <h3 class="feature-title">Structured Role Control</h3>
            <p class="feature-copy">Permissions separate warehouse, sales, finance, and HR workflows while keeping collaboration centralized.</p>
        </article>
        <article class="feature-card">
            <h3 class="feature-title">Mobile-Ready Actions</h3>
            <p class="feature-copy">Field teams can update attendance, plans, delivery status, and POD data through authenticated API endpoints.</p>
        </article>
    </section>
</div>
@endsection
