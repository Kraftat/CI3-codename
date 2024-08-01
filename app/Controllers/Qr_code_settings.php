<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\SchoolModel;
/**
 * @package : Ramom School QR Attendance
 * @version : 2.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Qr_code_settings.php
 * @copyright : Reserved RamomCoder Team
 */
class Qr_code_settings extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\SchoolModel
     */
    public $school;

    public $frontendModel;

    public $input;

    public $load;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->school = new \App\Models\SchoolModel();
    }

    public function index()
    {
        // check access permission
        if (!get_permission('frontend_setting', 'is_view')) {
            access_denied();
        }

        $branchID = $this->frontendModel->getBranchID();
        if ($_POST !== []) {
            $branchId = $this->request->getPost('branch_id');
            return redirect()->to(base_url('qrcode_attendance/setting?branch_id=' . $branchId));
        }

        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/jquery-asColorPicker-master/css/asColorPicker.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asColor.js', 'vendor/jquery-asColorPicker-master/libs/jquery-asGradient.js', 'vendor/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js']];
        $this->data['branch_id'] = $branchID;
        $this->data['setting'] = $this->frontendModel->get('front_cms_setting', ['branch_id' => $branchID], true);
        $this->data['title'] = translate('frontend');
        $this->data['sub_page'] = 'frontend/setting';
        $this->data['main_menu'] = 'frontend';
        echo view('layout/index', $this->data);
        return null;
    }
}
