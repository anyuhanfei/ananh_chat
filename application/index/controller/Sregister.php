<?php
namespace app\index\controller;

use Workerman\Worker;
use GatewayWorker\Register;
/**
 * 注册服务启动脚本
 * Register类其实也是基于基础的Worker开发的。
 * Gateway进程和BusinessWorker进程启动后分别向Register进程注册自己的通讯地址，Gateway进程和BusinessWorker通过Register进程得到通讯地址后，就可以建立起连接并通讯了。
 * 
 * Register类只能定制监听的ip和端口，并且目前只能使用text协议。
 */
class Sregister{

	public function __construct(){
		// register 服务必须是text协议
		$register = new Register('text://0.0.0.0:1236');
		
		// 如果不是在根目录启动，则运行runAll方法
		if(!defined('GLOBAL_START'))
		{
			Worker::runAll();
		}
	}
}
