<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<TITLE> FindThat.US </TITLE>
  <link type="text/css" rel="stylesheet" href="templates/standard/search.css">
  <!-- suggest script -->
	<style type="text/css">@import url("include/js_suggest/autocomplete.css");</style>
	
	<script type="text/javascript" src="include/js_suggest/jquery.js"></script>	
	<script type="text/javascript" src="include/js_suggest/autocomplete.js"></script>

  <!-- /suggest script -->
</HEAD>

<BODY>
<h1>FindThat.US</h1>


<?php

?>

<center>
<table cellpadding="5" cellspacing="1" class="searchBox">
<tr>
	<td align="center">

	<form action="index.php" method="get">

<table><tr><td>
	<div style="text-align:left;">
<input type="text" name="query" id='keyword' size="40" value="<?php echo htmlentities($query);?>" autocomplete="off">
<div id="sresults"></div>
</div>
<td>
<input type="submit" value="<?php echo $sph_messages['Search']?>">
</td></tr></table>

	   
<?php  if ($adv==1 || $advanced_search==1) {
?>
	<table width = "100%">
	<tr>
		<td width="40%"><input type="radio" name="type" value="and" <?php print $type=='and'?'checked':''?>><?php print $sph_messages['andSearch']?></td>
		<td><input type="radio" name="type" value="or" <?php print $_REQUEST['type']=='or'?'checked':''?>><?php print $sph_messages['orSearch']?></td></tr>
	<tr>
		<td><input type="radio" name="type" value="phrase" <?php print $_REQUEST['type']=='phrase'?'checked':''?>><?php print $sph_messages['phraseSearch']?></td>
		<td><?php print $sph_messages['show']?>
			<select name='results'>
		      <option <?php  if ($results_per_page==10) echo "selected";?>>10</option>
			  <option <?php  if ($results_per_page==20) echo "selected";?>>20</option>
		      <option <?php  if ($results_per_page==50) echo "selected";?>>50</option>
		      <option <?php  if ($results_per_page==100) echo "selected";?>>100</option>
			</select>
				
	  		<?php print $sph_messages['resultsPerPage']?>   
	  	</td>
	</tr>
	</table>
<?php }?>


	
<?php if ($catid<>0){?>     
	<center><b><?php print $sph_messages['Search']?></b>: <input type="radio" name="category" value="<?php print $catid?>"><?php print $sph_messages['Only in category']?> "<?php print $tpl_['category'][0]['category']?>'" <input type="radio" name="category" value="-1" checked><?php print $sph_messages['All sites']?></center>
<?php  }?>
	<input type="hidden" name="ft" value="1"> 
	</form>
		<?php if ($has_categories && $search==1 && $show_categories){?> 
		<a href="index.php"><?php print $sph_messages['Categories']?></a>
		<?php  }?>	   
	</td>

</tr>
</table>
</center>


<?php if ($pagination['total_results']==0){?>
	<div id ="result_report">
		<?php 
		$msg = str_replace ('%query', $ent_query, $sph_messages["noMatch"]);
		echo $msg;
		?>
	</div>
<?php  }?>	


<?php if ($pagination['total_results'] != 0 && $pagination['from'] <= $pagination['to']){?>
	<div id ="result_report">
	<?php  
	$result = $sph_messages['Results'];
	$result = str_replace ('%from', $pagination['from'], $result);
	$result = str_replace ('%to', $pagination['to'], $result);
	$result = str_replace ('%all', $pagination['total_results'], $result);
	
	$matchword = $sph_messages["matches"];
	if ($pagination['total_results'] == 1) {
		$matchword= $sph_messages["match"];
	} else {
		$matchword= $sph_messages["matches"];
	}

	$result = str_replace ('%matchword', $matchword, $result);	 
	$result = str_replace ('%secs', $pagination['time'], $result);
	echo $result;
	?>
	</div>
<?php  }?>	




<?php if (isset($search_results)) {
?>

<div id="results">

<!-- results listing -->

	<div class="idented">
<?php

if ( count($search_results) > 0)
foreach ($search_results as $item) {

	print 'weight=' . $item['weight'] . 
	'<a href="' . $item['url'] . '" class="title">' . ($item['title']?$item['title']:$sph_messages['Untitled']) .'</a>' . 
	
	$item['domain'] . ' ' . $item['url'] . ' ' . '<div class="description"> ' . $item['description']. '</div>' . ' ' . $item['domain'] . ' ' . 
	
	  ' - ' . $item['size'] . ' kb' . ' ' . $item['url2'] . ' ' . $item['fulltxt'] .  '<br>';
}

?>
	</div>
</div>

<?php }?>


<!-- links to other result pages-->
<?php if (isset($pagination['other_pages'])) {
	if ($adv==1) {
		$adv_qry = "&adv=1";
	}
	if ($type != "") {
		$type_qry = "&type=$type";
	}
?>
	<div id="other_pages">
	<?php print $sph_messages["Result page"]?>:
	<?php if ($start >1){?>
				<a href="<?php print 'index.php?query='.quote_replace(addmarks($query)).'&start='.$pagination['prev'].'&ft=1&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $sph_messages['Previous']?></a>
	<?php  }?>

	<?php  foreach ($pagination['other_pages'] as $page_num) {
				if ($page_num !=$start){?>
					<a href="<?php print 'index.php?query='.quote_replace(addmarks($query)).'&start='.$page_num.'&ft=1&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $page_num?></a>
				<?php } else {?>	
					<b><?php print $page_num?></b>
				<?php  }?>	
	<?php  }?>

	<?php if ($next <= $pages){ ?>	
			<a href="<?php print 'index.php?query='.quote_replace(addmarks($query)).'&start='.$pagination['next'].'&ft=1&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $sph_messages['Next']?></a>
	<?php  }?>

	</div>

<?php }?>



