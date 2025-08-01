<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\TrainingSchedule;
use App\Models\StudentTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TrainingOptTest extends TestCase
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
    public function it_can_opt_in_a_student_to_training()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Student training status updated successfully'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'student_id',
                        'training_schedule_id',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('student_training', [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ]);
    }

    /** @test */
    public function it_can_opt_out_a_student_from_training()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Student training status updated successfully'
                ]);

        $this->assertDatabaseHas('student_training', [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ]);
    }

    /** @test */
    public function it_can_update_existing_opt_status()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        // First opt-in
        $optInData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ];

        $this->postJson('/api/training-opt-in-out', $optInData);

        // Then opt-out
        $optOutData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optOutData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Student training status updated successfully'
                ]);

        $this->assertDatabaseHas('student_training', [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ]);

        // Should only have one record, not two
        $this->assertDatabaseCount('student_training', 1);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/training-opt-in-out', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['student_id', 'training_schedule_id', 'status']);
    }

    /** @test */
    public function it_validates_student_exists()
    {
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => 999, // Non-existent student
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['student_id']);
    }

    /** @test */
    public function it_validates_training_schedule_exists()
    {
        $student = Student::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => 999, // Non-existent training schedule
            'status' => 'opt-in'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['training_schedule_id']);
    }

    /** @test */
    public function it_validates_status_values()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'invalid-status'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_accepts_opt_in_status()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_accepts_opt_out_status()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_get_student_training_statuses()
    {
        $student = Student::factory()->create();
        $trainingSchedule1 = TrainingSchedule::factory()->create();
        $trainingSchedule2 = TrainingSchedule::factory()->create();

        // Create some training statuses
        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule1->id,
            'status' => 'opt-in'
        ]);

        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule2->id,
            'status' => 'opt-out'
        ]);

        $response = $this->getJson("/api/student-training-statuses?student_id={$student->id}");

        $response->assertStatus(200)
                ->assertJsonCount(2)
                ->assertJsonStructure([
                    '*' => [
                        'training_schedule_id',
                        'status'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_empty_array_for_student_with_no_statuses()
    {
        $student = Student::factory()->create();

        $response = $this->getJson("/api/student-training-statuses?student_id={$student->id}");

        $response->assertStatus(200)
                ->assertJson([]);
    }

    /** @test */
    public function it_requires_student_id_for_status_list()
    {
        $response = $this->getJson('/api/student-training-statuses');

        $response->assertStatus(400)
                ->assertJson(['message' => 'student_id is required']);
    }

    /** @test */
    public function it_returns_correct_statuses_for_specific_student()
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        // Create status for student1
        StudentTraining::create([
            'student_id' => $student1->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ]);

        // Create status for student2
        StudentTraining::create([
            'student_id' => $student2->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ]);

        // Get statuses for student1 only
        $response = $this->getJson("/api/student-training-statuses?student_id={$student1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJson([
                    [
                        'training_schedule_id' => $trainingSchedule->id,
                        'status' => 'opt-in'
                    ]
                ]);
    }

    /** @test */
    public function it_handles_multiple_statuses_for_same_student()
    {
        $student = Student::factory()->create();
        $trainingSchedule1 = TrainingSchedule::factory()->create();
        $trainingSchedule2 = TrainingSchedule::factory()->create();
        $trainingSchedule3 = TrainingSchedule::factory()->create();

        // Create multiple statuses for the same student
        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule1->id,
            'status' => 'opt-in'
        ]);

        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule2->id,
            'status' => 'opt-out'
        ]);

        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule3->id,
            'status' => 'opt-in'
        ]);

        $response = $this->getJson("/api/student-training-statuses?student_id={$student->id}");

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    /** @test */
    public function it_creates_new_record_when_opt_status_does_not_exist()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200);

        // Verify the record was created
        $this->assertDatabaseHas('student_training', [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ]);
    }

    /** @test */
    public function it_updates_existing_record_when_opt_status_exists()
    {
        $student = Student::factory()->create();
        $trainingSchedule = TrainingSchedule::factory()->create();

        // Create initial record
        StudentTraining::create([
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-in'
        ]);

        // Update the status
        $optData = [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ];

        $response = $this->postJson('/api/training-opt-in-out', $optData);

        $response->assertStatus(200);

        // Verify only one record exists and it's updated
        $this->assertDatabaseCount('student_training', 1);
        $this->assertDatabaseHas('student_training', [
            'student_id' => $student->id,
            'training_schedule_id' => $trainingSchedule->id,
            'status' => 'opt-out'
        ]);
    }
} 