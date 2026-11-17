<?php

namespace Tests\Feature;

use App\Models\OperationalCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalCostTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $superAdmin;
    }

    private function validCostPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Gas dapur',
            'category' => 'operational',
            'amount' => '150000',
            'incurred_at' => Carbon::today()->toDateString(),
            'note' => 'Isi ulang gas',
        ], $overrides);
    }

    public function test_guest_and_customer_cannot_access_cost_module(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.costs.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.costs.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.costs.create'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.costs.store'), $this->validCostPayload())->assertForbidden();
    }

    public function test_super_admin_can_create_cost(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('admin.costs.store'), $this->validCostPayload());

        $response->assertRedirect(route('admin.costs.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('operational_costs', [
            'name' => 'Gas dapur',
            'amount' => 150000,
            'recorded_by' => $superAdmin->id,
        ]);

        $this->assertSame(
            Carbon::today()->toDateString(),
            OperationalCost::query()->where('name', 'Gas dapur')->firstOrFail()->incurred_at->format('Y-m-d'),
        );
    }

    public function test_invalid_amount_is_rejected(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('admin.costs.store'), $this->validCostPayload(['amount' => 0]));

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('operational_costs', 0);

        $response = $this->actingAs($superAdmin)->post(route('admin.costs.store'), $this->validCostPayload(['amount' => -500]));

        $response->assertSessionHasErrors('amount');

        $response = $this->actingAs($superAdmin)->post(route('admin.costs.store'), $this->validCostPayload(['amount' => 'bukan-angka']));

        $response->assertSessionHasErrors('amount');
    }

    public function test_future_date_is_rejected(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('admin.costs.store'), $this->validCostPayload([
            'incurred_at' => Carbon::tomorrow()->toDateString(),
        ]));

        $response->assertSessionHasErrors('incurred_at');
        $this->assertDatabaseCount('operational_costs', 0);
    }

    public function test_super_admin_can_update_and_delete_cost(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $cost = OperationalCost::query()->create([
            'recorded_by' => $superAdmin->id,
            'name' => 'Listrik',
            'category' => 'operational',
            'amount' => 300000,
            'incurred_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($superAdmin)->put(route('admin.costs.update', $cost), $this->validCostPayload([
            'name' => 'Listrik toko',
            'amount' => '350000',
        ]));

        $response->assertRedirect(route('admin.costs.index'));
        $this->assertDatabaseHas('operational_costs', ['id' => $cost->id, 'name' => 'Listrik toko', 'amount' => 350000]);

        $response = $this->actingAs($superAdmin)->delete(route('admin.costs.destroy', $cost));

        $response->assertRedirect(route('admin.costs.index'));
        $this->assertSoftDeleted('operational_costs', ['id' => $cost->id]);
    }

    public function test_cost_appears_in_profit_report(): void
    {
        $superAdmin = $this->createSuperAdmin();

        OperationalCost::query()->create([
            'recorded_by' => $superAdmin->id,
            'name' => 'Gas dapur',
            'category' => 'operational',
            'amount' => 2500,
            'incurred_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Rp 2.500');
        $response->assertSee('Rp -2.500');
    }

    public function test_costs_can_be_filtered_by_period(): void
    {
        $superAdmin = $this->createSuperAdmin();

        OperationalCost::query()->create([
            'recorded_by' => $superAdmin->id,
            'name' => 'Biaya kemarin',
            'category' => 'operational',
            'amount' => 10000,
            'incurred_at' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.costs.index', ['period' => 'today']));

        $response->assertOk();
        $response->assertDontSee('Biaya kemarin');
        $response->assertSee('Rp 0');

        $response = $this->actingAs($superAdmin)->get(route('admin.costs.index', ['period' => 'last7']));

        $response->assertOk();
        $response->assertSee('Biaya kemarin');
    }
}
