<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Http\Requests\ParticipantRequest;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(): View
    {
        $participants = Participant::query()
            ->withCount([
                'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', EnrollmentStatus::Enrolled->value),
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('participants.index', compact('participants'));
    }

    public function create(): View
    {
        return view('participants.create', ['participant' => new Participant()]);
    }

    public function store(ParticipantRequest $request): RedirectResponse
    {
        Participant::create($request->validated());

        return redirect()->route('participants.index')->with('status', 'Účastník byl vytvořen.');
    }

    public function edit(Participant $participant): View
    {
        return view('participants.edit', compact('participant'));
    }

    public function update(ParticipantRequest $request, Participant $participant): RedirectResponse
    {
        $participant->update($request->validated());

        return redirect()->route('participants.index')->with('status', 'Účastník byl upraven.');
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        $participant->delete();

        return redirect()->route('participants.index')->with('status', 'Účastník byl smazán.');
    }
}
