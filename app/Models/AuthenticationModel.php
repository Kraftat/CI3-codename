<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthenticationModel extends Model
{
    protected $table = 'login_credential';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'password', 'role', 'user_id', 'active'];
    protected $appLib;
    protected $db;
    protected $applicationModel;
    protected $emailModel;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->appLib = service('appLib');
        $this->applicationModel = new \App\Models\ApplicationModel();
        $this->emailModel = new \App\Models\EmailModel();

        if (!$this->appLib) {
            log_message('error', 'AppLib service could not be loaded.');
            throw new \RuntimeException('AppLib service could not be loaded.');
        } else {
            log_message('info', 'AppLib service loaded successfully.');
        }
    }

    // checking login credential
    public function login_credential($username, $password)
    {
        $query = $this->db->table($this->table)
                          ->select('*')
                          ->where('username', $username)
                          ->limit(1)
                          ->get();
                          
        if ($query->getNumRows() == 1) {
            $row = $query->getRow();
            $verify_password = $this->appLib->verifyPassword($password, $row->password);
            if ($verify_password) {
                return $row;
            }
        }
        return false;
    }

    // password forgotten
    public function lose_password($username)
    {
        if (!empty($username)) {
            $query = $this->db->table($this->table)
                              ->select('*')
                              ->where('username', $username)
                              ->limit(1)
                              ->get();

            if ($query->getNumRows() > 0) {
                $login_credential = $query->getRow();
                $getUser = $this->applicationModel->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
                $key = hash('sha512', $login_credential->role . $login_credential->username . app_generate_hash());

                $resetPasswordTable = $this->db->table('reset_password');
                $query = $resetPasswordTable->getWhere(['login_credential_id' => $login_credential->id]);
                if ($query->getNumRows() > 0) {
                    $resetPasswordTable->where('login_credential_id', $login_credential->id)->delete();
                }

                $arrayReset = [
                    'key' => $key,
                    'login_credential_id' => $login_credential->id,
                    'username' => $login_credential->username,
                ];
                $resetPasswordTable->insert($arrayReset);

                // send email for forgot password
                $arrayData = [
                    'role' => $login_credential->role,
                    'branch_id' => $getUser['branch_id'],
                    'username' => $login_credential->username,
                    'name' => $getUser['name'],
                    'reset_url' => base_url('authentication/pwreset?key=' . $key),
                    'email' => $getUser['email'],
                ];
                $this->emailModel->sentForgotPassword($arrayData);
                return true;
            }
        }
        return false;
    }

    public function urlaliasToBranch($url_alias)
    {
        $saasExisting = $this->appLib->isExistingAddon('saas');
        if ($saasExisting && $this->db->tableExists("custom_domain")) {
            $getDomain = $this->getCurrentDomain();
            if (!empty($getDomain)) {
                return $getDomain->school_id;
            }
        }
        $query = $this->db->table('front_cms_setting')
                          ->select('branch_id')
                          ->where('url_alias', $url_alias)
                          ->get();

        if ($query->getNumRows() == 0) {
            return null;
        } else {
            return $query->getRow()->branch_id;
        }
    }

    public function getSegment($id = '')
    {
        return service('uri')->getSegment($id) . '/';
    }

    public function getCurrentDomain()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $url = rtrim($url, '/');
        $domain = parse_url($url, PHP_URL_HOST);
        if (substr($domain, 0, 4) == 'www.') {
            $domain = str_replace('www.', '', $domain);
        }
        $query = $this->db->table('custom_domain')
                          ->select('school_id')
                          ->where(['status' => 1, 'url' => $domain])
                          ->get();
        return $query->getRow();
    }

    public function getSchoolDeatls($url_alias = '')
    {
        if (!empty($url_alias)) {
            $builder = $this->db->table('front_cms_setting as fs');
            $builder->select('fs.facebook_url, fs.twitter_url, fs.linkedin_url, fs.youtube_url, branch.address, branch.school_name');
            $builder->join('branch', 'branch.id = fs.branch_id', 'left');
            $builder->where('fs.url_alias', $url_alias);
            $get = $builder->get()->getRow();

            if (empty($get)) {
                return '';
            } else {
                return $get;
            }
        } else {
            return '';
        }
    }

    public function getStudentLoginStatus($id = '')
    {
        return $this->db->table('branch')->select('IFNULL(student_login, 1) as login')->where('id', $id)->get()->getRow()->login;
    }

    public function getParentLoginStatus($id = '')
    {
        return $this->db->table('branch')->select('IFNULL(parent_login, 1) as login')->where('id', $id)->get()->getRow()->login;
    }
}
