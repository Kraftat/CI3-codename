<?php

namespace App\Models;

use CodeIgniter\Model;
class EventModel extends MYModel
{
    public function __construct()
    {
        parent::__construct();
    }
    function save($data = array())
    {
        $arrayEvent = array('branch_id' => $data['branch_id'], 'title' => $this->request->getPost('title'), 'remark' => $this->request->getPost('remarks'), 'type' => $data['type'], 'audition' => $data['audition'], 'image' => $data['image'], 'show_web' => isset($_POST['show_website']) ? 1 : 0, 'selected_list' => $data['selected_list'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'status' => 1);
        if (isset($data['id']) && !empty($data['id'])) {
            $builder->where('id', $data['id']);
            $builder->update('event', $arrayEvent);
        } else {
            $arrayEvent['created_by'] = get_loggedin_user_id();
            $arrayEvent['session_id'] = get_session_id();
            $builder->insert('event', $arrayEvent);
        }
    }
}



