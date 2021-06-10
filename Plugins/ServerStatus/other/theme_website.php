<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>WebsiteStatus</title>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/acewfdy/static@latest/System/typecho/plugin/ServerStatus/css/app.css" />
  <script>
    window.Config = {
      SiteName: "WebsiteStatus",
      ShowLink: <?php echo $UptimeLink; ?>,
      ApiKeys: [<?php
$data = ServerStatus_Plugin::GetConfig('UptimeKey');
$data = explode(',',$data);
foreach($data as $value){
	if($value == end($data)){
		echo  '\''.$value.'\'';
	}else{
		echo '\''.$value.'\',';
	}
}
?>],
      CountDays: <?php echo (int)$UptimeDay; ?>,
      Navi: [{}]
    };
  </script>
</head>
<body>
  <div id="app"></div>
</body>
<script src="https://cdn.jsdelivr.net/gh/acewfdy/static@latest/System/typecho/plugin/ServerStatus/js/main.js" type="text/javascript"></script>
</html>