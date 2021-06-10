<?php
/**
 * 服务器状态
 *
 * @version:2.1.0
 * @author Weifeng
 * https://github.com/acewfdy/Typecho
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

<?php $this->need('component/header.php'); ?>

	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->

<!-- <div id="content" class="app-content"> -->
   <a class="off-screen-toggle hide"></a>
   <main class="app-content-body <?php echo Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
    <!--文章-->
     <div class="col center-part">
    <!--标题下的一排功能信息图标：作者/时间/浏览次数/评论数/分类-->
         <header class="bg-light lter b-b wrapper-md">
             <h1 class="m-n font-thin h3"><i data-feather="server" class="i-sm m-r-sm"></i><?php if(empty($_GET['id'])){ ?>服务器列表<?php }else{ ?>服务器[<?php echo ServerStatus_Plugin::info($_GET['id'])['name']; ?>]的状态<?php } ?></h1>
             <small class="text-muted letterspacing indexWords"><?php echo $this->fields->intro; ?></small>
         </header>
      <div class="wrapper-md" id="post-panel">
	   <!--开始-->
       <ol class="breadcrumb bg-white-pure" itemscope=""><li>
           <a href="<?php echo $this->options->rootUrl ?>" itemprop="breadcrumb" title="" data-toggle="tooltip" data-original-title="返回首页"><span class="home-icons"><svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></span>首页</a>
             </li><li <?php if(!isset($_GET['id'])) { ?>class="avtive"<?php } ?>><a href="<?php echo $this->permalink(); ?>">服务器状态</a></li><?php if(isset($_GET['id'])) { ?><li class="active">服务器 [<?php echo ServerStatus_Plugin::info($_GET['id'])['name']; ?>] 的状态&nbsp;&nbsp;</li><?php } ?>
	   </ol>
       <!--结束-->
       <!--博客文章样式 begin with .blog-post-->
       <div id="postpage" class="blog-post">
        <article class="panel">
        <!--文章页面的头图-->
            <?php echo Content::exportHeaderImg($this); ?>
         <!--文章内容-->
         <div id="post-content" class="wrapper-lg">
            <div class="entry-content l-h-2x">
              <?php echo Content::postContent($this,$this->user->hasLogin()); ?>
            </div>
<?php if(empty($_GET['id']) && ServerStatus_Plugin::GetCount() > 1){ ?>
                <div class="row">
				    <?php
					ServerStatus_Plugin::output('<div class="table-responsive box-shadow-wrap-lg" style="text-align:center;">
  <table style="border-radius:10px!important;overflow:hidden!important;border-collapse:separate;">
    <thead></thead>
    <tbody>
      <tr><td style="width:70px;"><b>名称</b></td><td style="width:auto;">{name}</td></tr>
      <tr><td style="width:70px;"><b>ID</b></td><td style="width:auto;">{id}</td></tr>
      <tr><td style="width:70px;"><b>排序</b></td><td style="width:auto;">{order}</td></tr>
      <tr><td style="width:70px;"><b>类型</b></td><td style="width:auto;">{type_cn}</td></tr>
      <tr><td style="width:70px;"><b>介绍</b></td><td style="width:auto;">{desc}</td></tr>
    </tbody>
  </table>
  <div style="text-align:right;">
    <a class="btn btn-success" href="?id={id}" title="点击打开服务器[{name}]" target="_self" style="margin: 0 15px 0 0;">查看状态</a>
  </div>
  <br>
</div>');
					?>
				</div>
<?php
}else{
	if(empty($_GET['id'])) $_GET['id'] = ServerStatus_Plugin::info()['id'];
?>
              <div class="">
	            <div class="">
	              <span class="pull-right text-danger" id="cpus"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>CPU占用 </span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="cpu_csss" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            <div class="">
	              <span class="pull-right text-danger" id="memorys"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>占用内存 <span class="badge badge-sm bg-dark" id="memory_datas"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span</span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memory_csss" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            <div class="">
	              <span class="pull-right text-danger" id="disks"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>磁盘占用 <span class="badge badge-sm bg-dark" id="disk_datas"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="disk_csss" class="progress-bar bg-danger" data-toggle="tooltip"  style="width: 100%"></div>
	            </div>
	            <div class="">
	              <span class="pull-right text-danger" id="memCacheds"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>内存缓存 <span class="badge badge-sm bg-dark" id="memCached_datas"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memCached_csss" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            <div class="">
	              <span class="pull-right text-danger" id="memBufferss"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>内存缓冲 <span class="badge badge-sm bg-dark" id="memBuffers_datas"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memBuffers_csss" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            <div class="">
	              <span class="pull-right text-danger" id="state_ss"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>系统负载 <span id="states"><span class="badge badge-sm bg-dark"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="state_csss" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            <div class="" id="ios_div">
	              <span class="pull-right text-default" id="ios"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>IO</span>
	            </div>
				<br id="ios_br"/>
	            <div class="" id="io1s_div">
	              <span class="pull-right text-default" id="io1s"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>实时IO</span>
	            </div>
				<br id="io1s_br"/>
	            <div class="" id="eths_div">
	              <span class="pull-right text-default" id="eths"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>网络</span>
	            </div>
				<br id="eths_br"/>
	            <div class="" id="eth1s_div">
	              <span class="pull-right text-default" id="eth1s"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>实时网络</span>
	            </div>
				<br id="eth1s_br"/>
	            <div class="">
	              <span class="pull-right text-default" id="times"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>服务器系统时间</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="u_times"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>持续运行时长</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="ip"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的IP</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="address"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的网络地址</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="b"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的浏览器</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="sys"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的设备</span>
	            </div>
				<br />
	            <div class="">
	              <span class="pull-right text-default" id="sys_time"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的设备时间</span>
	            </div>
	          </div>
    <script type="text/javascript">
	    var Loading = null;
        $(function(){
	    	Swal.fire({allowOutsideClick:false,imageUrl:'https://ae01.alicdn.com/kf/U05ed7e65d7a749a29bd6164f9d4abe84w.gif',title:"与服务器通讯中...",showConfirmButton:true,timer:0});
			UserInfo();
	    	$.ajax({
                type : "get",
                url:"/ServerStatus/Status?id=<?php echo $_GET['id'];?>",
                async : true,
                dataType:"json",
                error : function(){
    				swal.close();
                    Swal.fire({allowOutsideClick:false,icon:'error',type:'error',title:"通讯失败，请重试！",showConfirmButton:true,timer:0});
                },
                success:function(data){
    				if(data != null ){
    					swal.close();
						StatusLoader();
    					Loading = setInterval(StatusLoader,<?php echo ServerStatus_Plugin::info($_GET['id'])['ajax']; ?>*1000);
    				}else{
    					swal.close();
    					Swal.fire({allowOutsideClick:false,icon:'error',type:'error',title:"通讯失败，请重试！",showConfirmButton:true,timer:0});
    				}
                }
            });
        });
		function getNowFormatDate(){
			var date = new Date();
			var seperator1 = "-";
			var seperator2 = ":";
			var month = date.getMonth() + 1;
			var strDate = date.getDate();
			if (month >= 1 && month <= 9) {
			  month = "0" + month;
			}
			if (strDate >= 0 && strDate <= 9) {
			  strDate = "0" + strDate;
			}
			var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
			      + " " + date.getHours() + seperator2 + date.getMinutes()
			      + seperator2 + date.getSeconds();
			return currentdate;
		}
		var getnet = setInterval(function(){
			$("#sys_time").html('<span class="badge badge-sm bg-dark">'+getNowFormatDate()+'</span>');
		},1000);
		function UserInfo(){
			$.ajax({
			    type : "get",
			    url : "/ServerStatus/IPInfo",
			    async : true,
			    error : function(){
			    	$("#ip").html('<span class="badge badge-sm bg-dark">获取失败</span>');
					$("#address").html('<span class="badge badge-sm bg-dark">获取失败</span>');
					$("#b").html('<span class="badge badge-sm bg-dark">获取失败</span>');
					$("#sys").html('<span class="badge badge-sm bg-dark">获取失败</span>');
			    },
			    success : function(data){
			    	if(data!=null){
			    		if(data.data['ip']==null && returnCitySN==null){
			    			$("#ip").html('<span class="badge badge-sm bg-dark">获取失败</span>');
			    		}else if(data.data['country']==null && data.data['region']==null && data.data['city']==null && data.data['isp']==null && returnCitySN==null){
			    			$("#address").html('<span class="badge badge-sm bg-dark">获取失败</span>');
			    		}else if(data.data['browse']==null){
			    			$("#b").html('<span class="badge badge-sm bg-dark">获取失败</span>');
			    		}else if(data.data['os']==null){
			    			$("#sys").html('<span class="badge badge-sm bg-dark">获取失败</span>');
			    		}else{
							if(data.data['ip']==null){
				    		    $("#ip").html('<span class="badge badge-sm bg-dark">'+returnCitySN.cip+'</span>');
							}else{
								$("#ip").html('<span class="badge badge-sm bg-dark">'+data.data['ip']+'</span>');
							}
							if(data.data['country']==null){
								if(returnCitySN.cname.indexOf('省') || returnCitySN.cname.indexOf('市')){
				    		        $("#address").html('<span class="badge badge-sm bg-dark">中国 '+returnCitySN.cname+'</span>');
								}else{
				    		        $("#address").html('<span class="badge badge-sm bg-dark">'+returnCitySN.cname+'</span>');
								}
							}else{
								$("#address").html('<span class="badge badge-sm bg-dark">'+data.data['country']+' '+data.data['region']+' '+data.data['city']+' '+data.data['isp']+'</span>');
							}
					    	$("#b").html('<span class="badge badge-sm bg-dark">'+data.data['browse']+'</span>');
					    	$("#sys").html('<span class="badge badge-sm bg-dark">'+data.data['os']+'</span>');
			    		}
			    	}
			    },
		    });
		};
        function returnFloat(value) {
            return value.toFixed(2) + '%';
        }
        function floats(value) {
            return value.toFixed(2);
        }
        function getPercent(curNum, totalNum, isHasPercentStr) {
            curNum = parseFloat(curNum);
            totalNum = parseFloat(totalNum);
            if (isNaN(curNum) || isNaN(totalNum)) {
                return 'Error';
            }
            return isHasPercentStr ? totalNum <= 0 ? '0%': (Math.round(curNum / totalNum * 10000) / 100.00 + '%') : totalNum <= 0 ? 0 : (Math.round(curNum / totalNum * 10000) / 100.00);
        }
        function getPercents(curNum, totalNum, isHasPercentStr) {
            curNum = parseFloat(curNum);
            totalNum = parseFloat(totalNum);
        
            if (isNaN(curNum) || isNaN(totalNum)) {
                return 'Error';
            }
        
            return isHasPercentStr ? totalNum <= 0 ? '0%': (Math.round(curNum / totalNum * 10000) / 100.00) : totalNum <= 0 ? 0 : (Math.round(curNum / totalNum * 10000) / 100.00);
        }
        function ForDight(Dight, How) {
            if (Dight < 0) {
                var Last = 0 + "B/s";
            } else if (Dight < 1024) {
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "B/s";
            } else if (Dight < 1048576) {
                Dight = Dight / 1024;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "K/s";
            } else {
                Dight = Dight / 1048576;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "M/s";
            }
            return Last;
        }
        function setSize(value, d) {
            switch (d) {
            case 'bit':
                return bit = value * 8;
                break;
            case 'bytes':
                return value;
                break;
            case 'kb':
                return value / 1024;
                break;
            case 'mb':
                return value / 1024 / 1024;
                break;
            case 'gb':
                return value / 1024 / 1024 / 1024;
                break;
            case 'tb':
                return value / 1024 / 1024 / 1024 / 1024;
                break;
            }
        }
        function StatusLoader() {
            $.ajax({
                type: "get",
                url: "/ServerStatus/Status?id=<?php echo $_GET['id'];?>",
                async: true,
                error: function() {
					if(Loading != null){
                        clearInterval(Loading);
					}
                    Swal.fire({allowOutsideClick:false,icon:'error',type:'error',title:"通讯异常，请重试！",showConfirmButton:true,timer:0});
                },
                success: function(data) {
                    if (data != null) {
                        /*处理器计算*/
                        var cpu = data.serverStatus.cpuUsage['user'] + data.serverStatus.cpuUsage['nice'] + data.serverStatus.cpuUsage['sys'];
                        $("#cpu").html(returnFloat(cpu));
                        $("#cpu_css").css("width", returnFloat(cpu));
                        if (cpu < 70) {
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-success");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-success");
                        }
                        if (cpu >= 70) {
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-warning");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-warning");
                        }
                        if (cpu >= 90) {
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-danger");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-danger");
                        }
                        $("#cpus").html(returnFloat(cpu));
                        $("#cpu_csss").css("width", returnFloat(cpu));
                        if (cpu < 70) {
                            $("#cpu_csss").removeClass();
                            $("#cpu_csss").addClass("progress-bar bg-success");
                            $("#cpus").removeClass();
                            $("#cpus").addClass("pull-right text-success");
                        }
                        if (cpu >= 70) {
                            $("#cpu_csss").removeClass();
                            $("#cpu_csss").addClass("progress-bar bg-warning");
                            $("#cpus").removeClass();
                            $("#cpus").addClass("pull-right text-warning");
                        }
                        if (cpu >= 90) {
                            $("#cpu_csss").removeClass();
                            $("#cpu_csss").addClass("progress-bar bg-danger");
                            $("#cpus").removeClass();
                            $("#cpus").addClass("pull-right text-danger");
                        }
                        /*内存数据计算*/
                        var memory_value = data.serverStatus.memRealUsage['value'];
                        var memory_max = data.serverStatus.memRealUsage['max'];
                        $("#memory").html(getPercent(memory_value, memory_max, memory_value));
                        $("#memory_css").css("width", getPercent(memory_value, memory_max, memory_value));
                        var me = getPercents(memory_value, memory_max, memory_value);
                        if (me != "Error") {
                            if (me < 70) {
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-success");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-success");
                            }
                            if (me >= 70) {
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-warning");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-warning");
                            }
                            if (me >= 90) {
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-danger");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-danger");
                            }
                        }
                        if (floats(setSize(memory_value, 'mb')) > 1024) {
                            var memory_data_value = floats(setSize(memory_value, 'gb')) + "GB";
                        } else {
                            var memory_data_value = floats(setSize(memory_value, 'mb')) + "MB";
                        }
                        if (floats(setSize(memory_max, 'mb')) > 1024) {
                            var memory_data_max = floats(setSize(memory_max, 'gb')) + "GB";
                        } else {
                            var memory_data_max = floats(setSize(memory_max, 'mb')) + "MB";
                        }
                        $("#memory_data").html(memory_data_value + " / " + memory_data_max);
                        $("#memorys").html(getPercent(memory_value, memory_max, memory_value));
                        $("#memory_csss").css("width", getPercent(memory_value, memory_max, memory_value));
                        if (me < 70) {
                            $("#memory_csss").removeClass();
                            $("#memory_csss").addClass("progress-bar bg-success");
                            $("#memorys").removeClass();
                            $("#memorys").addClass("pull-right text-success");
                        }
                        if (me >= 70) {
                            $("#memory_csss").removeClass();
                            $("#memory_csss").addClass("progress-bar bg-warning");
                            $("#memorys").removeClass();
                            $("#memorys").addClass("pull-right text-warning");
                        }
                        if (me >= 90) {
                            $("#memory_csss").removeClass();
                            $("#memory_csss").addClass("progress-bar bg-danger");
                            $("#memorys").removeClass();
                            $("#memorys").addClass("pull-right text-danger");
                        }
                        $("#memory_datas").html(memory_data_value + " / " + memory_data_max);
                        /*硬盘数据计算*/
                        var disk_value = data.serverInfo.diskUsage['value'];
                        var disk_max = data.serverInfo.diskUsage['max'];
                        $("#disk").html(getPercent(disk_value, disk_max, disk_value));
                        $("#disk_css").css("width", getPercent(disk_value, disk_max, disk_value));
                        var dk = getPercents(disk_value, disk_max, disk_value);
                        if (dk != "Error") {
                            if (dk < 70) {
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-success");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-success");
                            }
                            if (dk >= 70) {
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-warning");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-warning");
                            }
                            if (dk >= 90) {
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-danger");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-danger");
                            }
                        }
                        if (floats(setSize(disk_value, 'mb')) > 1024) {
                            var disk_data_value = floats(setSize(disk_value, 'gb')) + "GB";
                        } else {
                            var disk_data_value = floats(setSize(disk_value, 'mb')) + "MB";
                        }
                        if (floats(setSize(disk_max, 'mb')) > 1024) {
                            var disk_data_max = floats(setSize(disk_max, 'gb')) + "GB";
                        } else {
                            var disk_data_max = floats(setSize(disk_max, 'mb')) + "MB";
                        }
                        $("#disk_data").html(disk_data_value + " / " + disk_data_max);
                        $("#disks").html(getPercent(disk_value, disk_max, disk_value));
                        $("#disk_csss").css("width", getPercent(disk_value, disk_max, disk_value));
                        if (dk < 70) {
                            $("#disk_csss").removeClass();
                            $("#disk_csss").addClass("progress-bar bg-success");
                            $("#disks").removeClass();
                            $("#disks").addClass("pull-right text-success");
                        }
                        if (dk >= 70) {
                            $("#disk_csss").removeClass();
                            $("#disk_csss").addClass("progress-bar bg-warning");
                            $("#disks").removeClass();
                            $("#disks").addClass("pull-right text-warning");
                        }
                        if (dk >= 90) {
                            $("#disk_csss").removeClass();
                            $("#disk_csss").addClass("progress-bar bg-danger");
                            $("#disks").removeClass();
                            $("#disks").addClass("pull-right text-danger");
                        }
                        $("#disk_datas").html(disk_data_value + " / " + disk_data_max);
                        /*服务器负载状态*/
                        var state = "";
                        for (var i = 0; i < data.serverStatus.sysLoad.length; i++) {
                            state += '<span class="badge badge-sm bg-dark">' + data.serverStatus.sysLoad[i] + '</span>&nbsp;'
                        }
                        $("#state").html(state);
                        var state_s = getPercent(data.serverStatus.sysLoad[0], 2, data.serverStatus.sysLoad[0]);
                        $("#state_css").css("width", state_s);
                        $("#state_s").html(state_s);
                        var sta = getPercents(data.serverStatus.sysLoad[0], 2, data.serverStatus.sysLoad[0]);
                        if (sta != "Error") {
                            if (sta < 70) {
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-success");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-success");
                            }
                            if (sta >= 70) {
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-warning");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-warning");
                            }
                            if (sta >= 90) {
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-danger");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-danger");
                            }
                        }
                        $("#states").html(state);
                        $("#state_csss").css("width", state_s);
                        $("#state_ss").html(state_s);
                        if (sta != "Error") {
                            if (sta < 70) {
                                $("#state_csss").removeClass();
                                $("#state_csss").addClass("progress-bar bg-success");
                                $("#state_ss").removeClass();
                                $("#state_ss").addClass("pull-right text-success");
                            }
                            if (sta >= 70) {
                                $("#state_csss").removeClass();
                                $("#state_csss").addClass("progress-bar bg-warning");
                                $("#state_ss").removeClass();
                                $("#state_ss").addClass("pull-right text-warning");
                            }
                            if (sta >= 90) {
                                $("#state_csss").removeClass();
                                $("#state_csss").addClass("progress-bar bg-danger");
                                $("#state_ss").removeClass();
                                $("#state_ss").addClass("pull-right text-danger");
                            }
                        }
                        /*内存缓存计算*/
                        var memCached_value = data.serverStatus.memCached['value'];
                        var memCached_max = data.serverStatus.memCached['max'];
                        $("#memCached").html(getPercent(memCached_value, memCached_max, memCached_value));
                        $("#memCached_css").css("width", getPercent(memCached_value, memCached_max, memCached_value));
                        var mem = getPercents(memCached_value, memCached_max, memCached_value);
                        if (mem != "Error") {
                            if (mem < 70) {
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-success");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-success");
                            }
                            if (mem >= 70) {
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-warning");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-warning");
                            }
                            if (mem >= 90) {
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-danger");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-danger");
                            }
                        }
                        if (floats(setSize(memCached_value, 'mb')) > 1024) {
                            var memCached_data_value = floats(setSize(memCached_value, 'gb')) + "GB";
                       } else {
                            var memCached_data_value = floats(setSize(memCached_value, 'mb')) + "MB";
                        }
                        if (floats(setSize(memCached_max, 'mb')) > 1024) {
                            var memCached_data_max = floats(setSize(memCached_max, 'gb')) + "GB";
                        } else {
                            var memCached_data_max = floats(setSize(memCached_max, 'mb')) + "MB";
                        }
                        $("#memCached_data").html(memCached_data_value + " / " + memCached_data_max);
                        $("#memCacheds").html(getPercent(memCached_value, memCached_max, memCached_value));
                        $("#memCached_csss").css("width", getPercent(memCached_value, memCached_max, memCached_value));
                        if (mem < 70) {
                            $("#memCached_csss").removeClass();
                            $("#memCached_csss").addClass("progress-bar bg-success");
                            $("#memCacheds").removeClass();
                            $("#memCacheds").addClass("pull-right text-success");
                        }
                        if (mem >= 70) {
                            $("#memCached_csss").removeClass();
                            $("#memCached_csss").addClass("progress-bar bg-warning");
                            $("#memCacheds").removeClass();
                            $("#memCacheds").addClass("pull-right text-warning");
                        }
                        if (mem >= 90) {
                            $("#memCached_csss").removeClass();
                            $("#memCached_csss").addClass("progress-bar bg-danger");
                            $("#memCacheds").removeClass();
                            $("#memCacheds").addClass("pull-right text-danger");
                        }
                        $("#memCached_datas").html(memCached_data_value + " / " + memCached_data_max);
                        /*内存缓冲计算*/
                        var memBuffers_value = data.serverStatus.memBuffers['value'];
                        var memBuffers_max = data.serverStatus.memBuffers['max'];
                        $("#memBuffers").html(getPercent(memBuffers_value, memBuffers_max, memBuffers_value));
                        $("#memBuffers_css").css("width", getPercent(memBuffers_value, memBuffers_max, memBuffers_value));
                        var memB = getPercents(memCached_value, memCached_max, memCached_value);
                        if (memB != "Error") {
                            if (memB < 70) {
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-success");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-success");
                            }
                            if (memB >= 70) {
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-warning");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-warning");
                            }
                            if (memB >= 90) {
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-danger");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-danger");
                            }
                        }
                        if (floats(setSize(memBuffers_value, 'mb')) > 1024) {
                            var memBuffers_data_value = floats(setSize(memBuffers_value, 'gb')) + "GB";
                        } else {
                            var memBuffers_data_value = floats(setSize(memBuffers_value, 'mb')) + "MB";
                        }
                        if (floats(setSize(memBuffers_max, 'mb')) > 1024) {
                            var memBuffers_data_max = floats(setSize(memBuffers_max, 'gb')) + "GB";
                        } else {
                            var memBuffers_data_max = floats(setSize(memBuffers_max, 'mb')) + "MB";
                        }
                        $("#memBuffers_data").html(memBuffers_data_value + " / " + memBuffers_data_max);
                        $("#memBufferss").html(getPercent(memBuffers_value, memBuffers_max, memBuffers_value));
                        $("#memBuffers_csss").css("width", getPercent(memBuffers_value, memBuffers_max, memBuffers_value));
                        if (memB < 70) {
                            $("#memBuffers_csss").removeClass();
                            $("#memBuffers_csss").addClass("progress-bar bg-success");
                            $("#memBufferss").removeClass();
                            $("#memBufferss").addClass("pull-right text-success");
                        }
                        if (memB >= 70) {
                            $("#memBuffers_csss").removeClass();
                            $("#memBuffers_csss").addClass("progress-bar bg-warning");
                            $("#memBufferss").removeClass();
                            $("#memBufferss").addClass("pull-right text-warning");
                        }
                        if (memB >= 90) {
                            $("#memBuffers_csss").removeClass();
                            $("#memBuffers_csss").addClass("progress-bar bg-danger");
                            $("#memBufferss").removeClass();
                            $("#memBufferss").addClass("pull-right text-danger");
                        }
                        $("#memBuffers_datas").html(memBuffers_data_value + " / " + memBuffers_data_max);
                        /*服务器时间*/
                        $("#time").html('<span class="badge badge-sm bg-dark">' + data.serverInfo.serverTime + '</span>');
                        $("#times").html('<span class="badge badge-sm bg-dark">' + data.serverInfo.serverTime + '</span>');
                        /*服务器已运行时间*/
			        	var Uptime = '';
			        	if(data.serverInfo.serverUptime["days"] != ""){
			        		Uptime += data.serverInfo.serverUptime["days"] + ' 天 ';
		        		}
		        		if(data.serverInfo.serverUptime["hours"] != ""){
			        		Uptime += data.serverInfo.serverUptime["hours"] + ' 小时 ';
		        		}
			        	if(data.serverInfo.serverUptime["mins"] != ""){
			        		Uptime += data.serverInfo.serverUptime["mins"] + ' 分 ';
		        		}
			        	if(data.serverInfo.serverUptime["secs"] != ""){
		        			Uptime += data.serverInfo.serverUptime["secs"] + ' 秒 ';
		        		}
                        $("#u_time").html('<span class="badge badge-sm bg-dark">' + Uptime + '</span>');
                        $("#u_times").html('<span class="badge badge-sm bg-dark">' + Uptime + '</span>');
                        /*网络计算*/
		        		if(data.networkStats.networks.eth0 != null && data.networkStats.networks.eth0.tx != "" && data.networkStats.networks.eth0.rx != ""){
				        	$("#eths_div").show();
			        		$("#eth1s_div").show();
				        	$("#eths_br").show();
			        		$("#eth1s_br").show();
				        	se_tx = data.networkStats.networks.eth0.tx;
                            se_rx = data.networkStats.networks.eth0.rx;
				        	if (floats(setSize(data.networkStats.networks.eth0.tx, 'mb')) > 1024) {
                                var aaa_tx = floats(setSize(data.networkStats.networks.eth0.tx, 'gb')) + "GB";
                            } else {
                                var aaa_tx = floats(setSize(data.networkStats.networks.eth0.tx, 'mb')) + "MB";
                            }
                            if (floats(setSize(data.networkStats.networks.eth0.rx, 'mb')) > 1024) {
                                var aaa_rx = floats(setSize(data.networkStats.networks.eth0.rx, 'gb')) + "GB";
                            } else {
                                var aaa_rx = floats(setSize(data.networkStats.networks.eth0.rx, 'mb')) + "MB";
                            }
                            $("#eth").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + aaa_tx + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + aaa_rx + '</span>');
                            $("#eth1").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.eth0.tx - se_tx, 3) + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.eth0.rx - se_rx, 3) + '</span>');
                            $("#eths").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-upload" aria-hidden="true"></i>&nbsp;' + aaa_tx + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-download" aria-hidden="true"></i>&nbsp;' + aaa_rx + '</span>');
                            $("#eth1s").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.eth0.tx - se_tx, 3) + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.eth0.rx - se_rx, 3) + '</span>');
				        }else{
				        	$("#eths_div").hide();
				        	$("#eth1s_div").hide();
				        	$("#eths_br").hide();
				        	$("#eth1s_br").hide();
				        }
				        if(data.networkStats.networks.lo.tx != "" && data.networkStats.networks.lo.rx != ""){
				        	$("#ios_div").show();
				        	$("#io1s_div").show();
			        		$("#ios_br").show();
			        		$("#io1s_br").show();
			        		si_tx = data.networkStats.networks.lo.tx;
                            si_rx = data.networkStats.networks.lo.rx;
			        	    if (floats(setSize(data.networkStats.networks.lo.tx, 'mb')) > 1024) {
                                var lo_tx = floats(setSize(data.networkStats.networks.lo.tx, 'gb')) + "GB";
                            } else {
                                var lo_tx = floats(setSize(data.networkStats.networks.lo.tx, 'mb')) + "MB";
                            }
                            if (floats(setSize(data.networkStats.networks.lo.rx, 'mb')) > 1024) {
                                var lo_rx = floats(setSize(data.networkStats.networks.lo.rx, 'gb')) + "GB";
                            } else {
                                var lo_rx = floats(setSize(data.networkStats.networks.lo.rx, 'mb')) + "MB";
                            }
                            $("#io").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + lo_tx + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + lo_rx + '</span>');
                            $("#io1").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.lo.tx - si_tx, 3) + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.lo.rx - si_rx, 3) + '</span>');
                            $("#ios").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + lo_tx + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + lo_rx + '</span>');
                            $("#io1s").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.lo.tx - si_tx, 3) + '</span>&nbsp;' + '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;' + ForDight(data.networkStats.networks.lo.rx - si_rx, 3) + '</span>');
			        	}else{
				        	$("#ios_div").hide();
				        	$("#io1s_div").hide();
				        	$("#ios_br").hide();
				        	$("#io1s_br").hide();
			        	}
                    }
                },
            });
        }
    </script>
<?php } ?>
         </div>
        </article>
       </div>
       <!--评论-->
        <?php $this->need('component/comments.php'); ?>
      </div>
     </div>
     <!--文章右侧边栏开始-->
    <?php $this->need('component/sidebar.php'); ?>
     <!--文章右侧边栏结束-->
    </div>
   </main>
    <!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->