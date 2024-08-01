<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Sessions.php
 * @copyright : Reserved RamomCoder Team
 */
class Sessions extends AdminController
{
    public $appLib;

    public $validation;

    public $input;

    public $load;

    public $session;

    public $db;

    public function __construct()
    {
        parent::__construct();

        $this->appLib = service('appLib');}

    /* form validation rules */
    protected function rules()
    {
        return [['field' => 'session', 'label' => 'Session', 'rules' => 'trim|required|callback_unique_name']];
    }

    public function index()
    {
        if (is_superadmin_loggedin()) {
            if (isset($_POST['save'])) {
                $this->validation->setRule($this->rules());
                if ($this->validation->run() == true) {
                    $this->save($this->request->getPost());
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    return redirect()->to(base_url('sessions'));
                }
            }

            $this->data['title'] = translate('session_settings');
            $this->data['sub_page'] = 'sessions/index';
            $this->data['main_menu'] = 'settings';
            echo view('layout/index', $this->data);
        } else {
            session()->set('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
        return null;
    }

    public function set_academic($action = '')
    {
        if (is_loggedin()) {
            session()->set('set_session_id', $action);
            if (!empty($_SERVER['HTTP_REFERER'])) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(base_url('dashboard'), 'refresh');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /* academic sessions information are prepared and stored in the database here */
    public function edit()
    {
        if ($_POST !== []) {
            if (!is_superadmin_loggedin()) {
                ajax_access_denied();
            }

            $this->validation->setRule($this->rules());
            if ($this->validation->run() == true) {
                $this->save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function delete($id = '')
    {
        if (is_superadmin_loggedin()) {
            $this->db->table('id')->where();
            $this->db->table('schoolyear')->delete();
        }
    }

    /* unique academic sessions name verification is done here */
    public function unique_name($year)
    {
        $schoolyearID = $this->request->getPost('schoolyear_id');
        if (!empty($schoolyearID)) {
            $this->db->where_not_in('id', $schoolyearID);
        }

        $this->db->table(['school_year' => $year])->where();
        $uniformRow = $builder->get('schoolyear')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }
        $this->validation->setRule("unique_name", translate('already_taken'));
        return false;
    }

    protected function save($data)
    {
        ['school_year' => $data['session'], 'created_by' => get_loggedin_user_id()];
        if (!isset($data['schoolyear_id'])) {
            $this->db->table('schoolyear')->insert();
        } else {
            $this->db->table('id')->where();
            $this->db->table('schoolyear')->update();
        }
    }
}
