<?php

	$common = array
		(
		);

	$lines = @file($include_dir.'/common.txt');
	
	$common = $lines;
	
	//if (is_array($lines)) {
	//	while (list($id, $word) = each($lines))
	//		$common[trim($word)] = 1;
	//}
	
	$common= array_map('trim', $common);	// trims every item in the array
											// https://stackoverflow.com/questions/5762439/how-to-trim-white-spaces-of-array-values-in-php


	function countSubstrs($haystack, $needle) {
		$count = 0;
		while(strpos($haystack,$needle) !== false) {
		   $haystack = substr($haystack, (strpos($haystack,$needle) + 1));
		   $count++;
		}
		return $count;
	}
	
	
	function replace_ampersand($str) {
		return str_replace("&", "%26", $str);
	}
	
	
	function quote_replace($str) {

			$str = str_replace("\"",
						  "&quot;", $str);
			return str_replace("'","&apos;", $str);
	}	

	
	$entities = array
		(
		"&amp" => "&",
		"&apos" => "'",
		"&THORN;"  => "Þ",
		"&szlig;"  => "ß",
		"&agrave;" => "à",
		"&aacute;" => "á",
		"&acirc;"  => "â",
		"&atilde;" => "ã",
		"&auml;"   => "ä",
		"&aring;"  => "å",
		"&aelig;"  => "æ",
		"&ccedil;" => "ç",
		"&egrave;" => "è",
		"&eacute;" => "é",
		"&ecirc;"  => "ê",
		"&euml;"   => "ë",
		"&igrave;" => "ì",
		"&iacute;" => "í",
		"&icirc;"  => "î",
		"&iuml;"   => "ï",
		"&eth;"    => "ð",
		"&ntilde;" => "ñ",
		"&ograve;" => "ò",
		"&oacute;" => "ó",
		"&ocirc;"  => "ô",
		"&otilde;" => "õ",
		"&ouml;"   => "ö",
		"&oslash;" => "ø",
		"&ugrave;" => "ù",
		"&uacute;" => "ú",
		"&ucirc;"  => "û",
		"&uuml;"   => "ü",
		"&yacute;" => "ý",
		"&thorn;"  => "þ",
		"&yuml;"   => "ÿ",
		"&THORN;"  => "Þ",
		"&szlig;"  => "ß",
		"&Agrave;" => "à",
		"&Aacute;" => "á",
		"&Acirc;"  => "â",
		"&Atilde;" => "ã",
		"&Auml;"   => "ä",
		"&Aring;"  => "å",
		"&Aelig;"  => "æ",
		"&Ccedil;" => "ç",
		"&Egrave;" => "è",
		"&Eacute;" => "é",
		"&Ecirc;"  => "ê",
		"&Euml;"   => "ë",
		"&Igrave;" => "ì",
		"&Iacute;" => "í",
		"&Icirc;"  => "î",
		"&Iuml;"   => "ï",
		"&ETH;"    => "ð",
		"&Ntilde;" => "ñ",
		"&Ograve;" => "ò",
		"&Oacute;" => "ó",
		"&Ocirc;"  => "ô",
		"&Otilde;" => "õ",
		"&Ouml;"   => "ö",
		"&Oslash;" => "ø",
		"&Ugrave;" => "ù",
		"&Uacute;" => "ú",
		"&Ucirc;"  => "û",
		"&Uuml;"   => "ü",
		"&Yacute;" => "ý",
		"&Yhorn;"  => "þ",
		"&Yuml;"   => "ÿ"
		);

		
	function getHttpVars() {
		$superglobs = array(
			'_POST',
			'_GET',
			'HTTP_POST_VARS',
			'HTTP_GET_VARS');

		$httpvars = array();

		// extract the right array
		foreach ($superglobs as $glob) {
			global $$glob;
			if (isset($$glob) && is_array($$glob)) {
				$httpvars = $$glob;
			 }
			if (count($httpvars) > 0)
				break;
		}
		return $httpvars;
	}
	

	function get_cats($parent) {
		global $db;
		$query = "SELECT * FROM categories WHERE parent_num=$parent";
		
		$result = pg_query($db,$query);
		echo pg_last_error($db);
		$arr[] = $parent;
		if (pg_num_rows($result) <> '') {
			while ($row = pg_fetch_array($result)) {
				$id = $row[category_id];
				$arr = add_arrays($arr, get_cats($id));
			}
		}

		return $arr;
	}	


	function fst_lt_snd($version1, $version2) {

		$list1 = explode(".", $version1);
		$list2 = explode(".", $version2);

		$length = count($list1);
		$i = 0;
		while ($i < $length) {
			if ($list1[$i] < $list2[$i])
				return true;
			if ($list1[$i] > $list2[$i])
				return false;
			$i++;
		}
		
		if ($length < count($list2)) {
			return true;
		}
		return false;
	}	


	function distinct_array($arr) {
		rsort($arr);
		reset($arr);
		$newarr = array();
		$i = 0;
		$element = current($arr);

		for ($n = 0; $n < sizeof($arr); $n++) {
			if (next($arr) != $element) {
				$newarr[$i] = $element;
				$element = current($arr);
				$i++;
			}
		}

		return $newarr;
	}


	function get_dir_contents($dir) {
		$contents = Array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$contents[] = $file;
				}
			}
			closedir($handle);
		}
		return $contents;
	}	

	
	function remove_accents($string) {
		return (strtr($string, "ÀÁÂÃÄÅÆàáâãäåæÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñÞßÿý",
					  "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
	}
	
	
?>