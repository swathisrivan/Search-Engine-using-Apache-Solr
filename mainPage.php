<?php
// make sure browsers see this page as utf-8 encoded HTML

include 'SpellCorrector.php';

include 'Apache/Solr/Service.php';

$c  = "";
$op = array();

if (($handle = fopen("mapABCNewsDataFile.csv", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

        $op[$data[0]] = $data[1];
    }
    fclose($handle);
}

$additionalParameters = array(
 'sort' => 'pageRankFile desc',
);

header('Content-Type: text/html; charset=utf-8');

$limit   = 10;
$query   = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$is_PR   = false;
$tem     = array();
$c       = array();
$c_flag  = 0;
$orig    = "";

if (isset($_REQUEST['Rank_Search']) && ($_REQUEST['Rank_Search'] === "PR_Search"))
{
	$is_PR = true;
}
?>
<?php if(isset($_REQUEST['submit'])):?>
<?php
if ($query)
{
	$query   = trim($query);
	$query   = strtolower($query);
	$orig    = $query;
	$tem     = array();
	$c       = array();
	$c_flag  = 0;

	$tem = explode(" ", $query);
	
	$query = "";
	
	foreach ($tem as $te) 
	{
		$c[$te] = $te;
    		$c[$te] = SpellCorrector::correct($te);
		if($c[$te] != $te)
		{
			$c_flag = 1;
		}
		$query = $query . $c[$te] . " ";
	}
		
	$query = trim($query);
 	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/abcNewsCore/');
 
	// if magic quotes is enabled then stripslashes will be needed

	if (get_magic_quotes_gpc() == 1)
	{
		$query = stripslashes($query);
	}

	try
	{
		if($is_PR === true)
		{
			$results = $solr->search($query, 0, $limit, $additionalParameters);	

		}
		else
		{
			$results = $solr->search($query, 0, $limit);
		}
		
	}
	catch (Exception $e)
	{
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
}

?>
<?php endif; ?>
<html>
<head>
<title>PHP Solr Client Example</title>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#q" ).autocomplete({
      source: 'test.php'
    });
  } );

  </script>
</head>
<body>
<form accept-charset="utf-8" method="get">
<div class="ui-widget">
<label for="q">Search:</label>
<input id="q" name="q" type="text" value="<?php if($_REQUEST["submit"]) echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); if($_REQUEST["val_submit"]) echo htmlspecialchars($_REQUEST["val_submit"], ENT_QUOTES, 'utf-8');?>"/>
<input type="submit" name="submit"/>
</div>
<br>
<form>
<input type="radio" name="Rank_Search"
value="Lucene_Search" <?php echo ($is_PR === false)?'checked':'unchecked' ?>>Lucene Search
<input type="radio" name="Rank_Search"
value="PR_Search" <?php echo ($is_PR === true)?'checked':'unchecked' ?>>PageRank Search
</form>

<?php if(isset($_REQUEST['submit'])):?>
<?php
// display results
if ($results)
{
	$total = (int) $results->response->numFound;
	$start = min(1, $total);
	$end = min($limit, $total);
	?>
	<?php
	if($c_flag == 1)
	{ ?>
	<h3><?php echo "Showing Results for "?><span style="color:blue"><i><?php echo $query ?></i><span></h3>
	<h3 style = "diplay:inline"><?php echo "Search instead for "?><form style = "display:inline" accept-charset="utf-8" method="get"><input type="submit" VALUE="<?php echo htmlspecialchars($orig, ENT_QUOTES, 'utf-8'); ?>" NAME ="val_submit" style ='background-color: transparent;text-decoration: underline; border: none;color: blue;cursor: pointer;'></input></form></h3><br/>
	<?php
	}
	?>
		<div style = "width: 200px; word-wrap: break-word;">Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
		<ol>
		<?php
		// iterate result documents
		foreach ($results->response->docs as $doc)
		{
			$id = substr($doc->id, strrpos($doc->id, '/') + 1);
			?>
				<li style="list-style:none">
				<table style="text-align: left">
				<tr>
					<th>
                                        <?php
                                                if($doc->title)
                                                {
					?>
					<a  href = <?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');?> >
					<?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');
						}
						else
						{
					?>
					<a href = <?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8');?> >
                                        <?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8'); 

						}
					
                                        ?>
                                        </th>

				</tr>
				<tr>
					<td><a  style = "color:#468942" href = <?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8');?> >
					<?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8'); ?>
					</td>
				</tr>
				<tr>
					<td>
					<?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');
					?>
					</td>
				</tr>
				<tr>
					<td style = "font-size:12">
					<i>
						<?php
						$id      = htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');
						$lines   = file_get_contents("ABCNewsDownloadData/".$id);
						$d       = new DOMDocument();
						$mock    = new DOMDocument();
						$d->loadHTML($lines);
		
						while (($r = $d->getElementsByTagName("script")) && $r->length) 
						{
     							$r->item(0)->parentNode->removeChild($r->item(0));
    						}
	
						while (($r = $d->getElementsByTagName("style")) && $r->length)
                                                {
                                                        $r->item(0)->parentNode->removeChild($r->item(0));
                                                }

						$body    = $d->getElementsByTagName('body')->item(0);
				
						foreach ($body->childNodes as $child){
						    $mock->appendChild($mock->importNode($child, true));
						}
						$text = $mock->saveHTML();
						$text = strip_tags($text);
						$text = strtolower($text);
						$query = trim($query);
						$regex = '/[a-zA-Z][^\\.;]*('.strtolower($query).')[^\\.;]*/';
						$matches = array();
                                                preg_match_all($regex, $text, $matches);
						echo substr($matches[0][0], 0, 200);
						echo substr($matches[0][1], 0, 200);
						?>
					</i>
					</td>
				</tr>
				<tr>
					<td>
					<?php 
					        if($doc->og_description)
						{	
							echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8'); 
						}
						else
						{
							echo "Description not available for this web page";
						}
					?>
					</td>
				</tr>
				</table>
				</li>
				<br/>
				<?php
		}
	?>
		</ol>
		<?php
}
?>
<?php endif; ?>
<?php if(isset($_REQUEST['val_submit'])):?>
<?php
	$n_query = "";
	$n_query = $_REQUEST['val_submit'];
	$n_query = trim($n_query);
	$n_query = strtolower($n_query); 
	$n_solr  = new Apache_Solr_Service('localhost', 8983, '/solr/abcNewsCore/');


        if (get_magic_quotes_gpc() == 1)
        {
                $n_query = stripslashes($n_query);
        }

        try
        {
                $n_results = $n_solr->search($n_query, 0, 10);

        }
        catch (Exception $e)
        {
                die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
        }
	if ($n_results)
	{
        	$total = (int) $n_results->response->numFound;
        	$start = min(1, $total);
        	$end = min($limit, $total);
	}
	if ($n_results)
	{
        	$total = (int) $n_results->response->numFound;
        	$start = min(1, $total);
        	$end = min($limit, $total);
	}
	?>
	<div style = "width: 200px; word-wrap: break-word;">Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
	<ol>
	<?php
	// iterate result documents
	foreach ($n_results->response->docs as $doc)
	{
		$id = substr($doc->id, strrpos($doc->id, '/') + 1);
	?>
		<li style="list-style:none">
		<table style="text-align: left">
		<tr>
		<th>
		<?php
		if($doc->title)
		{
			?>
				<a  href = <?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');?> >
				<?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');
		}
		else
		{
			?>
				<a href = <?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8');?> >
				<?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8');

		}

		?>
		</th>
		</tr>
	
		<tr>
		<td><a  style = "color:#468942" href = <?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8');?> >
		<?php echo htmlspecialchars($op[$id], ENT_NOQUOTES, 'utf-8'); ?>
		</td>
		</tr>
		<tr>
		<td>
		<?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');
		?>
		</td>
		</tr>
		<tr>
		<td>
		<?php
		$id      = htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');
		$lines   = file_get_contents("ABCNewsDownloadData/".$id);
		$d       = new DOMDocument();
		$mock    = new DOMDocument();
		$d->loadHTML($lines);

		while (($r = $d->getElementsByTagName("script")) && $r->length)
		{
			$r->item(0)->parentNode->removeChild($r->item(0));
		}

		while (($r = $d->getElementsByTagName("style")) && $r->length)
		{
			$r->item(0)->parentNode->removeChild($r->item(0));
		}

		$body    = $d->getElementsByTagName('body')->item(0);

		foreach ($body->childNodes as $child){
			$mock->appendChild($mock->importNode($child, true));
		}
		$text = $mock->saveHTML();
		$text = strip_tags($text);
		$text = strtolower($text);
		$n_query = trim($n_query);
		$regex = '/[a-zA-Z][^\\.;]*('.strtolower($n_query).')[^\\.;]*/';
		$matches = array();
		preg_match_all($regex, $text, $matches);
		echo substr($matches[0][0], 0, 200);
		echo substr($matches[0][1], 0, 200);
		//var_dump($matches);

		?>
		</td>
		</tr>
		<tr>
		<td>
		<?php
		if($doc->og_description)
		{
			echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8');
		}
		else
		{
			echo "Description not available for this web page";
		}
		?>
		</td>
		</tr>
		</table>
		</li>
	<?php
	}
	?>
	</ol>
	</div>

<?php endif; ?>
</body>
<style>
a{
text-decoration:none;
}
a:hover {
   text-decoration: underline;
}
table,td,th{
width: 800px; 
word-wrap: break-word;
}
</style>
</html>

