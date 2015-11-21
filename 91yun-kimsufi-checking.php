<?php 
require_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'wp-load.php');
require_once("function.php");

$url=!empty($_GET["url"])?urldecode($_GET["url"]):"";

if($url !== ""){
	
	$r = $wpdb->get_row("select * from ".RKSTABLE." where url='".$url."'");
	echo $r->status;
	echo "||";
	echo $r->dateandtime;
	echo "||";
	echo $r->checkdt;
	echo "||";

}
else{
	echo "url is empty";
}

?>