<?php

namespace App\Libraries;

use Exception;
use GuzzleHttp\Client;

class ZoomLib
{
    private $zoom_api_key = "";
    private $zoom_api_secret = "";

    public function __construct($api_keys = array())
    {
        if (!empty($api_keys)) {
            $this->zoom_api_key = $api_keys['zoom_api_key'];
            $this->zoom_api_secret = $api_keys['zoom_api_secret'];
        }
    }

    public function get_access_token($code)
    {
        try {
            $redirect_uri = base_url('live_class/zoom_OAuth');
            $key = $this->zoom_api_key;
            $secret = $this->zoom_api_secret;

            $client = new Client(['verify' => false, 'base_uri' => 'https://zoom.us']);
            $response = $client->request('POST', '/oauth/token', [
                "headers" => [
                    "Authorization" => "Basic " . base64_encode($key . ':' . $secret),
                ],
                'form_params' => [
                    "grant_type" => "authorization_code",
                    "code" => $code,
                    "redirect_uri" => $redirect_uri,
                ],
            ]);
            return json_decode((string) $response->getBody()->getContents(), true);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }

        return null;
    }

    public function createMeeting(array $data = array(), string $access_token = '')
    {
        $post_time = $data['date'] . ' ' . $data['start_time'];
        $start_time = gmdate("Y-m-d\TH:i:s", strtotime($post_time));
        $createAMeetingArray = array();
        $createAMeetingArray['topic'] = $data['title'];
        $createAMeetingArray['agenda'] = "";
        $createAMeetingArray['type'] = 2; // Scheduled
        $createAMeetingArray['start_time'] = $start_time;
        $createAMeetingArray['timezone'] = $data['setting']['timezone'];
        $createAMeetingArray['password'] = empty($data['setting']['password']) ? "" : $data['setting']['password'];
        $createAMeetingArray['duration'] = empty($data['duration']) ? 60 : $data['duration'];
        $createAMeetingArray['settings'] = array(
            'join_before_host' => !empty($data['setting']['join_before_host']),
            'host_video' => !empty($data['setting']['host_video']),
            'participant_video' => !empty($data['setting']['participant_video']),
            'mute_upon_entry' => !empty($data['setting']['option_mute_participants']),
            'enforce_login' => false,
            'auto_recording' => "none",
            'alternative_hosts' => "",
            'audio' => "both",
        );

        $request_url = 'https://api.zoom.us/v2/users/me/meetings';
        $headers = array(
            'authorization: Bearer ' . $access_token,
            'content-type: application/json',
        );
        $postFields = json_encode($createAMeetingArray);
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_URL, $request_url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);

        return json_decode($response);
    }

    public function deleteMeeting(string $meeting_id, string $access_token = '')
    {
        $request_url = 'https://api.zoom.us/v2/meetings/' . $meeting_id;
        $headers = array(
            'authorization: Bearer ' . $access_token,
            'content-type: application/json',
        );
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_URL, $request_url);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        if (!$response) {
            return false;
        }

        return json_decode($response);
    }
}
