<?php
ob_start(); // output buffering is turned on
session_start(); // turn on sessions

// Load Composer's autoloader and initialize phpdotenv
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Fivetwofive\KrateCMS\KrateSettings;

// Load all required files first
require_once('functions.php');
require_once('database.php');
require_once('query_functions.php');
require_once('validation_functions.php');
require_once('auth_functions.php');

$dotenv = Dotenv::createImmutable(__DIR__ . '/..'); // Adjust the path to your .env location
$dotenv->load(); // Load environment variables from .env

// Database connection setup
$dbConfig = [
    'server' => $_ENV['DB_SERVER'],
    'user' => $_ENV['DB_USER'],
    'pass' => $_ENV['DB_PASS'],
    'name' => $_ENV['DB_NAME']
];

// Connect to database
$db = db_connect(
    $dbConfig['server'], 
    $dbConfig['user'], 
    $dbConfig['pass'], 
    $dbConfig['name']
);

// Initialize settings manager
$settingsManager = KrateSettings::getInstance($db);

// Site configuration with database settings integration
$config = [
    'site' => [
        'owner' => $_ENV['SITE_OWNER'],
        'author' => $_ENV['SITE_AUTHOR'],
        'name' => $_ENV['SITE_NAME'],
        'tagline' => $_ENV['SITE_TAGLINE'],
        'description' => $_ENV['SITE_DESCRIPTION'],
        
        'logo_url' => $settingsManager->getSetting('logo_url', ''), // Add logo_url from settings
        'audio_source_url' => $settingsManager->getSetting('audio_source', ''), // Add audio source from settings
    ],
    'db' => $dbConfig,
    'api' => [
        'postmark' => $_ENV['POSTMARK_API_TOKEN']
    ]
];

// Server configuration
$serverConfig = [
    'protocol' => empty($_SERVER['HTTPS']) ? 'http' : 'https',
    'name' => $_SERVER['SERVER_NAME'],
    'script' => $_SERVER['SCRIPT_NAME'],
    'host' => $_SERVER['HTTP_HOST'],
    'docRoot' => $_SERVER['DOCUMENT_ROOT'],
    'userAgent' => $_SERVER['HTTP_USER_AGENT'],
    'port' => $_SERVER['SERVER_PORT']
];

// Calculate base URL
$displayPort = ($serverConfig['protocol'] === 'http' && $serverConfig['port'] == 80 || 
                $serverConfig['protocol'] === 'https' && $serverConfig['port'] == 443) 
                ? '' : ":{$serverConfig['port']}";
$baseUrl = "{$serverConfig['protocol']}://{$serverConfig['name']}";

// Define constants
define('PRIVATE_PATH', __DIR__);
define('PROJECT_PATH', dirname(PRIVATE_PATH));
define('STYLES_PATH', $baseUrl . '/assets/css');
define('SCRIPTS_PATH', $baseUrl . '/assets/js');
define('IMAGES_PATH', $baseUrl . '/assets/images');

// Define WWW_ROOT
$publicEnd = strpos($_SERVER['SCRIPT_NAME'], '/public');
$docRoot = substr($_SERVER['SCRIPT_NAME'], 0, $publicEnd);
define('WWW_ROOT', $docRoot);

$errors = [];

/**
 * PSR-4 Autoloader
 */
spl_autoload_register(function($class) {
    // Project base namespace prefix
    $prefix = 'Fivetwofive\\KrateCMS\\';
    
    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No namespace match, move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});