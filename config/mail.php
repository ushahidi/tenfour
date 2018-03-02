<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mail Driver
    |--------------------------------------------------------------------------
    |
    | Laravel supports both SMTP and PHP's "mail" function as drivers for the
    | sending of e-mail. You may specify which one you're using throughout
    | your application here. By default, Laravel is setup for SMTP mail.
    |
    | Supported: "smtp", "mail", "sendmail", "mailgun", "mandrill", "ses", "log"
    |
    */

    'driver' => env('MAIL_DRIVER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Address
    |--------------------------------------------------------------------------
    |
    | Here you may provide the host address of the SMTP server used by your
    | applications. A default option is provided that is compatible with
    | the Mailgun mail service which will provide reliable deliveries.
    |
    */

    'host' => env('MAIL_HOST', 'smtp.mailgun.org'),

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Port
    |--------------------------------------------------------------------------
    |
    | This is the SMTP port used by your application to deliver e-mails to
    | users of the application. Like the host we have set this value to
    | stay compatible with the Mailgun e-mail application by default.
    |
    */

    'port' => env('MAIL_PORT', 587),

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => ['address' => env('MAIL_ADDRESS'), 'name' => env('MAIL_NAME')],

    /*
    |--------------------------------------------------------------------------
    | E-Mail Encryption Protocol
    |--------------------------------------------------------------------------
    |
    | Here you may specify the encryption protocol that should be used when
    | the application send e-mail messages. A sensible default using the
    | transport layer security protocol should provide great security.
    |
    */

    'encryption' => env('MAIL_ENCRYPTION', 'tls'),

    /*
    |--------------------------------------------------------------------------
    | SMTP Server Username
    |--------------------------------------------------------------------------
    |
    | If your SMTP server requires a username for authentication, you should
    | set it here. This will get used to authenticate with your server on
    | connection. You may also set the "password" value below this one.
    |
    */

    'username' => env('MAIL_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | SMTP Server Password
    |--------------------------------------------------------------------------
    |
    | Here you may set the password required by your SMTP server to send out
    | messages from your application. This will be given to the server on
    | connection so that the application will be able to send messages.
    |
    */

    'password' => env('MAIL_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Sendmail System Path
    |--------------------------------------------------------------------------
    |
    | When using the "sendmail" driver to send e-mails, we will need to know
    | the path to where Sendmail lives on this server. A default path has
    | been provided here, which will work well on most of your systems.
    |
    */

    'sendmail' => '/usr/sbin/sendmail -bs',

    /*
    |--------------------------------------------------------------------------
    | Mail "Pretend"
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, e-mail will not actually be sent over the
    | web and will instead be written to your application's logs files so
    | you may inspect the message. This is great for local development.
    |
    */

    'pretend' => env('MAIL_PRETEND', false),

    'empty_account_img' => 'data:image/png;base64,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',

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
