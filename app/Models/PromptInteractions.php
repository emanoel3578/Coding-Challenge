<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromptInteractions extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'user_id'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UserScope());
    }

    public function promptInteractionsHistory(): HasMany
    {
        return $this->hasMany(PromptInteractionsHistory::class, 'prompt_interaction_id', 'id');
    }
}
