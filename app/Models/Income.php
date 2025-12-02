<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = 'incomes';
    public $timestamps = false;

    protected $fillable = [
        'journal_reference',
        'date',
        'partner',
        'partner_type',
        'description'
    ];

    protected $guarded = [
        'id'
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_reference', 'reference');
    }

    public function items()
    {
        return $this->hasMany(IncomeItem::class);
    }

    public function payments()
    {
        return $this->hasMany(IncomePayment::class);
    }
}
