<?php
function notice_main(){
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
	    echo '<script>
    toastr.options = {
        closeButton: true, //是否显示关闭按钮
        debug: false, //是否使用debug模式
        progressBar: false,
        positionClass: "toast-top-right", //弹出窗的位置
        onclick: null,
        showDuration: "300", //显示的动画时间
        hideDuration: "1000", //消失的动画时间
        timeOut: "5000", //展现时间
        extendedTimeOut: "1000", //加长展示时间
        showEasing: "swing", //显示时的动画缓冲方式
        hideEasing: "linear", //消失时的动画缓冲方式
        showMethod: "fadeIn", //显示时的动画方式
        hideMethod: "fadeOut" //消失时的动画方式
    };
    toastr.info("'.$callback.'");
</script>';
    }
}

notice_main();
?>
