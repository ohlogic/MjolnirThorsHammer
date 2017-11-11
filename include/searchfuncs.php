<?php

define ('DEBUG', false);

error_reporting(E_ALL ^ E_NOTICE);

	function makeboollist($a) {
		global $entities, $stem_words, $common;
		
		while ($char = each($entities)) {
			$a = preg_replace("/".$char[0]."/i", $char[1], $a);
		}
		$a = trim($a);

		$a = preg_replace("/&quot;/i", "\"", $a);
		$returnWords = array();
		// get all phrases
		$regs = Array();
		while (preg_match("/([-]?)\"([^\"]+)\"/", $a, $regs)) {
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);
		}
		$a = strtolower(preg_replace("/[ ]+/", " ", $a));
//		$a = remove_accents($a);
		$a = trim($a);
		$words = explode(' ', $a);
		if ($a == "") {
			$limit = 0;
		} else {
			$limit = count($words);
		}
		// get all includes and excludes
		$k = 0;
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {

				if ( !in_array(substr($words[$k], 1), $common)) {
					$includeWords[] = substr($words[$k], 1);		// include words
				}
				else {
					$returnWords['ignore'][] = substr($words[$k], 1);
				}
					
				if (!ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);
					if ($stem_words == 1) {
						$returnWords['hilight'][] = PorterStemmer::Stem(substr($words[$k], 1));
					}
				}
				
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);		// exclude words
				
			} else {
				
				if ( !in_array($words[$k], $common)) {
					$includeWords[] = $words[$k];					// include words
				}
				else {
					$returnWords['ignore'][] = $words[$k];
				}
				
				if (!ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];
					if ($stem_words == 1) {
						$returnWords['hilight'][] = PorterStemmer::Stem($words[$k]);
					}
				}
			}
			$k++;
		}
		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
				$phrase = strtolower(preg_replace("/[ ]+/", " ", $phrase));
				$phrase = trim($phrase);
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word == '')) {
				if (ignoreWord($word)) {
					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}
			}
		}
		
		if ($returnWords['ignore'] != NULL)
		$returnWords['ignore'] = array_unique($returnWords['ignore']);		// removes dups from ignored list
		
		return $returnWords;
	}



	function ignoreword($word) {
		global $common;
		global $min_word_length;
		global $index_numbers;
		
		//echo '<br><br>';
		//echo '[      common    ]' . '<br>';
		//foreach($common as $str){
		//	echo $str .'<br>';
		//}
		
		if ($index_numbers == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < $min_word_length || (!preg_match("/".$pattern."/i", remove_accents($word))) || ($common[$word] == 1)) {
			return 1;
		} else {
			return 0;
		}
	}

function addmarks($a) {
	$a = preg_replace("/[ ]+/", " ", $a);
	$a = str_replace(" +", "+", $a);
	$a = str_replace(" ", "+", $a);
	
	/* if a + don't exist at front then add it except at the very end */
	return $a;
}

function search($searchstr, $start, $category, $type, $per_page, $domain) {
	global $length_of_link_desc,
	$show_meta_description, 
	$merge_site_results, 
	$stem_words, 
	$did_you_mean_enabled,
	$db;
	
	$results = array();
	
	if (DEBUG) {
	
			echo '<br><br>';
			echo '[      +      ]' . '<br>';
			if ( count($searchstr['+']) > 0)
			foreach($searchstr['+'] as $str){
				echo $str .'<br>';
			}
			
			echo '<br><br>';
			echo '[      -      ]' . '<br>';
			if ( count($searchstr['-']) > 0)
			foreach($searchstr['-'] as $str){
				echo $str .'<br>';
			}
			
			echo '<br><br>';
			echo '[      +s     ]' . '<br>';
			if ( count($searchstr['+s']) > 0)
			foreach($searchstr['+s'] as $str){
				echo $str .'<br>';
			}
			
			
			echo '<br><br>';
			echo '[ ignored words ]' . '<br>';
			if ( count($searchstr['ignore']) > 0)	
			foreach($searchstr['ignore'] as $str){
				echo $str .'<br>';
			}	
			
			
			echo '<br><br>';
			echo '[ did you mean ]' . '<br>';
			if ( count($searchstr['+']) > 0)
			foreach($searchstr['+'] as $str){
				echo SpellCorrector::correct($str) . '<br>';

			}
			
			echo '<br><br>';
			echo '[ highlight ]' . '<br>';
			if ( count($searchstr['hilight']) > 0)
			foreach($searchstr['hilight'] as $str){
				echo $str .'<br>';
			}
			
			
			
			echo '<br>';
	}
	
	
	$searchstr = synonym_expansion($searchstr);
	
	
	if (DEBUG) {
		
			echo '<br><br>';
			echo '[ AFTER synonyms added  + ]' . '<br>';
			if ( count($searchstr['+']) > 0)
			foreach($searchstr['+'] as $str){
				echo $str .'<br>';
			}
			
	}
	
	
	
	/*
	$res1 = pg_query($db, "select domain_id from domains where domain = '$domain'");
	if (pg_num_rows($res1)> 0) {
		$thisrow = pg_fetch_array($res1);
		$domain_qry = "and domain = ".$thisrow[0];
	} else {
		$domain_qry = "";
	}	
	*/
	
	
	
	if (count($searchstr['+']) == 0) {
		return null;
	}
	

	//find all sites containing the search phrase
	$wordarray = $searchstr['+s'];
	$phrase_words = 0;
	while ($phrase_words < count($wordarray)) {

		$searchword = addslashes($wordarray[$phrase_words]);
		$query1 = "SELECT link_id, title, url, description from links" . 
			" WHERE fulltxt ilike '% $searchword%'";

		echo pg_last_error($db);
		$result = pg_query($db, $query1);
		$num_rows = pg_num_rows($result);
		
		if ($num_rows == 0) {
			$possible_to_find = 0;
			echo 'did not find any phrase' .'<br>';
			
			break;
		}
		

		echo 'PHRASE SEARCH' . '<br>';
		while ($row = pg_fetch_assoc($result)) {
			
			echo $row['title'].' ' . $row['url'].' '. $row['description'] . '<br>'; 
			
				$details = get_details($row['link_id']);
				$det = pg_fetch_assoc($details);		
				if (DEBUG) printf ("%s %s %s %s\n", $det['title'], 
				$det['url'], $det['description'], $det['size']); 			
			
				$domain = get_domain($row['link_id']);
				$dom = pg_fetch_assoc($domain);	
				if (DEBUG) printf ("%s\n", $dom['domain']); 
				
			
				$weight = 'phase';
				$title = $row['title'];
				$url = $row['url'];
				$description = $row['description'];
				$domain = $dom['domain'];
				$fulltxt = $det['fulltxt'];
				$size = $det['size'];
				$url2 = $url;
				
				
				list($weight, $title, $url, $description, $domain, $fulltxt,
				$size, $url2) = highlight_text($searchstr, $weight, $title, 
				$url, $description, $domain, $fulltxt, $size, 0);	// default max_weight

				
				$found = negation_word_search($searchstr, $row['description'], $det['fulltxt']);

				if ($found == false)
				$results[] = array('weight' => $weight, 'title' => $title, 
				'url' => $url, 'description' => $description, 'domain' => $domain, 
				'fulltxt' => $fulltxt, 'size' => $size, 'url2' => $url2);			

				
		}
		$phrase_words++;
	}
	
	/*
	if (($category > 0) && $possible_to_find == 1) {
		$allcats = get_cats($category);
		$catlist = implode(",", $allcats);
		$query1 = "select link_id from links, sites, categories, site_category where links.site_id = sites.site_id and sites.site_id = site_category.site_id and site_category.category_id in ($catlist)";
		$result = mysqli_query($query1);
		echo mysqli_error();
		$num_rows = mysqli_num_rows($result);
		if ($num_rows == 0) {
			$possible_to_find = 0;
		}
		while ($row = mysqli_fetch_row($result)) {	
			$category_list[$row[0]] = 1;
		}
	}
	*/		

	
		//find all sites that include the search word		
		$wordarray = $searchstr['+'];
		$words = 0;
		$starttime = getmicrotime();
		
		
		while (($words < count($wordarray)) ) {

			$searchword = addslashes($wordarray[$words]);

			$query_select = "SELECT distinct link_id, weight, domain, keyword" . 
				" FROM link_keyword, keywords" . 
				" WHERE link_keyword.keyword_id = keywords.keyword_id" . 
				" AND keyword = '$searchword' ORDER BY weight DESC"; // $domain_qry 
			
			$res_begin = pg_query($db, $query_select);	// returns array
			echo pg_last_error($db);
			
			if ($res_begin == NULL) {
				echo 'it is NULL' .'<br>';
			}
			else {
				$num_rows = pg_num_rows($res_begin);
				
				while ($row = pg_fetch_row($res_begin)) {
					$linklist['id'][] = $row[0];
					$domains[$row[0]] = $row[2];
					$linklist['weight'][$row[0]] += $row[1];
					
				}
			}
			
			$words++;
		}
		
		
		if (count($linklist['id']) > 0)
		{
		
			$max_weight = max($linklist['weight']);

			
			if (DEBUG) echo '<br>';
			if (DEBUG) echo '--UNIQUE ID--' . '<br>';
			$res = array_unique($linklist['id']);
			if (DEBUG) print_r($res);
			
			if (DEBUG) echo '<br>';

			if (DEBUG) echo 'WEIGHT <br>';
			if (DEBUG) print_r($linklist['weight']);
			
			
			arsort($linklist['weight']);
			if (DEBUG) echo '<br>WEIGHT REVERSE SORTED<br>';
			if (DEBUG) print_r($linklist['weight']);
			
			
			if (DEBUG) echo '<br>Array KEYS <br>';
			$arr = array_keys($linklist['weight']);
			if (DEBUG) print_r($arr);
			
		
		
		
		
			if (DEBUG) echo '<br><br>Result:';
			foreach ($arr as $item) {
				if (DEBUG) echo '<br>link_id:' . $item . '<br>';
				if (DEBUG) echo '<br><br>weight:' . $linklist['weight'][$item] . '<br>';
				
				$details = get_details($item);
				$det = pg_fetch_assoc($details);		
				if (DEBUG) printf ("%s %s %s %s\n", $det['title'], $det['url'], $det['description'], $det['size']);	// $det['fulltxt'] 
				
				$domain = get_domain($item);
				$dom = pg_fetch_assoc($domain);	
				if (DEBUG) printf ("%s\n", $dom['domain']); 
				
				
				$weight = $linklist['weight'][$item];
				$title = $det['title'];
				$url = $det['url'];
				$description = $det['description'];
				$domain = $dom['domain'];
				$fulltxt = $det['fulltxt'];
				$size = $det['size'];
				$url2 = $url;
				
				
				list($weight, $title, $url, $description, $domain, $fulltxt,
				$size, $url2) = highlight_text($searchstr, $weight, $title, 
				$url, $description, $domain, $fulltxt, $size, $max_weight);

				$found = negation_word_search($searchstr, $det['description'], $det['fulltxt']);

				if ($found == false)
				$results[] = array('weight' => $weight, 'title' => $title, 
				'url' => $url, 'description' => $description, 'domain' => $domain, 
				'fulltxt' => $fulltxt, 'size' => $size, 'url2' => $url2);
				
			}
		}

	$end = getmicrotime()- $starttime;

	//echo $end . '<br>';
	
	return $results;
}

function synonym_expansion($searchstr) {
	global $db;
	
		// synonym expansion
		$arr = array();
		$arr = $searchstr['+'];
		foreach($arr as $str){
			
			$sql = "SELECT m.synonym FROM synonyms e, synonyms m WHERE m.group_id = e.group_id AND e.synonym = '" . $str . "' and e.root = 1";
			
			$res = pg_query($db, $sql);
			echo pg_last_error($db);
			
			if (pg_num_rows($res) > 0) {
				while ($row = pg_fetch_assoc($res) ){
					if ($row['synonym'] != $str){
						if (DEBUG) echo 'what is the synonym = ' . $row['synonym'] . '<br>';
						$searchstr['+'][] = $row['synonym'];
					}
				}
			}
		}
	return $searchstr;
}


function negation_word_search($searchstr, $description, $fulltxt) {
						
	if (count($searchstr['-']) > 0)
	foreach( $searchstr['-'] as $findit) {
		
		if (trim($findit) == '')
			continue;
		
			$pos = strpos(strtolower($description), $findit);
			$pos2 = strpos(strtolower($fulltxt), $findit);
			
			if ($pos === false && $pos2 === false)
				return false;
			else 
				return true;
	}
}


function highlight_text($searchstr, $weight, $title, $url, $description, $domain, $fulltxt, $size, $max_weight) {
	
	$url2 = $url;
	
	if ($max_weight > 0)
	$weight = number_format(($weight/$max_weight*100),2);
	
	if ($title=='')
		$title = $sph_messages["Untitled"];
	$regs = Array();

	if (strlen($title) > 80) {
		$title = substr($title, 0,76)."...";
	}
	
	if ($searchstr['hilight'] > 0)
	foreach($searchstr['hilight'] as $change) {
		while (preg_match("/[^\>](".$change.")[^\<]/i", " ".$title." ", $regs)) {
			$title = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $title);
		}

		while (preg_match("/[^\>](".$change.")[^\<]/i", " ".$fulltxt." ", $regs)) {
			$fulltxt = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $fulltxt);
		}
		
		while (preg_match("/[^\>](".$change.")[^\<]/i", $url2, $regs)) {
			$url2 = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $url2);
		}
	}
	
	return array($weight, $title, $url, $description, $domain, $fulltxt, $size, $url2);
}



function get_search_results($query, $start, $category, $searchtype, $results, $domain) {
	global $sph_messages, $results_per_page,
		$links_to_next,
		$show_query_scores,
		$desc_length;

	//if ($results != "") {
	//	$results_per_page = $results;
	//}

	// intresting search phrase, seems easy enough
	//if ($searchtype == "phrase") {
	//   $query=str_replace('"','',$query);
	//   $query = "\"".$query."\"";
	//}		
		
	$starttime = getmicrotime();
		
	if ($start == 0) 
		$start = 1;		
		

	$words = makeboollist($query);	// does nothing right now
	
	
	// $words equals the following arrays
	// 
	// $searchstr['+'];	// included
	// $searchstr['-'];	// not included
	// $searchstr['+s']	// search phrase
	
	$results =  search($words, $start, $category, $searchtype, $results_per_page, $domain);
	
	
	$rows = count($results);
	
	$query= stripslashes($query);

	$entitiesQuery = htmlspecialchars($query);
	$pagination['ent_query'] = $entitiesQuery;	
	
	
	$endtime = getmicrotime() - $starttime;

	//$time = $endtime;
	$time = ceil($endtime*1000)/1000;	// or 100/100
	
	$pagination['time'] = $time;
	
	$num_of_results = count($result) - 2;
	
	if ($start < 2)
		saveToLog(addslashes($query), $time, $rows);
	
	
	$from = ($start-1) * $results_per_page+1;
	$to = min(($start)*$results_per_page, $rows);

	
	$pagination['from'] = $from;
	$pagination['to'] = $to;
	$pagination['total_results'] = $rows;
	
	$pages = ceil($rows / $results_per_page);
	
	$pagination['pages'] = $pages;
	
	$prev = $start - 1;
	
	$pagination['prev'] = $prev;
	
	$next = $start + 1;
	
	$pagination['next'] = $next;
	$pagination['start'] = $start;
	$pagination['query'] = $entitiesQuery;
	
	
	if ($from <= $to) {

		$firstpage = $start - $links_to_next;
		if ($firstpage < 1) $firstpage = 1;
		$lastpage = $start + $links_to_next;
		if ($lastpage > $pages) $lastpage = $pages;

		for ($x=$firstpage; $x<=$lastpage; $x++)
			$pagination['other_pages'][] = $x;

	}
	
	return array($results, $pagination);	// similar to tuple 
}


function get_details($id) {
	global $db;
	
	$sql = "SELECT link_id, title, url, description, fulltxt, size FROM links where link_id = $id;";
	
	$res = pg_query($db, $sql);	// returns array
	echo pg_last_error($db);
	
	if ($res == NULL) {
		echo 'it is NULL__get_details' .'<br>';
	} else {
		$num_rows = pg_num_rows($res);
		if ($num_rows > 0)
			return $res;
	}
}


function get_domain($link_id) {
	global $db;	
	
	$sql = "SELECT link_keyword.domain, domains.domain as domain FROM link_keyword, domains WHERE link_keyword.link_id = $link_id and domains.domain_id = link_keyword.domain";
	
	$res = pg_query($db, $sql);	// returns array
	echo pg_last_error($db);
	
	if ($res == NULL) {
		echo 'it is NULL__get_domain' .'<br>';
	} else {
		$num_rows = pg_num_rows($res);
		if ($num_rows > 0)
			return $res;
	}
}

?>