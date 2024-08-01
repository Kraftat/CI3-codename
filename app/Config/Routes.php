<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Database\Config as DatabaseConfig;

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function () {
    $response = service('response');
    $response->setStatusCode(404); // Set the status code

    // Initialize the ApplicationModel
    $applicationModel = new \App\Models\ApplicationModel();

    // Pass the ApplicationModel to the view
    echo view('errors/error_404_message', ['applicationModel' => $applicationModel]);
});
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Get the current domain.
$url = 'http://localhost'; // Default value for CLI or undefined HTTP_HOST
if (isset($_SERVER['HTTP_HOST'])) {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
$url = rtrim($url, '/');
$domain = parse_url($url, PHP_URL_HOST);
if (substr($domain, 0, 4) == 'www.') {
    $domain = str_replace('www.', '', $domain);
}

// Initialize database connection
$db = \Config\Database::connect();

if ($db) {
    // Check if the custom_domain table exists
    $saas_default = false;
    if ($db->tableExists('custom_domain')) {
        $builder = $db->table('custom_domain');
        $builder->select('count(id) as cid');
        $builder->where('status', 1);
        $builder->where('url', $domain);
        $query = $builder->get();
        $getURL = $query->getRow();

        if ($getURL && $getURL->cid > 0) {
            $routes->get('authentication', 'Authentication::index/$1');
            $routes->get('forgot', 'Authentication::forgot/$1');
            $routes->get('teachers', 'Home::teachers');
            $routes->get('events', 'Home::events');
            $routes->get('news', 'Home::news');
            $routes->get('about', 'Home::about');
            $routes->get('faq', 'Home::faq');
            $routes->get('admission', 'Home::admission');
            $routes->get('gallery', 'Home::gallery');
            $routes->get('contact', 'Home::contact');
            $routes->get('admit_card', 'Home::admit_card');
            $routes->get('exam_results', 'Home::exam_results');
            $routes->get('certificates', 'Home::certificates');
            $routes->get('page/(:any)', 'Home::page/$1');
            $routes->get('gallery_view/(:any)', 'Home::gallery_view/$1');
            $routes->get('event_view/(:num)', 'Home::event_view/$1');
            $routes->get('news_view/(:any)', 'Home::news_view/$1');
            $routes->get('/', 'Home::index');
        } else {
            $saas_default = true;
        }
    } else {
        $saas_default = true;
    }
} else {
    $saas_default = true;
}

// Default routes
$routes->get('(:any)/authentication', 'Authentication::index/$1');
$routes->get('(:any)/forgot', 'Authentication::forgot/$1');
$routes->get('(:any)/teachers', 'Home::teachers');
$routes->get('(:any)/events', 'Home::events');
$routes->get('(:any)/news', 'Home::news');
$routes->get('(:any)/about', 'Home::about');
$routes->get('(:any)/faq', 'Home::faq');
$routes->get('(:any)/admission', 'Home::admission');
$routes->get('(:any)/gallery', 'Home::gallery');
$routes->get('(:any)/contact', 'Home::contact');
$routes->get('(:any)/admit_card', 'Home::admit_card');
$routes->get('(:any)/exam_results', 'Home::exam_results');
$routes->get('(:any)/certificates', 'Home::certificates');
$routes->get('(:any)/page/(:any)', 'Home::page/$2');
$routes->get('(:any)/gallery_view/(:any)', 'Home::gallery_view/$2');
$routes->get('(:any)/event_view/(:num)', 'Home::event_view/$2');
$routes->get('(:any)/news_view/(:any)', 'Home::news_view/$2');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('branch', 'Branch::index');
$routes->get('attachments', 'Attachments::index');
$routes->get('homework', 'Homework::index');
$routes->get('onlineexam', 'OnlineExam::index');
$routes->get('hostels', 'Hostels::index');
$routes->get('event', 'Event::index');
$routes->get('accounting', 'Accounting::index');
$routes->get('school_settings', 'SchoolSettings::index');
$routes->get('role', 'Role::index');
$routes->get('branch_role', 'BranchRole::index');
$routes->get('sessions', 'Sessions::index');
$routes->get('translations', 'Translations::index');
$routes->get('cron_api', 'CronApi::index');
$routes->get('modules', 'Modules::index');
$routes->get('system_student_field', 'SystemStudentField::index');
$routes->get('custom_field', 'CustomField::index');
$routes->get('backup', 'Backup::index');
$routes->get('advance_salary', 'AdvanceSalary::index');
$routes->get('system_update', 'SystemUpdate::index');
$routes->get('certificate', 'Certificate::index');
$routes->get('payroll', 'Payroll::index');
$routes->get('leave', 'Leave::index');
$routes->get('award', 'Award::index');
$routes->get('classes', 'Classes::index');
$routes->get('student_promotion', 'StudentPromotion::index');
$routes->get('live_class', 'LiveClass::index');
$routes->get('exam', 'Exam::index');
$routes->get('profile', 'Profile::index');
$routes->get('sections', 'Sections::index');
$routes->get('subscription_review/(:num)', 'Saas_website::purchase_complete/$1');

$routes->get('authentication', 'Authentication::index');
$routes->get('install', 'Install::index');

if ($saas_default) {
    $routes->get('/', 'Saas_website::index');
}
$routes->get('(:any)', 'Home::index/$1');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to override any defaults in this file. To do that, require additional
 * route files here to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
