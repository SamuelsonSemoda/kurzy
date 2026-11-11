<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Veškerá logika přihlašování a hlídání kapacity je na jednom místě.
 * Operace běží v transakci a zamyká řádek kurzu, aby dva souběžné požadavky
 * nemohly obsadit poslední volné místo současně.
 */
class EnrollmentService
{
    /**
     * Přihlásí účastníka do kurzu.
     *
     * @throws ValidationException
     */
    public function enroll(Course $course, Participant $participant): Enrollment
    {
        return DB::transaction(function () use ($course, $participant) {
            $course = Course::query()->lockForUpdate()->findOrFail($course->id);

            $existing = Enrollment::query()
                ->where('course_id', $course->id)
                ->where('participant_id', $participant->id)
                ->first();

            // Dvakrát do stejného kurzu nelze (zrušenou přihlášku lze obnovit).
            if ($existing && $existing->status !== EnrollmentStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'participant_id' => 'Tento účastník už je do kurzu přihlášen.',
                ]);
            }

            $this->assertHasFreeSeat($course);

            if ($existing) {
                $existing->update([
                    'status' => EnrollmentStatus::Enrolled,
                    'enrolled_at' => now(),
                ]);

                return $existing;
            }

            return Enrollment::create([
                'course_id' => $course->id,
                'participant_id' => $participant->id,
                'enrolled_at' => now(),
                'status' => EnrollmentStatus::Enrolled,
            ]);
        });
    }

    /**
     * Změní stav přihlášky (enrolled → cancelled / completed, cancelled → enrolled).
     *
     * @throws ValidationException
     */
    public function changeStatus(Enrollment $enrollment, EnrollmentStatus $target): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $target) {
            $course = Course::query()->lockForUpdate()->findOrFail($enrollment->course_id);
            $enrollment = Enrollment::query()->findOrFail($enrollment->id);

            if (! $enrollment->status->canTransitionTo($target)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Přihlášku ve stavu „%s“ nelze změnit na „%s“.',
                        $enrollment->status->label(),
                        $target->label()
                    ),
                ]);
            }

            // Obnovení zrušené přihlášky znovu zabírá místo → zkontrolovat kapacitu.
            if ($target === EnrollmentStatus::Enrolled) {
                $this->assertHasFreeSeat($course);
                $enrollment->enrolled_at = now();
            }

            $enrollment->status = $target;
            $enrollment->save();

            return $enrollment;
        });
    }

    private function assertHasFreeSeat(Course $course): void
    {
        if ($course->occupiedCount() >= $course->capacity) {
            throw ValidationException::withMessages([
                'course' => 'Kurz je plně obsazen, kapacita je vyčerpána.',
            ]);
        }
    }
}
