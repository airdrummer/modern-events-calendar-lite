<?php

namespace MEC\Tracking;

/**
 * Small shared helpers for the tracking events.
 */
class Util
{
    /**
     * Bucket a count into the fixed vocabulary used by all *_bucket properties,
     * so no raw counts ever leave the install.
     *
     * @param int $n
     * @return string
     */
    public static function bucket($n)
    {
        $n = (int) $n;
        if ($n <= 0) return '0';
        if ($n <= 4) return '1_4';
        if ($n <= 20) return '5_20';
        if ($n <= 100) return '21_100';
        return '101_plus';
    }

    /**
     * Price bucket (used by mec_booking_completed value_bucket).
     *
     * @param float $value
     * @return string
     */
    public static function price_bucket($value)
    {
        $value = (float) $value;
        if ($value <= 0) return '0';
        if ($value < 20) return '1_19';
        if ($value < 100) return '20_99';
        if ($value < 500) return '100_499';
        if ($value < 1000) return '500_999';
        return '1000_plus';
    }
}
