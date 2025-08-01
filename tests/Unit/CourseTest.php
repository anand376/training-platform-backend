<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Course;
use App\Models\TrainingSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_course()
    {
        $course = Course::create([
            'name' => 'Laravel Development',
            'description' => 'Learn Laravel framework',
            'duration' => 30
        ]);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Laravel Development', $course->name);
        $this->assertEquals('Learn Laravel framework', $course->description);
        $this->assertEquals(30, $course->duration);
    }

    /** @test */
    public function it_has_training_schedules_relationship()
    {
        $course = Course::create([
            'name' => 'PHP Basics',
            'description' => 'Introduction to PHP',
            'duration' => 15
        ]);

        $trainingSchedule = TrainingSchedule::create([
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Main Center'
        ]);

        $this->assertTrue($course->trainingSchedules->contains($trainingSchedule));
        $this->assertEquals(1, $course->trainingSchedules->count());
    }

    /** @test */
    public function it_can_have_multiple_training_schedules()
    {
        $course = Course::create([
            'name' => 'Web Development',
            'description' => 'Full stack web development',
            'duration' => 60
        ]);

        $schedule1 = TrainingSchedule::create([
            'course_id' => $course->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-30',
            'location' => 'Center A'
        ]);

        $schedule2 = TrainingSchedule::create([
            'course_id' => $course->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-15',
            'location' => 'Center B'
        ]);

        $this->assertEquals(2, $course->trainingSchedules->count());
        $this->assertTrue($course->trainingSchedules->contains($schedule1));
        $this->assertTrue($course->trainingSchedules->contains($schedule2));
    }

    /** @test */
    public function it_can_be_updated()
    {
        $course = Course::create([
            'name' => 'Old Name',
            'description' => 'Old description',
            'duration' => 10
        ]);

        $course->update([
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'duration' => 20
        ]);

        $this->assertEquals('Updated Name', $course->fresh()->name);
        $this->assertEquals('Updated description', $course->fresh()->description);
        $this->assertEquals(20, $course->fresh()->duration);
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $course = Course::create([
            'name' => 'Test Course',
            'description' => 'Test description',
            'duration' => 15
        ]);

        $courseId = $course->id;
        $course->delete();

        $this->assertDatabaseMissing('courses', ['id' => $courseId]);
    }

    /** @test */
    public function it_accepts_null_description()
    {
        $course = Course::create([
            'name' => 'Course Without Description',
            'description' => null,
            'duration' => 15
        ]);

        $this->assertNull($course->description);
    }

    /** @test */
    public function it_has_fillable_fields()
    {
        $course = new Course();
        $fillable = $course->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('duration', $fillable);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $course = Course::create([
            'name' => 'Test Course',
            'description' => 'Test description',
            'duration' => 15
        ]);

        $this->assertNotNull($course->created_at);
        $this->assertNotNull($course->updated_at);
    }
} 