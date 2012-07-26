<?php
// $Id: search.php,v 1.1 2004/09/09 05:14:50 onokazu Exp $
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

$xoopsOption['pagetype'] = "search";

include '../../mainfile.php';

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;
$mydirname = basename( dirname( __FILE__ ) ) ;

$config_handler =& xoops_gethandler('config');
$xoopsConfigSearch =& $config_handler->getConfigsByCat(XOOPS_CONF_SEARCH);

if ($xoopsConfigSearch['enable_search'] != 1) {
	header('Location: '.XOOPS_URL.'/index.php');
	exit();
}
$myts =& MyTextSanitizer::getInstance();
$action	= isset($_REQUEST['action']) 	? $myts->stripSlashesGPC($_REQUEST['action']) 	: "search";
$query	= isset($_REQUEST['query']) 	? $myts->stripSlashesGPC($_REQUEST['query']) 	: "";
$andor	= isset($_REQUEST['andor']) 	? $myts->stripSlashesGPC($_REQUEST['andor']) 	: "AND";
$mid 	= isset($_REQUEST['mid']) 	? intval($_REQUEST['mid']) 	: 0;
$uid 	= isset($_REQUEST['uid']) 	? intval($_REQUEST['uid']) 	: 0;
$start 	= isset($_REQUEST['start']) 	? intval($_REQUEST['start']) 	: 0;
$sug 	= isset($_REQUEST['sug']) 	? intval($_REQUEST['sug']) 	: 0;
$mids_p	= isset($_REQUEST['mids'])  	? $_REQUEST['mids']	 	: "";
$mids = array();
if( is_array($mids_p) ) { foreach($mids_p as $e){  $mids[] = intval($e); } }
$query	= str_replace(_MD_NBSP, " ", $query);
$queries = array();
$mb_suggest = array();
$mb_suggest_w = array();
if ( $action == "results" && $query == "" ) {
	redirect_header("index.php",1,_MD_PLZENTER);
	exit();
}

if ( $action == "showall" && ($query == "" || empty($mid)) ) {
	redirect_header("index.php",1,_MD_PLZENTER);
	exit();
}

if ($action == "showallbyuser" && (empty($mid) || empty($uid))) {
	redirect_header("index.php",1,_MD_PLZENTER);
	exit();
}

$groups = ( $xoopsUser ) ? $xoopsUser -> getGroups() : XOOPS_GROUP_ANONYMOUS;
$gperm_handler = & xoops_gethandler( 'groupperm' );
$available_modules = $gperm_handler->getItemIds('module_read', $groups);

if ($action == 'search') {
	include XOOPS_ROOT_PATH.'/header.php';
	$xoopsOption['template_main'] = 'search_index.html';
	include XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/include/searchform.php';
	$search_form  = $search_form->render();
	//Do not remove follows
	$search_form .= '<p><small><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</small></p>';
	$xoopsTpl->assign('search_form', $search_form);
	include XOOPS_ROOT_PATH.'/footer.php';
	exit();
}

if ( $andor != "OR" && $andor != "exact" && $andor != "AND" ) {
	$andor = "AND";
}

if ($action != 'showallbyuser') {
	if ( $andor != "exact" ) {
		$ignored_queries = array(); // holds kewords that are shorter than allowed minmum length
		$temp_queries = preg_split('/[\s,]+/', $query);
		foreach ($temp_queries as $q) {
			$q = trim($q);
			if (strlen($q) >= $xoopsConfigSearch['keyword_min']) {
				$queries[] = $myts->addSlashes($q);
				//for EUC-JP
				if(function_exists('mb_convert_kana') && function_exists('mb_detect_encoding')){
					if(preg_match(_MD_PREG_ZESU, $q) && mb_detect_encoding($q)=="EUC-JP"){ //Zenkaku Eisu
						$mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'a')._MD_HANKAKU_EISU;
						$mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'a');
					}elseif(preg_match(_MD_PREG_HESU, $q)){ //Hankaku Eisu
						$mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'A')._MD_ZENKAKU_EISU;
						$mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'A');
					}elseif(preg_match(_MD_PREG_ZKANA, $q) && mb_detect_encoding($q)=="EUC-JP"){ //Zenkaku Katakana
						$mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'k')._MD_HANKAKU_EISU;
						$mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'k');
					}elseif(preg_match(_MD_PREG_HKANA, $q) && mb_detect_encoding($q)=="EUC-JP"){ //Hankaku Katakana
						$mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'K')._MD_ZENKAKU_EISU;
						$mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'K');
					}else{
						$mb_suggest_w[] = $myts->addSlashes($q);
					}
				}
			}
		}
 		if (count($queries) == 0) {
			redirect_header('index.php', 2, sprintf(_MD_KEYTOOSHORT, $xoopsConfigSearch['keyword_min'], ceil($xoopsConfigSearch['keyword_min']/2) ));
			exit();
		}
	} else {
		$query = trim($query);
		if (strlen($query) < $xoopsConfigSearch['keyword_min']) {
			redirect_header('index.php', 2, sprintf(_MD_KEYTOOSHORT, $xoopsConfigSearch['keyword_min'], ceil($xoopsConfigSearch['keyword_min']/2) ));
 			exit();
		}
		$queries = array($myts->addSlashes($query));
	}
}
switch ($action) {
case "results":
	$module_handler =& xoops_gethandler('module');
	$criteria = new CriteriaCompo(new Criteria('hassearch', 1));
	$criteria->add(new Criteria('isactive', 1));
	$criteria->add(new Criteria('mid', "(".implode(',', $available_modules).")", 'IN'));
	$db =& Database::getInstance();
	$result = $db->query("SELECT mid FROM ".$db->prefix("search")." WHERE notshow!=0");
    	while (list($badmid) = $db->fetchRow($result)) {
		$criteria->add(new Criteria('mid', $badmid, '!='));
	}
	$modules =& $module_handler->getObjects($criteria, true);
	if(count($modules)==0){
		redirect_header("index.php",3,_MD_UNABLE_TO_SEARCH);
		exit();
	}
	if (empty($mids) || !is_array($mids)) {
		unset($mids);
		$mids = array_keys($modules);
	}
	include XOOPS_ROOT_PATH."/header.php";
	$xoopsOption['template_main'] = 'search_result.html';
	$xoopsTpl->assign('lang_search_results', _MD_SEARCHRESULTS);
	$xoopsTpl->assign('lang_keyword', _MD_KEYWORDS);
	if ($andor != 'exact') {
		foreach ($queries as $q) {
			$keywords = array();
			$keywords['key'] = htmlspecialchars(stripslashes($q));
			$xoopsTpl->append('keywords', $keywords);
 		}
 		if (!empty($ignored_queries)) {
			$xoopsTpl->assign('lang_ignoredwors', sprintf(_MD_IGNOREDWORDS, $xoopsConfigSearch['keyword_min']) );
			foreach ($ignored_queries as $q) {
				$badkeywords = array();
				$badkeywords['key'] = htmlspecialchars(stripslashes($q));
				$xoopsTpl->append('badkeywords', $badkeywords);
			}
		}
	} else {
		$keywords = array();
		$keywords['key'] = '"'.htmlspecialchars(stripslashes($queries[0])).'"';
		$xoopsTpl->append('keywords', $keywords);
	}
	if(count($mb_suggest)>0 && $sug!=1){
		$xoopsTpl->assign('lang_sugwords', _MD_KEY_WORD_SUG );
		$sug_url  = XOOPS_URL."/modules/".$mydirname."/index.php";
		$sug_url .= "?andor=".$andor;
		foreach ($mids as $m) {
			$sug_url .= "&mids%5B%5D=".$m;
		}
		$sug_url .= "&action=".$action;
		$sug_url .= "&sug=1";
		$xoopsTpl->assign('sug_url', $sug_url );
		foreach ($mb_suggest as $k=>$m) {
			$sug_keys = array();
			$sug_keys['key'] = htmlspecialchars(stripslashes($m));
			$sug_keys['url'] = $sug_url."&query=".urlencode(stripslashes($mb_suggest_w[$k]));
			$xoopsTpl->append('sug_keys', $sug_keys);
		}
	}
	foreach ($mids as $mid) {
		$mid = intval($mid);
		if ( in_array($mid, $available_modules) ) {
 			$module =& $modules[$mid];
			$results1 =& $module->search($queries, $andor, 5, 0);
			if(count($mb_suggest_w)>0){
				$results2 =& $module->search($mb_suggest_w, $andor, 5, 0);
			}else{
				$results2 = array();
			}
			$results  = array_merge($results1,$results2);
			usort($results, 'sort_by_date');
			$count = count($results);
 			if (!is_array($results) || $count == 0) {
				$no_match = _SR_NOMATCH;
				$showall_link = '';
			} else {
				$no_match = "";
				for ($i = 0; $i < $count; $i++) {
					if (isset($results[$i]['image']) && $results[$i]['image'] != '') {
						$results[$i]['image'] = '/modules/'.$module->getVar('dirname').'/'.$results[$i]['image'];
					} else {
						$results[$i]['image'] = '/modules/'.$mydirname.'/images/posticon.gif';
					}
					$results[$i]['link'] = '/modules/'.$module->getVar('dirname').'/'.$results[$i]['link'];
					$results[$i]['time'] = $results[$i]['time'] ? formatTimestamp($results[$i]['time']) : '';
					$results[$i]['uid'] = intval($results[$i]['uid']);
					if ( !empty($results[$i]['uid']) ) {
						$results[$i]['uname'] = XoopsUser::getUnameFromId($results[$i]['uid']);
					}
				}
				if ( $count == 5 ) {
					$search_url  = XOOPS_URL.'/modules/'.$mydirname.'/index.php?query='.urlencode(stripslashes(implode(' ', $queries)));
					$search_url .= "&mid=$mid&action=showall&andor=$andor";
					$showall_link = '<a href="'.$search_url.'">'._MD_SHOWALLR.'</a>';
				} else {
					$showall_link = '';
				}
			}
			if($module->getVar('dirname')==$mydirname){
				$module_name = _MD_COMMENTS;
			}else{
				$module_name = $myts->htmlSpecialChars($module->getVar('name'));
			}
  			$xoopsTpl->append('modules', array('name' => $module_name, 'results' => $results, 'showall_link' => $showall_link, 'no_match' => $no_match ));
		}
		unset($results1);
		unset($results2);
		unset($results);
		unset($module);
	}
	include "include/searchform.php";
	$search_form  = $search_form->render();
	//Do not remove follows
	$search_form .= '<p><small><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</small></p>';
	$xoopsTpl->assign('search_form', $search_form);
	break;
	
case "showall":
case "showallbyuser":
	include XOOPS_ROOT_PATH."/header.php";
	$db =& Database::getInstance();
	$result = $db->query("SELECT mid FROM ".$db->prefix("search")." WHERE notshow!=0");
	$undisplayable = array();
    	while (list($badmid) = $db->fetchRow($result)) {
		$undisplayable[] = $badmid;
	}
	if( in_array($mid,$undisplayable) || !in_array($mid, $available_modules) ){
		redirect_header("index.php",1,_NOPERM);
		exit();
	}
	$xoopsOption['template_main'] = 'search_result_all.html';
	$module_handler =& xoops_gethandler('module');
	$module =& $module_handler->get($mid);
	$results1 =& $module->search($queries, $andor, 20, $start, $uid);
	if(count($mb_suggest_w)>0){
		$results2 =& $module->search($mb_suggest_w, $andor, 20, $start, $uid);
	}else{
		$results2 = array();
	}
	$results  = array_merge($results1,$results2);
	usort($results, 'sort_by_date');
	$count = count($results);
	if (is_array($results) && $count > 0) {
		$next_results =& $module->search($queries, $andor, 1, $start + 20, $uid);
		$next_count = count($next_results);
		$has_next = false;
		if (is_array($next_results) && $next_count == 1) {
			$has_next = true;
		}
		$xoopsTpl->assign('lang_search_results', _MD_SEARCHRESULTS);
		if ($action == 'showall') {
			$xoopsTpl->assign('lang_keyword', _MD_KEYWORDS);
			if ($andor != 'exact') {
				foreach ($queries as $q) {
					$keywords = array();
					$keywords['key'] = htmlspecialchars(stripslashes($q));
					$xoopsTpl->append('keywords', $keywords);
				}
 			} else {
				$keywords = array();
				$keywords['key'] = '"'.htmlspecialchars(stripslashes($queries[0])).'"';
				$xoopsTpl->append('keywords', $keywords);
			}
		}
		$xoopsTpl->assign('showing', sprintf(_MD_SHOWING, $start+1, $start + $count));
		$xoopsTpl->assign('module_name', $myts->makeTboxData4Show($module->getVar('name')));
		for ($i = 0; $i < $count; $i++) {
			if (isset($results[$i]['image']) && $results[$i]['image'] != '') {
				$results['image'] = '/modules/'.$module->getVar('dirname').'/'.$results[$i]['image'];
			} else {
				$results['image'] = '/modules/'.$mydirname.'/images/posticon.gif';
			}
			$results['title'] = $myts->makeTboxData4Show($results[$i]['title']);
			$results['link'] = '/modules/'.$module->getVar('dirname').'/'.$results[$i]['link'];
			$results['time'] = $results[$i]['time'] ? formatTimestamp($results[$i]['time']) : '';
			$results['uid'] = intval($results[$i]['uid']);
			if ( !empty($results[$i]['uid']) ) {
				$results['uname'] = XoopsUser::getUnameFromId($results[$i]['uid']);
			}
			$xoopsTpl->append('results', $results);
		}
		$navi = '<table><tr>';
		$search_url = XOOPS_URL.'/modules/'.$mydirname.'/index.php?query='.urlencode(stripslashes(implode(' ', $queries)));
		$search_url .= "&mid=$mid&action=$action&andor=$andor";
		if ($action=='showallbyuser') {
			$search_url .= "&uid=$uid";
		}
		if ( $start > 0 ) {
			$prev = $start - 20;
			$navi .= "\n".'<td align="left">';
			$search_url_prev = $search_url."&start=$prev";
			$navi .= "\n".'<a href="'.htmlspecialchars($search_url_prev).'">'._MD_PREVIOUS.'</a></td>';
		}
		$navi .= "\n".'<td>&nbsp;&nbsp;</td>';
		if (false != $has_next) {
			$next = $start + 20;
			$search_url_next = $search_url."&start=$next";
			$navi .= "\n".'<td align="right"><a href="'.htmlspecialchars($search_url_next).'">'._MD_NEXT.'</a></td>';
		}
		$navi .= "\n".'</tr></table>';
		$xoopsTpl->assign('navi', $navi);
	} else {
		$xoopsTpl->assign('no_match', _MD_NOMATCH);
	}
	include "include/searchform.php";
	$search_form  = $search_form->render();
	//Do not remove follows
	$search_form .= '<p><small><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</small></p>';
	$xoopsTpl->assign('search_form', $search_form);
	break;
}
include XOOPS_ROOT_PATH."/footer.php";

//Sub rootin
function sort_by_date($p1, $p2) {
    return ($p2['time'] - $p1['time']);
}

?>