<?php

namespace Tests\Unit;

use App\Models\Voucher;
use App\Models\Treatment;
use App\Models\Customer;
use App\Models\Sales;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_has_string_key()
    {
        $voucher = new Voucher();

        $this->assertFalse($voucher->incrementing);
        $this->assertEquals('string', $voucher->getKeyType());
    }

    public function test_voucher_can_be_created_with_uuid()
    {
        $voucher = Voucher::factory()->create();

        $this->assertDatabaseHas('voucher', [
            'id' => $voucher->id,
        ]);
        $this->assertIsString($voucher->id);
    }

    public function test_voucher_belongs_to_treatment()
    {
        $treatment = Treatment::factory()->create();
        $voucher = Voucher::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertInstanceOf(Treatment::class, $voucher->treatment);
        $this->assertTrue($voucher->treatment->is($treatment));
    }

    public function test_voucher_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $voucher = Voucher::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $voucher->customer);
        $this->assertTrue($voucher->customer->is($customer));
    }

    public function test_voucher_belongs_to_session()
    {
        $session = Session::factory()->create();
        $voucher = Voucher::factory()->create(['session_id' => $session->id]);

        $this->assertInstanceOf(Session::class, $voucher->session);
        $this->assertTrue($voucher->session->is($session));
    }
}
