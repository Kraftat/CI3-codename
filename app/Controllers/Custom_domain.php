<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
use App\Models\CustomDomainModel;
/**
 * @package : Ramom school management system (Saas)
 * @version : 3.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Custom_domain.php
 * @copyright : Reserved RamomCoder Team
 */
class Custom_domain extends AdminController
{
    public $appLib;

    /**
     * @var App\Models\CustomDomainModel
     */
    public $customDomain;

    public $load;

    public $db;

    public $input;

    public $validation;

    public $custom_domainModel;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib'); 
$this->customDomain = new \App\Models\CustomDomainModel();
        if (!moduleIsEnabled('custom_domain')) {
            access_denied();
        }
    }

    public function index()
    {
        return redirect()->to(base_url('custom_domain/list'));
    }

    public function list()
    {
        if (!is_superadmin_loggedin()) {
            access_denied();
        }

        $this->data['customDomain'] = $this->custom_domainModel->getCustomDomain();
        $this->data['title'] = translate('custom_domain');
        $this->data['sub_page'] = 'custom_domain/list';
        $this->data['main_menu'] = 'custom_domain';
        echo view('layout/index', $this->data);
    }

    public function getRejectsDetails()
    {
        if ($_POST !== [] && is_superadmin_loggedin()) {
            $this->data['id'] = $this->request->getPost('id');
            echo view('custom_domain/getRejectsDetails_modal', $this->data);
        }
    }

    public function approved($id = '')
    {
        if (is_superadmin_loggedin()) {
            $this->db->table('id')->update('custom_domain', ['status' => 1, 'approved_date' => date('Y-m-d H:i:s')])->where();
        }
    }

    public function reject()
    {
        if (!is_superadmin_loggedin()) {
            ajax_access_denied();
        }

        if ($_POST !== []) {
            $customDomainID = $this->request->getPost('id');
            $comments = $this->request->getPost('comments');
            //update status
            $this->db->table('id')->update('custom_domain', ['status' => 2, 'comments' => $comments, 'approved_date' => date('Y-m-d H:i:s')])->where();
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = ['status' => 'success'];
            echo json_encode($array);
        }
    }

    public function delete($id = '')
    {
        if (!empty($id)) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('school_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('custom_domain')->delete();
        }
    }

    public function mylist()
    {
        if (is_superadmin_loggedin()) {
            access_denied();
        }

        $this->data['customDomain'] = $this->custom_domainModel->getCustomDomain();
        $this->data['title'] = translate('custom_domain');
        $this->data['sub_page'] = 'custom_domain/mylist';
        $this->data['main_menu'] = 'domain_request';
        echo view('layout/index', $this->data);
    }

    public function send_request()
    {
        if ($_POST !== []) {
            $domainType = $this->request->getPost('domainType');
            $url = "";
            if ($domainType == 'domain') {
                $domainType = 1;
                $url = $this->request->getPost('domain_name');
                $this->validation->setRules(['domain_name' => ["label" => 'URL', "rules" => 'trim|required|callback_domain_check|callback_unique_domain']]);
            } else {
                $domainType = 2;
                $url = $this->request->getPost('subdomain_name') . $this->custom_domainModel->getDomain_name($_SERVER['HTTP_HOST']);
                $this->validation->setRules(['subdomain_name' => ["label" => 'URL', "rules" => 'trim|required|callback_unique_domain']]);
            }

            $this->validation->setRules(['domainType' => ["label" => translate('type'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $arrayDomain = ['school_id' => get_loggedin_branch_id(), 'url' => $url, 'status' => 0, 'domain_type' => $domainType, 'comments' => ""];
                if (empty($this->request->getPost('id'))) {
                    $arrayDomain['request_date'] = date('Y-m-d H:i:s');
                    $this->db->table('custom_domain')->insert();
                } else {
                    $id = $this->request->getPost('id');
                    $this->db->table('id')->where();
                    $this->db->table('custom_domain')->update();
                }

                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('custom_domain/mylist');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function request_domain_edit($id = '')
    {
        if (is_superadmin_loggedin()) {
            access_denied();
        }

        $this->data['customDomain'] = $this->custom_domainModel->getCustomDomainDetails($id);
        if (empty($this->data['customDomain'])) {
            access_denied();
        }

        $this->data['title'] = translate('custom_domain');
        $this->data['sub_page'] = 'custom_domain/request_domain_edit';
        $this->data['main_menu'] = 'domain_request';
        echo view('layout/index', $this->data);
    }

    /* unique custom domain url verification is done here */
    public function unique_domain($url)
    {
        $domainType = $this->request->getPost('domainType');
        if ($domainType == 'subdomain') {
            $url .= $this->custom_domainModel->getDomain_name($_SERVER['HTTP_HOST']);
        }

        if ($this->request->getPost('id')) {
            $this->db->where_not_in('id', $this->request->getPost('id'));
        }

        $this->db->table('url')->where();
        $query = $builder->get('custom_domain')->num_rows();
        if ($query == 0) {
            return true;
        }

        $this->validation->setRule("unique_domain", translate('already_taken'));
        return false;
    }

    public function domain_check($url)
    {
        if (empty($url)) {
            return true;
        }

        if (preg_match("/^([a-z\\d](-*[a-z\\d])*)(\\.([a-z\\d](-*[a-z\\d])*))*\$/i", (string) $url) && preg_match("/^.{1,253}\$/", (string) $url) && preg_match("/^[^.]{1,63}(.[^.]{1,63})*\$/", (string) $url) && preg_match("/[.]/", (string) $url)) {
            return true;
        }

        $this->validation->setRule("domain_check", "Invalid Domain URL.");
        return false;
    }

    public function dns_instruction()
    {
        if (!is_superadmin_loggedin()) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->validation->setRules(['title' => ["label" => translate('title'), "rules" => 'trim|required']]);
            if ($this->validation->run() !== false) {
                $arrayDomain = ['title' => $this->request->getPost('title'), 'dns_status' => isset($_POST['dns_status']) ? 1 : 0, 'status' => isset($_POST['status']) ? 1 : 0, 'instruction' => $this->request->getPost('instruction', false), 'dns_title' => $this->request->getPost('dns_title'), 'dns_host_1' => $this->request->getPost('dns_host_1'), 'dns_host_2' => $this->request->getPost('dns_host_2'), 'dns_value_1' => $this->request->getPost('dns_value_1'), 'dns_value_2' => $this->request->getPost('dns_value_2')];
                $this->db->table('id')->where();
                $this->db->table('custom_domain_instruction')->update();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('custom_domain/dns_instruction');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['dns'] = $this->custom_domainModel->getDNSinstruction();
        $this->data['title'] = translate('custom_domain');
        $this->data['sub_page'] = 'custom_domain/dns_instruction';
        $this->data['main_menu'] = 'custom_domain';
        $this->data['headerelements'] = ['css' => ['vendor/summernote/summernote.css'], 'js' => ['vendor/summernote/summernote.js']];
        echo view('layout/index', $this->data);
    }
}
