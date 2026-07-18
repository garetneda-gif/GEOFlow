@extends('co-create.layout')

@section('title', '提交作品')
@section('step', '3')

@section('content')
<main class="flow-shell publish-shell">
    <div class="flow-heading compact">
        <p class="flow-kicker">STEP 03 · 发布与核验</p>
        <h1>发布完成后，<br>把链接带回来。</h1>
        <p>系统会识别平台和链接格式。本演示不会访问或抓取你的帖子内容。</p>
    </div>

    <section class="publish-ticket">
        <div>
            <small>你的必带话题</small>
            <strong id="mission-topic">{{ $task['topic'] }}</strong>
        </div>
        <button type="button" class="copy-button" data-copy-target="mission-topic">
            <i data-lucide="copy" aria-hidden="true"></i>
            <span>复制</span>
        </button>
        <span class="sr-only" id="copy-status" aria-live="polite"></span>
    </section>

    <form class="verify-form" method="POST" action="{{ route('co-create.verify') }}" data-verify-form>
        @csrf
        <label for="post_url">社媒帖子链接</label>
        <div class="url-input-wrap @error('post_url') has-error @enderror">
            <i data-lucide="link-2" aria-hidden="true"></i>
            <input
                id="post_url"
                name="post_url"
                type="url"
                inputmode="url"
                autocomplete="url"
                placeholder="https://"
                value="{{ old('post_url') }}"
                required
                @error('post_url') aria-invalid="true" aria-describedby="post-url-error" @enderror
            >
        </div>
        @error('post_url')
            <p class="form-error" id="post-url-error" role="alert"><i data-lucide="circle-alert" aria-hidden="true"></i>{{ $message }}</p>
        @enderror

        <label class="consent-check">
            <input
                type="checkbox"
                name="rights_confirmed"
                value="1"
                required
                @checked(old('rights_confirmed'))
                @error('rights_confirmed') aria-invalid="true" aria-describedby="rights-error" @enderror
            >
            <span class="consent-box"><i data-lucide="check" aria-hidden="true"></i></span>
            <span>这是我本人发布的公开内容，并同意用于本次演示核验</span>
        </label>
        @error('rights_confirmed')
            <p class="form-error" id="rights-error" role="alert"><i data-lucide="circle-alert" aria-hidden="true"></i>{{ $message }}</p>
        @enderror

        <div class="demo-verification-note">
            <i data-lucide="shield-check" aria-hidden="true"></i>
            <p><strong>演示核验范围</strong><span>仅检查链接格式、平台域名与任务状态，不请求外部平台数据。</span></p>
        </div>

        <div class="flow-action-dock">
            <button class="primary-action" type="submit" data-verify-button>
                核验并领取奖励
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </form>
</main>
@endsection
