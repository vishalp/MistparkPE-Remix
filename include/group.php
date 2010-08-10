<?php


function group_add($name) {

	$ret = false;
	if(x($name)) {
		$r = group_byname($name); // check for dups
		if($r !== false) 
			return true;
		$r = q("INSERT INTO `group` ( `name` )
			VALUES( '%s' ) ",
			dbesc($name)
		);
		$ret = $r;
	}	
	return $ret;
}


function group_rmv($name) {
	$ret = false;
	if(x($name)) {
		$r = q("SELECT * FROM `group` WHERE `name` = '%s' LIMIT 1",
			dbesc($name)
		);
		if(count($r))
			$group_id = $r[0]['id'];
		if(! $group_id)
			return false;

		// remove all members
		$r = q("DELETE FROM `group_member` WHERE `gid` = %d ",
			intval($group_id)
		);

		// remove group
		$r = q("DELETE FROM `group` WHERE `id` = %d LIMIT 1",
			dbesc($name)
		);

		$ret = $r;

	}
	// TODO!! remove this group from all content ACL's !!

	return $ret;
}

function group_byname($name) {
	if((! strlen($name)))
		return false;
	$r = q("SELECT * FROM `group` WHERE `name` = '%s' LIMIT 1",
		dbesc($name)
	);
	if(count($r))
		return $r[0]['id'];
	return false;
}

function group_rmv_member($name,$member) {
	$gid = group_byname($name);
	if(! $gid)
		return false;
	if(! ($gid && $member))
		return false;
	$r = q("DELETE FROM `group_member` WHERE `gid` = %d AND `contact-id` = %d LIMIT 1 ",
		intval($gid),
		intval($member)
	);
	return $r;
}


function group_add_member($name,$member) {
	$gid = group_byname($name);
	if((! $gid) || (! $member))
		return false;

	$r = q("SELECT * FROM `group_member` WHERE `id` = %d AND `contact-id` = %d LIMIT 1",	
		intval($gid),
		intval($member)
	);
	if(count($r))
		return true;	// You might question this, but 
				// we indicate success because the group was in fact created
				// -- It was just created at another time
 	if(! count($r))
		$r = q("INSERT INTO `group_member` (`gid`, `contact-id`)
			VALUES( %d, %d ) ",
			intval($gid),
			intval($member)
	);
	return $r;
}

function group_get_members($gid) {
	$ret = array();
	if(intval($gid)) {
		$r = q("SELECT `group_member`.`contact-id`, `contact`.* FROM `group_member` 
			LEFT JOIN `contact` ON `contact`.`id` = `group_member`.`contact-id` 
			WHERE `gid` = %d ",
			intval($gid)
		);
		if(count($r))
			$ret = $r;
	}
	return $ret;
}



function group_side($every="contacts",$each="group") {

	if(! local_user())
		return;

$createtext = t('Create a new group');
$linktext= t('Everybody');

$o .= <<< EOT

<div id="group-sidebar">
<h3>Groups</h3>

<div id="sidebar-new-group">
<a href="group/new">$createtext</a>
</div>

<div id="sidebar-group-list">
	<ul id="sidebar-group-ul">
	<li class="sidebar-group-li" ><a href="$every" >$linktext</a></li>

EOT;

	$r = q("SELECT * FROM `group` ");
	if(count($r)) {
		foreach($r as $rr)
			$o .= "	<li class=\"sidebar-group-li\"><a href=\"$each/{$rr['id']}\">{$rr['name']}</a></li>\r\n";
	}
	$o .= "	</ul>\r\n	</div>\r\n</div>";	

	return $o;
}

function expand_groups($a) {
	if(! (is_array($a) && count($a)))
		return array();
	$groups = implode(',', $a);
	$groups = dbesc($groups);
	$r = q("SELECT `contact-id` FROM `group_member` WHERE `gid` IN ( $groups )");
	$ret = array();
	if(count($r))
		foreach($r as $rr)
			$ret[] = $rr['contact-id'];
	return $ret;
}