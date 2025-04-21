<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'donation_request_id',
        'donor_id',
        'amount',
        'payment_method',
        'transaction_id',
        'currency',
        'status'
    ];

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
}
