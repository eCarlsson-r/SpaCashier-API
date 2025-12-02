<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $table = 'expense_items';
    public $timestamps = false;

    protected $fillable = [
        'amount',
        'description'
    ];

    protected $guarded = [
        'id'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
