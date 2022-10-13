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

	'title' => 'Impostazioni',
	'meta_title' => 'Impostazioni',
	'action' => 'Salva',

	'instance_title' => [
		'label' => 'Nome dell\'istanza',
		'description' => 'Il nome dell\'istanza è visualizzata nell\'intestazione delle pagine web e nelle email inviate dal sistema.',
	],

    'registration_open' => [
        'label' => 'Abilita registrazione da parte degli utenti',
        'description' => 'Se abilitata, gli utenti potranno registrarsi autonomamente. In caso contrario, solo un amministratore potrà iscrivere nuovi utenti.',
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
        'label' => 'ID App',
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
        'label' => 'Usa JWT',
        'description' => '',
    ],

    'key_public' => [
        'label' => 'Chiave pubblica',
        'description' => '',
    ],

    'key_private' => [
        'label' => 'Chiave privata',
        'description' => '',
    ],

];