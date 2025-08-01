<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class CourseTest extends TestCase
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
    public function it_can_list_all_courses()
    {
        // Create some test courses
        Course::factory()->count(3)->create();

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
                ->assertJsonCount(3)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'duration',
                        'created_at',
                        'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_a_new_course()
    {
        $courseData = [
            'name' => 'Laravel Development',
            'description' => 'Learn Laravel framework from scratch',
            'duration' => 30
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Laravel Development',
                    'description' => 'Learn Laravel framework from scratch',
                    'duration' => 30
                ]);

        $this->assertDatabaseHas('courses', $courseData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_course()
    {
        $response = $this->postJson('/api/courses', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'duration']);
    }

    /** @test */
    public function it_validates_duration_must_be_positive_integer()
    {
        $courseData = [
            'name' => 'Test Course',
            'duration' => -5
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['duration']);
    }

    /** @test */
    public function it_can_show_a_specific_course()
    {
        $course = Course::factory()->create([
            'name' => 'PHP Basics',
            'description' => 'Introduction to PHP programming',
            'duration' => 15
        ]);

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $course->id,
                    'name' => 'PHP Basics',
                    'description' => 'Introduction to PHP programming',
                    'duration' => 15
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_course()
    {
        $response = $this->getJson('/api/courses/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Course not found']);
    }

    /** @test */
    public function it_can_update_a_course()
    {
        $course = Course::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old description',
            'duration' => 10
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'duration' => 20
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'name' => 'Updated Name',
                    'description' => 'Updated description',
                    'duration' => 20
                ]);

        $this->assertDatabaseHas('courses', $updateData);
    }

    /** @test */
    public function it_can_partially_update_a_course()
    {
        $course = Course::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original description',
            'duration' => 10
        ]);

        $updateData = [
            'name' => 'Partially Updated Name'
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'name' => 'Partially Updated Name',
                    'description' => 'Original description',
                    'duration' => 10
                ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_course()
    {
        $updateData = [
            'name' => 'Updated Name'
        ];

        $response = $this->putJson('/api/courses/999', $updateData);

        $response->assertStatus(404)
                ->assertJson(['message' => 'Course not found']);
    }

    /** @test */
    public function it_can_delete_a_course()
    {
        $course = Course::factory()->create();

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Course deleted successfully']);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_course()
    {
        $response = $this->deleteJson('/api/courses/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Course not found']);
    }

    /** @test */
    public function it_validates_string_length_for_course_name()
    {
        $courseData = [
            'name' => str_repeat('a', 300), // Exceeds 255 characters
            'duration' => 10
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_accepts_null_description()
    {
        $courseData = [
            'name' => 'Course Without Description',
            'description' => null,
            'duration' => 15
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Course Without Description',
                    'description' => null,
                    'duration' => 15
                ]);
    }
} 