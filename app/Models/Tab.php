<?php

namespace App\Models;

use Database\Factories\TabFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $database_connection_id
 * @property string $type
 * @property string $label
 * @property string|null $table_name
 * @property string|null $sql_body
 * @property int $position
 * @property bool $is_active
 */
class Tab extends Model
{
    /** @use HasFactory<TabFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'label',
        'table_name',
        'sql_body',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<DatabaseConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(DatabaseConnection::class, 'database_connection_id');
    }
}
