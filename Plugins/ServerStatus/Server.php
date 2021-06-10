<?php
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=ServerStatus%2FServer.php'); ?>">服务器管理</a></li>
						<li><a href=<?php $options->adminUrl('options-plugin.php?config=ServerStatus'); ?>>插件设置</a></li>
                        <li><a href="https://wfblog.net/" target="_blank">作者博客</a></li>
                    </ul>
                </div>

                <div class="col-mb-12 col-tb-8" role="main">                  
                    <?php
						$prefix = $db->getPrefix();
						$servers = $db->fetchAll($db->select()->from($prefix.'ServerStatus_server')->order($prefix.'ServerStatus_server.order', Typecho_Db::SORT_ASC));
                    ?>
                    <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only">全选</i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only">操作</i>选中项 <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="你确认要删除这些服务器吗?" href="<?php $options->index('/action/ServerStatus_Manage?do=delete'); ?>">删除</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="10%"/>
								<col width="20%"/>
								<col width="20%"/>
								<col width="20%"/>
								<col width="10%"/>
								<col width="20%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th> </th>
									<th>名称</th>
									<th>标识</th>
									<th>类型</th>
									<th>刷新</th>
									<th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
								<?php if(!empty($servers)): $alt = 0;?>
								<?php foreach ($servers as $server): ?>
                                <tr id="id-<?php echo $server['id']; ?>">
                                    <td><input type="checkbox" value="<?php echo $server['id']; ?>" name="id[]"/><?php echo $server['id']; ?></td>
									<td><a href="<?php echo $request->makeUriByRequest('id=' . $server['id']); ?>" title="点击编辑"><?php echo $server['name']; ?></a>
									<td><?php echo $server['sign']; ?></td>
									<td><?php
                                        if ($server['type'] == "default"){
                                            echo "Nginx/Apache";
                                        }else if ($server['type']== "winbt"){
                                            echo "Win版宝塔";
                                        }elseif ($server['type'] == "linuxbt"){
                                            echo "Linux版宝塔";
                                        }else{
                                            echo "未知";
                                        } ?></td>
									<td><?php echo $server['ajax']; ?>s</td>
									<td><a<?php if($server['type'] == "default" || $server['type'] == "winbt"){ ?> href="<?php $options->index('/ServerStatus/Getfile?id='.$server['id']); ?>"<?php } ?> title="下载文件">下载</a> | <a onclick="Check('<?php echo $server['id']; ?>');" title="检测通讯">检测</a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5"><h6 class="typecho-list-table-title">没有任何服务器</h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
				</div>
                <div class="col-mb-12 col-tb-4" role="form">
                    <?php ServerStatus_Plugin::form()->render(); ?>
                </div>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
window.jQuery || document.write("<script src=\"https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js\"><\/script>");

(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table').tableDnD({
            onDrop : function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $options->index('/action/ServerStatus_Manage?do=sort'); ?>', 
                    $.param({id : ids}));

                $('tr', table).each(function (i) {
                    if (i % 2) {
                        $(this).addClass('even');
                    } else {
                        $(this).removeClass('even');
                    }
                });
            }
        });

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        <?php if (isset($request->id)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
function Check(id){
	Swal.fire({allowOutsideClick:false,imageUrl:'https://ae01.alicdn.com/kf/U05ed7e65d7a749a29bd6164f9d4abe84w.gif',title:"与服务器通讯中...",showConfirmButton:false,timer:0});
    $.getJSON("/ServerStatus/Check?id="+id,function(result){
		swal.close();
        if (result.code == 200) {
			result.text = "服务器ID："+id+"<br />状态码：200<br />"+result.msg;
			Swal.fire({allowOutsideClick:false,icon:'success',type:'success',title:"成功",html:result.text,showConfirmButton:true,timer:0});
        }else{
			result.text = "服务器ID："+id+"<br />状态码：false<br />"+result.msg;
			Swal.fire({allowOutsideClick:false,icon:'error',type:'error',title:"失败",html:result.text,showConfirmButton:true,timer:0});
        }
    });
}
</script>
<?php include 'footer.php'; ?>