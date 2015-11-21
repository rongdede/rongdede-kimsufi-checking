<?php 
require_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'wp-load.php');
require_once("function.php");


function url_get_contents($strUrl, $boolUseCookie=false)  
{  
    $ch = curl_init($strUrl);  
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, 0);  
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_HTTPGET, true);   
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);  
    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);   
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);  
    if ($boolUseCookie && is_array($_COOKIE) && count($_COOKIE) > 0) {  
        $cookie_str = '';  
        foreach($_COOKIE as $key => $value) {  
            $cookie_str .= "$key=$value; ";   
        }  
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_str);  
    }  
    $response = curl_exec($ch);  
		return $response;

    if (curl_errno($ch) != 0) {  
        return false;  
    }  
    curl_close($ch);  
    return $response;     
}  



//有货记录数据库
function soldoutlog($id,$dt,$status){
	global $wpdb;
	if($status == "havegoods"){
		$log = array("dateandtime"=>$dt,"checkdt"=>date("Y-m-d H:i:s"),"status"=>$status);
		
	}
	elseif($status == "soldout"){
		$log = array("checkdt"=>date("Y-m-d H:i:s"),"status"=>$status);
	}
	$wpdb->update(RKSTABLE,$log,array("id"=>$id));
}





date_default_timezone_set('PRC');
$dt = date("Y-m-d H:i:s");

$id=!empty($_GET["id"])?$_GET["id"]:0;

$r = $wpdb->get_row("select * from ".RKSTABLE." where id=".$id);
if(null !== $r)
{
	$url=$r->url;
	$matchcontent=$r->content;
	$yesorno = $r->issoldout;
	$matchtype = stripslashes($r->matchtype);
}

if(empty($id)){
	
	echo "id is empty";
	exit;
}


//读取源代码
//$page = file_get_contents($url);
$page = url_get_contents($url);
if ($matchtype=="regex"){
	$ismatch = preg_match($matchcontent,$page);
}
else
{
	$ismatch = strstr($page,$matchcontent);
}


if($ismatch){
	if($yesorno){
		soldoutlog($id,$dt,"havegoods");
		echo "havegoods";
		echo "||";
		echo $dt;
	}
	else
	{
		soldoutlog($id,$dt,"soldout");
		echo "soldout";
		echo "||";
		echo $dt;
	}

}
else
{
	if($yesorno)
	{
		soldoutlog($id,$dt,"soldout");
		echo "soldout";
		echo "||";
		echo $dt;
	}
	else
	{
		soldoutlog($id,$dt,"havegoods");
		echo "havegoods";
		echo "||";
		echo $dt;
	}
	
}


?>