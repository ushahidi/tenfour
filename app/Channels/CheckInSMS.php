<?php

namespace TenFour\Channels;

use TenFour\Messaging\SMSService;
use TenFour\Notifications\CheckInFollowUp;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

use Log;
use App;

define('SMS_BYTECOUNT', 140);
define('DELAY_AFTER_FOLLOWUP', 120);
define('DELAY_URL_SMS', 60);

class CheckInSMS
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($contact, Notification $notification)
    {
        $this->message_service = new SMSService();

        $sms = $notification->toSMS($contact);

        $delay = 0;

        if (isset($sms['_last_unreplied_check_in_id'])) {
            $contact->notify(new CheckInFollowUp(
                $sms['_last_unreplied_check_in_id'],
                $notification->organization,
                $sms['from']));
            $delay = DELAY_AFTER_FOLLOWUP;
        }

        $to = App::make('TenFour\Messaging\PhoneNumberAdapter');
        $to->setRawNumber($contact->contact);
        $sms['keyword'] = $this->message_service->getKeyword($to);
        $oneway = $this->message_service->isOneWay($to);;

        // TODO add delays to allow SMS messages the time to be received in order #1414

        if ($this->isURLOnSMSBoundary($oneway?'sms.checkin_oneway':'sms.checkin', $sms)) {
            // send sms without check-in url
            $check_in_url = $sms['check_in_url'];
            unset($sms['check_in_url']);
            $this->sendCheckInSMS($sms['from'], $to, $sms['msg'], $sms, $oneway, $delay);
            // send check-in url
            $this->sendCheckInURLSMS($sms['from'], $to, $check_in_url, $sms, $oneway, $delay+DELAY_URL_SMS);
        } else {
            // send together
            $this->sendCheckInSMS($sms['from'], $to, $sms['msg'], $sms, $oneway, $delay);
        }
    }

    public function isURLOnSMSBoundary($view, $data, $url_param = 'check_in_url') {
        $len_with_url = mb_strlen(view($view, $data));
        $count_with_url = floor($len_with_url / SMS_BYTECOUNT);

        unset($data[$url_param]);
        $len_without_url =  mb_strlen(view($view, $data));
        $count_without_url = floor($len_without_url / SMS_BYTECOUNT);

        return $count_with_url !== $count_without_url;
    }

    private function sendCheckInSMS($from, $to, $msg, $params, $oneway, $delay) {
        $params['sms_type'] = 'check_in';
        $this->message_service->setView($oneway?'sms.checkin_oneway':'sms.checkin');
        $this->message_service->send($to, $msg, $params, null, $from, $delay);
    }

    private function sendCheckInURLSMS($from, $to, $check_in_url, $params, $oneway, $delay) {
        $params['sms_type'] = 'check_in_url';
        $this->message_service->setView($oneway?'sms.checkin_oneway_url':'sms.checkin_url');
        $this->message_service->send($to, $check_in_url, $params, null, $from, $delay);
    }


}
