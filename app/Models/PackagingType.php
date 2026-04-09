<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function conversionsFrom()
    {
        return $this->hasMany(PackagingConversion::class, 'from_packaging_type_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(PackagingConversion::class, 'to_packaging_type_id');
    }
}
