<?php

error_reporting(E_ALL);

$include_dir = "./include";
include ("$include_dir/commonfuncs.php");

$query='';
$domain='';
$type='';
$category='';
$results='';
$start='';

if (isset($_GET['query']))
	$query = $_GET['query'];
if (isset($_GET['domain'])) 
	$domain = $_GET['domain'];
if (isset($_GET['type'])) 
	$type = $_GET['type'];
if (isset($_GET['category'])) 
	$category = $_GET['category'];
if (isset($_GET['results'])) 
	$results = $_GET['results'];
if (isset($_GET['start'])) 
	$start = $_GET['start'];


//if (isset($_GET['search']))
//	$search = $_GET['search'];

//if (isset($_GET['adv'])) 
//	$adv = $_GET['adv'];

//if (isset($_GET['catid'])) 
//	$catid = $_GET['catid'];	


$settings_dir = "./settings"; 
$template_dir =  "./templates"; 
$language_dir = "./languages";

require_once("$settings_dir/database.php");

require_once ("$include_dir/SpellCorrector.php");

require_once("$include_dir/searchfuncs.php");

include "$settings_dir/conf.php";

include "$language_dir/$language-language.php";


// boolean type assigns $type variable 
//if ($type != "or" && $type != "and" && $type != "phrase") { 
//	$type = "and";
//}


// domain name from GET variable 
//if (preg_match("/[^a-z0-9-.]+/", $domain)) {
//	$domain="";
//}


// $results_per_page from results assignment
//if ($results != "") {
//	$results_per_page = $results;
//}
 
 

// whatever here
if (get_magic_quotes_gpc()==1) {
	$query = stripslashes($query);
}

// ensures must be numeric $catid
//if (!is_numeric($catid)) {
//	$catid = "";
//}

// ensures must be numeric $category
//if (!is_numeric($category)) {
//	$category = "";
//}

function saveToLog ($query, $elapsed, $results) {
	global $db;
    if ($results =="") {
        $results = 0;
    }
    $query =  "insert into query_log (query, time, elapsed, results) values ('$query', now(), '$elapsed', '$results')";
	pg_query($db, $query);
	echo pg_last_error($db);                    
}

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}


function limit_per_page($results, $pagination) {
	global $results_per_page;
	
	if (!isset($pagination['start']))
	$pagination['start'] = 1;

	$start_subpos = $results_per_page * ($pagination['start'] - 1);
	
	if (count($results) > 0 )
	$results =  array_slice($results, $start_subpos, $results_per_page);
	
	return $results;
}



list($search_results, $pagination) = get_search_results($query, $start, $category, $type, $results, $domain);

$search_results = limit_per_page($search_results, $pagination);

require("$template_dir/$template/output_results_html.php");


?>