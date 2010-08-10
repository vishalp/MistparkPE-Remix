<?php



function ping_init(&$a) {

	if(! local_user())
		xml_status(0);

	$r = q("SELECT COUNT(*) AS `total` FROM `item` 
		WHERE `unseen` = 1 AND `visible` = 1 AND `deleted` = 0 ");
	$network = $r[0]['total'];

	$r = q("SELECT COUNT(*) AS `total` FROM `item` 
		WHERE `unseen` = 1 AND `visible` = 1 AND `deleted` = 0 AND `type` != 'remote' ");
	$home = $r[0]['total'];

	$r = q("SELECT COUNT(*) AS `total` FROM `intro` 
		WHERE `blocked` = 0 AND `ignore` = 0 ");
	$intro = $r[0]['total'];

	$myurl = $a->get_baseurl() . '/profile/' . $user['nickname'] ;
	$r = q("SELECT COUNT(*) AS `total` FROM `mail`
		WHERE `seen` = 0 AND `from-url` != '%s' ",
		dbesc($myurl)
	);

	$mail = $r[0]['total'];
	
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n<result><intro>$intro</intro><mail>$mail</mail><net>$network</net><home>$home</home></result>\r\n";

	killme();
}

