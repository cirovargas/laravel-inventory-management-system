<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tax_id',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function inventoryEntries(): HasMany
    {
        return $this->hasMany(InventoryEntry::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
