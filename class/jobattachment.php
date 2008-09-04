<?php
// $Id: jobattachment.php 1026 2008-04-14 15:27:26Z marcan $               //
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

class SmartmailJobattachment extends SmartObject {
    function SmartmailJobattachment() {
        $this->initVar("job_id", XOBJ_DTYPE_INT);
        $this->initVar("cid", XOBJ_DTYPE_INT);
        $this->initVar("mime_type", XOBJ_DTYPE_TXTBOX, '');
        $this->initVar("value", XOBJ_DTYPE_TXTAREA, '');
    }
}

class SmartmailJobattachmentHandler extends SmartPersistableObjectHandler {
    function SmartmailJobattachmentHandler($db) {
        parent::SmartPersistableObjectHandler($db, "Jobattachment", array("job_id", "cid"), "", "", "smartmail");
    }
}
?>