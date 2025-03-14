<?php
namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
class Donor extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'preferred_donation',
        'location',
    ];

    protected $hidden = [
        'password',
    ];
}
