<?php

return [

    /*
   |--------------------------------------------------------------------------
   | Settings Language Lines
   |--------------------------------------------------------------------------
   |
   | The following language lines are describes the settings available for
   | administrators and users. The key of this array must correspond to a
   | value in column 'key' of table 'settings'.
   |
   */

    'title' => 'Settings',
    'meta_title' => 'Settings',
	'action' => 'Save',

	'instance_title' => [
		'label' => 'Instance title',
		'description' => '',
	],

    'registration_open' => [
        'label' => 'Allow users\' registration',
        'description' => 'If checked, users will be allowed to register on their own.',
    ],

	/*
   |--------------------------------------------------------------------------
   | Check authentication API
   |--------------------------------------------------------------------------
	*/

	'api_check' => [
		'heading' => 'Check authentication API',
		'description' => '',
	],

	'api_check_jsonp_enable' => [
		'label' => 'Enable JSONP API',
		'description' => '',
	],

	'api_check_cors_enable' => [
		'label' => 'Enable CORS API',
		'description' => '',
	],

    /*
    |--------------------------------------------------------------------------
    | Facebook login
    |--------------------------------------------------------------------------
    */

    'fb' => [
        'heading' => 'Facebook',
        'description' => '',
    ],

    'fb_app_id' => [
        'label' => 'App ID',
        'description' => '',
    ],

    'fb_app_secret' => [
        'label' => 'App secret',
        'description' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT
    |--------------------------------------------------------------------------
    */

    'jwt' => [
        'heading' => 'JWT',
        'description' => '',
    ],

    'jwt_enable' => [
        'label' => 'Use JWT',
        'description' => '',
    ],

    'key_public' => [
        'label' => 'Public key',
        'description' => '',
    ],

    'key_private' => [
        'label' => 'Private key',
        'description' => '',
    ],

    'key_generate' => 'Generate keys',

    /*
    |--------------------------------------------------------------------------
    | KEYS SUBSECTION
    |--------------------------------------------------------------------------
    */

    'keys' => [
        'title' => 'Key generation',
        'meta_title' => 'Key generation',

        'description' => 'The Authentication Server can generate a pair of keys to use with JWT Token. Generated keys will be automatically set as active keys in the Settings panel.',

        'action' => 'Generate',
    ],

];