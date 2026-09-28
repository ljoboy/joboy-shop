<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'badge_color',
        'whatsapp_template',
        'is_default',
        'position',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
