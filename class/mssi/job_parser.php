<?php
define('JP_STATE_INIT', 0);

define('JP_STATE_JOB', 1);
define('JP_STATE_TEMPLATE', 2);
define('JP_STATE_COMMONVAR', 3);
define('JP_STATE_SUBSCRIBERS', 4);

class JobParser {

	var $job, $subscriber;

	var $jpState = JP_STATE_INIT;

	var $parser;

	function JobParser() {

		$this->parser = xml_parser_create();

		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, "tagOpen", "tagClose");
		// xml_set_character_data_handler($this->parser, "gotCdata");
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
	}

	function  &parse($jobXml) {
		// initialize DTO
		$this->job = array (
			'mailTemplate' => array (
				'attachments' => array ()
			),
			'commonVars' => array (),
			'subscriberList' => array ()
		);

		xml_parse($this->parser, $jobXml);

		return $this->job;
	}

	function tagOpen($parser, $tag, $attributes) {

		$this->currentTag = $tag;

		if ($tag == 'subscriber') {
			$this->subscriber = array ();
		} else
		if ($tag == 'var') {
			$varKey = $attributes['name'];
			$varValue = $attributes['value'];
			switch ($this->jpState) {
				case JP_STATE_JOB :
					$this->job[$varKey] = $varValue;
					break;
				case JP_STATE_TEMPLATE :
					if ($varKey != 'attachment') {
						$this->job['mailTemplate'][$varKey] = $varValue;
					} else {
						$attach = array ();
						$attach['cid'] = $attributes['cid'];
						$attach['mimeType'] = $attributes['mimeType'];
						$attach['value'] = $varValue;
						$this->job['mailTemplate']['attachments'][] = $attach;
						unset ($attach);
					}
					break;
				case JP_STATE_COMMONVAR :
					$this->job['commonVars'][$varKey] = $varValue;
					break;
				case JP_STATE_SUBSCRIBERS :
					$this->subscriber[$varKey] = $varValue;
					break;
			}
		} else {
			switch ($tag) {
				case 'mailJob' :
					$this->jpState = JP_STATE_JOB;
					break;
				case 'mailTemplate' :
					$this->jpState = JP_STATE_TEMPLATE;
					break;
				case 'commonVars' :
					$this->jpState = JP_STATE_COMMONVAR;
					break;
				case 'subscriberList' :
					$this->jpState = JP_STATE_SUBSCRIBERS;
					break;
				default :

			}
		}
	}

	function tagClose($parser, $tag) {
		if ($tag == 'subscriber') {
			$this->job['subscriberList'][] = $this->subscriber;
			unset ($this->subscriber);
		}
	}
}
?>