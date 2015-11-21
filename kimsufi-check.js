

function interval(cid,endTime)
{
	var n=(endTime-new Date().getTime())/1000;
	if(n<0) return;
	//document.getElementById(\"timeout\").innerHTML = n.toFixed(3);
	jQuery("#timeout"+cid).html(n.toFixed(3))
	setTimeout("interval("+cid+","+endTime+")", 10);
}

//异步分析的结果
function soldoutresult(cid,curl,cmatchcontent,cyesorno,cmatchtype,response){
	responsearr = response.split("||");
	if(responsearr[0] == "havegoods"){
		jQuery("#id"+cid).html("<font color='red' style='font-size:16px' ><b>有货</b></font>");
		jQuery("#dt"+cid).html(responsearr[1]);
		jQuery("#pretest"+cid).html(responsearr[2]);
		jQuery("#lj"+cid).css('display','block');
		if(jQuery("#tx"+cid).is(':checked')){
			jQuery("#mp3")[0].play();
			//alert("dfsfsfsdf"+cid);
		}

	}
	else if(responsearr[0] == "soldout"){
		jQuery("#id"+cid).html("<font color='blue' style='font-size:12px'>缺货</font>");
		jQuery("#pretest"+cid).html(responsearr[2]);
	}
	else{
	
		jQuery("#id"+cid).html(response);
	}

}

//开始检测时重置状态
function checking(id){jQuery("#id"+id).html("检测中。。");jQuery("#timeout"+id).html(duration);jQuery("#lj"+id).css('display','none');}

//开始通过ajax检测
function checksoldout(cid,curl,cmatchcontent,cyesorno,cmatchtype){
		checking(cid);
		var endTime = new Date().getTime() + duration + 100;
		setTimeout("checksoldout('"+cid+"','"+curl+"','"+cmatchcontent+"','"+cyesorno+"','"+cmatchtype+"')",duration);
		interval(cid,endTime);
		jQuery(document).ready(function($) {
		var data = {
			'action':'check_kimsufi',
			'id': cid,
			'url' : curl,
			'matchcontent' : cmatchcontent,
			'yesorno' : cyesorno,
			'matchtype' : cmatchtype
		};
		jQuery.post(ajaxurl,data, function(response,status) {
			soldoutresult(cid,curl,cmatchcontent,cyesorno,cmatchtype,response);
		});

	});
}

