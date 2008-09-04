<?php
// $Id: _add_test_users.php 1026 2008-04-14 15:27:26Z marcan $
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
set_time_limit(1200);
ini_set("Memory limit", "32M"); //make sure we have enough memory

include "header.php";
$xoopsLogger->activated = false;
$user_handler =& xoops_gethandler('user');
for ($i=10; $i < 40000; $i++) {
    $user = $user_handler->create();
    $user->setVar('uname', 'test'.$i);
    $user->setVar('pass', md5('test'.$i));
    $user->setVar('email', 'test'.$i.'@example.com');
    $user_handler->insert($user, true);
}

// include XOOPS_ROOT_PATH."/footer.php";
?>