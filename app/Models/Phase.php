<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'order',
        'input_type',
        'assigned_user_id',
    ];

    // 🔹 Relación con el usuario asignado
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // 🔹 Relación con procesos
    public function controlProcesses()
    {
        return $this->hasMany(ControlProcess::class);
    }
}
