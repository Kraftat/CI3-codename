<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/*
 * SSLCOMMERZ PAYMENT GATEWAY FOR CodeIgniter
 *
 * Module: Pay Via API (CodeIgniter 3.1.6)
 * Developed By: Prabal Mallick
 * Email: prabal.mallick@sslwireless.com
 * Author: Software Shop Limited (SSLWireless)
 *
 * */
class Sslcommerz
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    public $db;
    protected $ci;

    protected $api_config;

    protected $sslc_submit_url;

    protected $sslc_validation_url;

    protected $sslc_mode;

    protected $sslc_data;

    protected $store_id;

    protected $store_pass;

    public $error = '';

    public function __construct()
    {
        $this->db = \Config\Database::connect();


        $this->ci =& get_instance();
        $this->initialize();
    }

    public function initialize($branchID = '')
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->api_config = $db->table('payment_config')->get('payment_config')->row_array();
        if (empty($this->api_config)) {
            $this->api_config = ['sslcz_store_id' => '', 'sslcz_store_passwd' => '', 'sslcommerz_sandbox' => '', 'sslcommerz_status' => ''];
        }

        $this->setSSLCommerzMode($this->api_config['sslcommerz_sandbox'] ? 1 : 0);
        $this->store_id = $this->api_config['sslcz_store_id'];
        $this->store_pass = $this->api_config['sslcz_store_passwd'];
        $sslczSessionApi = ".sslcommerz.com/gwprocess/v4/api.php";
        $sslczValidationApi = ".sslcommerz.com/validator/api/validationserverAPI.php";
        $this->sslc_submit_url = "https://" . $this->sslc_mode . $sslczSessionApi;
        $this->sslc_validation_url = "https://" . $this->sslc_mode . $sslczValidationApi;
    }

    public function RequestToSSLC($postData)
    {
        if ($postData != '' && is_array($postData)) {
            $postData['store_id'] = $this->store_id;
            $postData['store_passwd'] = $this->store_pass;
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $this->sslc_submit_url);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            $content = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($code == 200 && !curl_errno($handle)) {
                curl_close($handle);
                $sslcommerzResponse = $content;
                # PARSE THE JSON RESPONSE
                if ($this->sslc_data = json_decode($sslcommerzResponse, true)) {
                    if (isset($this->sslc_data['status']) && $this->sslc_data['status'] == 'SUCCESS') {
                        if (isset($this->sslc_data['GatewayPageURL']) && $this->sslc_data['GatewayPageURL'] != '') {
                            echo "<div style='text-align:center;margin:20% 20% 20%;border:2px solid blue;'><h2>Please wait, payment page will be loaded shortly ... ...</h2></div>";
                            echo "\n\t\t\t\t\t\t\t<script>\n\t\t\t\t\t\t\twindow.location.href = '" . $this->sslc_data['GatewayPageURL'] . "';\n\t\t\t\t\t\t\t</script>\n\t\t\t\t\t\t\t";
                            exit;
                        }
                        $this->error = "No redirect URL found!";
                        return $this->error;
                    }
                    $this->error = $this->sslc_data['failedreason'];
                    echo $this->error;
                } else {
                    $this->error = "JSON Data parsing error!";
                    echo $this->error;
                }
            } else {
                $msg = "Failed to connect with API.";
                echo $this->error = $msg;
                // echo false;
            }
        } else {
            curl_close($handle);
            $msg = "Please check STORE_ID, STORE_PASSWD and IS_SANDBOX value";
            $this->error = $msg;
            return false;
        }

        return null;
    }

    public function EasyCheckout($postData, $storeid, $storepass)
    {
        if ($postData != '' && is_array($postData) && ($storeid == $this->store_id && $storepass == $this->store_pass)) {
            $postData['store_id'] = $this->store_id;
            $postData['store_passwd'] = $this->store_pass;
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $this->sslc_submit_url);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            $content = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($code == 200 && !curl_errno($handle)) {
                curl_close($handle);
                $sslcommerzResponse = $content;
                # PARSE THE JSON RESPONSE
                if ($this->sslc_data = json_decode($sslcommerzResponse, true)) {
                    if (isset($this->sslc_data['status']) && $this->sslc_data['status'] == 'SUCCESS') {
                        if (isset($this->sslc_data['GatewayPageURL']) && $this->sslc_data['GatewayPageURL'] != "") {
                            if (SSLCZ_IS_SANDBOX) {
                                echo json_encode(['status' => 'success', 'data' => $this->sslc_data['GatewayPageURL'], 'logo' => $this->sslc_data['storeLogo']]);
                            } else {
                                echo json_encode(['status' => 'SUCCESS', 'data' => $this->sslc_data['GatewayPageURL'], 'logo' => $this->sslc_data['storeLogo']]);
                            }

                            exit;
                        }
                        $message = "No redirect URL found!";
                        $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                        echo $this->error;
                    } else {
                        $message = $this->sslc_data['failedreason'];
                        $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                        echo $this->error;
                    }
                } else {
                    $message = "JSON Data parsing error!";
                    $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                    echo $this->error;
                }
            } else {
                $message = "Failed to connect with API.";
                $this->error = json_encode(['status' => 'FAILED', 'data' => null, 'message' => $message]);
                echo $this->error;
            }
        } else {
            curl_close($handle);
            $this->error = "Please check STORE_ID, STORE_PASSWD and IS_SANDBOX value";
            return false;
        }

        return null;
    }

    # SET SSLCOMMERZ PAYMENT MODE - LIVE OR TEST
    protected function setSSLCommerzMode($test)
    {
        $this->sslc_mode = $test ? "sandbox" : "securepay";
    }

    public function ValidateResponse($amount = 0, $currency = "BDT", $postData = '')
    {
        if ($postData == '' && !is_array($postData)) {
            $this->error = "Please provide valid transaction ID and post request data";
            echo $this->error;
        }

        return $this->Validation($amount, $currency, $postData);
    }

    protected function Validation($merchantTransAmount, $merchantTransCurrency, $postData)
    {
        # MERCHANT SYSTEM INFO
        if ($merchantTransAmount != 0) {
            # CALL THE FUNCTION TO CHECK THE RESULT
            $postData['store_id'] = $this->store_id;
            $postData['store_pass'] = $this->store_pass;
            $valId = urlencode((string) $postData['val_id']);
            $storeId = urlencode((string) $this->store_id);
            $storePasswd = urlencode((string) $this->store_pass);
            $requestedUrl = $this->sslc_validation_url . "?val_id=" . $valId . "&store_id=" . $storeId . "&store_passwd=" . $storePasswd . "&v=1&format=json";
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $requestedUrl);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            $result = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($code == 200 && !curl_errno($handle)) {
                # TO CONVERT AS ARRAY
                # $result = json_decode($result, true);
                # $status = $result['status'];
                # TO CONVERT AS OBJECT
                $result = json_decode($result);
                $this->sslc_data = $result;
                # TRANSACTION INFO
                $status = $result->status;
                $tranDate = $result->tran_date;
                $tranId = $result->tran_id;
                $valId = $result->val_id;
                $amount = $result->amount;
                $storeAmount = $result->store_amount;
                $bankTranId = $result->bank_tran_id;
                $cardType = $result->card_type;
                $currencyType = $result->currency_type;
                $currencyAmount = $result->currency_amount;
                # ISSUER INFO
                $cardNo = $result->card_no;
                $cardIssuer = $result->card_issuer;
                $cardBrand = $result->card_brand;
                $cardIssuerCountry = $result->card_issuer_country;
                $cardIssuerCountryCode = $result->card_issuer_country_code;
                # API AUTHENTICATION
                $APIConnect = $result->APIConnect;
                $validatedOn = $result->validated_on;
                $gwVersion = $result->gw_version;
                # GIVE SERVICE
                if ($status == "VALID" || $status == "VALIDATED") {
                    if ($merchantTransCurrency == "BDT") {
                        if (abs($merchantTransAmount - $amount) < 1 && trim((string) $merchantTransCurrency) === trim('BDT')) {
                            return true;
                        }
                        # DATA TEMPERED
                        $this->error = "Data has been tempered";
                        echo $this->error;
                        return false;
                    }
                    if (abs($merchantTransAmount - $currencyAmount) < 1 && trim((string) $merchantTransCurrency) === trim($currencyType)) {
                        //echo "trim($merchant_trans_id) == trim($tran_id) && ( abs($merchant_trans_amount-$currency_amount) < 1 ) && trim($merchant_trans_currency)==trim($currency_type)";
                        return true;
                    }
                    else {
                        # DATA TEMPERED
                        $this->error = "Data has been tempered";
                        echo $this->error;
                        return false;
                    }
                }
                # FAILED TRANSACTION
                $this->error = "Failed Transaction";
                echo $this->error;
                return false;
            }
            # Failed to connect with SSLCOMMERZ
            $this->error = "Failed to connect with SSLCOMMERZ";
            echo $this->error;
            return false;
        }
        # INVALID DATA
        $this->error = "Invalid data";
        echo $this->error;
        return false;
    }

    public function ipn_request($storePassword, $postdata = [])
    {
        $password = $this->store_pass;
        $storeId = $this->store_id;
        if (isset($_POST['val_id'])) {
            $valId = $_POST['val_id'];
        }

        $status = $_POST['status'];
        if ($storePassword == $password && is_array($postdata)) {
            $otherState['gateway_return'] = $postdata;
            if ($status == 'FAILED') {
                $otherState['ipn_result'] = ['hash_validation_status' => 'SUCCESS', 'reason' => 'Order FAILED by IPN.'];
                return $otherState;
            }
            if ($status == 'CANCELLED') {
                $otherState['ipn_result'] = ['hash_validation_status' => 'SUCCESS', 'reason' => 'Order CANCELLED by IPN.'];
                return $otherState;
            }
            if ($status == 'VALID' || $status == 'VALIDATED') {
                $validUrlOwn = $this->sslc_validation_url . "?val_id=" . $valId . "&store_id=" . $storeId . "&store_passwd=" . $password . "&v=1&format=json";
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $validUrlOwn);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
                $result = curl_exec($handle);
                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                if ($code == 200 && !curl_errno($handle)) {
                    $result = json_decode($result);
                    $ipnReturn = ['gateway_return' => ['APIConnect' => $result->APIConnect, 'tran_id' => $result->tran_id, 'amount' => $result->amount, 'card_type' => $result->card_type, 'store_amount' => $result->store_amount, 'bank_tran_id' => $result->bank_tran_id, 'status' => $result->status, 'tran_date' => $result->tran_date, 'currency' => $result->currency, 'card_issuer' => $result->card_issuer, 'card_brand' => $result->card_brand, 'card_issuer_country' => $result->card_issuer_country, 'card_issuer_country_code' => $result->card_issuer_country_code, 'store_id' => $storeId, 'verify_sign' => $_POST['verify_sign'], 'currency_type' => $result->currency_type, 'currency_amount' => $result->currency_amount, 'risk_level' => $result->risk_level, 'risk_title' => $result->risk_title, 'token_key' => $result->token_key, 'validated_on' => $result->validated_on], 'ipn_result' => ['hash_validation_status' => '', 'reason' => '']];
                    if ($_POST['currency_amount'] == $result->currency_amount) {
                        if ($result->status == 'VALIDATED' || $result->status == 'VALID') {
                            if ($_POST['card_type'] != "") {
                                $ipnReturn['ipn_result']['hash_validation_status'] = 'SUCCESS';
                                $ipnReturn['ipn_result']['reason'] = 'IPN Triggered & Hash valodation success.';
                            } else {
                                $ipnReturn['ipn_result']['hash_validation_status'] = 'FAILED';
                                $ipnReturn['ipn_result']['reason'] = 'Card Type Empty or Mismatched.';
                            }
                        } else {
                            $ipnReturn['ipn_result']['hash_validation_status'] = 'FAILED';
                            $ipnReturn['ipn_result']['reason'] = 'Your Validation id could not be Verified.';
                        }
                    } else {
                        $ipnReturn['ipn_result']['hash_validation_status'] = 'FAILED';
                        $ipnReturn['ipn_result']['reason'] = 'Your Paid Amount is Mismatched.';
                    }

                    return $ipnReturn;
                }
            }
        }

        return null;
    }
}
