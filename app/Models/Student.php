<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'phone'];

    public function trainings()
    {
        return $this->belongsToMany(TrainingSchedule::class, 'student_training')
            ->withPivot('status')
            ->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
