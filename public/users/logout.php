<?php
declare(strict_types=1);

// Load bootstrap and get application container
$app = require_once(__DIR__ . '/../../config/bootstrap.php');

try {
    // Extract required services
    $urlHelper = $app['urlHelper'];
    $sessionHelper = $app['sessionHelper'];
    $requestHelper = $app['requestHelper'];
    $userManager = $app['userManager'];

    if (!$requestHelper->isPost()) {
        $urlHelper->redirect('login.php');
    }

    validate_csrf_token($requestHelper->post('csrf_token'));

    $userManager->logout();
    $sessionHelper->setMessage('You have been successfully logged out.');
    
    // Redirect to login page
    $urlHelper->redirect('login.php');
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    $sessionHelper->setMessage('Unable to log out. Please try again.');
    $urlHelper->redirect('login.php');
}
