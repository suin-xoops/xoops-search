<?php
// $Id: comment_search.php,v 1.0 2005/01/26 17:39:00 suin 
// FILE		::	comment_search.php
// AUTHOR	::	suin <sim@suin.jp>
// WEB		::	AmethystBlue <http://www.suin.jp>
//

function b_search_comment_search($queryarray, $andor, $limit, $offset, $userid){
	global $xoopsDB;
	$sql = "SELECT c.com_id, c.com_modified, c.com_uid, c.com_title, c.com_text, m.name FROM ".$xoopsDB->prefix("xoopscomments")." c,".$xoopsDB->prefix("modules")." m  WHERE com_status=2 AND c.com_modid=m.mid ";
	if ( $userid != 0 ) {
		$sql .= " AND c.com_uid=".$userid." ";
	}
	// because count() returns 1 even if a supplied variable
	// is not an array, we must check if $querryarray is really an array
	if ( is_array($queryarray) && $count = count($queryarray) ) {
		$sql .= " AND ((com_title LIKE '%$queryarray[0]%' OR com_text LIKE '%$queryarray[0]%')";
		for($i=1;$i<$count;$i++){
			$sql .= " $andor ";
			$sql .= "(com_title LIKE '%$queryarray[$i]%' OR com_text LIKE '%$queryarray[$i]%')";
		}
		$sql .= ") ";
	}
	$sql .= "ORDER BY com_modified DESC";
	$result = $xoopsDB->query($sql,$limit,$offset);
	$ret = array();
	$i = 0;

	//本文のサニタイズ用に追記
	$myts =& MyTextSanitizer::getInstance();

 	while($myrow = $xoopsDB->fetchArray($result)){
		$ret[$i]['link'] = "index.php?com_id=".$myrow['com_id'];
		$ret[$i]['title'] = "[".$myrow['name']."] ".$myrow['com_title'];
		$ret[$i]['time'] = $myrow['com_modified'];
		$ret[$i]['uid'] = $myrow['com_uid'];
		//本文始め
		$context = $myrow['com_text'];
		$context = strip_tags($myts->displayTarea(strip_tags($context)));
		$ret[$i]['context'] = search_make_context($context,$queryarray);
		//本文終わり
		$i++;
	}
	return $ret;
}
?>