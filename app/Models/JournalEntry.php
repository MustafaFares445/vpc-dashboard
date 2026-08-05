<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $fillable = ['entry_date', 'reference', 'description', 'created_by'];

    protected function casts(): array
    {
        return ['entry_date' => 'date'];
    }

    public function lines(): HasMany { return $this->hasMany(JournalEntryLine::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
