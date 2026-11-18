<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
    }

    private function manager(array $o = []): User
    {
        return User::factory()->create(['role' => 'manager'] + $o);
    }

    /** A jeans product with 2 sizes x 2 colours, 5 of each. */
    private function jeans(): Product
    {
        $p = Product::create(['name' => 'Baggy jeans', 'slug' => 'baggy-jeans', 'category' => 'Jeans', 'cost_price' => 100, 'selling_price' => 250, 'reorder_level' => 2, 'show_in_store' => true]);
        foreach ([['32', 'Blue'], ['32', 'Black'], ['34', 'Blue'], ['34', 'Black']] as [$s, $c]) {
            $p->variants()->create(['size' => $s, 'color' => $c, 'stock_qty' => 5]);
        }
        $p->update(['stock_qty' => 20]);

        return $p->fresh('variants');
    }

    private function jpeg(): UploadedFile
    {
        $im = imagecreatetruecolor(1600, 1200);
        imagefill($im, 0, 0, imagecolorallocate($im, 30, 80, 160));
        $path = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        imagejpeg($im, $path);

        return new UploadedFile($path, 'jeans.jpg', 'image/jpeg', null, true);
    }

    // ---------------------------------------------------------------- products

    public function test_manager_creates_a_product_with_sizes_colours_and_photos(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.products.store'), [
            'name' => 'Slim jeans', 'category' => 'Jeans', 'cost_price' => 120, 'selling_price' => 280, 'reorder_level' => 3, 'show_in_store' => 1,
            'description' => 'Stretch denim.', 'images' => [$this->jpeg()],
            'new' => [['size' => '30', 'color' => 'Blue', 'stock' => 4], ['size' => '32', 'color' => 'Blue', 'stock' => 6, 'price' => 300], ['size' => '', 'color' => '']],
        ])->assertSessionHasNoErrors();

        $p = Product::first();
        $this->assertSame(2, $p->variants()->count());
        $this->assertSame(10, $p->stock_qty);
        $this->assertSame('slim-jeans-' . $p->id, $p->slug);
        $this->assertEquals(300, $p->variants()->where('size', '32')->first()->selling_price);
        $this->assertSame(1, $p->images()->count());
        Storage::disk('uploads')->assertExists($p->images->first()->path);
        $this->assertSame(2, $p->movements()->count()); // one opening entry per variant
    }

    public function test_duplicate_variants_and_non_images_are_rejected(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.products.store'), [
            'name' => 'X', 'cost_price' => 1, 'selling_price' => 2, 'reorder_level' => 1,
            'new' => [['size' => 'M', 'color' => 'Red'], ['size' => 'm', 'color' => 'red']],
        ])->assertSessionHasErrors('new');
        $this->assertSame(0, Product::count());

        $this->actingAs($m)->post(route('manager.products.store'), [
            'name' => 'X', 'cost_price' => 1, 'selling_price' => 2, 'reorder_level' => 1,
            'images' => [UploadedFile::fake()->create('evil.php', 10, 'application/x-php')],
        ])->assertSessionHasErrors('images.0');
    }

    public function test_photo_can_be_made_main_and_deleted(): void
    {
        $m = $this->manager();
        $p = $this->jeans();
        $svc = app(\App\Services\ProductImageService::class);
        $a = $svc->add($p, $this->jpeg());
        $b = $svc->add($p, $this->jpeg());
        $this->assertSame($a->id, $p->fresh()->images->first()->id);

        $this->actingAs($m)->post(route('manager.products.image.main', $b))->assertSessionHas('success');
        $this->assertSame($b->id, $p->fresh()->images->first()->id);
        $this->actingAs($m)->delete(route('manager.products.image.delete', $b));
        $this->assertNull(ProductImage::find($b->id));
        Storage::disk('uploads')->assertMissing($b->path);
    }

    public function test_stock_is_tracked_per_size_and_colour(): void
    {
        $m = $this->manager();
        $p = $this->jeans();
        $v = $p->variants->firstWhere(fn ($x) => $x->size === '32' && $x->color === 'Blue');

        $this->actingAs($m)->post(route('manager.products.adjust', $p), ['variant_id' => $v->id, 'action' => 'purchase', 'qty' => 10])->assertSessionHasNoErrors();
        $this->assertSame(15, $v->fresh()->stock_qty);
        $this->assertSame(30, $p->fresh()->stock_qty);
        // a variant of another product is refused
        $other = Product::create(['name' => 'Other', 'cost_price' => 1, 'selling_price' => 2]);
        $this->actingAs($m)->post(route('manager.products.adjust', $other), ['variant_id' => $v->id, 'action' => 'purchase', 'qty' => 1])->assertSessionHasErrors('variant_id');
    }

    // ---------------------------------------------------------------- online store

    public function test_guests_see_the_home_page_and_the_shop_with_prices_in_cedis(): void
    {
        $p = $this->jeans();
        $this->get('/')->assertOk()->assertSee('A step above');
        $this->get('/store')->assertRedirect('/shop');
        $this->get('/shop')->assertOk()->assertSee('Baggy jeans')->assertSee('GH₵ 250.00');
        $this->get('/shop/baggy-jeans')->assertOk()->assertSee('Size')->assertSee('Colour');
        $this->get('/shop?q=nothing-like-this')->assertOk()->assertDontSee('Baggy jeans');

        $p->update(['show_in_store' => false]);
        $this->get('/shop')->assertDontSee('Baggy jeans');
        $this->get('/shop/baggy-jeans')->assertNotFound();
    }

    public function test_shop_front_is_laid_out_in_titled_rows_and_filters_to_a_grid(): void
    {
        $jeans = $this->jeans();
        $shirt = Product::create(['name' => 'Plain shirt', 'slug' => 'plain-shirt', 'category' => 'Shirts', 'cost_price' => 50, 'selling_price' => 120, 'stock_qty' => 4, 'show_in_store' => true]);
        $shirt->variants()->create(['stock_qty' => 4]);

        $res = $this->get('/shop')->assertOk();
        $res->assertSee('New arrivals')->assertSee('See all')->assertSee('Baggy jeans')->assertSee('Plain shirt')->assertSee('GH₵ 120.00');
        $html = $res->getContent();
        $this->assertStringContainsString('overflow-x-auto', $html);                    // sideways-scrolling rows
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'name="variant_id" value="' . $shirt->variants()->first()->id . '"'), 'plain product gets a "+" that adds it directly');
        $this->assertStringNotContainsString('name="variant_id" value="' . $jeans->variants->first()->id . '"', $html); // sizes/colours: "+" opens the product page

        // a category (or search) becomes a plain grid with a result count
        $this->get('/shop?category=Shirts')->assertOk()->assertSee('Plain shirt')->assertDontSee('Baggy jeans')->assertSee('1 product found');
        $this->get('/shop?q=jeans')->assertOk()->assertSee('Baggy jeans')->assertDontSee('Plain shirt');
    }

    public function test_offers_show_old_price_and_discount_and_have_their_own_band_and_filter(): void
    {
        $jeans = $this->jeans();
        $jeans->update(['compare_at_price' => 500, 'is_deal' => true]);   // selling 250 -> 50% off, and chosen as a deal
        $shirt = Product::create(['name' => 'Plain shirt', 'slug' => 'plain-shirt', 'category' => 'Shirts', 'cost_price' => 50, 'selling_price' => 120, 'stock_qty' => 4, 'show_in_store' => true]);
        $shirt->variants()->create(['stock_qty' => 4]);

        $this->assertSame(50, $jeans->fresh('variants')->discount_percent);
        $this->assertNull($shirt->fresh('variants')->discount_percent);

        $res = $this->get('/shop')->assertOk()->assertSee("Today's deals")->assertSee('-50%')->assertSee('GH₵ 500.00');
        $this->assertStringContainsString('bg-jm-red', $res->getContent());           // the crimson deals band
        $this->get('/shop?deals=1')->assertOk()->assertSee('Baggy jeans')->assertDontSee('Plain shirt')->assertSee("Today's deals");

        // a "was" price that is not higher than the selling price is no discount, though the product stays a deal until the manager removes it
        $jeans->update(['compare_at_price' => 200]);
        $this->assertNull($jeans->fresh('variants')->discount_percent);
        $this->get('/shop?deals=1')->assertSee('Baggy jeans');
        $jeans->update(['is_deal' => false]);
        $this->get('/shop?deals=1')->assertDontSee('Baggy jeans');
    }

    public function test_shop_listing_filters_by_price_and_sorts(): void
    {
        foreach ([['Cheap tee', 50], ['Mid shirt', 150], ['Pricey coat', 400]] as [$n, $price]) {
            $p = Product::create(['name' => $n, 'slug' => \Illuminate\Support\Str::slug($n), 'category' => 'Mix', 'cost_price' => 10, 'selling_price' => $price, 'stock_qty' => 5, 'show_in_store' => true]);
            $p->variants()->create(['stock_qty' => 5]);
        }
        $this->get('/shop?min_price=100&max_price=300')->assertOk()->assertSee('Mid shirt')->assertDontSee('Cheap tee')->assertDontSee('Pricey coat');

        $asc = $this->get('/shop?sort=price_asc')->getContent();
        $this->assertLessThan(strpos($asc, 'Mid shirt'), strpos($asc, 'Cheap tee'));
        $this->assertLessThan(strpos($asc, 'Pricey coat'), strpos($asc, 'Mid shirt'));
        $desc = $this->get('/shop?sort=price_desc')->getContent();
        $this->assertLessThan(strpos($desc, 'Cheap tee'), strpos($desc, 'Pricey coat'));
    }

    public function test_manager_can_set_an_old_price_on_a_product(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.products.store'), ['name' => 'Jacket', 'cost_price' => 100, 'selling_price' => 200, 'compare_at_price' => 260, 'reorder_level' => 2, 'show_in_store' => 1, 'opening_stock' => 3])->assertSessionHasNoErrors();
        $this->assertEquals(260, Product::first()->compare_at_price);
        $this->get('/shop')->assertSee('-23%');
    }

    public function test_cart_page_shows_summary_and_quantity_buttons_change_the_cart(): void
    {
        $shirt = Product::create(['name' => 'Plain shirt', 'slug' => 'plain-shirt', 'category' => 'Shirts', 'cost_price' => 50, 'selling_price' => 120, 'compare_at_price' => 150, 'stock_qty' => 4, 'show_in_store' => true]);
        $v = $shirt->variants()->create(['stock_qty' => 4]);

        $this->get('/cart')->assertSee('Your cart is empty!')->assertSee('Start shopping');
        $this->post('/cart/add', ['variant_id' => $v->id, 'qty' => 2]);
        $this->get('/cart')->assertOk()->assertSee('Cart (2)')->assertSee('Cart summary')->assertSee('Checkout')->assertSee('GH₵ 240.00')->assertSee('-20%')->assertSee('Remove');

        $this->post('/cart/update', ['variant_id' => $v->id, 'qty' => 3]);                    // "+"
        $this->assertSame(3, session('cart')[$v->id]);
        $this->post('/cart/update', ['variant_id' => $v->id, 'qty' => 9]);                    // beyond stock: capped
        $this->assertSame(4, session('cart')[$v->id]);
        $this->post('/cart/update', ['variant_id' => $v->id, 'qty' => 0]);                    // "-" to zero / Remove
        $this->assertArrayNotHasKey($v->id, session('cart', []));
    }

    public function test_checkout_page_has_three_steps_summary_and_confirm_button(): void
    {
        $p = $this->jeans();
        $v = $p->variants->first();
        $this->get('/checkout')->assertRedirect(route('shop.cart'));                      // empty cart goes back

        $this->post('/cart/add', ['variant_id' => $v->id, 'qty' => 2]);
        $this->get('/checkout')->assertOk()
            ->assertSee('Customer address')->assertSee('Delivery details')->assertSee('Payment method')
            ->assertSee('Shipment (2 items)', false)->assertSee('Pay on delivery')->assertSee('Order summary')
            ->assertSee('Confirm order')->assertSee('Modify cart')->assertSee('GH₵ 500.00');

        Setting::set('store_open', '0');
        $this->get('/checkout')->assertSee('Online ordering is paused');
    }

    public function test_product_page_has_breadcrumb_delivery_panel_details_and_discount(): void
    {
        $p = $this->jeans();
        $p->update(['compare_at_price' => 500, 'description' => 'Loose fit denim.', 'sku' => 'JN-1']);
        $this->get('/shop/baggy-jeans')->assertOk()
            ->assertSee('Similar products from Jeans')->assertSee('Variation available')->assertSee('Delivery &amp; returns', false)->assertSee('Product details')
            ->assertSee('Loose fit denim.')->assertSee('JN-1')->assertSee('-50%')->assertSee('GH₵ 500.00')->assertSee('Add to cart')
        ;
    }

    public function test_plus_on_a_tile_adds_to_cart_and_keeps_browsing(): void
    {
        $shirt = Product::create(['name' => 'Plain shirt', 'slug' => 'plain-shirt', 'category' => 'Shirts', 'cost_price' => 50, 'selling_price' => 120, 'stock_qty' => 4, 'show_in_store' => true]);
        $v = $shirt->variants()->create(['stock_qty' => 4]);

        $this->from('/shop')->post('/cart/add', ['variant_id' => $v->id, 'stay' => 1])->assertRedirect('/shop')->assertSessionHas('success');
        $this->assertSame(1, session('cart')[$v->id]);
        $this->post('/cart/add', ['variant_id' => $v->id])->assertRedirect(route('shop.cart')); // product page button still goes to the cart
        $this->assertSame(2, session('cart')[$v->id]);
    }

    public function test_customer_buys_from_the_website_end_to_end(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        Setting::set('default_delivery_fee', '20');
        $mgr = $this->manager(['phone' => '0200000009']);
        $p = $this->jeans();
        $v = $p->variants->firstWhere(fn ($x) => $x->size === '34' && $x->color === 'Black');

        $this->post('/cart/add', ['variant_id' => $v->id, 'qty' => 2])->assertRedirect(route('shop.cart'));
        $this->get('/cart')->assertOk()->assertSee('Size 34 · Black')->assertSee('GH₵ 500.00');

        $res = $this->post('/checkout', ['name' => 'Ama Mensah', 'phone' => '+233 24 400 0111', 'location' => 'Osu, near the mall', 'notes' => 'Call first']);
        $d = Delivery::first();
        $this->assertNotNull($d);
        $res->assertRedirect();
        $this->assertStringContainsString('/order/' . $d->reference, $res->headers->get('Location'));

        $this->assertSame('online', $d->source);
        $this->assertSame('pending', $d->status);
        $this->assertEquals(500, $d->total_amount);
        $this->assertEquals(20, $d->delivery_fee);
        $this->assertEquals(520, $d->balance);                 // pays goods + fee on delivery
        $this->assertSame('0244000111', $d->customer->phone);
        $this->assertSame(3, $v->fresh()->stock_qty);          // 5 - 2
        $this->assertSame(18, $p->fresh()->stock_qty);
        $this->assertSame($v->id, $d->items->first()->variant_id);
        $this->assertNull(session('cart'));                    // cart emptied

        // the customer and the manager are texted
        $this->assertSame(1, SmsLog::where('purpose', 'order_received')->count());
        $this->assertSame(1, SmsLog::where('purpose', 'new_order')->where('phone', '0200000009')->count());

        // signed thank-you page works, an unsigned one does not
        $this->get($res->headers->get('Location'))->assertOk()->assertSee($d->reference)->assertSee('GH₵ 520.00')->assertSee('Print invoice');
        $this->get('/order/' . $d->reference)->assertForbidden();
    }

    public function test_website_prices_come_from_the_server_and_overselling_is_blocked(): void
    {
        Http::fake();
        $p = $this->jeans();
        $v = $p->variants->first();
        $this->post('/cart/add', ['variant_id' => $v->id, 'qty' => 20]);
        $this->assertSame(5, session('cart')[$v->id]);         // capped at stock

        // someone empties the shelf while the customer is in the cart
        $v->update(['stock_qty' => 1]);
        $this->post('/checkout', ['name' => 'Kofi', 'phone' => '0244000222', 'location' => 'Accra'])->assertRedirect(route('shop.cart'));
        $this->assertSame(0, Delivery::count());
        $this->assertSame(1, $v->fresh()->stock_qty);

        $this->post('/cart/add', ['variant_id' => 999999])->assertSessionHasErrors('variant_id');
    }

    public function test_website_customer_cannot_overwrite_an_existing_customer_and_bots_are_stopped(): void
    {
        Http::fake();
        Customer::create(['name' => 'Real Name', 'phone' => '0244000111', 'location' => 'Real place']);
        $p = $this->jeans();
        $this->post('/cart/add', ['variant_id' => $p->variants->first()->id]);

        $this->post('/checkout', ['name' => 'company_website' === 'x' ? '' : 'Hacker', 'phone' => '0244000111', 'location' => 'Elsewhere', 'company_website' => 'spam.com'])->assertStatus(422);
        $this->post('/checkout', ['name' => 'Hacker', 'phone' => '0244000111', 'location' => 'Elsewhere'])->assertRedirect();
        $this->assertSame('Real Name', Customer::first()->name);
        $this->assertSame('Elsewhere', Delivery::first()->location);
    }

    public function test_paused_store_refuses_orders(): void
    {
        Http::fake();
        Setting::set('store_open', '0');
        $p = $this->jeans();
        $this->post('/cart/add', ['variant_id' => $p->variants->first()->id]);
        $this->post('/checkout', ['name' => 'A', 'phone' => '0244000111', 'location' => 'X'])->assertSessionHasErrors('store');
        $this->assertSame(0, Delivery::count());
    }

    // ---------------------------------------------------------------- POS

    public function test_counter_sale_is_paid_delivered_and_takes_stock(): void
    {
        $m = $this->manager();
        $p = $this->jeans();
        $v = $p->variants->first();

        $res = $this->actingAs($m)->post(route('manager.pos.store'), [
            'type' => 'counter', 'items' => [['variant_id' => $v->id, 'qty' => 2, 'price' => 240]], 'method' => 'momo', 'received' => 480,
        ]);
        $d = Delivery::first();
        $res->assertRedirect(route('manager.deliveries.invoice', ['delivery' => $d, 'print' => 1, 'format' => 'receipt']));

        $this->assertSame('pos', $d->source);
        $this->assertSame('delivered', $d->status);
        $this->assertEquals(480, $d->total_amount);            // the price the manager typed (a discount), not the list price
        $this->assertEquals(0, $d->balance);
        $this->assertSame('momo', $d->payments->first()->method);
        $this->assertSame(3, $v->fresh()->stock_qty);
        $this->assertSame(Customer::where('phone', '0000000000')->first()->id, $d->customer_id); // walk-in
    }

    public function test_counter_sale_part_payment_leaves_a_balance_and_change_is_not_recorded(): void
    {
        $m = $this->manager();
        $v = $this->jeans()->variants->first();
        $this->actingAs($m)->post(route('manager.pos.store'), ['type' => 'counter', 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 250]], 'received' => 100, 'name' => 'Kojo', 'phone' => '0555000111']);
        $this->assertEquals(150, Delivery::first()->balance);

        $this->actingAs($m)->post(route('manager.pos.store'), ['type' => 'counter', 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 250]], 'received' => 1000]);
        $this->assertEquals(250, Delivery::latest('id')->first()->amount_paid); // capped: change is not income
    }

    public function test_phone_order_goes_into_the_delivery_queue(): void
    {
        Http::fake();
        $m = $this->manager();
        $v = $this->jeans()->variants->first();

        $this->actingAs($m)->post(route('manager.pos.store'), [
            'type' => 'delivery', 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 250]],
            'name' => 'Esi', 'phone' => '0241112222', 'location' => 'Tema', 'delivery_fee' => 30,
        ])->assertSessionHasNoErrors();
        $d = Delivery::first();
        $this->assertSame('phone', $d->source);
        $this->assertSame('pending', $d->status);
        $this->assertEquals(280, $d->balance);
        $this->assertSame(4, $v->fresh()->stock_qty);

        // a phone order needs a name, phone and location
        $this->actingAs($m)->post(route('manager.pos.store'), ['type' => 'delivery', 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 250]]])
            ->assertSessionHasErrors(['name', 'phone', 'location']);
        $this->assertSame(1, Delivery::count());
    }

    public function test_pos_cannot_oversell(): void
    {
        $m = $this->manager();
        $v = $this->jeans()->variants->first();
        $this->actingAs($m)->post(route('manager.pos.store'), ['type' => 'counter', 'items' => [['variant_id' => $v->id, 'qty' => 6, 'price' => 250]]])->assertSessionHasErrors('qty');
        $this->assertSame(0, Delivery::count());
        $this->assertSame(5, $v->fresh()->stock_qty);
    }

    // ---------------------------------------------------------------- invoices, settings, access

    public function test_invoice_prints_for_managers_and_via_signed_links_only(): void
    {
        $m = $this->manager();
        $p = $this->jeans();
        Setting::set('business_name', 'Drezwel Fashion');
        $v = $p->variants->first();
        $this->actingAs($m)->post(route('manager.pos.store'), ['type' => 'counter', 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 250]], 'received' => 250, 'name' => 'Ama', 'phone' => '0244000111']);
        $d = Delivery::first();

        $this->actingAs($m)->get(route('manager.deliveries.invoice', $d))->assertOk()
            ->assertSee('Drezwel Fashion - Invoice')->assertSee($d->reference)->assertSee('Baggy jeans')->assertSee('Size 32 · Blue')->assertSee('TOTAL GHS')->assertSee('250.00')->assertSee('Fully paid')
            // details carried over from the old paper invoice
            ->assertSee('Dealers in fashion and fashion accessories')->assertSee('Lascala Bus Stop')->assertSee('drezwelgh@gmail.com')
            ->assertSee('Goods sold are not returnable')->assertSee('THANK YOU FOR CHOOSING DREZWEL')->assertSee("Customer's Signature", false)->assertSee("Manager's Signature", false);
        $this->actingAs($m)->get(route('manager.deliveries.invoice', [$d, 'format' => 'receipt']))->assertOk()->assertSee('Receipt');

        $this->app['auth']->forgetGuards();
        $this->get(route('manager.deliveries.invoice', $d))->assertRedirect('/login');
        $this->get('/invoice/' . $d->reference)->assertForbidden();                                   // unsigned
        $this->get(URL::temporarySignedRoute('invoice.public', now()->addDay(), ['reference' => $d->reference]))->assertOk()->assertSee($d->reference);
        $this->get(URL::temporarySignedRoute('invoice.public', now()->subMinute(), ['reference' => $d->reference]))->assertForbidden(); // expired
    }

    public function test_phone_setting_with_two_numbers_dials_the_first(): void
    {
        $this->assertSame('0200680701', Setting::phoneLink());
        $this->get('/shop')->assertSee('0200680701/0203264474')->assertSee('tel:0200680701', false);
    }

    public function test_tracking_page_links_to_a_signed_invoice(): void
    {
        $c = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $d = Delivery::create(['reference' => 'DEL-X-1', 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => 'Dress', 'delivery_date' => today(), 'total_amount' => 100, 'items_sent' => 1]);
        $this->get('/track?ref=DEL-X-1&phone=0244000111')->assertOk()->assertSee('Print invoice')->assertSee('signature=', false);
    }

    public function test_settings_are_managed_and_riders_cannot_use_pos_or_settings(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->put(route('manager.settings.update'), [
            'business_name' => 'Drezwel', 'phone' => '0241234567', 'website' => 'https://drezwell.com', 'default_delivery_fee' => 15, 'email' => 'hi@drezwell.com',
        ])->assertSessionHasNoErrors();
        $this->assertSame('15', Setting::get('default_delivery_fee'));
        $this->assertSame('0', Setting::get('store_open')); // unticked = paused
        $this->get('/shop')->assertSee('0241234567')->assertSee('drezwell.com');

        foreach (['manager.pos', 'manager.settings'] as $r) {
            $this->actingAs($m)->get(route($r))->assertOk();
        }
        $rider = User::factory()->create(['role' => 'rider']);
        foreach (['manager.pos', 'manager.settings', 'manager.products.index'] as $r) {
            $this->actingAs($rider)->get(route($r))->assertForbidden();
        }
        $this->actingAs($rider)->post(route('manager.pos.store'), [])->assertForbidden();
    }

    public function test_cancelling_an_online_order_returns_stock_to_the_right_size(): void
    {
        Http::fake();
        $m = $this->manager();
        $p = $this->jeans();
        $v = $p->variants->firstWhere(fn ($x) => $x->size === '34' && $x->color === 'Blue');
        $this->post('/cart/add', ['variant_id' => $v->id, 'qty' => 3]);
        $this->post('/checkout', ['name' => 'Ama', 'phone' => '0244000111', 'location' => 'Osu']);
        $this->assertSame(2, $v->fresh()->stock_qty);

        $this->actingAs($m)->post(route('manager.deliveries.cancel', Delivery::first()));
        $this->assertSame(5, $v->fresh()->stock_qty);
        $this->assertSame(20, $p->fresh()->stock_qty);
    }

    public function test_adding_from_a_tile_shows_a_view_cart_message_and_product_page_suggests_other_categories(): void
    {
        $jeans = $this->jeans();
        $cap = Product::create(['name' => 'Plain cap', 'slug' => 'plain-cap', 'category' => 'Caps', 'cost_price' => 10, 'selling_price' => 30, 'show_in_store' => true, 'stock_qty' => 3]);
        $cap->variants()->create(['stock_qty' => 3]);

        $this->post(route('shop.cart.add'), ['variant_id' => $jeans->variants->first()->id, 'stay' => 1])->assertSessionHas('cart_added');
        $this->get(route('shop.index'))->assertSee('added to your cart')->assertSee('View cart');

        // the only jeans has no same-category siblings, yet other categories are suggested
        $this->get(route('shop.product', 'baggy-jeans'))->assertOk()->assertSee('You may also like')->assertSee('Plain cap');
    }

    public function test_product_page_fills_the_suggestions_row_and_photos_open_full_size(): void
    {
        $main = $this->jeans();
        $main->images()->create(['path' => 'products/a.jpg', 'sort_order' => 0]);
        foreach (range(1, 3) as $n) {                      // a small shop: 2 in stock, 1 sold out, all in other categories
            $p = Product::create(['name' => "Other {$n}", 'slug' => "other-{$n}", 'category' => "Cat {$n}", 'cost_price' => 10, 'selling_price' => 30, 'show_in_store' => true, 'stock_qty' => $n === 3 ? 0 : 4]);
            $p->variants()->create(['stock_qty' => $n === 3 ? 0 : 4]);
        }

        $this->get(route('shop.product', 'baggy-jeans'))->assertOk()
            ->assertSee('Other 1')->assertSee('Other 2')->assertSee('Other 3')      // three suggestions, not a lone card
            ->assertSee('id="lightbox"', false)->assertSee('id="zoomBtn"', false);
    }

    public function test_product_page_has_one_stock_counter_that_follows_the_selection_not_a_count_on_every_size(): void
    {
        $p = $this->jeans();
        $html = $this->get(route('shop.product', $p->slug))->assertOk()->assertSee('id="stockNote"', false)->assertSee('"stock":5', false)->getContent();
        $this->assertStringNotContainsString('class="left block', $html);          // no "N left" line under each size / colour
        $this->assertStringContainsString("Few units left: only ", $html);          // the single counter wording
        $this->assertStringNotContainsString('unitsLeft', $html);
    }

    public function test_manager_picks_todays_deals_regardless_of_price_reductions(): void
    {
        $m = $this->manager();
        $shirt = Product::create(['name' => 'Plain shirt', 'slug' => 'plain-shirt', 'category' => 'Shirts', 'cost_price' => 50, 'selling_price' => 120, 'stock_qty' => 4, 'show_in_store' => true]);
        $shirt->variants()->create(['stock_qty' => 4]);
        $sale = Product::create(['name' => 'Reduced tee', 'slug' => 'reduced-tee', 'category' => 'Shirts', 'cost_price' => 20, 'selling_price' => 60, 'compare_at_price' => 90, 'stock_qty' => 4, 'show_in_store' => true]);
        $sale->variants()->create(['stock_qty' => 4]);

        // a reduced price alone no longer puts a product in the deals row
        $this->get('/shop?deals=1')->assertDontSee('Reduced tee')->assertDontSee('Plain shirt');

        // one click in the product list adds a product that has NO price reduction
        $this->actingAs($m)->post(route('manager.products.deal', $shirt))->assertSessionHas('success');
        $this->assertTrue($shirt->fresh()->is_deal);
        $this->flushSession();
        $this->get('/shop?deals=1')->assertSee('Plain shirt')->assertDontSee('Reduced tee');
        $this->get('/shop')->assertSee("Today's deals")->assertSee('Plain shirt');

        // the tick box on the product form does the same, and un-ticking removes it
        $this->actingAs($m)->get(route('manager.products.index'))->assertSee(route('manager.products.deal', $shirt), false);
        $this->actingAs($m)->get(route('manager.products.edit', $sale))->assertOk()->assertSee("Feature in Today's deals", false);
        $this->actingAs($m)->post(route('manager.products.deal', $shirt));
        $this->assertFalse($shirt->fresh()->is_deal);
        $this->flushSession();                                              // drop the "removed from Today's deals" confirmation
        $this->get('/shop?deals=1')->assertDontSee('Plain shirt');

        $this->app['auth']->forgetGuards();
        $this->post(route('manager.products.deal', $shirt))->assertRedirect('/login');
    }

    public function test_a_product_with_different_variant_prices_shows_its_main_price_not_a_range(): void
    {
        $p = Product::create(['name' => 'Two tone jeans', 'slug' => 'two-tone-jeans', 'category' => 'Jeans', 'cost_price' => 100, 'selling_price' => 250, 'compare_at_price' => 280, 'show_in_store' => true, 'stock_qty' => 10]);
        $p->variants()->create(['size' => '32', 'color' => 'Blue', 'stock_qty' => 5]);                       // uses the main price, 250
        $p->variants()->create(['size' => '32', 'color' => 'Black', 'price' => 220, 'stock_qty' => 5]);      // its own, lower price

        $html = $this->get(route('shop.product', $p->slug))->assertOk()->assertSee('GH₵ 250.00')->getContent();
        $this->assertStringNotContainsString('250.00 –', $html);
        $this->assertStringNotContainsString('220.00 –', $html);
        $this->assertStringContainsString('"price":220', $html);                                            // the black price is still used once black is chosen
        $this->get('/shop')->assertSee('GH₵ 250.00')->assertDontSee('GH₵ 220.00+');                       // cards show the main price too
        $this->assertSame(11, $p->fresh('variants')->discount_percent);                                      // 280 -> 250
    }
}
