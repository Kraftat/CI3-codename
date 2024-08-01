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
 * @filename : Library.php
 * @copyright : Reserved RamomCoder Team
 */
class Library extends AdminController

{
    public $appLib;

    protected $db;


    /**
     * @var App\Models\LibraryModel
     */
    public $library;

    public $validation;

    public $input;

    public $libraryModel;

    public $applicationModel;

    public $load;

    public function __construct()
    {


        parent::__construct();

        $this->appLib = service('appLib'); 
$this->library = new \App\Models\LibraryModel();
    }

    public function index()
    {
        if (is_loggedin()) {
            return redirect()->to(base_url('dashboard'));
        }

        redirect(base_url(), 'refresh');
        return null;
    }

    /* book form validation rules */
    protected function book_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['book_title' => ["label" => translate('book_title'), "rules" => 'trim|required']]);
        $this->validation->setRules(['purchase_date' => ["label" => translate('purchase_date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['category_id' => ["label" => translate('book_category'), "rules" => 'trim|required']]);
        $this->validation->setRules(['publisher' => ["label" => translate('publisher'), "rules" => 'trim|required']]);
        $this->validation->setRules(['price' => ["label" => translate('price'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['total_stock' => ["label" => translate('total_stock'), "rules" => 'trim|required']]);
    }

    /* category form validation rules */
    protected function category_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['name' => ["label" => translate('category'), "rules" => 'trim|required|callback_unique_category']]);
    }

    // book page
    public function book()
    {
        if (!get_permission('book', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('book', 'is_add')) {
                ajax_access_denied();
            }

            $this->book_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all route information in the database file
                $this->libraryModel->book_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/book');
                $array = ['status' => 'success', 'url' => $url];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['booklist'] = $this->appLib->getTable('book');
        $this->data['title'] = translate('books');
        $this->data['sub_page'] = 'library/book';
        $this->data['main_menu'] = 'library';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    /* the book information is updated here */
    public function book_edit($id = '')
    {
        if (!get_permission('book', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->book_validation();
            if ($this->validation->run() !== false) {
                $post = $this->request->getPost();
                //save all route information in the database file
                $this->libraryModel->book_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/book');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['book'] = $this->appLib->getTable('book', ['t.id' => $id], true);
        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['booklist'] = $this->appLib->getTable('book');
        $this->data['title'] = translate('books_entry');
        $this->data['sub_page'] = 'library/book_edit';
        $this->data['main_menu'] = 'library_book';
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        echo view('layout/index', $this->data);
    }

    public function book_delete($id = '')
    {
        if (get_permission('book', 'is_delete')) {
            $file = 'uploads/book_cover/' . get_type_name_by_id('book', $id, 'cover');
            if (file_exists($file)) {
                @unlink($file);
            }

            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('book')->delete();
        }
    }

    // category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('book_category', 'is_add')) {
                access_denied();
            }

            $this->category_validation();
            if ($this->validation->run() !== false) {
                //save hostel type information in the database file
                $this->libraryModel->category_save($this->request->getPost());
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('library/category'));
            }
        }

        $this->data['categorylist'] = $this->appLib->getTable('book_category');
        $this->data['title'] = translate('category');
        $this->data['sub_page'] = 'library/category';
        $this->data['main_menu'] = 'library';
        echo view('layout/index', $this->data);
        return null;
    }

    public function category_edit()
    {
        if ($_POST !== []) {
            if (!get_permission('book_category', 'is_edit')) {
                ajax_access_denied();
            }

            $this->category_validation();
            if ($this->validation->run() !== false) {
                //update book category information in the database file
                $this->libraryModel->category_save($this->request->getPost());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('library/category');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function category_delete($id)
    {
        if (get_permission('book_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('book_category')->delete();
        }
    }

    /* book issue information are prepared and stored in the database here */
    public function book_manage($action = '', $id = '')
    {
        if (!get_permission('book_manage', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            if (!get_permission('book_manage', 'is_add')) {
                access_denied();
            }

            $arrayLeave = ['issued_by' => get_loggedin_user_id(), 'status' => $this->request->getPost('status')];
            $id = $this->request->getPost('id');
            if (!is_superadmin_loggedin()) {
                $this->db->table('branch_id')->where();
            }

            $this->db->table('id')->where();
            $this->db->table('book_issues')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }

        if ($action == "delete") {
            $this->db->table('id')->where();
            $this->db->table('book_issues')->delete();
        }

        $this->data['branch_id'] = $this->applicationModel->get_branch_id();
        $this->data['booklist'] = $this->libraryModel->getBookIssueList();
        $this->data['title'] = translate('book_manage');
        $this->data['sub_page'] = 'library/book_manage';
        $this->data['main_menu'] = 'library';
        echo view('layout/index', $this->data);
    }

    public function bookIssued()
    {
        if ($_POST !== []) {
            if (!get_permission('book_manage', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['category_id' => ["label" => translate('book_category'), "rules" => 'required']]);
            $this->validation->setRules(['book_id' => ["label" => translate('book_title'), "rules" => 'trim|required|callback_validation_stock']]);
            $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'required']]);
            $roleID = $this->request->getPost('role_id');
            if ($roleID == 7) {
                $this->validation->setRules(['class_id' => ["label" => translate('class'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['user_id' => ["label" => translate('user_name'), "rules" => 'required']]);
            $this->validation->setRules(['date_of_expiry' => ["label" => 'Date Of Expiry', "rules" => 'trim|required|callback_validation_date']]);
            if ($this->validation->run() !== false) {
                $data = $this->request->getPost();
                //save book issued information in the database file
                $this->libraryModel->issued_save($data);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/book_manage');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    public function issued_book_delete($id)
    {
        if (get_permission('book_manage', 'is_delete')) {
            $status = get_type_name_by_id('book_issues', $id, 'status');
            if ($status == 2 || $status == 3) {
                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $this->db->table('id')->where();
                $this->db->table('book_issues')->delete();
            }
        }
    }

    public function request()
    {
        // check access permission
        if (!get_permission('book_request', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('book_request', 'is_add')) {
                access_denied();
            }

            $this->validation->setRules(['book_id' => ["label" => translate('book_title'), "rules" => 'required|callback_validation_stock']]);
            $this->validation->setRules(['date_of_issue' => ["label" => translate('date_of_issue'), "rules" => 'trim|required']]);
            $this->validation->setRules(['date_of_expiry' => ["label" => translate('date_of_expiry'), "rules" => 'trim|required|callback_validation_date']]);
            if ($this->validation->run() !== false) {
                $arrayIssue = ['branch_id' => get_loggedin_branch_id(), 'book_id' => $this->request->getPost('book_id'), 'user_id' => get_loggedin_user_id(), 'role_id' => loggedin_role_id(), 'date_of_issue' => date("Y-m-d", strtotime((string) $this->request->getPost('date_of_issue'))), 'date_of_expiry' => date("Y-m-d", strtotime((string) $this->request->getPost('date_of_expiry'))), 'issued_by' => get_loggedin_user_id(), 'status' => 0, 'session_id' => get_session_id()];
                $this->db->table('book_issues')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/request');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('library');
        $this->data['sub_page'] = 'library/request';
        $this->data['main_menu'] = 'library';
        echo view('layout/index', $this->data);
    }

    public function request_delete($id)
    {
        if (get_permission('book_request', 'is_delete')) {
            $status = get_type_name_by_id('book_issues', $id, 'status');
            if ($status == 0) {
                $this->db->table('id')->where();
                $this->db->table('user_id')->where();
                $this->db->table('role_id')->where();
                $this->db->table('book_issues')->delete();
            }
        }
    }

    // validation book stock
    public function validation_stock($bookId)
    {
        $query = $db->table('book')->get('book')->row_array();
        $stock = $query['total_stock'];
        $issued = $query['issued_copies'];
        if ($stock == 0 || $issued >= $stock) {
            $this->validation->setRule("validation_stock", translate('the_book_is_not_available_in_stock'));
            return false;
        }

        return true;
    }

    public function getBookApprovelDetails()
    {
        if (get_permission('book_manage', 'is_add')) {
            $this->data['book_id'] = $this->request->getPost('id');
            echo view('library/bookDetailsModal', $this->data);
        }
    }

    public function bookReturn()
    {
        if ($_POST !== []) {
            if (!get_permission('book_manage', 'is_add')) {
                ajax_access_denied();
            }

            $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required|callback_return_validation']]);
            $this->validation->setRules(['fine_amount' => ["label" => translate('role'), "rules" => 'trim|numeric']]);
            if ($this->validation->run() !== false) {
                $id = $this->request->getPost('issue_id');
                $getData = $builder->getWhere('book_issues', ['id' => $id])->row_array();
                $type = $this->request->getPost('type');
                $date = strtotime((string) $this->request->getPost('date'));
                if ($type == '1') {
                    // update book issued copies value
                    $this->db->set('issued_copies', 'issued_copies-1', false);
                    $this->db->table('id')->where();
                    $this->db->table('book')->update();
                    $arrayReturn = ['return_by' => get_loggedin_user_id(), 'status' => 3, 'fine_amount' => $this->request->getPost('fine_amount'), 'return_date' => date("Y-m-d", $date)];
                } elseif ($type == '2') {
                    $arrayReturn = ['fine_amount' => $this->request->getPost('fine_amount'), 'date_of_expiry' => date("Y-m-d", $date)];
                }

                if (!is_superadmin_loggedin()) {
                    $this->db->table('branch_id')->where();
                }

                $this->db->table('id')->where();
                $this->db->table('book_issues')->update();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/book_manage');
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'url' => '', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // validation date
    public function validation_date($date)
    {
        if ($date) {
            $date = strtotime((string) $date);
            $today = strtotime(date('Y-m-d'));
            if ($today >= $date) {
                $this->validation->setRule("validation_date", translate('today_or_the_previous_day_can_not_be_issued'));
                return false;
            }

            return true;
        }

        return null;
    }

    public function return_validation($date)
    {
        $date = strtotime((string) $date);
        $id = $this->request->getPost('issue_id');
        $get = $builder->select('date_of_issue,date_of_expiry')->get_where('book_issues', ['id' => $id])->row_array();
        if (strtotime((string) $get['date_of_issue']) >= $date) {
            $this->validation->setRule("return_validation", translate('invalid_return_date_entered'));
            return false;
        }

        return true;
    }

    /* book category exists validation */
    public function unique_category($name)
    {
        $categoryId = $this->request->getPost('category_id');
        $this->applicationModel->get_branch_id();
        if (!empty($categoryId)) {
            $this->db->where_not_in('id', $categoryId);
        }

        $this->db->table('name')->where();
        $this->db->table('branch_id')->where();
        $query = $builder->get('book_category');
        if ($query->num_rows() > 0) {
            $this->validation->setRule("unique_category", translate('already_taken'));
            return false;
        }

        return true;
    }

    /* get book list based on the category */
    public function getBooksByCategory()
    {
        $categoryID = $this->request->getPost('category_id');
        $html = "";
        if (!empty($categoryID)) {
            $books = $builder->select('id,title')->get_where('book', ['category_id' => $categoryID])->result_array();
            if (count($books) > 0) {
                $html .= '<option value = "">' . translate('select') . '</option>';
                foreach ($books as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['title'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_category_first') . '</option>';
        }

        echo $html;
    }
}
