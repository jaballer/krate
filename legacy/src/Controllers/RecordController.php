<?php
declare(strict_types=1);

namespace Krate\Controllers;

use Exception;
use Krate\Services\RecordService;
use Krate\Core\Helpers\RequestHelper;
use Krate\Core\Helpers\SessionHelper;
use Krate\Core\Helpers\HtmlHelper;
use Krate\Core\Helpers\UrlHelper;

class RecordController
{
    private RecordService $recordService;
    private RequestHelper $requestHelper;
    private SessionHelper $sessionHelper;
    private HtmlHelper $htmlHelper;
    private $settingsManager;
    private $socialLinksService;
    private UrlHelper $urlHelper;
    private $userManager;
    private array $config;
    private $recordImageService;
    private \Twig\Environment $twig;

    /**
     * @param array $app Application service container from config/bootstrap.php.
     */
    public function __construct(array $app)
    {
        $this->recordService = $app['recordService'];
        $this->requestHelper = $app['requestHelper'];
        $this->sessionHelper = $app['sessionHelper'];
        $this->htmlHelper = $app['htmlHelper'];
        $this->settingsManager = $app['settingsManager'];
        $this->socialLinksService = $app['socialLinksService'];
        $this->urlHelper = $app['urlHelper'];
        $this->userManager = $app['userManager'];
        $this->config = $app['config'];
        $this->recordImageService = $app['recordImageService'];
        $this->twig = $app['twig'];
    }

    /**
     * List all records (optionally filtered by a search term).
     */
    public function index(): void
    {
        $searchTerm = $this->requestHelper->get('search');

        echo $this->twig->render('records/index.twig', [
            'records' => $this->recordService->findAll($searchTerm),
            'search_term' => $searchTerm ?? '',
            'hero_title' => $this->settingsManager->getSetting('site_name'),
            'hero_tagline' => $this->settingsManager->getSetting('site_tagline'),
        ]);
    }

    /**
     * Show a single record (publicly viewable).
     */
    public function show(): void
    {
        $recordId = $this->requestHelper->get('id');
        if (!$recordId) {
            throw new Exception('Record ID not provided');
        }

        $record = $this->recordService->findById((int) $recordId);
        if (!$record) {
            throw new Exception('Record not found');
        }

        echo $this->twig->render('records/show.twig', [
            'record' => $record,
            'page_title' => $record->getTitle(),
        ]);
    }

    /**
     * Add a new record. Handles GET (render form) and POST (validate, persist).
     */
    public function add(): void
    {
        if (!$this->sessionHelper->isLoggedIn()) {
            $this->sessionHelper->setMessage('Please login to add records');
            $this->urlHelper->redirect('../index.php');
        }

        $errors = [];

        if ($this->requestHelper->isPost()) {
            validate_csrf_token($this->requestHelper->post('csrf_token'));

            $formData = [
                'title' => $this->requestHelper->post('title'),
                'artist' => $this->requestHelper->post('artist'),
                'genre' => $this->requestHelper->post('genre'),
                'release_year' => $this->requestHelper->post('release_year'),
                'label' => $this->requestHelper->post('label'),
                'catalog_number' => $this->requestHelper->post('catalog_number'),
                'format' => $this->requestHelper->post('format'),
                'speed' => $this->requestHelper->post('speed'),
                'condition' => $this->requestHelper->post('condition'),
                'purchase_date' => $this->requestHelper->post('purchase_date'),
                'purchase_price' => $this->requestHelper->post('purchase_price'),
                'notes' => $this->requestHelper->post('notes'),
                'purchase_link' => $this->requestHelper->post('purchase_link'),
                'audio_file_url' => $this->requestHelper->post('audio_file_url'),
                'bpm' => $this->requestHelper->post('bpm'),
            ];

            // Track newly stored images so they can be rolled back on failure
            $storedImages = [];
            $formData['front_image'] = null;
            $formData['back_image'] = null;

            try {
                $frontImagePath = $this->recordImageService->storeUploadedImage($_FILES['front_image'] ?? [], 'record-front');
                if ($frontImagePath !== null) {
                    $formData['front_image'] = $frontImagePath;
                    $storedImages[] = $frontImagePath;
                }

                $backImagePath = $this->recordImageService->storeUploadedImage($_FILES['back_image'] ?? [], 'record-back');
                if ($backImagePath !== null) {
                    $formData['back_image'] = $backImagePath;
                    $storedImages[] = $backImagePath;
                }

                $this->recordService->create($formData);
                $this->sessionHelper->setMessage('Record added successfully!');
                $this->urlHelper->redirect('../index.php');
            } catch (Exception $e) {
                foreach ($storedImages as $imagePath) {
                    $this->recordImageService->deleteUploadedImage($imagePath);
                }
                $errors[] = 'Error adding record: ' . $e->getMessage();
            }
        }

        echo $this->twig->render('records/add.twig', [
            'errors' => $errors,
        ]);
    }

    /**
     * Edit an existing record. Handles both the GET (render form) and POST
     * (validate, persist, redirect) paths. Renders via the shared Twig layout.
     */
    public function edit(): void
    {
        // Require login
        if (!$this->sessionHelper->isLoggedIn()) {
            $this->sessionHelper->setMessage('Please login to edit records');
            $this->urlHelper->redirect('../index.php');
        }

        $recordId = $this->requestHelper->get('id');
        if (!$recordId) {
            throw new Exception('Record ID not provided');
        }

        $record = $this->recordService->findById((int) $recordId);
        if (!$record) {
            throw new Exception('Record not found');
        }

        if ($this->requestHelper->isPost()) {
            validate_csrf_token($this->requestHelper->post('csrf_token'));

            // Track newly stored images so they can be rolled back on failure
            $storedImages = [];
            $frontImagePath = $record->getFrontImage();
            $backImagePath = $record->getBackImage();

            try {
                $newFrontImagePath = $this->recordImageService->storeUploadedImage($_FILES['front_image'] ?? [], 'record-front');
                if ($newFrontImagePath !== null) {
                    $frontImagePath = $newFrontImagePath;
                    $storedImages[] = $newFrontImagePath;
                }

                $newBackImagePath = $this->recordImageService->storeUploadedImage($_FILES['back_image'] ?? [], 'record-back');
                if ($newBackImagePath !== null) {
                    $backImagePath = $newBackImagePath;
                    $storedImages[] = $newBackImagePath;
                }

                $this->recordService->update((int) $recordId, [
                    'title' => $this->requestHelper->post('title'),
                    'artist' => $this->requestHelper->post('artist'),
                    'genre' => $this->requestHelper->post('genre'),
                    'release_year' => $this->requestHelper->post('release_year'),
                    'label' => $this->requestHelper->post('label'),
                    'catalog_number' => $this->requestHelper->post('catalog_number'),
                    'format' => $this->requestHelper->post('format'),
                    'speed' => $this->requestHelper->post('speed'),
                    'condition' => $this->requestHelper->post('condition'),
                    'purchase_date' => $this->requestHelper->post('purchase_date'),
                    'purchase_price' => $this->requestHelper->post('purchase_price'),
                    'notes' => $this->requestHelper->post('notes'),
                    'front_image' => $frontImagePath,
                    'back_image' => $backImagePath,
                    'purchase_link' => $this->requestHelper->post('purchase_link'),
                    'audio_file_url' => $this->requestHelper->post('audio_file_url'),
                    'bpm' => $this->requestHelper->post('bpm'),
                ]);

                $this->sessionHelper->setMessage('Record updated successfully!');
                $this->urlHelper->redirect('details.php?id=' . $recordId);
            } catch (Exception $e) {
                foreach ($storedImages as $imagePath) {
                    $this->recordImageService->deleteUploadedImage($imagePath);
                }
                throw $e;
            }
        }

        echo $this->twig->render('records/edit.twig', [
            'record' => $record,
            'page_title' => 'Edit Vinyl Record: ' . $record->getTitle() . ' by ' . $record->getArtist(),
        ]);
    }

    /**
     * Delete a record. GET renders a confirmation page; POST performs deletion.
     */
    public function delete(): void
    {
        if (!$this->sessionHelper->isLoggedIn()) {
            $this->sessionHelper->setMessage('Please login to delete records');
            $this->urlHelper->redirect('../index.php');
        }

        $recordId = $this->requestHelper->get('id');
        if (!$recordId) {
            throw new Exception('Record ID not provided');
        }

        $record = $this->recordService->findById((int) $recordId);
        if (!$record) {
            throw new Exception('Record not found');
        }

        if ($this->requestHelper->isPost()) {
            validate_csrf_token($this->requestHelper->post('csrf_token'));

            $this->recordService->delete((int) $recordId);
            $this->sessionHelper->setMessage('Record deleted successfully');
            $this->urlHelper->redirect('../index.php');
        }

        echo $this->twig->render('records/delete.twig', [
            'record' => $record,
            'page_title' => 'Delete Vinyl Record: ' . $record->getTitle() . ' by ' . $record->getArtist(),
        ]);
    }
}
