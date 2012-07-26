<?php
// $Id: mylinks.php,v 1.1 2005/02/02 06:04:00 suin 
// FILE		::	mylinks.php
// AUTHOR	::	suin <sim@suin.jp>
// WEB		::	AmethystBlue <http://www.suin.jp>
//
function b_search_mylinks($queryarray, $andor, $limit, $offset, $userid){
	global $xoopsDB;
	$sql = "SELECT l.lid,l.cid,l.title,l.submitter,l.date,t.description FROM ".$xoopsDB->prefix("mylinks_links")." l LEFT JOIN ".$xoopsDB->prefix("mylinks_text")." t ON t.lid=l.lid WHERE status>0";
	if ( $userid != 0 ) {
		$sql .= " AND l.submitter=".$userid." ";
	}
	// because count() returns 1 even if a supplied variable
	// is not an array, we must check if $querryarray is really an array
	if ( is_array($queryarray) && $count = count($queryarray) ) {
		$sql .= " AND ((l.title LIKE '%$queryarray[0]%' OR t.description LIKE '%$queryarray[0]%')";
		for($i=1;$i<$count;$i++){
			$sql .= " $andor ";
			$sql .= "(l.title LIKE '%$queryarray[$i]%' OR t.description LIKE '%$queryarray[$i]%')";
		}
		$sql .= ") ";
	}
	$sql .= "ORDER BY l.date DESC";
	$result = $xoopsDB->query($sql,$limit,$offset);
	$ret = array();
	$i = 0;

	//��ʸ�Υ��˥������Ѥ��ɵ�
	$myts =& MyTextSanitizer::getInstance();

 	while($myrow = $xoopsDB->fetchArray($result)){
		$ret[$i]['image'] = "images/home.gif";
		$ret[$i]['link'] = "singlelink.php?cid=".$myrow['cid']."&amp;lid=".$myrow['lid']."";
		$ret[$i]['title'] = $myts->htmlSpecialChars($myrow['title']);
		$ret[$i]['time'] = $myrow['date'];
		$ret[$i]['uid'] = $myrow['submitter'];
		//��ʸ�Ϥ�
		$context = $myrow['description'];
		$context = strip_tags($myts->displayTarea(strip_tags($context)));
		$ret[$i]['context'] = search_make_context($context,$queryarray);
		//��ʸ�����
		$i++;
	}
	return $ret;
}
?>
