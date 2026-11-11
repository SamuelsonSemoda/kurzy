<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function __construct(private readonly EnrollmentService $enrollments)
    {
    }

    /** Přihlášení účastníka do kurzu. */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
        ], [
            'participant_id.required' => 'Vyberte účastníka.',
            'participant_id.exists' => 'Vybraný účastník neexistuje.',
        ]);

        $participant = Participant::findOrFail($data['participant_id']);

        // Případné ValidationException (duplicita, plná kapacita) Laravel
        // převede na návrat zpět s chybovou hláškou.
        $this->enrollments->enroll($course, $participant);

        return redirect()
            ->route('courses.show', $course)
            ->with('status', $participant->name.' byl(a) přihlášen(a) do kurzu.');
    }

    /** Změna stavu přihlášky: enrolled / cancelled / completed. */
    public function updateStatus(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
        ], [
            'status.required' => 'Chybí cílový stav přihlášky.',
        ]);

        $enrollment = $this->enrollments->changeStatus($enrollment, EnrollmentStatus::from($data['status']));

        return redirect()
            ->route('courses.show', $enrollment->course_id)
            ->with('status', 'Stav přihlášky byl změněn na „'.$enrollment->status->label().'“.');
    }
}
