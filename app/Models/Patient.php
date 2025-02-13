<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    public $timestamps = true;
    protected $fillable = ['name', 'last_name', 'email', 'age', 'gender', 'diagnosis', 'medicines', 'notes', 'user_id'];

    // Cast medicines to array when retrieving from database
    protected $casts = [
        'medicines' => 'array'
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
