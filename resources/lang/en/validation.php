<?php

return [
    'phone' => [
        'required' => 'Phone is required',
        'numeric' => 'Phone must be numeric',
        'digits' => 'Phone must be 12 digits',
        'wrong_phone_number' => 'Wrong phone number',
    ],
    'login_code' => [
        'required' => 'Login code is required',
        'numeric' => 'Login code must be numeric',
        'between' => 'Login code must be between 111111 and 999999',
    ],
];
