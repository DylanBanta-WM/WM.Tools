<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChromebookInventory extends Model
{
    protected $table = 'chromebook_inventory';

    protected $fillable = [
        'serial_number',
        'asset_id',
    ];

    /**
     * Get all usage records for this chromebook
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(ChromebookUsage::class, 'serial_number', 'serial_number');
    }

    /**
     * Get the most recent usage record
     */
    public function latestUsage(): HasMany
    {
        return $this->hasMany(ChromebookUsage::class, 'serial_number', 'serial_number')
                    ->latest('recorded_at')
                    ->limit(1);
    }
}
