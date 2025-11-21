<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintImage extends Model
{
    protected $table = 'complaints_images';

    protected $fillable = ['complaint_id', 'path', 'position'];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
