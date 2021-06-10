<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>ServerStatus</title>
  <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/acewfdy/static/Image/20200324105322.png" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-lg-10 col-lg-offset-1 text-center">
<?php
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
?>
      <div class="panel panel-info">
        <div class="panel-heading"><?php echo $server['name'];?></div>
        <ul class="list-group">
          <li class="list-group-item">ID：<?php echo $server['id'];?></li>
          <li class="list-group-item">排序：<?php echo $server['order'];?></li>
          <li class="list-group-item">类型：<?php echo $server['type_cn'];?></li>
          <li class="list-group-item">介绍：<br><?php echo $server['desc'];?></li>
        </ul>
        <div class="panel-footer"><a class="btn btn-success" href="?id=<?php echo $server['id'];?>">查看状态</a></div>
      </div>
<?php
}
?>
      </div>
    </div>
  </div>
  
</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
</html>