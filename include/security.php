<?php

function can_write_wall(&$a,$owner) {
        if((! (local_user())) && (! (remote_user())))
                return false;
        if((local_user()) && ($_SESSION['uid'] == $owner))
                return true;
	$sql_extra = (($a->config['rockstar']) ? '' : sprintf(" AND `readonly` = 0  AND `rel` IN ( %d , %d ) ",
							intval(DIRECTION_OUT),
							intval(DIRECTION_BOTH)) );
		
        $r = q("SELECT * FROM `contact` WHERE `id` = %d AND `blocked` = 0 AND `pending` = 0 $sql_extra LIMIT 1",
                intval($_SESSION['visitor_id'])
        );
        if(count($r))
                return true;
        return false;

}
