<?php

namespace App\Models;

use CodeIgniter\Model;
class ContentModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function save_content($data, $page_id = '')
    {
        if ($page_id) {
            $builder->where('id', $page_id);
            $builder->update('front_cms_pages', $data);
        } else {
            $builder->insert('front_cms_pages', $data);
        }
    }
    public function get_page_list()
    {
        $builder->select('front_cms_pages.*,front_cms_menu.title as menu_title,front_cms_menu.alias,branch.name as branch_name,front_cms_setting.url_alias');
        $builder->from('front_cms_pages');
        $builder->join('front_cms_menu', 'front_cms_menu.id=front_cms_pages.menu_id', 'left');
        $builder->join('branch', 'branch.id=front_cms_pages.branch_id', 'left');
        $builder->join('front_cms_setting', 'front_cms_setting.branch_id=front_cms_pages.branch_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('front_cms_pages.branch_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->result_array();
    }
    // upload image
    public function uploadBanner($img_name = '', $path = '')
    {
        $prev_image = $this->request->getPost('old_photo');
        $image = $_FILES['photo']['name'];
        $return_image = '';
        if ($image != '') {
            $destination = './uploads/frontend/' . $path . '/';
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $image_path = $img_name . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], $destination . $image_path);
            // need to unlink previous slider
            if ($prev_image != $image_path) {
                if (file_exists($destination . $prev_image)) {
                    @unlink($destination . $prev_image);
                }
            }
            $return_image = $image_path;
        } else {
            $return_image = $prev_image;
        }
        return $return_image;
    }
}



