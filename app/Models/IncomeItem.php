<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeItem extends Model
{
    protected $table = 'income_items';
    public $timestamps = false;
    
    protected $fillable = [
        'type',
        'transaction',
        'amount',
        'description'
    ];

    protected $guarded = [
        'id'
    ];

    public function income()
    {
        return $this->belongsTo(Income::class);
    }
}
