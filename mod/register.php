<?php

if(! function_exists('register_post')) {
function register_post(&$a) {

	$verified = 1;
	$blocked  = 0;


	$r = q("SELECT * FROM `user WHERE 1");
	if(count($r) > 1) {
		notice( t('Permission denied') . EOL);
		return;
	}

	if(x($_POST,'username'))
		$username = notags(trim($_POST['username']));
	if(x($_POST['nickname']))
		$nickname = notags(trim($_POST['nickname']));
	if(x($_POST,'email'))
		$email = notags(trim($_POST['email']));

	if((! x($username)) || (! x($email)) || (! x($nickname))) {
		notice( t('Please enter the required information.') . EOL );
		return;
	}

	$err = '';

	if(!eregi('[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\.[A-Za-z]{2,6}',$email))
		$err .= t(' Not a valid email address.');
	if(strlen($username) > 48)
		$err .= t(' Please use a shorter name.');
	if(strlen($username) < 3)
		$err .= t(' Name too short.');

	if(! preg_match("/^[a-zA-Z][a-zA-Z0-9\-\_]*$/",$nickname))
		$err .= t(' Nickname <strong>must</strong> start with a letter and contain only letters, numbers, dashes, or underscore.') ;

	if(strlen($err)) {
		notice( $err . EOL );
		return;
	}


	$new_password = trim($_POST['password']);
	$verify = trim($_POST['password2']);
	if($new_password != $verify) {
		notice( t('Passwords do not match.') . EOL);
		return;
	}

	$new_password_encoded = hash('whirlpool',$new_password);

	$res=openssl_pkey_new(array(
		'digest_alg' => 'whirlpool',
		'private_key_bits' => 4096,
		'encrypt_key' => false ));

	// Get private key

	$prvkey = '';

	openssl_pkey_export($res, $prvkey);

	// Get public key

	$pkey = openssl_pkey_get_details($res);
	$pubkey = $pkey["key"];

	$r = q("INSERT INTO `user` ( `username`, `password`, `email`, `nickname`,
		`pubkey`, `prvkey`, `verified`, `blocked` )
		VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', %d, %d )",
		dbesc($username),
		dbesc($new_password_encoded),
		dbesc($email),
		dbesc($nickname),
		dbesc($pubkey),
		dbesc($prvkey),
		intval($verified),
		intval($blocked)
		);

	if($r) {
		$r = q("SELECT `uid` FROM `user` 
			WHERE `username` = '%s' AND `password` = '%s' LIMIT 1",
			dbesc($username),
			dbesc($new_password_encoded)
			);
		if($r !== false && count($r))
			$newuid = intval($r[0]['uid']);
	}
	else {
		notice( t('An error occurred during registration. Please try again.') . EOL );
		return;
	} 		

	if(x($newuid) !== false) {
		$r = q("INSERT INTO `profile` ( `profile-name`, `is-default`, `name`, `photo`, `thumb` )
			VALUES ( '%s', %d, '%s', '%s', '%s' ) ",
			'default',
			1,
			dbesc($username),
			dbesc($a->get_baseurl() . "/photo/profile/{$newuid}.jpg"),
			dbesc($a->get_baseurl() . "/photo/avatar/{$newuid}.jpg")

		);
		if($r === false) {
			notice( t('An error occurred creating your default profile. Please try again.') . EOL );
			// Start fresh next time.
			$r = q("DELETE FROM `user` WHERE 1");
			return;
		}
		$r = q("INSERT INTO `contact` ( `created`, `self`, `name`, `photo`, `thumb`, `blocked`, `pending`, `url`,
			`request`, `notify`, `poll`, `confirm`, `name-date`, `uri-date`, `avatar-date` )
			VALUES ( '%s', 1, '%s', '%s', '%s', 0, 0, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) ",
			datetime_convert(),
			dbesc($username),
			dbesc($a->get_baseurl() . "/photo/profile/{$newuid}.jpg"),
			dbesc($a->get_baseurl() . "/photo/avatar/{$newuid}.jpg"),
			dbesc($a->get_baseurl() . "/profile/$nickname"),
			dbesc($a->get_baseurl() . "/dfrn_request/$nickname"),
			dbesc($a->get_baseurl() . "/dfrn_notify/$nickname"),
			dbesc($a->get_baseurl() . "/dfrn_poll/$nickname"),
			dbesc($a->get_baseurl() . "/dfrn_confirm/$nickname"),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert())
		);


	}

	return;
}}






if(! function_exists('register_content')) {
function register_content(&$a) {

	if($a->config['register_policy'] == REGISTER_CLOSED) {
		notice("Permission denied." . EOL);
		return;
	}

	$o = file_get_contents("view/register.tpl");
	$o = replace_macros($o, array(
		'$registertext' =>((x($a->config,'register_text'))
			? '<div class="error-message">' . $a->config['register_text'] . '</div>'
			: "" ),
		'$sitename' => $a->get_hostname()
	));
	return $o;

}}

