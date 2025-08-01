<?php

namespace Database\Factories;

use App\Models\TrainingSchedule;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingSchedule>
 */
class TrainingScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+2 months');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 months');
        
        return [
            'course_id' => Course::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'location' => $this->faker->city() . ' Training Center',
        ];
    }
} 