<?php
// $Id: newsletter.php 1026 2008-04-14 15:27:26Z marcan $                   //
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

class SmartmailNewsletter extends SmartObject {
    var $nl_prefix = "smartmail_nltpl_";

    function SmartmailNewsletter(&$handler) {

    	$this->SmartObject($handler);

        $this->quickInitVar('newsletter_id', XOBJ_DTYPE_INT);
        $this->quickInitVar('newsletter_name', XOBJ_DTYPE_TXTBOX, true, _NL_AM_NAME);
        $this->quickInitVar('newsletter_description', XOBJ_DTYPE_TXTAREA, false, _NL_AM_DESCRIPTION);
        $this->quickInitVar('newsletter_template', XOBJ_DTYPE_TXTBOX, false, _NL_AM_TEMPLATE);
        $this->quickInitVar('newsletter_from_name', XOBJ_DTYPE_TXTBOX, false, _NL_AM_FROMNAME);
        $this->quickInitVar('newsletter_from_email', XOBJ_DTYPE_TXTBOX, false, _NL_AM_FROMEMAIL);
        $this->quickInitVar('newsletter_email', XOBJ_DTYPE_TXTBOX, false, _NL_AM_EMAIL);
        $this->quickInitVar('newsletter_confirm_text', XOBJ_DTYPE_TXTAREA, false, _NL_AM_CONFIRM_TEXT);
        $this->quickInitVar('newsletter_type', XOBJ_DTYPE_INT, false, _NL_AM_NL_TYPE, _NL_AM_NL_TYPE_DSC);
    }

    function getNewsletterAdminLink() {
    	$ret = $this->getVar('newsletter_name');
    	return $ret;
    }

    function getNewsletterRulesLink() {
		$ret = '<a href="' . SMARTMAIL_ADMIN_URL . 'newsletter.php?id=' . $this->id() . '"><img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'configure.png" style="vertical-align: middle;" alt="' . _NL_AM_NEWSLETTERRULES . '" title="' . _NL_AM_NEWSLETTERRULES . '" /></a>';
		return $ret;
    }

    function getNewsletterDispatchesLink() {
		$ret = '<a href="' . SMARTMAIL_ADMIN_URL . 'dispatchlist.php?id=' . $this->id() . '"><img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'klipper_dock2.png" style="vertical-align: middle;" alt="' . _NL_AM_DISPATCHES . '" title="' . _NL_AM_DISPATCHES . '" /></a>';
		return $ret;
    }

    function getNewsletterBlocksLink() {
		$ret = '<a href="' . SMARTMAIL_ADMIN_URL . 'blocks.php?id=' . $this->id() . '"><img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'view_remove.png" style="vertical-align: middle;" alt="' . _NL_AM_BLOCKS . '" title="' . _NL_AM_BLOCKS . '" /></a>';
		return $ret;
    }

	function getNewsletterSubscribersLink() {
		$ret = '<a href="' . SMARTMAIL_ADMIN_URL . 'subscribers.php?id=' . $this->id() . '"><img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'people.png" style="vertical-align: middle;" alt="' . _NL_AM_SUBSCRIBERS . '" title="' . _NL_AM_SUBSCRIBERS . '" /></a>';
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
		global $smartmail_newsletter_handler;
	    include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");

	    if ($action == false) {
	        $action = $_SERVER['REQUEST_URI'];
	    }
	    if ($title == false) {
	        $title = $this->isNew() ? _ADD : _EDIT;
	        $title .= " "._NL_AM_NEWSLETTER;
	    }

	    $form = new XoopsThemeForm($title, 'form', $action);
	    if (!$this->isNew()) {
			$form->addElement(new XoopsFormHidden('id', $this->getVar('newsletter_id')));
		}

		$form->addElement(new XoopsFormText(_NL_AM_NAME, 'newsletter_name', 35, 255, $this->getVar('newsletter_name', 'e')), true);
		$form->addElement(new XoopsFormTextArea(_NL_AM_DESCRIPTION, 'newsletter_description', $this->getVar('newsletter_description', 'e')));
		$form->addElement(new XoopsFormText(_NL_AM_FROMNAME, 'newsletter_from_name', 35, 255, $this->getVar('newsletter_from_name', 'e')), true);
		$form->addElement(new XoopsFormText(_NL_AM_FROMEMAIL, 'newsletter_from_email', 35, 255, $this->getVar('newsletter_from_email', 'e')), true);
		$form->addElement(new XoopsFormText(_NL_AM_EMAIL, 'newsletter_email', 35, 255, $this->getVar('newsletter_email', 'e')), true);

		$form->addElement(new XoopsFormTextArea(_NL_AM_CONFIRM_TEXT, "newsletter_confirm_text", $this->getVar('newsletter_confirm_text', 'e'), 10, 50, "newsletter_confirm_text"));

		$newsletter_type_select = new XoopsFormSelect(_NL_AM_NL_TYPE, 'newsletter_type', $this->getVar('newsletter_type', 'e'));
		$newsletter_type_select->setDescription(_NL_AM_NL_TYPE_DSC);
		$newsletter_type_select->addOptionArray($smartmail_newsletter_handler->getNewsletterTypes());
		$form->addElement($newsletter_type_select, true);

		$member_handler = &xoops_gethandler('member');
		$group_list = &$member_handler->getGroupList();
		$groups_checkbox = new XoopsFormCheckBox(_NL_AM_PERMISSIONS_SELECT, 'newsletter_permissions[]', $this->getPermissions());
		$groups_checkbox->setDescription(_NL_AM_PERMISSIONS_SELECT_DSC);
		foreach ($group_list as $group_id => $group_name) {
		    if ($group_id !== XOOPS_GROUP_ANONYMOUS) {
		        $groups_checkbox->addOption($group_id, $group_name);
		    }
		}
		$form->addElement($groups_checkbox);

		$template_select = new XoopsFormSelect(_NL_AM_TEMPLATE, 'newsletter_template', $this->getVar('newsletter_template', 'e'));
		require_once XOOPS_ROOT_PATH."/class/xoopslists.php";
		$templates = XoopsLists::getFileListAsArray(XOOPS_ROOT_PATH."/modules/".$GLOBALS['xoopsModule']->getVar('dirname')."/templates");
		foreach ($templates as $filename) {
		    $strlen = strlen($this->nl_prefix);
		    if (substr($filename, 0, $strlen) == $this->nl_prefix) {
		        // Template name begins with smartmail_nltpl_
		        $filename = substr($filename, 0, strpos($filename, '.')); // Cut off extension
		        $template_select->addOption(substr($filename, $strlen)); // Cut off prefix
		    }
		}
		$form->addElement($template_select);

		$form->addElement(new XoopsFormHidden('op', 'save'));
		$form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
	    return $form;
	}

	/**
	* Process submissal of form from getForm()
	*
	* @return bool
	*/
	function processFormSubmit() {
        $this->setVar('newsletter_name', $_REQUEST['newsletter_name']);
        $this->setVar('newsletter_description', $_REQUEST['newsletter_description']);
        $this->setVar('newsletter_template', $this->nl_prefix.$_REQUEST['newsletter_template'].".html");
        $this->setVar('newsletter_from_name', $_REQUEST['newsletter_from_name']);
        $this->setVar('newsletter_from_email', $_REQUEST['newsletter_from_email']);
        $this->setVar('newsletter_email', $_REQUEST['newsletter_email']);
        $this->setVar('newsletter_type', $_REQUEST['newsletter_type']);
	    return true;
	}

    /**
	* Process post-save operations following save of object after submissal of form from getForm()
	*
	* @return bool
	*/
	function postSave() {
		$ret = $this->storePermissions($_REQUEST['newsletter_permissions']);

	    return $ret;
	}

	/**
	* Returns rules for this newsletter
	*
	* @return array
	*/
	function getRules() {
	    $rulehandler = xoops_getmodulehandler('rule', 'smartmail');
	    return $rulehandler->getObjects(new Criteria('newsletterid', $this->getVar('newsletter_id')));
	}

	/**
	 * Get next dispatch for this newsletter from start time
	 *
	 * @param int $starttime time to start with
	 *
	 * @return int
	 */
	function getNextDispatch($starttime = null) {
	    if (is_null($starttime)) {
	        $starttime = time();
	    }
	    else {
	        $starttime++;
	    }

	    $rules = $this->getRules();
	    if (count($rules) > 0) {
	        foreach ($rules as $rule) {
	            $dispatchtime = $rule->getNextDispatchTime($starttime);

	            if ((!isset($nexttime) || $dispatchtime < $nexttime) && $dispatchtime > $starttime) {
	                $nexttime = $dispatchtime;
	            }
	        }
	    }
	    else {
	        $nexttime = $starttime + 3600; //add one hour
	    }

	    if (!isset($nexttime)) {
	        return time();
	    }
	    if ($nexttime > time()) {
	        return $nexttime;
	    }
	    return $this->getNextDispatch($nexttime);
	}

	/**
	 * Store newsletter permissions
	 *
	 * @param array $groups groups that are granted a specific permission
	 * @param string $perm_name name of the permission
	 *
	 * @return bool TRUE if success FALSE if fail
	 */
	function storePermissions($groups, $perm_name='smartmail_newsletter_subscribe') {
		$smartModule = smart_getModuleInfo('smartmail');
		$module_id = $smartModule->getVar('mid');
		$gperm_handler = &xoops_gethandler('groupperm');
		// First, if the permissions are already there, delete them
		if (!$gperm_handler->deleteByModule($module_id, $perm_name, $this->getVar('newsletter_id'))) {
			return false;
		}
		$result = true;
		// Save the new permissions
		if (count($groups) > 0) {
			foreach ($groups as $group_id) {
				if (!$gperm_handler->addRight($perm_name, $this->getVar('newsletter_id'), $group_id, $module_id)) {
					$result = false;
				}
			}
		}
		return $result;
	}

	/**
	 * Retreive the groups that are granted a specific permission
	 *
	 * @param string $perm_name name of the permission
	 *
	 * @return array groups that are granted the permission
	 */
	function getPermissions($perm_name='smartmail_newsletter_subscribe') {
		$smartModule = smart_getModuleInfo('smartmail');
		$gperm_handler =& xoops_gethandler('groupperm');

		//Get groups allowed for an item id
		return $gperm_handler->getGroupIds($perm_name, $this->getVar('newsletter_id'), $smartModule->getVar('mid'));
	}

    /**
    * Returns an array representation of the object
    *
    * @return array
    */
    function toArray() {
        $ret = array();
        $vars = $this->getVars();
        foreach (array_keys($vars) as $i) {
            $ret[$i] = $this->getVar($i);
        }
        return $ret;
    }
}

class SmartmailNewsletterHandler extends SmartPersistableObjectHandler {
    function SmartmailNewsletterHandler($db) {
       parent::SmartPersistableObjectHandler($db, "newsletter", "newsletter_id", "newsletter_name", "", "smartmail");
    }

    /**
     * Get list of newsletters allowed by the groups
     *
     * @param array $groups
     * @param Criteria $criteria
     * @return array
     */
    function getAllowedList($groups, $criteria = null) {
		return $this->getAllowedObjects(false, $groups, $criteria);
    }

    /**
     * Get objects of newsletters allowed by the groups
     *
     * @param array $groups
     * @param Criteria $criteria
     * @return array
     */
    function getAllowedNewsletters($groups, $criteria = null) {
		return $this->getAllowedObjects(true, $groups, $criteria);
    }

    /**
     * Get objects of newsletters allowed by the groups
     *
     * @param bool $asObject
     * @param array $groups
     * @param Criteria $criteria
     * @return array
     */
    function getAllowedObjects ($asObject = true, $groups, $criteria = null) {
    	$smartModule = smart_getModuleInfo('smartmail');
		$perm_handler = xoops_gethandler('groupperm');
		$allowed_newsletterids = $perm_handler->getItemIds('smartmail_newsletter_subscribe', $groups, $smartModule->getVar('mid'));
    	$nl_criteria = new CriteriaCompo();
    	if (!is_null($criteria)) {
    		$nl_criteria->add($criteria);
    	}

    	$nl_criteria->add(new Criteria('newsletter_id', "(".implode(',', $allowed_newsletterids).")", "IN"));

		// Adding a criteria to only retun NL that have recipients type set to 1 (users who subscribe to this newsletter)
		$nl_criteria->add(new Criteria('newsletter_type', 1));

    	if ($asObject) {
    		return $this->getObjects($nl_criteria, true);
    	} else {
    		return $this->getList($nl_criteria);
    	}
    }

    /**
     * Get the different newsletter types of subdcribers
     *
     * @return array
     */
    function getNewsletterTypes() {
    	$ret = array(1 => _NL_AM_TYPE_NORMAL,
    				 2 => _NL_AM_TYPE_ALL_WHO_ACCEPT,
    				 3 => _NL_AM_TYPE_ALL);
    	return $ret;
    }
}
?>