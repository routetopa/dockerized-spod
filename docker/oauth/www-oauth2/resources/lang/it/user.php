<?php

return [

	'index' => [
		'title' => 'Gestione utenti',
		'meta_title' => 'Gestione utenti',
		'is_banned_heading' => 'Stato',
		'is_banned_true' => 'Sospeso',
		'is_banned_false' => 'Abilitato',
	],

	'model' => [
		'username' => 'Nome utente',
		'password' => 'Password',
		'password_confirm' => 'Conferma password',
		'email' => 'E-mail',
		'roles' => 'Ruoli',
        'is_banned' => 'Bannato',
        'last_login_at' => 'Ultimo accesso',
        'created_at' => 'Data di registrazione',
	],

	'edit' => [
		'title' => 'Modifica utente',
		'meta_title' => 'Modifica utente',
		'is_banned' => 'Questo utente è sospeso',
		'roles_is' => 'Questo utente è <div class="chip">admin</div>',
	],

	'create' => [
		'title' => 'Aggiungi utente',
		'meta_title' => 'Aggiungi utente',
	],

];