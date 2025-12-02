<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'image',
        'intro_key',
        'title_key',
        'subtitle_key',
        'description_key',
        'action_key',
        'action_page',
    ];

    protected $guarded = ['id'];
}
