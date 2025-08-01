<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'start_date', 'end_date', 'location'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_training')
            ->withPivot('status')
            ->withTimestamps();
    }
}
