<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
    ];

    protected $guarded = [
        'id'
    ];

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
}
