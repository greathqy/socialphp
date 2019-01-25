<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<title>明星之梦数据后台</title>
<link type="text/css" rel="stylesheet" href="/assets/css/style.css">
<script type="text/javascript" src="/assets/js/jquery.js"></script>
</head><body>
<div id="wrapper">
    <h1>明星之梦数据后台</h1>
    <ul id="nav">
		<?php if (isset($menu) && $menu == 'schema'): ?>
        <li><a href="/index.php?m=index&a=index" class="cur">Schema数据</a></li>
		<?php else: ?>
        <li><a href="/index.php?m=index&a=index">Schema数据</a></li>
		<?php endif; ?>
		<?php if (isset($menu) && $menu == 'api'): ?>
        <li><a href="/index.php?m=index&a=api" class="cur">API调用</a></li>
		<?php else: ?>
        <li><a href="/index.php?m=index&a=api">API调用</a></li>
		<?php endif; ?>
    </ul>
    <div id="description">
		<?php if (isset($function_description)): ?>
		<?php echo $function_description;?>
		<?php endif; ?>
	</div>
    <div id="main">
    	<div id="panel">
        	<form action="" method="post" id="callfrm">
				<p><b><?php echo isset($level1) ? $level1 : '类';?></b><br>
					<select name="class" id="vclass">
						<option selected="selected" value="0">请选择</option>
						<option value="user">user（用户缓存层操作类）</option>
					</select>
                </p>
				<p><b><?php echo isset($level2) ? $level2 : '方法';?></b><br>
                	<select name="method" id="method">
                    	<option selected="selected" value="0">请选择</option>
                    </select>
                </p>
				<p><b><?php echo isset($level3) ? $level3 : '参数列表';?></b><br>
                	<span id="parameter">
                    	<input name="paras" disabled="disabled" type="text">
                    </span>
                </p>
                <p class="submit">
                	<input id="debugbtn" value="显示结果" type="button">
                	<input id="callbtn" value="格式化显示" type="button">
                </p>
            </form>
        </div>
        <div id="content">
       	  <div id="php">请选择功能操作</div>
            <div id="results">	
				操作结果显示如下.
            </div>
        </div>    
    </div>

    <div id="foot">        
        <span>Star Dreams</span>
    </div>
</div>
<script type="text/javascript">
<?php if (isset($javascript_vars)): ?>
var data = <?php echo $javascript_vars;?>;
<?php else: ?>
var data = {};
<?php endif; ?>
</script>
<script type="text/javascript">
$(window).keydown(function(event){
  if(event.keyCode == 13) {
	$("#callbtn").click();
  }
 if(event.ctrlKey&&event.keyCode=='13'){
	 $("#debugbtn").click();
 }
});

function createClass(){
	var str='<option value="0">请选择</option>';
	for(var i in data){
		str+='<option value="'+i+'">'+i+"（"+data[i]["intro"]+"）"+'</option>';
	}
	$("#vclass").html(str).change(function(){
		if($(this).val()==0){
			$("#method").html('<option value="0">请选择</option>');
			$("#parameter").html('<input type="text" name="paras" disabled="disabled" />');
			//setPath();
		}else{
			createMethod();
			//setPath($(this).val());
		}
	});
}	
function createMethod(){
	var vclass =$("#vclass").val();
	var str='<option value="0">请选择</option>';		
	for(var i in data[vclass].actions){
		str+='<option value="'+i+'">'+i+"（"+data[vclass].actions[i]["intro"]+"）"+'</option>';
	}
	$("#method").html(str).change(function(){
		if($(this).val()==0){
			$("#parameter").html('<input type="text" name="paras" disabled="disabled" />');
			setPath(vclass);
		}else{
			createParas();
			setPath(vclass,$(this).val());
		}
	});
	$("#parameter").html('<input type="text" name="paras" disabled="disabled" />');
}	
function createParas(){
	var vclass=$("#vclass").val();
	var method=$("#method").val();
	var str='';
	for(var i in data[vclass].actions[method].paras){
		if(typeof data[vclass].actions[method].paras[i] == "object")
		{
			for(var j in data[vclass].actions[method].paras[i])
			{
				str += '<span>' + data[vclass].actions[method].paras[i][j] + '：</span><br /><input type="text" name="paras[]" key="paras[' + i + '][' + j + ']" value="" /><br />';
			}
		}
		else
		{
			str+='<span>'+data[vclass].actions[method].paras[i]+'：</span><br /><input type="text" name="paras[]" key="paras['+i+']" value="" /><br />';
		}
	}

	if(!str) str='<input type="text" name="paras[]" key="paras[]" disabled="disabled" value="参数为空" />';
	
	$("#parameter").html(str);
	$("#parameter input").focus(function(){
		if($(this).val()==this.defaultValue){
			$(this).val("");
		}
	}).blur(function(){
		if($(this).val()==""){
			$(this).val(this.defaultValue);	
		}		
	});	
}
function setPath(vclass, method){
	if(typeof data[vclass] == "undefined" || typeof data[vclass].actions[method] == "undefined" || typeof data[vclass].actions[method].retintro == "undefined")
		$("#php").html("");
	else
		$("#php").html("<pre>" + data[vclass].actions[method].retintro + "</pre>");
}
var resultStr="";
function format(data){
	var indent="　　";
	var str=arguments.length==1?"":arguments[1];
	if(typeof data == "object"){
		resultStr+='<font color="#0000FF">Array</font> <font color="#800000">(</font><br />';
		for(var i in data){
			if(typeof data[i] == "object"){
				var tmp=indent+str+'"<font color="#FF00FF">'+i+'</font>"'+' <font color="#800000">=></font> ';
			}else{
				var tmp=indent+str+'"<font color="#FF00FF">'+i+'</font>"'+' <font color="#800000">=></font> "<font color="#FF00FF">'+data[i]+'</font>",<br />';
			}
			resultStr+=tmp;
			if(typeof data[i] == "object"){
				format(data[i],indent+str);
			}
		}
		resultStr+=str+'<font color="#800000">)</font><br />';
	} else {
		resultStr+='<font color="#0000FF">'+data+'</font>';
	}
}


$(function(){
	createClass();

	$("#callbtn").click(function(){
		var sp = '';
		var paras = '';
		$("input[name='paras[]']").each(function(i, n){
			if(paras == '')
				paras += $(n).attr('key')+'='+$(n).val();
			else
				paras += '&'+$(n).attr('key')+'='+$(n).val();
		});
		sp = 'class='+$("#vclass").val()+'&method='+$("#method").val() + "&" + paras;
		$.ajax({
			type:	"post",
			url:	"?c=index&a=response&function=<?php echo isset($action) ? $action : 'schema';?>" + "&" + $("#snsinfo").val(),
			data:	sp,
			dataType: 'json',
			success: function(data){
				if(typeof(data.errno) != 'undefined')
				{
					format(data.result);
					$("#results").html(resultStr);
					resultStr="";
				}
				else
				{
					$("#results").html('内部错误');
				}
			},
			complete: function(XMLHttpRequest, textStatus){},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert('失败，请稍后再试一次!');
			}
		});
	});
	$("#debugbtn").click(function(){
		var sp = '';
		var paras = '';
		$("input[name='paras[]']").each(function(i, n){
			if(paras == '')
				paras += $(n).attr('key')+'='+$(n).val();
			else
				paras += '&'+$(n).attr('key')+'='+$(n).val();
		});
		sp = 'class='+$("#vclass").val()+'&method='+$("#method").val() + "&" + paras;
		$.ajax({
			type:	"post",
			url:	"?c=index&a=response&function=<?php echo isset($action) ? $action : 'schema';?>" + "&" + $("#snsinfo").val(),
			data:	sp,
			success: function(data){
				$("#results").html('<pre>'+data+'</pre>');
			},
			complete: function(XMLHttpRequest, textStatus){},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert('失败，请稍后再试一次!');
			}
		});
	});
});

</script>
<div style="position: absolute; display: none; z-index: 9999;" id="livemargins_control"><img src="/assets/images/monitor-background-horizontal.png" style="position: absolute; left: -77px; top: -5px;" width="77" height="5">	<img src="/assets/images/monitor-background-vertical.png" style="position: absolute; left: 0pt; top: -5px;">	<img id="monitor-play-button" src="/assets/images/monitor-play-button.png" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5" style="position: absolute; left: 1px; top: 0pt; opacity: 0.5; cursor: pointer;"></div></body></html>
