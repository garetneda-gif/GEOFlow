@extends('co-create.layout')

@section('title', '共创完成')
@section('step', '4')

@section('content')
<main class="flow-shell reward-shell">
    <section class="reward-hero">
        <span class="success-orbit"><i data-lucide="check" aria-hidden="true"></i></span>
        <p class="flow-kicker">STEP 04 · 核验通过</p>
        <h1>谢谢你，<br>让这一杯有了新故事。</h1>
        <p>已识别为 {{ $platform }} 公开链接，演示核验完成。</p>
    </section>

    <section class="reward-voucher" aria-label="演示奖励凭证">
        <div class="voucher-brand">
            <img src="{{ asset('images/luckin-coffee-footer-logo.png') }}" alt="luckin coffee">
            <span>CO-CREATION REWARD</span>
        </div>
        <div class="voucher-main">
            <small>共创奖励</small>
            <strong>{{ $reward['name'] }}</strong>
            <p>参考编号 {{ $reward['code'] }}</p>
        </div>
        <div class="voucher-footer">
            <span>已发放</span>
            <b>仅供概念演示，无实际兑换效力</b>
        </div>
    </section>

    <section class="submission-proof">
        <span><i data-lucide="badge-check" aria-hidden="true"></i></span>
        <div><small>已核验作品 · {{ $platform }}</small><strong>{{ $task['topic'] }}</strong><p>链接指纹已记录，原始地址不在系统内保留</p></div>
    </section>

    <form method="POST" action="{{ route('co-create.restart') }}">
        @csrf
        <div class="flow-action-dock">
            <button class="primary-action" type="submit">
                再体验一次
                <i data-lucide="rotate-ccw" aria-hidden="true"></i>
            </button>
        </div>
    </form>
</main>
@endsection
