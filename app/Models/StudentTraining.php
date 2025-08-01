<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StudentTraining extends Model
{
    use HasFactory;
    
    protected $table = 'student_training';

    protected $fillable = ['student_id', 'training_schedule_id', 'status'];
}
