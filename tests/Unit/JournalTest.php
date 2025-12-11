<?php

namespace Tests\Unit;

use App\Models\Journal;
use App\Models\JournalRecord;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Transfer;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_can_be_created()
    {
        $journal = Journal::factory()->create();

        $this->assertDatabaseHas('journals', [
            'id' => $journal->id,
            'reference' => $journal->reference,
        ]);
    }

    public function test_journal_has_many_records()
    {
        $journal = Journal::factory()->create();
        $account = Account::factory()->create();
        
        $record = JournalRecord::create([
            'journal_id' => $journal->id,
            'account_id' => $account->id,
            'debit' => 100000,
            'credit' => 0,
            'description' => 'Test record',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $journal->records);
        $this->assertTrue($journal->records->contains($record));
    }

    public function test_journal_has_one_expense()
    {
        $journal = Journal::factory()->create();
        
        $expense = Expense::factory()->create([
            'journal_reference' => $journal->reference,
        ]);

        $this->assertInstanceOf(Expense::class, $journal->expenses);
        $this->assertEquals($journal->reference, $journal->expenses->journal_reference);
    }

    public function test_journal_has_one_income()
    {
        $journal = Journal::factory()->create();
        
        $income = Income::factory()->create([
            'journal_reference' => $journal->reference,
        ]);

        $this->assertInstanceOf(Income::class, $journal->incomes);
        $this->assertEquals($journal->reference, $journal->incomes->journal_reference);
    }
}
