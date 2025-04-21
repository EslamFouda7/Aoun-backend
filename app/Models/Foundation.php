<?php
namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
class Foundation extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'foundation_name',
        'type_of_foundation',
        'email',
        'phone',
        'password',
        'required_donation',
        'location',
    ];

    protected $hidden = [
        'password',
    ];
    public function donationRequests()
    {
        return $this->hasMany(DonationRequest::class);
    }
}
