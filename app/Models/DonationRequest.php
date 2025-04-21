<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'foundation_id',
        'title',
        'description',
        'location',
        'reqiured_donation',
        'required_amount',
        'file_path',
    ];

    // العلاقة مع المؤسسة
    public function foundation()
    {
        return $this->belongsTo(Foundation::class);
    }
    public function donations()
{
    return $this->hasMany(Donation::class);
}


}