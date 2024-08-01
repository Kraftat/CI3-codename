<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SystemUpdateModel;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : System_update.php
 * @copyright : Reserved RamomCoder Team
 */
class System_update extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\SystemUpdateModel
     */
    public $systemUpdate;

    public $system_updateModel;

    public $load;

    public $input;

    public $agent;

    private $tmp_dir;

    private $tmp_update_dir;

    private $purchase_code;

    private $latest_version;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->systemUpdate = new \App\Models\SystemUpdateModel();
    }

    public function index()
    {
        if (!get_permission('system_update', 'is_add')) {
            access_denied();
        }

        if (!extension_loaded('curl')) {
            $this->data['curl_extension'] = 0;
        } elseif (!empty($this->system_updateModel->getPurchaseCode()['purchase_code'])) {
            $this->data['purchase_code'] = true;
            if ($this->system_updateModel->is_connected()) {
                $this->data['internet'] = true;
                $this->data['curl_extension'] = 1;
                $getUpdateInfo = $this->system_updateModel->get_update_info();
                if (str_contains((string) $getUpdateInfo, 'Curl Error -')) {
                    $this->data['update_errors'] = $getUpdateInfo;
                    $this->data['latest_version'] = "0.0.0";
                    $this->data['support_expiry_date'] = "-/-/-";
                    $this->data['block'] = 0;
                } else {
                    $getUpdateInfo = json_decode((string) $getUpdateInfo);
                    $this->data['update_errors'] = "";
                    $this->data['get_update_info'] = $getUpdateInfo;
                    $this->data['latest_version'] = $getUpdateInfo->latest_version;
                    $this->data['support_expiry_date'] = $getUpdateInfo->support_expiry_date;
                    $this->data['purchase_code'] = $getUpdateInfo->purchase_code;
                    $this->data['block'] = $getUpdateInfo->block;
                }
            } else {
                $this->data['internet'] = false;
            }
        } else {
            $this->data['purchase_code'] = false;
        }

        $this->data['zip_extension'] = extension_loaded('zip') ? 1 : 0;
        $this->data['current_version'] = $this->system_updateModel->get_current_db_version();
        $this->data['title'] = translate('system_update');
        $this->data['sub_page'] = 'system_update/index';
        $this->data['main_menu'] = 'settings';
        echo view('layout/index', $this->data);
    }

    public function update_install()
    {
        if (!get_permission('system_update', 'is_add')) {
            access_denied();
        }

        $getPurchaseCode = $this->system_updateModel->getPurchaseCode();
        $getCurrentVersion = $this->system_updateModel->get_current_db_version();
        $getAddonChk = $this->system_updateModel->addonChk();
        $getIP = $this->system_updateModel->getIP();
        $latestVersion = $this->request->getPost('latest_version');
        $this->latest_version = $latestVersion;
        $this->purchase_code = $getPurchaseCode['purchase_code'];
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
        $url = UPDATE_INSTALL_URL;
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, $this->agent->agent_string());
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 300);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_FILE, $zipResource);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, ['purchase_code' => $this->purchase_code, 'item' => 'school', 'addon' => $getAddonChk, 'current_version' => $getCurrentVersion, 'ip_address' => $getIP, 'url' => base_url()]);
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

            $zip->close();
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to open downloaded zip file']);
            exit;
        }

        fclose($zipResource);
        $this->cleanTmpFiles();
        echo json_encode(['status' => '1', 'message' => 'Successfully Updated']);
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
        }

        return $error;
    }

    public function database()
    {
        if (!get_permission('system_update', 'is_add')) {
            access_denied();
        }

        $dbUpdate = $this->system_updateModel->upgrade_database_silent();
        if ($dbUpdate['success'] == false) {
            echo json_encode(['status' => '0', 'message' => $dbUpdate['message']]);
            exit;
        }

        $message = '<div>
            <h4>Congratulations your Ramom software has been successfully updated ' . config_item('version') . '.</h4>
            <p>
                This window will reload automatically in 5 seconds. You are strongly recommended to manually clear your browser cache.
            </p>
        </div>';
        set_alert('success', translate('you_are_now_using_the_latest_version'));
        echo json_encode(['status' => '1', 'message' => $message]);
    }
}
