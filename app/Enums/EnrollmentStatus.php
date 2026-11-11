<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Přihlášen',
            self::Cancelled => 'Zrušeno',
            self::Completed => 'Dokončeno',
        };
    }

    /**
     * Stavy, které zabírají místo v kurzu (do kapacity se počítají
     * přihlášení i ti, kdo kurz dokončili; zrušené přihlášky místo uvolňují).
     *
     * @return array<int, string>
     */
    public static function occupying(): array
    {
        return [self::Enrolled->value, self::Completed->value];
    }

    /**
     * Povolené přechody mezi stavy.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Enrolled => [self::Cancelled, self::Completed],
            self::Cancelled => [self::Enrolled],
            self::Completed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
