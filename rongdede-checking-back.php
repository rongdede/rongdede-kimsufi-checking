<?php 
/*
需要在后台定时运行的程序。
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'wp-load.php');
global $wpdb;
require_once("function.php");

$rows = $wpdb->get_results("select * from ".RKSTABLE);
$urls = array();
foreach($rows as $r){
	$urls[] = plugins_url( 'kimsufi-checking.php?id='.$r->id, __FILE__ );
}

function rolling_curl($urls, $delay) { 
$queue = curl_multi_init(); 
$map = array(); 

foreach ($urls as $url) { 
$ch = curl_init(); 

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_NOSIGNAL, true);
curl_setopt($ch, CURLOPT_HEADER, 0);  
curl_setopt($ch, CURLOPT_TIMEOUT, 500);  
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_HTTPGET, true);   
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);  
curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);   
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);  

curl_multi_add_handle($queue, $ch); 
$map[(string) $ch] = $url; 
} 

$responses = array(); 
do { 
while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ; 

if ($code != CURLM_OK) { break; } 

// a request was just completed -- find out which one 
while ($done = curl_multi_info_read($queue)) { 

// get the info and content returned on the request 
$info = curl_getinfo($done['handle']); 
$error = curl_error($done['handle']); 
$results = curl_multi_getcontent($done['handle']); 
$responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results'); 

// remove the curl handle that just completed 
curl_multi_remove_handle($queue, $done['handle']); 
curl_close($done['handle']); 
} 

// Block for data in / output; error handling is done by curl_multi_exec 
if ($active > 0) { 
curl_multi_select($queue, 0.5); 
} 

} while ($active); 

curl_multi_close($queue); 
 return $responses;
} 

function callback($data, $delay) { 
usleep($delay); 
return $data; 
} 


$re=rolling_curl($urls,5);
echo $re;


?>