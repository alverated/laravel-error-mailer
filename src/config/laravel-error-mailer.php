<?php

return [
    'subject' => 'Site Error',
    'template' => 'vendor.mailer',
    'recipients' => [
        'youremail@domain.com',
    ],
    'from' => [
        'name'  => 'Error Handler',
        'email' => 'errormailer@domain.com',
    ],
    'reply_to' => [
        'name'  => 'Support',
        'email' => 'support@domain.com',
    ],
    'reported_by' => 'LaravelErrorMailer',
];