@extends('co-create.layout')

@section('title', '你的共创任务')
@section('step', '2')

@section('content')
<main class="flow-shell task-shell">
    <div class="flow-heading compact">
        <p class="flow-kicker">STEP 02 · 匹配完成</p>
        <h1>这道题，<br>想交给你来回答。</h1>
    </div>

    <article class="mission-card mission-{{ $task['accent'] }}">
        <div class="mission-topline">
            <span>{{ $task['eyebrow'] }}</span>
            <b>NO. {{ $task['number'] }}</b>
        </div>
        <p class="mission-match"><i data-lucide="sparkles" aria-hidden="true"></i>因为你选择了「{{ $task['audience'] }}」</p>
        <h2>{{ $task['title'] }}</h2>
        <p class="mission-topic">{{ $task['topic'] }}</p>
        <p class="mission-brief">{{ $task['brief'] }}</p>
        <div class="mission-stamp">CO<br>CREATE</div>
    </article>

    <section class="task-requirements" aria-labelledby="requirement-title">
        <div class="section-title-row">
            <h2 id="requirement-title">交稿清单</h2>
            <span>约 5 分钟</span>
        </div>
        <ul>
            <li>
                <span><i data-lucide="camera" aria-hidden="true"></i></span>
                <div><strong>画面</strong><p>{{ $task['shot'] }}</p></div>
            </li>
            <li>
                <span><i data-lucide="text" aria-hidden="true"></i></span>
                <div><strong>文字</strong><p>{{ $task['copy'] }}</p></div>
            </li>
            <li>
                <span><i data-lucide="globe-2" aria-hidden="true"></i></span>
                <div><strong>平台</strong><p>小红书、抖音、微博、B站、快手、Instagram、Threads 或 X</p></div>
            </li>
        </ul>
    </section>

    <section class="reward-preview">
        <span><i data-lucide="gift" aria-hidden="true"></i></span>
        <div><small>完成核验后</small><strong>{{ $task['reward'] }}</strong></div>
        <b>演示权益</b>
    </section>

    <form method="POST" action="{{ route('co-create.task.accept') }}">
        @csrf
        <div class="flow-action-dock split-action">
            <a class="secondary-action" href="{{ route('co-create.profile') }}">换个身份</a>
            <button class="primary-action" type="submit">
                接受任务
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </form>
</main>
@endsection
