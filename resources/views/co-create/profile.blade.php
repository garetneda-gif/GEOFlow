@extends('co-create.layout')

@section('title', '认识你')
@section('step', '1')

@section('content')
<main class="flow-shell">
    <div class="flow-heading">
        <p class="flow-kicker">STEP 01 · 认识你</p>
        <h1>今天的你，<br>更接近哪一种状态？</h1>
        <p>我们会据此匹配不同的话题。无需注册，选择最接近的一项即可。</p>
    </div>

    <form class="profile-form" method="POST" action="{{ route('co-create.profile.store') }}">
        @csrf
        <fieldset
            class="audience-grid"
            aria-required="true"
            @error('audience') aria-invalid="true" aria-describedby="audience-error" @enderror
        >
            <legend class="sr-only">请选择用户身份</legend>
            @php
                $audiences = [
                    'student' => ['graduation-cap', '学生党', '早八、图书馆与校园日常'],
                    'office' => ['briefcase-business', '通勤上班族', '工位、会议与城市通勤'],
                    'family' => ['users-round', '亲子家庭', '周末、散步与家庭碰杯'],
                    'community' => ['bike', '兴趣社群', '骑行、摄影与同好现场'],
                ];
            @endphp
            @foreach($audiences as $value => [$icon, $label, $description])
                <label class="audience-option">
                    <input type="radio" name="audience" value="{{ $value }}" required @checked(old('audience', $selectedAudience) === $value)>
                    <span class="audience-card">
                        <span class="audience-icon"><i data-lucide="{{ $icon }}" aria-hidden="true"></i></span>
                        <span class="audience-copy">
                            <strong>{{ $label }}</strong>
                            <small>{{ $description }}</small>
                        </span>
                        <span class="radio-mark"><i data-lucide="check" aria-hidden="true"></i></span>
                    </span>
                </label>
            @endforeach
        </fieldset>

        @error('audience')
            <p class="form-error" id="audience-error" role="alert"><i data-lucide="circle-alert" aria-hidden="true"></i>{{ $message }}</p>
        @enderror

        <div class="flow-action-dock">
            <button class="primary-action" type="submit">
                为我匹配任务
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </form>
</main>
@endsection
