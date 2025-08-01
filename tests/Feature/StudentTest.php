<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\TrainingSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;

class StudentTest extends TestCase
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
    public function it_can_list_all_students()
    {
        // Create some test students with users
        Student::factory()->count(3)->create();

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
                ->assertJsonCount(3)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'user_id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'created_at',
                        'updated_at',
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_students_by_user_id()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Student::factory()->create(['user_id' => $user1->id]);
        Student::factory()->create(['user_id' => $user2->id]);

        $response = $this->getJson("/api/students?user_id={$user1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(1);
    }

    /** @test */
    public function it_can_create_a_new_student_with_user()
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890'
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User and Student created successfully'
                ])
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ],
                    'student' => [
                        'id',
                        'user_id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'student'
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_student()
    {
        $response = $this->postJson('/api/students', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'first_name', 'last_name']);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_creating_student()
    {
        // Create a user with existing email
        User::factory()->create(['email' => 'existing@example.com']);

        $studentData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_confirmation()
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_show_a_specific_student()
    {
        $student = Student::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);

        $response = $this->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $student->id,
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane@example.com'
                ])
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_student()
    {
        $response = $this->getJson('/api/students/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Student not found']);
    }

    /** @test */
    public function it_can_update_a_student()
    {
        $student = Student::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'phone' => '1234567890'
        ]);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '0987654321',
            'name' => 'Updated User Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/students/{$student->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Student and user updated successfully'
                ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '0987654321'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'name' => 'Updated User Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_can_partially_update_a_student()
    {
        $student = Student::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'phone' => '1234567890'
        ]);

        $updateData = [
            'first_name' => 'Partially Updated'
        ];

        $response = $this->putJson("/api/students/{$student->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Student and user updated successfully'
                ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Partially Updated',
            'last_name' => 'Name',
            'phone' => '1234567890'
        ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_updating_student()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $student = Student::factory()->create();

        $updateData = [
            'email' => 'existing@example.com'
        ];

        $response = $this->putJson("/api/students/{$student->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_get_student_by_user_id()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/students/user/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $student->id,
                    'user_id' => $user->id
                ]);
    }

    /** @test */
    public function it_returns_404_when_getting_student_by_nonexistent_user_id()
    {
        $response = $this->getJson('/api/students/user/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Student not found for this user']);
    }

    /** @test */
    public function it_can_create_student_for_existing_user()
    {
        $user = User::factory()->create();

        $studentData = [
            'first_name' => 'New',
            'last_name' => 'Student',
            'phone' => '1234567890'
        ];

        $response = $this->postJson("/api/students/user/{$user->id}", $studentData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Student created successfully for existing user'
                ])
                ->assertJsonStructure([
                    'student' => [
                        'id',
                        'user_id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone'
                    ]
                ]);

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'first_name' => 'New',
            'last_name' => 'Student',
            'email' => $user->email,
            'phone' => '1234567890'
        ]);
    }

    /** @test */
    public function it_prevents_creating_duplicate_student_for_user()
    {
        $user = User::factory()->create();
        Student::factory()->create(['user_id' => $user->id]);

        $studentData = [
            'first_name' => 'Duplicate',
            'last_name' => 'Student'
        ];

        $response = $this->postJson("/api/students/user/{$user->id}", $studentData);

        $response->assertStatus(409)
                ->assertJson(['message' => 'Student record already exists for this user']);
    }

    /** @test */
    public function it_returns_404_when_creating_student_for_nonexistent_user()
    {
        $studentData = [
            'first_name' => 'Test',
            'last_name' => 'Student'
        ];

        $response = $this->postJson('/api/students/user/999', $studentData);

        $response->assertStatus(404)
                ->assertJson(['message' => 'User not found']);
    }

    /** @test */
    public function it_can_delete_a_student_and_user()
    {
        $student = Student::factory()->create();

        $response = $this->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Student and user deleted successfully']);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $student->user_id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_student()
    {
        $response = $this->deleteJson('/api/students/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Student not found']);
    }

    /** @test */
    public function it_validates_phone_number_length()
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => str_repeat('1', 25) // Exceeds 20 characters
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function it_accepts_null_phone_number()
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => null
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => null
        ]);
    }
} 