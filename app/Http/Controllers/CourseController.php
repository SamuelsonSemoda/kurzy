<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'only_free' => ['nullable', 'boolean'],
        ], [
            'from.date' => 'Datum „od“ není platné.',
            'to.date' => 'Datum „do“ není platné.',
            'to.after_or_equal' => 'Datum „do“ nesmí být dřív než datum „od“.',
        ]);

        $courses = Course::query()
            ->withOccupied()
            ->filter($filters)
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('courses.index', compact('courses', 'filters'));
    }

    public function create(): View
    {
        return view('courses.create', ['course' => new Course(['capacity' => 10])]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $course = Course::create($request->validated());

        return redirect()->route('courses.show', $course)->with('status', 'Kurz byl vytvořen.');
    }

    public function show(Course $course): View
    {
        $enrollments = $course->enrollments()
            ->with('participant')
            ->orderByRaw("case status when 'enrolled' then 0 when 'completed' then 1 else 2 end")
            ->orderBy('enrolled_at')
            ->get();

        // Nabízíme všechny, kdo nemají aktivní přihlášku (zrušenou lze obnovit).
        $availableParticipants = Participant::query()
            ->whereDoesntHave('enrollments', fn ($q) => $q
                ->where('course_id', $course->id)
                ->whereIn('status', EnrollmentStatus::occupying()))
            ->orderBy('name')
            ->get();

        return view('courses.show', compact('course', 'enrollments', 'availableParticipants'));
    }

    public function edit(Course $course): View
    {
        return view('courses.edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('courses.show', $course)->with('status', 'Kurz byl upraven.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Kurz byl smazán.');
    }
}
