<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Publication;
use App\Models\Like;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'id',
        'name',
        'username',
        'email',
        'password',
        'profile_img',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function profileImage() {
        if($this->profile_img) {
            return asset('storage/' . $this->profile_img);
        }

        return asset('storage/images/default-profile_img.png');
    }

    // Um usuário (fotógrafo) pode ter várias publicações
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    // Um usuário pode dar várias curtidas
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }


}
