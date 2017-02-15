<?php

namespace RollCall\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use RollCall\Models\Organization;

class RollCall extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $roll_call, array $organization, array $creator, array $contact)
    {
        $this->roll_call = $roll_call;
        $this->organization = $organization;
        $this->creator = $creator;
        $this->contact = $contact;
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

        $gravatar = ! empty($this->creator['email']) ? md5(strtolower(trim($this->creator['email']))) : '00000000000000000000000000000000';
        $roll_call_url = $client_url .'/rollcalls/'. $this->roll_call['id'];
        $subject = str_limit($this->roll_call['message'], $limit = 50, $end = '...');

        $answer_url_no = $client_url .'/rollcalls/'. $this->roll_call['id']. '/answer/0';
        $answer_url_yes = $client_url .'/rollcalls/'. $this->roll_call['id']. '/answer/1';
        $answer_url = $client_url .'/rollcalls/'. $this->roll_call['id']. '/reply';

        $unsubscribe_url = $client_url . '/unsubscribe/' .
          '?token=' . urlencode($this->contact['unsubscribe_token']) .
          '&email=' . urlencode($this->contact['contact']) .
          '&org_name=' . urlencode($org->name);

        return $this->view('emails.rollcall')
                    ->text('emails.rollcall_plain')
                    ->with([
                        'msg'            => $this->roll_call['message'],
                        'roll_call_url'  => $roll_call_url,
                        'gravatar'       => $gravatar,
                        'answers'        => $this->roll_call['answers'],
                        'org_subdomain'  => $this->organization['subdomain'],
                        'author'         => $this->creator['name'],
                        'answer_url_no'  => $answer_url_no,
                        'answer_url_yes' => $answer_url_yes,
                        'answer_url'     => $answer_url,
                        'unsubscribe_url'=> $unsubscribe_url,
                    ])
                    ->subject($subject)
                    ->from($from_address, $this->creator['name'])
                    ->replyTo($from_address, $this->creator['name']);
    }
}
