<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CoCreationController extends Controller
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const TASKS = [
        'student' => [
            'audience' => '学生党',
            'eyebrow' => '校园灵感任务',
            'number' => '01',
            'title' => '早八续航搭子，由你定义',
            'topic' => '#我的早八咖啡搭子',
            'brief' => '记录一杯陪你赶早课、泡图书馆或完成小组作业的日常时刻，说说它为什么属于你的校园节奏。',
            'shot' => '咖啡与校园日常同框，人物可不出镜',
            'copy' => '正文不少于 30 字，并带上任务话题',
            'reward' => '共创体验权益 × 1',
            'accent' => 'sky',
        ],
        'office' => [
            'audience' => '通勤上班族',
            'eyebrow' => '办公灵感任务',
            'number' => '02',
            'title' => '下午三点，给办公室一点好心情',
            'topic' => '#我的工位咖啡时刻',
            'brief' => '分享一个被咖啡重新点亮的工作片段：可能是会议间隙、专注时刻，也可以是同事间的一次轻松碰杯。',
            'shot' => '工位、通勤包或会议桌中的真实一幕',
            'copy' => '正文不少于 30 字，并带上任务话题',
            'reward' => '共创体验权益 × 1',
            'accent' => 'blue',
        ],
        'family' => [
            'audience' => '亲子家庭',
            'eyebrow' => '周末灵感任务',
            'number' => '03',
            'title' => '周末一起喝的那一杯',
            'topic' => '#我们家的周末碰杯',
            'brief' => '记录一次轻松的家庭饮品时刻，讲讲不同口味如何出现在同一张桌上，让共同选择变成周末记忆。',
            'shot' => '家庭出游、散步或居家场景中的饮品合照',
            'copy' => '正文不少于 30 字，并带上任务话题',
            'reward' => '共创体验权益 × 1',
            'accent' => 'coral',
        ],
        'community' => [
            'audience' => '兴趣社群',
            'eyebrow' => '城市灵感任务',
            'number' => '04',
            'title' => '把你的城市喝法带进同好圈',
            'topic' => '#同好碰杯计划',
            'brief' => '把咖啡带进骑行、摄影、露营或其他兴趣现场，分享你和同好们独有的碰杯方式。',
            'shot' => '兴趣装备、城市地点与饮品形成明确关联',
            'copy' => '正文不少于 30 字，并带上任务话题',
            'reward' => '共创体验权益 × 1',
            'accent' => 'mint',
        ],
    ];

    public function index(): View
    {
        return view('co-create.entry');
    }

    public function profile(): View
    {
        return view('co-create.profile', [
            'selectedAudience' => session('co_creation.audience'),
        ]);
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:student,office,family,community'],
        ], [
            'audience.required' => '请选择一个最接近你的身份',
            'audience.in' => '请选择页面中提供的用户身份',
        ]);

        $request->session()->put('co_creation.audience', $validated['audience']);
        $request->session()->forget([
            'co_creation.task_accepted',
            'co_creation.post_fingerprint',
            'co_creation.platform',
            'co_creation.reward',
        ]);

        return redirect()->route('co-create.task');
    }

    public function task(): RedirectResponse|View
    {
        $task = $this->selectedTask();
        if ($task === null) {
            return redirect()->route('co-create.profile');
        }

        return view('co-create.task', ['task' => $task]);
    }

    public function acceptTask(Request $request): RedirectResponse
    {
        if ($this->selectedTask() === null) {
            return redirect()->route('co-create.profile');
        }

        if (is_array($request->session()->get('co_creation.reward'))) {
            return redirect()->route('co-create.reward');
        }

        $request->session()->put('co_creation.task_accepted', true);

        return redirect()->route('co-create.publish');
    }

    public function publish(): RedirectResponse|View
    {
        $task = $this->selectedTask();
        if ($task === null) {
            return redirect()->route('co-create.profile');
        }

        if (! session('co_creation.task_accepted', false)) {
            return redirect()->route('co-create.task');
        }

        return view('co-create.publish', ['task' => $task]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $task = $this->selectedTask();
        if ($task === null) {
            return redirect()->route('co-create.profile');
        }

        if (! $request->session()->get('co_creation.task_accepted', false)) {
            return redirect()->route('co-create.task');
        }

        if (is_array($request->session()->get('co_creation.reward'))) {
            return redirect()->route('co-create.reward');
        }

        $validated = $request->validate([
            'post_url' => [
                'required',
                'string',
                'max:500',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $url = trim((string) $value);
                    $parts = parse_url($url);
                    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
                    $host = (string) ($parts['host'] ?? '');

                    if (
                        $scheme !== 'https'
                        || isset($parts['user'])
                        || isset($parts['pass'])
                        || isset($parts['port'])
                        || ! $this->isAllowedPlatformPostUrl($url)
                    ) {
                        $fail('请粘贴受支持社媒平台的 HTTPS 公开帖子链接，不要使用平台首页或登录页');
                    }
                },
            ],
            'rights_confirmed' => ['accepted'],
        ], [
            'post_url.required' => '请先粘贴已经发布的帖子链接',
            'post_url.max' => '帖子链接过长，请检查后重试',
            'rights_confirmed.accepted' => '请确认内容为本人发布且允许用于本次共创核验',
        ]);

        $postUrl = $this->normalizeSocialUrl((string) $validated['post_url']);
        $request->session()->put([
            'co_creation.post_fingerprint' => hash('sha256', $postUrl),
            'co_creation.platform' => $this->platformName($postUrl),
            'co_creation.reward' => [
                'name' => $task['reward'],
                'code' => 'COCREATE-'.Str::upper(Str::random(6)),
            ],
        ]);

        return redirect()->route('co-create.reward');
    }

    public function reward(): RedirectResponse|View
    {
        $task = $this->selectedTask();
        $reward = session('co_creation.reward');

        if ($task === null || ! is_array($reward)) {
            return redirect()->route('co-create.profile');
        }

        return view('co-create.reward', [
            'task' => $task,
            'reward' => $reward,
            'platform' => session('co_creation.platform', '社媒平台'),
        ]);
    }

    public function restart(Request $request): RedirectResponse
    {
        $request->session()->forget('co_creation');

        return redirect()->route('co-create.entry');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function selectedTask(): ?array
    {
        $audience = session('co_creation.audience');

        return is_string($audience) ? (self::TASKS[$audience] ?? null) : null;
    }

    private function platformName(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        foreach ($this->platformDomains() as $domain => $name) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $name;
            }
        }

        return '其他社媒平台';
    }

    private function normalizeSocialUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '/');

        return $scheme.'://'.$host.($path === '' ? '/' : $path);
    }

    private function isAllowedPlatformHost(string $host): bool
    {
        $host = strtolower($host);

        foreach (array_keys($this->platformDomains()) as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    private function isAllowedPlatformPostUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if (! $this->isAllowedPlatformHost($host) || $path === '' || $path === '/') {
            return false;
        }

        foreach ($this->platformPostPatterns() as $domain => $pattern) {
            if (($host === $domain || str_ends_with($host, '.'.$domain)) && preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function platformPostPatterns(): array
    {
        return [
            'v.douyin.com' => '~^/[A-Za-z0-9_-]{4,}/?$~i',
            'v.kuaishou.com' => '~^/[A-Za-z0-9_-]{4,}/?$~i',
            'xiaohongshu.com' => '~^/(?:explore|discovery/item)/[A-Za-z0-9_-]+/?$~i',
            'xhslink.com' => '~^/(?:(?:m|o)/)?[A-Za-z0-9_-]{4,}/?$~i',
            'douyin.com' => '~^/(?:video|note|share/(?:video|note))/[A-Za-z0-9_-]+/?$~i',
            'iesdouyin.com' => '~^/(?:video|note|share/(?:video|note))/[A-Za-z0-9_-]+/?$~i',
            'weibo.com' => '~^/(?:(?:u/)?[0-9]+/[A-Za-z0-9_-]+|detail/[A-Za-z0-9_-]+)/?$~i',
            'bilibili.com' => '~^/(?:video/(?:BV|av)[A-Za-z0-9]+|opus/[0-9]+)/?$~i',
            'b23.tv' => '~^/[A-Za-z0-9_-]{4,}/?$~i',
            'kuaishou.com' => '~^/(?:short-video|f)/[A-Za-z0-9_-]+/?$~i',
            'instagram.com' => '~^/(?:p|reel|tv)/[A-Za-z0-9_-]+/?$~i',
            'threads.net' => '~^/@[^/]+/post/[A-Za-z0-9_-]+/?$~i',
            'x.com' => '~^/[^/]+/status/[0-9]+/?$~i',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function platformDomains(): array
    {
        return [
            'xiaohongshu.com' => '小红书',
            'xhslink.com' => '小红书',
            'douyin.com' => '抖音',
            'iesdouyin.com' => '抖音',
            'weibo.com' => '微博',
            'bilibili.com' => '哔哩哔哩',
            'b23.tv' => '哔哩哔哩',
            'kuaishou.com' => '快手',
            'instagram.com' => 'Instagram',
            'threads.net' => 'Threads',
            'x.com' => 'X',
        ];
    }
}
