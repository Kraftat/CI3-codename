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
 * @filename : Translations.php
 * @copyright : Reserved RamomCoder Team
 */
class Translations extends AdminController

{
    protected $db;




    public $load;

    public $session;

    public $appLib;

    public $input;

    public $dbforge;

    public $image_lib;

    public function __construct()
    {




        parent::__construct();

        $this->appLib = service('appLib');}

    public function index()
    {
        if (!get_permission('translations', 'is_view')) {
            access_denied();
        }

        $this->data['edit_language'] = '';
        $this->data['sub_page'] = 'language/index';
        $this->data['main_menu'] = 'settings';
        $this->data['title'] = translate('translations');
        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css', 'vendor/bootstrap-toggle/css/bootstrap-toggle.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js', 'vendor/bootstrap-toggle/js/bootstrap-toggle.min.js']];
        echo view('layout/index', $this->data);
    }

    public function set_language($action = '')
    {
        if (is_loggedin()) {
            session()->set('set_lang', $action);
            $isRTL = $this->appLib->getRTLStatus($action);
            session()->set('is_rtl', $isRTL);
            if (!empty($_SERVER['HTTP_REFERER'])) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(base_url('dashboard'), 'refresh');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function update()
    {
        if (!get_permission('translations', 'is_edit')) {
            access_denied();
        }

        $language = html_escape($this->request->getGet('lang'));
        if (!empty($language)) {
            $queryLanguage = $db->query(sprintf('SELECT `id`, `word`, `%s` FROM `languages`', $language));
            if ($this->request->getPost('submit') == 'update') {
                if ($queryLanguage->num_rows() > 0) {
                    $words = $queryLanguage->result();
                    foreach ($words as $row) {
                        $word = $this->request->getPost('word_' . $row->word);
                        if (!empty($word)) {
                            $this->db->table('word')->where();
                            $this->db->table('languages')->update();
                        }
                    }

                    $this->db->table('lang_field')->where();
                    $this->db->table('language_list')->update();
                }

                set_alert('success', translate('information_has_been_updated_successfully'));
                return redirect()->to(base_url('translations'));
            }

            $this->data['select_language'] = $language;
            $this->data['query_language'] = $queryLanguage;
            $this->data['sub_page'] = 'language/index';
            $this->data['main_menu'] = 'settings';
            $this->data['title'] = translate('translations');
            echo view('layout/index', $this->data);
        } else {
            session()->set('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
        return null;
    }

    // Fahad fixed lang for automated translation and flag CDN
    public function submitted_data($action = '', $id = '')
    {
        if ($action == 'create') {
            if (!get_permission('translations', 'is_add')) {
                access_denied();
            }

            $language = $this->request->getPost('name', true);
            $languageCode = $this->request->getPost('language_code', true);
            $flagUrl = $this->request->getPost('flag_url', true);
            $this->db->table('language_list')->insert();
            $id = $this->db->insert_id();
            // Handle file upload if no flag is selected from dropdown
            if (empty($flagUrl) && !empty($_FILES["flag"]["name"])) {
                move_uploaded_file($_FILES['flag']['tmp_name'], 'uploads/language_flags/flag_' . $id . '.png');
                $this->create_thumb('uploads/language_flags/flag_' . $id . '.png');
                $flagUrl = base_url('uploads/language_flags/flag_' . $id . '.png');
                $this->db->table('id')->where();
                $this->db->table('language_list')->update();
            }

            $language = 'lang_' . $id;
            $this->db->table('id')->where();
            $this->db->table('language_list')->update();
            $this->load->dbforge();
            $fields = [$language => ['type' => 'LONGTEXT', 'collation' => 'utf8_unicode_ci', 'null' => true, 'default' => '']];
            $res = $this->dbforge->add_column('languages', $fields);
            if ($res == true) {
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                set_alert('error', translate('information_add_failed'));
            }

            return redirect()->to(base_url('translations'));
        }

        if ($action == 'rename') {
            if (!get_permission('translations', 'is_edit')) {
                access_denied();
            }

            $language = $this->request->getPost('rename', true);
            $languageCode = $this->request->getPost('language_code', true);
            $flagUrl = $this->request->getPost('flag_url', true);
            $this->db->table('id')->where();
            $this->db->table('language_list')->update();
            // Handle flag selection or file upload
            if (!empty($flagUrl)) {
                $this->db->table('id')->where();
                $this->db->table('language_list')->update();
            } elseif (!empty($_FILES["flag"]["name"])) {
                move_uploaded_file($_FILES['flag']['tmp_name'], 'uploads/language_flags/flag_' . $id . '.png');
                $this->create_thumb('uploads/language_flags/flag_' . $id . '.png');
                $flagUrl = base_url('uploads/language_flags/flag_' . $id . '.png');
                $this->db->table('id')->where();
                $this->db->table('language_list')->update();
            }

            set_alert('success', translate('information_has_been_updated_successfully'));
            return redirect()->to(base_url('translations'));
        }

        if ($action == 'delete') {
            if (!get_permission('translations', 'is_delete')) {
                access_denied();
            }

            $lang = $db->table('language_list')->get('language_list')->row()->lang_field;
            $this->load->dbforge();
            $this->dbforge->drop_column('languages', $lang);
            $this->db->table('id')->where();
            $this->db->table('language_list')->delete();
            if (file_exists('uploads/language_flags/flag_' . $id . '.png')) {
                unlink('uploads/language_flags/flag_' . $id . '.png');
                unlink('uploads/language_flags/flag_' . $id . '_thumb.png');
            }
        }
        return null;
    }

    public function create_thumb($source)
    {
        ini_set('memory_limit', '-1');
        $config['image_library'] = 'gd2';
        $config['create_thumb'] = true;
        $config['maintain_ratio'] = true;
        $config['width'] = 16;
        $config['height'] = 12;
        $config['source_image'] = $source;
        $this->image_lib = service('image_lib', $config);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }

    /* language publish/unpublished */
    public function status()
    {
        if (is_superadmin_loggedin()) {
            $id = $this->request->getPost('lang_id');
            $status = $this->request->getPost('status');
            if ($status == 'true') {
                $arrayData['status'] = 1;
                $message = translate('language_published');
            } else {
                $arrayData['status'] = 0;
                $message = translate('language_unpublished');
            }

            $this->db->table('id')->where();
            $this->db->table('language_list')->update();
            echo $message;
        }
    }

    /* RTL enable/disable */
    public function isRTL()
    {
        if (is_superadmin_loggedin()) {
            $id = $this->request->getPost('lang_id');
            $status = $this->request->getPost('status');
            if ($status == 'true') {
                $arrayData['rtl'] = 1;
                $message = "RTL is enabled.";
            } else {
                $arrayData['rtl'] = 0;
                $message = "RTL is disabled.";
            }

            $this->db->table('id')->where();
            $this->db->table('language_list')->update();
            $isRTL = $db->table('language_list')->get('language_list')->row();
            $lan = session()->get('set_lang');
            if ($lan == $isRTL->lang_field) {
                session()->set('is_rtl', $isRTL->rtl);
            }

            echo $message;
        }
    }

    public function get_details()
    {
        $this->request->getPost('id');
        $this->db->table('id')->where();
        $query = $builder->get('language_list');
        $result = $query->row_array();
        echo json_encode($result);
    }
}
