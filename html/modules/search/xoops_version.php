<?php
// $Id: xoops_version.php,v 1.1 2004/01/29 14:45:48 buennagel Exp $
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

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;
$mydirname = basename( dirname( __FILE__ ) ) ;

$modversion['name'] = _MI_SEARCH_NAME;
$modversion['version'] = 1.0;
$modversion['description'] = _MI_SEARCH_DESC;
$modversion['author'] = "suin(http://www.suin.jp/)";
$modversion['credits'] = "suin";
$modversion['help'] = "";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 0;
$modversion['image'] = "images/search_logo.png";
$modversion['dirname'] = $mydirname;

$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

$modversion['hasMain'] = 1;

$modversion['blocks'][1]['file'] = "search.php";
$modversion['blocks'][1]['name'] = _MI_SEARCH_BLICK1;
$modversion['blocks'][1]['description'] = _MI_SEARCH_BLICK_DESC1;
$modversion['blocks'][1]['show_func'] = "b_search_search_show";
$modversion['blocks'][1]['template'] = "search_block_search.html";
$modversion['blocks'][1]['can_clone'] = true ;

$modversion['templates'][1]['file'] = "search_result.html";
$modversion['templates'][1]['description'] = _MI_SEARCH_TEMPLATE_DESC1;
$modversion['templates'][2]['file'] = "search_result_all.html";
$modversion['templates'][2]['description'] = _MI_SEARCH_TEMPLATE_DESC2;
$modversion['templates'][3]['file'] = "search_index.html";
$modversion['templates'][3]['description'] = _MI_SEARCH_TEMPLATE_DESC3;

//myblockadmin
if( ! empty( $_POST['fct'] ) && ! empty( $_POST['op'] ) && $_POST['fct'] == 'modulesadmin' && $_POST['op'] == 'update_ok' && $_POST['dirname'] == $modversion['dirname'] ) {
	include dirname( __FILE__ ) . "/include/onupdate.inc.php" ;
}


?>