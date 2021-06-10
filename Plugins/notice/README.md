# notice
Typecho提示插件，已将样式改为toastr

## 食用教程
先下载插件放入/usr/plugins/下，改名为notice  
在模板footer.php中的body中加入以下代码
```notice
<!-- 消息提示 -->
<link href="/usr/plugins/notice/toastr.min.css" rel="stylesheet">
<script src="/usr/plugins/notice/toastr.min.js"></script>
<script type="text/javascript">
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
    toastr.info("<?php echo '欢迎来到本站！<br />'; $this->archiveTitle(array(
'post' =>_t('您正在阅读<strong>《 %s 》</strong>'),
'page' =>_t('您正在查看<strong> %s </strong>页面'),
'category'=>_t('您正在查看分类<strong> %s </strong>下的文章'),
'search'=>_t('您正在查看包含关键字<strong> %s </strong>的文章'),
'tag' =>_t('您正在查看标签<strong> %s </strong>下的文章'),
'author'=>_t('您正在查看<strong> %s </strong>的主页')
), '', '');?>");
</script>
```
