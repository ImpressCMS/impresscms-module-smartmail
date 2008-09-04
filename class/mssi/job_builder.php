<?php



class JobBuilder {

	var $job;

	function JobBuilder($description, $fromAddr, $fromName, $runAfter, $runNotAfter, $key) {

		// initialize DTO 
		$this->job = array (
			'description' => $description,
			'fromAddr' => $fromAddr,
			'fromName' => $fromName,
			'runAfter' => $runAfter,
			'runNotAfter' => $runNotAfter,
			'key' => $key,
			'mailTemplate' => array (
				'attachments' => array ()
			),
			'commonVars' => array (),
			'subscriberList' => array ()
		);		
	}
	
	function setMailTemplate($subject, $bodyHtml, $bodyText) {
		$this->job['mailTemplate']['subject'] = $subject;
		$this->job['mailTemplate']['bodyHtml'] = $bodyHtml;
		$this->job['mailTemplate']['bodyText'] = $bodyText;
	}

	function addAttachment($cid, $mimeType, $value) {
		
		$this->job['mailTemplate']['attachments'][] = array(
			'cid' => $cid,
			'mimeType' => $mimeType,
			'value' => $value
		);
	}
	
	function setCommonVars($vars) {
		$this->job['commonVars'] = $vars;
	}

	function addSubscriber($vars) {
		// uid and email is must and special key
		$this->job['subscriberList'][] = $vars;
	}
	
	function addSubscriberArray($subs) {
		foreach ($subs as $subVars) {
			$this->addSubscriber($subVars);
		}
	}
	
	function getXml() {
		$xmlLines = array();
		// root element mailJob starts
		$xmlLines[] = '<mailJob>';

		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'description', htmlentities($this->job['description'], ENT_QUOTES));
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'fromAddr', $this->job['fromAddr']);
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'fromName', htmlentities($this->job['fromName'], ENT_QUOTES));
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'runAfter', $this->job['runAfter']);
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'runNotAfter', $this->job['runNotAfter']);
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'key', $this->job['key']);
		
		// mailTemplate starts
		$xmlLines[] = '  <mailTemplate>';

		$xmlLines[] = sprintf('    <var name="%s" value="%s" />', 'subject' , htmlentities($this->job['mailTemplate']['subject'], ENT_QUOTES));
		$xmlLines[] = sprintf('    <var name="%s" value="%s" />', 'bodyHtml', base64_encode($this->job['mailTemplate']['bodyHtml']));
		$xmlLines[] = sprintf('    <var name="%s" value="%s" />', 'bodyText', base64_encode($this->job['mailTemplate']['bodyText']));
		// attachments
		foreach ($this->job['mailTemplate']['attachments'] as $attachment) {
			$xmlLines[] = sprintf('    <var name="%s" cid="%s" mimeType="%s" value="%s" />', 'attachment', $attachment['cid'], $attachment['mimeType'], base64_encode($attachment['value']));
		}
		// mailTemplate ends
		$xmlLines[] = '  </mailTemplate>';

		// commonVars starts
		$xmlLines[] = '  <commonVars>';
		// commonVars
		foreach ($this->job['commonVars'] as $varKey => $varValue) {
			$xmlLines[] = sprintf('    <var name="%s" value="%s" />', htmlentities($varKey, ENT_QUOTES), htmlentities($varValue, ENT_QUOTES));
		}
		// commonVars ends
		$xmlLines[] = '  </commonVars>';

		// subscriberList starts
		$xmlLines[] = '  <subscriberList>';
		foreach ($this->job['subscriberList'] as $subscriber) {
			// subscriber starts
			$xmlLines[] = '    <subscriber>';
			// subscriber vars
			foreach ($subscriber as $varKey => $varValue) {
				$xmlLines[] = sprintf('      <var name="%s" value="%s" />', htmlentities($varKey, ENT_QUOTES), htmlentities($varValue, ENT_QUOTES));
			}
			// subscriber ends
			$xmlLines[] = '    </subscriber>';

		}
		// subscriberList ends
		$xmlLines[] = '  </subscriberList>';
		
		// root element mailJob ends
		$xmlLines[] = '</mailJob>';
		
		return implode ("\n", $xmlLines);
	}
}

class StatusBuilder {
	
	function getXml($jobId, $subCount) {
		$xmlLines = array();
		// root element jobStatus starts
		$xmlLines[] = '<jobStatus>';

		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'jobId', $jobId);
		$xmlLines[] = sprintf('  <var name="%s" value="%s" />', 'subCount', $subCount);
		
		// root element jobStatus ends
		$xmlLines[] = '</jobStatus>';
		
		return implode ("\n", $xmlLines);
	}
}

?>
