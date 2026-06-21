<?php
declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load bootstrap and get application container
$app = require_once(__DIR__ . '/../../config/bootstrap.php');

use Krate\Controllers\UserController;

try {
    // Initialize the UserController
    $userController = new UserController($app['userManager']);
    
    // Get view data from controller
    $viewData = $userController->index();
    
    // Extract variables for the view
    extract($viewData);

} catch (Exception $e) {
    error_log("Error in user management: " . $e->getMessage());
    $error_message = "An error occurred while processing your request.";
}

include('../../src/Views/templates/header.php');
include('../../src/Views/users/index.php');
include('../../src/Views/templates/footer.php');