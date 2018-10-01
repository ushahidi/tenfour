<?php

namespace TenFour\Services;

use UrlShortener;

class URLFactory
{
    const BASE = '/#/';

    public function __construct()
    {
    }

    public static function makeCheckInURL($organization, $check_in_id, $user_id, $reply_token, $answer_id = '-')
    {
        return $organization->url(
            self::BASE .
            'r/' .
            $check_in_id . '/' .
            $answer_id . '/' .
            $user_id . '/' .
            urlencode($reply_token));
    }

    public static function makeVerifyURL($email, $code)
    {
        return 'https://' .
            config('tenfour.domain') .
            self::BASE .
            'signup/verify/' .
            urlencode($email) . '/' .
            urlencode($code);
    }

    public static function makeInviteURL($organization, $member_id, $member_email, $invite_token)
    {
        return $organization->url(
            self::BASE .
            'signin/invite/' .
            urlencode($organization->subdomain) . '/' .
            urlencode($member_id) . '/' .
            urlencode($member_email) . '/' .
            urlencode($invite_token));
    }

    public static function makeResetPasswordURL($organization, $email, $token)
    {
         return $organization->url(
             self::BASE .
             'signin/password/reset/' .
             urlencode($organization->subdomain) . '/' .
             urlencode($email) . '/' .
             urlencode($token));
    }

    public static function makeUnsubscribeURL($organization, $contact, $token)
    {
         return $organization->url(
             self::BASE .
             'unsubscribe/' .
             urlencode($organization->name) . '/' .
             urlencode($contact) . '/' .
             urlencode($token));
    }

    public static function makePaymentsURL($organization)
    {
        return $organization->url(self::BASE . 'settings/payments');
    }

    public static function makeContactsImportURL($organization)
    {
        return $organization->url(self::BASE . 'settings/contactsImport');
    }

    public static function makeLDAPSettingsURL($organization)
    {
        return $organization->url(self::BASE . 'settings/ldap');
    }

    public static function makePeopleURL($organization)
    {
        return $organization->url(self::BASE . 'people');
    }

    public static function shorten($url)
    {
        if (!config('urlshortener.driver')) {
            return $url;
        }

        try {
            $url = UrlShortener::shorten($url);
        } catch (\Waavi\UrlShortener\Exceptions\InvalidResponseException $e) {
            \Log::error($e);
        }

        return $url;
    }

    public static function makeVoiceReplyURL($check_in_id, $recipient_id)
    {
        return self::makeAPIURL("/voice/reply?check_in_id=" . $check_in_id . "&recipient_id=" . $recipient_id);
    }

    public static function makeVoiceAnswerURL($check_in_id, $recipient_id, $contact_id)
    {
        return self::makeAPIURL("/voice/answer?check_in_id=" . $check_in_id . "&recipient_id=" . $recipient_id . "&contact_id=" . $contact_id);
    }

    public static function makeVoiceEventURL($check_in_id, $recipient_id, $contact_id)
    {
        return self::makeAPIURL("/voice/event?check_in_id=" . $check_in_id . "&recipient_id=" . $recipient_id . "&contact_id=" . $contact_id);
    }

    private static function makeAPIURL($path)
    {
        // return "https://e527e791.ngrok.io" . $path;
        return "https://" . config('api.domain') . $path;
    }
}
