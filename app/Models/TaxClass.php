<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hsn_code',
        'local_tax_code',
        'rate',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
