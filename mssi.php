<?php

// Pull in common code
require_once('header.php');

include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/mssi/nusoap.php");
include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/mssi/job_parser.php");
include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/mssi/job_builder.php");
//$xoopsLogger->activated = false; //deactivate logging
//error_reporting(0); //deactivate error reporting

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

	if ($job['key'] != $GLOBALS['xoopsModuleConfig']['newsletter_passphrase']) {
	    return new soap_fault("Key mismatch");
	}

	$job_handler =& xoops_getmodulehandler('job', 'smartmail', true);
	if (!is_object($job_handler)) {
	    return new soap_fault("Could not instantiate job handler");
	}
	$jobRunAfter = empty($job['runAfter']) ? 0 : $job['runAfter'];
	$jobNotRunAfter = empty($job['runNotAfter']) ? 0 : $job['runNotAfter'];

	$job_obj =& $job_handler->create();
	$job_obj->setVar('job_description', $job['description']);
	$job_obj->setVar('job_from_addr', $job['fromAddr']);
	$job_obj->setVar('job_from_name', $job['fromName']);
	$job_obj->setVar('job_subject',	$job['mailTemplate']['subject']);
	$job_obj->setVar('job_is_html', (empty($job['mailTemplate']['bodyHtml']) ? 0 : 1) );
	$job_obj->setVar('job_body_html', $job['mailTemplate']['bodyHtml']);
	$job_obj->setVar('job_body_txt', $job['mailTemplate']['bodyText']);
	$job_obj->setVar('job_vars', serialize($job['commonVars']));
	$job_obj->setVar('job_run_after', $jobRunAfter);
	$job_obj->setVar('job_run_not_after', $jobNotRunAfter);

	if (!$job_handler->insert($job_obj, true)) {
	    // could not insert the job, return
	    return new soap_fault('InsertFailure', 'Job Handler', 'Could not insert job in database', implode('\n', $job_obj->getErrors()));
	}
	// Job is no longer new
	$job_obj->unsetNew();

	$attachment_handler =& xoops_getmodulehandler('jobattachment', 'smartmail');
	foreach ($job['mailTemplate']['attachments'] as $att) {
	    $attachment =& $attachment_handler->create();
	    $attachment->setVar('job_id', $job_obj->getVar('job_id'));
	    $attachment->setVar('cid', $att['cid']);
	    $attachment->setVar('mime_type', $att['mime_type']);
	    $attachment->setVar('value', $att['value']);
		$attachment_handler->insert($attachment, true);
	}

	$insertcount = 0;
	$errstring = "";
	$recipient_handler =& xoops_getmodulehandler('recipient');
	foreach ($job['subscriberList'] as $subscriber) {
	    $recipient =& $recipient_handler->create();
	    $recipient->setVar('job_id', $job_obj->getVar('job_id'));
	    $recipient->setVar('uid', $subscriber['uid']);
	    $recipient->setVar('email', $subscriber['email']);

	    unset($subscriber['uid'], $subscriber['email']);

	    $recipient->setVar('vars', $subscriber);
	    if (!$recipient_handler->insert($recipient, true)) {
	        $errstring .= "Could not insert ".$recipient->getVar('email')." ".implode(',', $recipient->getErrors());
	    }
	    $insertcount++;
	}
	unset($job, $jp);

    $sb = new StatusBuilder();
    $resXml = $sb->getXml($jobId, count($job['subscriberList'])." ".$errstring);

	return $errstring.$resXML;
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>