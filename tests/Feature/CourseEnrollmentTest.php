<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    /** Automatická kontrola: vytvoří kurz a ověří, že se uložil. */
    public function test_course_is_created_and_persisted(): void
    {
        $response = $this->post('/courses', [
            'name' => 'Úvod do Laravelu',
            'code' => 'LRV-101',
            'capacity' => 12,
            'starts_at' => '2030-01-15 09:00:00',
        ]);

        $course = Course::where('code', 'LRV-101')->first();

        $this->assertNotNull($course, 'Kurz se nepodařilo uložit do databáze.');
        $response->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseHas('courses', [
            'name' => 'Úvod do Laravelu',
            'code' => 'LRV-101',
            'capacity' => 12,
        ]);
        $this->assertSame(12, $course->capacity);
        $this->assertSame('2030-01-15 09:00', $course->starts_at->format('Y-m-d H:i'));
    }

    public function test_course_code_must_be_unique(): void
    {
        Course::factory()->create(['code' => 'DUP-1']);

        $this->post('/courses', [
            'name' => 'Další', 'code' => 'DUP-1', 'capacity' => 5, 'starts_at' => '2030-01-01 10:00',
        ])->assertSessionHasErrors('code');

        $this->assertSame(1, Course::count());
    }

    public function test_capacity_must_be_positive_integer(): void
    {
        foreach (['0', '-3', '2.5', 'abc', ''] as $bad) {
            $this->post('/courses', [
                'name' => 'X', 'code' => 'C-'.md5($bad), 'capacity' => $bad, 'starts_at' => '2030-01-01 10:00',
            ])->assertSessionHasErrors('capacity');
        }

        $this->assertSame(0, Course::count());
    }

    public function test_participant_email_must_be_valid_and_unique(): void
    {
        $this->post('/participants', ['name' => 'Jan', 'email' => 'neni-email'])
            ->assertSessionHasErrors('email');

        $this->post('/participants', ['name' => 'Jan', 'email' => 'jan@example.com'])
            ->assertSessionHasNoErrors();

        $this->post('/participants', ['name' => 'Jiný Jan', 'email' => 'jan@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, Participant::count());
    }

    public function test_participant_can_enroll_into_course(): void
    {
        $course = Course::factory()->create(['capacity' => 2]);
        $participant = Participant::factory()->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'participant_id' => $participant->id,
            'status' => 'enrolled',
        ]);
        $this->assertSame(1, $course->occupiedCount());
        $this->assertSame(1, $course->freeSeats());
    }

    public function test_participant_cannot_enroll_twice(): void
    {
        $course = Course::factory()->create(['capacity' => 5]);
        $participant = Participant::factory()->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasErrors('participant_id');

        $this->assertSame(1, Enrollment::count());
    }

    public function test_capacity_cannot_be_exceeded(): void
    {
        $course = Course::factory()->create(['capacity' => 2]);
        [$a, $b, $c] = Participant::factory()->count(3)->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $a->id]);
        $this->post(route('enrollments.store', $course), ['participant_id' => $b->id]);
        $this->post(route('enrollments.store', $course), ['participant_id' => $c->id])
            ->assertSessionHasErrors('course');

        $this->assertSame(2, $course->occupiedCount());
        $this->assertTrue($course->isFull());
        $this->assertDatabaseMissing('enrollments', ['participant_id' => $c->id]);
    }

    public function test_cancelled_enrollment_frees_seat_and_can_be_restored(): void
    {
        $course = Course::factory()->create(['capacity' => 1]);
        [$a, $b] = Participant::factory()->count(2)->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $a->id]);
        $enrollmentA = Enrollment::first();

        $this->patch(route('enrollments.status', $enrollmentA), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();
        $this->assertSame(EnrollmentStatus::Cancelled, $enrollmentA->fresh()->status);
        $this->assertSame(1, $course->freeSeats());

        // Uvolněné místo obsadí někdo jiný ...
        $this->post(route('enrollments.store', $course), ['participant_id' => $b->id])
            ->assertSessionHasNoErrors();

        // ... takže původní přihlášku už obnovit nejde (kapacita).
        $this->patch(route('enrollments.status', $enrollmentA), ['status' => 'enrolled'])
            ->assertSessionHasErrors('course');
        $this->assertSame(EnrollmentStatus::Cancelled, $enrollmentA->fresh()->status);
    }

    public function test_cancelled_participant_can_enroll_again_without_duplicate_row(): void
    {
        $course = Course::factory()->create(['capacity' => 3]);
        $participant = Participant::factory()->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);
        $this->patch(route('enrollments.status', Enrollment::first()), ['status' => 'cancelled']);
        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Enrollment::count());
        $this->assertSame(EnrollmentStatus::Enrolled, Enrollment::first()->status);
    }

    public function test_completed_enrollment_is_final(): void
    {
        $course = Course::factory()->create(['capacity' => 3]);
        $participant = Participant::factory()->create();

        $this->post(route('enrollments.store', $course), ['participant_id' => $participant->id]);
        $enrollment = Enrollment::first();

        $this->patch(route('enrollments.status', $enrollment), ['status' => 'completed'])
            ->assertSessionHasNoErrors();
        $this->patch(route('enrollments.status', $enrollment), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');

        $this->assertSame(EnrollmentStatus::Completed, $enrollment->fresh()->status);
    }

    public function test_capacity_cannot_be_reduced_below_occupied_seats(): void
    {
        $course = Course::factory()->create(['capacity' => 3]);
        Participant::factory()->count(2)->create()->each(
            fn ($p) => $this->post(route('enrollments.store', $course), ['participant_id' => $p->id])
        );

        $this->put(route('courses.update', $course), [
            'name' => $course->name, 'code' => $course->code, 'capacity' => 1,
            'starts_at' => $course->starts_at->format('Y-m-d H:i'),
        ])->assertSessionHasErrors('capacity');
    }

    public function test_courses_can_be_filtered_by_date_and_free_seats(): void
    {
        $soonFree = Course::factory()->create(['code' => 'A-1', 'capacity' => 2, 'starts_at' => '2030-03-10 10:00']);
        $soonFull = Course::factory()->create(['code' => 'B-1', 'capacity' => 1, 'starts_at' => '2030-03-12 10:00']);
        $late = Course::factory()->create(['code' => 'C-1', 'capacity' => 5, 'starts_at' => '2030-09-01 10:00']);

        $p = Participant::factory()->create();
        $this->post(route('enrollments.store', $soonFull), ['participant_id' => $p->id]);

        // termín
        $this->get('/courses?from=2030-03-01&to=2030-03-31')
            ->assertSee('A-1')->assertSee('B-1')->assertDontSee('C-1');

        // volná místa
        $this->get('/courses?only_free=1')
            ->assertSee('A-1')->assertSee('C-1')->assertDontSee('B-1');

        // kombinace
        $this->get('/courses?from=2030-03-01&to=2030-03-31&only_free=1')
            ->assertSee('A-1')->assertDontSee('B-1')->assertDontSee('C-1');
    }
}
