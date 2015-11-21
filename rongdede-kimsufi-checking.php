<?php 
/*
Plugin Name: kimsufi Checking
Plugin URI: 
Description: auto check Kimsufi's server weather soldout
Version: 0.0.1
Author: Rongdede
Author URI: 
Text Domain: 91yun.org
*/
require_once("function.php");


class rongdedekimsuficheck{
	
	//析构函数
	function __construct() {
		
		//插件激活时候的处理
		register_activation_hook( __FILE__, Array($this,'myplugin_activate'));
		

		//检查是否有货的ajax
		add_action( 'wp_ajax_check_kimsufi', array($this,'checkkimsufi') );
		add_action( 'wp_ajax_nopriv_check_kimsufi', array($this,'checkkimsufi') );

		
		//判断是否该页面
		//add_filter( "template_include", array($this,'check_page')); 
		add_filter('the_content', array($this,'check_page'));


	}


function check_page($text){
	
	if(is_page("kimsufi-checking")){
			$rongdedekimsufioptionarr = array();
			$rongdedekimsufioptionarr = get_option("rongdedekimsufioption")?get_option("rongdedekimsufioption"):array("checktype"=>"ajax");
			$rongdedekimsufioption = $rongdedekimsufioptionarr["checktype"];
			$text = "";
			//$text = $text."<script type='text/javascript' src='".includes_url("js/jquery/jquery.js?ver=1.11.3")."'></script>";
			if($rongdedekimsufioption == "ajax"){
				$text = $text."<script>var duration=90000;</script>";
			}
			else{
				$text = $text."<script>var duration=10000;</script>";
			}
			$text = $text."<script type='text/javascript' src='".plugins_url( "kimsufi-check.js?ver=201511190415", __FILE__ )."'></script>";
			$text = $text."<link rel='stylesheet' href='".plugins_url( "kimsufi-check.css", __FILE__ )."' type='text/css'>";
			$text = $text."<script>var ajaxurl='".admin_url('admin-ajax.php')."';</script>";
			$text = $text."<audio id='mp3' controls='controls'>";
			$text = $text."<source src='".plugins_url( 'Anaconda.mp3', __FILE__ )."' type='audio/mpeg'>";
			$text = $text."<embed src='".plugins_url( 'Anaconda.mp3', __FILE__ )."' autostart='false' loop='ture'>";
			$text = $text."</audio>";
			$text = $text."<A href='#' target='_self' onclick='jQuery(\"#mp3\")[0].play()'>播放</A> │ <A href='#' onclick='jQuery(\"#mp3\")[0].pause()' target='_self'>停止</A>";
			$text = $text."		<table style='width:100%;'>";
			$text = $text."	<TR>";
			$text = $text."		<td style='width:50px'>名称</td>";
			$text = $text."		<td style='width:100px'>状态</td>";
			$text = $text."		<td style='width:100px'>上次检查时间</td>";
			$text = $text."		<td style='width:100px'>是否提醒</td>";
			$text = $text."		<td style='width:150px'>上次有货时间</td>";
			$text = $text."		<td>购买链接</td>";
			$text = $text."	</tr>";
			global $wpdb;
			$rows = $wpdb->get_results('select * from '.RKSTABLE);
			foreach($rows as $r){
				$text = $text."<tr>";
				$text = $text."<td><a href='$r->url' target='_blank'>$r->name</a></td>\r\n";
				$text = $text."<td><span id='id$r->id'>准备开始</span></td>\r\n";
				$text = $text."<td><div style='display:none'><span id='timeout$r->id'></span></div><span id='pretest$r->id'>$r->checkdt</span></td>\r\n";
				$text = $text."<td><input type='checkbox' id='tx$r->id'>音乐提醒</td>\r\n";
				$text = $text."<td><span id='dt$r->id'>$r->dateandtime</span><script>checksoldout('$r->id','$r->url','".stripslashes($r->content)."','$r->issoldout','$r->matchtype')</script></td>\r\n";
				$text = $text."<td><a href='$r->url' id='lj$r->id' style='display:none' target='_blank'>$r->url</a></td>\r\n";
				$text = $text."</tr>\r\n";			
			
			}
			$text = $text ."</table>";
			return $text;


	
	}
	else
	{
		return $text;
	}


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


//检查是否有货的ajax
	function checkkimsufi(){
		global $wpdb;
		date_default_timezone_set('PRC');
		$dt = date("Y-m-d H:i:s");


		$id=!empty($_POST["id"])?$_POST["id"]:0;
		$url=!empty($_POST["url"])?$_POST["url"]:"";
		$matchcontent=!empty($_POST["matchcontent"])?$_POST["matchcontent"]:"";
		$yesorno=!empty($_POST["yesorno"])?$_POST["yesorno"]:1;
		$matchtype=!empty($_POST["matchtype"])?$_POST["matchtype"]:"str";
		
		$matchcontent = stripslashes($matchcontent);
		
		if(empty($id) || empty($url) || !isset($matchcontent)){
			
			echo "id,url,or issoldout is empty";
			exit;
		}
		
		$rongdedekimsufioptionarr = array();
		$rongdedekimsufioptionarr = get_option("rongdedekimsufioption")?get_option("rongdedekimsufioption"):array("checktype"=>"ajax");
		$rongdedekimsufioption = $rongdedekimsufioptionarr["checktype"];
		if($rongdedekimsufioption == "ajax"){
			//读取源代码
			//$page = file_get_contents($url);
			$page = $this->url_get_contents($url);
			if ($matchtype=="regex"){
				$ismatch = preg_match($matchcontent,$page);
			}
			else
			{
				$ismatch = strstr($page,$matchcontent);
			}
		//echo "ddd";
			if($ismatch){
				if($yesorno){
					$this->soldoutlog($id,$dt,"havegoods");
					echo "havegoods";
					echo "||";
					echo $dt;
					echo "||";
					echo date("Y-m-d H:i:s");
					echo "||";
				}
				else
				{
					$this->soldoutlog($id,$dt,"soldout");
					echo "soldout";
					echo "||";
					echo "";
					echo "||";
					echo date("Y-m-d H:i:s");
					echo "||";
				}
			
			}
			else
			{
				if($yesorno)
				{
					$this->soldoutlog($id,$dt,"soldout");
					echo "soldout";
					echo "||";
					echo "";
					echo "||";
					echo date("Y-m-d H:i:s");
					echo "||";
				}
				else
				{
					$this->soldoutlog($id,$dt,"havegoods");
					echo "havegoods";
					echo "||";
					echo $dt;
					echo "||";
					echo date("Y-m-d H:i:s");
					echo "||";
				}
				
			}
		}
		elseif($rongdedekimsufioption == "91yun"){
			$page = $this->url_get_contents("http://www.91yun.org/wp-content/plugins/rongdede-kimsufi-checking/91yun-kimsufi-checking.php?url=".urlencode($url));
			echo $page;
			
		}
		elseif($rongdedekimsufioption == "back"){
			$r = $wpdb->get_row("select * from ".RKSTABLE." where id='".$id."'");
			echo $r->status;
			echo "||";
			if(null == $r->dateandtime){
				echo " ";
			}
			else{
				echo $r->dateandtime;
			}
			echo "||";
			echo $r->checkdt;
			echo "||";
		}



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


		//插件激活时执行的内容
	function myplugin_activate(){
		$rongdedekimsufioption = array();
		$$rongdedekimsufioption["checktype"]="91yun";
		update_option("rongdedekimsufioption",$rongdedekimsufioption);

		//创建数据库
		global $wpdb;
		$activesql = "select COUNT(*) from information_schema.tables WHERE table_name = '".RKSTABLE."'";
		$createsql = "create table ".RKSTABLE." ( 
		id int NOT NULL AUTO_INCREMENT primary key,
		author int NOT NULL,
		name varchar(255) NOT NULL,
		url varchar(255) NOT NULL,
		content text,
		issoldout bool NOT NULL,
		tag text,
		matchtype varchar(20),
		dateandtime varchar(255),
		checkdt varchar(255),
		status varchar(255)
	)";
		$current_user = wp_get_current_user();
		//如果数据表不存在就创建数据表
		if(!$wpdb->get_var($activesql)){
			$wpdb->query($createsql);
			//插入初始的ks数据
			$ksjson =  file_get_contents(dirname(__FILE__)."/ks.json");
			$ksarr = json_decode($ksjson,true);
			foreach($ksarr["france"] as $fvalue){
					$options = Array(
						"name" => $fvalue["name"],
						"author"=>$current_user->ID,
						"url" => $fvalue["url"],
						"content" => stripslashes($fvalue["content"]),
						"issoldout" => $fvalue["issoldout"],
						"matchtype" => $fvalue["matchtype"],
						"tag" => $fvalue["tag"]			
					);
					$wpdb->insert(RKSTABLE,$options);
				}
		}

		//如果page不存在，就创建page
		$soldout = get_page_by_title("kimsufi checking");
		if (Null == $soldout)
		{
			$page = array(
			 'post_title' => 'kimsufi checking',
			 'post_content' => 'kimsufi checking',
			 'post_name' => 'kimsufi-checking',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_type' => 'page'
			);
			 wp_insert_post($page);


		}


	}

}

$myissoldeout = new rongdedekimsuficheck();
require_once("rongdede-options.php");

?>