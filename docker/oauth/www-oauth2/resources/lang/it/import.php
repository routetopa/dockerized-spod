<?php

return [
	'import' => 'Importa',

	'users' => [
	    'title' => 'Importa utenti',
        'meta_title' => 'Importa utenti',

        'action_submit' => 'Carica e importa',
        'option_only_verified' => 'Importa solo indirizzi email verificati',
        'description' => 'Il file CSV deve avere il seguente formato:<br><kbd>email,is_admin,is_verified</kbd><br>senza riga di intestazione.',
        'file_button' => 'Scegli file CSV',

        'message_success' => 'I seguenti utenti sono stati importati con successo:',

        'errors' => [
            'already_present' => ':email è già presente',
            'file_open' => 'Non è stato possibile aprire il file caricato',
            'file_upload' => 'Errore durante il caricamento del file',
            'line_not_csv' => 'La riga :line non è in formato CSV',
            'line_wrong_count' => 'Il numero di valori alla riga :line è :values invece di :expected',
            'line_invalid_email' => 'Il campo email :email alla riga :line non è un indirizzo valido',
            'line_invalid_is_admin' => 'Il campo is_admin :is_admin alla riga :line non è un booleano',
            'line_invalid_is_verified' => 'Il campo is_verified :is_verified alla riga :line non è un booleano',
            'not_verified' => ':email non era verificato ed è stato ignorato',
            'save' => 'Impossibile creare l\'utente :email',
        ],
	],
];