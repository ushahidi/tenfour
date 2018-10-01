<?php

namespace TenFour\Mail;

use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Services\URLFactory;

use App;
use Illuminate\Mail\Mailable;

class Verification extends Mailable
{

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
        $url = URLFactory::makeVerifyURL($this->address['address'], $this->address['code']);
        $url = URLFactory::shorten($url);

        return $this->view('emails.verification')
            ->with([
                'action_url'        => $url,
                'code'              => $this->address['code']
            ])
            ->subject('Verify your TenFour email address');
    }
}
