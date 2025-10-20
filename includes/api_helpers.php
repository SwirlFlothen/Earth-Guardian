<?php
require_once 'api_config.php';

/**
 * Safe wrapper for file_get_contents with error handling
 */
function safeFileGetContents($url) {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'EarthGuardian/1.0',
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);

    try {
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            throw new Exception("Failed to fetch data from URL: " . $url);
        }
        return $data;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Get weather data from OpenWeather API
 */
function getWeatherData($lat, $lon) {
    try {
        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&appid=%s&units=metric',
            $lat,
            $lon,
            OPEN_WEATHER_API_KEY
        );

        $data = safeFileGetContents($url);
        if ($data === false) {
            throw new Exception("Failed to fetch weather data");
        }

        $result = json_decode($data, true);
        if (!$result) {
            throw new Exception("Failed to decode weather data");
        }

        return $result;
    } catch (Exception $e) {
        error_log("Weather API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get air quality data from OpenWeather API
 */
function getAirQualityData($lat, $lon) {
    try {
        $url = sprintf(
            'http://api.openweathermap.org/data/2.5/air_pollution?lat=%s&lon=%s&appid=%s',
            $lat,
            $lon,
            OPEN_WEATHER_API_KEY
        );

        $data = safeFileGetContents($url);
        if ($data === false) {
            throw new Exception("Failed to fetch AQI data");
        }

        $result = json_decode($data, true);
        if (!$result) {
            throw new Exception("Failed to decode AQI data");
        }

        return $result;
    } catch (Exception $e) {
        error_log("AQI API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get EPIC image from NASA API
 */
function getEpicImage() {
    try {
        // First try to get the latest available date
        $availableUrl = 'https://api.nasa.gov/EPIC/api/natural/available?api_key=' . NASA_API_KEY;
        $availableData = safeFileGetContents($availableUrl);
        if ($availableData === false) {
            throw new Exception("Failed to fetch available dates");
        }

        $dates = json_decode($availableData, true);
        if (!$dates || empty($dates)) {
            throw new Exception("No available dates found");
        }

        // Get the most recent date
        $latestDate = end($dates);
        $url = sprintf(
            'https://api.nasa.gov/EPIC/api/natural/date/%s?api_key=%s',
            $latestDate,
            NASA_API_KEY
        );

        $data = safeFileGetContents($url);
        if ($data === false) {
            throw new Exception("Failed to fetch image data");
        }

        $imageData = json_decode($data, true);
        if (!$imageData || empty($imageData)) {
            throw new Exception("No image data found");
        }

        // Get the most recent image
        $latestImage = $imageData[0];
        $date = substr($latestImage['date'], 0, 10);
        $dateParts = explode('-', $date);
        
        $imageUrl = sprintf(
            'https://api.nasa.gov/EPIC/archive/natural/%s/%s/%s/png/%s.png?api_key=%s',
            $dateParts[0],
            $dateParts[1],
            $dateParts[2],
            $latestImage['image'],
            NASA_API_KEY
        );

        return [
            'url' => $imageUrl,
            'caption' => $latestImage['caption'] ?? 'Earth View',
            'date' => $latestImage['date']
        ];
    } catch (Exception $e) {
        error_log("NASA EPIC API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get appropriate weather icon based on OpenWeather icon code
 */
function getWeatherIcon($code) {
    $icons = [
        '01d' => 'fa-sun',
        '01n' => 'fa-moon',
        '02d' => 'fa-cloud-sun',
        '02n' => 'fa-cloud-moon',
        '03d' => 'fa-cloud',
        '03n' => 'fa-cloud',
        '04d' => 'fa-cloud',
        '04n' => 'fa-cloud',
        '09d' => 'fa-cloud-rain',
        '09n' => 'fa-cloud-rain',
        '10d' => 'fa-cloud-showers-heavy',
        '10n' => 'fa-cloud-showers-heavy',
        '11d' => 'fa-bolt',
        '11n' => 'fa-bolt',
        '13d' => 'fa-snowflake',
        '13n' => 'fa-snowflake',
        '50d' => 'fa-smog',
        '50n' => 'fa-smog'
    ];
    
    return isset($icons[$code]) ? $icons[$code] : 'fa-cloud';
}

/**
 * Get AQI status text and styling based on AQI value
 */
function getAQIStatus($aqi) {
    if ($aqi <= 50) {
        return ['Good', 'text-success', 'fa-smile'];
    } elseif ($aqi <= 100) {
        return ['Moderate', 'text-warning', 'fa-meh'];
    } elseif ($aqi <= 150) {
        return ['Unhealthy for Sensitive Groups', 'text-orange', 'fa-frown'];
    } elseif ($aqi <= 200) {
        return ['Unhealthy', 'text-danger', 'fa-face-frown'];
    } elseif ($aqi <= 300) {
        return ['Very Unhealthy', 'text-purple', 'fa-face-dizzy'];
    } else {
        return ['Hazardous', 'text-dark', 'fa-skull'];
    }
}
?>