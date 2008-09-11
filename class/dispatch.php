<?php
// $Id: dispatch.php 1026 2008-04-14 15:27:26Z marcan $           //
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Authors: Jan Keller Pedersen (AKA Mithrandir) & Jannik Nielsen (Bitkid)   //
// URL: http://www.idg.dk/ http://www.xoops.org/ http://www.web-udvikling.dk //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
if (!class_exists('SmartPersistableObjectHandler')) {
	include_once(XOOPS_ROOT_PATH . "/modules/smartobject/class/smartobjecthandler.php");
}

if (!class_exists('SmartObject')) {
	include_once(XOOPS_ROOT_PATH . "/modules/smartobject/class/smartobject.php");
}

class SmartmailDispatch extends SmartObject {
    var $newsletter; //Instance of newsletter object
    var $ads = array(); //Array of ads
    var $attachments = array();

    function SmartmailDispatch(&$handler) {
    	$this->SmartObject($handler);

        $this->quickInitVar('dispatch_id', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('newsletterid', XOBJ_DTYPE_INT, true, _NL_AM_NEWSLETTER);
        $this->quickInitVar('dispatch_time', XOBJ_DTYPE_LTIME, false, _NL_AM_TIME);
        $this->quickInitVar('dispatch_subject', XOBJ_DTYPE_TXTBOX, false, _NL_AM_SUBJECT);
        $this->quickInitVar('dispatch_status', XOBJ_DTYPE_INT, false, _NL_AM_STATUS); //0 = not ready, 1 = ready to send, 2 = dispatched, 3 = to be sent, 4 = in progress
        $this->quickInitVar('dispatch_content', XOBJ_DTYPE_TXTAREA, false, _NL_AM_CONTENT);
        $this->quickInitVar('dispatch_receivers', XOBJ_DTYPE_INT, false, _NL_AM_RECEIVERS);
    }

    function getVar($key, $format = 's') {
        if ($format == 's' && in_array($key, array('newsletterid', 'dispatch_status'))) {
            return call_user_func(array($this,$key));
        }
        return parent::getVar($key, $format);
    }

    function newsletterid() {
		$smart_registry = SmartObjectsRegistry::getInstance();
    	$ret = $this->getVar('newsletterid', 'e');
		$obj = $smart_registry->getSingleObject('newsletter', $ret, 'smartmail');

    	if (!$obj->isNew()) {
   			$ret = $obj->getAdminViewItemLink();
    	}
    	return $ret;
    }

    function dispatch_status() {
    	$ret = $this->getVar('dispatch_status', 'e');
    	switch ($ret) {
    		case 1:
    			return _NL_AM_READY;
    			break;
    		case 2:
    			return _NL_AM_DISPATCHED;
    			break;
    		default:
    			return _NL_AM_NOTREADY;
    	}
    }

    function getDispatchPreviewLink() {
    	$js = "javascript: openWithSelfMain('newsletterpreview.php?id=" . $this->id() . "', 'preview', '1000', '700')";
		$ret = '<a href="' . $js . '"><img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'filefind.png" style="vertical-align: middle;" alt="' . _NL_AM_PREVIEW . '" title="' . _NL_AM_PREVIEW . '" /></a>';
		return $ret;
    }

    /**
    * Get a {@link XoopsForm} object for creating/editing objects
    * @param mixed $action receiving page - defaults to $_SERVER['REQUEST_URI']
    * @param mixed $title title of the form
    *
    * @return object
    */
    function getForm($action = false, $title = false) {
        include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");

        if ($action == false) {
            $url_parts = parse_url($_SERVER['REQUEST_URI']);
            $action = $url_parts['path'];
        }
        if ($title == false) {
            $title = $this->isNew() ? _ADD : _EDIT;
            $title .= " "._NL_AM_DISPATCH;
        }

        $form = new XoopsThemeForm($title, 'form', $action);
        if (!$this->isNew()) {
            $form->addElement(new XoopsFormHidden('id', $this->getVar('dispatch_id')));
        }
        else {
            $this->assignVar('dispatch_time', $this->getNextDispatch());
        }
        $form->addElement(new XoopsFormHidden('op', 'save'));
        $form->addElement(new XoopsFormHidden('nid', $this->getVar('newsletterid', 'e')));
        $time = new XoopsFormDateTime(_NL_AM_TIME, 'dispatch_time', 15, $this->getVar('dispatch_time'));
        $time->_name = "dispatch_time"; //XOOPS 2.0.13.2 < fix for missing name attribute
        $form->addElement($time);
        $form->addElement(new XoopsFormText(_NL_AM_SUBJECT, 'dispatch_subject', 75, 255, $this->getVar('dispatch_subject', 'e')));
        $status_radio = new XoopsFormRadio(_NL_AM_STATUS, 'dispatch_status', $this->getVar('dispatch_status', 'e'));
        $status_radio->addOption(0, _NL_AM_NOTREADY);
        $status_radio->addOption(1, _NL_AM_READY);
        $status_radio->addOption(2, _NL_AM_DISPATCHED);
        $form->addElement($status_radio);

        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        return $form;
    }

    /**
	* Process submissal of form from getForm()
	*
	* @return bool
	*/
    function processFormSubmit() {
        $this->setVar('newsletterid', $_REQUEST['nid']);
        $this->setVar('dispatch_time', intval(strtotime($_REQUEST['dispatch_time']['date']) + $_REQUEST['dispatch_time']['time']));
        $this->setVar('dispatch_subject', $_REQUEST['dispatch_subject']);
        $this->setVar('dispatch_status', $_REQUEST['dispatch_status']);

        return true;
    }

    /**
	* Process post-save operations following save of object after submissal of form from getForm()
	*
	* @return bool
	*/
    function postSave() {
        return true;
    }

    /**
	* Builds a newsletter ready to be mailed
	*
	* @param bool $edit if true, the output will have edit links for editing blocks
	*
	* @return string
	*
	**/
    function build($edit = false) {
        $this->loadNewsletter();
        $newsletter_id = $this->getVar('newsletterid', 'e');
        //Assign information
        global $xoopsTpl;
        $xoopsTpl->assign('dispatchid', $this->getVar('dispatch_id'));
        $xoopsTpl->assign('receivercount', $this->getReceiverCount());
        $xoopsTpl->assign('newsletter', $this->newsletter->toArray());

        $timestamp = $this->getVar("dispatch_time");
        $date = array('weekday' => strftime("%A", $timestamp), 'day' => date("d", $timestamp), 'month' => strftime("%B", $timestamp));
        $xoopsTpl->assign('date', $date);

        $newsletterblock_handler = xoops_getmodulehandler('block');
        $newsletterblocks = $newsletterblock_handler->getByNewsletter($newsletter_id, $this->getVar('dispatch_id'), $edit);
        if (isset($newsletterblocks[$this->getVar('newsletterid', 'e')])) {
            $xoopsTpl->assign("blocks", $newsletterblocks[$newsletter_id]);
        }

        //Apply template
        $content['html'] = $xoopsTpl->fetch('db:'.$this->newsletter->getVar('newsletter_template'));

        //Generate subject if not present
        /**
         * @todo find a new way of generating subject automatically
         */
        if ($this->getVar('dispatch_subject') == "") {
        	$subject = $this->newsletter->getVar('newsletter_name','n');
        	$this->setVar('dispatch_subject', $subject);
        }

        //Return content
        return $content;
    }

    /**
	* Send a newsletter dispatch
	*
	* @param bool $preview whether it is a preview send
	* @param string $email email to send to
	*
	* @return bool
	**/
    function send($preview = true, $email = "") {
        //Get articles as array
        $content = $this->build();
        $this->loadNewsletter();

        if ($preview) {
            include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/previewmailer.php");
            $mailer = new NewsletterPreviewMailer();
            $recipients = $email;
        }
        else {
        	$mailer = "smart"; // change to a newsletter preference?
        	// Include class file
        	include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/".strtolower($mailer)."mailer.php");
        	// Calculate class name
        	$classname = "Newsletter".ucfirst($mailer)."Mailer";
        	// Instantiate mailer class
        	$mailer = new $classname($this);
        	// Get recipient list
        	$recipients = $this->getReceiverList();
        }

        $mailer->body = $content['html'];
        $mailer->fromEmail = $this->newsletter->getVar('newsletter_from_email');
        $mailer->fromName = $this->newsletter->getVar('newsletter_from_name');

        $subject = $this->getVar('dispatch_subject', 'n');
        $mailer->subject = $subject ;
        $mailer->attachments = $this->attachments;
        $mailer->dispatchid = $this->getVar('dispatch_id');

        if ($mailer->send($recipients) ) {
            // Temporary commenting - put back when testing has finished
            if (!$preview) {
                $this->setVar('dispatch_content', $content["html"]);
                $this->setVar('dispatch_subject', $subject);
                $this->setVar('dispatch_receivers', $this->getReceiverCount());

                //set dispatch status to "Dispatched"
                $this->setStatus(2);

                if (count($this->newsletter->getRules()) > 0) {
                    //create next dispatch for the newsletter
                    $this->createNextDispatch();
                }
            }
            return true;
        }
        else {
            //If preview, but no email is set, don't send
            return false;
        }
    }

    /**
	* Load newsletter object into property
	*
	* @return void
	**/
    function loadNewsletter() {
        if (!is_object($this->newsletter)) {
            $newsletter_handler = xoops_getmodulehandler('newsletter', 'smartmail');
            $this->newsletter = $newsletter_handler->get($this->getVar('newsletterid', 'e'));
        }
    }

    /**
	* Creates next dispatch based on the newsletter rules
	*
	* @return int
	**/
    function getNextDispatch($starttime = null) {
        $this->loadNewsletter();
        $nexttime = $this->newsletter->getNextDispatch($starttime);
        return isset($nexttime) ? $nexttime : 0;
    }

    /**
	* Get dispatch's {@link Newsletter}
	*
	* @return object
	*/
    function getNewsletter() {
        $this->loadNewsletter();
        return $this->newsletter;
    }

    /**
	* Get the number of recipients to this newsletter
	*
	* @return int
	**/
    function getReceiverCount() {
        $subscriber_handler = xoops_getmodulehandler('subscriber', 'smartmail');
        $criteria = new CriteriaCompo(new Criteria('newsletterid', $this->newsletter->getVar('newsletter_id')));
        $subscribercount = $subscriber_handler->getCount($criteria);
        return $subscribercount;
    }

    /**
	* Get a list of recipients to this newsletter
	*
	* @return array
	**/
    function getReceiverList() {
        $smartmail_subscriber_handler = xoops_getmodulehandler('subscriber', 'smartmail');
        return $smartmail_subscriber_handler->getRecipientList($this->getVar('newsletterid', 'e'), $this->newsletter->getVar('newsletter_type'));
    }

    /**
	 * Create x dispatches from a timestamp
	 *
	 * @param int $start_time timestamp to start from
	 * @param int $number number of dispatches to create
	 *
	 * @return void
	 */
    function createNextDispatch($start_time = 0, $number = 1) {
    	$newsletter_id = $this->getVar('newsletterid', 'e');
        $dispatch_handler = xoops_getmodulehandler('dispatch', 'smartmail');
        if ($start_time == 0) {
            $start_time = $dispatch_handler->getLastDispatchTime($newsletter_id);
        }
        $dispatch_handler->createNextDispatch($newsletter_id, $start_time, $number);
    }

    /**
	* Set status of this to something
	* @param int $status
	*
	* @return bool
	**/
    function setStatus($status) {
        $dispatch_handler = xoops_getmodulehandler('dispatch', 'smartmail');
        $this->setVar('dispatch_status', $status);
        return $dispatch_handler->insert($this, true);
    }
}

class SmartmailDispatchHandler extends SmartPersistableObjectHandler {
    function SmartmailDispatchHandler($db) {
        parent::SmartPersistableObjectHandler($db, "dispatch", "dispatch_id", "dispatch_subject", "", "smartmail");
    }

    /**
    * Returns dispatch objects that are ready for dispatch and are set to dispatch in the past
    *
    * @return array
    */
    function getReadyDispatches() {
        $criteria = new CriteriaCompo(new Criteria('dispatch_status', 1));
        $criteria->add(new Criteria('dispatch_time', time(), "<="));
        return $this->getObjects($criteria, true);
    }

    /**
    * Returns the timestamp of last dispatch
    *
    * @param int $newsletterid Which newsletter to fetch for
    *
    * @retun int
    **/
    function getLastDispatchTime($newsletterid) {
        $sql = "SELECT MAX(dispatch_time) FROM ".$this->table." WHERE newsletterid=".intval($newsletterid);
        $result = $this->db->query($sql);
        list($ret) = $this->db->fetchRow($result);
        return $ret;
    }

    /**
     * Create x dispatches from a timestamp
     *
     * @param int $newsletterid Newsletter to add dispatches for
     * @param int $start_time timestamp to start calculating
     * @param int $number number of dispatches to create
     *
     * @return void
     */
    function createNextDispatch($newsletterid, $start_time, $number) {
        $newsletter_handler = xoops_getmodulehandler('newsletter', 'smartmail');
        $newsletter = $newsletter_handler->get($newsletterid);
        if ($start_time < time()) {
            $start_time = time();
        }
        for ($i = 0; $i < $number; $i++) {
            $next_timestamp = $newsletter->getNextDispatch($start_time);
            //echo "<br />".date('d-m-Y H:i:s', $next_timestamp);
            $next_dispatch = $this->create();
            $next_dispatch->setVar('newsletterid', $newsletterid);
            $next_dispatch->setVar("dispatch_time", $next_timestamp);
            $this->insert($next_dispatch, true);
            $start_time = $next_timestamp;
        }
    }
}
?>