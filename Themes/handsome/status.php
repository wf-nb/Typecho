<?php
/**
 * 服务器状态
 *
 * @version:1.0.5
 * @author Weifeng
 * https://github.com/acewfdy/Handsome
 * @package custom
 */

error_reporting(E_ALL ^ E_NOTICE);
$md5key = 'WDNMD'; //服务器信息加密密匙，尽量设置复杂
setcookie('client_ip',get_ip());

/**
 * BT 或 EP 的函数
 */
    /**
	 * BT
	 * 检测服务器通讯
	 */
	function bt_check($bt_url,$bt_key){
		$url = $bt_url.'/system?action=GetSystemTotal';
        $p_data = bt_token($bt_key);
        $result = bt_get($url,$p_data,60,$bt_url);
        $data = json_decode($result,true);
		if(isset($data['status']) && $data['status'] == false){
			$msg = array('status' => false,'msg' => $data['msg']);
		}else{
			$msg = array('status' => 200,'msg' => '成功');
		}
        return $msg;
	}
    /**
     * BT
     * 获取系统基础统计
     */
    function bt_system($bt_url,$bt_key){
        $url = $bt_url.'/system?action=GetSystemTotal';
        $p_data = bt_token($bt_key);
        $result = bt_get($url,$p_data,60,$bt_url);
        $data = json_decode($result,true);
        return $data;
    }
    /**
     * BT
     * 获取磁盘分区信息
     */
    function bt_disk($bt_url,$bt_key){
        $url = $bt_url.'/system?action=GetDiskInfo';
        $p_data = bt_token($bt_key);
        $result = bt_get($url,$p_data,60,$bt_url);
        $data = json_decode($result,true);
        return $data;
    }
    /**
     * BT
     * 获取实时状态信息
     */
    function bt_network($bt_url,$bt_key){
        $url = $bt_url.'/system?action=GetNetWork';
        $p_data = bt_token($bt_key);
        $result = bt_get($url,$p_data,60,$bt_url);
        $data = json_decode($result,true);
        return $data;
    }
    /**
     * BT
     * 构造带有签名的关联数组
     */
    function bt_token($bt_key){
        $now_time = time();
        $p_data = array(
            'request_token'	=>	md5($now_time.''.md5($bt_key)),
            'request_time'	=>	$now_time
        );
        return $p_data;
    }
    /**
     * BT
     * 发起POST请求
     * @param String $url 目标网填，带http://
     * @param Array|String $data 欲提交的数据
     * @return string
     */
    function bt_get($url, $data,$timeout = 60, $bt_url){
        //定义cookie保存位置
        $cookie_file=__DIR__ . '/assets/cache/'.md5($bt_url).'.cookie';
        if(!file_exists($cookie_file)){
            $fp = fopen($cookie_file,'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
	/**
     * 获得访客操作系统
     */
    function get_os() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $os = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $os)) {
                $os = 'Windows';
            } else if (preg_match('/mac/i', $os)) {
                $os = 'MAC';
            } else if (preg_match('/linux/i', $os)) {
                $os = 'Linux';
            } else if (preg_match('/unix/i', $os)) {
                $os = 'Unix';
            } else if (preg_match('/bsd/i', $os)) {
                $os = 'BSD';
            } else {
                $os = 'Other';
            }
            return $os;
        } else {
            return 'unknow';
        }
    }
    /**
     * 获得访问者浏览器
     */
    function get_browse() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } else if (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } else if (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } else if (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } else if (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return 'unknow';
        }
    }
    /**
     * 获得访问者浏览器语言
     */
    function get_lang() {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match('/zh-cn/i',$lang)) {
                $lang = '简体中文';
            } else if (preg_match('/zh/i',$lang)) {
                $lang = '繁体中文';
            } else {
                $lang = 'English';
            }
            return $lang;
        } else {
            return 'unknow';
        }
    }
	/**
	 * 获取访问者IP
	 */
	function get_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
	/**
	 * 更新缓存数据
	 */
	function updateData($name,$data){
		$dirPath = __DIR__ . '/assets/cache';
        $filePath = $dirPath . '/'.$name;
		$file = fopen($filePath, "w");
    	fwrite($file, $data);
    	fclose($file);
	}
	/**
	 * 获取网站协议
	 */
	function get_http_type($type){
		if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')){
			if($type == 1){
			    return 'HTTPS';
			}else{
				return 'https';
			}
		}else{
			if($type == 1){
			    return 'HTTP';
			}else{
				return 'http';
			}
		}
	}
	/**
	 * 检测缓存文件夹
	 */
	function check_dir(){
        $dirPath = __DIR__ . '/assets/cache';
        //检测缓存目录是否存在，不存在则创建
        if (is_dir($dirPath) == false) {
            mkdir($dirPath, 0775, true);
        }
	}

if(isset($_GET['api'])){
    $api = $_GET['api'];
	if($api == 'server'){
        //定义服务器信息保存位置
        $info_file = __DIR__ . '/assets/cache/'.md5($md5key).'.json';
        if(file_exists($info_file)){
            $info_arr = json_decode(file_get_contents($info_file),true);
			$bt_check = bt_check($info_arr['bt_url'],$info_arr['bt_key']);
			if($bt_check['status'] == 200){
                $bt_system = bt_system($info_arr['bt_url'],$info_arr['bt_key']);
                $bt_disk = bt_disk($info_arr['bt_url'],$info_arr['bt_key']);
                $bt_network = bt_network($info_arr['bt_url'],$info_arr['bt_key']);
			    if($info_arr['show'] == 1){
			    	$bt_disk_arr = $bt_disk[0]['size'];
				    unset($bt_disk['size']);
				    $bt_disk['size'] = array(
				        'diskTotal' => $bt_disk_arr[0],
				        'diskUsed' => $bt_disk_arr[1],
				        'diskFree' => $bt_disk_arr[2],
				        'diskPercent' => $bt_disk_arr[3]
					    );
			        $msg = array(
                        'status' => 200,
                        'msg' => '获取成功',
				        'show' => 1,
				        'bt' => array(
					        'system' => $bt_system,
					        'disk' => $bt_disk,
					        'network' => $bt_network
						    ),
					    'now' => date("h:i:sa")
                    );
			    }elseif($info_arr['show'] == 2){
			        $msg = array(
                        'status' => 200,
                        'msg' => '获取成功',
				        'show' => 2,
				        'ep' => $ep_info
                    );
			    }elseif($info_arr['show'] == 3){
				    $msg = array(
                        'status' => 200,
                        'msg' => '获取成功',
				        'show' => 2,
					    'bt' => $bt_info,
				        'ep' => $ep_info
                    );
			    }
		    }else{
				$msg = $bt_check;
			}
        }else{
            $msg = array(
                'status' => 403,
                'msg' => '服务器信息未填写，请填写后刷新页面'
            );
        }
		echo json_encode($msg);
	}elseif($api == 'user'){
		$ip = $_COOKIE['client_ip'];
		//定义服务器信息保存位置
        $info_file = __DIR__ . '/assets/cache/'.md5($md5key).'.json';
        if(file_exists($info_file)){
            $info_arr = json_decode(file_get_contents($info_file),true);
			$ip_api = $info_arr['ip_api'];
			if($ip_api == 'IP.SB'){
		        $ip_info = json_decode(file_get_contents('https://api.ip.sb/geoip/'.$ip),true);
		        $ip_info_arr = array(
				    'ip' => $ip_info['ip'],
			    	'country' => $ip_info['country'],
			    	'region' => $ip_info['region'],
			    	'city' => $ip_info['city'],
			    	'isp' => $ip_info['isp']
				);
			}else{
				$ip_info = json_decode(file_get_contents('http://ip-api.com/json/'.$ip),true);
		        $ip_info_arr = array(
				    'ip' => $ip_info['query'],
			    	'country' => $ip_info['country'],
			    	'region' => $ip_info['regionName'],
			    	'city' => $ip_info['city'],
			    	'isp' => $ip_info['isp']
				);
			}
		    $msg = array(
		        'os' => get_os(),
			    'browser' => get_browse(),
			    'lang' => get_lang(),
			    'ipinfo' => array(
			        'ip' => $ip_info_arr['ip'],
			    	'country' => $ip_info_arr['country'],
			    	'region' => $ip_info_arr['region'],
			    	'city' => $ip_info_arr['city'],
			    	'isp' => $ip_info_arr['isp']
			    )
		    );
		}else{
            $msg = array(
                'status' => 403,
                'msg' => '服务器信息未填写，请填写后刷新页面'
            );
        }
		echo json_encode($msg);
	}
	exit();
}

$ajax_time = $this->fields->ajax_time; //数据刷新秒数
$file_url = $this->fields->file_url; //文件网址
$sweet_js = $this->fields->sweet_js; //是否调用sweetalertjs
$ip_api = $this->fields->ip_api; //获取IP信息的API
$bt_url = $this->fields->bt_url; //宝塔地址
$bt_key = $this->fields->bt_key; //宝塔密匙
$ep_url = $this->fields->ep_url; //EP地址
$ep_key = $this->fields->ep_key; //EP密匙

/**
 * $pageset 0为关闭 1为bt 2为ep 3为两个都有
 */
if(empty($bt_url) && empty($bt_key) && empty($ep_url) && empty($ep_key)) {
    $pageset = 0;
}elseif(isset($bt_url) && isset($bt_key)) {
    $pageset = 1;
}elseif(isset($ep_url) && isset($ep_key)) {
    $pageset = 2;
}elseif(isset($bt_url) && isset($bt_key) && isset($ep_url) && isset($ep_key)) {
    $pageset = 3;
}else{
    $pageset = 0;
}
if(empty($file_url)){
	$http_type = get_http_type(0);
	$file_url = $http_type.'://'.$_SERVER['HTTP_HOST'].'/usr/themes/handsome/status.php';
}
if(empty($ajax_time)){
    $ajax_time = '10';
}elseif($ajax_time < '1'){
	$ajax_time = '1';
}

/**
 * 保存服务器信息为JSON
 */
if($pageset == 1){
    $info_arr = array(
	    'show' => 1,
		'ip_api' => $ip_api,
        'bt_url' => $bt_url,
        'bt_key' => $bt_key
    );
	check_dir();
    updateData(md5($md5key).'.json',json_encode($info_arr));
}elseif($pageset == 2){
    $info_arr = array(
	    'show' => 2,
		'ip_api' => $ip_api,
        'ep_url' => $ep_url,
        'ep_key' => $ep_key
    );
	check_dir();
    updateData(md5($md5key).'.json',json_encode($info_arr));
}elseif($pageset == 3){
    $info_arr = array(
	    'show' => 3,
		'ip_api' => $ip_api,
        'bt_url' => $bt_url,
        'bt_key' => $bt_key,
        'ep_url' => $ep_url,
        'ep_key' => $ep_key
    );
	check_dir();
	updateData(md5($md5key).'.json',json_encode($info_arr));
}
?>

<?php $this->need('component/header.php'); ?>

<?php if($lazyload == "1"):?>
    <script src="https://cdn.bootcss.com/lazyloadjs/3.2.2/lazyload.min.js"></script>
<?php endif; ?>
<?php if($sweet_js == "0" || empty($sweet_js)):?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<?php endif; ?>


<!-- aside -->
<?php $this->need('component/aside.php'); ?>
<!-- / aside -->

<!-- <div id="content" class="app-content"> -->
<a class="off-screen-toggle hide"></a>
<main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
        <!--文章-->
        <div class="col center-part">
            <header class="bg-light lter b-b wrapper-md">
                <h1 class="m-n font-thin h3"><i data-feather="server" class="i-sm m-r-sm"></i><?php _me("服务器状态") ?></h1>
                <small class="text-muted letterspacing indexWords"><?php echo $this->fields->intro; ?></small>
            </header>
            <div class="wrapper-md" id="post-panel">
                <!--博客文章样式 begin with .blog-post-->
                <div id="postpage" class="blog-post">
                    <article class="panel">
                        <!--文章内容-->
         <div id="post-content" class="wrapper-lg">
            <div class="entry-content l-h-2x">
                          </div>
            
              <div class="">
              	
	            <div class="">
	              <span class="pull-right text-danger" id="cpu"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>CPU</span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="cpu_css" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            
	            
	            <div class="">
	              <span class="pull-right text-danger" id="memory"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>占用内存 <span class="badge badge-sm bg-dark" id="memory_data"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span</span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memory_css" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            
	            
	            <div class="">
	              <span class="pull-right text-danger" id="disk"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>磁盘占用 <span class="badge badge-sm bg-dark" id="disk_data"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="disk_css" class="progress-bar bg-danger" data-toggle="tooltip"  style="width: 100%"></div>
	            </div>
	            
	            
	            <div class="">
	              <span class="pull-right text-danger" id="memCached"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>内存缓存 <span class="badge badge-sm bg-dark" id="memCached_data"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memCached_css" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            
	            
	            <div class="">
	              <span class="pull-right text-danger" id="memBuffers"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>内存缓冲 <span class="badge badge-sm bg-dark" id="memBuffers_data"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="memBuffers_css" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            
	            
	            <div class="">
	              <span class="pull-right text-danger" id="state_s"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>系统负载 <span id="state"><span class="badge badge-sm bg-dark"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span></span></span>
	            </div>
	            <div class="progress progress-xs m-t-sm bg-white">
	              <div id="state_css" class="progress-bar bg-danger" data-toggle="tooltip" style="width: 100%"></div>
	            </div>
	            
	            <div class="">
	              <span class="pull-right text-default" id="eth"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>eth</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="eth1"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>实时流量</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="os_span"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>操作系统</span>
	            </div>
	            <br/>
	           
	            <div class="">
	              <span class="pull-right text-default" id="time_span"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>服务器系统时间</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="uptime_span"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>持续运行时长</span>
	            </div>
	            <br/>
	            <div class="">
	              <span class="pull-right text-default"><span class="badge badge-sm bg-dark"><?php echo get_http_type(1); ?></span></span>
	              <span>通信协议名称版本</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default"><span class="badge badge-sm bg-dark"><?php echo PHP_VERSION; ?></span></span>
	              <span>PHP版本</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default"><span class="badge badge-sm bg-dark">GET</span></span>
	              <span>请求方法</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="ip"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的IP</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="address"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的地址</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="isp"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的ISP</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="browser"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的浏览器</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="lang"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的语言</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="sys"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的设备</span>
	            </div>
	            <br/>
	            
	            <div class="">
	              <span class="pull-right text-default" id="sys_time"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
	              <span>您的系统时间</span>
	            </div>
	            <br/>
	            

	          </div>
                     </div>
        </article>
       </div>
                <!--评论-->
                <?php $this->need('component/comments.php') ?>
            </div>
        </div>
        <!--文章右侧边栏开始-->
        <?php $this->need('component/sidebar.php'); ?>
        <!--文章右侧边栏结束-->
    </div>
</main>

<script type="text/javascript">
    function returnFloat(value){
        return value.toFixed(2)+'%';
    }
    function floats(value){
        return value.toFixed(2);
    }
    function getPercent(curNum, totalNum, isHasPercentStr) {
        curNum = parseFloat(curNum);
        totalNum = parseFloat(totalNum);

        if (isNaN(curNum) || isNaN(totalNum)) {
            return 'Error';
        }

        return isHasPercentStr ?
            totalNum <= 0 ? '0%' : (Math.round(curNum / totalNum * 10000) / 100.00 + '%') :
            totalNum <= 0 ? 0 : (Math.round(curNum / totalNum * 10000) / 100.00);
    }
    function getPercents(curNum, totalNum, isHasPercentStr) {
        curNum = parseFloat(curNum);
        totalNum = parseFloat(totalNum);

        if (isNaN(curNum) || isNaN(totalNum)) {
            return 'Error';
        }

        return isHasPercentStr ?
            totalNum <= 0 ? '0%' : (Math.round(curNum / totalNum * 10000) / 100.00) :
            totalNum <= 0 ? 0 : (Math.round(curNum / totalNum * 10000) / 100.00);
    }
    function ForDight(Dight,How)
    {
        if (Dight<0){
            var Last=0+"B/s";
        }else if (Dight<1024){
            var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"B/s";
        }else if (Dight<1048576){
            Dight=Dight/1024;
            var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"K/s";
        }else{
            Dight=Dight/1048576;
            var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"M/s";
        }
        return Last;
    }
    var se_rx;
    var se_tx;
    var si_rx;
    var si_tx;
	function Pageloader(){
        $.ajax({
            type : "get",
            url : "<?php echo $file_url; ?>?api=server",
            async : true,
            dataType:"json",
            error : function(){
                $("#cpu").html("Error");
            },
            success : function(data){
                if(data != null ){
					if(data.status == 200){
                        var cpu = data.bt.system.cpuRealUsed;
                        $("#cpu").html(returnFloat(cpu));
                        $("#cpu_css").css("width",returnFloat(cpu));
                        if(cpu<70){
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-success");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-success");
                        }
                        if(cpu>=70){
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-warning");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-warning");
                        }
                        if(cpu>=90){
                            $("#cpu_css").removeClass();
                            $("#cpu_css").addClass("progress-bar bg-danger");
                            $("#cpu").removeClass();
                            $("#cpu").addClass("pull-right text-danger");
                        }

                        var memory_value = data.bt.system.memRealUsed;
                        var memory_max = data.bt.system.memTotal;
                        $("#memory").html(getPercent(memory_value,memory_max,memory_value));
                        $("#memory_css").css("width",getPercent(memory_value,memory_max,memory_value));
                        var me = getPercents(memory_value,memory_max,memory_value);
                        if(me != "Error"){
                            if(me<70){
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-success");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-success");
                            }
                            if(me>=70){
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-warning");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-warning");
                            }
                            if(me>=90){
                                $("#memory_css").removeClass();
                                $("#memory_css").addClass("progress-bar bg-danger");
                                $("#memory").removeClass();
                                $("#memory").addClass("pull-right text-danger");
                            }
                        }
                        if(floats((memory_value/1024000))>1000){
                            var memory_data_value = floats(memory_value)+"GB";
                        } else{
                            var memory_data_value = floats(memory_value)+"MB";
                        }
                        if(floats((memory_max/1024000))>1000){
                            var memory_data_max = memory_max+"GB";
                        } else{
                            var memory_data_max = memory_max+"MB";
                        }
                        $("#memory_data").html(memory_data_value+" / "+memory_data_max);

                        $("#time_span").html('<span class="badge badge-sm bg-dark">'+data.now+'</span>');
					
					    $("#os_span").html('<span class="badge badge-sm bg-dark">'+data.bt.system.system+'</span>');

                        $("#uptime_span").html('<span class="badge badge-sm bg-dark">'+data.bt.system.time+'</span>');

                        var disk_value = data.bt.disk.size.diskUsed;
                        var disk_max = data.bt.disk.size.diskTotal;
                        $("#disk").html(data.bt.disk.size.diskPercent);
                        $("#disk_css").css("width",data.bt.disk.size.diskPercent);
                        var dk = parseFloat(data.bt.disk.size.diskPercent);
                        if(dk != "Error"){
                            if(dk<70){
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-success");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-success");
                            }
                            if(dk>=70){
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-warning");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-warning");
                            }
                            if(dk>=90){
                                $("#disk_css").removeClass();
                                $("#disk_css").addClass("progress-bar bg-danger");
                                $("#disk").removeClass();
                                $("#disk").addClass("pull-right text-danger");
                            }
                        }
                        var disk_data_value = disk_value+"B";
                        var disk_data_max = disk_max+"B";
                        $("#disk_data").html(disk_data_value+" / "+disk_data_max);


                        var state = '<span class="badge badge-sm bg-dark">'+data.bt.network.load.one+'</span>&nbsp;<span class="badge badge-sm bg-dark">'+data.bt.network.load.five+'</span>&nbsp;<span class="badge badge-sm bg-dark">'+data.bt.network.load.fifteen+'</span>'
                        $("#state").html(state);
                        var state_s = getPercent(data.bt.network.load.one,data.bt.network.load.max,data.bt.network.load.one);
                        $("#state_css").css("width",state_s);
                        $("#state_s").html(state_s);
                        var sta = getPercents(data.bt.network.load.one,data.bt.network.load.max,data.bt.network.load.one);
                        if(sta != "Error"){
                            if(sta<70){
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-success");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-success");
                            }
                            if(sta>=70){
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-warning");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-warning");
                            }
                            if(sta>=90){
                                $("#state_css").removeClass();
                                $("#state_css").addClass("progress-bar bg-danger");
                                $("#state_s").removeClass();
                                $("#state_s").addClass("pull-right text-danger");
                            }
                        }

                        var memCached_value = data.bt.network.mem.memCached;
                        var memCached_max = data.bt.network.mem.memTotal;
                        $("#memCached").html(getPercent(memCached_value,memCached_max,memCached_value));
                        $("#memCached_css").css("width",getPercent(memCached_value,memCached_max,memCached_value));
                        var mem = getPercents(memCached_value,memCached_max,memCached_value);
                        if(mem != "Error"){
                            if(mem<70){
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-success");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-success");
                            }
                            if(mem>=70){
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-warning");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-warning");
                            }
                            if(mem>=90){
                                $("#memCached_css").removeClass();
                                $("#memCached_css").addClass("progress-bar bg-danger");
                                $("#memCached").removeClass();
                                $("#memCached").addClass("pull-right text-danger");
                            }
                        }
                        if(floats((memCached_value/1024000))>1000){
                            var memCached_data_value = floats(memCached_value)+"GB";
                        } else{
                            var memCached_data_value = floats(memCached_value)+"MB";
                        }
                        if(floats((memCached_max/1024000))>1000){
                            var memCached_data_max = floats(memCached_max)+"GB";
                        } else{
                            var memCached_data_max = floats(memCached_max)+"MB";
                        }
                        $("#memCached_data").html(memCached_data_value);

                        var memBuffers_value = data.bt.network.mem.memBuffers;
                        var memBuffers_max = data.bt.network.mem.memTotal;
                        $("#memBuffers").html(getPercent(memBuffers_value,memBuffers_max,memBuffers_value));
                        $("#memBuffers_css").css("width",getPercent(memBuffers_value,memBuffers_max,memBuffers_value));
                        var memB = getPercents(memCached_value,memCached_max,memCached_value);
                        if(memB != "Error"){
                            if(memB<70){
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-success");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-success");
                            }
                            if(memB>=70){
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-warning");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-warning");
                            }
                            if(memB>=90){
                                $("#memBuffers_css").removeClass();
                                $("#memBuffers_css").addClass("progress-bar bg-danger");
                                $("#memBuffers").removeClass();
                                $("#memBuffers").addClass("pull-right text-danger");
                            }
                        }
                        if(floats((memBuffers_value/1024000))>1000){
                            var memBuffers_data_value = floats(memBuffers_value)+"GB";
                        } else{
                            var memBuffers_data_value = floats(memBuffers_value)+"MB";
                        }
                        if(floats((memBuffers_max/1024000))>1000){
                            var memBuffers_data_max = floats(memBuffers_max)+"GB";
                        } else{
                            var memBuffers_data_max = floats(memBuffers_max)+"MB";
                        }
                        $("#memBuffers_data").html(memBuffers_data_value);
                        if(floats((data.bt.network.upTotal/1024000))>1000){
                            var aaa_tx = floats((data.bt.network.upTotal/1024000000))+"GB";
                        } else{
                            var aaa_tx = floats((data.bt.network.upTotal/1024000))+"MB";
                        }
                        if(floats((data.bt.network['downTotal']/1024000))>1000){
                            var aaa_rx = floats((data.bt.network.downTotal/1024000000))+"GB";
                        } else{
                            var aaa_rx = floats((data.bt.network.downTotal/1024000))+"MB";
                        }
                        $("#eth1").html('<span class="badge badge-sm bg-dark"><i class="fa fa-cloud-upload" aria-hidden="true"></i>&nbsp;'+ForDight(data.bt.network.upTotal-se_tx,3)+'</span>&nbsp;'+
                        '<span class="badge badge-sm bg-dark"><i class="fa fa-cloud-download" aria-hidden="true"></i>&nbsp;'+ForDight(data.bt.network.downTotal-se_rx,3)+'</span>');

                        $("#eth").html('<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-upload" aria-hidden="true"></i>&nbsp;'+aaa_tx+'</span>&nbsp;'+
                        '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-download" aria-hidden="true"></i>&nbsp;'+aaa_rx+'</span>&nbsp;'+
                        '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></i>&nbsp;'+ForDight(data.bt.network.upTotal-se_tx,3)+'</span>&nbsp;'+
                        '<span class="badge badge-sm bg-dark"><i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp;'+ForDight(data.bt.network.downTotal-se_rx,3)+'</span>');
                        se_tx = data.bt.network.upTotal;
                        se_rx = data.bt.network.downTotal;
						setInterval(function(){Pageloader()},<?php echo $ajax_time; ?>*1000);
					}else{
						Swal.fire({allowOutsideClick:false,icon:'error',type:'error',title:data.msg,showConfirmButton:true,timer:0});
					}
                }
            },
        });
	};
	$(function(){
		Pageloader();
		Swal.fire({allowOutsideClick:false,imageUrl:'https://ae01.alicdn.com/kf/U05ed7e65d7a749a29bd6164f9d4abe84w.gif',title:"与服务器通讯中...",showConfirmButton:true,timer:0});
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
    setInterval(function(){
        $("#sys_time").html('<span class="badge badge-sm bg-dark">'+getNowFormatDate()+'</span>');
    },1000);
    function UserInfo(){
        $.ajax({
            type : "get",
            url : "<?php echo $file_url; ?>?api=user",
            async : true,
            dataType:"json",
            error : function(){
                UserInfo();
            },
            success : function(data){
                if(data!=null){
                    if(data.ipinfo.ip==null){
                        UserInfo();
                    }else if(data.ipinfo.country==null){
                        UserInfo();
                    }else if(data.ipinfo.region==null){
                        UserInfo();
                    }else if(data.ipinfo.city==null){
                        UserInfo();
                    }else if(data.ipinfo.isp==null){
                        UserInfo();
                    }else{
                        $("#ip").html('<span class="badge badge-sm bg-dark">'+data.ipinfo.ip+'</span>');
                        $("#address").html('<span class="badge badge-sm bg-dark">'+data.ipinfo.country+'&nbsp;'+data.ipinfo.region+'&nbsp;'+data.ipinfo.city+'</span>');
						$("#lang").html('<span class="badge badge-sm bg-dark">'+data.lang+'</span>');
                        $("#browser").html('<span class="badge badge-sm bg-dark">'+data.browser+'</span>');
                        $("#sys").html('<span class="badge badge-sm bg-dark">'+data.os+'</span>');
                        $("#isp").html('<span class="badge badge-sm bg-dark">'+data.ipinfo.isp+'</span>');
                    }
                }
            },
        });
    };UserInfo();
</script>
<!-- footer -->
<?php $this->need('component/footer.php'); ?>
<!-- / footer -->
