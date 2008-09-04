<?php

// Pull in common code
require_once('common.php');

// Create the server instance
$server = new soap_server();
// Initialize WSDL support
$server->configureWSDL('mssiwsdl', 'urn:mssiwsdl');

// Register the method to expose
$server->register('submitJob',			// method name
    array(
		'jobXml' => 'xsd:string' 
	),									// input parameters
    array(
		'statusXml' => 'xsd:string' 
	),									// output parameters
    'urn:mssiwsdl',						// namespace
    'urn:mssiwsdl#submitJob',			// soapaction
    'rpc',								// style
    'encoded',							// use
    'Submit bulk mail job.'				// documentation
);

// Define the method as a PHP function
function submitJob($jobXml) {

	$jp = new JobParser();
	$job = $jp->parse($jobXml);
	
	global $dbConfig;
	
	if ($job['key'] == $dbConfig['API_KEY']) {
		// Connecting, selecting database
		$link = mysql_connect($dbConfig['DB_HOST'], $dbConfig['DB_USER'], $dbConfig['DB_PASS']) or die('Could not connect: ' . mysql_error());
		mysql_select_db($dbConfig['DB_NAME'], $link) or die('Could not select database: ' . mysql_error());
		
		$jobRunAfter = empty($job['runAfter']) ? 0 : $job['runAfter'];
		$jobNotRunAfter = empty($job['runNotAfter']) ? 0 : $job['runNotAfter'];
		
		$jobSql = sprintf("INSERT INTO mssi_job (description, mail_from_addr, mail_from_name, mail_subject, mail_is_html, mail_body_html, mail_body_txt, vars, run_after, run_not_after) VALUES ('%s', '%s', '%s', '%s', %d, '%s', '%s', '%s', %d, %d)", 
			$job['description'], 
			$job['fromAddr'], 
			$job['fromName'], 
			$job['mailTemplate']['subject'], 
			empty($job['mailTemplate']['bodyHtml']) ? 0 : 1, 
			$job['mailTemplate']['bodyHtml'], 
			$job['mailTemplate']['bodyText'],
			serialize($job['commonVars']),
			$jobRunAfter,
			$jobNotRunAfter
		);
		
		$result = mysql_query($jobSql) or die('Query failed: ' . mysql_error() . ' => ' . $jobSql);
		$jobId = mysql_insert_id(); 
		
		foreach ($job['mailTemplate']['attachments'] as $att) {
			$attSql = sprintf("INSERT INTO mssi_job_attachment (job_id, cid, mime_type, value) VALUES (%d, '%s', '%s', '%s')", 
				$jobId,
				$att['cid'], 
				$att['mimeType'], 
				$att['encoding'],
				$att['value']
			);
			$result = mysql_query($attSql) or die('Query failed: ' . mysql_error() . ' => ' . $attSql);
		}
	
		foreach ($job['subscriberList'] as $sub) {
			$uid = $sub['uid'];
			$email = $sub['email'];
			unset($sub['uid'], $sub['email']);
			$subSql = sprintf("INSERT INTO mssi_subscriber (job_id, uid, email, vars) VALUES (%d, %d, '%s', '%s')", 
				$jobId,
				$uid, 
				$email, 
				serialize($sub)
			);
			$result = mysql_query($subSql) or die('Query failed: ' . mysql_error() . ' => ' . $subSql);
		}
		// mysql_free_result($result);
		
		mysql_close($link);

		$sb = new StatusBuilder();
		$resXml = $sb->getXml($jobId, count($job['subscriberList']));
		
		return $resXml;
	} 
	
	return new soap_fault('InvalidKey', 'Job Handler', 'Could not verify passed key.', $job['key']);
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>