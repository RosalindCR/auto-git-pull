# auto-git-pull
利用WebHook实现PHP自动部署Git代码

## 一、为AMH设置Git自动部署
----------
注意：本项目以AMH服务器作web运行环境做配置，其它运行环境的请参照修改！
为方便统一管理，现作两点规范：

	1. 在amh上新建的网站名称统一用小写字母表示（即：虚拟主机》主标识域名）
	2. git项目名称与amh网站名称一致，否则会部署失败

----------

### 1. 生成公钥

公钥有两个：1. git用户公钥，2. 部署公钥：

git用户公钥

    ssh-keygen -t rsa -C "amh@test.com"
    # 然后一直回车就行
    # 生成的文件通常是 /root/.ssh/id_rsa，如果非root用户请查看提示上的路径

部署公钥

	sudo -Hu www ssh-keygen -t rsa 
	# 请选择 “no passphrase”，一直回车下去

### 2. 准备钩子文件
复制项目文件到/home/wwwroot/index/web目录

	cd /home/wwwroot/index/web
	git clone https://github.com/sujianchao/auto-git-pull.git

确保你的hook文件可以访问：http://amh-serverip:8888/hook/index.php(默认输出Error, missing repo!)，钩子准备完成。(amh服务器默认端口为8888）

修改钩子日志目录权限（本项目配置日志路径默认在amh的index项目log路径下，方便管理）
	
	chmod 777 /home/wwwroot/index/log

### 3.修改git配置和保存git用户名密码

	sudo -Hu www git config --global credential.helper store # 永久保存
	sudo -Hu www git config --global user.name "amhtest" 
	sudo -Hu www git config --global user.email "amh@test.com" # 邮箱请与git服务器登录邮箱上一致

#### 例如Git服务是Coding网站
1.添加用户公钥

复制/root/.ssh/id_rsa.pub内容到个人设置页的SSH公钥里添加即可（[https://coding.NET/user/account/setting/keys](https://coding.NET/user/account/setting/keys "https://coding.NET/user/account/setting/keys")）

2.添加部署公钥

复制/home/www/.ssh/id_rsa.pub的内容并添加到部署公钥:

选择项目 > 设置 > 部署公钥 > 新建 > 粘贴到下面框并确认

3.添加hook

选择项目 > 设置 > WebHook > 新建hook > 粘贴你的hook/index.php所在的网址。比如:http://amh-serverip:8888/hook/index.php, 令牌可选。

稍过几秒刷新页面查看hook状态，显示为绿色勾就OK了。

## 二、初始化
### 1.amh上新建网站 如：test
查看wwwroot目录是否已生产test目录

	ls /home/wwwroot/
index  test

删除test》web目录内所有文件并修改权限（amh中新建的web目录默认所有者为root，用户组为www，不符合git自动部署的要求，必须所有者和用户组都为www）

	#以下命令，每次在amh中新建一个网站都要执行一次！！！
	rm -rvf /home/wwwroot/test/web/*
	chown -R www:www /home/wwwroot/test/web

### 2.git clone 项目文件到网站目录

	sudo -Hu www git clone https://git.coding.net/sujianchao/test.git  /home/wwwroot/test/web  --depth=1
这个时候应该会要求你输入一次Coding的帐号和密码，因为上面我们设置了永久保存用户名和密码，所以之后再执行git就不会要求输入用户名和密码了。

**！！注意，这里初始化clone必须要用www用户**

往Coding.Net提交一次代码测试：（略）

OK，稍过几秒，正常的话你在配置的项目目录里就会有你的项目文件了。在log目录下会生产日志文件

	ls /home/wwwroot/index/log/
access.log  deploy-test-2017-06-05.log  error.log  nginx_error.log

	cat /home/wwwroot/index/log/deploy-test-2017-06-05.log
2017-06-05 16:31:57	[info]	{"time":"2017-06-05 16:31:57","ip":"120.132.59.96","exec":"sudo -Hu www git --git-dir=/home/wwwroot/test/web/.git --work-tree=/home/wwwroot/test/web pull","result":"0","output":["Updating 4a5cf9a..db881ce","Fast-forward"," index.html | 2 +-"," 1 file changed, 1 insertion(+), 1 deletion(-)"]}

祝你成功！
