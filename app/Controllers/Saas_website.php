<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SaasModel;
use App\Models\SaasEmailModel;
use App\Models\ApplicationModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


/**
 * @package : Ramom school management system (Saas)
 * @version : 3.1
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Saas.php
 * @copyright : Reserved RamomCoder Team
 */
class Saas_website extends MyController
{
    /**
     * @var mixed
     */
    public $Recaptcha;

    /**
     * @var mixed
     */
    public $Html2pdf;

    public $html2pdf;

    public $mailer;

    public $email;

    /**
     * @var SaasModel
     */
    public $saasModel;

    /**
     * @var SaasEmailModel
     */
    public $saasEmailModel;

    public $applicationModel;

    public $validation;

    public $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Load the necessary models
        $this->saasModel = new SaasModel();
        $this->saasEmailModel = new SaasEmailModel();
        $this->applicationModel = new ApplicationModel();

        // Load the validation service
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $this->data['getSettings'] = $this->saasModel->getSettings();
        if ($this->data['getSettings']->captcha_status == 1) {
            $this->Recaptcha = service('recaptcha', ['site_key' => $this->data['getSettings']->recaptcha_site_key, 'secret_key' => $this->data['getSettings']->recaptcha_secret_key]);
            $this->data['Recaptcha'] = ['widget' => $this->Recaptcha->getWidget(), 'script' => $this->Recaptcha->getScriptTag()];
        }

        $this->data['featureslist'] = $this->saasModel->getFeaturesList();
        $this->data['faqs'] = $this->saasModel->getFAQList();
        $this->data['getPeriodType'] = $this->saasModel->getPeriodTypeWebsite();
        $this->data['getPackageList'] = $this->saasModel->getPackageListWebsite();
        $this->data['applicationModel'] = $this->applicationModel; // Pass the applicationModel to the view
        echo view('saas_website/index', $this->data);
    }

    public function register()
    {
        if ($_POST !== []) {
            $this->validation->setRules([
                'school_name' => ["label" => translate('school_name'), "rules" => 'trim|required'],
                'country' => ["label" => translate('country'), "rules" => 'trim|required'],
                'school_address' => ["label" => translate('school_address'), "rules" => 'trim|required'],
                'admin_name' => ["label" => translate('admin_name'), "rules" => 'trim|required'],
                'gender' => ["label" => translate('gender'), "rules" => 'trim|required'],
                'admin_phone' => ["label" => translate('phone'), "rules" => 'trim|required|numeric'],
                'admin_email' => ["label" => translate('email'), "rules" => 'trim|required|valid_email|callback_unique_email'],
                'admin_username' => ["label" => translate('admin_username'), "rules" => 'trim|required|callback_unique_username'],
                'admin_password' => ["label" => translate('password'), "rules" => 'trim|required'],
                "logo_file" => ["label" => "School Logo", "rules" => "callback_handle_upload[logo_file]"],
                'retype_admin_password' => ["label" => translate('retype_password'), "rules" => 'trim|required|matches[admin_password]']
            ]);

            $getSettings = $this->saasModel->getSettings('captcha_status,terms_status');
            if ($getSettings->captcha_status == 1) {
                $this->validation->setRules(['g-recaptcha-response' => ["label" => 'Captcha', "rules" => 'trim|required']]);
            }

            if ($getSettings->terms_status == 1) {
                $this->validation->setRules(['terms_cb' => ["label" => 'Agreement', "rules" => 'trim|required']]);
            }

            if ($this->validation->run() == true) {
                $packageId = $this->request->getPost('package_id');
                $registrationId = $this->request->getPost('registration_id');
                do {
                    $referenceNo = mt_rand(100000, 999999);
                    $refenceStatus = $this->saasModel->checkReferenceNo($referenceNo);
                } while ($refenceStatus);

                // Check subscription payment status
                $getPlanDetails = $this->saasModel->getPackageDetails($packageId);
                // Check package status
                if (empty($getPlanDetails)) {
                    $array = ['status' => 'error', 'message' => translate('invalid_package'), 'title' => translate('error')];
                    echo json_encode($array);
                    exit;
                }

                // Combine country code and phone number
                $adminPhone = $this->request->getPost('admin_phone');
                $phoneCountryCode = $this->request->getPost('phone_country_code');
                $contactNumber = isset($phoneCountryCode) && !empty($phoneCountryCode) ? $phoneCountryCode . $adminPhone : $adminPhone;
                // Save all register information in the database
                $arrayData = [
                    'package_id' => $packageId,
                    'reference_no' => $referenceNo,
                    'school_name' => $this->request->getPost('school_name'),
                    'country' => $this->request->getPost('country'),
                    'address' => $this->request->getPost('school_address'),
                    'admin_name' => $this->request->getPost('admin_name'),
                    'gender' => $this->request->getPost('gender'),
                    'contact_number' => $contactNumber,
                    'email' => $this->request->getPost('admin_email'),
                    'username' => $this->request->getPost('admin_username'),
                    'password' => $this->request->getPost('admin_password'),
                    'message' => $this->request->getPost('message'),
                    'logo' => $this->saasModel->fileupload('logo_file', './uploads/saas_school_logo/'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->db->table('saas_school_register')->insert($arrayData);
                $regID = $this->db->insertID();
                // Check if the user is coming from registration_pending
                if (!empty($registrationId)) {
                    $this->db->table('saas_registration_form')->where('registration_id', $registrationId)->update(['status' => 'converted', 'date_of_conversion' => date('Y-m-d H:i:s')]);
                }

                // Send email to submit school registered
                $arrayData['plan_name'] = $getPlanDetails->name;
                $arrayData['date'] = _d($arrayData['created_at']);
                $arrayData['fees_amount'] = number_format($getPlanDetails->price - $getPlanDetails->discount, 2, '.', '');
                $arrayData['invoice_url'] = base_url('subscription_review/' . $arrayData['reference_no']);
                $arrayData['payment_url'] = base_url('saas_payment/index/' . $arrayData['reference_no']);
                $this->saasEmailModel->sentSchoolRegister($arrayData);
                if ($getPlanDetails->free_trial == 1) {
                    $this->db->table('saas_school_register')->where('id', $regID)->update(['payment_status' => 1]);
                    $url = base_url('subscription_review/' . $arrayData['reference_no']);
                    // Automatic subscription approval
                    $getSettings = $this->saasModel->getSettings();
                    if ($getSettings->automatic_approval == 1) {
                        $this->saasModel->automaticSubscriptionApproval($regID, $this->data['global_config']['currency'], $this->data['global_config']['currency_symbol']);
                    }
                } else {
                    $url = base_url("saas_payment/index/" . $arrayData['reference_no']);
                }

                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    public function handle_upload($str, $fields)
    {
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = pathinfo((string) $fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setRule('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > 2097152) {
                    $this->validation->setRule('handle_upload', translate('file_size_shoud_be_less_than') . " 2048KB.");
                    return false;
                }
            } else {
                $this->validation->setRule('handle_upload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }

    public function unique_username($username)
    {
        $query = $this->db->table('login_credential')->where('username', $username)->get();
        if ($query->getNumRows() > 0) {
            $this->validation->setRule("unique_username", translate('username_has_already_been_used'));
            return false;
        }

        return true;
    }

    public function getPlanDetails()
    {
        if ($_POST !== []) {
            if (is_loggedin()) {
                echo json_encode(['islogin' => true, 'message' => "Logout first, Then try again.", 'title' => translate('error')]);
                exit;
            }

            $packageId = $this->request->getPost('package_id');
            $getPlanDetails = $this->saasModel->getPackageDetails($packageId);
            $expiryDate = $this->saasModel->getPlanExpiryDate($packageId);
            $html = "<li>" . translate('plan') . " " . translate('name') . "<span>" . $getPlanDetails->name . "</span></li>\r\n            <li>" . translate('start_date') . "<span>" . date('d-M-Y') . "</span></li>\r\n            <li>" . translate('expiry_date') . "<span>" . $expiryDate . "</span></li>\r\n            <li class='total-costs'>" . translate('total_cost') . "<span>" . ($getPlanDetails->free_trial == 1 ? translate('free') : $this->data['global_config']['currency_symbol'] . number_format($getPlanDetails->price - $getPlanDetails->discount, 2, '.', '')) . "</span></li>";
            $recaptchaStatus = 0;
            $getSettings = $this->saasModel->getSettings('captcha_status');
            if ($getSettings->captcha_status == 1) {
                $recaptchaStatus = $getSettings->captcha_status;
            }

            echo json_encode(['html' => $html, 'Recaptcha' => $recaptchaStatus]);
        }
    }

    public function purchase_complete($referenceNo = '')
    {
        if (!empty($referenceNo)) {
            $schoolRegDetails = $this->saasModel->getSchoolRegDetails($referenceNo);
            if (empty($schoolRegDetails['id'])) {
                set_alert('error', "This pages was not found.");
                return redirect()->back();
            }

            $this->data['schoolRegDetails'] = $schoolRegDetails;
            echo view('saas_website/purchase_complete', $this->data);
        }

        return null;
    }

    public function invoicePDFDownload($referenceNo = '')
    {
        if (!empty($referenceNo)) {
            $schoolRegDetails = $this->saasModel->getSchoolRegDetails($referenceNo);
            if (empty($schoolRegDetails['id'])) {
                set_alert('error', "This pages was not found.");
                return redirect()->back();
            }

            $this->data['schoolRegDetails'] = $schoolRegDetails;
            $html = view('saas_website/pdfPrint', $this->data, true);
            $pdfFilePath = sprintf('invoice_%s.pdf', $referenceNo);
            $this->Html2pdf = service('html2pdf');
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->Output($pdfFilePath, "D");
        }

        return null;
    }

    public function getTermsConditions()
    {
        $getSettings = $this->saasModel->getSettings();
        echo "<p>" . nl2br($getSettings->terms_and_conditions) . "</p>";
    }

    public function send_email()
    {
        if ($_POST !== []) {
            $this->validation->setRules([
                'name' => ["label" => 'Name', "rules" => 'trim|required'],
                'email' => ["label" => 'Email', "rules" => 'trim|required|valid_email'],
                'mobile' => ["label" => 'Mobile', "rules" => 'trim|required|numeric'],
                'subject' => ["label" => 'Subject', "rules" => 'trim|required'],
                'message' => ["label" => 'Message', "rules" => 'trim|required']
            ]);

            if ($this->validation->run() == true) {
                $getSettings = $this->saasModel->getSettings();
                $name = $this->request->getPost('name');
                $email = $this->request->getPost('email');
                $mobile = $this->request->getPost('mobile');
                $subject = $this->request->getPost('subject');
                $message = $this->request->getPost('message');
                $msg = '<h3>Sender Information</h3>';
                $msg .= '<br><br><b>Name: </b> ' . $name;
                $msg .= '<br><br><b>Email: </b> ' . $email;
                $msg .= '<br><br><b>Phone: </b> ' . $mobile;
                $msg .= '<br><br><b>Subject: </b> ' . $subject;
                $msg .= '<br><br><b>Message: </b> ' . $message;
                $data = ['branch_id' => 9999, 'recipient' => $getSettings->receive_contact_email, 'subject' => 'Contact Form Email', 'message' => $msg];
                if ($this->mailer->send($data)) {
                    session()->setFlashdata('msg_success', 'Message Successfully Sent. We will contact you shortly.');
                } else {
                    session()->setFlashdata('msg_error', $this->email->print_debugger());
                }

                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function submit_registration_form()
    {
        $this->validation = service('validation');
        // Validation rules
        $this->validation->setRules([
            'admin_name' => ["label" => 'Administrator Name', "rules" => 'required'],
            'organisation_type' => ["label" => 'Organisation Type', "rules" => 'required'],
            'school_name' => ["label" => 'School Name', "rules" => 'required'],
            'estimated_students' => ["label" => 'Estimated Number of Students', "rules" => 'required|numeric'],
            'admin_email' => ["label" => 'Email', "rules" => 'required|valid_email'],
            'admin_phone' => ["label" => 'Phone Number', "rules" => 'required'],
            'phone_country_code' => ["label" => 'Phone Country Code', "rules" => 'required'],
            'role_in_school' => ["label" => 'Role in School', "rules" => 'required']
        ]);

        if ($this->request->getPost('organisation_type') === 'group') {
            $this->validation->setRules(['number_of_branches' => ["label" => 'Number of Branches', "rules" => 'required|numeric']]);
        }

        if ($this->validation->run() === false) {
            // If validation fails, send errors back to the modal form
            $errors = $this->validation->getErrors();
            echo json_encode(['status' => false, 'message' => $errors]);
        } else {
            // Prepare data for insertion
            $data = [
                'registration_id' => substr(uniqid(), 6, 5),
                'name' => $this->request->getPost('admin_name'),
                'organization_type' => $this->request->getPost('organisation_type'),
                'school_name' => $this->request->getPost('school_name'),
                'number_of_branches' => $this->request->getPost('number_of_branches'),
                'number_of_students' => $this->request->getPost('estimated_students'),
                'email' => $this->request->getPost('admin_email'),
                'phone_country_code' => $this->request->getPost('phone_country_code'),
                'phone_number' => $this->request->getPost('admin_phone'),
                'role' => $this->request->getPost('role_in_school'),
                'status' => 'new',
                'ip_address' => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            // Insert data into the database
            $inserted = $this->saasModel->insert_registration_data($data);
            if ($inserted) {
                echo json_encode(['status' => true, 'message' => 'Request submitted successfully. We will contact you soon.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Failed to submit registration. Please try again.']);
            }
        }
    }

    public function registration_pending($registrationId)
    {
        $this->data['getSettings'] = $this->saasModel->getSettings();
        if ($this->data['getSettings']->captcha_status == 1) {
            $this->Recaptcha = service('recaptcha', ['site_key' => $this->data['getSettings']->recaptcha_site_key, 'secret_key' => $this->data['getSettings']->recaptcha_secret_key]);
            $this->data['Recaptcha'] = ['widget' => $this->Recaptcha->getWidget(), 'script' => $this->Recaptcha->getScriptTag()];
        } else {
            $this->data['Recaptcha'] = ['widget' => '', 'script' => ''];
        }

        $this->data['registration'] = $this->saasModel->getRegistrationRequestById($registrationId);
        if (empty($this->data['registration']) || empty($this->data['registration']['package_id']) || $this->data['registration']['status'] != 'pending') {
            $this->data['applicationModel'] = $this->applicationModel; // Pass the applicationModel to the view
            $this->response->setStatusCode(404);
            echo view('errors/error_404_message', $this->data);
            return;
        }

        // Fetch package details
        $packageId = $this->data['registration']['package_id'];
        $this->data['package'] = $this->saasModel->getPackageDetails($packageId);
        // Calculate expiry date using the registration created_at date
        $createdAt = date('Y-m-d', strtotime((string) $this->data['registration']['created_at']));
        $expiryDate = $this->saasModel->getPlanExpiryDate($packageId);
        $this->data['created_at'] = date('d-M-Y', strtotime($createdAt));
        $this->data['expiry_date'] = $expiryDate;
        $this->data['applicationModel'] = $this->applicationModel; // Pass the applicationModel to the view
        echo view('saas_website/registration_pending', $this->data);
    }

    public function unique_email($email)
    {
        $query = $this->db->table('saas_school_register')->where('email', $email)->get();
        if ($query->getNumRows() > 0) {
            $this->validation->setRule('unique_email', translate('email_has_already_been_used'));
            return false;
        }

        return true;
    }
}
