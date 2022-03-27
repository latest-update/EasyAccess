<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'serial',
        'token'
    ];

    public function user () : HasOne
    {
        return $this->hasOne(User::class);
    }
}
