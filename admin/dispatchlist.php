<?php
// $Id: dispatchlist.php 1026 2008-04-14 15:27:26Z marcan $
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
include "header.php";
include_once SMARTOBJECT_ROOT_PATH."class/smartobjecttable.php";

smart_xoops_cp_header();

$dispatch_handler = xoops_getmodulehandler('dispatch');
$newsletter_handler = xoops_getmodulehandler('newsletter', 'smartmail');

if (isset($_REQUEST['add'])) {
    $newsletterid = $_REQUEST['id'] = ($_REQUEST['newsletterid']);
    $number = intval($_REQUEST['number']);
    $start_time = $dispatch_handler->getLastDispatchTime($newsletterid);
    $dispatch_handler->createNextDispatch($newsletterid, $start_time, $number);
}

$newsletterlist = $newsletter_handler->getList();
$adminMenu_title = _NL_AM_DISPATCHES_LIST;

$criteria = new CriteriaCompo();
$criteria->add(new Criteria('dispatch_status', 2, "!="));
if (isset($_REQUEST['id'])) {
    $criteria->add(new Criteria('newsletterid', intval($_REQUEST['id'])));
    $newsletterObj = $newsletter_handler->get(intval($_REQUEST['id']));
    $adminMenu_title .= ' > ' . $newsletterObj->getVar('newsletter_name');
}
smart_adminMenu(1, $adminMenu_title);

$criteria->setSort("dispatch_time");
$criteria->setOrder("ASC");

$collapse_dsc = isset($_REQUEST['id']) ? _NL_AM_DISPATCHES_LIST_FOR_NEWSLETTER_DSC : _NL_AM_DISPATCHES_LIST_DSC;

smart_collapsableBar('dispatches_lists', _NL_AM_DISPATCHES_LIST, $collapse_dsc);
$xoopsTpl->assign('newsletterlist', $newsletterlist);
$dispatches = $dispatch_handler->getObjects($criteria, true, false);
$xoopsTpl->display('db:smartmail_admin_dispatch_add_form.html');

$objectTable = new SmartObjectTable($dispatch_handler, $criteria);
$objectTable->addColumn(new SmartObjectColumn('newsletterid', 'left', 150, 'getNewsletterAdminLink'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_subject'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_status'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_time'));

$objectTable->addCustomAction('getDispatchPreviewLink');
$objectTable->render();
unset($criteria);
smart_close_collapsable('dispatches_lists');

// Dispatched ones, too:
$criteria = new CriteriaCompo(new Criteria("dispatch_status", 1, ">"));
if (isset($_REQUEST['id'])) {
    $criteria->add(new Criteria('newsletterid', intval($_REQUEST['id'])));
    $xoopsTpl->assign('newsletterid', intval($_REQUEST['id']));
}
$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$criteria->setStart($start);
$criteria->setLimit(30);
$criteria->setSort("dispatch_time");
$criteria->setOrder("DESC");

$dispatched = $dispatch_handler->getObjects($criteria, true, false);
$dispatched_count = $dispatch_handler->getCount($criteria);

$collapse_dsc = isset($_REQUEST['id']) ? _NL_AM_SENT_DISPATCHES_LIST_FOR_NEWSLETTER_DSC : _NL_AM_DISPATCHES_LIST_DSC;

smart_collapsableBar('sent_dispatches_lists', _NL_AM_SENT_DISPATCHES_LIST, $collapse_dsc);

$objectTable = new SmartObjectTable($dispatch_handler, $criteria);
$objectTable->addColumn(new SmartObjectColumn('newsletterid', 'left', 150, 'getNewsletterAdminLink'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_subject'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_time'));
$objectTable->addColumn(new SmartObjectColumn('dispatch_receivers'));

$objectTable->render();
unset($criteria);
smart_close_collapsable('sent_dispatches_lists');

smart_modFooter ();
xoops_cp_footer();
?>