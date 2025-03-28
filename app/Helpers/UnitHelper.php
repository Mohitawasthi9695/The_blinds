<?php
 function convertToMeters($value, $unit, $decimals = 2) {
    $conversionRates = [
        'mm' => 0.001,  // 1 mm = 0.001 m
        'cm' => 0.01,   // 1 cm = 0.01 m
        'm' => 1,       // 1 meter = 1 m
        'ft' => 0.3048, // 1 foot = 0.3048 m
        'inch' => 0.0254 // 1 inch = 0.0254 m
    ];
    $convertedValue = $value * ($conversionRates[strtolower($unit)] ?? 1);
    return round($convertedValue, $decimals);
}
function convertToMM($value, $unit, $decimals = 2) {
    $conversionRates = [
        'mm' => 1,        // 1 mm = 1 mm
        'cm' => 10,       // 1 cm = 10 mm
        'm' => 1000,      // 1 meter = 1000 mm
        'ft' => 304.8,    // 1 foot = 304.8 mm
        'inch' => 25.4    // 1 inch = 25.4 mm
    ];
    $convertedValue = $value * ($conversionRates[strtolower($unit)] ?? 1);
    return round($convertedValue, $decimals);
}
function convertToFeet($value, $unit, $decimals = 2) {
    $conversionRates = [
        'mm' => 0.00328084,  // 1 mm = 0.00328084 ft
        'cm' => 0.0328084,   // 1 cm = 0.0328084 ft
        'm' => 3.28084,      // 1 meter = 3.28084 ft
        'ft' => 1,           // 1 foot = 1 ft
        'inch' => 0.0833333  // 1 inch = 0.0833333 ft
    ];
    $convertedValue = $value * ($conversionRates[strtolower($unit)] ?? 1);
    return round($convertedValue, $decimals);
}
function convertUnit($value, $fromUnit, $toUnit, $decimals = 2) {
    $conversionRates = [
        'mm' => 0.001,  // 1 mm = 0.001 m
        'cm' => 0.01,   // 1 cm = 0.01 m
        'm' => 1,       // 1 meter = 1 m
        'ft' => 0.3048, // 1 foot = 0.3048 m
        'inch' => 0.0254, // 1 inch = 0.0254 m
        'km' => 1000,   // 1 km = 1000 m
        'yard' => 0.9144, // 1 yard = 0.9144 m
        'mile' => 1609.34 // 1 mile = 1609.34 m
    ];
    
    $fromUnit = strtolower($fromUnit);
    $toUnit = strtolower($toUnit);
    $valueInMeters = $value * ($conversionRates[$fromUnit] ?? 1);
    $convertedValue = $valueInMeters / ($conversionRates[$toUnit] ?? 1);
    return round($convertedValue, $decimals);
}
?>