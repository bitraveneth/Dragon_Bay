<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'product_id',
        'price',
        'notes',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
