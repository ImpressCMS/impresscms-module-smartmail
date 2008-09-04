<?php
/*
// TODO - strptime is PHP 5.1 feature, so can't use now
define('GMT_FORMAT', '%A, %d %B %Y %T %Z');
function gmt_to_timestamp($gmtstr) {
	$retval = false;
	if (!empty ($gmtstr)) {
		$ftime = strptime($gmtstr, GMT_FORMAT);
		$retval = gmmktime(
                   $ftime['tm_hour'],
                   $ftime['tm_min'],
                   $ftime['tm_sec'],
                   $ftime['tm_mon'] + 1 ,
                   $ftime['tm_mday'],
                   $ftime['tm_year'] + 1900
                 );
	}
	return $retval;
}
*/
?>
