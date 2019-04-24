<?php
// Here you can initialize variables that will be available to your tests

use Codeception\Util\Fixtures;

function makeSNSMailNotification($from_email, $check_in_id, $message) {

  $Message = json_encode([
    'content' => 'From: John Doe <' . $from_email . '>
Content-Type: multipart/alternative;boundary=\'Apple-Mail=_853679B4-70FE-4F54-9EC2-BDAE6FBA1A9C\'
Mime-Version: 1.0
To: TenFour <checkin+' . $check_in_id . '@tenfour.org>

' . $message . '

',
    'mail' => [
      'timestamp' => '2016-01-27T14 =>59 =>38.237Z',
      'source' => 'test@ushahidi.com',
      'sourceArn' => 'arn =>aws =>ses =>us-west-2 =>888888888888 =>identity/example.com',
      'sendingAccountId' => '123456789012',
      'messageId' => '00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000',
      'destination' => [
        'jane@example.com',
        'mary@example.com',
        'richard@example.com'
      ],
      'headersTruncated' => false,
      'headers' => [
        [
          'name' => 'From',
          'value' => 'John Doe <test@ushahidi.com>'
        ],
        [
          'name' => 'To',
          'value' => 'Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>'
        ],
        [
          'name' => 'Message-ID',
          'value' => 'custom-message-ID'
        ],
        [
          'name' => 'Subject',
          'value' => 'Hello'
        ],
        [
          'name' => 'Content-Type',
          'value' => 'text/plain; charset=UTF-8'
        ],
        [
          'name' => 'Content-Transfer-Encoding',
          'value' => 'base64'
        ],
        [
          'name' => 'Date',
          'value' => 'Wed, 27 Jan 2016 14 =>05 =>45 +0000'
        ]
      ],
      'commonHeaders' => [
        'from' => [
          'John Doe <test@ushahidi.com>'
        ],
        'date' => 'Wed, 27 Jan 2016 14 =>05 =>45 +0000',
        'to' => [
          'Jane Doe <jane@example.com>, Mary Doe <mary@example.com>, Richard Doe <richard@example.com>'
        ],
        'messageId' => 'custom-message-ID',
        'subject' => 'Hello'
      ]
    ]
  ], JSON_HEX_QUOT);

  return json_encode([
    "Type" => "Notification",
    "MessageId" => "bar",
    "TopicArn" => "baz",
    "Subject" => "Re: Did you receive this test check-in?",
    "Timestamp" => "2016-01-27T14:59:38.237Z",
    "SignatureVersion" => "1",
    "Signature" => true,
    "SigningCertURL" => "https://sns.foo.amazonaws.com/bar.pem",
    "Message" => $Message
  ]);
}

Fixtures::add('self_test_reply', makeSNSMailNotification('test@ushahidi.com', 6, 'Confirmed'));

/* SES bounces and complaints */
// Permanent bounce
Fixtures::add('permanent_bounce', '
{
"Type": "Notification",
"MessageId": "0000-0000",
"TopicArn": "arn:aws:sns:test",
"Message": "{\"notificationType\":\"Bounce\",\"bounce\":{\"bounceType\":\"Permanent\",\"bounceSubType\":\"General\",\"bouncedRecipients\":[{\"emailAddress\":\"linda@ushahidi.com\",\"action\":\"failed\",\"status\":\"5.1.1\",\"diagnosticCode\":\"smtp; 550 5.1.1 user unknown\"}],\"timestamp\":\"2017-05-10T14:04:18.846Z\",\"feedbackId\":\"0100015bf2acbdd5-cca9dda3-1f99-48b1-b95b-3be50af66dcb-000000\",\"remoteMtaIp\":\"205.251.242.49\",\"reportingMTA\":\"dsn; a8-74.smtp-out.amazonses.com\"},\"mail\":{\"timestamp\":\"2017-05-10T14:04:18.000Z\",\"source\":\"rollcall@staging.rollcall.io\",\"sourceArn\":\"arn:aws:ses:us-east-1:513259414768:identity/staging.rollcall.io\",\"sourceIp\":\"41.60.239.75\",\"sendingAccountId\":\"513259414768\",\"messageId\":\"0100015bf2acbb4f-07ecb898-87d6-46aa-9c4e-dcde116c5ec6-000000\",\"destination\":[\"linda@ushahidi.com\"]}}",
"Timestamp" : "2012-05-02T00:54:06.655Z",
"SignatureVersion" : "1",
"Signature" : "EXAMPLEw6JRNwm1LFQL4ICB0bnXrdB8ClRMTQFGBqwLpGbM78tJ4etTwC5zU7O3tS6tGpey3ejedNdOJ+1fkIp9F2/LmNVKb5aFlYq+9rk9ZiPph5YlLmWsDcyC5T+Sy9/umic5S0UQc2PEtgdpVBahwNOdMW4JPwk0kAJJztnc=",
"SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
"UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
}
');

// Transient bounce
Fixtures::add('transient_bounce', '
{
"Type": "Notification",
"MessageId": "0000-0000",
"TopicArn": "arn:aws:sns:test",
"Message": "{\"notificationType\":\"Bounce\",\"bounce\":{\"bounceType\":\"Transient\",\"bounceSubType\":\"ContentRejected\",\"bouncedRecipients\":[{\"emailAddress\":\"linda@ushahidi.com\",\"action\":\"failed\",\"status\":\"5.1.1\",\"diagnosticCode\":\"smtp; 550 5.1.1 user unknown\"}],\"timestamp\":\"2017-05-10T14:04:18.846Z\",\"feedbackId\":\"0100015bf2acbdd5-cca9dda3-1f99-48b1-b95b-3be50af66dcb-000000\",\"remoteMtaIp\":\"205.251.242.49\",\"reportingMTA\":\"dsn; a8-74.smtp-out.amazonses.com\"},\"mail\":{\"timestamp\":\"2017-05-10T14:04:18.000Z\",\"source\":\"rollcall@staging.rollcall.io\",\"sourceArn\":\"arn:aws:ses:us-east-1:513259414768:identity/staging.rollcall.io\",\"sourceIp\":\"41.60.239.75\",\"sendingAccountId\":\"513259414768\",\"messageId\":\"0100015bf2acbb4f-07ecb898-87d6-46aa-9c4e-dcde116c5ec6-000000\",\"destination\":[\"linda@ushahidi.com\"]}}",
"Timestamp" : "2012-05-02T00:54:06.655Z",
"SignatureVersion" : "1",
"Signature" : "EXAMPLEw6JRNwm1LFQL4ICB0bnXrdB8ClRMTQFGBqwLpGbM78tJ4etTwC5zU7O3tS6tGpey3ejedNdOJ+1fkIp9F2/LmNVKb5aFlYq+9rk9ZiPph5YlLmWsDcyC5T+Sy9/umic5S0UQc2PEtgdpVBahwNOdMW4JPwk0kAJJztnc=",
"SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
"UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
}
');

// out-of-office bounce
Fixtures::add('out_of_office_bounce', '
{
"Type": "Notification",
"MessageId": "0000-0000",
"TopicArn": "arn:aws:sns:test",
"Message": "{\"notificationType\":\"Bounce\",\"bounce\":{\"bounceType\":\"Transient\",\"bounceSubType\":\"General\",\"bouncedRecipients\":[{\"emailAddress\":\"linda@ushahidi.com\",\"action\":\"failed\",\"status\":\"5.1.1\",\"diagnosticCode\":\"smtp; 550 5.1.1 user unknown\"}],\"timestamp\":\"2017-05-10T14:04:18.846Z\",\"feedbackId\":\"0100015bf2acbdd5-cca9dda3-1f99-48b1-b95b-3be50af66dcb-000000\",\"remoteMtaIp\":\"205.251.242.49\",\"reportingMTA\":\"dsn; a8-74.smtp-out.amazonses.com\"},\"mail\":{\"timestamp\":\"2017-05-10T14:04:18.000Z\",\"source\":\"rollcall@staging.rollcall.io\",\"sourceArn\":\"arn:aws:ses:us-east-1:513259414768:identity/staging.rollcall.io\",\"sourceIp\":\"41.60.239.75\",\"sendingAccountId\":\"513259414768\",\"messageId\":\"0100015bf2acbb4f-07ecb898-87d6-46aa-9c4e-dcde116c5ec6-000000\",\"destination\":[\"linda@ushahidi.com\"]}}",
"Timestamp" : "2012-05-02T00:54:06.655Z",
"SignatureVersion" : "1",
"Signature" : "EXAMPLEw6JRNwm1LFQL4ICB0bnXrdB8ClRMTQFGBqwLpGbM78tJ4etTwC5zU7O3tS6tGpey3ejedNdOJ+1fkIp9F2/LmNVKb5aFlYq+9rk9ZiPph5YlLmWsDcyC5T+Sy9/umic5S0UQc2PEtgdpVBahwNOdMW4JPwk0kAJJztnc=",
"SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
"UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
}
');

// Complaint
Fixtures::add('complaint', '
{
"Type": "Notification",
"MessageId": "0000-0000",
"TopicArn": "arn:aws:sns:test",
"Message": "{\"notificationType\": \"Complaint\",\"complaint\": {\"userAgent\": \"AnyCompany Feedback Loop (V0.01)\", \"complainedRecipients\": [{\"emailAddress\": \"linda@ushahidi.com\"}], \"complaintFeedbackType\": \"abuse\", \"arrivalDate\": \"2016-01-27T14:59:38.237Z\", \"timestamp\": \"2016-01-27T14:59:38.237Z\", \"feedbackId\": \"000001378603177f-18c07c78-fa81-4a58-9dd1-fedc3cb8f49a-000000\"}}",
"Timestamp" : "2012-05-02T00:54:06.655Z",
"SignatureVersion" : "1",
"Signature" : "EXAMPLEw6JRNwm1LFQL4ICB0bnXrdB8ClRMTQFGBqwLpGbM78tJ4etTwC5zU7O3tS6tGpey3ejedNdOJ+1fkIp9F2/LmNVKb5aFlYq+9rk9ZiPph5YlLmWsDcyC5T+Sy9/umic5S0UQc2PEtgdpVBahwNOdMW4JPwk0kAJJztnc=",
"SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem",
"UnsubscribeURL" : "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96"
}
');
