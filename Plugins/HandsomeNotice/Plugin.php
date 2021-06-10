<?php
/**
 * Handsome专用消息提示插件
 * @package HandsomeNotice
 * @author jrotty,Weifeng
 * @version 1.0
 * @link https://wfblog.net
 */

class HandsomeNotice_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
        Typecho_Plugin::factory('Widget_Archive')->footer = array('HandsomeNotice_Plugin', 'footer');
        return _t('插件已启用');
    }

	/* 禁用插件方法 */
	public static function deactivate()
	{
        return _t('插件已禁用');
	}

	/* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form){}

	/* 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	/* 插件实现方法 */
    /**
     * 为header引入文件
     * @return void
     */
    public static function header(){}
    
    /**
     * 为footer引入文件
     * @return void
     */
    public static function footer()
	{
        $referer = $_SERVER["HTTP_REFERER"];
        $refererhost = parse_url($referer);
        $host = strtolower($refererhost['host']);
        $ben=$_SERVER['HTTP_HOST'];

        $callback = "Hello！欢迎来自<strong>".$host."</strong>的朋友！";
        if($referer == ""||$referer == null){
            if(!Typecho_Cookie::get('firstView')){
                Typecho_Cookie::set('firstView', '1', 0, Helper::options()->siteUrl);
                $callback = "欢迎您访问我的博客~  我倍感荣幸啊 嘿嘿";
            }else{
                $callback = "您直接访问了本站!  莫非您记住了我的<strong>域名</strong>.厉害~  我倍感荣幸啊 嘿嘿";
            }
        }elseif(strstr($ben,$host)){ 
            $callback ="host"; 
        }elseif (preg_match('/baiducontent.*/i', $host)){
            $callback = '您通过 <strong>百度快照</strong> 找到了我，厉害！';
        }elseif(preg_match('/baidu.*/i', $host)){
            $callback = '您通过 <strong>百度</strong> 找到了我，厉害！';
        }elseif(preg_match('/so.*/i', $host)){
            $callback = '您通过 <strong>好搜</strong> 找到了我，厉害！';
        }elseif(!preg_match('/www\.google\.com\/reader/i', $referer) && preg_match('/google\./i', $referer)) {
            $callback = '您居然通过 <strong>Google</strong> 找到了我! 一定是个技术宅吧!';
        }elseif(preg_match('/search\.yahoo.*/i', $referer) || preg_match('/yahoo.cn/i', $referer)){
            $callback = '您通过 <strong>Yahoo</strong> 找到了我! 厉害！'; 
        }elseif(preg_match('/cn\.bing\.com\.*/i', $referer) || preg_match('/yahoo.cn/i', $referer)){
            $callback = '您通过 <strong>Bing</strong> 找到了我! 厉害！';
        }elseif(preg_match('/google\.com\/reader/i', $referer)){
            $callback = "感谢你通过 <strong>Google</strong> 订阅我!  既然过来读原文了. 欢迎留言指导啊.嘿嘿 ^_^";
        } elseif (preg_match('/xianguo\.com\/reader/i', $referer)) {
            $callback = "感谢你通过 <strong>鲜果</strong> 订阅我!  既然过来读原文了. 欢迎留言指导啊.嘿嘿 ^_^";
        } elseif (preg_match('/zhuaxia\.com/i', $referer)) {
            $callback = "感谢你通过 <strong>抓虾</strong> 订阅我!  既然过来读原文了. 欢迎留言指导啊.嘿嘿 ^_^";
        } elseif (preg_match('/inezha\.com/i', $referer)) {
            $callback = "感谢你通过 <strong>哪吒</strong> 订阅我!  既然过来读原文了. 欢迎留言指导啊.嘿嘿 ^_^";
        } elseif (preg_match('/reader\.youdao/i', $referer)) {
            $callback = "感谢你通过 <strong>有道</strong> 订阅我!  既然过来读原文了. 欢迎留言指导啊.嘿嘿 ^_^";
        }
        if( $callback != "host"){//排除本地访问
	        echo "
<script type=\"text/javascript\">
    function notice(){
		$.message({
    	    title: '页面加载完毕',
    	    message: '{$callback}',
			type: 'success',
			autoHide: !1,
			time: '5000'
    	});
	};
	$(function(){
		notice();
	});
</script>
";
        }
    }
}
?>