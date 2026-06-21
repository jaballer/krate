<?php
declare(strict_types=1);

use Krate\Controllers\AdminController;

if (!isset($router)) {
    throw new RuntimeException('Router is not initialized');
}

// Define routes for the admin dashboard
$router->addRoute('GET', '/admin/dashboard', function() use ($app) {
    try {
        $adminController = new AdminController($app);
        $adminController->handleRequest();
    } catch (Exception $e) {
        error_log("Error in admin dashboard: " . $e->getMessage());
    }
});

// Add POST route for handling form submissions
$router->addRoute('POST', '/admin/dashboard', function() use ($app) {
    try {
        $adminController = new AdminController($app);
        $adminController->handleRequest();
    } catch (Exception $e) {
        error_log("Error in admin dashboard: " . $e->getMessage());
    }
});
