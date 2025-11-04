<?php

namespace App\Utils;

/**
 * Utility class for calculating geographical distances.
 */
class DistanceCalculator
{
    private const EARTH_RADIUS_METERS = 6371000; // Earth radius in meters

    /**
     * Calculate the distance between two points using the Haversine formula.
     *
     *
     * @param float $latitude1 Latitude of point 1 in degrees.
     * @param float $longitude1 Longitude of point 1 in degrees.
     * @param float $latitude2 Latitude of point 2 in degrees.
     * @param float $longitude2 Longitude of point 2 in degrees.
     * @return float Distance in meters.
     */
    public static function calculate(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        // Convert degrees to radians
        $lat1Rad = deg2rad($latitude1);
        $lon1Rad = deg2rad($longitude1);
        $lat2Rad = deg2rad($latitude2);
        $lon2Rad = deg2rad($longitude2);

        // Calculate deltas
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance
        $distance = self::EARTH_RADIUS_METERS * $c;

        return $distance;
    }

    /**
     * Check if the distance between two points is within a specified range (threshold).
     *
     *
     * @param float $latitude1 Latitude of point 1.
     * @param float $longitude1 Longitude of point 1.
     * @param float $latitude2 Latitude of point 2.
     * @param float $longitude2 Longitude of point 2.
     * @param float $threshold Distance threshold in meters.
     * @return bool True if the distance is within or equal to the threshold, false otherwise.
     */
    public static function isWithinRange(float $latitude1, float $longitude1, float $latitude2, float $longitude2, float $threshold): bool
    {
        $distance = self::calculate($latitude1, $longitude1, $latitude2, $longitude2);
        return $distance <= $threshold;
    }
}