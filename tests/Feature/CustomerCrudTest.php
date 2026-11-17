<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function sale(Customer $c, ProductVariant $v, int $qty = 2, array $o = []): Delivery
    {
        return app(SalesService::class)->placeOrder(['name' => $c->name, 'phone' => $c->phone, 'location' => 'Osu'], [['variant_id' => $v->id, 'qty' => $qty]],
            $o + ['source' => 'phone', 'status' => 'pending']);
    }

    private function variant(): ProductVariant
    {
        $p = Product::create(['name' => 'Jeans', 'slug' => 'jeans', 'cost_price' => 60, 'selling_price' => 100, 'stock_qty' => 10]);

        return $p->variants()->create(['stock_qty' => 10]);
    }

    public function test_manager_adds_a_customer_and_the_phone_is_cleaned_and_unique(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.customers.store'), ['name' => 'Ama Boateng', 'phone' => '+233 24 123 4567', 'location' => 'Madina', 'notes' => 'Wholesale'])->assertRedirect();

        $c = Customer::where('name', 'Ama Boateng')->firstOrFail();
        $this->assertSame('0241234567', $c->phone);

        $this->actingAs($m)->post(route('manager.customers.store'), ['name' => 'Copy', 'phone' => '0241234567'])->assertSessionHasErrors('phone');
        $this->actingAs($m)->post(route('manager.customers.store'), ['name' => 'Bad', 'phone' => '12345'])->assertSessionHasErrors('phone');
        $this->actingAs($m)->post(route('manager.customers.store'), ['name' => 'Walk', 'phone' => SalesService::WALK_IN_PHONE])->assertSessionHasErrors('phone');
        $this->actingAs($m)->post(route('manager.customers.store'), ['name' => '', 'phone' => '0201112222'])->assertSessionHasErrors('name');
        $this->assertSame(1, Customer::count());
    }

    public function test_manager_edits_name_phone_and_notes_but_cannot_clash_with_another_customer(): void
    {
        $m = $this->manager();
        $a = Customer::create(['name' => 'A', 'phone' => '0241111111']);
        Customer::create(['name' => 'B', 'phone' => '0242222222']);

        $this->actingAs($m)->put(route('manager.customers.update', $a), ['name' => 'Aba', 'phone' => '0243333333', 'location' => 'Tema', 'notes' => 'VIP'])->assertSessionHas('success');
        $a->refresh();
        $this->assertSame(['Aba', '0243333333', 'VIP'], [$a->name, $a->phone, $a->notes]);

        $this->actingAs($m)->put(route('manager.customers.update', $a), ['name' => 'Aba', 'phone' => '0242222222'])->assertSessionHasErrors('phone');
        $this->actingAs($m)->put(route('manager.customers.update', $a), ['name' => 'Aba Two'])->assertSessionHas('success');   // phone left alone when not sent
        $this->assertSame('0243333333', $a->fresh()->phone);
    }

    public function test_a_customer_with_no_orders_is_deleted(): void
    {
        $c = Customer::create(['name' => 'Gone', 'phone' => '0244444444']);
        $this->actingAs($this->manager())->delete(route('manager.customers.destroy', $c))->assertRedirect(route('manager.customers.index'))->assertSessionHas('success');
        $this->assertNull(Customer::find($c->id));
    }

    public function test_a_customer_with_orders_needs_the_orders_deleted_with_them(): void
    {
        $m = $this->manager();
        $v = $this->variant();
        $c = Customer::create(['name' => 'Kofi', 'phone' => '0245555555']);
        $this->sale($c, $v, 3);
        $this->assertSame(7, $v->fresh()->stock_qty);

        $this->actingAs($m)->delete(route('manager.customers.destroy', $c))->assertSessionHas('error');          // refused: has an order
        $this->assertNotNull(Customer::find($c->id));
        $this->assertSame(1, Delivery::count());

        $this->actingAs($m)->delete(route('manager.customers.destroy', $c), ['with_orders' => 1])->assertSessionHas('success');
        $this->assertNull(Customer::find($c->id));
        $this->assertSame(0, Delivery::count());
        $this->assertSame(10, $v->fresh()->stock_qty);                                                           // their items went back
    }

    public function test_nothing_is_deleted_when_one_order_cannot_be_removed(): void
    {
        $m = $this->manager();
        $v = $this->variant();
        $c = Customer::create(['name' => 'Esi', 'phone' => '0246666666']);
        $ok = $this->sale($c, $v, 1);
        $paid = $this->sale($c, $v, 1, ['source' => 'online']);
        $paid->update(['payment_method' => 'speso', 'payment_status' => 'paid']);

        $this->actingAs($m)->delete(route('manager.customers.destroy', $c), ['with_orders' => 1])->assertSessionHas('error');
        $this->assertNotNull(Customer::find($c->id));
        $this->assertNotNull(Delivery::find($ok->id));                                                           // all or nothing
        $this->assertSame(8, $v->fresh()->stock_qty);
    }

    public function test_the_walk_in_customer_is_protected(): void
    {
        $w = Customer::create(['name' => 'Walk-in customer', 'phone' => SalesService::WALK_IN_PHONE]);
        $m = $this->manager();

        $this->actingAs($m)->delete(route('manager.customers.destroy', $w))->assertSessionHas('error');
        $this->assertNotNull(Customer::find($w->id));
        $this->actingAs($m)->put(route('manager.customers.update', $w), ['name' => 'Counter', 'phone' => '0249999999'])->assertSessionHas('success');
        $this->assertSame(SalesService::WALK_IN_PHONE, $w->fresh()->phone);                                      // phone cannot be changed
    }

    public function test_pages_show_the_add_form_edit_and_delete_controls_and_only_managers_can_use_them(): void
    {
        $m = $this->manager();
        $c = Customer::create(['name' => 'Yaw', 'phone' => '0247777777']);

        $this->actingAs($m)->get(route('manager.customers.index'))->assertOk()->assertSee('Add a customer')->assertSee(route('manager.customers.destroy', $c), false);
        $this->actingAs($m)->get(route('manager.customers.show', $c))->assertOk()->assertSee('Delete customer')->assertSee('name="phone"', false);

        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->post(route('manager.customers.store'), ['name' => 'X', 'phone' => '0248888888'])->assertForbidden();
        $this->actingAs($rider)->delete(route('manager.customers.destroy', $c))->assertForbidden();
        $this->assertNotNull(Customer::find($c->id));
    }
}
