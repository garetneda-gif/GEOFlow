<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CoCreationFlowTest extends TestCase
{
    public function test_landing_page_introduces_the_co_creation_journey_and_scan_entry(): void
    {
        $this->get(route('co-create.entry'))
            ->assertOk()
            ->assertSee('用户共创系统')
            ->assertSee('下一杯灵感')
            ->assertSee('data-demo-qr', false)
            ->assertSee(route('co-create.profile'), false);
    }

    public function test_profile_page_offers_every_supported_audience(): void
    {
        $this->get(route('co-create.profile'))
            ->assertOk()
            ->assertSee('name="audience"', false)
            ->assertSee('value="student"', false)
            ->assertSee('value="office"', false)
            ->assertSee('value="family"', false)
            ->assertSee('value="community"', false)
            ->assertSee('aria-required="true"', false)
            ->assertSee('required', false)
            ->assertSee('学生党')
            ->assertSee('通勤上班族')
            ->assertSee('亲子家庭')
            ->assertSee('兴趣社群');
    }

    #[DataProvider('audienceProvider')]
    public function test_supported_audience_selection_is_stored_in_the_session(string $audience): void
    {
        $this->post(route('co-create.profile.store'), ['audience' => $audience])
            ->assertRedirect(route('co-create.task'))
            ->assertSessionHas('co_creation.audience', $audience);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function audienceProvider(): array
    {
        return [
            'student' => ['student'],
            'office' => ['office'],
            'family' => ['family'],
            'community' => ['community'],
        ];
    }

    public function test_profile_rejects_an_unsupported_audience(): void
    {
        $this->from(route('co-create.profile'))
            ->post(route('co-create.profile.store'), ['audience' => 'other'])
            ->assertRedirect(route('co-create.profile'))
            ->assertSessionHasErrors(['audience'])
            ->assertSessionMissing('co_creation.audience');

        $this->get(route('co-create.profile'))
            ->assertSee('id="audience-error"', false)
            ->assertSee('aria-describedby="audience-error"', false);
    }

    public function test_selecting_a_new_profile_clears_downstream_progress(): void
    {
        $this->withSession([
            'co_creation' => [
                'audience' => 'student',
                'task_accepted' => true,
                'post_fingerprint' => hash('sha256', 'old'),
                'reward' => ['name' => 'old', 'code' => 'old'],
            ],
        ])->post(route('co-create.profile.store'), ['audience' => 'office'])
            ->assertSessionHas('co_creation.audience', 'office')
            ->assertSessionMissing('co_creation.task_accepted')
            ->assertSessionMissing('co_creation.post_fingerprint')
            ->assertSessionMissing('co_creation.reward');
    }

    public function test_protected_steps_redirect_to_profile_when_audience_is_missing(): void
    {
        foreach (['co-create.task', 'co-create.publish', 'co-create.reward'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('co-create.profile'));
        }

        $this->post(route('co-create.task.accept'))->assertRedirect(route('co-create.profile'));
        $this->post(route('co-create.verify'), [
            'post_url' => 'https://www.xiaohongshu.com/explore/demo-post',
            'rights_confirmed' => '1',
        ])->assertRedirect(route('co-create.profile'));
    }

    public function test_user_can_view_accept_and_open_the_publish_step(): void
    {
        $session = ['co_creation' => ['audience' => 'office']];

        $this->withSession($session)
            ->get(route('co-create.task'))
            ->assertOk()
            ->assertSee('匹配完成')
            ->assertSee('接受任务');

        $this->post(route('co-create.task.accept'))
            ->assertRedirect(route('co-create.publish'))
            ->assertSessionHas('co_creation.task_accepted', true);

        $this->get(route('co-create.publish'))
            ->assertOk()
            ->assertSee('name="post_url"', false)
            ->assertSee('演示核验范围');
    }

    public function test_verify_requires_an_accepted_task(): void
    {
        $this->withSession(['co_creation' => ['audience' => 'family']])
            ->post(route('co-create.verify'), [
                'post_url' => 'https://www.weibo.com/123456/demo',
                'rights_confirmed' => '1',
            ])
            ->assertRedirect(route('co-create.task'))
            ->assertSessionMissing('co_creation.reward');
    }

    public function test_verify_requires_the_publish_rights_confirmation(): void
    {
        $this->withSession($this->acceptedTaskSession())
            ->from(route('co-create.publish'))
            ->post(route('co-create.verify'), [
                'post_url' => 'https://www.xiaohongshu.com/explore/demo-post',
            ])
            ->assertRedirect(route('co-create.publish'))
            ->assertSessionHasErrors(['rights_confirmed'])
            ->assertSessionMissing('co_creation.reward');

        $this->withSession($this->acceptedTaskSession())
            ->get(route('co-create.publish'))
            ->assertOk()
            ->assertSee('name="rights_confirmed"', false)
            ->assertSee('required', false);
    }

    #[DataProvider('invalidPostUrlProvider')]
    public function test_verify_rejects_non_social_or_unsafe_links(string $postUrl): void
    {
        $this->withSession($this->acceptedTaskSession())
            ->from(route('co-create.publish'))
            ->post(route('co-create.verify'), [
                'post_url' => $postUrl,
                'rights_confirmed' => '1',
            ])
            ->assertRedirect(route('co-create.publish'))
            ->assertSessionHasErrors(['post_url'])
            ->assertSessionMissing('_old_input.post_url')
            ->assertSessionMissing('co_creation.reward');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidPostUrlProvider(): array
    {
        return [
            'non-social-domain' => ['https://example.com/posts/not-social-media'],
            'plain-http' => ['http://www.xiaohongshu.com/explore/insecure'],
            'userinfo' => ['https://user:password@www.xiaohongshu.com/explore/unsafe'],
            'platform-root' => ['https://www.xiaohongshu.com/'],
            'platform-login' => ['https://www.xiaohongshu.com/login'],
            'arbitrary-platform-path' => ['https://x.com/explore'],
        ];
    }

    #[DataProvider('supportedPostUrlProvider')]
    public function test_verify_accepts_each_declared_social_platform(string $postUrl, string $platform): void
    {
        $this->withSession($this->acceptedTaskSession())
            ->post(route('co-create.verify'), [
                'post_url' => $postUrl,
                'rights_confirmed' => '1',
            ])
            ->assertRedirect(route('co-create.reward'))
            ->assertSessionHas('co_creation.platform', $platform)
            ->assertSessionHas('co_creation.reward');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function supportedPostUrlProvider(): array
    {
        return [
            'xiaohongshu' => ['https://www.xiaohongshu.com/explore/demo', '小红书'],
            'douyin' => ['https://www.douyin.com/video/728123456789', '抖音'],
            'douyin-share-link' => ['https://v.douyin.com/uF3tWjKSwkQ/', '抖音'],
            'weibo' => ['https://weibo.com/123/demo', '微博'],
            'bilibili' => ['https://www.bilibili.com/video/BV1demo123', '哔哩哔哩'],
            'kuaishou' => ['https://www.kuaishou.com/short-video/demo', '快手'],
            'kuaishou-share-link' => ['https://v.kuaishou.com/AbCdE123', '快手'],
            'xiaohongshu-share-link' => ['https://xhslink.com/o/AbCdE123', '小红书'],
            'instagram' => ['https://www.instagram.com/p/demo', 'Instagram'],
            'threads' => ['https://www.threads.net/@demo/post/demo', 'Threads'],
            'x' => ['https://x.com/demo/status/123', 'X'],
        ];
    }

    public function test_complete_flow_unlocks_a_demo_reward_for_a_valid_social_link(): void
    {
        $postUrl = 'https://www.xiaohongshu.com/explore/6688abc123';

        $this->post(route('co-create.profile.store'), ['audience' => 'student'])
            ->assertRedirect(route('co-create.task'));
        $this->post(route('co-create.task.accept'))
            ->assertRedirect(route('co-create.publish'));
        $this->post(route('co-create.verify'), [
            'post_url' => $postUrl,
            'rights_confirmed' => '1',
        ])
            ->assertRedirect(route('co-create.reward'))
            ->assertSessionHas('co_creation.post_fingerprint', hash('sha256', $postUrl))
            ->assertSessionHas('co_creation.platform', '小红书')
            ->assertSessionHas('co_creation.reward');

        $this->get(route('co-create.reward'))
            ->assertOk()
            ->assertSee('核验通过')
            ->assertSee('仅供概念演示，无实际兑换效力')
            ->assertSee('原始地址不在系统内保留')
            ->assertSee('小红书');
    }

    public function test_repeat_verify_keeps_the_original_reward(): void
    {
        $reward = ['name' => '共创体验权益 × 1', 'code' => 'COCREATE-ORIGINAL'];

        $this->withSession([
            'co_creation' => [
                'audience' => 'student',
                'task_accepted' => true,
                'post_fingerprint' => hash('sha256', 'first'),
                'platform' => '小红书',
                'reward' => $reward,
            ],
        ])->post(route('co-create.verify'), [
            'post_url' => 'https://www.weibo.com/123456/new-post',
            'rights_confirmed' => '1',
        ])
            ->assertRedirect(route('co-create.reward'))
            ->assertSessionHas('co_creation.reward', $reward)
            ->assertSessionHas('co_creation.platform', '小红书');
    }

    public function test_restart_clears_the_co_creation_session(): void
    {
        $this->withSession($this->acceptedTaskSession())
            ->post(route('co-create.restart'))
            ->assertRedirect(route('co-create.entry'))
            ->assertSessionMissing('co_creation');
    }

    /**
     * @return array{co_creation: array{audience: string, task_accepted: bool}}
     */
    private function acceptedTaskSession(): array
    {
        return [
            'co_creation' => [
                'audience' => 'student',
                'task_accepted' => true,
            ],
        ];
    }
}
