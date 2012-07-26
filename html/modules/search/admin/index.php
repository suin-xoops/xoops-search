<?php
include '../../../mainfile.php';
include_once XOOPS_ROOT_PATH.'/include/cp_header.php';
$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
if ( file_exists(XOOPS_ROOT_PATH."/modules/".$mydirname."/language/".$xoopsConfig['language']."/modinfo.php") ) {
	include XOOPS_ROOT_PATH."/modules/".$mydirname."/language/".$xoopsConfig['language']."/modinfo.php";
} else {
	include XOOPS_ROOT_PATH."/modules/".$mydirname."/language/japanese/admin.php";
}

// security check
if( ! isset( $module ) || ! is_object( $module ) ) $module = $xoopsModule ;
else if( ! is_object( $xoopsModule ) ) die( '$xoopsModule is not set' )  ;
$op = isset($_REQUEST['op']) ? trim($_REQUEST['op']) : 'default' ;
switch($op){
case 'default':
	include_once 'menu.php';
	xoops_cp_header();
        echo "\n".'<table width="100%" border="0" cellspacing="1" class="outer">';
        echo "\n".'<tr><td class="odd">';
        echo "\n".'<a href="index.php"><h4>' ._MI_SEARCH_NAME. '</h4></a>';
        echo "\n".'<table border="0" cellpadding="4" cellspacing="1" width="100%">';
	while( list($k, $v) = each($adminmenu) )
	{
	        echo "\n".'<tr class="bg1" align="left">';
	        echo "\n".'<td><span class="fg2"><a href="'.XOOPS_URL.'/modules/'.$mydirname.'/'.$adminmenu[$k]['link']. '">' .$adminmenu[$k]['title']. '</a></span></td>';
	        echo "\n".'<td><span class="fg2">' .$adminmenu[$k]['desc']. '</span></td>';
	        echo "\n".'</tr>';
	}
        echo "\n".'</table>';
        echo "\n".'</td></tr>';
        echo "\n".'</table>';
	xoops_cp_footer();
	break;
case 'tpl':
	header('Location: '.XOOPS_URL.'/modules/system/admin.php?fct=tplsets&op=listtpl&tplset='.$xoopsConfig['template_set'].'&moddir='.$mydirname.'');
	break;
}
?>