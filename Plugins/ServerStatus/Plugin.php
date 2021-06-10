<?php
/**
 * <strong style="color:#B0E2FF;font-family: 楷体;">Typecho服务器状态插件</strong>
 * 
 * @package ServerStatus
 * @author <strong style="color:#B0E2FF;font-family: 楷体;">Weifeng</strong>
 * @version <strong style="color:#B0E2FF;font-family: 楷体;">2.1.0</strong>
 * @update: 2020-07-31
 * @link https://wfblog.net/
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class ServerStatus_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 * 
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function activate()
	{
		$logDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/ServerStatus/log';
		$otherDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/ServerStatus/other';
		$themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
		if (!is_dir($logDir) and !@mkdir($logDir)){
			throw new Typecho_Plugin_Exception('无法创建临时目录.');
		}
		if(!self::testwrite($logDir)){
			throw new Typecho_Plugin_Exception('log目录没有写入的权限');
		}
		if(is_dir($themeDir.'/handsome')){
			if(!copy($otherDir.'/server.php',$themeDir.'/handsome/server.php')){
				throw new Typecho_Plugin_Exception('插件权限不足，请给予足够的权限！');
			}
			if(!copy($otherDir.'/website.php',$themeDir.'/handsome/website.php')){
				throw new Typecho_Plugin_Exception('插件权限不足，请给予足够的权限！');
			}
		}
		$msg = ServerStatus_Plugin::install();
		Typecho_Plugin::factory('Widget_Archive') ->header = array('ServerStatus_Plugin', 'header');
		Typecho_Plugin::factory('Widget_Archive') ->footer = array('ServerStatus_Plugin', 'footer');
		Typecho_Plugin::factory('admin/footer.php')->end = array('ServerStatus_Plugin', 'adminfooter');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('ServerStatus_Plugin', 'parse');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('ServerStatus_Plugin', 'parse');
		Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = array('ServerStatus_Plugin', 'parse');
		Helper::addPanel(1, 'ServerStatus/Server.php', 'ServerStatus', 'ServerStatus', 'administrator');
		Helper::addPanel(3, 'ServerStatus/Server.php', 'ServerStatus', 'ServerStatus', 'administrator');
		Helper::addAction('ServerStatus_Manage', 'ServerStatus_Action');
		Helper::addRoute('ServerStatus_Check', '/ServerStatus/Check', 'ServerStatus_Action', 'Check');
		Helper::addRoute('ServerStatus_IPInfo', '/ServerStatus/IPInfo', 'ServerStatus_Action', 'IPInfo');
		Helper::addRoute('ServerStatus_Status', '/ServerStatus/Status', 'ServerStatus_Action', 'Status');
		Helper::addRoute('ServerStatus_Getfile', '/ServerStatus/Getfile', 'ServerStatus_Action', 'Getfile');
		Helper::addRoute('ServerStatus_Server', '/ServerStatus/Server', 'ServerStatus_Action', 'ServerIframe');
		Helper::addRoute('ServerStatus_Website', '/ServerStatus/Website', 'ServerStatus_Action', 'WebsiteIframe');
		return _t($msg);
	}
	
	/**
	 * 禁用插件方法,如果禁用失败,直接抛出异常
	 * 
	 * @static
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function deactivate()
	{
		$otherDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/ServerStatus/other';
		$themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
		if(is_dir($themeDir.'/handsome')){
			if(file_exists($themeDir.'/handsome/server.php')){
				if(!unlink($themeDir.'/handsome/server.php')){
					throw new Typecho_Plugin_Exception('插件权限不足，请给予足够的权限！');
				}
			}
			if(file_exists($themeDir.'/handsome/website.php')){
				if(!unlink($themeDir.'/handsome/website.php')){
					throw new Typecho_Plugin_Exception('插件权限不足，请给予足够的权限！');
				}
			}
		}
		Helper::removeRoute("ServerStatus_Check");
		Helper::removeRoute("ServerStatus_IPInfo");
		Helper::removeRoute("ServerStatus_Status");
		Helper::removeRoute("ServerStatus_Server");
		Helper::removeRoute("ServerStatus_Website");
		Helper::removeRoute("ServerStatus_Getfile");
		Helper::removeAction('ServerStatus_Manage');
		Helper::removePanel(1, 'ServerStatus/Server.php');
		Helper::removePanel(3, 'ServerStatus/Server.php');
	}
	
	/**
	 * 获取插件配置面板
	 * 
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form 配置面板
	 * @return void
	 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$options = Typecho_Widget::widget('Widget_Options');
		echo '<div class="col-mb-12">
					<ul class="typecho-option-tabs clearfix">
						<li><a href="'.$options->adminUrl.'extending.php?panel=ServerStatus%2FServer.php">服务器管理</a></li>
						<li class="current"><a href="'.$options->adminUrl.'options-plugin.php?config=ServerStatus">插件设置</a></li>
						<li><a href="https://wfblog.net/" target="_blank">作者博客</a></li>
					</ul>
				</div>';
		echo "<h2>感谢使用ServerStatus插件，本插件由<a href='https://wfblog.net/'>Weifeng</a>编写</h2>";
		$SweetAlert = new Typecho_Widget_Helper_Form_Element_Radio(
			'SweetAlert', array(
				'0' => '未引入',
				'1' => '已引入',
			), '0', 'SweetAlert', '是否已经引入SweetAlert组件，如果已经引入，请选择后者，不然会报错哦~');
		$IPApi = new Typecho_Widget_Helper_Form_Element_Select(
			'IPApi', array(
				'IP.SB' => '<a href="https://ip.sb/">IP.SB</a>--Json',
				'IP-API' => '<a href="https://ip-api.com/">IP-API</a>--Json',
				'PConline' => '<a href="https://www.pconline.com.cn/">PConline</a>--Json',
				'IW3C' => '<a href="https://www.iw3c.com.cn/">IW3C</a>--Json',
				'SOHU' => '<a href="https://pv.sohu.com/cityjson?ie=utf-8">SOHU</a>--Js',
			), 'IP.SB', 'IPApi', '获取IP信息的API，默认为IP.SB，四个API各有优劣（IP.SB支持IPV6但国内通讯速度较慢，IP-API信息全但国内通讯速度较慢，PConline获取速度快但国外信息不全，IW3C速度快但是是小厂，SOHU是JS不需要通过服务器获取但信息不全）');
		$UptimeKey = new Typecho_Widget_Helper_Form_Element_Textarea('UptimeKey', NULL, 'ur567798-9efa447ca49c9e64e1f3f984', 'UptimeRobot Key', '每个Key之间以英文半角逗号隔开');
		$UptimeLink = new Typecho_Widget_Helper_Form_Element_Radio(
			'UptimeLink', array(
				'true' => '是',
				'false' => '否',
			), 'true', 'UptimeRobot Link', '是否展示监控网站的链接');
		$UptimeDay = new Typecho_Widget_Helper_Form_Element_Text('UptimeDay', NULL, '60', 'UptimeRobot Day', '日志天数。可选范围1~90，默认60天');
		$form->addInput($SweetAlert);
		$form->addInput($IPApi);
		$form->addInput($UptimeKey);
		$form->addInput($UptimeLink);
		$form->addInput($UptimeDay);
	}

	/**
	 * 个人用户的配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	/**
	 * 初始化以及升级插件数据库，如初始化失败,直接抛出异常
	 *
	 * @access public
	 * @return string
	 * @throws Typecho_Plugin_Exception
	 */
	public static function install()
	{
		if (substr(trim(dirname(__FILE__), '/'), -12) != 'ServerStatus') {
			throw new Typecho_Plugin_Exception(_t('插件目录名必须为ServerStatus'));
		}
		$db = Typecho_Db::get();
		$adapterName = $db->getAdapterName();
		if (strpos($adapterName, 'Mysql') !== false) {
			$prefix  = $db->getPrefix();
			$sqlrows = file_get_contents('usr/plugins/ServerStatus/other/mysql.sql');
			$sqlrows = str_replace('typecho_', $prefix, $sqlrows);
			$sqlrows = str_replace('%charset%', 'utf8', $sqlrows);
			$sqlrows = explode(';', $sqlrows);
			try {
				$configLink = '<a href="' . Helper::options()->adminUrl . 'options-plugin.php?config=ServerStatus">' . _t('前往设置') . '</a>';
				# 初始化数据库如果不存在
				if (!$db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}ServerStatus_server';", Typecho_Db::READ))) {
					foreach ($sqlrows as $sqlrow) {
						$sqlrow = trim($sqlrow);
						if ($sqlrow) {
							$db->query($sqlrow, Typecho_Db::WRITE);
						}
					}
					$msg = _t('成功创建数据表，ServerStatus插件启用成功，') . $configLink;
				} else {
					$dbrows = $db->fetchAll($db->select()->from($prefix.'ServerStatus_server')->order($prefix.'ServerStatus_server.id', Typecho_Db::SORT_ASC));
					if ($db->query("DROP TABLE IF EXISTS {$prefix}ServerStatus_server;", Typecho_Db::WRITE)) {
						foreach ($sqlrows as $sqlrow) {
							$sqlrow = trim($sqlrow);
							if ($sqlrow) {
								$db->query($sqlrow, Typecho_Db::WRITE);
							}
						}
						foreach ($dbrows as $dbrow) {
							if ($dbrow) {
								unset($dbrow['id']);
								unset($dbrow['order']);
								$dbrow['order'] = $db->fetchObject($db->select(array('MAX(order)' => 'maxOrder'))->from($prefix.'ServerStatus_server'))->maxOrder + 1;
								$db->query($db->insert($prefix.'ServerStatus_server')->rows($dbrow));
							}
						}
						$msg = _t('增量更新数据库成功，ServerStatus插件启用成功。');
						return $msg;
					} else {
						throw new Typecho_Plugin_Exception(_t('增量更新数据库失败，ServerStatus插件启用失败，错误信息：数据库%sServerStatus_server表删除失败。', $prefix));
					}
				}
			} catch (Typecho_Db_Exception $e) {
				throw new Typecho_Plugin_Exception(_t('数据表建立失败，ServerStatus插件启用失败，错误信息：%s。', $e->getMessage()));
			} catch (Exception $e) {
				throw new Typecho_Plugin_Exception($e->getMessage());
			}
		} else {
			throw new Typecho_Plugin_Exception(_t('你的适配器为%s，目前ServerStatus插件仅支持Mysql', $adapterName));
		}
	}

	/**
	 * 为header引入文件
	 * @return void
	 */
	public static function header()
	{
		$options = Typecho_Widget::widget('Widget_Options');
		if(!empty($options->plugin('ServerStatus')->UptimeKey) || $options->plugin('ServerStatus')->SweetAlert != 1){
			echo "\n";
			echo '<!-- ServerStatus Plugin Of Typecho -->';
			echo "\n";
			if($options->plugin('ServerStatus')->SweetAlert != 1){
				echo "";
			}
			if(!empty($options->plugin('ServerStatus')->UptimeKey)){
				echo <<<EOF
<script type="text/javascript">
  var WebsiteStatus = null;
  // array of Monitor-specific API keys or Main API key to list all monitors
  var __apiKeys = [

EOF;
				$data = $options->plugin('ServerStatus')->UptimeKey;
				$data = explode(',',$data);
				foreach($data as $value){
					if($value == end($data)){
						echo  '	\''.$value.'\'';
					}else{
						echo '	\''.$value.'\','.PHP_EOL;
					}
				}
				echo <<<EOF

  ];
</script>
EOF;
				echo "\n";
			}
			echo '<!-- ServerStatus Plugin Of Typecho -->';
			echo "\n";
		}
	}
	
	/**
	 * 为footer引入文件
	 * @return void
	 */
	public static function footer(){
		$options = Typecho_Widget::widget('Widget_Options');
		echo "\n";
		echo '<!-- ServerStatus Plugin Of Typecho -->';
		echo "\n";
		if($options->plugin('ServerStatus')->SweetAlert != 1){
			echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@8\"></script>\n";
		}
		if($options->plugin('ServerStatus')->IPApi == 'SOHU'){
			echo "<script src=\"https://pv.sohu.com/cityjson?ie=utf-8\"></script>\n";
		}
		if(!empty($options->plugin('ServerStatus')->UptimeKey)){
			echo "<script src=\"https://cdn.jsdelivr.net/npm/mustache@4.0.1/mustache.min.js\"></script>\n";
			echo "<script src=\"https://cdn.jsdelivr.net/gh/acewfdy/static@latest/System/typecho/plugin/ServerStatus/js/cup.js\"></script>\n";
		}
		// 插入控制台标识
		echo '<script>console.log("\n %c ServerStatus Plugin Of Typecho By Weifeng ","color:#fff; background: linear-gradient(to right , #7A88FF, #d27aff); padding:5px; border-radius: 10px;");console.log("\n %c 插件：https://wfblog.net/archives/serverstatus_plugin.html","color:#fff; background: linear-gradient(to right , #7A88FF, #d27aff); padding:5px; border-radius: 10px;"); </script>';
		echo "\n";
		echo '<!-- ServerStatus Plugin Of Typecho -->';
		echo "\n";
	}

	/**
	 * 后台footer
	 * @return void
	 */
	public static function adminfooter()
	{
		$url = $_SERVER['PHP_SELF'];
		$filename = substr($url, strrpos($url, '/') + 1);
		if ($filename == 'index.php') {
			echo '<script>$(document).ready(function() {$("#start-link").append("<li><a href=\"';
			Helper::options()->adminUrl('extending.php?panel=ServerStatus/Server.php');
			echo '\">ServerStatus</a></li>");});</script>';
		}
	}

	/**
	 * 判断目录是否可写
	 * @return true or false
	 */
	public static function testWrite($dir) {
		$testFile = "_test.txt";
		$fp = @fopen($dir . "/" . $testFile, "w");
		if (!$fp) {
			return false;
		}
		fclose($fp);
		$rs = @unlink($dir . "/" . $testFile);
		if ($rs) {
			return true;
		}
		return false;
	}

	/**
	 * 管理服务器表单
	 * @return void
	 */
	public static function form($action = NULL)
	{
		/** 构建表格 */
		$options = Typecho_Widget::widget('Widget_Options');
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/ServerStatus_Manage', $options->index),
		Typecho_Widget_Helper_Form::POST_METHOD);

		/** 服务器名称 */
		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('服务器名称*'));
		$form->addInput($name);

		/** 服务器标识 */
		$sign = new Typecho_Widget_Helper_Form_Element_Text('sign', NULL, NULL, _t('服务器标识*'));
		$form->addInput($sign);

		/** 服务器类型 */
		$type = new Typecho_Widget_Helper_Form_Element_Select('type', array(
			'default'=>'Nginx/Apache',
			'winbt'=>'Windows版宝塔',
			'linuxbt'=>'Linux版宝塔'
		),'default', _t('服务器类型*'), '选择服务器的类型（按照实际情况选择，通用脚本信息较不准）');
		$form->addInput($type);

		/** 服务器通讯地址 */
		$url = new Typecho_Widget_Helper_Form_Element_Text('url', NULL, "http://", _t('通讯地址*'), _t('Linux版宝塔就填形如http://xxx:8888/的网址<br>Windows版宝塔和Nginx/Apache填写服务器使用插件目录下ServerStatus.php文件的网址'));
		$form->addInput($url);

		/** 服务器通讯密匙 */
		$key = new Typecho_Widget_Helper_Form_Element_Text('key', NULL, "WeifengNB", _t('通讯密匙*'),  _t('宝塔填写API密匙，其他没有就乱填'));
		$form->addInput($key);

		/** 刷新间隔 */
		$ajax =  new Typecho_Widget_Helper_Form_Element_Text('ajax', NULL, "10", _t('刷新间隔*'),"每次刷新数据之间的间隔时间（单位：s），推荐10");
		$form->addInput($ajax);

		/** 服务器描述 */
		$desc =  new Typecho_Widget_Helper_Form_Element_Textarea('desc', NULL, "位置：XXX<br />\n防御：XXX<br />\n带宽：XXX", _t('服务器描述*'),"服务器简单介绍（输入<code style=\"padding: 2px 4px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;\">&lt;br&gt;</code>换行）");
		$form->addInput($desc);

		/** 服务器动作 */
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);

		/** 服务器主键 */
		$id = new Typecho_Widget_Helper_Form_Element_Hidden('id');
		$form->addInput($id);

		/** 提交按钮 */
		$submit = new Typecho_Widget_Helper_Form_Element_Submit();
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);
		$request = Typecho_Request::getInstance();

		if (isset($request->id) && 'insert' != $action) {
			/** 更新模式 */
			$db = Typecho_Db::get();
			$prefix = $db->getPrefix();
			$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $request->id));
			if (!$server) {
				throw new Typecho_Widget_Exception(_t('服务器不存在'), 404);
			}
			
			$name->value($server['name']);
			$sign->value($server['sign']);
			$type->value($server['type']);
			$url->value($server['url']);
			$key->value($server['key']);
			$ajax->value($server['ajax']);
			$desc->value($server['desc']);
			$do->value('update');
			$id->value($server['id']);
			$submit->value(_t('编辑服务器'));
			$_action = 'update';
		} else {
			$do->value('insert');
			$submit->value(_t('增加服务器'));
			$_action = 'insert';
		}
		
		if (empty($action)) {
			$action = $_action;
		}

		/** 给表单增加规则 */
		if ('insert' == $action || 'update' == $action) {
			$name->addRule('required', _t('必须填写服务器名称'));
			$sign->addRule('required', _t('必须填写服务器标识'));
			$type->addRule('required', _t('必须选择服务器类型'));
			$ajax->addRule('required', _t('必须填写刷新间隔时间'));
			$key->addRule('required', _t('必须填写通讯密匙'));
			$desc->addRule('required', _t('必须填写服务器介绍'));
			$url->addRule('required', _t('必须填写通讯地址'));
			$url->addRule('url', _t('不是一个合法的通讯地址'));
		}
		if ('update' == $action) {
			$id->addRule('required', _t('服务器主键不存在'));
			$id->addRule(array(new ServerStatus_Plugin, 'ServerExists'), _t('服务器不存在'));
		}
		return $form;
	}

	public static function ServerExists($id)
	{
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $id)->limit(1));
		return $server ? true : false;
	}

	/**
	 * 控制输出格式
	 */
	public static function output_str($pattern=NULL, $num=0, $type=NULL)
	{
		$options = Typecho_Widget::widget('Widget_Options');
		if (!isset($options->plugins['activated']['ServerStatus'])) {
			return 'ServerStatus插件未激活';
		}
		if (!isset($pattern) || $pattern == "" || $pattern == NULL || $pattern == "SHOW_TEXT" || $pattern == "SHOW_MAIN") {
			$pattern = "<li><a href=\"?id={id}\" title=\"{desc}\" target=\"_self\">{name}</a></li>\n";
		} else if ($pattern == "SHOW_ALL") {
			$pattern = "<li><a href=\"?id={id}\" title=\"{desc}\" target=\"_self\">{name}</a> 标识：{sign} 类型：{type}</li>\n";
		}
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$sql = $db->select()->from($prefix.'ServerStatus_server');
		if (!isset($type) || $type == "") {
			$type = NULL;
		}
		if ($type) {
			$sql = $sql->where('type=?', $type);
		}
		$sql = $sql->order($prefix.'ServerStatus_server.order', Typecho_Db::SORT_ASC);
		$num = intval($num);
		if ($num > 0) {
			$sql = $sql->limit($num);
		}
		$servers = $db->fetchAll($sql);
		$str = "";
		foreach ($servers as $server) {
			if ($server['desc'] == ""){
				$server['desc'] = "一个未知的服务器";
			}
			if ($server['type'] == "default"){
				$server['type_cn'] = "Nginx/Apache";
			}else if ($server['type']== "winbt"){
				$server['type_cn'] = "Win版宝塔";
			}elseif ($server['type'] == "linuxbt"){
				$server['type_cn'] = "Linux版宝塔";
			}else{
				$server['type_cn'] = "未知";
			}
			$str .= str_replace(
				array('{id}', '{name}', '{sign}', '{type}', '{type_cn}', '{desc}', '{order}'),
				array($server['id'], $server['name'], $server['sign'], $server['type'], $server['type_cn'], $server['desc'], $server['order']),
				$pattern
			);
		}
		return $str;
	}

	//输出
	public static function output($pattern=NULL, $num=0, $type=NULL)
	{
		echo ServerStatus_Plugin::output_str($pattern, $num, $type);
	}

	public static function info($id=null,$echo=null)
	{
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		if(empty($id)){
			$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->limit(1));
		}else{
			$server = $db->fetchRow($db->select()->from($prefix.'ServerStatus_server')->where('id = ?', $id)->limit(1));
		}
		if($echo == 'json'){
			$data = json_encode($server,JSON_UNESCAPED_UNICODE);
		}else{
			$data = $server;
		}
		return $data;
	}

	/**
	 * 解析
	 * 
	 * @access public
	 * @param array $matches 解析值
	 * @return string
	 */
	public static function parseCallback($matches)
	{
		$db = Typecho_Db::get();
		$pattern = $matches[3];
		$num = $matches[1];
		$type = $matches[2];
		if($type == 'info' && !empty($num)){
			return ServerStatus_Plugin::info($num);
		}else{
			return ServerStatus_Plugin::output_str($pattern, $num, $type);
		}
	}

	public static function parse($text, $widget, $lastResult)
	{
		$text = empty($lastResult) ? $text : $lastResult;
		if ($widget instanceof Widget_Archive || $widget instanceof Widget_Abstract_Comments) {
			return preg_replace_callback("/<ServerStatus\s*(\d*)\s*(\w*)>\s*(.*?)\s*<\/ServerStatus>/is", array('ServerStatus_Plugin', 'parseCallback'), $text);
		} else {
			return $text;
		}
	}

	/**
	 * 获取服务器数量
	 */
	public static function GetCount()
	{
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$count = count($db->fetchAll($db->select()->from($prefix.'ServerStatus_server')));
		return $count;
	}

	/**
	 * 获取设置内容
	 */
	public static function GetConfig($key='UptimeKey')
	{
		$options = Typecho_Widget::widget('Widget_Options');
		if (!isset($options->plugins['activated']['ServerStatus'])) {
			return 'ServerStatus插件未激活';
		}
		return $options->plugin('ServerStatus')->$key;
	}
}
?>