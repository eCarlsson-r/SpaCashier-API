<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';
    public $timestamps = false;
    
    protected $fillable = [
        'journal_reference',
        'date',
        'from_wallet_id',
        'to_wallet_id',
        'amount',
        'description'
    ];

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_reference', 'reference');
    }
}
