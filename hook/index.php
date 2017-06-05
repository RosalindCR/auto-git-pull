<?php #!/usr/bin/env /usr/bin/php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	set_time_limit(0);

    $wwwUser = 'www';
    //$wwwGroup = 'www';
	
	$ip = '127.0.0.1';
	$json = json_decode(file_get_contents('php://input'),true);
	$repo = $json['repository']['name'];

	if(empty($repo) || is_null($repo))
	{
		exit('Error, missing repo!');
	}
	$repo = strtolower($repo);	//AMH网站名称统一用小写，将Git项目的大写字母转换成小写
	$target = "/home/wwwroot/$repo/web"; // 网站web目录
/*	
    $token = '填写git服务器hook令牌';
    if (empty($json['token']) || $json['token'] !== $token) {
        exit("Error, missing token!");
    }
*/


    //$cmd = "sudo -Hu www cd $target && git pull";
	//    $output = shell_exec($cmd);
	$cmd = "sudo -Hu $wwwUser git --git-dir=$target/.git --work-tree=$target pull";
	exec($cmd, $output, $result);
	$output = json_encode($output);//json格式化


	if($_SERVER['HTTP_CLIENT_IP']){$ip=$_SERVER['HTTP_CLIENT_IP'];}
		elseif($_SERVER['HTTP_X_FORWARDED_FOR']){$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];}
			else{$ip=$_SERVER['REMOTE_ADDR'];}
	//	echo "$ip";//获取git服务器推送IP

	$logfilename = "/home/wwwroot/index/log/deploy-$repo-".date("Y-m-d").".log";
	$logtime = date("Y-m-d H:i:s");

	//文本换行符\r\n制表符\t必须要用双引号输出，单引号输出无效
	$logdata=$logtime."\t[info]\t{\"time\":\"$logtime\",\"ip\":\"$ip\",\"exec\":\"$cmd\",\"result\":\"$result\",\"output\":$output}\r\n";
	//"a+"	读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
	if( ($TxtRes=fopen ($logfilename,"a+")) === FALSE){
		echo("create ".$logfilename." false!\r\n");
		exit();
	}
	if(!fwrite ($TxtRes,$logdata)){ //将信息写入文件
		echo ($logfilename." write false！\r\n");
		fclose($TxtRes);
		exit();
	}
	echo ($logfilename." write success！\r\n");
	fclose ($TxtRes); //关闭指针			

