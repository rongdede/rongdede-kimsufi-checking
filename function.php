<?php
global $wpdb;
define('RKSTABLE', $wpdb->prefix.'rongdede_kimsufi_checking');

class RKSTT{
	function __construct() {
	
	}

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

	
	//判断源代码是否匹配
	function matchstock($url){
		global $wpdb;
		$r = $wpdb->get_row("select * from ".RKSTABLE." where url='".$url."'");
		if(null !== $r){
			$matchcontent=stripslashes($r->content);
			$yesorno=$r->issoldout;
			$matchtype=$r->matchtype;
			$rongdedestockoptionarr = array();
			$rongdedestockoptionarr = get_option("rongdedestockoption")?get_option("rongdedestockoption"):array("checktype"=>"ajax");
			$rongdedestockoption = $rongdedestockoptionarr["checktype"];
			//读取源代码
			//$page = file_get_contents($url);
			$page = $this->url_get_contents($url);
			if(!$page){
				return "can't get the page";
			}
			$page = str_replace("\n","",$page);
			$page = str_replace("\r","",$page);
			$matchcontent = str_replace("\n","",$matchcontent);
			$matchcontent = str_replace("\r","",$matchcontent);
			if ($matchtype=="regex"){
				$ismatch = preg_match($matchcontent,$page);
			}
			else
			{
				$ismatch = strpos($page,$matchcontent);
			}
			if($ismatch){
				if($yesorno){
					return "havegoods";

				}
				else
				{
					return "soldout";
				}
			
			}
			else
			{
				if($yesorno)
				{
					return "soldout";
				}
				else
				{
					return "havegoods";
				}
				
			}
			
		}
		else{
			return "url not exits";
		}
	}


	function echoresult($status,$instocktime,$checkdt){
		echo $status;
		echo "||";
		echo $instocktime;
		echo "||";
		echo $checkdt;
		echo "||";
	}
}

?>