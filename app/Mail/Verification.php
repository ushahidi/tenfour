<?php

namespace TenFour\Mail;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Services\URLShortenerService;

class Verification extends Mailable
{
    use Queueable, SerializesModels;

    protected $address;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $address)
    {
        $this->address = $address;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $shortener = App::make('TenFour\Services\URLShortenerService');

        $url = 'https://app.' .
            config('tenfour.domain') .
            '/organization/email/confirmation/?email=' .
            urlencode($this->address['address']) .
            '&token=' .
            urlencode($this->address['verification_token']);

        return $this->view('emails.verification')
            ->with([
                'action_url'        => $shortener->shorten($url),
            ])
            ->subject('Verify your TenFour email address');
    }
}