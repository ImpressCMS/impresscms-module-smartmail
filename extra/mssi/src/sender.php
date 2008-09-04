<?php

// no time-out please 
set_time_limit(0);

define ('SENDER_LOCK_FILE', 'logs/sender.lock');
if (file_exists(SENDER_LOCK_FILE) && filemtime(SENDER_LOCK_FILE) > time() - 20) {
	die('Another instance running...');
}

touch(SENDER_LOCK_FILE);

// pull in the phpmailer code
require_once('include/class.phpmailer.php');
require_once('include/class.smtp.php');

// pull in the MSSI config
require_once('mssi_config.php');

// get max email count in this session
$maxCount = empty($_GET['maxCount']) ? $mailerConfig['MAX_PER_SESSION'] : intval($_GET['maxCount']); 

// Connecting, selecting database
$link = mysql_connect($dbConfig['DB_HOST'], $dbConfig['DB_USER'], $dbConfig['DB_PASS']) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbConfig['DB_NAME'], $link) or die('Could not select database: ' . mysql_error());

while ($maxCount > 0) {

	// query for picking first job in queue
	$pickJobSql = 'select job_id, mail_from_addr, mail_from_name, mail_subject, mail_is_html, mail_body_html, mail_body_txt, vars from mssi_job where job_status <= 1 and job_id in (select distinct job_id from mssi_subscriber where status = 0) order by run_after asc limit 0, 1';
	$result = mysql_query($pickJobSql) or die('Query failed: ' . mysql_error() . ' => ' . $pickJobSql);
	
	if (mysql_num_rows($result) == 0) {
		// get out of loop
		$maxCount = -1;
	} else {
	
		$jobDb = mysql_fetch_assoc($result);
		mysql_free_result($result);
	
		$jobId = $jobDb['job_id'];
		echo date('H:i:s') . ' &gt;&gt; ##### Picked job with jobId = ' . $jobId . '<br/>';

		$mail = new PHPMailer();
		
		$mail->From     = $jobDb['mail_from_addr'];
		$mail->FromName = $jobDb['mail_from_name'];
		$mail->Host     = $mailerConfig['SMTP_SERVERS'];
		$mail->Mailer   = "smtp";
		
		$mail->IsHTML($jobDb['mail_is_html'] == 1);
		
		$mail->Subject = html_entity_decode($jobDb['mail_subject'], ENT_QUOTES);
		
		$bodyHtml = base64_decode($jobDb['mail_body_html']);
		$bodyText = base64_decode($jobDb['mail_body_text']);
		
		$commonVars = unserialize($jobDb['vars']);
		$cvCount = count($commonVars);
		
		$searchVars = array();
		$replaceVars = array();
		
		foreach ($commonVars as $vKey => $vValue) {
			$searchVars[] = '{' . strtoupper($vKey) . '}'; 
			$replaceVars[] = $vValue; 
		}
		
		while ($maxCount > 0) {
			$batchCount = ($maxCount < $mailerConfig['MAILER_BATCH']) ? $maxCount : $mailerConfig['MAILER_BATCH'];
			// get the batch
			$qrySubSql = sprintf("select uid, email, vars from mssi_subscriber where job_id = %d and status = 0 limit 0, %d", $jobId, $batchCount);
			$result = mysql_query($qrySubSql) or die('Query failed: ' . mysql_error() . ' => ' . $qrySubSql);
			
			// if no sunscriber left in the job, continue to next job
			if (mysql_num_rows($result) == 0) break;

			$subIds = array();
			while ($row = mysql_fetch_assoc($result)) {
				
				// delete old sub-vars 
				array_splice($searchVars, $cvCount);
				array_splice($replaceVars, $cvCount);

				$subVars = unserialize($row['vars']);
				$subVars['uid'] = $row['uid'];
				$subVars['email'] = $row['email'];
				
				foreach ($subVars as $vKey => $vValue) {
					$searchVars[] = '{' . strtoupper($vKey) . '}'; 
					$replaceVars[] = $vValue; 
				}
				
				$mail->Body = str_replace($searchVars, $replaceVars, $bodyHtml);
				// $mail->BodyAlt = str_replace($searchVars, $replaceVars, $bodyText);
				
				$mail->AddAddress($row['email']);

    			if($mail->Send()) {
					$subIds[] = $row['uid'];
    			} else {
			        echo date('H:i:s') . ' &gt;&gt; There has been a mail error sending to ' . $row['email'] . '<br>';
    			}
        				
				// clear address for next loop
				$mail->ClearAddresses();
			}
			mysql_free_result($result);
			
			// set them in process 
			$setSubSql = sprintf("update mssi_subscriber set status = 2, status_updated = unix_timestamp() where job_id = %d and uid in (%s)", $jobId, implode(',', $subIds));
			$result = mysql_query($setSubSql) or die('Query failed: ' . mysql_error() . ' => ' . $setSubSql);
			
			echo date('H:i:s') . ' &gt;&gt; Mail sent for batch <i>' . implode(',', $subIds) . '</i><br/>';
			touch(SENDER_LOCK_FILE);
			
			// decrease the counter
			$maxCount = $maxCount - count($subIds);
		}
	}
}

?>