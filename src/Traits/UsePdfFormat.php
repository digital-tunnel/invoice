<?php

namespace DigitalTunnel\Invoice\Traits;

use NumberFormatter;

trait UsePdfFormat
{
    public function formatMoney(int $amount = null, ?string $locale = null): ?string
    {
        return $amount ? number_format($amount / 100, 2, '.', ',') : null;
    }

    public function formatPercentage(?int $percentage, ?string $locale = null): string|false|null
    {
        if (! $percentage) {
            return null;
        }

        $formatter = new NumberFormatter($locale ?? app()->getLocale(), NumberFormatter::PERCENT);

        return $formatter->format(($percentage > 1) ? ($percentage / 100) : $percentage);
    }
}
