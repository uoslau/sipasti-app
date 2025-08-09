<?php

if (!function_exists('parseNominal')) {
    function parseNominal($value)
    {
        return $value !== null ? (int) str_replace('.', '', $value) : null;
    }
}

if (!function_exists('formatNominal')) {
    function formatNominal($value, $withRp = true)
    {
        if (!is_numeric($value)) {
            return $withRp ? 'Rp 0' : '0';
        }

        $formatted = number_format($value, 0, ',', '.');

        return $withRp ? 'Rp ' . $formatted : $formatted;
    }
}
