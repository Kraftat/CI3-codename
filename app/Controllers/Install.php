<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\InstallModel;
use CodeIgniter\Config\Services;
class Install extends AdminController
{
    /**
     * @var \App\Models\InstallModel
     */
    public $InstallModel;

    public $appLib;

    public $input;

    public $validation;

    public $_install;

    public $db;

    public $load;

    protected $installModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->InstallModel = new \App\Models\InstallModel();
        $config = config('App');
        if ($config->installed) {
            redirect()->to(site_url('authentication'))->send();
            return;
        }
    }

    public function index()
    {
        $this->data['step'] = 1;
        if ($_POST !== []) {
            if ($this->request->getPost('step') == 2) {
                $this->data['step'] = 2;
            }

            if ($this->request->getPost('step') == 3) {
                $this->data['step'] = 2;
                // Validating the hostname, the database name and the username. The password is optional
                $this->validation->setRules(['purchase_username' => ["label" => 'Envato Username', "rules" => 'trim|required']]);
                $this->validation->setRules(['purchase_code' => ["label" => 'Purchase Code', "rules" => 'trim|required|callback_purchase_validation']]);
                if ($this->validation->run() == true) {
                    $file = APPPATH . 'config/purchase_key.php';
                    $text = json_encode([$this->request->getPost('purchase_username'), $this->request->getPost('purchase_code')]);
                    @chmod($file, FILE_WRITE_MODE);
                    write_file($file, $text);
                    $this->data['step'] = 3;
                }
            }

            if ($this->request->getPost('step') == 4) {
                $this->data['step'] = 3;
                // Validating the hostname, the database name and the username. The password is optional
                $this->validation->setRules(['hostname' => ["label" => 'Hostname', "rules" => 'trim|required']]);
                $this->validation->setRules(['database' => ["label" => 'Database', "rules" => 'trim|required']]);
                $this->validation->setRules(['username' => ["label" => 'Username', "rules" => 'trim|required']]);
                if ($this->validation->run() == true) {
                    $hostname = $this->request->getPost('hostname');
                    $username = $this->request->getPost('username');
                    $password = $this->request->getPost('password');
                    $database = $this->request->getPost('database');
                    // Connect to the database
                    $link = mysqli_connect($hostname, $username, $password, $database);
                    if (!$link) {
                        $this->data['mysql_error'] = "Error: Unable to connect to MySQL Database." . PHP_EOL;
                    } else {
                        // Write the new database.php file
                        if ($this->_install->write_database_config($this->request->getPost())) {
                            $this->data['step'] = 4;
                        }

                        // Close the connection
                        mysqli_close($link);
                    }
                }
            }

            if ($this->request->getPost('step') == 5) {
                // Validating the diagnostic name, superadmin name, superadmin email, login username, login password
                $this->validation->setRules(['school_name' => ["label" => 'School Name', "rules" => 'trim|required']]);
                $this->validation->setRules(['sa_name' => ["label" => 'Superadmin Name', "rules" => 'trim|required']]);
                $this->validation->setRules(['sa_email' => ["label" => 'Superadmin Email', "rules" => 'trim|required|valid_email']]);
                $this->validation->setRules(['sa_password' => ["label" => 'Superadmin Password', "rules" => 'trim|required']]);
                $this->validation->setRules(['timezone' => ["label" => 'Timezone', "rules" => 'trim|required']]);
                if ($this->validation->run() == true) {
                    $purchaseCode = $this->purchase_code_verification();
                    if (isset($purchaseCode->status) && $purchaseCode->status) {
                        if (!empty($purchaseCode->sql)) {
                            $encryptionKey = bin2hex(substr(md5(random_int(0, mt_getrandmax())), 0, 10));
                            $staffId = substr(md5(random_int(0, mt_getrandmax()) . microtime() . time() . uniqid()), 3, 7);
                            $db = \Config\Database::connect();
                            // Execute a multi query
                            if (mysqli_multi_query($this->db->conn_id, $purchaseCode->sql)) {
                                $this->_install->clean_up_db_query();
                                $schoolName = $this->request->getPost('school_name');
                                $timezone = $this->request->getPost('timezone');
                                $email = $this->request->getPost('sa_email');
                                $password = $this->request->getPost('sa_password');
                                // Superadmin add in the database
                                $staffData = ['staff_id' => $staffId, 'name' => $this->request->getPost('sa_name'), 'joining_date' => date('Y-m-d'), 'email' => $email];
                                $this->db->table('staff')->insert();
                                $insertId = $this->db->insert_id();
                                // Save superadmin login credential information in the database
                                $credentialData = ['user_id' => $insertId, 'username' => $email, 'password' => $this->_install->pass_hashed($password), 'role' => 1, 'active' => 1];
                                if ($this->db->table('login_credential')) {
                                    // global settings DB update
                                    $this->db->table('id')->where()->insert();
                                    $this->db->table('global_settings')->update();
                                    // Write the new autoload.php file
                                    $this->_install->update_autoload_installed();
                                    // Write the new routes.php file
                                    $this->_install->write_routes_config();
                                    $this->_install->update_config_installed($encryptionKey);
                                }
                            }

                            $this->data['step'] = 5;
                        } else {
                            $this->data['step'] = 2;
                            $this->data['purchase_error'] = "SQL not found";
                        }
                    } else {
                        $this->data['step'] = 2;
                        $this->data['purchase_error'] = $purchaseCode->message;
                    }
                } else {
                    $this->data['step'] = 4;
                }
            }
        }

        echo view('install/index', $this->data);
    }

    public function purchase_validation($purchaseCode)
    {
        if ($purchaseCode != "") {
            $array['purchase_username'] = $this->request->getPost('purchase_username');
            $array['purchase_code'] = $purchaseCode;
            $apiResult = $this->_install->call_CurlApi($array);
            if (!empty($apiResult) && $apiResult->status == false) {
                $this->validation->setRule("purchase_validation", $apiResult->message);
                return false;
            }

            return true;
        }

        return null;
    }

    public function purchase_code_verification()
    {
        $file = APPPATH . 'config/purchase_key.php';
        @chmod($file, FILE_WRITE_MODE);
        $purchase = file_get_contents($file);
        $purchase = json_decode($purchase);

        $array = [];
        if (is_array($purchase)) {
            $array['purchase_username'] = trim((string) $purchase[0]);
            $array['purchase_code'] = trim((string) $purchase[1]);
        }

        $array['sys_install'] = true;
        return $this->_install->call_CurlApi($array);
    }
}
