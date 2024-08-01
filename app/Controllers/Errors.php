<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ApplicationModel;

/**
 * @package : Ramom school management system
 * @version : 5.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Errors.php
 * @copyright : Reserved RamomCoder Team
 */
class Errors extends Controller
{
    /**
     * @var mixed
     */
    public $appLib;

    protected $applicationModel;

    public function __construct()
    {
        $this->appLib = service('appLib'); 
        $this->applicationModel = new ApplicationModel();
    }

    public function index()
    {
        $data['applicationModel'] = $this->applicationModel;
        return view('errors/error_404_message', $data);
    }

    public function show404()
    {
        $this->response->setStatusCode(404);
        $data['applicationModel'] = $this->applicationModel;
        return view('errors/error_404_message', $data);
    }
}
