<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'grades';
    public $timestamps = false;
    
    protected $fillable = [
        'grade',
        'start_date',
        'end_date'
    ];

    protected $guarded = [
        'id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
