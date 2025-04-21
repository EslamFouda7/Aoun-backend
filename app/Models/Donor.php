<?php
namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

class Donor extends Authenticatable
{
    use HasFactory, HasApiTokens;
    use Notifiable, CanResetPassword;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'preferred_donation',
        'location',
    ];

    protected $hidden = [
        'password', 'remember_token',
        ];
        public function donations()
{
    return $this->hasMany(Donation::class);
}
}
