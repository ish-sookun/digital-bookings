<?php

namespace App;

enum ReservationStatus: string
{
    case Option = 'option';
    case Confirmed = 'confirmed';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Option => 'Option',
            self::Confirmed => 'Confirmed',
            self::Canceled => 'Canceled',
        };
    }

    /**
     * Tailwind classes for the status indicator dot.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Option => 'bg-amber-500',
            self::Confirmed => 'bg-green-500',
            self::Canceled => 'bg-red-500',
        };
    }

    /**
     * Tailwind classes for highlighting the reference number on the index page.
     */
    public function referenceClasses(): string
    {
        return match ($this) {
            self::Option => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Confirmed => 'bg-green-50 text-green-700 ring-green-200',
            self::Canceled => 'bg-red-50 text-red-700 ring-red-200',
        };
    }

    /**
     * Tailwind classes for calendar reservation pills.
     */
    public function calendarClasses(): string
    {
        return match ($this) {
            self::Option => 'bg-amber-100 text-amber-800 hover:bg-amber-200',
            self::Confirmed => 'bg-green-100 text-green-800 hover:bg-green-200',
            self::Canceled => 'bg-red-100 text-red-800 hover:bg-red-200',
        };
    }
}
