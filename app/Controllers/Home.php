<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentFieldsModel;
use App\Models\EmailModel;
use App\Models\TestimonialModel;
use App\Models\GalleryModel;
use App\Models\AdmissionpaymentModel;
use App\Models\CardManageModel;
use App\Models\TimetableModel;
use App\Models\ExamModel;
use App\Models\CertificateModel;
use CodeIgniter\Controller;

/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Home.php
 * @copyright : Reserved RamomCoder Team
 */
class Home extends FrontendController
{
    protected $ciqrcode;
    protected $appLib;
    protected $mailer;
    protected $recaptcha;
    protected $db;

    protected $studentFields;
    protected $email;
    protected $testimonial;
    protected $gallery;
    protected $admissionpayment;
    protected $cardManage;
    protected $timetable;
    protected $exam;
    protected $certificate;

    public function __construct()
    {
        parent::__construct();

        $this->recaptcha = service('recaptcha');
        $this->mailer = service('mailer');
        $this->ciqrcode = service('ciqrcode');
        $this->appLib = service('appLib');
        helper('custom_fields');
        
        $this->studentFields = new StudentFieldsModel();
        $this->email = new EmailModel();
        $this->testimonial = new TestimonialModel();
        $this->gallery = new GalleryModel();
        $this->admissionpayment = new AdmissionpaymentModel();
        $this->cardManage = new CardManageModel();
        $this->timetable = new TimetableModel();
        $this->exam = new ExamModel();
        $this->certificate = new CertificateModel();
    }

    public function index()
    {
        $this->home();
    }

    public function home()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['sliders'] = $this->homeModel->getCmsHome('slider', $branchID, 1, false);
        $this->data['features'] = $this->homeModel->getCmsHome('features', $branchID, 1, false);
        $this->data['wellcome'] = $this->homeModel->getCmsHome('wellcome', $branchID);
        $this->data['teachers'] = $this->homeModel->getCmsHome('teachers', $branchID);
        $this->data['testimonial'] = $this->homeModel->getCmsHome('testimonial', $branchID);
        $this->data['services'] = $this->homeModel->getCmsHome('services', $branchID);
        $this->data['cta_box'] = $this->homeModel->getCmsHome('cta', $branchID);
        $this->data['statistics'] = $this->homeModel->getCmsHome('statistics', $branchID);
        $this->data['page_data'] = $this->homeModel->get('front_cms_home_seo', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/index', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function about()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_about', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/about', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function faq()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_faq', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/faq', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function events()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $urlAlias = $this->data['cms_setting']['url_alias'];
        $getLatestEventList = $this->homeModel->getLatestEventList($branchID);
        $page = html_escape(urldecode($this->request->getGet('page')));
        $page = is_numeric($page) ? (int)$page : 0;

        $totalRecords = count($getLatestEventList);
        $config = [
            'page_query_string' => true,
            'query_string_segment' => 'page',
            'base_url' => base_url() . $urlAlias . '/events',
            'total_rows' => $totalRecords,
            'per_page' => 12,
            'full_tag_open' => '<ul class="pagination justify-content-center">',
            'full_tag_close' => '</ul>',
            'first_link' => '<i class="far fa-angle-double-left"></i>',
            'first_tag_open' => '<li class="previous">',
            'first_tag_close' => '</li>',
            'last_link' => '<i class="far fa-angle-double-right"></i>',
            'last_tag_open' => '<li class="next">',
            'last_tag_close' => '</li>',
            'next_link' => '<i class="far fa-angle-right"></i>',
            'next_tag_open' => '<li class="next">',
            'next_tag_close' => '</li>',
            'prev_link' => '<i class="far fa-angle-left"></i>',
            'prev_tag_open' => '<li class="previous">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="active"><span>',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
        ];
        $this->pagination->initialize($config);
        $conditions = [
            'limit' => $config['per_page'],
            'start' => $page,
        ];
        $this->data['links'] = $this->pagination->create_links();
        $this->data['results'] = $this->homeModel->getLatestEventList($branchID, $conditions);
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_events', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/events', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function event_view($id)
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['event'] = $this->homeModel->get('event', ['id' => $id, 'branch_id' => $branchID, 'status' => 1, 'show_web' => 1], true);
        if (empty($this->data['event']['id'])) {
            return redirect()->back();
        }

        $this->data['page_data'] = $this->homeModel->get('front_cms_events', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/event_view', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function news_view($alias = '')
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['event'] = $this->homeModel->get('front_cms_news_list', ['alias' => $alias, 'branch_id' => $branchID, 'show_web' => 1], true);
        if (empty($this->data['event']['id'])) {
            return redirect()->back();
        }

        $this->data['page_data'] = $this->homeModel->get('front_cms_news', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/news_view', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function teachers()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_teachers', ['branch_id' => $branchID], true);
        $this->data['departments'] = $this->homeModel->get_teacher_departments($branchID);
        $this->data['doctor_list'] = $this->homeModel->get_teacher_list("", $branchID);
        $this->data['main_contents'] = view('home/teachers', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function admission()
    {
        if (!$this->data['cms_setting']['online_admission']) {
            return redirect()->to(site_url('home'));
        }

        $branchID = $this->homeModel->getDefaultBranch();
        $captcha = $this->data['cms_setting']['captcha_status'];
        if ($captcha == 'enable') {
            $this->recaptcha = service('recaptcha', ['site_key' => $this->data['cms_setting']['recaptcha_site_key'], 'secret_key' => $this->data['cms_setting']['recaptcha_secret_key']]);
            $this->data['recaptcha'] = ['widget' => $this->recaptcha->getWidget(), 'script' => $this->recaptcha->getScriptTag()];
        }

        if ($this->request->getMethod() == 'post') {
            $this->validation->setRules(["first_name" => ["label" => "First Name", "rules" => "trim|required"]]);
            $this->validation->setRules(["class_id" => ["label" => "Class", "rules" => "trim|required"]]);
            $this->validation->setRules(["guardian_photo" => ["label" => "Guardian Photo", "rules" => "callback_handle_upload[guardian_photo]"]]);
            $this->validation->setRules(["student_photo" => ["label" => "Student Photo", "rules" => "callback_handle_upload[student_photo]"]]);
            $validationArr = $this->studentFields->getOnlineStatusArr($branchID);
            unset($validationArr[0]);
            foreach ($validationArr as $value) {
                if ($value->status && $value->required) {
                    if ($value->prefix == 'student_email' || $value->prefix == 'guardian_email') {
                        $this->validation->setRules([$value->prefix => ["label" => "Email", "rules" => 'trim|required|valid_email']]);
                    } elseif ($value->prefix == 'student_mobile_no' || $value->prefix == 'guardian_mobile_no') {
                        $this->validation->setRules([$value->prefix => ["label" => "Mobile No", "rules" => 'trim|required|numeric']]);
                    } elseif ($value->prefix == 'student_photo' || $value->prefix == 'guardian_photo' || $value->prefix == 'upload_documents') {
                        if (isset($_FILES[$value->prefix]) && empty($_FILES[$value->prefix]['name'])) {
                            $this->validation->setRule($value->prefix, ucwords(str_replace('_', ' ', $value->prefix)), "required");
                        }
                    } elseif ($value->prefix == 'previous_school_details') {
                        $this->validation->setRules(["school_name" => ["label" => "School Name", "rules" => "trim|required"]]);
                        $this->validation->setRules(["qualification" => ["label" => "Qualification", "rules" => "trim|required"]]);
                    } else {
                        $this->validation->setRule($value->prefix, ucwords(str_replace('_', ' ', $value->prefix)), 'trim|required');
                    }
                }
            }

            if ($captcha == 'enable') {
                $this->validation->setRules(['g-recaptcha-response' => ["label" => 'Captcha', "rules" => 'trim|required']]);
            }

            // custom fields validation rules
            $customFields = getOnlineCustomFields('student', $branchID);
            foreach ($customFields as $fieldsValue) {
                if ($fieldsValue['required']) {
                    $fieldsID = $fieldsValue['id'];
                    $fieldLabel = $fieldsValue['field_label'];
                    $this->validation->setRules(["custom_fields[student][" . $fieldsID . "]" => ["label" => $fieldLabel, "rules" => 'trim|required']]);
                }
            }

            if ($this->validation->run() == true) {
                $admissionDate = empty($_POST['admission_date']) ? "" : date("Y-m-d", strtotime((string)$this->request->getPost('admission_date')));
                $birthday = empty($_POST['birthday']) ? "" : date("Y-m-d", strtotime((string)$this->request->getPost('birthday')));
                $previousDetails = $this->request->getPost('school_name');
                if (!empty($previousDetails)) {
                    $previousDetails = ['school_name' => $this->request->getPost('school_name'), 'qualification' => $this->request->getPost('qualification'), 'remarks' => $this->request->getPost('previous_remarks')];
                    $previousDetails = json_encode($previousDetails);
                } else {
                    $previousDetails = "";
                }

                do {
                    $referenceNo = mt_rand(01, 99999999);
                    $refenceStatus = $this->homeModel->checkAdmissionReferenceNo($referenceNo);
                } while ($refenceStatus);

                $arrayData = [
                    'reference_no' => $referenceNo,
                    'first_name' => $this->request->getPost('first_name'),
                    'last_name' => $this->request->getPost('last_name'),
                    'gender' => $this->request->getPost('gender'),
                    'birthday' => $birthday,
                    'admission_date' => $admissionDate,
                    'religion' => $this->request->getPost('religion'),
                    'caste' => $this->request->getPost('caste'),
                    'blood_group' => $this->request->getPost('blood_group'),
                    'mobile_no' => $this->request->getPost('student_mobile_no'),
                    'mother_tongue' => $this->request->getPost('mother_tongue'),
                    'present_address' => $this->request->getPost('present_address'),
                    'permanent_address' => $this->request->getPost('permanent_address'),
                    'city' => $this->request->getPost('city'),
                    'state' => $this->request->getPost('state'),
                    'category_id' => $this->request->getPost('category'),
                    'email' => $this->request->getPost('student_email'),
                    'student_photo' => $this->uploadImage('images/student', 'student_photo'),
                    'previous_school_details' => $previousDetails,
                    'guardian_name' => $this->request->getPost('guardian_name'),
                    'guardian_relation' => $this->request->getPost('guardian_relation'),
                    'father_name' => $this->request->getPost('father_name'),
                    'mother_name' => $this->request->getPost('mother_name'),
                    'grd_occupation' => $this->request->getPost('guardian_occupation'),
                    'grd_income' => $this->request->getPost('guardian_income'),
                    'grd_education' => $this->request->getPost('guardian_education'),
                    'grd_email' => $this->request->getPost('guardian_email'),
                    'grd_mobile_no' => $this->request->getPost('guardian_mobile_no'),
                    'grd_address' => $this->request->getPost('guardian_address'),
                    'grd_city' => $this->request->getPost('guardian_city'),
                    'grd_state' => $this->request->getPost('guardian_state'),
                    'grd_photo' => $this->uploadImage('images/parent', 'guardian_photo'),
                    'status' => 1,
                    'branch_id' => $branchID,
                    'class_id' => $this->request->getPost('class_id'),
                    'section_id' => $this->request->getPost('section'),
                    'doc' => $this->uploadImage('online_ad_documents', 'upload_documents'),
                    'apply_date' => date("Y-m-d H:i:s"),
                    'created_date' => date("Y-m-d H:i:s")
                ];
                $this->db->table('online_admission')->insert($arrayData);
                $studentID = $this->db->insertID();

                // handle custom fields data
                $classSlug = 'student';
                $customField = $this->request->getPost(sprintf('custom_fields[%s]', $classSlug));
                if (!empty($customField)) {
                    saveCustomFieldsOnline($customField, $studentID);
                }

                // check out admission payment status
                $getStudent = $this->admissionpayment->getStudentDetails($studentID);
                if ($getStudent['fee_elements']['status'] == 0) {
                    $url = base_url("home/admission_confirmation/" . $referenceNo);
                    $sectionName = empty($arrayData['section_id']) ? "N/A" : get_type_name_by_id('section', $arrayData['section_id']);
                    // applicant email send 
                    $arrayData['institute_name'] = get_type_name_by_id('branch', $arrayData['branch_id']);
                    $arrayData['reference_no'] = $referenceNo;
                    $arrayData['student_name'] = $arrayData['first_name'] . " " . $arrayData['last_name'];
                    $arrayData['class_name'] = get_type_name_by_id('class', $arrayData['class_id']);
                    $arrayData['section_name'] = $sectionName;
                    $arrayData['payment_url'] = base_url("admissionpayment/index/" . $referenceNo);
                    $arrayData['admission_copy_url'] = $url;
                    $arrayData['paid_amount'] = 0;
                    $this->email->onlineAdmission($arrayData);
                    session()->setFlashdata('success', "Thank you for submitting the online registration form. Please you can print this copy.");
                } else {
                    $url = base_url("admissionpayment/index/" . $referenceNo);
                }

                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_admission', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/admission', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function checkAdmissionStatus()
    {
        if ($this->request->getMethod() == 'post') {
            $this->validation->setRules(["refno" => ["label" => "Enter Your Reference Number", "rules" => "trim|required|callback_admissionstatus"]]);
            if ($this->validation->run() == true) {
                $referenceNo = $this->request->getPost("refno");
                $url = base_url("home/admission_confirmation/" . $referenceNo);
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }
    }

    public function admissionstatus($referenceNo)
    {
        if (!empty($referenceNo)) {
            $this->db->table('reference_no')->where('reference_no', $referenceNo);
            $query = $this->db->get('online_admission');
            if ($query->getNumRows() < 1) {
                $this->validation->setError('admissionstatus', "Invalid Reference Number.");
                return false;
            }
        }

        return true;
    }

    public function handle_upload($str, $fields)
    {
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $fileSize = $_FILES[$fields]["size"];
            $fileName = $_FILES[$fields]["name"];
            $allowedExts = ['jpg', 'jpeg', 'png'];
            $extension = pathinfo((string)$fileName, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES[$fields]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts, true)) {
                    $this->validation->setError('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }

                if ($fileSize > 2097152) {
                    $this->validation->setError('handle_upload', translate('file_size_shoud_be_less_than') . " 2048KB.");
                    return false;
                }
            } else {
                $this->validation->setError('handle_upload', translate('error_reading_the_file'));
                return false;
            }

            return true;
        }

        return null;
    }

    public function uploadImage($role, $fields)
    {
        $returnPhoto = '';
        if (isset($_FILES[$fields]) && !empty($_FILES[$fields]['name'])) {
            $config['upload_path'] = './uploads/' . $role . '/';
            $config['overwrite'] = false;
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $file = $this->request->getFile($fields); 
            $file->initialize($config);
            if ($file->isValid() && !$file->hasMoved()) {
                $file->move($config['upload_path']);
                $returnPhoto = $file->getName();
            }
        }

        return $returnPhoto;
    }

    public function admission_confirmation($studentID = '')
    {
        $getStudent = $this->admissionpayment->getStudentDetails($studentID);
        if (empty($getStudent['id'])) {
            set_alert('error', "This application was not found.");
            return redirect()->back();
        }

        $this->data['student'] = $getStudent;
        $this->data['branchID'] = $this->data['student']['branch_id'];
        $this->data['page_data'] = $this->homeModel->get('front_cms_admission', ['branch_id' => $this->data['student']['branch_id']], true);
        $this->data['main_contents'] = view('home/admission_confirmation', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function contact()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $captcha = $this->data['cms_setting']['captcha_status'];
        if ($captcha == 'enable') {
            $this->recaptcha = service('recaptcha', ['site_key' => $this->data['cms_setting']['recaptcha_site_key'], 'secret_key' => $this->data['cms_setting']['recaptcha_secret_key']]);
            $this->data['recaptcha'] = ['widget' => $this->recaptcha->getWidget(), 'script' => $this->recaptcha->getScriptTag()];
        }

        if ($this->request->getMethod() == 'post') {
            $this->validation->setRules(['name' => ["label" => 'Name', "rules" => 'trim|required']]);
            $this->validation->setRules(['email' => ["label" => 'Email', "rules" => 'trim|required|valid_email']]);
            $this->validation->setRules(['phoneno' => ["label" => 'Phone', "rules" => 'trim|required']]);
            $this->validation->setRules(['subject' => ["label" => 'Subject', "rules" => 'trim|required']]);
            $this->validation->setRules(['message' => ["label" => 'Message', "rules" => 'trim|required']]);
            if ($captcha == 'enable') {
                $this->validation->setRules(['g-recaptcha-response' => ["label" => 'Captcha', "rules" => 'trim|required']]);
            }

            if ($this->validation->run() !== false) {
                if ($captcha == 'enable') {
                    $captchaResponse = $this->recaptcha->verifyResponse($this->request->getPost('g-recaptcha-response'));
                } else {
                    $captchaResponse = ['success' => true];
                }

                if ($captchaResponse['success'] == true) {
                    $name = $this->request->getPost('name');
                    $email = $this->request->getPost('email');
                    $phoneno = $this->request->getPost('phoneno');
                    $subject = $this->request->getPost('subject');
                    $message = $this->request->getPost('message');
                    $msg = '<h3>Sender Information</h3>';
                    $msg .= '<br><br><b>Name: </b> ' . $name;
                    $msg .= '<br><br><b>Email: </b> ' . $email;
                    $msg .= '<br><br><b>Phone: </b> ' . $phoneno;
                    $msg .= '<br><br><b>Subject: </b> ' . $subject;
                    $msg .= '<br><br><b>Message: </b> ' . $message;
                    $data = ['branch_id' => $branchID, 'recipient' => $this->data['cms_setting']['receive_contact_email'], 'subject' => 'Contact Form Email', 'message' => $msg];
                    $send = $this->mailer->send($data, true);
                    if ($send == true) {
                        session()->setFlashdata('msg_success', 'Message Successfully Sent. We will contact you shortly.');
                    } else {
                        session()->setFlashdata('msg_error', 'Message Not Successfully Sent. Error - ' . $send);
                    }
                } else {
                    $error = 'Captcha is invalid';
                    session()->setFlashdata('error', $error);
                }

                return redirect()->to(base_url('home/contact'));
            }
        }

        $this->data['page_data'] = $this->homeModel->get('front_cms_contact', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/contact', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function admit_card()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_admitcard', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/admit_card', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function admitCardprintFn()
    {
        if ($this->request->getMethod() == 'post') {
            $this->cardManage = new CardManageModel();
            $this->timetable = new TimetableModel();
            $this->ciqrcode = service('ciqrcode', ['cacheable' => false]);
            $this->validation->setRules(['exam_id' => ["label" => translate('exam'), "rules" => 'trim|required']]);
            $this->validation->setRules(['register_no' => ["label" => translate('register_no'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                //get all QR Code file
                $files = glob('uploads/qr_code/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        //delete file
                    }
                }

                $registerNo = $this->request->getPost('register_no');
                $userID = $this->db->table('student')->select('student.id,enroll.class_id,enroll.section_id')->join('enroll', 'student.id = enroll.student_id', 'inner')->where('student.register_no', $registerNo)->where('enroll.session_id', session()->get('set_session_id'))->get()->getRowArray();
                if (empty($userID)) {
                    $array = ['status' => '0', 'error' => "Register No Not Found."];
                    echo json_encode($array);
                    exit;
                }

                $templateID = $this->request->getPost('templete_id');
                if (empty($templateID) || $templateID == 0) {
                    $array = ['status' => '0', 'error' => "No Default Template Set."];
                    echo json_encode($array);
                    exit;
                }

                $this->data['exam_id'] = $this->request->getPost('exam_id');
                $this->data['userID'] = $userID;
                $this->data['template'] = $this->cardManage->get('card_templete', ['id' => $templateID], true);
                $this->data['print_date'] = date('Y-m-d');
                $cardData = view('home/admitCardprintFn', $this->data);
                $array = ['status' => 'success', 'card_data' => $cardData];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function exam_results()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_exam_results', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/exam_results', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function examResultsPrintFn()
    {
        $this->exam = new ExamModel();
        if ($this->request->getMethod() == 'post') {
            $this->validation->setRules(['exam_id' => ["label" => translate('exam'), "rules" => 'trim|required']]);
            $this->validation->setRules(['register_no' => ["label" => translate('register_no'), "rules" => 'trim|required']]);
            $this->validation->setRules(['session_id' => ["label" => translate('academic_year'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $sessionID = $this->request->getPost('session_id');
                $registerNo = $this->request->getPost('register_no');
                $examID = $this->request->getPost('exam_id');
                $userID = $this->db->table('student')->select('student.id,enroll.class_id,enroll.section_id')->join('enroll', 'student.id = enroll.student_id', 'inner')->where('student.register_no', $registerNo)->where('enroll.session_id', $sessionID)->get()->getRowArray();
                if (empty($userID)) {
                    $array = ['status' => '0', 'error' => "Register No Not Found."];
                    echo json_encode($array);
                    exit;
                }

                $result = $this->exam->getStudentReportCard($userID['id'], $examID, $sessionID, $userID['class_id'], $userID['section_id']);
                if (empty($result['exam'])) {
                    $array = ['status' => '0', 'error' => "Exam Results Not Found."];
                    echo json_encode($array);
                    exit;
                }

                $this->data['result'] = $result;
                $this->data['sessionID'] = $sessionID;
                $this->data['userID'] = $userID['id'];
                $this->data['examID'] = $examID;
                $this->data['grade_scale'] = $this->request->getPost('grade_scale');
                $this->data['attendance'] = $this->request->getPost('attendance');
                $this->data['print_date'] = date('Y-m-d');
                $cardData = view('home/reportCard', $this->data);
                $array = ['status' => 'success', 'card_data' => $cardData];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function certificates()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_certificates', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/certificates', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function certificatesPrintFn()
    {
        if ($this->request->getMethod() == 'post') {
            $this->certificate = new CertificateModel();
            $this->ciqrcode = service('ciqrcode', ['cacheable' => false]);
            //get all QR Code file
            $files = glob('uploads/qr_code/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    //delete file
                }
            }

            $this->validation->setRules(['templete_id' => ["label" => translate('certificate'), "rules" => 'trim|required']]);
            $this->validation->setRules(['register_no' => ["label" => translate('register_no'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $registerNo = $this->request->getPost('register_no');
                $userID = $this->db->table('student')->select('id')->where('register_no', $registerNo)->get()->getRowArray();
                if (empty($userID)) {
                    $array = ['status' => '0', 'error' => "Register No Not Found."];
                    echo json_encode($array);
                    exit;
                }

                $this->data['user_type'] = 1;
                $templateID = $this->request->getPost('templete_id');
                $this->data['template'] = $this->certificate->get('certificates_templete', ['id' => $templateID], true);
                $this->data['userID'] = $userID['id'];
                $this->data['print_date'] = date('Y-m-d');
                $cardData = view('home/certificatesPrintFn', $this->data);
                $array = ['status' => 'success', 'card_data' => $cardData];
            } else {
                $error = $this->validation->getErrors();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function gallery()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_gallery', ['branch_id' => $branchID], true);
        $this->data['category'] = $this->homeModel->getGalleryCategory($branchID);
        $this->data['galleryList'] = $this->homeModel->getGalleryList($branchID);
        $this->data['main_contents'] = view('home/gallery', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function gallery_view($alias = '')
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_gallery', ['branch_id' => $branchID], true);
        $this->data['gallery'] = $this->homeModel->get('front_cms_gallery_content', ['branch_id' => $branchID, 'alias' => $alias], true);
        $this->data['category'] = $this->homeModel->getGalleryCategory($branchID);
        $this->data['galleryList'] = $this->homeModel->getGalleryList($branchID);
        $this->data['main_contents'] = view('home/gallery_view', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function page($url = '')
    {
        $this->db->table('front_cms_menu')->select('front_cms_menu.title as menu_title,front_cms_menu.alias,front_cms_pages.*')->join('front_cms_pages', 'front_cms_pages.menu_id = front_cms_menu.id', 'inner')->where('front_cms_menu.alias', $url)->where('front_cms_menu.publish', 1);
        $getData = $this->db->get()->getRowArray();
        $this->data['page_data'] = $getData;
        $this->data['active_menu'] = 'page';
        $this->data['main_contents'] = view('home/page', $this->data);
        echo view('home/layout/index', $this->data);
    }

    public function getSectionByClass()
    {
        $html = "";
        $classID = $this->request->getPost("class_id");
        if (!empty($classID)) {
            $result = $this->db->table('sections_allocation')->select('sections_allocation.section_id,section.name')->join('section', 'section.id = sections_allocation.section_id', 'left')->where('sections_allocation.class_id', $classID)->get()->getResultArray();
            if (is_array($result) && count($result)) {
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_selection_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_class_first') . '</option>';
        }

        echo $html;
    }

    public function get_branch_url()
    {
        $branchID = $this->request->getPost("branch_id");
        $url = $this->db->table('front_cms_setting')->select('url_alias')->where('branch_id', $branchID)->get()->getRowArray();
        echo json_encode(['url_alias' => base_url($url['url_alias'])]);
    }

    public function news()
    {
        $branchID = $this->homeModel->getDefaultBranch();
        $urlAlias = $this->data['cms_setting']['url_alias'];
        $getLatestNewsList = $this->homeModel->getLatestNewsList($branchID);
        $page = html_escape(urldecode($this->request->getGet('page')));
        $page = is_numeric($page) ? (int)$page : 0;

        $totalRecords = count($getLatestNewsList);
        $config = [
            'page_query_string' => true,
            'query_string_segment' => 'page',
            'base_url' => base_url() . $urlAlias . '/news',
            'total_rows' => $totalRecords,
            'per_page' => 12,
            'full_tag_open' => '<ul class="pagination justify-content-center">',
            'full_tag_close' => '</ul>',
            'first_link' => '<i class="far fa-angle-double-left"></i>',
            'first_tag_open' => '<li class="previous">',
            'first_tag_close' => '</li>',
            'last_link' => '<i class="far fa-angle-double-right"></i>',
            'last_tag_open' => '<li class="next">',
            'last_tag_close' => '</li>',
            'next_link' => '<i class="far fa-angle-right"></i>',
            'next_tag_open' => '<li class="next">',
            'next_tag_close' => '</li>',
            'prev_link' => '<i class="far fa-angle-left"></i>',
            'prev_tag_open' => '<li class="previous">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="active"><span>',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
        ];
        $this->pagination->initialize($config);
        $conditions = [
            'limit' => $config['per_page'],
            'start' => $page,
        ];
        $this->data['links'] = $this->pagination->create_links();
        $this->data['results'] = $this->homeModel->getLatestNewsList($branchID, $conditions);
        $this->data['branchID'] = $branchID;
        $this->data['page_data'] = $this->homeModel->get('front_cms_news', ['branch_id' => $branchID], true);
        $this->data['main_contents'] = view('home/news', $this->data);
        echo view('home/layout/index', $this->data);
    }
}

