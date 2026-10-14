<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Like;


class Publication extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'location',
        'image_path',
    ];

    // Cada publicação pertence a um fotógrafo (usuário)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Uma publicação pode receber várias curtidas
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
}