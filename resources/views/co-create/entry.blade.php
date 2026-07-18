@extends('co-create.layout')

@section('title', '用户共创系统')
@section('body-class', 'cocreate-entry-page')

@section('content')
<main class="cocreate-entry-shell">
    <section class="entry-copy" aria-labelledby="entry-title">
        <p class="section-index">LUCKIN COMMUNITY / 01</p>
        <h1 id="entry-title">下一杯灵感，<br><em>从你开始。</em></h1>
        <p class="entry-lead">消费之后，别急着离场。领取一个属于你的共创任务，把真实生活里的咖啡时刻带到社交平台。</p>

        <ol class="entry-steps" aria-label="参与流程">
            <li><b>01</b><span>找到适合<br>你的话题</span></li>
            <li><b>02</b><span>发布真实<br>社媒内容</span></li>
            <li><b>03</b><span>提交链接<br>领取奖励</span></li>
        </ol>

        <a class="primary-action desktop-start" href="{{ route('co-create.profile') }}">
            模拟扫码进入
            <i data-lucide="arrow-up-right" aria-hidden="true"></i>
        </a>
    </section>

    <section class="scan-stage" aria-label="扫码参与共创">
        <div class="scan-stage-copy">
            <span>SCAN TO CREATE</span>
            <strong>扫一下<br>接住灵感</strong>
        </div>

        <a class="cup-scan-card" href="{{ route('co-create.profile') }}" aria-label="演示二维码，点击进入下一步">
            <span class="cup-lid"></span>
            <span class="cup-body">
                <img src="{{ asset('images/luckin-coffee-footer-logo.png') }}" alt="luckin coffee">
                <span class="qr-frame">
                    <span class="qr-grid" data-demo-qr aria-hidden="true"></span>
                </span>
                <small>扫码领取你的共创任务</small>
            </span>
        </a>

        <div class="scan-note">
            <i data-lucide="scan-line" aria-hidden="true"></i>
            <span>演示二维码<br>点击也可继续</span>
        </div>
    </section>

    <a class="primary-action mobile-start" href="{{ route('co-create.profile') }}">
        开始我的共创任务
        <i data-lucide="arrow-right" aria-hidden="true"></i>
    </a>
</main>
@endsection
