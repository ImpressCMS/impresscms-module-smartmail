<?php

if (!defined("XOOPS_ROOT_PATH")) {
 	die("XOOPS root path not defined");
}

function xoops_module_update_smartmail($module) {

	include_once(XOOPS_ROOT_PATH . "/modules/" . $module->getVar('dirname') . "/include/functions.php");
	include_once(XOOPS_ROOT_PATH . "/modules/smartobject/class/smartdbupdater.php");

	$dbupdater = new SmartobjectDbupdater();

    ob_start();

	echo "<code>" . _SDU_UPDATE_UPDATING_DATABASE . "<br />";

	// Adding custom_css field in smartmail_page
    $table = new SmartDbTable('smartmail_newsletter');
    if (!$table->fieldExists('newsletter_type')) {
    	$table->addNewField('newsletter_type', "INT(11) NOT NULL default '1'");
    }

    if (!$dbupdater->updateTable($table)) {
        /**
         * @todo trap the errors
         */
    }
	unset($table);

    $feedback = ob_get_clean();
    if (method_exists($module, "setMessage")) {
        $module->setMessage($feedback);
    }
    else {
        echo $feedback;
    }

	return true;
}

function xoops_module_install_smartmail($module) {

    ob_start();

	include_once(XOOPS_ROOT_PATH . "/modules/" . $module->getVar('dirname') . "/include/functions.php");

    $feedback = ob_get_clean();
    if (method_exists($module, "setMessage")) {
        $module->setMessage($feedback);
    }
    else {
        echo $feedback;
    }

	return true;
}
?>