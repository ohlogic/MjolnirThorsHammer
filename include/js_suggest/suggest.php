<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); // Any notices/warnings will cause errors in suggest javascript
// 0

require_once('../../settings/database.php'); 
require_once('../../settings/conf.php');


$suggest_keywords = true;

if (get_magic_quotes_gpc()==1) {
	$_GET['keyword'] = stripslashes($_GET['keyword']);
} 


$_GET['keyword'] = addslashes($_GET['keyword']);

/*
	if search string too small, do not search for keywords/phrases
*/ 
if (strlen($_GET['keyword'])<3)
{
	$suggest_phrases = false;
	$suggest_keywords = false;
}

/*
	check if search string is phrase
*/ 
if (!strpos($_GET['keyword'],' '))
{
	$suggest_phrases = false;
}


$values = array();

if ($suggest_keywords)
{
		$result = pg_query($db, $sql = "
		SELECT keyword, count(keyword) as results 
		FROM keywords INNER JOIN link_keyword USING (keyword_id) 
		WHERE keyword LIKE '" . $_GET['keyword'] . "%' 
		GROUP BY keyword 
		ORDER BY results desc 
		LIMIT $suggest_rows
		");
		if($result && pg_num_rows($result)) {		
		    while($row = pg_fetch_array($result)) {
		        $values[$row['keyword']] = $row['results'];
		    }    
		}

	arsort($values);
	$values = array_slice($values, 0, $suggest_rows);
}


if (is_array($values))
{
	arsort($values); 
	if (is_array($values)) foreach ($values as $_key => $_val) {
		$suffix = ($_val>1)?'s':'';
		$js_array[] = '"' . str_replace($_GET['keyword'], '<b>' . $_GET['keyword'] . '</b>' , str_replace('"','\"',$_key) ) . '", "<small><b>' . $_val . '</b> result'.$suffix.'</small>"';
	}
	
	if (count($js_array) > 0)
	echo json_encode(  implode(", ", $js_array)  );
	else
	echo json_encode('');
}

?>
