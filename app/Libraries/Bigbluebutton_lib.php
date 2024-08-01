<?php

namespace App\Libraries;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\ResponseInterface;

class bigbluebuttonLib {
    private $bbb_security_salt = "";
    private $bbb_server_base_url = "";

    public function __construct(array $api_keys = []) {
        if (!empty($api_keys)) {
            $this->bbb_security_salt = $api_keys['bbb_security_salt'];
            $this->bbb_server_base_url = $api_keys['bbb_server_base_url'];
        }
    }

    private function putEnv(): void {
        putenv("BBB_SECURITY_SALT=" . $this->bbb_security_salt);
        putenv("BBB_SERVER_BASE_URL=" . $this->bbb_server_base_url);
    }

    public function createMeeting(array $data = []): string|bool {
        $this->putEnv();
        $bigBlueButton = new BigBlueButton();
        $urlLogout = base_url('live_class/bbb_callback');
        $createMeetingParameters = new CreateMeetingParameters($data['meeting_id'], $data['title']);
        $createMeetingParameters->setAttendeePassword($data['attendee_password']);
        $createMeetingParameters->setModeratorPassword($data['moderator_password']);
        $createMeetingParameters->setDuration($data['duration']);
        $createMeetingParameters->setMaxParticipants($data['max_participants']);
        $createMeetingParameters->setMuteOnStart($data['mute_on_start'] != 0);
        $createMeetingParameters->setWebcamsOnlyForModerator(true);
        $createMeetingParameters->setRecord($data['set_record'] != 0);
        $createMeetingParameters->setAllowStartStopRecording($data['set_record'] != 0);
        $createMeetingParameters->setAutoStartRecording(!(bool) $data['set_record']);
        $createMeetingParameters->setLogoutUrl($urlLogout);

        $response = $bigBlueButton->createMeeting($createMeetingParameters);
        if ($response->getReturnCode() == 'FAILED') {
            return false;
        }

        $joinMeetingParameters = new JoinMeetingParameters($data['meeting_id'], $data['title'], $data['moderator_password']);
        $joinMeetingParameters->setUsername($data['presen_name']);
        $joinMeetingParameters->setRedirect(true);
        return $bigBlueButton->getJoinMeetingURL($joinMeetingParameters);
    }

    public function joinMeeting(array $data = []): string|ResponseInterface {
        try {
            $this->putEnv();
            $bigBlueButton = new BigBlueButton();
            $joinMeetingParameters = new JoinMeetingParameters($data['meeting_id'], $data['title'], $data['attendee_password']);
            $joinMeetingParameters->setUsername($data['presen_name']);
            $joinMeetingParameters->setRedirect(true);
            return $bigBlueButton->getJoinMeetingURL($joinMeetingParameters);
        } catch (\Exception $exception) {
            throw HTTPException::forInternalServerError('Internal error '.$exception->getMessage());
        }
    }
}
