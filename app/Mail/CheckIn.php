<?php

namespace TenFour\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;


class CheckIn extends Mailable
{
    use Queueable, SerializesModels;

    protected $check_in;
    protected $organization;
    protected $creator;
    protected $contact;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $check_in, array $organization, array $creator, array $contact, array $user)
    {
        $this->check_in = $check_in;
        $this->organization = $organization;
        $this->creator = $creator;
        $this->contact = $contact;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $org = Organization::findOrFail($this->organization['id']);

        $client_url = $org->url();
        $domain = config('tenfour.domain');

        $from_address = 'checkin-' . $this->check_in['id'] .'@'. $domain;

        $profile_picture = $this->creator['profile_picture'];
        $initials = UserTransformer::generateInitials($this->creator['name']);
        $check_in_url = $client_url .'/checkins/'. $this->check_in['id'];
        $subject = str_limit($this->check_in['message'], $limit = 50, $end = '...');

        // $user_url_fragment = '/' . $this->user['id'] . '?token=' . urlencode($this->user['reply_token']);
        // $answer_url_no = $client_url . '/r/' . $this->check_in['id'] . '/0' . $user_url_fragment;
        // $answer_url_yes = $client_url . '/r/' . $this->check_in['id'] . '/1' . $user_url_fragment;
        // $answer_url = $client_url .'/checkins/'. $this->check_in['id']. '/answer';
        // $reply_url = $client_url .'/checkins/'. $this->check_in['id']. '/reply';

        $has_custom_answers = false;

        if ($this->check_in['answers']) {
          foreach ($this->check_in['answers'] as $index => $answer) {

              $this->check_in['answers'][$index]['url'] =
                  $client_url .
                  '/#/r/' .
                  $this->check_in['id'] . '/' .
                  $index . '/' .
                  $this->user['id'] . '/' .
                  urlencode($this->user['reply_token']);

              if ($answer['type'] == 'custom') {
                $has_custom_answers = true;
              }
          }
        }

        $unsubscribe_url = $client_url . '/#/unsubscribe/' .
          urlencode($org->name) . '/' .
          urlencode($this->contact['contact']) . '/' .
          urlencode($this->contact['unsubscribe_token']);

        return $this->view('emails.checkin')
                    ->text('emails.checkin_plain')
                    ->with([
                        'msg'               => $this->check_in['message'],
                        'check_in_url'      => $check_in_url,
                        'profile_picture'   => $profile_picture,
                        'initials'          => $initials,
                        'answers'           => $this->check_in['answers'],
                        'org_subdomain'     => $this->organization['subdomain'],
                        'org_name'          => $this->organization['name'],
                        'author'            => $this->creator['name'],
                        // 'answer_url_no'     => $answer_url_no,
                        // 'answer_url_yes'    => $answer_url_yes,
                        // 'answer_url'        => $answer_url,
                        // 'reply_url'         => $reply_url,
                        'has_custom_answers'=> $has_custom_answers,
                        'unsubscribe_url'   => $unsubscribe_url,
                    ])
                    ->subject($subject)
                    ->from($from_address, $this->creator['name'])
                    ->replyTo($from_address, $this->creator['name']);
    }
}
