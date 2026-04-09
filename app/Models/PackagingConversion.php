<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_packaging_type_id',
        'to_packaging_type_id',
        'factor',
        'notes',
    ];

    public function fromType()
    {
        return $this->belongsTo(PackagingType::class, 'from_packaging_type_id');
    }

    public function toType()
    {
        return $this->belongsTo(PackagingType::class, 'to_packaging_type_id');
    }
}
