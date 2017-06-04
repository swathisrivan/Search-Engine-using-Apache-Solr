<?php

header('Content-Type: text/html; charset=utf-8');


$sugg_arr = array();
$tem      = array();
$l        = 0;
$i        = 0;
$ta       = "";
$st       = "";

if (isset($_REQUEST['term'])) 
{
	$query = $_REQUEST['term'];
	$query = strtolower($query);
}

if ($query)
{
	$tem = explode(" ", $query);	 
	$l   = count($tem);
	if (get_magic_quotes_gpc() == 1)
	{
		$query = stripslashes($tem[$l - 1]);
	}
	else
	{
		$query = ($tem[$l - 1]);
	}

	$l = $l - 1;
	
	try
	{
		$i = 0;
		foreach ($tem as $te)
		{
			if($i === $l)
			{
				break;
			}
			$ta = $ta." ".$te;
                        $i  = $i + 1;
	
		}
			
		$results = file_get_contents("http://localhost:8983/solr/abcNewsCore/suggest?indent=on&q=".$query."&wt=json");
		$results = json_decode($results);
		$res_arr = $results->suggest->suggest->{$query}->suggestions;
		foreach ($res_arr as $value) 
		{
			if (!preg_match('/[^a-z]/', (string)$value->term))
                        {
				$st = $ta . " " . (string)$value->term;
				$st = trim($st);
				array_push($sugg_arr, $st); 
			}
			$st = "";   			
		}
		echo json_encode($sugg_arr);

	}
	catch (Exception $e)
	{
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}

}

?>
