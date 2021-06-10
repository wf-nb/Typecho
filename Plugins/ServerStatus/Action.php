<?php
class ServerStatus_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;
	public $plugin_dir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/ServerStatus/';

    public function Status($id = null)
	{
		error_reporting(E_ALL^E_NOTICE);
		header("Access-Control-Allow-Origin:*");
        header('Content-type: application/json');
		if(empty($id)){
			$id = $_GET['id'];
		}
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $id)->limit(1));
		if(!file_exists($this->plugin_dir.'log/'.$server['sign'].'.log')){
            $fp = fopen($this->plugin_dir.'log/'.$server['sign'].'.log','w+');
			fwrite($fp,'|'.strtotime("now"));
			fclose($fp);
        }
		$log = file_get_contents($this->plugin_dir.'log/'.$server['sign'].'.log');
		$log_arr = explode('|',$log);
		$nowTime = strtotime("now");
		$updateTime = strtotime(date("Y-m-d H:i:s",$log_arr[1])."+{$server['ajax']}second");
		if($nowTime >= $updateTime || !is_array($log_arr) || empty($log_arr[0])){
			if($server['type'] == 'default' || $server['type'] == 'winbt'){
				$data = $this->getContent($server['url'].'?key='.$server['key'].'&action=fetch',null);
				echo $data;
				file_put_contents($this->plugin_dir.'log/'.$server['sign'].'.log',$data.'|'.$nowTime);
			}elseif($server['type'] == 'linuxbt'){
				$url_parse = parse_url($server['url']);
                $server['url'] = $url_parse['scheme'].'://'.$url_parse['host'].':'.$url_parse['port'];
				$bt_system = $this->bt_system($server['url'],$server['key']);
        	    $bt_network = $this->bt_network($server['url'],$server['key']);
				$bt_disk = $bt_network['disk'][0]['size'];
				$data = array(
				    "serverInfo" => array(
					    "serverTime" => date("Y-m-d H:i:s"),
						"serverUptime" => array(
						    "days" => explode('天',$bt_system['time'])[0],
							"hours" => explode('时',explode('天',$bt_system['time'])[1])[0],
							"mins" => explode('分',explode('时',explode('天',$bt_system['time'])[1])[1])[0],
							"secs" => explode('秒',explode('分',explode('时',explode('天',$bt_system['time'])[1])[1])[1])[0]
						),
						"serverUtcTime" => gmdate("Y/m/d H:i:s"),
						"diskUsage" => array(
						    "value" => str_replace('G','',$bt_disk[1])*1024*1024*1024,
							"max" => str_replace('G','',$bt_disk[0])*1024*1024*1024
			            )
	    	        ),
					"serverStatus" => array(
					    "sysLoad" => array(
						    $bt_network['load']['one'],
						    $bt_network['load']['five'],
						    $bt_network['load']['fifteen']
						),
						"cpuUsage" => array(
						    "user" => $bt_system['cpuRealUsed'],
							"nice" => 0,
						    "sys" => $bt_system['cpuRealUsed'],
						    "idle" => 100-$bt_system['cpuRealUsed']*2
						),
						"memRealUsage" => array(
						    "value" => $bt_network['mem']['memRealUsed']*1024*1024,
							"max" => $bt_network['mem']['memTotal']*1024*1024
		    	        ),
						"memBuffers" => array(
						    "value" => $bt_network['mem']['memBuffers']*1024*1024,
							"max" => $bt_network['mem']['memTotal']*1024*1024
		    	        ),
						"memCached" => array(
						    "value" => $bt_network['mem']['memCached']*1024*1024,
							"max" => $bt_network['mem']['memTotal']*1024*1024
		    	        ),
						"swapUsage" => array(
						    "value" => 0,
							"max" => 0
		    	        ),
						"swapCached" => array(
						    "value" => 0,
							"max" => 0
		    	        )
					),
					"networkStats" => array(
					    "networks" => array(
						    "lo" => array(
							    "rx" => $bt_network['downTotal'],
								"tx" => $bt_network['upTotal']
							),
						    "eth0" => array(
							    "rx" => $bt_network['downTotal'],
								"tx" => $bt_network['upTotal']
							)
						)
					)
				);
				echo json_encode($data,JSON_UNESCAPED_UNICODE);
				file_put_contents($this->plugin_dir.'log/'.$server['sign'].'.log',json_encode($data,JSON_UNESCAPED_UNICODE).'|'.$nowTime);
			}
		}else{
			echo $log_arr[0];
		}
	}

	public function IPInfo()
	{
		error_reporting(E_ALL^E_NOTICE);
		header("Access-Control-Allow-Origin:*");
        header('Content-type: application/json');
		$ip = $this->getIp();
		if(explode('.',$ip)[0] == '10' || explode('.',$ip)[0] == '127' || explode('.',$ip)[0].'.'.explode('.',$ip)[1] == '192.168'){
			$data = array(
    	    	"code" => 200,
		    	"msg" => "获取IP信息成功",
				"ip" => $ip,
				"data" => array(
					"isp" => "Yourself",
					"country" => "该IP为内网IP",
					"region" => "",
					"city" => "",
					"ip" => $ip,
					"os" => $this->get_os(),
					"browse" => $this->get_browse(),
					"date" => date("Y-m-d")
				)
    		);
    		echo json_encode($data,JSON_UNESCAPED_UNICODE);
    		exit;
		}elseif(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE) && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
			$data = array(
		        "code" => false,
			    "msg" => "请传入正确的公共IP"
		    );
		    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		    exit;
		}
		$log = file_get_contents($this->plugin_dir.'log/ipinfo.log');
		$log_list = explode(PHP_EOL,$log);
		foreach($log_list as $k => $v){
			$v_arr = json_decode($v,true);
			if(!empty($v) && count($v_arr) != 0 && in_array($ip,$v_arr)){
				if($v_arr['date'] == date("Y-m-d")){
					$v_arr['os'] = $this->get_os();
					$v_arr['browse'] = $this->get_browse();
					$data = array(
		        		"code" => 200,
			    		"msg" => "获取IP信息成功",
				        "ip" => $ip,
				    	"data" => $v_arr
		    		);
					echo json_encode($data,JSON_UNESCAPED_UNICODE);
				    exit;
				}else{
					$log_new = str_replace($v.PHP_EOL,'',$log);
					file_put_contents($this->plugin_dir.'log/ipinfo.log',$log_new);
					break;
				}
			}else{
				continue;
			}
		}
		$IPApi = Typecho_Widget::widget('Widget_Options')->plugin('ServerStatus')->IPApi;
		$get = Typecho_Http_Client::get();
		$get->setHeader('User-Agent','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36');
		$get->setTimeout(3);
		switch($IPApi){
			case 'IP.SB':
			    $url = 'https://api.ip.sb/geoip/'.$ip;
				$get->setHeader('Referer','https://ip.sb/');
				break;
			case 'IP-API':
			    $url = 'http://ip-api.com/json/'.$ip.'?lang=zh-CN';
				$get->setHeader('Referer','https://ip-api.com/');
				$get->setHeader('Accept-Language','zh-CN,zh;q=0.9');
				break;
			case 'PConline':
			    $url = 'https://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.$ip;
				$get->setHeader('Referer','https://whois.pconline.com.cn/');
				$get->setHeader('Accept-Language','zh-CN,zh;q=0.9');
				$local = json_decode($this->strToUtf8(trim(file_get_contents('https://whois.pconline.com.cn/ipJson.jsp?json=true'))),true);
				break;
			case 'IW3C':
			    $url = 'https://v2.api.iw3c.top/?api=ip&ip='.$ip;
				$get->setHeader('Referer','https://www.iw3c.com.cn/');
				break;
			default:
			    break;
		}
		if(isset($url)){
		    $output = json_decode($this->strToUtf8(trim($get->send($url))),true);
		}
		$info = array();
		$save_file = false;
		if($IPApi == 'IP.SB'){
			$info['code'] = (isset($output['code']) && $output['status'] == 401)?false:200;
			$info['ip'] = $output['ip'];
			$info['country'] = $output['country'];
			if(!isset($output['region']) || !isset($output['city'])){
			    $info['region'] = '';
    		    $info['city'] = '';
		    }else{
			    $info['region'] = $output['region'];
    		    $info['city'] = $output['city'];
		    }
			$info['isp'] = $output['isp'];
			$save_file = true;
		}elseif($IPApi == 'IP-API'){
			$info['code'] = (isset($output['status']) && $output['status'] == 'fail')?false:200;
			$info['ip'] = $output['query'];
			$info['country'] = $output['country'];
			if(!isset($output['regionName']) || !isset($output['city'])){
			    $info['region'] = '';
    		    $info['city'] = '';
		    }else{
			    $info['region'] = $output['regionName'];
    		    $info['city'] = $output['city'];
			}
			$info['isp'] = $output['isp'];
			$save_file = true;
		}elseif($IPApi == 'PConline'){
			$info['code'] = ($output['ip'] == $local['ip'])?false:200;
	        $info['ip'] = $output['ip'];
			if($output['err'] == 'noprovince'){
		    	$info['country'] = $output['addr'];
			}else{
				$info['country'] = '中国';
				$info['region'] = $output['pro'];
    		    $info['city'] = $output['city'];
				$info['isp'] = explode(' ',$output['addr']);
				$info['isp'] = end($info['isp']);
			}
			$save_file = true;
		}elseif($IPApi == 'IW3C'){
			$info['code'] = (isset($output['status']) && ($output['status'] == 'fail' || $output['status'] == 'error'))?false:200;
			$info['ip'] = $output['data']['ip'];
			$info['country'] = $output['data']['country'];
			if(!isset($output['data']['region']) || !isset($output['data']['city'])){
			    $info['region'] = '';
    		    $info['city'] = '';
		    }else{
			    $info['region'] = $output['data']['region'];
    		    $info['city'] = $output['data']['city'];
			}
			$info['isp'] = $output['data']['isp'];
			$save_file = true;
		}else{
			$info['code'] = 200;
		}
		if($info['code'] == false){
			$data = array(
			    "code" => false,
				"msg" => "获取IP信息失败",
				"ip" => $ip
			);
		}else{
			$info['date'] = date("Y-m-d");
			unset($info['code']);
			if($save_file == true){
			    file_put_contents($this->plugin_dir.'log/ipinfo.log',json_encode($info,JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND | LOCK_EX);
			}
			$info['os'] = $this->get_os();
			$info['browse'] = $this->get_browse();
			$data = array(
		        "code" => 200,
				"msg" => "获取IP信息成功",
				"ip" => $ip,
				"data" => $info
			  );
		}
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}

    public function Check()
	{
		error_reporting(E_ALL^E_NOTICE);
		header("Access-Control-Allow-Origin:*");
        header('Content-type: application/json');
		$id = $_GET['id'];
		$type = $_GET['type'];
		if(empty($id)){
			echo '未传入服务器ID，请返回重试！';
			exit;
		}
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $id)->limit(1));
		if($server['type'] == 'default' || $server['type'] == 'winbt'){
			$data = $this->getContent($server['url'].'?key='.$server['key'].'&action=fetch',null);
			$data_arr = json_decode($data,true);
			if(empty($data)){
				$msg = array('code' => false,'msg' => '服务器通讯失败，可能是地址和密匙校验错误<br />服务器返回：无');
			}elseif(!isset($data_arr['serverStatus']['memRealUsage']['max']) || empty($data_arr['serverStatus']['memRealUsage']['max'])){
			    $msg = array('code' => false,'msg' => '服务器通讯失败，可能是权限不足引起的失败<br />服务器返回：'.$data);
		    }else{
			    $msg = array('code' => 200,'msg' => '服务器通讯成功');
		    };
		}elseif($server['type'] == 'linuxbt'){
			$url_parse = parse_url($server['url']);
            $server['url'] = $url_parse['scheme'].'://'.$url_parse['host'].':'.$url_parse['port'];
			$data = $this->bt_system($server['url'],$server['key']);
			if(empty($data)){
				$msg = array('code' => false,'msg' => '服务器通讯失败，可能是地址输入错误<br />服务器返回：无');
			}else{
				if(isset($data['status']) && $data['status'] == false){
				    $msg = array('code' => false,'msg' => $data['msg']);
		    	}else{
				    $msg = array('code' => 200,'msg' => '服务器通讯成功');
		    	};
			}
		}
		echo json_encode($msg,JSON_UNESCAPED_UNICODE);
	}

    public function Getfile()
	{
		$id = $_GET['id'];
		if(empty($id)){
			echo '未传入服务器ID，请返回重试！';
			exit;
		}
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $id)->limit(1));
		$url = $server['url'];
		$key = $server['key'];

		require_once($this->plugin_dir."Pclzip.php");
		$file_real = rand(100,999).'.zip';
		$file = $this->plugin_dir."log/{$file_real}";

		$file_path = $this->plugin_dir."other/ServerStatus.php";
		$cache_path = $this->plugin_dir."log/".md5($url.$key).".php";

		if(file_exists($file)) unlink($file);
		if(file_exists($cache_path)) unlink($cache_path);

		//安装包
		$file_str = file_get_contents($file_path);
		$file_str = str_replace('{ServerStatus_url}',$url,$file_str);
		$file_str = str_replace('{ServerStatus_key}',$key,$file_str);
		if(!file_exists($cache_path)){
		    $fp = fopen($cache_path,'w+');
		    fwrite($fp,$file_str);
		    fclose($fp);
		}

		$zip = new PclZip($file);
		$makeZip = $zip->add(array(array(PCLZIP_ATT_FILE_NAME => $cache_path,PCLZIP_ATT_FILE_NEW_FULL_NAME => "ServerStatus.php")));

        if($makeZip){
			$file_size=filesize("$file");
			header("Content-Description: File Transfer");
			header("Content-Type:application/force-download");
			header("Content-Length: {$file_size}");
			header("Content-Disposition:attachment; filename=ServerStatus_{$file_real}");
			readfile("$file");
		}else{
			echo '创建压缩包失败！';
		}
		unlink($file);
	    unlink($cache_path);
	}

    public function ServerIframe()
	{
		$id = $_GET['id'];
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$sql = $db->select()->from($prefix.'ServerStatus_server');
		if(empty($id) && ServerStatus_Plugin::GetCount() > 1){
		    $sql = $sql->order($prefix.'ServerStatus_server.id', Typecho_Db::SORT_ASC);
			$servers = $db->fetchAll($sql);
			include_once($this->plugin_dir."other/theme_index.php");
		}else{
			if(empty($id)){
				$id = ServerStatus_Plugin::info()['id'];
			}
			include_once($this->plugin_dir."other/theme_server.php");
		}
	}

    public function WebsiteIframe()
	{
		$options = Typecho_Widget::widget('Widget_Options');
		$UptimeKey = $options->plugin('ServerStatus')->UptimeKey;
		$UptimeLink = $options->plugin('ServerStatus')->UptimeLink;
		$UptimeDay = $options->plugin('ServerStatus')->UptimeDay;
		include_once($this->plugin_dir."other/theme_website.php");
	}

    function getContent($url,$header){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);                // true获取响应头的信息
		if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);        // 跳过证书验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);        // 跳过证书验证
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);        // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);           // 自动设置Referer
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);        // 设置等待时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);              // 设置cURL允许执行的最长秒数
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

	public function getIp() { 
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); 
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); 
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); 
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR']; 
		else $ip = "Unknown"; 
		return ($ip); 
	}

	public function insertServer()
	{
		if (ServerStatus_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		/** 取出数据 */
		$server = $this->request->from('name', 'sign', 'type', 'url', 'key', 'ajax', 'desc');
		$server['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix.'ServerStatus_server'))->maxOrder + 1;

		/** 插入数据 */
		$server['id'] = $this->db->query($this->db->insert($this->prefix.'ServerStatus_server')->rows($server));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('server-'.$server['id']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('服务器 <a href="%s">%s</a> 已经被增加',
		$server['url'], $server['name']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=ServerStatus%2FServer.php', $this->options->adminUrl));
	}

	public function updateServer()
	{
		if (ServerStatus_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}

		/** 取出数据 */
		$server = $this->request->from('id', 'name', 'sign', 'type', 'url', 'key', 'ajax', 'desc');

		/** 更新数据 */
		$this->db->query($this->db->update($this->prefix.'ServerStatus_server')->rows($server)->where('id = ?', $server['id']));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('server-'.$server['id']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('服务器 <a href="%s">%s</a> 已经被更新',
		$server['url'], $server['name']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=ServerStatus%2FServer.php', $this->options->adminUrl));
	}

    public function deleteServer()
    {
        $ids = $this->request->filter('int')->getArray('id');
        $deleteCount = 0;
        if ($ids && is_array($ids)) {
            foreach ($ids as $id) {
                if ($this->db->query($this->db->delete($this->prefix.'ServerStatus_server')->where('id = ?', $id))) {
                    $deleteCount ++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('服务器已经删除') : _t('没有服务器被删除'), NULL,
        $deleteCount > 0 ? 'success' : 'notice');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=ServerStatus%2FServer.php', $this->options->adminUrl));
    }

    public function sortServer()
    {
        $servers = $this->request->filter('int')->getArray('id');
        if ($servers && is_array($servers)) {
			foreach ($servers as $sort => $id) {
				$this->db->query($this->db->update($this->prefix.'ServerStatus_server')->rows(array('order' => $sort + 1))->where('id = ?', $id));
			}
        }
    }

	public function action()
	{
		$user = Typecho_Widget::widget('Widget_User');
		$user->pass('administrator');
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is('do=insert'))->insertServer();
		$this->on($this->request->is('do=update'))->updateServer();
		$this->on($this->request->is('do=delete'))->deleteServer();
		$this->on($this->request->is('do=sort'))->sortServer();
		$this->response->redirect($this->options->adminUrl);
	}

    /**
     * BT
     * 获取系统基础统计
     */
    function bt_system($bt_url,$bt_key){
        $url = $bt_url.'/system?action=GetSystemTotal';
        $p_data = $this->bt_token($bt_key);
        $result = $this->bt_get($url,$p_data,60,$bt_url);
        $data = json_decode($result,true);
        return $data;
    }
    /**
     * BT
     * 获取实时状态信息
     */
    function bt_network($bt_url,$bt_key){
        $url = $bt_url.'/system?action=GetNetWork';
        $p_data = $this->bt_token($bt_key);
        $result = $this->bt_get($url,$p_data,60,$bt_url);
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
        $cookie_file = $this->plugin_dir.'log/'.md5($bt_url).'.cookie';
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
            $os = $this->get_oss($_SERVER['HTTP_USER_AGENT']);
            return $os['title'];
        } else {
            return 'Unknown';
        }
    }
    /**
     * 获得访问者浏览器
     */
    function get_browse() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $this->get_browsers($_SERVER['HTTP_USER_AGENT']);
            return $br['title'];
        } else {
            return 'Unknown';
        }
    }
	function get_browsers($ua){
		$title = '非主流浏览器';
		$icon = 'iconfontua icon-globe';
		if(preg_match('/rv:(11.0)/i', $ua, $matches)){
			$title = 'Internet Explorer '. $matches[1];
			$icon = 'iconfontua icon-internet-explorer';//ie11
		}elseif (preg_match('#MSIE ([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Internet Explorer '. $matches[1];
			
			if ( strpos($matches[1], '7') !== false || strpos($matches[1], '8') !== false)
				$icon = 'iconfontua icon-internet-explorer';//ie8
			elseif ( strpos($matches[1], '9') !== false)
				$icon = 'iconfontua icon-internet-explorer';//ie9
			elseif ( strpos($matches[1], '10') !== false)
				$icon = 'iconfontua icon-internet-explorer';//ie10
		}elseif (preg_match('#Edg[A-Za-z]*/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Edge '. $matches[1];
			$icon = 'iconfontua icon-edge';	
		}elseif (preg_match('#TheWorld ([a-zA-Z0-9.]+)#i', $ua, $matches)){
			$title = 'TheWorld(世界之窗) '. $matches[1];
			$icon = 'iconfontua icon-theworld';
		}elseif (preg_match('#JuziBrowser#i', $ua, $matches)){
			$title = 'Juzi(桔子) ';//.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#KBrowser#i', $ua, $matches)){
			$title = 'KBrowser(超快) ';//.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#MyIE#i', $ua, $matches)){
			$title = 'MyIE(蚂蚁) ';//.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#(?:Firefox|Phoenix|Firebird|BonEcho|GranParadiso|Minefield|Iceweasel)/([a-zA-Z0-9.]+)#i', $ua, $matches)){
			$title = 'Firefox '. $matches[1];
			$icon = 'iconfontua icon-firefox';
		}elseif (preg_match('#CriOS/([a-zA-Z0-9.]+)#i', $ua, $matches)){
			$title = 'Chrome for iOS '. $matches[1];
			$icon = 'iconfontua icon-chrome';
		} elseif (preg_match('#(?:LieBaoFast|LBBROWSER)/?([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $title = '猎豹 '. $matches[1];
			$icon = 'iconfontua icon-liebaoliulanqi';
		}elseif (preg_match('#Opera.(.*)Version[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Opera '. $matches[2];
			$icon = 'iconfontua icon-opera';
			if (preg_match('#opera mini#i', $ua)) 
				$title = 'Opera Mini '. $matches[2];
		}elseif (preg_match('#OPR/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Opera '. $matches[1];
			$icon = 'iconfontua icon-opera';
		}elseif (preg_match('#Maxthon( |\/)([a-zA-Z0-9.]+)#i', $ua,$matches)) {
			$title = 'Maxthon(遨游) '. $matches[2];
			$icon = 'iconfontua icon-liulanqi-aoyou';
		}elseif (preg_match('/360/i', $ua, $matches)) {
			$title = '360浏览器';//放弃360怪异UA
			$icon = 'iconfontua icon-browser-360';
			if (preg_match('/Alitephone Browser/i', $ua)) {
				$title = '360极速浏览器';
				$icon = 'iconfontua icon-liulanqi-jisu';
			}
		}elseif (preg_match('#(?:SE |SogouMobileBrowser/)([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '搜狗浏览器 '.$matches[1];
			$icon = 'iconfontua icon-liulanqi-sougou';
		}elseif (preg_match('#QQ/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'QQ '.$matches[1];
			$icon = 'iconfontua icon-qq';
		}elseif (preg_match('#MicroMessenger/([a-zA-Z0-9.]+)#i', $ua,$matches)) {
			$title = '微信 '. $matches[1];
			$icon = 'iconfontua icon-wechat';
		}elseif (preg_match('#QQBrowser/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'QQ浏览器 '.$matches[1];
			$icon = 'iconfontua icon-QQliulanqi';
		}elseif (preg_match('#YYE/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'YY浏览器 '.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#115Browser/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '115 '.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#37abc/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '37abc '.$matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#UCWEB([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'UC '. $matches[1];
			$icon = 'iconfontua icon-ucliulanqi';
		}elseif (preg_match('#UC?Browser/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'UC '. $matches[1];
			$icon = 'iconfontua icon-ucliulanqi';
		}elseif (preg_match('#Quark/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '夸克 '. $matches[1];
			$icon = 'iconfontua icon-kuakeliulanqi';
		}elseif (preg_match('#2345(?:Explorer|Browser)/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '2345浏览器 '. $matches[1];
			$icon = 'iconfontua icon-globe';	
		}elseif (preg_match('#XiaoMi/MiuiBrowser/([0-9.]+)#i', $ua, $matches)) {
			$title = '小米 '. $matches[1];
			$icon = 'iconfontua icon-xiaomi';	
		}elseif (preg_match('#SamsungBrowser/([0-9.]+)#i', $ua, $matches)) {
			$title = '三星 '. $matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('/WeiBo/i', $ua, $matches)) {
			$title = '微博 ';//. $matches[1];
			$icon = 'iconfontua icon-weibo';
		}elseif (preg_match('/BIDU/i', $ua, $matches)) {
			$title = '百度 ';//. $matches[1];
			$icon = 'iconfontua icon-browser-baidu';
		}elseif (preg_match('#baiduboxapp/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '百度 '. $matches[1];
			$icon = 'iconfontua icon-browser-baidu';	
		}elseif (preg_match('#SearchCraft/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '简单搜索 '. $matches[1];
			$icon = 'iconfontua icon-browser-baidu';
		}elseif (preg_match('#Qiyu/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '旗鱼浏览器 '. $matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#mailapp/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '邮箱客户端 '. $matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('/Sleipnir/i', $ua, $matches)) {
			$title = '神马 ';//. $matches[1];
			$icon = 'iconfontua icon-browser-shenma';
		}elseif (preg_match('/MZBrowser/i', $ua, $matches)) {
			$title = '魅族 ';//. $matches[1];
			$icon = 'iconfontua icon-meizu';
		}elseif (preg_match('/VivoBrowser/i', $ua, $matches)) {
			$title = 'ViVO ';//. $matches[1];
			$icon = 'iconfontua icon-VIVO';
		}elseif (preg_match('/mixia/i', $ua, $matches)) {
			$title = '米侠 ';//. $matches[1];
			$icon = 'iconfontua icon-globe';
		}elseif (preg_match('#CoolMarket/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = '酷安 '. $matches[1];//typecho ua获取不完整
			$icon = 'iconfontua icon-coolapk';	
		}elseif (preg_match('#YaBrowser/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Yandex '. $matches[1];
			$icon = 'iconfontua icon-yandex';	
		}elseif (preg_match('#Chrome/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Google Chrome '. $matches[1];
			$icon = 'iconfontua icon-chrome';
		}elseif (preg_match('#Safari/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
			$title = 'Safari '. $matches[1];
			$icon = 'iconfontua icon-safari';
		}
		return array('title' => $title, 'icon' => $icon);
	}
	function get_oss($ua){
		$title = '非主流操作系统';
		$icon = 'iconfontua icon-search';
		if (preg_match('/win/i', $ua)) {
			if (preg_match('/Windows NT 6.1/i', $ua)) {
				$title = "Windows 7";
				$icon = "iconfontua icon-win";
			}elseif (preg_match('/Windows 98/i', $ua)) {
				$title = "Windows 98";
				$icon = "iconfontua icon-win2";
			}elseif (preg_match('/Windows NT 5.0/i', $ua)) {
				$title = "Windows 2000";
				$icon = "iconfontua icon-win2";	
			}elseif (preg_match('/Windows NT 5.1/i', $ua)) {
				$title = "Windows XP";
				$icon = "iconfontua icon-win";
			}elseif (preg_match('/Windows NT 5.2/i', $ua)) {
				if (preg_match('/Win64/i', $ua)) {
					$title = "Windows XP 64 bit";
				} else {
					$title = "Windows Server 2003";
				}
				$icon = 'iconfontua icon-win';
			}elseif (preg_match('/Windows NT 6.0/i', $ua)) {
				$title = "Windows Vista";
				$icon = "iconfontua icon-windows";
			}elseif (preg_match('/Windows NT 6.2/i', $ua)) {
				$title = "Windows 8";
				$icon = "iconfontua icon-win8";
			}elseif (preg_match('/Windows NT 6.3/i', $ua)) {
				$title = "Windows 8.1";
				$icon = "iconfontua icon-win8";
			}elseif (preg_match('/Windows NT 10.0/i', $ua)) {
				$title = "Windows 10";
				$icon = "iconfontua icon-win3";
			}elseif (preg_match('/Windows Phone/i', $ua)) {
				$matches = explode(';',$ua);
				$title = $matches[2];
				$icon = "iconfontua icon-winphone";
			}
		} elseif (preg_match('#iPod.*.CPU.([a-zA-Z0-9.( _)]+)#i', $ua, $matches)) {
			$title = "iPod ".str_replace('_', '.', $matches[1]);
			$icon = "iconfontua icon-ipod";
		} elseif (preg_match('/iPhone OS ([_0-9]+)/i', $ua, $matches)) {
			$title = "iPhone ".str_replace('_', '.', $matches[1]);
			$icon = "iconfontua icon-iphone";
		} elseif (preg_match('/iPad; CPU OS ([_0-9]+)/i', $ua, $matches)) {
			$title = "iPad ".str_replace('_', '.', $matches[1]);
			$icon = "iconfontua icon-ipad";
		} elseif (preg_match('/Mac OS X ([0-9_]+)/i', $ua, $matches)) {
			if (count(explode(7,$matches[1]))>1) $matches[1] = 'Lion '.$matches[1];
			elseif (count(explode(8,$matches[1]))>1) $matches[1] = 'Mountain Lion '.$matches[1];
			$title = "Mac OS X ".str_replace('_', '.', $matches[1]);

			$icon = "iconfontua icon-MacOS";
		} elseif (preg_match('/Macintosh/i', $ua)) {
			$title = "Mac OS";
			$icon = "iconfontua icon-iconmacos";
		} elseif (preg_match('/CrOS/i', $ua)){
			$title = "Google Chrome OS";
			$icon = "iconfontua icon-iconchromeos";
		} elseif (preg_match('/Android.([0-9. _]+)/i',$ua, $matches)) {
				$title= "Android " . $matches[1];
				$icon = "iconfontua icon-android";	
		} elseif (preg_match('/Linux/i', $ua)) {
			$title = 'Linux';
			$icon = 'iconfontua icon-linux';
			if (preg_match('/Ubuntu/i', $ua)) {
				$title = "Ubuntu Linux";
				$icon = "iconfontua icon-ubuntu";
			} elseif (preg_match('#Debian#i', $ua)) {
				$title = "Debian GNU/Linux";
				$icon = "iconfontua icon-debian";
			} elseif (preg_match('#Fedora#i', $ua)) {
				$title = "Fedora Linux";
				$icon = "iconfontua icon-fedora";
			}
		}	
		return array('title' => $title, 'icon' => $icon);
	}
	function strToUtf8($str){
        $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        if($encode == 'UTF-8'){
            return $str;
        }else{
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }
}
