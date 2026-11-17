<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\Markup;
use App\Support\Pages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePagesTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_every_policy_page_has_real_content_even_when_the_saved_text_is_empty(): void
    {
        foreach (['delivery_text', 'policy_returns', 'policy_terms', 'policy_privacy', 'faq_text', 'about_text'] as $k) {
            Setting::set($k, '');                                              // exactly how the old settings form left them
        }

        $this->get('/delivery-info')->assertOk()->assertSee('How an order reaches you')->assertSee('Pay on delivery');
        $this->get('/return-policy')->assertOk()->assertSee('Returns', false)->assertSee('Exchanges');
        $this->get('/terms')->assertOk()->assertSee('Terms');
        $this->get('/privacy-policy')->assertOk()->assertSee('We do not sell your personal information');
        $this->get('/faqs')->assertOk()->assertSee('How can I place an order?')->assertSee('Do you sell wholesale?');
        $this->get('/about')->assertOk()->assertSee('How we serve you');
    }

    public function test_pages_fill_in_the_shops_own_details(): void
    {
        Setting::set('business_name', 'Drezwel');
        Setting::set('phone', '0201112222');
        Setting::set('email', 'hello@drezwel.test');

        $html = $this->get('/delivery-info')->assertOk()->getContent();
        $this->assertStringContainsString('0201112222', $html);
        $this->assertStringNotContainsString('{phone}', $html);                // placeholders are replaced
        $this->assertStringContainsString(route('track'), $html);
        $this->get('/privacy-policy')->assertSee('hello@drezwel.test');
    }

    public function test_the_shop_front_and_home_page_show_the_introduction_and_faqs(): void
    {
        foreach (['/', '/shop'] as $url) {
            $this->get($url)->assertOk()->assertSee('The Best Online Fashion Store in Ghana')->assertSee('Frequently asked questions')->assertSee('How long does delivery take?');
        }
        $this->get('/shop?q=zzz')->assertOk()->assertDontSee('Frequently asked questions');       // not on search results
        $this->get('/')->assertSee('Help', false)->assertSee(route('site.page', 'faqs'), false);
    }

    public function test_manager_edits_a_page_and_it_goes_live_and_an_empty_save_restores_the_draft(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->get(route('manager.pages'))->assertOk()->assertSee('Returns &amp; exchanges', false)->assertSee('Suggested draft')->assertSee('FAQs');
        $this->actingAs($m)->get(route('manager.pages.edit', 'return-policy'))->assertOk()->assertSee('Load the suggested draft')->assertSee('unworn, unwashed');

        $this->actingAs($m)->put(route('manager.pages.update', 'return-policy'), ['body' => "# Our rules\n\nAll sales are final for **{business}**.\n\n- No refunds"])->assertSessionHas('success');
        $this->get('/return-policy')->assertOk()->assertSee('Our rules')->assertSee('All sales are final for')->assertSee('No refunds')->assertDontSee('unworn, unwashed');
        $this->assertFalse(Pages::usesDraft('policy_returns'));
        $this->actingAs($m)->get(route('manager.pages'))->assertSee('Edited by you');

        $this->actingAs($m)->put(route('manager.pages.update', 'return-policy'), ['body' => ''])->assertSessionHas('success');
        $this->get('/return-policy')->assertSee('unworn, unwashed');
    }

    public function test_page_text_cannot_inject_scripts(): void
    {
        $html = Markup::render("<script>alert(1)</script>\n\n[click](javascript:alert(1))\n\n<img src=x onerror=alert(1)>");
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function test_faq_text_is_split_into_questions_and_answers(): void
    {
        $faqs = Markup::faqs("### First?\nAnswer one.\n\nMore.\n\n### Second?\nAnswer two.");
        $this->assertCount(2, $faqs);
        $this->assertSame('First?', $faqs[0]['q']);
        $this->assertStringContainsString('More.', $faqs[0]['a']);
        $this->assertSame('Answer two.', $faqs[1]['a']);
    }

    public function test_unknown_pages_and_non_managers_are_refused(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->get(route('manager.pages.edit', 'nope'))->assertNotFound();
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->get(route('manager.pages'))->assertForbidden();
        $this->actingAs($rider)->put(route('manager.pages.update', 'terms'), ['body' => 'x'])->assertForbidden();
        $this->get('/nope-page')->assertNotFound();
    }
}
