<?php

namespace App\Models;

use Database\Factories\FinanceEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceEntry extends Model
{
    /** @use HasFactory<FinanceEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'type',
        'category',
        'description',
        'amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'amount' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
