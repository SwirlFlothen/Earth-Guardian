<?php
/**
 * API Configuration File
 * Contains all API keys and configuration settings
 */

// Only define constants if they haven't been defined already
// API Keys (Replace with your actual API keys or use environment variables for production)
define('OPEN_WEATHER_API_KEY', '4f041f825bf295a234efa9affb820581');
define('AQI_API_KEY', getenv('AQI_API_KEY') ?: ''); // No key provided, keeping original
define('NASA_API_KEY', '36sN6N5wSt9Y7EDJ5cZKGgeTAbmyd6IEUUkQYLU4');

// API Endpoints
define('WEATHER_API_ENDPOINT', 'https://api.openweathermap.org/data/2.5/weather');
define('AQI_API_ENDPOINT', 'https://api.waqi.info/feed/geo:');
define('NASA_EPIC_API_ENDPOINT', 'https://api.nasa.gov/EPIC/api/natural/');

// Default location (London)
define('DEFAULT_LAT', '51.5074');
define('DEFAULT_LON', '-0.1278');

// Cache settings
define('CACHE_DURATION', 3600); // 1 hour in seconds
define('CACHE_PATH', __DIR__ . '/../public/assets/cache/');

?>