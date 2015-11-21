<?php

class rongdedekimsuficheckOptions{


	function __construct() {
	
		//添加菜单
		add_action('admin_menu', array($this,'rongdedekimsuficheck_menu'));
			//add_action('admin_menu', array($this,'rongdedekimsuficheck_menu'));
//			$this->rongdedetranslateengine = get_option("rongdedetranslateengine")?get_option("rongdedetranslateengine"):"baidu";
//			$this->rongdedeclient_id = get_option("rongdedeclient_id")?get_option("rongdedeclient_id"):"";
//			$this->rongdededefaultlang = get_option("rongdededefaultlang")?get_option("rongdededefaultlang"):"zh";
//			$this->rongdedelangs = get_option("rongdedelangs")?get_option("rongdedelangs"):array("zh"=>"简体中文","en"=>"English");


	}


	//添加后台菜单
	//添加后台菜单
	function rongdedekimsuficheck_menu(){
	
		add_menu_page( 'kimsufi-checking', 'kimsufi checking', 'manage_options', 'rongdedekimsuficheck', '', '');
		add_submenu_page( 'rongdedekimsuficheck', '选项', '设置', 'manage_options', 'rongdedekimsuficheck', array($this,'rongdede_options') );

	}

	function rongdede_options(){
		global $wpdb;
		$current_user = wp_get_current_user();
		$type = isset($_POST["type"])?$_POST["type"]:"";

		if (!empty($type)){
			$option = array();
			$option = get_option("rongdedekimsufioption");
			$option["checktype"]=$type;
			update_option("rongdedekimsufioption",$option);
			echo '<script>location.href="admin.php?page=rongdedekimsuficheck"</script>';

		}
		
		else
		{
			$option = array();
			$option = get_option("rongdedekimsufioption");

		?>

<div>
<div id="theme-options-wrap">    <div class="icon32" id="icon-tools"> <br /> </div>    <h1>Rongdede Kimsufi Checking</h1>    <p>选择检测方式.</p>   <BR/> <BR/><BR/> </div>
<form method="post" action="admin.php?page=rongdedekimsuficheck">
<p>
<input type="hidden" name="action" value="options">

	<table width="100%">
	<tr>
		<td><input type="radio" name="type" value="ajax"<?php if($option["checktype"] == "ajax"){echo " checked";} ?>>传统模式</br>
		<input type="radio" name="type" value="91yun"<?php if($option["checktype"] == "91yun"){echo " checked";} ?>>调用91yun.org官方数据（推荐）</br>
		<input type="radio" name="type" value="back"<?php if($option["checktype"] == "back"){echo " checked";} ?>>后台检测模式</td>
	</tr>
	<tr style="height:100px">
	<td><input type="submit"></td>
	</tr>
	<tr>
		<td>
		<p>传统模式：前端页面通过ajax调用php代码实时监测kimsufi页面判断是否有货。</p>
		<p>弊端：由于拉取页面源代码进行判断，这个过程比较久，通常要十几秒，因此php程序会持续占用cpu资源。支撑不了几个用户同时刷，人稍微一多，你服务器就会被卡死。这个模式只适合你自己刷着用。</p>
		<hr /> </td>
	</tr>
	<tr>
		<td>
		<p>直接调用91yun.org的官方数据：什么都不用动，直接读取91yun的数据库来拉取是否有货的数据。服务器数据2分钟更新一次。前端20秒刷一次。</p>
		<p>弊端：其实没啥弊端，但对于数据放在别人那不放心的完美主义者会比较纠结吧。</p>
		<hr /> </td>
	</tr>
	<tr>
		<td>
		<p>后台检测模式：这个需要在服务器端用crontab或者计划任务来运行wp-content/plugins/rongdede-kimsufi-checking/rongdede-kimsufi-checking-back.php每隔一段时间自己检测下，然后写入数据库。前端只需要读取数据库状态就可以了。比如</p>
		<p>*/2 * * * * /usr/local/php/php /home/www/wordpress/wp-content/plugins/rongdede-kimsufi-checking/rongdede-kimsufi-checking-back.php</p>
		<p>弊端：需要有服务器控制权，并且懂点服务器相关的设置,抓取页面还是蛮费CPU资源的，小鸡请不要轻易尝试</p>
		<hr /> </td>
	</tr>

	
	</table>
</form>


</div>
		<?php
		
		}
	
	}

}

$myrongdedeoptions = new rongdedekimsuficheckOptions;

?>