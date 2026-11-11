<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'capacity', 'starts_at'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'starts_at' => 'datetime',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'enrollments')
            ->withPivot(['id', 'status', 'enrolled_at'])
            ->withTimestamps();
    }

    /** Počet přihlášek, které zabírají místo (bez zrušených). */
    public function occupiedCount(): int
    {
        if (array_key_exists('occupied_count', $this->attributes)) {
            return (int) $this->attributes['occupied_count'];
        }

        return $this->enrollments()
            ->whereIn('status', EnrollmentStatus::occupying())
            ->count();
    }

    public function freeSeats(): int
    {
        return max(0, $this->capacity - $this->occupiedCount());
    }

    public function isFull(): bool
    {
        return $this->freeSeats() === 0;
    }

    /** Přidá sloupec occupied_count (kvůli výpisu bez N+1 dotazů). */
    public function scopeWithOccupied(Builder $query): Builder
    {
        return $query->withCount([
            'enrollments as occupied_count' => fn (Builder $q) => $q->whereIn('status', EnrollmentStatus::occupying()),
        ]);
    }

    public function scopeWithFreeSeats(Builder $query): Builder
    {
        return $query->whereRaw(
            'courses.capacity > (select count(*) from enrollments
                where enrollments.course_id = courses.id
                and enrollments.status in (?, ?))',
            EnrollmentStatus::occupying()
        );
    }

    /** Filtr podle termínu (od / do) a volných míst. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->where('starts_at', '>=', $from.' 00:00:00'))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->where('starts_at', '<=', $to.' 23:59:59'))
            ->when(! empty($filters['only_free']), fn (Builder $q) => $q->withFreeSeats());
    }
}
