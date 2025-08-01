<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TrainingSchedule;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TrainingScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    /** @test */
    public function it_can_list_all_training_schedules()
    {
        // Create some test schedules with courses
        TrainingSchedule::factory()->count(3)->create();

        $response = $this->getJson('/api/training-schedules');

        $response->assertStatus(200)
                ->assertJsonCount(3)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'course_id',
                        'start_date',
                        'end_date',
                        'location',
                        'created_at',
                        'updated_at',
                        'course' => [
                            'id',
                            'name',
                            'description',
                            'duration'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_a_new_training_schedule()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Main Training Center'
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(201)
                ->assertJson([
                    'course_id' => $course->id,
                    'start_date' => '2024-01-15',
                    'end_date' => '2024-01-30',
                    'location' => 'Main Training Center'
                ]);

        $this->assertDatabaseHas('training_schedules', $scheduleData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_schedule()
    {
        $response = $this->postJson('/api/training-schedules', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['course_id', 'start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_course_exists()
    {
        $scheduleData = [
            'course_id' => 999, // Non-existent course
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30'
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['course_id']);
    }

    /** @test */
    public function it_validates_start_date_before_end_date()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => '2024-01-30',
            'end_date' => '2024-01-15' // End date before start date
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_validates_location_max_length()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => str_repeat('a', 300) // Exceeds 255 characters
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['location']);
    }

    /** @test */
    public function it_accepts_null_location()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => null
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('training_schedules', [
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => null
        ]);
    }

    /** @test */
    public function it_can_show_a_specific_training_schedule()
    {
        $course = Course::factory()->create([
            'name' => 'Laravel Development',
            'description' => 'Learn Laravel framework'
        ]);
        
        $schedule = TrainingSchedule::factory()->create([
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Main Training Center'
        ]);

        $response = $this->getJson("/api/training-schedules/{$schedule->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $schedule->id,
                    'course_id' => $course->id,
                    'start_date' => '2024-01-15',
                    'end_date' => '2024-01-30',
                    'location' => 'Main Training Center'
                ])
                ->assertJsonStructure([
                    'course' => [
                        'id',
                        'name',
                        'description',
                        'duration'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_training_schedule()
    {
        $response = $this->getJson('/api/training-schedules/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Training Schedule not found']);
    }

    /** @test */
    public function it_can_update_a_training_schedule()
    {
        $course = Course::factory()->create();
        $schedule = TrainingSchedule::factory()->create([
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Old Location'
        ]);

        $updateData = [
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-15',
            'location' => 'New Location'
        ];

        $response = $this->putJson("/api/training-schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'start_date' => '2024-02-01',
                    'end_date' => '2024-02-15',
                    'location' => 'New Location'
                ]);

        $this->assertDatabaseHas('training_schedules', [
            'id' => $schedule->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-15',
            'location' => 'New Location'
        ]);
    }

    /** @test */
    public function it_can_partially_update_a_training_schedule()
    {
        $schedule = TrainingSchedule::factory()->create([
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Original Location'
        ]);

        $updateData = [
            'location' => 'Updated Location'
        ];

        $response = $this->putJson("/api/training-schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'start_date' => '2024-01-15',
                    'end_date' => '2024-01-30',
                    'location' => 'Updated Location'
                ]);
    }

    /** @test */
    public function it_validates_date_logic_when_updating()
    {
        $schedule = TrainingSchedule::factory()->create([
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30'
        ]);

        $updateData = [
            'start_date' => '2024-02-01',
            'end_date' => '2024-01-15' // End date before start date
        ];

        $response = $this->putJson("/api/training-schedules/{$schedule->id}", $updateData);

        $response->assertStatus(422)
                ->assertJson(['message' => 'start_date must be before or equal to end_date']);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_schedule()
    {
        $updateData = [
            'location' => 'Updated Location'
        ];

        $response = $this->putJson('/api/training-schedules/999', $updateData);

        $response->assertStatus(404)
                ->assertJson(['message' => 'Training Schedule not found']);
    }

    /** @test */
    public function it_can_delete_a_training_schedule()
    {
        $schedule = TrainingSchedule::factory()->create();

        $response = $this->deleteJson("/api/training-schedules/{$schedule->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Training Schedule deleted successfully']);

        $this->assertDatabaseMissing('training_schedules', ['id' => $schedule->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_schedule()
    {
        $response = $this->deleteJson('/api/training-schedules/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Training Schedule not found']);
    }

    /** @test */
    public function it_validates_date_format()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-30'
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_allows_same_start_and_end_date()
    {
        $course = Course::factory()->create();
        
        $scheduleData = [
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-15' // Same date
        ];

        $response = $this->postJson('/api/training-schedules', $scheduleData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('training_schedules', $scheduleData);
    }
} 