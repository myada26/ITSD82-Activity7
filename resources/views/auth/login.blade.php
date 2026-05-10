@extends('layouts.auth')
@section('title', 'Sign In')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --ink: #0d1411;
        --ink-2: #1c2a23;
        --muted: #5b6f66;
        --muted-2: #8aa89a;
        --line: #e5ece8;
        --line-2: #d8e2dc;
        --bg: #f6f8f6;
        --green: #093816;
        --green-2: #0f4d20;
        --green-accent: #1a7a41;
        --gold: #c9a44a;
        --error: #b42318;
        --error-bg: #fef3f2;
    }

    body {
        background: var(--bg) !important;
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
        color: var(--ink) !important;
    }

    .auth-shell {
        display: flex;
        min-height: 100vh;
        width: 100%;
        background: #fff;
    }

    /* ============ LEFT — EDITORIAL PANEL ============ */
    .auth-aside {
        display: none;
        position: relative;
        flex: 1;
        min-height: 100vh;
        background: radial-gradient(ellipse at top left, #0f4d20 0%, #093816 55%, #061f0c 100%);
        color: #eaf3ec;
        padding: 56px 64px;
        overflow: hidden;
        flex-direction: column;
    }

    .auth-aside .rings {
        position: absolute;
        inset: -120px auto auto -180px;
        opacity: 0.18;
        pointer-events: none;
    }

    .auth-aside .rings svg {
        width: 780px;
        height: 780px;
    }

    .auth-aside .glow {
        position: absolute;
        right: -160px;
        bottom: -160px;
        width: 520px;
        height: 520px;
        border-radius: 999px;
        background: radial-gradient(closest-side, rgba(201, 164, 74, .18), transparent 70%);
        pointer-events: none;
    }

    .aside-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 2;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .brand-mark {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .14);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gold);
    }

    .brand-text strong {
        display: block;
        font-family: 'Fraunces', serif;
        font-weight: 600;
        font-size: 18px;
        letter-spacing: .02em;
        color: #fff;
    }

    .brand-text span {
        display: block;
        font-size: 11px;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(234, 243, 236, .55);
        margin-top: 2px;
    }

    .version-pill {
        font-size: 11px;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(234, 243, 236, .6);
        padding: 6px 10px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 999px;
    }

    .aside-hero {
        position: relative;
        z-index: 2;
        margin-top: auto;
        margin-bottom: 56px;
        max-width: 560px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 600;
        margin-bottom: 24px;
    }

    .eyebrow::before {
        content: "";
        width: 28px;
        height: 1px;
        background: var(--gold);
    }

    .aside-hero h1 {
        font-family: 'Fraunces', serif;
        font-weight: 500;
        font-size: 56px;
        line-height: 1.05;
        letter-spacing: -0.02em;
        margin: 0 0 20px;
        color: #fff;
    }

    .aside-hero h1 em {
        font-style: italic;
        font-weight: 400;
        color: var(--gold);
    }

    .aside-hero p {
        font-size: 15px;
        line-height: 1.65;
        color: rgba(234, 243, 236, .72);
        margin: 0;
        max-width: 480px;
    }

    .team-block {
        position: relative;
        z-index: 2;
        border-top: 1px solid rgba(255, 255, 255, .1);
        padding-top: 24px;
        margin-bottom: 32px;
    }

    .team-label {
        font-size: 11px;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(234, 243, 236, .55);
        font-weight: 600;
        margin-bottom: 16px;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px 20px;
    }

    .team-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .team-avatar {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(201, 164, 74, .25), rgba(255, 255, 255, .08));
        border: 1px solid rgba(255, 255, 255, .12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Fraunces', serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--gold);
        flex-shrink: 0;
    }

    .team-info {
        line-height: 1.25;
        min-width: 0;
    }

    .team-info strong {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .team-info span {
        display: block;
        font-size: 11px;
        color: rgba(234, 243, 236, .55);
        margin-top: 2px;
    }

    .aside-footer {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: rgba(234, 243, 236, .5);
        border-top: 1px solid rgba(255, 255, 255, .08);
        padding-top: 20px;
    }

    .aside-footer .secure {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .aside-footer .dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #4ade80;
        box-shadow: 0 0 0 3px rgba(74, 222, 128, .2);
    }

    /* ============ RIGHT — FORM PANEL ============ */
    .auth-main {
        flex: 1;
        min-height: 100vh;
        background: #fff;
        display: flex;
        flex-direction: column;
        padding: 32px 40px;
        position: relative;
    }

    .auth-top {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--muted);
    }

    .auth-top a {
        color: var(--green-accent);
        font-weight: 600;
        text-decoration: none;
    }

    .auth-top a:hover {
        text-decoration: underline;
    }

    .auth-card {
        margin: auto;
        width: 100%;
        max-width: 440px;
        padding: 24px 0;
    }

    .form-eyebrow {
        font-size: 11px;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--green-accent);
        font-weight: 700;
        margin-bottom: 14px;
    }

    .auth-card h2 {
        font-family: 'Fraunces', serif;
        font-weight: 500;
        font-size: 40px;
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: var(--ink);
        margin: 0 0 12px;
    }

    .auth-card .lede {
        font-size: 14px;
        line-height: 1.6;
        color: var(--muted);
        margin: 0 0 32px;
    }

    .alert-box {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        background: var(--error-bg);
        border: 1px solid #fecdca;
        border-radius: 10px;
        margin-bottom: 20px;
        color: var(--error);
        font-size: 13px;
        font-weight: 500;
    }

    .field {
        margin-bottom: 18px;
    }

    .field-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .field-head label {
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-2);
        letter-spacing: .02em;
    }

    .field-head a {
        font-size: 12px;
        color: var(--green-accent);
        font-weight: 600;
        text-decoration: none;
    }

    .field-head a:hover {
        text-decoration: underline;
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        border: 1px solid var(--line-2);
        border-radius: 10px;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .input-wrap:focus-within {
        border-color: var(--green-accent);
        box-shadow: 0 0 0 4px rgba(26, 122, 65, .08);
    }

    .input-wrap.has-error {
        border-color: #f97066;
        background: var(--error-bg);
    }

    .input-wrap .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 14px;
        color: var(--muted-2);
    }

    .input-wrap input {
        flex: 1;
        border: 0;
        outline: none;
        background: transparent;
        padding: 13px 14px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        color: var(--ink);
    }

    .input-wrap input::placeholder {
        color: #9aada4;
        font-weight: 400;
    }

    .toggle-pw {
        border: 0;
        background: transparent;
        padding: 0 14px;
        color: var(--muted-2);
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .toggle-pw:hover {
        color: var(--ink-2);
    }

    .field-error {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        font-size: 12px;
        color: var(--error);
        font-weight: 500;
    }

    .row-remember {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 8px 0 24px;
        cursor: pointer;
        user-select: none;
    }

    .row-remember input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .check-box {
        width: 18px;
        height: 18px;
        border-radius: 5px;
        border: 1.5px solid var(--line-2);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        transition: all .15s;
        flex-shrink: 0;
    }

    .row-remember input:checked+.check-box {
        background: var(--green-accent);
        border-color: var(--green-accent);
    }

    .row-remember input:checked+.check-box svg {
        opacity: 1;
    }

    .check-box svg {
        opacity: 0;
        transition: opacity .15s;
    }

    .row-remember span.lbl {
        font-size: 13px;
        color: var(--ink-2);
        font-weight: 500;
    }

    .submit-btn {
        width: 100%;
        padding: 14px 18px;
        border: 0;
        border-radius: 10px;
        background: var(--ink);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: background-color .15s, transform .15s;
        font-family: inherit;
    }

    .submit-btn:hover {
        background: var(--green-2);
    }

    .submit-btn:active {
        transform: translateY(1px);
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 28px 0 18px;
        font-size: 11px;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted-2);
        font-weight: 600;
    }

    .divider::before,
    .divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--line);
    }

    .quick-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .quick-btn {
        text-align: left;
        padding: 12px 14px;
        border: 1px solid var(--line-2);
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s, background-color .15s;
    }

    .quick-btn:hover {
        border-color: var(--green-accent);
        background: #f8fbf9;
    }

    .quick-btn strong {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .quick-btn span {
        display: block;
        font-size: 11px;
        color: var(--muted);
        letter-spacing: .04em;
    }

    .legal {
        margin-top: 28px;
        font-size: 12px;
        color: var(--muted);
        line-height: 1.55;
        text-align: center;
    }

    .legal a {
        color: var(--ink-2);
        text-decoration: underline;
        text-decoration-color: var(--line-2);
        text-underline-offset: 3px;
    }

    .legal a:hover {
        text-decoration-color: var(--green-accent);
    }

    /* ============ RESPONSIVE ============ */
    @media (min-width: 1024px) {
        .auth-aside {
            display: flex;
            max-width: 56%;
        }
    }

    @media (max-width: 1023px) {
        .auth-main {
            padding: 24px;
        }
    }

    @media (max-width: 640px) {
        .auth-card h2 {
            font-size: 32px;
        }

        .auth-main {
            padding: 20px;
        }

        .quick-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="auth-shell">

    {{-- ============ LEFT — EDITORIAL PANEL ============ --}}
    <aside class="auth-aside" aria-label="FCATS introduction">
        <div class="rings" aria-hidden="true">
            <svg viewBox="0 0 600 600" fill="none" stroke="#c9a44a" stroke-width="1">
                @for ($i = 1; $i
                <= 14; $i++)
                    <circle cx="300" cy="300" r="{{ 14 + $i * 26 }}" />
                @endfor
            </svg>
        </div>
        <div class="glow" aria-hidden="true"></div>

        <header class="aside-header">
            <div class="brand">
                <div class="brand-mark">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <div class="brand-text">
                    <strong>FCATS</strong>
                    <span>Fee &amp; Fine Collection Tracking System</span>
                </div>
            </div>
            <div class="version-pill">v2.4</div>
        </header>

        <div class="aside-hero">
            <div class="eyebrow">The Platform</div>
            <h1>Fee collection,<br><em>reconciled</em> in real time.</h1>
            <p>A secure platform built for student organizations and administrators — track every transaction, generate audit-ready reports, and manage financial records with full transparency.</p>
        </div>

        <div class="team-block">
            <div class="team-label">Project Team</div>
            <div class="team-grid">
                @foreach([
                ['CC', 'Carlos Fidel Castro', 'Proponent · Developer'],
                ['HS', 'Heaven Soberano', 'Proponent'],
                ['RA', 'Rey Angelo Arsenal', 'Proponent'],
                ['JB', 'Boon Jeferson Brigoli', 'Developer'],
                ['JB', 'Jao Beronio', 'Developer'],
                ['PL', 'Paul Juluis Labrador','Developer'],
                ] as [$initials, $name, $role])
                <div class="team-row">
                    <div class="team-avatar">{{ $initials }}</div>
                    <div class="team-info">
                        <strong>{{ $name }}</strong>
                        <span>{{ $role }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="aside-footer">
            <span>© CMU SUPREME STUDENT COUNCIL · All rights reserved</span>
            <span class="secure">
                <span class="dot"></span>
                Encrypted · TLS 1.3
            </span>
        </div>
    </aside>

    {{-- ============ RIGHT — FORM PANEL ============ --}}
    <main class="auth-main">
        <div class="auth-top">
            <span>Need access?</span>
            <a href="#">Contact admin</a>
        </div>

        <div class="auth-card">
            <div class="form-eyebrow">Sign In</div>
            <h2>Welcome back</h2>
            <p class="lede">Enter your credentials to continue. Sessions expire after 30 minutes of inactivity.</p>

            @if(session('error') || $errors->has('session') || $errors->has('locked'))
            <div class="alert-box">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('error') ?? $errors->first('session') ?? $errors->first('locked') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="field">
                    <div class="field-head">
                        <label for="username">Username or email</label>
                    </div>
                    <div class="input-wrap {{ $errors->has('username') ? 'has-error' : '' }}">
                        <span class="icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 11h-6M19 8v6" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="e.g. admin.ssc"
                            autocomplete="username"
                            autofocus>
                    </div>
                    @error('username')
                    <div class="field-error">
                        <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </div>

                <div class="field">
                    <div class="field-head">
                        <label for="password">Password</label>
                        <a href="#">Forgot password?</a>
                    </div>
                    <div class="input-wrap {{ $errors->has('password') ? 'has-error' : '' }}">
                        <span class="icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="11" width="16" height="10" rx="2" />
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password">
                        <button type="button" class="toggle-pw" aria-label="Show password"
                            onclick="const f=document.getElementById('password'); f.type=f.type==='password'?'text':'password'; this.setAttribute('aria-label', f.type==='password'?'Show password':'Hide password');">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <div class="field-error">
                        <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                    @enderror
                </div>

                <label class="row-remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span class="check-box" aria-hidden="true">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </span>
                    <span class="lbl">Keep me signed in on this device</span>
                </label>

                <button type="submit" class="submit-btn">
                    <span>Sign in</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </button>

                <p class="legal">
                    By signing in you agree to the
                    <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </main>
</div>
@endsection