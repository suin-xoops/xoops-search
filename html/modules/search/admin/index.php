<?php
include '../../../mainfile.php';
include_once XOOPS_ROOT_PATH.'/include/cp_header.php';
$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
if ( file_exists(XOOPS_ROOT_PATH."/modules/".$mydirname."/language/".$xoopsConfig['language']."/modinfo.php") ) {
	include XOOPS_ROOT_PATH."/modules/".$mydirname."/language/".$xoopsConfig['language']."/modinfo.php";
} else {
	include XOOPS_ROOT_PATH."/modules/".$mydirname."/language/japanese/admin.php";
}

$footer = '<p><small><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</small></p>';

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
	echo "\n".'<tr class="bg1" align="left">';
	echo "\n".'<td><span class="fg2"><a href="'.XOOPS_URL.'/modules/system/admin.php?fct=preferences&op=showmod&mod='.$module->getvar('mid').'">'._PREFERENCES.'</a></span></td>';
	echo "\n".'<td><span class="fg2"></span></td>';
	echo "\n".'</tr>';
        echo "\n".'</table>';
        echo "\n".'</td></tr>';
        echo "\n".'</table>';
	echo "\n".$footer;
	xoops_cp_footer();
	break;
case 'edit':
	$criteria = new CriteriaCompo();
	$criteria->add(new Criteria('hassearch', 1));
	$criteria->add(new Criteria('isactive', 1));
	$module_handler =& xoops_gethandler('module');
	$mod_arr = $module_handler->getList($criteria);
	$db =& Database::getInstance();
	$myrow = array();
	$result = $db->query("SELECT mid FROM ".$db->prefix("search")." WHERE notshow!=0");
    	while (list($mid) = $db->fetchRow($result)) {
		$myrow[] = $mid;
	}
	xoops_cp_header();
        echo "\n".'<table width="100%" border="0" cellspacing="1" class="outer">';
        echo "\n".'<tr><td class="odd">';
        echo "\n".'<h4>' ._MI_SEARCH_MENU4. '</h4>';
        echo "\n".'<div>' ._AM_CHECK_MODULE_NOT_TO_SEARCH. '</div>';
        echo "\n".'<form action="index.php" method="POST">';
        echo "\n".'<table border="0" cellpadding="4" cellspacing="1" width="100%">';
	while( list($mid, $name) = each($mod_arr) )
	{
		$check = in_array($mid, $myrow) ? " checked" : "" ;
		$exval = in_array($mid, $myrow) ? 1 : 0 ;
		$color = in_array($mid, $myrow) ? "red" : "black" ;
	        echo "\n".'<tr class="bg1" align="left">';
	        echo "\n".'<td><span class="fg2" style="color:'.$color.' ;"><input type="checkbox" name="dis_mids[' .$mid. ']" value="1"' .$check. '><input type="hidden" name="old_mids[' .$mid. ']" value="'. $exval .'">&nbsp;' .$name. '</td>';
	        echo "\n".'</tr>';
	}
        echo "\n".'</table>';
	echo "\n".'<input type="hidden" name="op" value="updata">';
	echo "\n".'<input type="submit" value="' ._SEND. '">';
        echo "\n".'</form>';
        echo "\n".'</td></tr>';
        echo "\n".'</table>';
	echo "\n".$footer;
	xoops_cp_footer();
	break;
case 'updata':
	$dis_mids = isset($_REQUEST['dis_mids']) ? $_REQUEST['dis_mids'] : array() ;
	$old_mids = isset($_REQUEST['old_mids']) ? $_REQUEST['old_mids'] : array() ;
	$db =& Database::getInstance();
	foreach($old_mids as $i => $v){
		$dis_mids[$i] = empty($dis_mids[$i]) ? 0 : 1;
		if ($old_mids[$i] != $dis_mids[$i]) {
			$result = $db->query("SELECT COUNT(*) FROM ".$db->prefix('search')." WHERE mid=".$i);
			list($count) = $db->fetchRow($result);
			if ( $count > 0 ) {
				$db->query('UPDATE '.$db->prefix('search').' SET notshow ='.$dis_mids[$i].' WHERE mid ='.$i);
			}else{
				$db->query("INSERT INTO ".$db->prefix('search')." (mid, notshow) VALUES (".$i.", ".$dis_mids[$i].")");
			}
		}
	}
	redirect_header('index.php?op=edit',2,_AM_DBUPDATED);
	break;
case 'tpl':
	header('Location: '.XOOPS_URL.'/modules/system/admin.php?fct=tplsets&op=listtpl&tplset='.$xoopsConfig['template_set'].'&moddir='.$mydirname.'');
	break;
}
?>