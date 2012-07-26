<?
//nao-ponさんの本文表示ハック
function search_make_context($text,$words,$l=255)
{
	static $strcut = "";
	if (!$strcut)
		$strcut = create_function ( '$a,$b,$c', (function_exists('mb_strcut'))?
			'return mb_strcut($a,$b,$c);':
			'return strcut($a,$b,$c);');
	
	if (!is_array($words)) $words = array();
	
	$ret = "";
	$q_word = str_replace(" ","|",preg_quote(join(' ',$words),"/"));
	
	if (preg_match("/$q_word/i",$text,$match))
	{
		$ret = ltrim(preg_replace('/\s+/', ' ', $text));
		list($pre, $aft)=preg_split("/$q_word/i", $ret, 2);
		$m = intval($l/2);
		$ret = (strlen($pre) > $m)? "... " : "";
		$ret .= $strcut($pre, max(strlen($pre)-$m+1,0),$m).$match[0];
		$m = $l-strlen($ret);
		$ret .= $strcut($aft, 0, min(strlen($aft),$m));
		if (strlen($aft) > $m) $ret .= " ...";
	}
	
	if (!$ret)
		$ret = $strcut($text, 0, $l);
	
	return $ret;
}

function sort_by_date($p1, $p2) {
    return ($p2['time'] - $p1['time']);
}

function &context_search( $funcname, $queryarray, $andor = 'AND', $limit = 0, $offset = 0, $userid = 0){

	if( $funcname=="" ){
		return false;
	}
	return $funcname($queryarray, $andor, $limit, $offset, $userid);

}
?>