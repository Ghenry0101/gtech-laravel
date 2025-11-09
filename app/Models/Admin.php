<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'display_name',
        'email',
        'password',
        'position',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'position' => 'string',
    ];

    /**
     * Admins table does not contain a remember_token column.
     */
    protected $rememberTokenName = null;

    public function hasPosition(string $position): bool
    {
        return $this->position === $position;
    }
}
