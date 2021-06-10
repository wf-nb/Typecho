<?php
/**
 * 网站状态
 *
 * @version:2.1.0
 * @author Weifeng
 * https://github.com/acewfdy/Typecho
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$options = Typecho_Widget::widget('Widget_Options');
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
             <h1 class="m-n font-thin h3"><i data-feather="server" class="i-sm m-r-sm"></i>网站状态</h1>
             <small class="text-muted letterspacing indexWords"><?php echo $this->fields->intro; ?></small>
         </header>
      <div class="wrapper-md" id="post-panel">
       <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl); ?>
       <!--博客文章样式 begin with .blog-post-->
       <div id="postpage" class="blog-post">
        <article class="panel">
         <div id="post-content" class="wrapper-lg">
           <div class="entry-content l-h-2x">
             <div id="stattip-err" class="alert alert-warning hide" role="alert">
               <b>当前状态：</b>部分服务出现异常。
             </div>
             <div id="stattip-ok" class="alert alert-success hide" role="alert">
               <b>当前状态：</b>所有服务正常运行中，没有发现异常。
             </div>
             <div id="stattip-load" class="alert alert-info" role="alert">
               <b>当前状态：</b>正在检测状态，请稍候...
             </div>
             <div class="table-responsive box-shadow-wrap-lg">
               <table>
                 <thead>
                   <tr id="server-title">
                   </tr>
                 </thead>
                 <tbody id="server-container"></tbody>
				 <tfoot>
                   <tr>
				     <td>
					 注意：
					 </td>
				     <td>
					 数
					 </td>
					 <td>
					 据
					 </td>
					 <td>
					 将
					 </td>
					 <td>
					 在
					 </td>
					 <td>
					 <span id="last-update">5:00</span>
					 </td>
					 <td>
					 后
					 </td>
					 <td>
					 刷
					 </td>
					 <td>
					 新
					 </td>
				   </tr>
				 </tfoot>
               </table>
             </div>
		   </div>
  <script type="template/mustache" id="server-template">
    <tr class="{{alert}}">
      <td rowspan="2" class="sertitle">
        <a class="wname" href="{{url}}" target="_blank">{{friendly_name}}</span>
		<br>
        <span class="label label-status label-{{label}}"><span class="glyphicon glyphicon-{{statusicon}}"></span> {{statustext}}</span>
      </td>
      {{#charts}}
      <td class="center">
      <span class="status-{{uptype}} set-tooltip" data-toggle="tooltip" data-placement="top" title="{{uptimetext}}"><span class="glyphicon glyphicon-{{upsign}}"></span></span>
      </td>
      {{/charts}}
    </tr>
    <tr class="{{alert}} barl">
      <td colspan="8" class="barls">
        <div class="progress progress-xs m-t-sm bg-white">
        {{#progress}}
          <div class="progress-bar progress-bar-{{types}} set-tooltip bg-{{types}}" style="width: {{len}}%" data-toggle="tooltip" data-placement="top" title="{{stattip}}"></div>
        {{/progress}}
       </div>
      </td>
    </tr>
  </script>
  <script src="https://cdn.jsdelivr.net/npm/mustache@4.0.1/mustache.min.js" type="text/javascript"></script>
  <script type="text/javascript">
  // array of Monitor-specific API keys or Main API key to list all monitors
  var __apiKeys = [
<?php
$data = ServerStatus_Plugin::GetConfig('UptimeKey');
$data = explode(',',$data);
foreach($data as $value){
	if($value == end($data)){
		echo  '    \''.$value.'\'';
	}else{
		echo '    \''.$value.'\','.PHP_EOL;
	}
}
?>
  ];
  </script>
  <script src="https://cdn.jsdelivr.net/gh/acewfdy/static/System/typecho/plugin/ServerStatus/js/cup.js" type="text/javascript"></script>
  <script type="text/javascript">
    jQuery(document).ready(WebsiteStatus.dashboard.init);
  </script>
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