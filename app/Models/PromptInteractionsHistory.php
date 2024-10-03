<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromptInteractionsHistory extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'prompt_interactions_history';

    protected $fillable = [
        'user_id',
        'prompt_interaction_id',
        'content',
        'role',
        'type',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UserScope());
    }

    public function promptInteractions()
    {
        return $this->belongsTo(PromptInteractions::class, 'prompt_interactions_id', 'id');
    }
}
