<?php

namespace Tests\Feature;

use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $o = []): User
    {
        return User::factory()->create(array_merge(['role' => 'rider', 'phone' => '0244000111', 'email' => 'joe@x.test', 'password' => 'old-password'], $o));
    }

    private function sentCode(): ?string
    {
        $code = null;
        Http::assertSent(function ($req) use (&$code) {
            if (preg_match('/reset code is (\d{6})/', $req['message'] ?? '', $m)) {
                $code = $m[1];
            }

            return true;
        });

        return $code;
    }

    private function fakeSms(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
    }

    public function test_login_page_links_to_forgot_password_and_forms_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Forgot password?');
        $this->get('/forgot-password')->assertOk()->assertSee('Send me a code');
        $this->get('/reset-password')->assertNotFound();                       // no code requested yet
    }

    public function test_user_resets_password_with_an_sms_code_by_phone_or_email(): void
    {
        $this->fakeSms();
        $u = $this->user();

        $this->post('/forgot-password', ['login' => '+233 24 400 0111'])->assertRedirect('/reset-password');
        $code = $this->sentCode();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertSame(1, SmsLog::where('purpose', 'password_reset')->where('phone', '0244000111')->count());

        $this->get('/reset-password')->assertOk()->assertSee('024••••111');   // masked hint
        $this->post('/reset-password', ['code' => $code, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])->assertRedirect('/login');

        $this->assertTrue(Hash::check('brand-new-pass', $u->fresh()->password));
        $this->post('/login', ['login' => 'joe@x.test', 'password' => 'brand-new-pass'])->assertRedirect(route('rider.mine')); // new password works
        $this->assertAuthenticated();

        // the code was single-use and is not stored in the SMS log
        $this->post('/logout');
        $this->post('/reset-password', ['code' => $code, 'password' => 'another-pass-1', 'password_confirmation' => 'another-pass-1'])->assertNotFound();
        $this->assertFalse(SmsLog::where('message', 'like', "%{$code}%")->exists());

        // email works too (the code still goes to the phone on file)
        $this->post('/forgot-password', ['login' => 'JOE@X.TEST'])->assertRedirect('/reset-password');
        $this->assertSame(2, SmsLog::where('purpose', 'password_reset')->count());
    }

    public function test_reply_is_identical_for_unknown_inactive_and_phoneless_accounts_and_nothing_is_sent(): void
    {
        $this->fakeSms();
        $this->user(['is_active' => false, 'phone' => '0244000222', 'email' => 'gone@x.test']);
        $this->user(['phone' => null, 'email' => 'nophone@x.test']);

        foreach (['0200000000', '0244000222', 'nophone@x.test', 'nobody@x.test'] as $i => $login) {
            $this->post('/forgot-password', ['login' => $login])->assertRedirect('/reset-password')
                ->assertSessionHas('success', 'If that account has a phone number on file, we have texted it a 6-digit code.');
            \Illuminate\Support\Facades\RateLimiter::clear('pw-reset-ip:127.0.0.1');
        }
        Http::assertNothingSent();

        // and trying to use a code afterwards just fails generically
        $this->post('/reset-password', ['code' => '123456', 'password' => 'whatever-pass', 'password_confirmation' => 'whatever-pass'])->assertSessionHasErrors('code');
    }

    public function test_wrong_codes_are_limited_and_a_weak_or_mismatched_password_is_refused(): void
    {
        $this->fakeSms();
        $u = $this->user();
        $this->post('/forgot-password', ['login' => '0244000111']);
        $code = $this->sentCode();
        $bad = $code === '000000' ? '111111' : '000000';
        $good = ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'];

        $this->post('/reset-password', ['code' => $code, 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post('/reset-password', ['code' => $code, 'password' => 'brand-new-pass', 'password_confirmation' => 'different-pass'])->assertSessionHasErrors('password');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/reset-password', ['code' => $bad] + $good)->assertSessionHasErrors('code');
        }
        $this->post('/reset-password', ['code' => $code] + $good)->assertSessionHasErrors('code');        // locked out even with the right code
        $this->assertTrue(Hash::check('old-password', $u->fresh()->password));                          // password untouched
    }

    public function test_codes_cannot_be_requested_in_quick_succession(): void
    {
        $this->fakeSms();
        $this->user();
        $this->post('/forgot-password', ['login' => '0244000111'])->assertSessionHasNoErrors();
        $this->post('/forgot-password', ['login' => '0244000111'])->assertSessionHasErrors('login');
        $this->assertSame(1, SmsLog::where('purpose', 'password_reset')->count());
    }

    public function test_reset_signs_out_remembered_devices_and_signed_in_users_cannot_use_the_forms(): void
    {
        $this->fakeSms();
        $u = $this->user();
        $u->forceFill(['remember_token' => 'old-token'])->save();
        $this->post('/forgot-password', ['login' => '0244000111']);
        $this->post('/reset-password', ['code' => $this->sentCode(), 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']);
        $this->assertNotSame('old-token', $u->fresh()->remember_token);

        $this->actingAs($u->fresh())->get('/forgot-password')->assertRedirect();                        // guest-only pages
    }
}
