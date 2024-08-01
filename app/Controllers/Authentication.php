<?php

namespace App\Controllers;

use App\Models\AuthenticationModel;
use App\Models\ApplicationModel;
use CodeIgniter\Controller;
use Config\Database;

class Authentication extends BaseController
{
    public $appLib;
    public $AuthenticationModel;
    public $ApplicationModel;
    public $data;
    protected $db;
    protected $userAgent;
    protected $validation;

    public function __construct()
    {
        $this->appLib = service('appLib');
        $this->db = Database::connect();
        $this->AuthenticationModel = new AuthenticationModel();
        $this->ApplicationModel = new ApplicationModel();
        $this->userAgent = service('userAgent');
        $this->validation = \Config\Services::validation();
    }

    public function index($url_alias = '')
    {
        if (session()->get('loggedin')) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($this->request->getMethod() == 'post') {
            log_message('debug', 'POST data received: ' . json_encode($this->request->getPost()));

            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required'
            ];
            if ($this->validate($rules)) {
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');
                log_message('debug', 'Email: ' . $email . ' Password: ' . $password);

                $loginCredential = $this->AuthenticationModel->login_credential($email, $password);
                if ($loginCredential) {
                    if ($loginCredential->active) {
                        $getUser = $this->AuthenticationModel->getUserNameByRoleID($loginCredential->role, $loginCredential->user_id);
                        $getConfig = $this->db->table('global_settings')->select('translation, session_id, institute_name, footer_text')->getWhere(['id' => 1])->getRow();

                        $language = $getConfig->translation;
                        if ($this->appLib->isExistingAddon('saas') && $loginCredential->role != 1) {
                            $schoolSettings = $this->db->table('branch')->getWhere(['id' => $getUser['branch_id']])->getRow();
                            if (empty($schoolSettings)) {
                                set_alert('error', translate('inactive_school'));
                                return redirect()->to(base_url('authentication'));
                            }
                            $language = $schoolSettings->translation;
                        }

                        $userType = $this->getUserType($loginCredential->role, $getUser['branch_id']);
                        if ($userType === false) {
                            return redirect()->to(base_url('authentication'));
                        }

                        $isRTL = $this->appLib->getRTLStatus($language);
                        $sessionData = [
                            'name' => $getUser['name'],
                            'logger_photo' => $getUser['photo'],
                            'loggedin_branch' => $getUser['branch_id'],
                            'loggedin_id' => $loginCredential->id,
                            'loggedin_userid' => $loginCredential->user_id,
                            'loggedin_role_id' => $loginCredential->role,
                            'loggedin_type' => $userType,
                            'set_lang' => $language,
                            'is_rtl' => $isRTL,
                            'set_session_id' => $getConfig->session_id,
                            'loggedin' => true
                        ];
                        session()->set($sessionData);

                        // Debug: Check if session data is set correctly
                        log_message('debug', 'Session Data: ' . json_encode(session()->get()));

                        $this->db->table('login_credential')->update(['last_login' => date('Y-m-d H:i:s')], ['id' => $loginCredential->id]);
                        $this->loginLog($loginCredential->user_id, $loginCredential->role, $getUser['branch_id']);
                        if (session()->has('redirect_url')) {
                            return redirect()->to(session()->get('redirect_url'));
                        }

                        return redirect()->to(base_url('dashboard'));
                    }

                    set_alert('error', translate('inactive_account'));
                    return redirect()->to(base_url('authentication'));
                }

                set_alert('error', translate('username_password_incorrect'));
                return redirect()->to(base_url('authentication'));
            } else {
                // Set validation errors
                $this->data['validation'] = $this->validation;
            }
        }

        $this->data['branch_id'] = $this->AuthenticationModel->urlaliasToBranch($url_alias);
        $schoolDetails = $this->AuthenticationModel->getSchoolDeatls($url_alias);
        if (!empty($schoolDetails) && is_object($schoolDetails)) {
            $this->data['global_config']['institute_name'] = $schoolDetails->school_name;
            $this->data['global_config']['address'] = $schoolDetails->address;
            $this->data['global_config']['facebook_url'] = $schoolDetails->facebook_url;
            $this->data['global_config']['twitter_url'] = $schoolDetails->twitter_url;
            $this->data['global_config']['linkedin_url'] = $schoolDetails->linkedin_url;
            $this->data['global_config']['youtube_url'] = $schoolDetails->youtube_url;
            $this->data['global_config']['footer_text'] = $schoolDetails->footer_text;
        }

        $this->data['applicationModel'] = $this->ApplicationModel;
        $this->data['authenticationModel'] = $this->AuthenticationModel;

        echo view('authentication/login', $this->data);
        return null;
    }

    public function forgot($url_alias = '')
    {
        if (session()->get('loggedin')) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($this->request->getMethod() == 'post') {
            $rules = [
                'username' => 'required|valid_email'
            ];
            if ($this->validate($rules)) {
                $username = $this->request->getPost('username');
                $res = $this->AuthenticationModel->lose_password($username);
                session()->setFlashdata('reset_res', $res ? 'true' : 'false');
                return redirect()->to(base_url('authentication/forgot'));
            }
        }

        $this->data['branch_id'] = $this->AuthenticationModel->urlaliasToBranch($url_alias);
        $schoolDetails = $this->AuthenticationModel->getSchoolDeatls($url_alias);
        if (!empty($schoolDetails) && is_object($schoolDetails)) {
            $this->data['global_config'] = [
                'institute_name' => $schoolDetails->school_name,
                'address' => $schoolDetails->address,
                'facebook_url' => $schoolDetails->facebook_url,
                'twitter_url' => $schoolDetails->twitter_url,
                'linkedin_url' => $schoolDetails->linkedin_url,
                'youtube_url' => $schoolDetails->youtube_url,
            ];
        } else {
            $this->data['global_config'] = [
                'institute_name' => '',
                'address' => '',
                'facebook_url' => '',
                'twitter_url' => '',
                'linkedin_url' => '',
                'youtube_url' => '',
            ];
        }

        $this->data['applicationModel'] = $this->ApplicationModel;
        $this->data['authenticationModel'] = $this->AuthenticationModel;

        echo view('authentication/forgot', $this->data);
        return null;
    }

    public function pwreset()
    {
        if (session()->get('loggedin')) {
            return redirect()->to(base_url('dashboard'));
        }

        $key = $this->request->getGet('key');
        if (!empty($key)) {
            $query = $this->db->table('reset_password')->getWhere(['key' => $key]);
            if ($query->getNumRows() > 0) {
                if ($this->request->getMethod() == 'post') {
                    $rules = [
                        'password' => 'required|min_length[4]|matches[c_password]',
                        'c_password' => 'required|min_length[4]',
                    ];
                    if ($this->validate($rules)) {
                        $password = $this->appLib->pass_hashed($this->request->getPost('password'));
                        $this->db->table('login_credential')->update(['password' => $password], ['id' => $query->getRow()->login_credential_id]);
                        $this->db->table('reset_password')->delete(['login_credential_id' => $query->getRow()->login_credential_id]);
                        set_alert('success', 'Password Reset Successfully');
                        return redirect()->to(base_url('authentication'));
                    }
                }

                echo view('authentication/pwreset', $this->data);
            } else {
                set_alert('error', 'Token Has Expired');
                return redirect()->to(base_url('authentication'));
            }
        } else {
            set_alert('error', 'Token Has Expired');
            return redirect()->to(base_url('authentication'));
        }

        return null;
    }

    public function logout()
    {
        $webURL = base_url();
        if (!session()->get('is_superadmin_loggedin')) {
            $cmsRow = $this->db->table('front_cms_setting')->getWhere(['cms_active' => 1])->getRowArray();
            if (!empty($cmsRow['url_alias'])) {
                $webURL = base_url($cmsRow['url_alias']);
            }
        }

        session()->destroy();
        return redirect()->to($webURL);
    }

    private function loginLog($userID = 0, $role = 0, $branchID = '')
    {
        $browser = $this->userAgent->isBrowser() ? $this->userAgent->getBrowser() . ' ' . $this->userAgent->getVersion() : ($this->userAgent->isRobot() ? $this->userAgent->getRobot() : ($this->userAgent->isMobile() ? $this->userAgent->getMobile() : 'Unknown'));
        $ipAddress = $this->request->getIPAddress();
        $data = [
            'user_id' => $userID,
            'role' => $role,
            'ip' => $ipAddress == "::1" ? "127.0.0.1" : $ipAddress,
            'platform' => $this->userAgent->getPlatform(),
            'browser' => $browser,
            'timestamp' => date('Y-m-d H:i:s'),
            'branch_id' => $branchID
        ];
        $this->db->table('login_log')->insert($data);
    }

    private function getUserType($role, $branchId)
    {
        switch ($role) {
            case 6:
                if ($this->AuthenticationModel->getParentLoginStatus($branchId) == 0) {
                    set_alert('error', translate('parent_login_has_been_disabled'));
                    return false;
                }
                return 'parent';
            case 7:
                if ($this->AuthenticationModel->getStudentLoginStatus($branchId) == 0) {
                    set_alert('error', translate('student_login_has_been_disabled'));
                    return false;
                }
                $enrollID = $this->AuthenticationModel->getEnrollID(session()->get('loggedin_userid'), session()->get('set_session_id'));
                session()->set('enrollID', $enrollID);
                return 'student';
            default:
                return 'staff';
        }
    }
}
