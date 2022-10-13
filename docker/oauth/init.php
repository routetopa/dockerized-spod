$user = new \App\User;
$user->email = 'DEFMAIL';
$user->is_banned = 0;
$user->roles = 'admin';
$user->password = bcrypt( 'DEFPASS' );
$user->save();
exit;
