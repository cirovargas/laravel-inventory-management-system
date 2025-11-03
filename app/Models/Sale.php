<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'sale_number',
        'total_amount',
        'total_cost',
        'total_profit',
        'status',
        'sale_date',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'sale_date' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryEntries(): HasMany
    {
        return $this->hasMany(InventoryEntry::class);
    }
}
