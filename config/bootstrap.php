<?php
declare(strict_types=1);

// Define root path and start output buffering
define('ROOT_PATH', realpath(__DIR__ . '/..'));
ob_start();

// Require Composer's autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Harden session defaults before the session is started.
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', empty($_SERVER['HTTPS']) ? '0' : '1');

$sessionParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $sessionParams['lifetime'] ?? 0,
    'path' => $sessionParams['path'] ?? '/',
    'domain' => $sessionParams['domain'] ?? '',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Start session after the hardened cookie settings are applied.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Import required classes
use Krate\Core\Helpers\UrlHelper;
use Krate\Core\Helpers\HtmlHelper;
use Krate\Core\Helpers\SessionHelper;
use Krate\Core\Helpers\RequestHelper;
use Krate\Core\Validation\ValidationService;
use Krate\Core\Database\DatabaseConnection;
use Krate\Core\Database\DatabaseService;
use Krate\Models\KrateSettings;
use Krate\Controllers\RecordController;
use Krate\Models\Record;
use Krate\Services\UserManager;
use Krate\Services\RecordService;
use Krate\Services\RecordImageService;
use Krate\Services\SocialLinksService;
use Krate\Services\PageService;
use Krate\Services\SubjectService;
use Krate\Services\RankingService;

// Initialize the application container
$app = [];

// Database configuration
$dbConfig = [
    'server' => $_ENV['DB_SERVER'],
    'user' => $_ENV['DB_USER'],
    'pass' => $_ENV['DB_PASS'],
    'name' => $_ENV['DB_NAME']
];

// Initialize database connection
$dbConnection = new DatabaseConnection($dbConfig);
$dbService = new DatabaseService($dbConnection);

// Add database connection to the app container
$app['databaseConnection'] = $dbConnection;

// Initialize settings manager with DatabaseConnection
$settingsManager = KrateSettings::getInstance($dbConnection);

// Calculate base URL and WWW_ROOT
$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
$serverName = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'];
$displayPort = ($protocol === 'http' && $port == 80 || 
                $protocol === 'https' && $port == 443) 
                ? '' : ":{$port}";
$baseUrl = "{$protocol}://{$serverName}{$displayPort}";
//$baseUrl = "{$protocol}://{$serverName}";

$publicEnd = strpos($_SERVER['SCRIPT_NAME'], '/public');
$docRoot = ($publicEnd !== false) ? substr($_SERVER['SCRIPT_NAME'], 0, $publicEnd) : '';
define('WWW_ROOT', $docRoot);

// Initialize core services
$urlHelper = new UrlHelper(WWW_ROOT);
$htmlHelper = new HtmlHelper();
$sessionHelper = new SessionHelper();
$requestHelper = new RequestHelper();
$validationService = new ValidationService();

// Add helpers to the app container
$app['requestHelper'] = $requestHelper;
$app['sessionHelper'] = $sessionHelper;
$app['htmlHelper'] = $htmlHelper;
$app['settingsManager'] = $settingsManager;

function csrf_token(): string {
    global $sessionHelper;
    return $sessionHelper->getCsrfToken();
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

function validate_csrf_token(?string $token): void {
    global $sessionHelper;
    $sessionHelper->validateCsrfToken($token);
}

// Initialize business services
$socialLinksService = new SocialLinksService($settingsManager, $htmlHelper);
$recordService = new RecordService($dbConnection);
$recordImageService = new RecordImageService(ROOT_PATH . '/public');
$userManager = new UserManager($dbConnection, $_ENV['POSTMARK_API_TOKEN'] ?? null);

// Initialize RankingService
$rankingService = new RankingService($dbConnection);

// Site configuration with database settings integration
$config = [
    'site' => [
        'owner' => $_ENV['SITE_OWNER'],
        'author' => $_ENV['SITE_AUTHOR'],
        'name' => $_ENV['SITE_NAME'],
        'tagline' => $_ENV['SITE_TAGLINE'],
        'description' => $_ENV['SITE_DESCRIPTION'],
        'logo_url' => $settingsManager->getSetting('logo_url', ''),
        'audio_source_url' => $settingsManager->getSetting('audio_source', ''),
    ],
    'db' => $dbConfig,
    'api' => [
        'postmark' => $_ENV['POSTMARK_API_TOKEN'] ?? null
    ]
];

// Add services to app container
$app['socialLinksService'] = $socialLinksService;
$app['recordService'] = $recordService;
$app['recordImageService'] = $recordImageService;
$app['userManager'] = $userManager;
$app['urlHelper'] = $urlHelper;
$app['config'] = $config;
$app['rankingService'] = $rankingService;

// Add to service container
// $app['pageService'] = new PageService($app['databaseConnection']);
// $app['subjectService'] = new SubjectService($app['databaseConnection']);

// Define path constants
define('PRIVATE_PATH', ROOT_PATH . '/src');

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(ROOT_PATH . '/src/Views');
$twig = new \Twig\Environment($loader, [
    'cache' => ROOT_PATH . '/cache/twig',
    'debug' => $_ENV['APP_ENV'] ?? 'development' !== 'production',
    'auto_reload' => $_ENV['APP_ENV'] ?? 'development' !== 'production',
]);

// Add global variables to Twig
$twig->addGlobal('site_name', $config['site']['name'] ?? 'Krate');

// Add CSRF helper functions
$twig->addFunction(new \Twig\TwigFunction('csrf_token', 'csrf_token'));
$twig->addFunction(new \Twig\TwigFunction('csrf_field', 'csrf_field'));

// Add helper functions to Twig
$twig->addFunction(new \Twig\TwigFunction('url_for', [$urlHelper, 'urlFor']));
$twig->addFunction(new \Twig\TwigFunction('h', [$htmlHelper, 'escape']));
$twig->addFunction(new \Twig\TwigFunction('display_errors', [$htmlHelper, 'displayErrors']));
$twig->addFunction(new \Twig\TwigFunction('display_social_links', [$socialLinksService, 'displayLinks']));

// Layout context for the shared Twig base layout (templates/base.twig).
// These mirror the data the legacy PHP header.php/footer.php read from scope,
// so any template that {% extends 'templates/base.twig' %} gets the full chrome.
$currentUserId = $sessionHelper->getCurrentUserId();
$twig->addGlobal('is_logged_in', $sessionHelper->isLoggedIn());
$twig->addGlobal('is_admin', $currentUserId ? $userManager->isAdmin($currentUserId) : false);
$twig->addGlobal('site', $config['site']);
$twig->addGlobal('dark_mode', (bool) $settingsManager->getSetting('dark_mode', false));
$twig->addGlobal('audio_player_on', (bool) $settingsManager->getSetting('audio_player_on'));
$twig->addGlobal('audio_source_desc', $settingsManager->getSettingDescription('audio_source'));

// Flash message exposed as a function so it is read-and-cleared only when a
// template actually renders the layout (not eagerly on redirect-only requests).
$twig->addFunction(new \Twig\TwigFunction('get_flash', [$sessionHelper, 'getAndClearMessage']));

// HTML helper function to escape output for safe rendering
// This prevents XSS attacks by converting special characters to HTML entities
function h(string $string = ""): string {
    global $htmlHelper;
    return $htmlHelper->escape($string);
}

// URL helper function to generate a URL for a given path
// This abstracts the URL generation logic, making it easier to manage routes
function url_for(string $path): string {
    global $urlHelper;
    return $urlHelper->urlFor($path);
}

// Redirect function to send the user to a specified location
// This function does not return; it terminates the current script and performs a redirect
function redirect_to(string $location): never {
    global $urlHelper;
    $urlHelper->redirect($location);
}

// After initializing Twig
$app['twig'] = $twig;

// Return the application container
return $app;
