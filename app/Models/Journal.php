<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'journals';
    
    protected $fillable = [
        'reference',
        'date',
        'description',
    ];

    public function records()
    {
        return $this->hasMany(JournalRecord::class);
    }

    public function expenses()
    {
        return $this->hasOne(Expense::class, 'journal_reference', 'reference');
    }

    public function incomes()
    {
        return $this->hasOne(Income::class, 'journal_reference', 'reference');
    }

    public function transfers()
    {
        return $this->hasOne(Transfer::class, 'journal_reference', 'reference');
    }
}
