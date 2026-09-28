<?php 
namespace App\Helpers;

use App\Models\Status;

if (!function_exists('getStatusId')) {
    function getStatusId(string $code, string $typeCode)
    {
        $status = Status::where('slug', $code)
            ->whereHas('statusType', function ($query) use ($typeCode) {
                $query->where('slug', $typeCode);
            })
            ->first();

        if ($status) {
            return $status->id;
        } else {
            throw new \Exception("Status with slug {$code} for {$typeCode} not found.");
        }
    }
}
