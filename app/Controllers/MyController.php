<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Validation\Validation;
use Config\Database;
use App\Models\ApplicationModel;

class MyController extends BaseController
{
    /**
     * @var \CodeIgniter\Session\Session
     */
    public $session;

    protected $helpers = ['custom_fields', 'general', 'unzip'];

    protected $data = [];

    protected $db;

    protected $applicationModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        // Initialize the database connection
        $this->db = Database::connect();
        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();
        $this->applicationModel = new ApplicationModel();

        $this->response->setHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT');
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader("Expires", 'Mon, 26 Jul 1997 05:00:00 GMT');
        if (config('App')->installed == false) {
            return redirect()->to(site_url('install'));
        }

        $globalSettings = $this->db->table('global_settings')->where('id', 1)->get()->getRowArray();
        $branchID = model('ApplicationModel')->get_branch_id();
        if (!empty($branchID)) {
            $branch = $this->db->table('branch')->select('currency_formats,symbol_position,symbol,currency,timezone')->where('id', $branchID)->get()->getRow();
            $globalSettings['currency'] = $branch->currency;
            $globalSettings['currency_symbol'] = $branch->symbol;
            $globalSettings['currency_formats'] = $branch->currency_formats;
            $globalSettings['symbol_position'] = $branch->symbol_position;
            if (!empty($branch->timezone)) {
                $globalSettings['timezone'] = $branch->timezone;
            }
        }

        $this->data['global_config'] = $globalSettings;
        $this->data['theme_config'] = $this->db->table('theme_settings')->where('id', 1)->get()->getRowArray();
        date_default_timezone_set($globalSettings['timezone']);
        return null;
    }

    public function get_payment_config()
    {
        $branchID = model('ApplicationModel')->get_branch_id();
        return $this->db->table('payment_config')->where('branch_id', $branchID)->get()->getRowArray();
    }

    public function getBranchDetails()
    {
        $branchID = model('ApplicationModel')->get_branch_id();
        $branch = $this->db->table('branch')->where('id', $branchID)->get()->getRowArray();
        if (empty($branch)) {
            return ['stu_generate' => "", 'grd_generate' => ""];
        }

        return $branch;
    }

    public function photoHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', (string) $this->data['global_config']['image_extension'])));
        $allowedSizeKB = $this->data['global_config']['image_size'];
        $allowedSize = floatval(1024 * $allowedSizeKB);
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validator->setError('photoHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > $allowedSize) {
                    $this->validator->setError('photoHandleUpload', translate('file_size_shoud_be_less_than') . sprintf(' %s KB.', $allowedSizeKB));
                    return false;
                }
            } else {
                $this->validator->setError('photoHandleUpload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }

    public function fileHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', (string) $this->data['global_config']['file_extension'])));
        $allowedSizeKB = $this->data['global_config']['file_size'];
        $allowedSize = floatval(1024 * $allowedSizeKB);
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validator->setError('fileHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > $allowedSize) {
                    $this->validator->setError('fileHandleUpload', translate('file_size_shoud_be_less_than') . sprintf(' %s KB.', $allowedSizeKB));
                    return false;
                }
            } else {
                $this->validator->setError('fileHandleUpload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }
}
