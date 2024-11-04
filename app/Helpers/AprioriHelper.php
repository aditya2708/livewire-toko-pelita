<?php

namespace App\Helpers;

class AprioriHelper
{
    private static $supportStats;
    private static $confidenceStats;
    private static $liftStats;
    private static $isInitialized = false;

    public static function initialize($rules)
    {
        if (empty($rules)) {
            self::$isInitialized = false;
            return;
        }

        self::$supportStats = self::calculateStats(array_column($rules, 'support'));
        self::$confidenceStats = self::calculateStats(array_column($rules, 'confidence'));
        self::$liftStats = self::calculateStats(array_column($rules, 'lift'));
        self::$isInitialized = true;
    }

    private static function calculateStats($data)
    {
        if (empty($data)) {
            return null;
        }

        sort($data);
        $count = count($data);
        return [
            'min' => $data[0],
            'max' => $data[$count - 1],
            'q1' => $data[intval($count * 0.25)],
            'median' => $data[intval($count * 0.5)],
            'q3' => $data[intval($count * 0.75)],
        ];
    }

    public static function formatPercentage($value)
    {
        return number_format($value * 100, 2) . '%';
    }

    public static function formatDecimal($value)
    {
        return number_format($value, 2);
    }

    public static function getSupportDescription($support)
    {
        $percentage = self::formatPercentage($support);
        
        if (!self::$isInitialized || self::$supportStats === null) {
            return "Support: $percentage";
        }

        $description = "";
        if ($support < self::$supportStats['q1']) $description = "Jarang";
        elseif ($support < self::$supportStats['median']) $description = "Kadang-kadang";
        elseif ($support < self::$supportStats['q3']) $description = "Sering";
        else $description = "Sangat Sering";

        return "$description ($percentage)";
    }

    public static function getConfidenceDescription($confidence)
    {
        $percentage = self::formatPercentage($confidence);
        
        if (!self::$isInitialized || self::$confidenceStats === null) {
            return "Confidence: $percentage";
        }

        $description = "";
        if ($confidence < self::$confidenceStats['q1']) $description = "Kemungkinan Kecil";
        elseif ($confidence < self::$confidenceStats['median']) $description = "Mungkin";
        elseif ($confidence < self::$confidenceStats['q3']) $description = "Cukup Mungkin";
        else $description = "Sangat Mungkin";

        return "$description ($percentage)";
    }

    public static function getLiftDescription($lift)
    {
        $formattedLift = self::formatDecimal($lift);
        
        if (!self::$isInitialized || self::$liftStats === null) {
            return "Lift: $formattedLift";
        }

        $description = "";
        if ($lift < 1) $description = "Tidak Ada Hubungan Khusus";
        elseif ($lift < self::$liftStats['q1']) $description = "Sedikit Terkait";
        elseif ($lift < self::$liftStats['median']) $description = "Cukup Terkait";
        elseif ($lift < self::$liftStats['q3']) $description = "Sangat Terkait";
        else $description = "Sangat Erat Terkait";

        return "$description ($formattedLift)";
    }

    public static function getDetailedStats($type)
    {
        $stats = null;
        switch ($type) {
            case 'support':
                $stats = self::$supportStats;
                break;
            case 'confidence':
                $stats = self::$confidenceStats;
                break;
            case 'lift':
                $stats = self::$liftStats;
                break;
        }

        if (!$stats) {
            return "Statistik tidak tersedia";
        }

        return "Min: " . self::formatDecimal($stats['min']) . 
               ", Q1: " . self::formatDecimal($stats['q1']) . 
               ", Median: " . self::formatDecimal($stats['median']) . 
               ", Q3: " . self::formatDecimal($stats['q3']) . 
               ", Max: " . self::formatDecimal($stats['max']);
    }

    public static function isInitialized()
    {
        return self::$isInitialized;
    }
}