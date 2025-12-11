<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\Treatment;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Bed;
use App\Models\Walkin;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_has_default_status()
    {
        $session = Session::factory()->create();

        $this->assertEquals('waiting', $session->status);
    }

    public function test_session_belongs_to_treatment()
    {
        $treatment = Treatment::factory()->create();
        $session = Session::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertInstanceOf(Treatment::class, $session->treatment);
        $this->assertTrue($session->treatment->is($treatment));
    }

    public function test_session_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $session = Session::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $session->customer);
        $this->assertTrue($session->customer->is($customer));
    }

    public function test_session_belongs_to_employee()
    {
        $employee = Employee::factory()->create();
        $session = Session::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $session->employee);
        $this->assertTrue($session->employee->is($employee));
    }

    public function test_session_belongs_to_bed()
    {
        $bed = Bed::factory()->create();
        $session = Session::factory()->create(['bed_id' => $bed->id]);

        $this->assertInstanceOf(Bed::class, $session->bed);
        $this->assertTrue($session->bed->is($bed));
    }

    public function test_session_has_one_walkin()
    {
        $session = Session::factory()->create();
        $walkin = Walkin::factory()->create(['session_id' => $session->id]);

        $this->assertInstanceOf(Walkin::class, $session->walkin);
        $this->assertTrue($session->walkin->is($walkin));
    }

    public function test_session_has_one_voucher()
    {
        $session = Session::factory()->create();
        $voucher = Voucher::factory()->create(['session_id' => $session->id]);

        $this->assertInstanceOf(Voucher::class, $session->voucher);
        $this->assertTrue($session->voucher->is($voucher));
    }
}
