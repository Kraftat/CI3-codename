<?php

namespace App\Models;

use CodeIgniter\Model;
class LibraryModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        parent::__construct();
    }
    public function book_save($data)
    {
        $arraybook = array('branch_id' => $this->applicationModel->get_branch_id(), 'title' => $data['book_title'], 'isbn_no' => $data['isbn_no'], 'author' => $data['author'], 'edition' => $data['edition'], 'purchase_date' => date('Y-m-d', strtotime($data['purchase_date'])), 'category_id' => $data['category_id'], 'publisher' => $data['publisher'], 'description' => $data['description'], 'price' => $data['price'], 'total_stock' => $data['total_stock']);
        if ($_FILES['cover_image']['name'] != "") {
            $config['upload_path'] = 'uploads/book_cover/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = false;
            $config['file_name'] = 'cover_image_' . app_generate_hash();
            $this->upload->initialize($config);
            if ($this->upload->do_upload("cover_image")) {
                $arraybook['cover'] = $this->upload->data('file_name');
            }
        }
        if (!isset($data['book_id'])) {
            $builder->insert('book', $arraybook);
        } else {
            if ($_FILES['cover_image']['name'] != "") {
                if (!empty($data['old_file'])) {
                    $file = 'uploads/book_cover/' . $data['old_file'];
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
            }
            $builder->where('id', $data['book_id']);
            $builder->update('book', $arraybook);
        }
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function category_save($data)
    {
        $arrayData = array('name' => $data['name'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (!isset($arrayData['category_id'])) {
            $builder->insert('book_category', $arrayData);
        } else {
            $builder->where('id', $arrayData['category_id']);
            $builder->update('book_category', $arrayData);
        }
    }
    // book issue information storage
    public function issued_save($data)
    {
        $arrayIssue = array('branch_id' => $this->applicationModel->get_branch_id(), 'book_id' => $data['book_id'], 'user_id' => $data['user_id'], 'role_id' => $data['role_id'], 'date_of_issue' => date("Y-m-d"), 'date_of_expiry' => date("Y-m-d", strtotime($data['date_of_expiry'])), 'issued_by' => get_loggedin_user_id(), 'status' => 1, 'session_id' => get_session_id());
        $builder->insert('book_issues', $arrayIssue);
        // update book issued copies value
        $builder->set('issued_copies', 'issued_copies+1', FALSE);
        $builder->where('id', $arrayIssue['book_id']);
        $builder->update('book');
        if ($db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    // get book issue list
    public function getBookIssueList($id = '')
    {
        $builder->select('bi.*,b.title,b.cover,b.isbn_no,b.edition,b.author,br.name as branch_name,c.name as category_name,roles.name as role_name');
        $builder->from('book_issues as bi');
        $builder->join('book as b', 'b.id = bi.book_id', 'left');
        $builder->join('branch as br', 'br.id = bi.branch_id', 'left');
        $builder->join('roles', 'roles.id = bi.role_id', 'left');
        $builder->join('book_category as c', 'c.id = b.category_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('bi.branch_id', get_loggedin_branch_id())->where();
        }
        $this->db->table('bi.session_id', get_session_id())->where();
        if ($id != '') {
            $builder->where('bi.id', $id);
            return $builder->get()->row_array();
        } else {
            $builder->order_by('bi.id', 'desc');
            return $builder->get()->result_array();
        }
    }
    // get book issue list
    public function get_book_issue_list()
    {
        $builder->select('bi.*,b.title,b.cover,b.isbn_no,b.edition,c.name as category_name');
        $builder->from('book_issues as bi');
        $builder->join('book as b', 'b.id = bi.book_id', 'left');
        $builder->join('book_category as c', 'c.id = b.category_id', 'left');
        if (is_parent_loggedin()) {
            $builder->where('bi.user_role', 'student');
            $this->db->table('bi.user_id', get_activeChildren_id())->where();
        } else {
            $this->db->table('bi.user_role', get_loggedin_user_type())->where();
            $this->db->table('bi.user_id', get_loggedin_user_id())->where();
        }
        $this->db->table('bi.session_id', get_session_id())->where();
        $builder->order_by('bi.id', 'desc');
        return $builder->get();
    }
}



