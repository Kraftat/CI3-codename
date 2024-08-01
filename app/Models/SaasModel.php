<?php
//fixed
namespace App\Models;

use CodeIgniter\Model;

class SaasModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // get package list
    public function getPackageList()
    {
        $builder = $this->db->table('saas_package');
        return $builder->get()->getResult();
    }

    // plan package save and update function
    public function packageSave($data)
    {
        $builder = $this->db->table('saas_package');
        $module = $this->request->getPost('modules');
        $period_type = $data['period_type'];
        $insertData = [
            'name' => $data['name'],
            'price' => empty($data['price']) ? 0 : $data['price'],
            'recommended' => empty($data['recommended']) ? 0 : 1,
            'discount' => empty($data['discount']) ? 0 : $data['discount'],
            'student_limit' => $data['student_limit'],
            'staff_limit' => $data['staff_limit'],
            'teacher_limit' => $data['teacher_limit'],
            'parents_limit' => $data['parents_limit'],
            'free_trial' => empty($data['free_trial']) ? 0 : 1,
            'show_onwebsite' => isset($data['show_website']) ? 1 : 0,
            'status' => isset($data['package_status']) ? 1 : 0,
            'period_type' => $period_type,
            'period_value' => $period_type == 1 ? 0 : $data['period_value'],
            'permission' => json_encode($module)
        ];
        $id = $this->request->getPost('id');
        if (empty($id)) {
            $insertData['created_at'] = date('Y-m-d H:i:s');
            $builder->insert($insertData);
        } else {
            $insertData['updated_at'] = date('Y-m-d H:i:s');
            $builder->where('id', $id);
            $builder->update($insertData);
        }
        return $this->db->affectedRows() > 0;
    }

    public function getPeriodType()
    {
        $arrayPeriod = ['' => translate('select'), '2' => translate('days'), '3' => translate('monthly'), '4' => translate('yearly'), '1' => translate('lifetime')];
        return $arrayPeriod;
    }

    public function getPeriodTypeWebsite()
    {
        $arrayPeriod = ['2' => translate('days'), '3' => translate('months'), '4' => translate('years'), '1' => translate('lifetime')];
        return $arrayPeriod;
    }

    public function getSaasPackage()
    {
        $builder = $this->db->table('saas_package');
        $builder->select('id, name');
        $builder->where('status', 1);
        $result = $builder->get()->getResult();
        $arrayData = ['' => translate('select')];
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->name;
        }
        return $arrayData;
    }

    public function getSubscriptionsExpiredNotification()
    {
        $message = "";
        $sql = "SELECT `expired_alert`,`expired_alert_days`,`expired_alert_message`,`expired_message` FROM `saas_settings` WHERE `id` = '1'";
        $settings = $this->db->query($sql)->getRow();
        if (!empty($settings)) {
            if ($settings->expired_alert == 1) {
                $days = $settings->expired_alert_days;
                $date = date('Y-m-d', strtotime("+ {$days} days"));
                $school_id = get_loggedin_branch_id();
                $sql = "SELECT `expire_date` FROM `saas_subscriptions` WHERE date(`expire_date`) <= " . $this->db->escape($date) . " AND `school_id` = " . $this->db->escape($school_id);
                $subscriptions = $this->db->query($sql)->getRow();
                if (!empty($subscriptions)) {
                    if (date("Y-m-d", strtotime($subscriptions->expire_date)) < date("Y-m-d")) {
                        return $settings->expired_message;
                    }
                    $date1 = new \DateTime(date("Y-m-d"));
                    $date2 = new \DateTime($subscriptions->expire_date);
                    $diff = $date2->diff($date1)->format("%a");
                    $days = intval($diff);
                    $message = $settings->expired_alert_message;
                    $message = str_replace('{days}', $days, $message);
                }
            }
        }
        return $message;
    }

    public function getSchool($id)
    {
        $builder = $this->db->table('branch');
        $builder->select('branch.*,saas_subscriptions.package_id,saas_subscriptions.expire_date,saas_subscriptions.id as subscriptions_id,saas_subscriptions.upgrade_lasttime');
        $builder->join('saas_subscriptions', 'saas_subscriptions.school_id = branch.id', 'inner');
        $builder->where('branch.id', $id);
        return $builder->get()->getRow();
    }

    public function getSubscriptionList($type = '')
    {
        $builder = $this->db->table('branch');
        $builder->select('branch.id as bid,branch.name as branch_name,branch.status,upgrade_lasttime,email,mobileno,saas_subscriptions.expire_date,sp.name as package_name,sp.period_type,saas_subscriptions.created_at,sp.price,free_trial,sp.discount');
        $builder->join('saas_subscriptions', 'saas_subscriptions.school_id = branch.id', 'inner');
        $builder->join('saas_package as sp', 'sp.id = saas_subscriptions.package_id', 'left');
        if (preg_match('/^[1-9][0-9]*$/', $type)) {
            if ($type == 1) {
                $builder->where('branch.status', 1);
                $builder->where("date(saas_subscriptions.expire_date) >", date("Y-m-d"));
            }
            if ($type == 2) {
                $builder->where('branch.status', 0);
            }
            if ($type == 3) {
                $builder->where("date(saas_subscriptions.expire_date) <", date("Y-m-d"));
            }
        }
        return $builder->get()->getResult();
    }

    public function getPendingRequest($start = '', $end = '')
    {
        $builder = $this->db->table('saas_school_register');
        $builder->select('saas_school_register.*,sp.name as package_name,IFNULL(sp.price-sp.discount, 0) as plan_price');
        $builder->join('saas_package as sp', 'sp.id = saas_school_register.package_id', 'left');
        if (!empty($start) && !empty($end)) {
            $builder->where('date(saas_school_register.created_at) >=', $start);
            $builder->where('date(saas_school_register.created_at) <=', $end);
        }
        return $builder->get()->getResult();
    }

    public function checkSubscriptionValidity($school_id = "")
    {
        if (!is_superadmin_loggedin()) {
            if (empty($school_id)) {
                $school_id = get_loggedin_branch_id();
            }
            $sql = "SELECT `id`,`expire_date` FROM `saas_subscriptions` WHERE `school_id` = " . $this->db->escape($school_id);
            $subscriptions = $this->db->query($sql)->getRow();
            if (empty($subscriptions)) {
                return true;
            }
            if ($subscriptions->expire_date == "") {
                return true;
            }
            if (date("Y-m-d", strtotime($subscriptions->expire_date)) < date("Y-m-d")) {
                set_alert('error', translate('subscription_expired'));
                return false;
            }
        }
        return true;
    }

    public function schoolSave($data)
    {
        $builder = $this->db->table('branch');
        $arrayBranch = [
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'country' => $data['country'],
            'city' => $data['city'],
            'state' => $data['state'],
            'address' => $data['address'],
            'status' => $data['state_id']
        ];
        if (!isset($data['branch_id'])) {
            $builder->insert($arrayBranch);
            $id = $this->db->insertID();
        } else {
            $id = $data['branch_id'];
            $builder->where('id', $data['branch_id']);
            $builder->update($arrayBranch);
        }
        $file_upload = false;
        if (isset($_FILES["logo_file"]) && !empty($_FILES['logo_file']['name'])) {
            $fileInfo = pathinfo($_FILES["logo_file"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["logo_file"]["tmp_name"], "uploads/app_image/logo-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["text_logo"]) && !empty($_FILES['text_logo']['name'])) {
            $fileInfo = pathinfo($_FILES["text_logo"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["text_logo"]["tmp_name"], "uploads/app_image/logo-small-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["print_file"]) && !empty($_FILES['print_file']['name'])) {
            $fileInfo = pathinfo($_FILES["print_file"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["print_file"]["tmp_name"], "uploads/app_image/printing-logo-" . $img_name);
            $file_upload = true;
        }
        if (isset($_FILES["report_card"]) && !empty($_FILES['report_card']['name'])) {
            $fileInfo = pathinfo($_FILES["report_card"]["name"]);
            $img_name = $id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["report_card"]["tmp_name"], "uploads/app_image/report-card-logo-" . $img_name);
            $file_upload = true;
        }
        return $id;
    }

    public function saveSchoolSaasData($package_id = '', $schoolID = '', $paymentData = [])
    {
        $builder = $this->db->table('global_settings');
        $globalSettings = $builder->get()->getRow();
        $currency = $globalSettings ? $globalSettings->currency : 'USD';

        $builder = $this->db->table('saas_package');
        $saasPackage = $builder->get()->getRow();
        $periodValue = $saasPackage->period_value;
        $dateAdd = '';
        if ($saasPackage->period_type == 2) {
            $dateAdd = "+{$periodValue} days";
        }
        if ($saasPackage->period_type == 3) {
            $dateAdd = "+{$periodValue} month";
        }
        if ($saasPackage->period_type == 4) {
            $dateAdd = "+{$periodValue} year";
        }
        if (!empty($dateAdd)) {
            $dateAdd = date('Y-m-d', strtotime($dateAdd));
        }

        $builder = $this->db->table('saas_subscriptions');
        $arraySubscriptions = [
            'package_id' => $package_id,
            'school_id' => $schoolID,
            'expire_date' => $dateAdd
        ];
        $builder->insert($arraySubscriptions);
        $subscriptionsID = $this->db->insertID();

        $builder = $this->db->table('saas_subscriptions_transactions');
        $arraySubscriptionsTransactions = [
            'subscriptions_id' => $subscriptionsID,
            'package_id' => $package_id,
            'payment_id' => empty($paymentData['txn_id']) ? substr(app_generate_hash(), 3, 8) : $paymentData['txn_id'],
            'amount' => $saasPackage->price,
            'discount' => $saasPackage->discount,
            'payment_method' => empty($paymentData['payment_method']) ? 1 : $paymentData['payment_method'],
            'purchase_date' => date("Y-m-d"),
            'expire_date' => $dateAdd,
            'currency' => $currency
        ];
        $builder->insert($arraySubscriptionsTransactions);

        $permission = json_decode($saasPackage->permission, true);
        $modules_manage = [];
        $builder = $this->db->table('permission_modules');
        $getPermissions = $builder->get()->getResult();
        foreach ($getPermissions as $key => $value) {
            $modules_manage[] = [
                'modules_id' => $value->id,
                'isEnabled' => in_array($value->id, $permission) ? 1 : 0,
                'branch_id' => $schoolID
            ];
        }
        $builder = $this->db->table('modules_manage');
        $builder->insertBatch($modules_manage);
    }

    public function getPackageListWebsite($website = true)
    {
        $builder = $this->db->table('saas_package');
        if ($website) {
            $builder->where('show_onwebsite', 1);
        }
        $builder->where('status', 1);
        $builder->orderBy("free_trial desc, id asc");
        return $builder->get()->getResult();
    }

    public function getFAQList()
    {
        $builder = $this->db->table('saas_cms_faq_list');
        $builder->orderBy("id", "asc");
        return $builder->get()->getResult();
    }

    public function getFeaturesList()
    {
        $builder = $this->db->table('saas_cms_features');
        return $builder->get()->getResult();
    }

    public function getSettings($sel = "*")
    {
        $builder = $this->db->table('saas_settings');
        $builder->select($sel);
        $builder->where('id', 1);
        return $builder->get()->getRow();
    }

    public function getPackageDetails($plan_id = '')
    {
        $builder = $this->db->table('saas_package');
        $builder->select('period_value, period_type, name, price, discount, free_trial');
        $builder->where('status', 1);
        $builder->where('id', $plan_id);
        return $builder->get()->getRow();
    }

    public function getSchoolRegDetails($reference_no = '')
    {
        $builder = $this->db->table('saas_school_register');
        $builder->select('saas_school_register.*, saas_package.period_value, saas_package.period_type, saas_package.name, saas_package.price, saas_package.discount, saas_package.free_trial');
        $builder->join('saas_package', 'saas_package.id = saas_school_register.package_id', 'inner');
        $builder->where('saas_school_register.reference_no', $reference_no);
        return $builder->get()->getRowArray();
    }

    public function checkReferenceNo($ref_no)
    {
        $builder = $this->db->table('saas_school_register');
        $builder->select("id");
        $builder->where("reference_no", $ref_no);
        $result = $builder->get()->getRowArray();
        return !empty($result);
    }

    public function getPlanExpiryDate($plan_id = '')
    {
        $formats = 'd-M-Y';
        $get_format = get_global_setting('date_format');
        if ($get_format != '') {
            $formats = $get_format;
        }
        $getPlanDetails = $this->getPackageDetails($plan_id);
        $expiryDate = "";
        $period_value = $getPlanDetails->period_value;
        if ($getPlanDetails->period_type == 1) {
            $expiryDate = translate('lifetime');
        } elseif ($getPlanDetails->period_type == 2) {
            $expiryDate = date($formats, strtotime("+{$period_value} day"));
        } elseif ($getPlanDetails->period_type == 3) {
            $expiryDate = date($formats, strtotime("+{$period_value} month"));
        } elseif ($getPlanDetails->period_type == 4) {
            $expiryDate = date($formats, strtotime("+{$period_value} year"));
        }
        return $expiryDate;
    }

    public function getPendingSchool($id)
    {
        $builder = $this->db->table('saas_school_register');
        $builder->select('*');
        $builder->where('id', $id);
        $builder->where('status !=', 1);
        return $builder->get()->getRow();
    }

    public function fileupload($media_name, $upload_path = "", $old_file = '', $enc = true)
    {
        if (file_exists($_FILES[$media_name]['tmp_name']) && !$_FILES[$media_name]['error'] == UPLOAD_ERR_NO_FILE) {
            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = '*';
            if ($enc == true) {
                $config['encrypt_name'] = true;
            } else {
                $config['overwrite'] = true;
            }
            $this->upload->initialize($config);
            if ($this->upload->do_upload($media_name)) {
                if (!empty($old_file)) {
                    $file_name = $config['upload_path'] . $old_file;
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                }
                return $this->upload->data('file_name');
            }
        }
        return null;
    }

    public function save_faq($data)
    {
        $builder = $this->db->table('saas_cms_faq_list');
        $faq_data = [
            'title' => $data['title'],
            'description' => $data['description']
        ];
        if (isset($data['faq_id']) && !empty($data['faq_id'])) {
            $builder->where('id', $data['faq_id']);
            $builder->update($faq_data);
        } else {
            $builder->insert($faq_data);
        }
    }

    public function save_features($data)
    {
        $builder = $this->db->table('saas_cms_features');
        $feature_data = [
            'title' => $data['title'],
            'icon' => $data['icon'],
            'description' => $data['description']
        ];
        if (isset($data['feature_id']) && !empty($data['feature_id'])) {
            $builder->where('id', $data['feature_id']);
            $builder->update($feature_data);
        } else {
            $builder->insert($feature_data);
        }
    }

    public function getTransactions($start = '', $end = '')
    {
        $builder = $this->db->table('saas_subscriptions_transactions as tr');
        $builder->select('tr.*,payment_types.name as payvia,branch.name as school_name,branch.id as bid,saas_package.name as plan_name');
        $builder->join('saas_subscriptions as ss', 'ss.id = tr.subscriptions_id', 'inner');
        $builder->join('saas_package', 'saas_package.id = tr.package_id', 'left');
        $builder->join('branch', 'branch.id = ss.school_id', 'inner');
        $builder->join('payment_types', 'payment_types.id = tr.payment_method', 'left');
        if (!empty($start) && !empty($end)) {
            $builder->where('date(tr.created_at) >=', $start);
            $builder->where('date(tr.created_at) <=', $end);
        }
        $builder->orderBy('tr.id', 'ASC');
        return $builder->get()->getResult();
    }

    public function getSectionsPaymentMethod()
    {
        $branchID = 9999;
        $builder = $this->db->table('payment_config');
        $builder->where('branch_id', $branchID);
        $builder->select('paypal_status,stripe_status,payumoney_status,paystack_status,razorpay_status,sslcommerz_status,jazzcash_status,midtrans_status,flutterwave_status,paytm_status,toyyibpay_status,payhere_status,tap_status');
        $status = $builder->get()->getRowArray();
        $payvia_list = ['' => translate('select_payment_method')];
        if ($status['paypal_status'] == 1) {
            $payvia_list['paypal'] = 'Paypal';
        }
        if ($status['stripe_status'] == 1) {
            $payvia_list['stripe'] = 'Stripe';
        }
        if ($status['payumoney_status'] == 1) {
            $payvia_list['payumoney'] = 'PayUmoney';
        }
        if ($status['paystack_status'] == 1) {
            $payvia_list['paystack'] = 'Paystack';
        }
        if ($status['razorpay_status'] == 1) {
            $payvia_list['razorpay'] = 'Razorpay';
        }
        if ($status['sslcommerz_status'] == 1) {
            $payvia_list['sslcommerz'] = 'sslcommerz';
        }
        if ($status['jazzcash_status'] == 1) {
            $payvia_list['jazzcash'] = 'Jazzcash';
        }
        if ($status['midtrans_status'] == 1) {
            $payvia_list['midtrans'] = 'Midtrans';
        }
        if ($status['flutterwave_status'] == 1) {
            $payvia_list['flutterwave'] = 'Flutter Wave';
        }
        if ($status['paytm_status'] == 1) {
            $payvia_list['paytm'] = 'Paytm';
        }
        if ($status['toyyibpay_status'] == 1) {
            $payvia_list['toyyibPay'] = 'toyyibPay';
        }
        if ($status['payhere_status'] == 1) {
            $payvia_list['payhere'] = 'Payhere';
        }
        if ($status['tap_status'] == 1) {
            $payvia_list['tap'] = 'Tap Payments';
        }
        return $payvia_list;
    }

    public function automaticSubscriptionApproval($saas_register_id = '', $currency = 'USD', $symbol = '$')
    {
        $getSchool = $this->getPendingSchool($saas_register_id);
        if (!empty($getSchool)) {
            $current_PackageID = $getSchool->package_id;
            $builder = $this->db->table('saas_school_register');
            $builder->where('id', $saas_register_id);
            $builder->update(['status' => 1, 'payment_status' => 1, 'date_of_approval' => date('Y-m-d H:i:s')]);
            $arrayBranch = [
                'name' => $getSchool->school_name,
                'school_name' => $getSchool->school_name,
                'email' => $getSchool->email,
                'mobileno' => $getSchool->contact_number,
                'currency' => $currency,
                'symbol' => $symbol,
                'city' => "",
                'state' => "",
                'address' => $getSchool->address,
                'status' => 1
            ];
            $builder = $this->db->table('branch');
            $builder->insert($arrayBranch);
            $schoolID = $this->db->insertID();
            $inser_data1 = [
                'branch_id' => $schoolID,
                'name' => $getSchool->admin_name,
                'sex' => $getSchool->gender == 1 ? 'male' : 'female',
                'mobileno' => $getSchool->contact_number,
                'joining_date' => date("Y-m-d"),
                'email' => $getSchool->email
            ];
            $inser_data2 = [
                'username' => $getSchool->username,
                'role' => 2
            ];
            $inser_data1['staff_id'] = substr(app_generate_hash(), 3, 7);
            $builder = $this->db->table('staff');
            $builder->insert($inser_data1);
            $staffID = $this->db->insertID();
            $inser_data2['active'] = 1;
            $inser_data2['user_id'] = $staffID;
            $inser_data2['password'] = $this->appLib->passHashed($getSchool->password);
            $builder = $this->db->table('login_credential');
            $builder->insert($inser_data2);
            if (!empty($getSchool->logo)) {
                copy('./uploads/saas_school_logo/' . $getSchool->logo, "./uploads/app_image/logo-small-{$schoolID}.png");
            }
            $paymentData = [];
            if (!empty($getSchool->payment_data)) {
                if ($getSchool->payment_data == 'olp') {
                    $paymentData['payment_method'] = 15;
                    $paymentData['txn_id'] = $getSchool->reference_no;
                } else {
                    $paymentData = json_decode($getSchool->payment_data, TRUE);
                    $paymentData['payment_method'] = $paymentData['payment_method'];
                    $paymentData['txn_id'] = $paymentData['txn_id'];
                }
            }
            $this->saveSchoolSaasData($current_PackageID, $schoolID, $paymentData);
            $arrayData['email'] = $getSchool->email;
            $arrayData['package_id'] = $getSchool->package_id;
            $arrayData['admin_name'] = $getSchool->admin_name;
            $arrayData['reference_no'] = $getSchool->reference_no;
            $arrayData['school_name'] = $getSchool->school_name;
            $arrayData['login_username'] = $getSchool->username;
            $arrayData['password'] = $getSchool->password;
            $arrayData['subscription_start_date'] = _d(date("Y-m-d"));
            $arrayData['invoice_url'] = base_url('saas_website/purchase_complete/' . $arrayData['reference_no']);
            $this->saas_emailModel->sentSubscriptionApprovalConfirmation($arrayData);
        }
    }

    //fahad test
    public function getRegistrationRequests()
    {
        $builder = $this->db->table('saas_registration_form');
        return $builder->get()->getResultArray();
    }

    // Update the status of a registration
    public function updateRegistrationStatus($id, $status)
    {
        $builder = $this->db->table('saas_registration_form');
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $builder->where('id', $id);
        return $builder->update($data);
    }

    // Delete a registration
    public function deleteRegistration($id)
    {
        return $this->db->table('saas_registration_form')->delete(['id' => $id]);
    }

    public function insert_registration_data($data)
    {
        $builder = $this->db->table('saas_registration_form');
        return $builder->insert($data);
    }

    public function getRegistrationRequestById($registration_id)
    {
        $builder = $this->db->table('saas_registration_form');
        $builder->select('saas_registration_form.*, saas_package.name as package_name');
        $builder->join('saas_package', 'saas_registration_form.package_id = saas_package.id', 'left');
        $builder->where('saas_registration_form.registration_id', $registration_id);
        return $builder->get()->getRowArray();
    }

    public function getFilteredRegistrationRequests($start_date, $end_date, $status = '')
    {
        $builder = $this->db->table('saas_registration_form');
        $builder->select('saas_registration_form.*, saas_package.name as package_name');
        $builder->join('saas_package', 'saas_registration_form.package_id = saas_package.id', 'left');
        $builder->where('saas_registration_form.created_at >=', $start_date);
        $builder->where('saas_registration_form.created_at <=', $end_date);
        if (!empty($status)) {
            $builder->where('saas_registration_form.status', $status);
        }
        return $builder->get()->getResultArray();
    }
}
