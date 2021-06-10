<?php
/**
 * 一款消息通知插件，判断来路地址输出欢迎消息。
 * 
 * @package notice
 * @author jrotty
 * @edit by Weifeng
 * @version 0.4
 * @link http://qqdie.com
 */

class notice_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
        Typecho_Plugin::factory('Widget_Archive')->footer = array('notice_Plugin', 'main');
    }

	/* 禁用插件方法 */
	public static function deactivate(){}

	/* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form){}

	/* 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	/* 插件实现方法 */
    public static function main(){
        $plugin_url = Helper::options()->pluginUrl;
        echo '<script src="'.$plugin_url.'/notice/toastr.min.js"></script>
<link href="'.$plugin_url.'/notice/toastr.min.css" rel="stylesheet">';
        include('notice.php');
    }
}
