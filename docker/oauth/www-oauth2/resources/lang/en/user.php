<?php

return [

	'index' => [
		'title' => 'User management',
		'meta_title' => 'User management',
		'is_banned_heading' => 'Status',
		'is_banned_true' => 'Banned',
		'is_banned_false' => 'Allowed',
	],

	'model' => [
		'username' => 'Username',
		'password' => 'Password',
		'password_confirm' => 'Confirm password',
		'email' => 'E-mail',
		'roles' => 'Roles',
        'is_banned' => 'Banned',
        'last_login_at' => 'Last login',
        'created_at' => 'Registered',
	],

	'edit' => [
		'title' => 'Edit user',
		'meta_title' => 'Edit user',
		'is_banned' => 'This user is banned',
		'roles_is' => 'This user is <div class="chip">admin</div>',
	],

	'create' => [
		'title' => 'Add user',
		'meta_title' => 'Add user',
	],

];