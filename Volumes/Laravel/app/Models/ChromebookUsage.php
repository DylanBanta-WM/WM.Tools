<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChromebookUsage extends Model
{
    protected $table = 'chromebook_usage';

    protected $fillable = [
        'serial_number',
        'asset_id',
        'user_email',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the chromebook inventory record
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(ChromebookInventory::class, 'serial_number', 'serial_number');
    }
}
