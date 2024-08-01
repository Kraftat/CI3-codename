<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.2
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Addons.php
 * @copyright : Reserved RamomCoder Team
 */
class Addons extends MyController

{
    protected $db;



    /**
     * @var App\Models\AddonsModel
     */
    public $addons;

    public $validation;

    public $load;

    public $input;

    public $addonsModel;

    protected $extractPath = "";

    protected $initClassPath = "";

    private $tmp_dir;

    private $tmp_update_dir;

    private $purchase_code;

    private $latest_version;

    public function __construct()
    {

        $request = \Config\Services::request(); // Use CodeIgniter's request service
        $db = \Config\Database::connect(); // Use CodeIgniter's database connection

        $this->addons = new \App\Models\AddonsModel();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    public function index()
    {
        $this->manage();
    }

    /* addons manager */
    public function manage()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['purchase_code' => ["label" => translate('purchase_code'), "rules" => 'trim|required']]);
            $this->validation->setRules(['zip_file' => ["label" => 'Addon Zip File', "rules" => 'callback_zipfileHandleUpload[zip_file]']]);
            if (isset($_FILES["zip_file"]) && empty($_FILES['zip_file']['name'])) {
                $this->validation->setRules(['zip_file' => ["label" => 'Addon Zip File', "rules" => 'required']]);
            }

            if ($this->validation->run() == true) {
                $result = $this->fileUpload();
                if ($result['status'] == 'success') {
                    $array = ['status' => 'success', 'message' => $result['message']];
                } elseif ($result['status'] == 'fail') {
                    $array = ['status' => 'fail', 'error' => ['zip_file' => $result['message']]];
                } elseif ($result['status'] == 'purchase_code') {
                    $array = ['status' => 'fail', 'error' => ['purchase_code' => $result['message']]];
                }

                echo json_encode($array);
                exit;
            }

            $error = $this->validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];

            echo json_encode($array);
            exit;
        }

        $this->data['validation_error'] = '';
        $this->data['addonList'] = $this->addonsModel->getList();
        $this->data['title'] = translate('addon_manager');
        $this->data['sub_page'] = 'addons/index';
        $this->data['main_menu'] = 'addon';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    public function zipfileHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', 'zip')));
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('zipfileHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
            } else {
                $this->validation->setRule('zipfileHandleUpload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }

    /* addons zip upload */
    private function fileUpload()
{


    if ($request->getFile('zip_file')->isValid()) {
        $dir = WRITEPATH . 'uploads/addons'; // Use CodeIgniter's WRITEPATH constant for safe file storage
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            file_put_contents($dir . '/index.html', '');
        }

        $purchaseCode = $request->getPost('purchase_code');
        $uploadPath = $dir . '/';
        $file = $request->getFile('zip_file');
        $zippedFileName = $file->getName();
        $file->move($uploadPath);
        
        $randomDir = generate_encryption_key();
        $this->extractPath = $uploadPath . $randomDir;

        // Unzip uploaded update file and remove zip file
        $zip = new \ZipArchive();
        $res = $zip->open($uploadPath . $zippedFileName);
        if ($res === true) {
            $fileName = trim($zip->getNameIndex(0), '/');
            $zip->extractTo($this->extractPath);
            $zip->close();
            unlink($uploadPath . $zippedFileName);

            $configPath = $this->extractPath . '/' . $fileName . '/config.json';
            if (file_exists($configPath)) {
                $config = file_get_contents($configPath);
                if (!empty($config)) {
                    $json = json_decode($config);
                    if (
                        !empty($json->name) &&
                        !empty($json->version) &&
                        !empty($json->unique_prefix) &&
                        !empty($json->items_code) &&
                        !empty($json->last_update) &&
                        !empty($json->system_version)
                    ) {
                        $currentVersion = $this->addonsModel->get_current_db_version();
                        if ($json->system_version > $currentVersion) {
                            $this->addonsModel->directoryRecursive($this->extractPath);
                            $requiredSystem = wordwrap((string) $json->system_version, 1, '.', true);
                            $currentVersion = wordwrap((string) $currentVersion, 1, '.', true);
                            return ['status' => 'fail', 'message' => sprintf('Minimum System required version %s, your running version %s', $requiredSystem, $currentVersion)];
                        }

                        if ($this->addonsModel->addonInstalled($json->unique_prefix)) {
                            $array = [
                                'product_name' => $json->name,
                                'version' => $json->version,
                                'system_version' => $json->system_version,
                                'unique_prefix' => $json->unique_prefix,
                                'purchase_code' => $purchaseCode
                            ];
                            $apiResult = $this->addonsModel->call_CurlApi($array);
                            if (isset($apiResult->status) && $apiResult->status) {
                                if (!empty($apiResult->sql)) {
                                    $sqlContent = $apiResult->sql;
                                    $db->query('USE ' . $db->database . ';');
                                    foreach (explode(";\n", $sqlContent) as $sql) {
                                        $sql = trim($sql);
                                        if ($sql !== '' && $sql !== '0') {
                                            $db->query($sql);
                                        }
                                    }

                                    // Handle addon directory and files
                                    $this->addonsModel->copyDirectory($this->extractPath . '/' . $fileName, './');
                                    if (file_exists('./config.json')) {
                                        unlink('./config.json');
                                    }

                                    // Execute initClass script
                                    if (!empty($json->initClass)) {
                                        $initClassPath = $this->extractPath . '/' . $fileName . '/' . $json->initClass;
                                        if (file_exists($initClassPath) && is_readable($initClassPath) && include $initClassPath) {
                                            $init = new InitClass();
                                            $init->up();
                                            unlink('./' . $json->initClass);
                                        }
                                    }

                                    // Insert addon details in DB
                                    $arrayAddon = [
                                        'name' => $json->name,
                                        'prefix' => $json->unique_prefix,
                                        'version' => $json->version,
                                        'purchase_code' => $purchaseCode,
                                        'items_code' => $json->items_code,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                    $db->table('addon')->insert($arrayAddon);

                                    $message = "<div class='alert alert-success mt-lg'><div>\r\n
                                        <h4>Congratulations your {$json->name} has been successfully Installed.</h4>\r\n
                                        <p>\r\n
                                            This window will reload automatically in 5 seconds. You are strongly recommended to manually clear your browser cache.\r\n
                                        </p>\r\n
                                    </div></div>";
                                    $this->addonsModel->directoryRecursive($this->extractPath);
                                    return ['status' => 'success', 'message' => $message];
                                }

                                $this->addonsModel->directoryRecursive($this->extractPath);
                                return ['status' => 'purchase_code', 'message' => 'SQL not found'];
                            }

                            $this->addonsModel->directoryRecursive($this->extractPath);
                            return ['status' => 'purchase_code', 'message' => $apiResult->message];
                        }

                        // This addon already installed
                        $this->addonsModel->directoryRecursive($this->extractPath);
                        return ['status' => 'fail', 'message' => "This addon already installed."];
                    }

                    // Invalid JSON
                    $this->addonsModel->directoryRecursive($this->extractPath);
                    return ['status' => 'fail', 'message' => "Invalid config JSON."];
                }

                // JSON content is empty
                $this->addonsModel->directoryRecursive($this->extractPath);
                return ['status' => 'fail', 'message' => "JSON content is empty."];
            }

            // Config file does not exist
            $this->addonsModel->directoryRecursive($this->extractPath);
            return ['status' => 'fail', 'message' => "Config file does not exist."];
        }

        return ['status' => 'fail', 'message' => "Zip extract fail."];
    }

    return null;
}


    public function update($items = '')
    {
        $addon = $this->addonsModel->getAddonDetails($items);
        if (empty($addon)) {
            set_alert('error', translate('addon_not_found'));
            return redirect()->to(base_url('addons/manage'));
        }

        $this->data['status'] = 1;
        if (!extension_loaded('curl')) {
            $this->data['curl_extension'] = 0;
        } elseif (!empty($addon->purchase_code)) {
            $this->data['purchase_code'] = true;
            if ($this->addonsModel->is_connected()) {
                $this->data['internet'] = true;
                $this->data['curl_extension'] = 1;
                $getUpdateInfo = $this->addonsModel->get_update_info($addon);
                if (str_contains((string) $getUpdateInfo, 'Curl Error -')) {
                    $this->data['update_errors'] = $getUpdateInfo;
                    $this->data['latest_version'] = "0.0.0";
                    $this->data['support_expiry_date'] = "-/-/-";
                    $this->data['purchase_code'] = "-";
                    $this->data['block'] = 0;
                } else {
                    $getUpdateInfo = json_decode((string) $getUpdateInfo);
                    $this->data['update_errors'] = "";
                    $this->data['get_update_info'] = $getUpdateInfo;
                    $this->data['latest_version'] = $getUpdateInfo->latest_version;
                    $this->data['support_expiry_date'] = $getUpdateInfo->support_expiry_date;
                    $this->data['purchase_code'] = $getUpdateInfo->purchase_code;
                    $this->data['block'] = $getUpdateInfo->block;
                    $this->data['status'] = $getUpdateInfo->status;
                }
            } else {
                $this->data['internet'] = false;
            }
        } else {
            $this->data['latest_version'] = "0";
            $this->data['purchase_code'] = false;
        }

        $this->data['zip_extension'] = extension_loaded('zip') ? 1 : 0;
        $this->data['addon'] = $addon;
        $this->data['current_version'] = $addon->version;
        $this->data['items'] = $addon->prefix;
        $this->data['title'] = translate('addon_update');
        $this->data['sub_page'] = 'addons/addon_update';
        $this->data['main_menu'] = 'addon';
        echo view('layout/index', $this->data);
        return null;
    }

    public function update_install()
    {
        $latestVersion = $this->request->getPost('latest_version');
        $items = $this->request->getPost('items');
        $systemVersion = $this->addonsModel->get_current_db_version();
        $addon = $this->addonsModel->getAddonDetails($items);
        if (empty($addon)) {
            echo json_encode(['status' => 0, 'message' => translate('addon_not_found')]);
            exit;
        }

        $this->latest_version = $latestVersion;
        $this->purchase_code = $addon->purchase_code;
        $tmpDir = @ini_get('upload_tmp_dir');
        if (!$tmpDir) {
            $tmpDir = @sys_get_temp_dir();
            if ($tmpDir === '' || $tmpDir === '0') {
                $tmpDir = FCPATH . 'temp';
            }
        }

        $tmpDir = rtrim($tmpDir, '/') . '/';
        if (!is_writable($tmpDir)) {
            $message = sprintf('Temporary directory not writable - <b>%s</b><br />Please contact your hosting provider make this directory writable. The directory needs to be writable for the update files.', $tmpDir);
            echo json_encode(['status' => 0, 'message' => $message]);
            exit;
        }

        $this->tmp_dir = $tmpDir;
        $tmpDir = $tmpDir . 'v' . $latestVersion . '/';
        $this->tmp_update_dir = $tmpDir;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
            fopen($tmpDir . 'index.html', 'w');
        }

        $zipFile = $tmpDir . $latestVersion . '.zip';
        // Local Zip File Path
        $zipResource = fopen($zipFile, "w+");
        // Get The Zip File From Server
        $url = UPDATE_INSTALL_ADDON_URL;
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 50);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_FILE, $zipResource);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, ['purchase_code' => $this->purchase_code, 'item' => $addon->prefix, 'current_version' => $addon->version, 'system_version' => $systemVersion, 'url' => base_url()]);
        $success = curl_exec($curlHandle);
        if (!$success) {
            fclose($zipResource);
            $this->cleanTmpFiles();
            $error = $this->getErrorByStatusCode(curl_getinfo($curlHandle, CURLINFO_HTTP_CODE));
            if ($error == '') {
                // Uknown error
                $error = curl_error($curlHandle);
            }

            echo json_encode(['status' => 0, 'message' => $error]);
            exit;
        }

        curl_close($curlHandle);
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            if (!$zip->extractTo('./')) {
                echo json_encode(['status' => 0, 'message' => 'Failed to extract downloaded zip file']);
                exit;
            }

            $initClassPath = FCPATH . sprintf('uploads/addons/%s/initClass.php', $latestVersion);
            if (file_exists($initClassPath) && is_readable($initClassPath) && include $initClassPath) {
                $init = new InitClass();
                $init->up();
                @delete_dir(FCPATH . ('uploads/addons/' . $latestVersion));
            }

            $zip->close();
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to open downloaded zip file']);
            exit;
        }

        fclose($zipResource);
        $this->cleanTmpFiles();
        $message = '<div>
            <h4>Congratulations your Ramom software has been successfully updated ' . config_item('version') . '.</h4>
            <p>
                This window will reload automatically in 5 seconds. You are strongly recommended to manually clear your browser cache.
            </p>
        </div>';
        set_alert('success', translate('you_are_now_using_the_latest_version'));
        echo json_encode(['status' => '1', 'message' => $message]);
    }

    private function cleanTmpFiles()
    {
        if (is_dir($this->tmp_update_dir) && @!delete_dir($this->tmp_update_dir)) {
            @rename($this->tmp_update_dir, $this->tmp_dir . 'delete_this_' . uniqid());
        }
    }

    private function getErrorByStatusCode($statusCode)
    {
        $error = '';
        if ($statusCode == 505) {
            $mailBody = 'Hello. I tried to upgrade to the latest version but for some reason the upgrade failed. Please remove the key from the upgrade log so i can try again. My installation URL is: ' . base_url() . '. Regards.';
            $mailSubject = 'Purchase Key Removal Request - [' . $this->purchase_code . ']';
            $error = 'Purchase key already used to download upgrade files for version ' . wordwrap((string) $this->latest_version, 1, '.', true) . '. Performing multiple auto updates to the latest version with one purchase key is not allowed. If you have multiple installations you must buy another license.<br /><br /> If you have staging/testing installation and auto upgrade is performed there, <b>you should perform manually upgrade</b> in your production area<br /><br /> <h4 class="bold">Upgrade failed?</h4> The error can be shown also if the update failed for some reason, but because the purchase key is already used to download the files, you won’t be able to re-download the files again.<br /><br />Click <a href="mailto:ramomcoder@yahoo.com?subject=' . $mailSubject . '&body=' . $mailBody . '"><b>here</b></a> to send an mail and get your purchase key removed from the upgrade log.';
        } elseif ($statusCode == 506) {
            $error = 'This is not a valid purchase code.';
        } elseif ($statusCode == 507) {
            $error = 'Purchase key empty.';
        } elseif ($statusCode == 508) {
            $error = 'This purchase code is blocked.';
        } elseif ($statusCode == 509) {
            $error = 'This purchase code is not valid for this item.';
        }

        return $error;
    }

    public function update_purchase_code()
    {
        if ($_POST !== []) {
            $this->validation->setRules(['purchase_code' => ["label" => translate('purchase_code'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->db->table('prefix')->where();
                $this->db->table('addon')->update();
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }
}
