<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class Sslcommerz
{
    protected BaseConnection $db;

    protected array $api_config;

    protected string $sslc_submit_url;

    protected string $sslc_validation_url;

    protected string $sslc_mode;

    protected array $sslc_data;

    protected string $store_id;

    protected string $store_pass;

    public string $error = '';

    public function __construct()
    {
        $this->db = Database::connect();
        $this->initialize();
    }

    public function initialize($branchID = ''): void
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->api_config = $this->db->table('payment_config')
            ->select('sslcz_store_id, sslcz_store_passwd, sslcommerz_sandbox, sslcommerz_status')
            ->where('branch_id', $branchID)
            ->get()
            ->getRowArray();

        if (empty($this->api_config)) {
            $this->api_config = [
                'sslcz_store_id' => '',
                'sslcz_store_passwd' => '',
                'sslcommerz_sandbox' => '',
                'sslcommerz_status' => '',
            ];
        }

        $this->setSSLCommerzMode(($this->api_config['sslcommerz_sandbox']) ? 1 : 0);
        $this->store_id = $this->api_config['sslcz_store_id'];
        $this->store_pass = $this->api_config['sslcz_store_passwd'];
        $sslcz_session_api = ".sslcommerz.com/gwprocess/v4/api.php";
        $sslcz_validation_api = ".sslcommerz.com/validator/api/validationserverAPI.php";
        $this->sslc_submit_url = "https://" . $this->sslc_mode . $sslcz_session_api;
        $this->sslc_validation_url = "https://" . $this->sslc_mode . $sslcz_validation_api;
    }

    public function requestToSSLC($post_data): string|false|null
    {
        if ($post_data != '' && is_array($post_data)) {
            $post_data['store_id'] = $this->store_id;
            $post_data['store_passwd'] = $this->store_pass;

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $this->sslc_submit_url);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            $content = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($code == 200 && !(curl_errno($handle))) {
                curl_close($handle);
                $sslcommerzResponse = $content;

                // PARSE THE JSON RESPONSE
                $this->sslc_data = json_decode($sslcommerzResponse, true);
                if ($this->sslc_data && isset($this->sslc_data['status']) && $this->sslc_data['status'] == 'SUCCESS') {
                    if (isset($this->sslc_data['GatewayPageURL']) && $this->sslc_data['GatewayPageURL'] != '') {
                        echo "<div style='text-align:center;margin:20% 20% 20%;border:2px solid blue;'><h2>Please wait, payment page will be loaded shortly ... ...</h2></div>";
                        echo "
                            <script>
                            window.location.href = '" . $this->sslc_data['GatewayPageURL'] . "';
                            </script>
                            ";
                        exit;
                    }

                    $this->error = "No redirect URL found!";
                    return $this->error;
                }

                $this->error = $this->sslc_data['failedreason'] ?? "JSON Data parsing error!";
                echo $this->error;
            } else {
                $msg = "Failed to connect with API.";
                echo $this->error = $msg;
            }
            curl_close($handle);
            return false;
        }

        $msg = "Please check STORE_ID, STORE_PASSWD and IS_SANDBOX value";
        $this->error = $msg;
        return false;
    }

    public function easyCheckout($post_data, $storeid, $storepass): ?bool
    {
        if ($post_data != '' && is_array($post_data) && ($storeid == $this->store_id && $storepass == $this->store_pass)) {
            $post_data['store_id'] = $this->store_id;
            $post_data['store_passwd'] = $this->store_pass;

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $this->sslc_submit_url);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            $content = curl_exec($handle);

            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($code == 200 && !(curl_errno($handle))) {
                curl_close($handle);
                $sslcommerzResponse = $content;

                // PARSE THE JSON RESPONSE
                $this->sslc_data = json_decode($sslcommerzResponse, true);
                if ($this->sslc_data && isset($this->sslc_data['status']) && $this->sslc_data['status'] == 'SUCCESS') {
                    if (isset($this->sslc_data['GatewayPageURL']) && $this->sslc_data['GatewayPageURL'] != "") {
                        echo json_encode(['status' => 'success', 'data' => $this->sslc_data['GatewayPageURL'], 'logo' => $this->sslc_data['storeLogo']]);
                        exit;
                    }

                    $message = "No redirect URL found!";
                    $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                    echo $this->error;
                } else {
                    $message = $this->sslc_data['failedreason'] ?? "JSON Data parsing error!";
                    $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                    echo $this->error;
                }
            } else {
                $message = "Failed to connect with API.";
                $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                echo $this->error;
            }
            curl_close($handle);
            return null;
        }

        $this->error = "Please check STORE_ID, STORE_PASSWD and IS_SANDBOX value";
        return false;
    }

    // SET SSLCOMMERZ PAYMENT MODE - LIVE OR TEST
    protected function setSSLCommerzMode($test)
    {
        $this->sslc_mode = $test ? "sandbox" : "securepay";
    }

    public function validateResponse($amount = 0, $currency = "BDT", $post_data = ''): bool
    {
        if ($post_data == '' || !is_array($post_data)) {
            $this->error = "Please provide valid transaction ID and post request data";
            echo $this->error;
            return false;
        }

        return $this->validation($amount, $currency, $post_data);
    }

    protected function validation($merchant_trans_amount, $merchant_trans_currency, array $post_data): bool
    {
        // MERCHANT SYSTEM INFO
        if ($merchant_trans_amount != 0) {
            // CALL THE FUNCTION TO CHECK THE RESULT
            $post_data['store_id'] = $this->store_id;
            $post_data['store_pass'] = $this->store_pass;

            $val_id = urlencode((string) $post_data['val_id']);
            $store_id = urlencode((string) $this->store_id);
            $store_passwd = urlencode((string) $this->store_pass);
            $requested_url = ($this->sslc_validation_url . "?val_id=" . $val_id . "&store_id=" . $store_id . "&store_passwd=" . $store_passwd . "&v=1&format=json");
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $requested_url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);

            $result = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($code == 200 && !(curl_errno($handle))) {
                $result = json_decode($result);
                $this->sslc_data = $result;

                // TRANSACTION INFO
                $status = $result->status;
                $tran_date = $result->tran_date;
                $tran_id = $result->tran_id;
                $val_id = $result->val_id;
                $amount = $result->amount;
                $store_amount = $result->store_amount;
                $bank_tran_id = $result->bank_tran_id;
                $card_type = $result->card_type;
                $currency_type = $result->currency_type;
                $currency_amount = $result->currency_amount;

                // ISSUER INFO
                $card_no = $result->card_no;
                $card_issuer = $result->card_issuer;
                $card_brand = $result->card_brand;
                $card_issuer_country = $result->card_issuer_country;
                $card_issuer_country_code = $result->card_issuer_country_code;

                // API AUTHENTICATION
                $APIConnect = $result->APIConnect;
                $validated_on = $result->validated_on;
                $gw_version = $result->gw_version;

                // GIVE SERVICE
                if ($status == "VALID" || $status == "VALIDATED") {
                    if ($merchant_trans_currency == "BDT") {
                        if ((abs($merchant_trans_amount - $amount) < 1) && trim((string) $merchant_trans_currency) === trim('BDT')) {
                            return true;
                        }

                        // DATA TEMPERED
                        $this->error = "Data has been tempered";
                        echo $this->error;
                        return false;
                    }

                    if ((abs($merchant_trans_amount - $currency_amount) < 1) && trim((string) $merchant_trans_currency) === trim($currency_type)) {
                        return true;
                    }
                    // DATA TEMPERED
                    $this->error = "Data has been tempered";
                    echo $this->error;
                    return false;
                }

                // FAILED TRANSACTION
                $this->error = "Failed Transaction";
                echo $this->error;
                return false;
            }

            // Failed to connect with SSLCOMMERZ
            $this->error = "Failed to connect with SSLCOMMERZ";
            echo $this->error;
            return false;
        }

        // INVALID DATA
        $this->error = "Invalid data";
        echo $this->error;
        return false;
    }

    public function ipnRequest($store_password, $postdata = array()): ?array
    {
        $password = $this->store_pass;
        $store_id = $this->store_id;

        if (isset($_POST['val_id'])) {
            $val_id = $_POST['val_id'];
        }

        $status = $_POST['status'];

        if ($store_password == $password && is_array($postdata)) {
            $other_state['gateway_return'] = $postdata;

            if ($status == 'FAILED') {
                $other_state['ipn_result'] = array(
                    'hash_validation_status' => 'SUCCESS',
                    'reason' => 'Order FAILED by IPN.'
                );
                return $other_state;
            }

            if ($status == 'CANCELLED') {
                $other_state['ipn_result'] = array(
                    'hash_validation_status' => 'SUCCESS',
                    'reason' => 'Order CANCELLED by IPN.'
                );
                return $other_state;
            }

            if ($status == 'VALID' || $status == 'VALIDATED') {
                $valid_url_own = ($this->sslc_validation_url . "?val_id=" . $val_id . "&store_id=" . $store_id . "&store_passwd=" . $password . "&v=1&format=json");
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $valid_url_own);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
                $result = curl_exec($handle);
                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                if ($code == 200 && !(curl_errno($handle))) {
                    $result = json_decode($result);
                    $ipn_return = array(
                        'gateway_return' => array(
                            'APIConnect' => $result->APIConnect,
                            'tran_id' => $result->tran_id,
                            'amount' => $result->amount,
                            'card_type' => $result->card_type,
                            'store_amount' => $result->store_amount,
                            'bank_tran_id' => $result->bank_tran_id,
                            'status' => $result->status,
                            'tran_date' => $result->tran_date,
                            'currency' => $result->currency,
                            'card_issuer' => $result->card_issuer,
                            'card_brand' => $result->card_brand,
                            'card_issuer_country' => $result->card_issuer_country,
                            'card_issuer_country_code' => $result->card_issuer_country_code,
                            'store_id' => $store_id,
                            'verify_sign' => $_POST['verify_sign'],
                            'currency_type' => $result->currency_type,
                            'currency_amount' => $result->currency_amount,
                            'risk_level' => $result->risk_level,
                            'risk_title' => $result->risk_title,
                            'token_key' => $result->token_key,
                            'validated_on' => $result->validated_on
                        ),
                        'ipn_result' => array(
                            'hash_validation_status' => '',
                            'reason' => ''
                        )
                    );

                    if ($_POST['currency_amount'] == $result->currency_amount) {
                        if ($result->status == 'VALIDATED' || $result->status == 'VALID') {
                            if ($_POST['card_type'] != "") {
                                $ipn_return['ipn_result']['hash_validation_status'] = 'SUCCESS';
                                $ipn_return['ipn_result']['reason'] = 'IPN Triggered & Hash validation success.';
                            } else {
                                $ipn_return['ipn_result']['hash_validation_status'] = 'FAILED';
                                $ipn_return['ipn_result']['reason'] = 'Card Type Empty or Mismatched.';
                            }
                        } else {
                            $ipn_return['ipn_result']['hash_validation_status'] = 'FAILED';
                            $ipn_return['ipn_result']['reason'] = 'Your Validation id could not be Verified.';
                        }
                    } else {
                        $ipn_return['ipn_result']['hash_validation_status'] = 'FAILED';
                        $ipn_return['ipn_result']['reason'] = 'Your Paid Amount is Mismatched.';
                    }

                    return $ipn_return;
                }
            }
        }

        return null;
    }
}
?>
