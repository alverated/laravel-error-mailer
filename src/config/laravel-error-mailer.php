<?php

return [
    'subject' => 'Site Error',
    'template' => 'vendor.mailer',
    'recipients' => [
        'alver.noquiao@gmail.com',
    ],
    'from' => [
        'name'  => 'Error Handler',
        'email' => 'error@domain.com',
    ],
    'reply_to' => [
        'name'  => 'Support',
        'email' => 'support@domain.com',
    ]
];