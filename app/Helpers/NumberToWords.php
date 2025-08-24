<?php

namespace App\Helpers;

class NumberToWords
{
    public static function toWords($number)
    {
        $number = abs($number);
        $words = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";

        if ($number < 12) {
            $temp = " " . $words[$number];
        } else if ($number < 20) {
            $temp = self::toWords($number - 10) . " belas";
        } else if ($number < 100) {
            $temp = self::toWords($number / 10) . " puluh " . self::toWords($number % 10);
        } else if ($number < 200) {
            $temp = " seratus " . self::toWords($number - 100);
        } else if ($number < 1000) {
            $temp = self::toWords($number / 100) . " ratus " . self::toWords($number % 100);
        } else if ($number < 2000) {
            $temp = " seribu " . self::toWords($number - 1000);
        } else if ($number < 1000000) {
            $temp = self::toWords($number / 1000) . " ribu " . self::toWords($number % 1000);
        } else if ($number < 1000000000) {
            $temp = self::toWords($number / 1000000) . " juta " . self::toWords($number % 1000000);
        } else if ($number < 1000000000000) {
            $temp = self::toWords($number / 1000000000) . " miliar " . self::toWords(fmod($number, 1000000000));
        } else if ($number < 1000000000000000) {
            $temp = self::toWords($number / 1000000000000) . " triliun " . self::toWords(fmod($number, 1000000000000));
        }

        return trim($temp);
    }

    public static function monthName($month)
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return $months[$month];
    }

    public static function dayName($day)
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $days[$day];
    }
}
