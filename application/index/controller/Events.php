<?php
namespace app\index\controller;


/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 */
use \GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;

use app\index\controller\Index;


class Events
{
    /**
     * 新建一个类的静态成员，用来保存数据库实例
     */
    public static $db = null;

    protected static $group_id = 1;

    /**
     * 进程启动后初始化数据库连接
     * 并初始化数据库(因每次重启进程，client_id都会重新按顺序分配)
     */
    public static function onWorkerStart($worker)
    {
        self::$db = new Connection('127.0.0.1', '3306', 'root', '168168', 'oct_chat');
        // self::$db->query("DELETE FROM `chat`");
    }

    /**
     * 客户端发送消息时的回调函数
     * 单聊天室，一对一聊天
     * 广播用sendToAll，单人发送用sendToUid
     * @param int $client_id 客户端id（自动传入）
     * @param json $message 客户端发送给服务器的信息
     */
    public static function onMessage($client_id, $message){
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        //解码为数组格式
        $message_data = json_decode($message, true);
        if(!$message_data){
            return ;
        }
        //获取user信息（session）
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        //业务执行
        switch($message_data['type']){
            // 登录
            case 'login':
                //登录，把user信息加入session中
                $_SESSION['user_id'] = $message_data['user_id'];
                $_SESSION['user_name'] = $message_data['user_name'];
                //重新赋值user变量
                $user_id = $_SESSION['user_id'];
                $user_name = $_SESSION['user_name'];
                //判断是否已经有client_id绑定到此uid上了，（手动限制一个uid绑定一个client_id）（官方指定验证uid是否在线的函数，放心食用）
                $online_client = Gateway::getClientIdByUid($user_id);
                if($online_client){
                    $result_message = array('type'=>'login_error', 'msg'=>'登录失败回复，此uid已经登录了');
                    Gateway::sendToClient($client_id, json_encode($result_message));
                    return;
                }
                //client_id和uid绑定
                Gateway::bindUid($client_id, $user_id);
                //广播，登录消息
                $result_message = array('type'=>'login', 'user_id'=>$user_id, 'user_name'=>$user_name, 'time'=>date('Y-m-d H:i:s',time()), 'msg'=>'会员上线消息');
                Gateway::sendToAll(json_encode($result_message));
                //向当前客户端发送在线列表
                $clients_list = Gateway::getAllClientSessions();
                $user_list = array();
                if($clients_list){
                    foreach($clients_list as $tmp_client_id=>$item)
                    {
                        $user_list[$item['user_id']] = $item['user_name'];
                    }
                }
                $result_message = array('type'=>'online_login', 'user_list'=>$user_list, 'user_id'=>$user_id, 'user_name'=>$user_name, 'msg'=>'在线列表');
                Gateway::sendToUid($user_id, json_encode($result_message));
                return;
            //联系列表
            case 'linkman':
                $linkman_sql = "SELECT `send_user_id` FROM `user_chat` WHERE `send_user_id`='$user_id' OR `receive_user_id`='$user_id' GROUP BY `send_user_id`";
                $res = self::$db->query($linkman_sql);
                $linkman_list = array();
                if($res){
                    foreach($res as $v){
                        if($v['send_user_id'] != $user_id){
                            $client_id_list = Gateway::getClientIdByUid($v['send_user_id']); //获取uid绑定的client_id，数组
                            if($client_id_list){
                                $client_id_session = Gateway::getSession($client_id_list[0]); //获取一个client_id的session
                                $linkman_list[$client_id_session['user_id']] = $client_id_session['user_name'];
                            }
                        }
                    }
                }
                $result_message = array('type'=>'linkman', 'linkman_list'=>$linkman_list, 'msg'=>'联系列表');
                Gateway::sendToUid($user_id, json_encode($result_message)); //发送回自己
                return;
            //发送信息
            case 'say':
                //获取数据
                $receive_user_id = $message_data['receive_user_id'];
                $chat_content = $message_data['content'];
                $chat_time = date('Y-m-d H:i:s',time());
                //聊天信息加入数据库
                $insert_chat_sql = "INSERT INTO `user_chat` (`send_user_id`,`receive_user_id`,`content`,`chat_time`) VALUE ($user_id, $receive_user_id, '$chat_content', '$chat_time')";
                $res = self::$db->query($insert_chat_sql);
                //消息提示（向'接收客户端'发送'发送客户端'的id和名称，表示有消息需要接收）
                $result_message = array('type'=>'prompt','send_user_id'=>$user_id, 'send_user_name'=>$user_name, 'msg'=>'向接收客户端发送消息提示');
                Gateway::sendToUid($receive_user_id, json_encode($result_message));
                //消息内容，如果当前聊天窗口不是被发送客户端，则不处理
                $result_message = array(
                    'type'=>'say', 
                    'send_user_id'=>$user_id, 
                    'send_user_name'=>$user_name, 
                    'receive_user_id'=>$receive_user_id,
                    'time'=>$chat_time, 
                    'content'=>$chat_content,
                    'msg'=>'消息内容'
                );
                Gateway::sendToUid($receive_user_id, json_encode($result_message));
                return;
            //选择会员，获取聊天记录
            case 'content':
                $to_user_id = $message_data['to_user_id'];
                $content_sql = "SELECT * FROM `user_chat` WHERE (`send_user_id`='$to_user_id' OR `receive_user_id`='$to_user_id') AND (`send_user_id`='$user_id' OR `receive_user_id`='$user_id')";
                $content_res = self::$db->query($content_sql);
                $result_message = array('type'=>'content', 'content'=>$content_res, 'msg'=>'聊天记录');
                Gateway::sendToUid($user_id, json_encode($result_message));
                return;
                
            //其他情况（心跳或非已知类型）
            default:
                return;
        }
    }

    /**
     * 客户端退出时的回调函数
     * 退出登录，全员广播
     */
    public static function onClose($client_id){
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id type:outlogin\n";
        
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $result_message = array(
            'type'=>'outlogin',
            'user_id'=>$user_id,
            'user_name'=>$user_name,
            'msg'=>'退出登录'
        );
        Gateway::sendToAll(json_encode($result_message));
        Gateway::unbindUid($client_id, $user_id);
    }
}
