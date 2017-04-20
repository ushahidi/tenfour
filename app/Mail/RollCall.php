<?php

namespace RollCall\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use RollCall\Models\Organization;
use RollCall\Http\Transformers\UserTransformer;


class RollCall extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $roll_call, array $organization, array $creator, array $contact, array $user)
    {
        $this->roll_call = $roll_call;
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
        $domain = config('rollcall.domain');

        $from_address = 'rollcall-' . $this->roll_call['id'] .'@'. $domain;

        $profile_picture = $this->creator['profile_picture'];
        $initials = UserTransformer::generateInitials($this->creator['name']);
        $roll_call_url = $client_url .'/rollcalls/'. $this->roll_call['id'];
        $subject = str_limit($this->roll_call['message'], $limit = 50, $end = '...');

        $user_url_fragment = '/' . $this->user['id'] . '?token=' . urlencode($this->user['reply_token']);
        $answer_url_no = $client_url . '/r/' . $this->roll_call['id'] . '/0' . $user_url_fragment;
        $answer_url_yes = $client_url . '/r/' . $this->roll_call['id'] . '/1' . $user_url_fragment;
        $answer_url = $client_url .'/rollcalls/'. $this->roll_call['id']. '/answer';
        $reply_url = $client_url .'/rollcalls/'. $this->roll_call['id']. '/reply';

        $has_custom_answers = false;

        if ($this->roll_call['answers']) {          
          foreach ($this->roll_call['answers'] as $index => $answer) {
              $this->roll_call['answers'][$index]['url'] = $client_url . '/r/' . $this->roll_call['id'] . '/' . $index . $user_url_fragment;

              if ($answer['type'] == 'custom') {
                $has_custom_answers = true;
              }
          }
        }

        $unsubscribe_url = $client_url . '/unsubscribe/' .
          '?token=' . urlencode($this->contact['unsubscribe_token']) .
          '&email=' . urlencode($this->contact['contact']) .
          '&org_name=' . urlencode($org->name);

        return $this->view('emails.rollcall')
                    ->text('emails.rollcall_plain')
                    ->with([
                        'msg'               => $this->roll_call['message'],
                        'roll_call_url'     => $roll_call_url,
                        'profile_picture'   => $profile_picture,
                        'initials'          => $initials,
                        'answers'           => $this->roll_call['answers'],
                        'org_subdomain'     => $this->organization['subdomain'],
                        'org_name'          => $this->organization['name'],
                        'author'            => $this->creator['name'],
                        'answer_url_no'     => $answer_url_no,
                        'answer_url_yes'    => $answer_url_yes,
                        'answer_url'        => $answer_url,
                        'reply_url'         => $reply_url,
                        'has_custom_answers'=> $has_custom_answers,
                        'unsubscribe_url'   => $unsubscribe_url,
                    ])
                    ->subject($subject)
                    ->from($from_address, $this->creator['name'])
                    ->replyTo($from_address, $this->creator['name']);
    }
}
