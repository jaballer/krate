<?php
declare(strict_types=1);

// Load bootstrap and get application container
$app = require_once(__DIR__ . '/../../config/bootstrap.php');

use Krate\Controllers\RecordController;

try {
    $recordController = new RecordController($app);
    $recordController->index();
} catch (Exception $e) {
    error_log("Error in records index: " . $e->getMessage());
    $app['sessionHelper']->setMessage("Error: " . $e->getMessage());
    $app['urlHelper']->redirect('../index.php');
}
