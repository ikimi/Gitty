<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
</head>
<body>
<?php
echo $remind;
if($cover)
{
	echo '<input type="button" value="确定" onclick="window.location=\''.site_url('install/action').'\'"';
//	echo '<input type="button" value="asdf" onclick="window.location=\'http://baidu.com\'">';
}
?>
</body>
</html>
