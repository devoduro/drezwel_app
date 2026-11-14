<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name = 'Baggy jeans', string $cat = 'Jeans'): Product
    {
        $p = Product::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'category' => $cat, 'cost_price' => 100, 'selling_price' => 250, 'stock_qty' => 5, 'show_in_store' => true]);
        $p->variants()->create(['stock_qty' => 5]);

        return $p;
    }

    public function test_home_page_has_the_brand_content_and_contact_details(): void
    {
        $this->product();
        $res = $this->get('/')->assertOk();
        $res->assertSee('A step above')->assertSee('All products')->assertSee('Jeans')->assertSee('New arrivals')->assertSee('Baggy jeans')
            ->assertSee('Buy at wholesale price')->assertSee('Follow on WhatsApp')
            ->assertSee('0200680701')->assertSee('0203264474')->assertSee('drezwelgh@gmail.com')->assertSee('Lascala Bus Stop')
            ->assertSee('https://www.instagram.com/drezwel_gh', false)
            ->assertSee('application/ld+json', false)->assertSee('og:title', false);
        $this->assertStringContainsString('tel:0200680701', $res->getContent());
        $this->assertStringContainsString('tel:0203264474', $res->getContent());
    }

    public function test_static_pages_render_with_editable_text(): void
    {
        foreach (['/about' => 'dedicated to providing fashion enthusiasts', '/contact' => 'Send message', '/wholesale' => 'Send enquiry',
            '/delivery-info' => 'Pay on delivery', '/return-policy' => 'Goods sold are not returnable', '/terms' => 'Terms and conditions', '/privacy-policy' => 'Privacy policy'] as $url => $text) {
            $this->get($url)->assertOk()->assertSee($text);
        }

        $m = User::factory()->create(['role' => 'manager']);
        $this->actingAs($m)->put(route('manager.settings.update'), ['business_name' => 'Drezwel', 'default_delivery_fee' => 0, 'policy_returns' => "Return policy\n\nReturns within 3 days.", 'about_text' => 'Our new story.', 'hours' => 'Mon-Sat 8am-7pm'])->assertSessionHasNoErrors();
        $this->app['auth']->forgetGuards();
        $this->get('/return-policy')->assertSee('Returns within 3 days.');
        $this->get('/about')->assertSee('Our new story.')->assertSee('Mon-Sat 8am-7pm');
    }

    public function test_manager_sets_a_hero_picture_that_shows_under_an_overlay_and_can_remove_it(): void
    {
        \Illuminate\Support\Facades\Storage::fake('uploads');
        $m = User::factory()->create(['role' => 'manager']);
        $im = imagecreatetruecolor(2400, 1000);
        $path = tempnam(sys_get_temp_dir(), 'h') . '.jpg';
        imagejpeg($im, $path);
        $file = new \Illuminate\Http\UploadedFile($path, 'hero.jpg', 'image/jpeg', null, true);
        $base = ['business_name' => 'Drezwel', 'default_delivery_fee' => 0];

        $this->get('/')->assertDontSee('uploads/site/', false);                      // no picture yet: plain background

        $this->actingAs($m)->put(route('manager.settings.update'), $base + ['hero_image' => $file])->assertSessionHasNoErrors();
        $stored = Setting::get('hero_image');
        $this->assertStringStartsWith('site/', $stored);
        \Illuminate\Support\Facades\Storage::disk('uploads')->assertExists($stored);
        $this->assertLessThanOrEqual(2000, getimagesizefromstring(\Illuminate\Support\Facades\Storage::disk('uploads')->get($stored))[0]); // shrunk

        $this->app['auth']->forgetGuards();
        $this->get('/')->assertSee('uploads/' . $stored, false)->assertSee('from-slate-950/90', false); // picture + dark overlay

        $this->actingAs($m)->put(route('manager.settings.update'), $base + ['remove_hero_image' => 1]);
        \Illuminate\Support\Facades\Storage::disk('uploads')->assertMissing($stored);
        $this->app['auth']->forgetGuards();
        $this->get('/')->assertDontSee('uploads/site/', false);

        // a non-image is refused
        $this->actingAs($m)->put(route('manager.settings.update'), $base + ['hero_image' => \Illuminate\Http\UploadedFile::fake()->create('x.php', 5, 'application/x-php')])->assertSessionHasErrors('hero_image');
    }

    public function test_home_hero_tiles_only_show_products_that_have_photos(): void
    {
        \Illuminate\Support\Facades\Storage::fake('uploads');
        $this->product('Plain shirt', 'Shirts');
        $this->get('/')->assertOk()->assertDontSee('translate-y-6', false);          // no photo: no empty placeholder tiles
    }

    public function test_contact_page_is_a_help_center_with_ways_to_reach_us_form_faq_and_map(): void
    {
        $res = $this->get('/contact')->assertOk();
        $res->assertSee('Help center')->assertSee('Call us')->assertSee('Chat with us')->assertSee('drezwelgh@gmail.com')
            ->assertSee('Send us a message')->assertSee('Send message')->assertSee('Common questions')->assertSee('How do I pay?')
            ->assertSee('Visit us')->assertSee('Open in Google Maps')->assertSee('Lascala Bus Stop')->assertSee('name="company_website"', false);
        $this->assertStringContainsString('tel:0200680701', $res->getContent());
        $this->assertStringContainsString('wa.me/233200680701', $res->getContent());

        // the wholesale version shares the layout but has its own wording and no FAQ
        $this->get('/wholesale')->assertOk()->assertSee('Send a wholesale enquiry')->assertSee('Send enquiry')->assertSee('Buy at wholesale price')->assertDontSee('Common questions');
    }

    public function test_about_page_has_help_menu_story_reasons_categories_and_find_us(): void
    {
        $this->product('Baggy jeans', 'Jeans');
        $res = $this->get('/about')->assertOk();
        $res->assertSee('Help center')->assertSee('Who we are')->assertSee('dedicated to providing fashion enthusiasts')
            ->assertSee('Why shop with')->assertSee('Pay on delivery')->assertSee('Track every step')
            ->assertSee('What we sell')->assertSee('Jeans')->assertSee('Find us')->assertSee('Lascala Bus Stop')->assertSee('Open in Google Maps')
            ->assertSee('Shop now')->assertSee('Contact us');
        $this->assertStringContainsString('tel:0200680701', $res->getContent());

        // shared menu: About is the highlighted entry on this page, Contact on the contact page
        $this->assertMatchesRegularExpression('/font-bold text-jm-primary"><i class="fas fa-circle-info/', $res->getContent());
        $this->assertMatchesRegularExpression('/font-bold text-jm-primary"><i class="fas fa-headset/', $this->get('/contact')->getContent());

        // text edited in Settings still drives the page
        Setting::set('about_text', "First paragraph here.\n\nSecond paragraph here.");
        $this->get('/about')->assertSee('First paragraph here.')->assertSee('Second paragraph here.');
    }

    public function test_site_menu_has_a_login_for_visitors_and_a_dashboard_link_for_signed_in_staff(): void
    {
        foreach (['/shop', '/about', '/contact', '/cart', '/track'] as $url) {
            $res = $this->get($url)->assertOk()->assertSee('Login');
            $this->assertStringContainsString('href="' . route('login') . '"', $res->getContent());
            $res->assertDontSee('My dashboard');
        }

        $manager = User::factory()->create(['role' => 'manager', 'name' => 'Ama Boss']);
        $this->actingAs($manager)->get('/shop')->assertOk()->assertSee('My dashboard')->assertSee('Log out')->assertSee('Ama');
        $this->assertStringContainsString('href="' . route('manager.dashboard') . '"', $this->get('/shop')->getContent());

        $this->app['auth']->forgetGuards();
        $rider = User::factory()->create(['role' => 'rider']);
        $this->assertStringContainsString('href="' . route('rider.mine') . '"', $this->actingAs($rider)->get('/shop')->getContent());
    }

    public function test_manager_can_send_a_test_sms_and_sees_whether_speso_is_configured(): void
    {
        $m = User::factory()->create(['role' => 'manager']);

        // no key: the page says so and the test reports the real reason
        config(['services.speso_sms.api_key' => '']);
        $this->actingAs($m)->get(route('manager.sms'))->assertOk()->assertSee('Speso connection')->assertSee('Missing')->assertSee('Send a test SMS');
        $this->actingAs($m)->post(route('manager.sms.test'), ['phone' => '024 400 0111'])->assertSessionHas('error');
        $this->assertSame(1, SmsLog::where('purpose', 'test')->where('success', false)->count());

        // with a key it goes out through Speso and is logged
        config(['services.speso_sms.api_key' => 'k', 'services.speso_sms.sender_id' => 'Drezwel']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $this->actingAs($m)->get(route('manager.sms'))->assertSee('Set')->assertDontSee('Missing')->assertDontSee('>k<', false);   // the key is never shown
        $this->actingAs($m)->post(route('manager.sms.test'), ['phone' => '0244000111', 'message' => 'Hello from the test'])->assertSessionHas('success');
        Http::assertSent(fn ($r) => $r['phone'] === '0244000111' && $r['message'] === 'Hello from the test' && $r['sender_id'] === 'Drezwel');
        $this->assertSame(1, SmsLog::where('purpose', 'test')->where('success', true)->count());

        // bad numbers are refused, and riders cannot use it
        $this->actingAs($m)->post(route('manager.sms.test'), ['phone' => '123'])->assertSessionHasErrors('phone');
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->post(route('manager.sms.test'), ['phone' => '0244000111'])->assertForbidden();
    }

    public function test_old_site_addresses_redirect_permanently(): void
    {
        foreach (['/categories' => '/shop', '/wholesale-order' => '/wholesale', '/track-your-order' => '/track', '/support-policy' => '/delivery-info', '/users/login' => '/login', '/flash-deals' => '/shop'] as $old => $new) {
            $this->get($old)->assertStatus(301)->assertRedirect($new);
        }
        $this->get('/category/streetwear')->assertStatus(301)->assertRedirect('/shop?q=streetwear');
    }

    public function test_contact_form_stores_the_message_and_texts_the_manager(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        User::factory()->create(['role' => 'manager', 'phone' => '0200000009']);

        $this->post('/contact', ['type' => 'contact', 'name' => 'Ama', 'phone' => '+233 24 400 0111', 'message' => 'Do you have size 34?'])->assertRedirect('/contact')->assertSessionHas('success');
        $msg = ContactMessage::first();
        $this->assertSame('0244000111', $msg->phone);
        $this->assertSame('new', $msg->status);
        $this->assertSame(1, SmsLog::where('purpose', 'new_message')->where('phone', '0200000009')->count());

        $this->post('/contact', ['type' => 'wholesale', 'name' => 'Kofi', 'phone' => '0555000111', 'subject' => '50 jeans', 'message' => 'Need 50 pieces for my shop'])->assertRedirect('/wholesale');
        $this->assertSame('wholesale', ContactMessage::latest('id')->first()->type);
    }

    public function test_contact_form_rejects_bad_input_and_bots(): void
    {
        $this->post('/contact', ['type' => 'contact', 'name' => '', 'phone' => '123', 'message' => 'x'])->assertSessionHasErrors(['name', 'phone', 'message']);
        $this->post('/contact', ['type' => 'contact', 'name' => 'Bot', 'phone' => '0244000111', 'message' => 'buy pills now', 'company_website' => 'spam.com'])->assertStatus(422);
        $this->post('/contact', ['type' => 'evil', 'name' => 'A', 'phone' => '0244000111', 'message' => 'hello there'])->assertSessionHasErrors('type');
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_manager_reads_and_handles_messages_and_nobody_else_can(): void
    {
        Http::fake();
        $this->post('/contact', ['type' => 'contact', 'name' => 'Ama', 'phone' => '0244000111', 'message' => 'Hello, size 34 please']);
        $m = User::factory()->create(['role' => 'manager']);
        $msg = ContactMessage::first();

        $this->actingAs($m)->get(route('manager.messages'))->assertOk()->assertSee('Hello, size 34 please')->assertSee('Mark handled');
        $this->actingAs($m)->post(route('manager.messages.toggle', $msg));
        $this->assertSame('handled', $msg->fresh()->status);
        $this->actingAs($m)->get(route('manager.messages'))->assertDontSee('Hello, size 34 please');           // inbox shows new only by default
        $this->actingAs($m)->get(route('manager.messages', ['status' => 'all']))->assertSee('Hello, size 34 please');

        $this->app['auth']->forgetGuards();
        $this->get(route('manager.messages'))->assertRedirect('/login');
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->get(route('manager.messages'))->assertForbidden();
    }

    public function test_sitemap_lists_pages_and_only_visible_products(): void
    {
        $this->product('Visible shirt', 'Shirts');
        $hidden = $this->product('Hidden coat', 'Coats');
        $hidden->update(['show_in_store' => false]);

        $res = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('content-type'));
        $res->assertSee(url('/about'), false)->assertSee(url('/terms'), false)->assertSee(url('/shop/visible-shirt'), false)->assertDontSee('hidden-coat');
        $this->assertStringContainsString('Sitemap:', file_get_contents(public_path('robots.txt')));
    }

    public function test_site_uses_the_site_layout_everywhere_and_links_staff_login(): void
    {
        foreach (['/', '/shop', '/about', '/contact', '/track', '/cart'] as $url) {
            $this->get($url)->assertOk()->assertSee('Staff login')->assertSee('Returns policy')->assertSee('Wholesale');
        }
        $this->get('/login')->assertOk();
    }
}
