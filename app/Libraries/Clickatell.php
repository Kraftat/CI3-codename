<?php

namespace App\Libraries;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Database\BaseConnection;

class Clickatell
{
    const ERR_NONE              = 0;
    const ERR_AUTH_FAIL         = 1;
    const ERR_SEND_MESSAGE_FAIL = 2;
    const ERR_SESSION_EXPIRED   = 3;
    const ERR_PING_FAIL         = 4;
    const ERR_CALL_FAIL         = 5;

    public $error = self::ERR_NONE;
    public $error_message = '';

    private $session_id = false;
    private $username;
    private $password;
    private $api_id;
    private $from_no;

    const BASEURL = "http://api.clickatell.com";

    protected $db;
    protected $config;

    /**
     * Class constructor - loads CodeIgniter and Configs
     */
    public function __construct(BaseConnection $db, BaseConfig $config)
    {
        $this->db = $db;
        $this->config = $config;

        $branchID = is_superadmin_loggedin() ? $this->config->request->getPost('branch_id') : get_loggedin_branch_id();

        $clickatell = $this->db->table('sms_credential')->where(['sms_api_id' => 2, 'branch_id' => $branchID])->get()->getRowArray();
        $this->username = $clickatell['field_one'] ?? '';
        $this->password = $clickatell['field_two'] ?? '';
        $this->api_id   = $clickatell['field_three'] ?? '';
        $this->from_no  = $clickatell['field_four'] ?? '';
    }

    /**
     * Method for Authentication with Clickatell
     *
     * @return string|false $session_id
     */
    public function authenticate()
    {
        $url = self::BASEURL . '/http/auth?user=' . $this->username
             . '&password=' . $this->password . '&api_id=' . $this->api_id;

        $result = $this->_do_api_call($url);
        $result = explode(':', $result);

        if ($result[0] === 'OK') {
            $this->session_id = trim($result[1]);
            return $this->session_id;
        }

        $this->error = self::ERR_AUTH_FAIL;
        $this->error_message = $result[0];
        return false;
    }

    /**
     * Method to send a text message to number
     *
     * @param string $to
     * @param string $message
     * @return string|false
     */
    public function send_message(string $to, string $message)
    {
        if ($this->session_id == false) {
            $this->authenticate();
        }

        if ($this->error == self::ERR_NONE) {
            $message = urlencode($message);
            $url = self::BASEURL . '/http/sendmsg?session_id=' . $this->session_id
                . '&to=' . $to . '&text=' . $message . '&from=' . $this->from_no . '&MO=1';

            $result = $this->_do_api_call($url);
            $result = explode(':', $result);

            if ($result[0] === 'ID') {
                return $result[1];
            }

            $this->error = self::ERR_SEND_MESSAGE_FAIL;
            $this->error_message = $result[0];
            return false;
        }

        return false;
    }

    /**
     * Method to get account balance
     *
     * @return float|false|null
     */
    public function get_balance()
    {
        if ($this->session_id == false) {
            $this->authenticate();
        }

        if ($this->error == self::ERR_NONE) {
            $url = self::BASEURL . '/http/getbalance?session_id=' . $this->session_id;

            $result = $this->_do_api_call($url);
            $result = explode(':', $result);

            if ($result[0] === 'Credit') {
                return (float)$result[1];
            }

            $this->error = self::ERR_CALL_FAIL;
            $this->error_message = $result[0];
            return false;
        }

        return null;
    }

    /**
     * Method to send a ping to keep session live
     *
     * @return bool|null
     */
    public function ping(): ?bool
    {
        if ($this->session_id == false) {
            $this->authenticate();
        }

        if ($this->error == self::ERR_NONE) {
            $url = self::BASEURL . '/http/ping?session_id=' . $this->session_id;

            $result = $this->_do_api_call($url);
            $result = explode(':', $result);

            if ($result[0] === 'OK') {
                return true;
            }

            $this->error = self::ERR_PING_FAIL;
            $this->error_message = $result[0];
            return false;
        }

        return null;
    }

    /**
     * Method to call HTTP url - to be expanded
     *
     * @param string $url
     * @return string response
     */
    private function _do_api_call(string $url): string
    {
        $result = file($url);
        return implode("\n", $result);
    }
}
