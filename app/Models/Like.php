<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Publication;



class Like extends Model
{
    protected $fillable = [
        'user_id',
        'publication_id',
    ];

    // A curtida pertence a um usuário
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A curtida pertence a uma publicação
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}