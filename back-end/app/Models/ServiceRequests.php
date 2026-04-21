<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Status;
use App\Models\User;
use App\Models\Services;

class ServiceRequests extends Model
{
    //
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        'request_number',
        'user_id',
        'service_id',
        'status_id',
        'full_name',
        'email',
        'phone_number',
        'address',
        'details',
        'propose_price',
        'negotiated_price',
        'validated_by',
        'rejected_by',
        'rejection_reason'
    ];
    protected $table= "services_requests";
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
