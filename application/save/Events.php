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
// require_once '/your/path/of/mysql-master/src/Connection.php';


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
        self::$db = new Connection('127.0.0.1', '3306', 'root', 'root', 'otochat');
        self::$db->query("DELETE FROM `chat`");
    }

    /**
     * 客户端发送消息时的回调函数
     * 单聊天室，一对一聊天
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
        switch($message_data['type']){
            // 登录
            case 'login':
                //获取客户端名称并加入session
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['client_name'] = $client_name;
                //获取连接中的用户（只有一个房间）
                $clients_list = Gateway::getClientSessionsByGroup(self::$group_id);
                if($clients_list){ //有用户才能整理数据
                    foreach($clients_list as $tmp_client_id=>$item)
                    {
                        $clients_list[$tmp_client_id] = $item['client_name'];
                    }
                }
                $clients_list[$client_id] = $client_name;
                //广播的信息
                $login_message = array('type'=>'login', 'client_id'=>$client_id, 'client_name'=>$client_name, 'time'=>date('Y-m-d H:i:s'));
                //广播当前客户端登录信息
                Gateway::sendToGroup(self::$group_id, json_encode($login_message));
                //加入房间
                Gateway::joinGroup($client_id, self::$group_id);
                //给当前客户端发送用户列表
                $login_message['clients_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($login_message));
                return;
            //联系列表
            case 'linkman':
                $linkman_sql = "SELECT `to_client_id`,`to_client_name` FROM `chat` WHERE `client_id`='$client_id' OR `to_client_id`='$client_id' GROUP BY `client_id`";
                $res = self::$db->query($linkman_sql);
                $linkman_list = array();
                if($res){
                    foreach($res as $v){
                        $linkman_list[$v['to_client_id']] = $v['to_client_name'];
                    }
                }
                $linkman_list_message = array(
                    'type'=>'linkman',
                    'linkman_list'=>$linkman_list
                );
                Gateway::sendToClient($client_id, json_encode($linkman_list_message)); //发送回自己
                return;
            //发送信息
            case 'say':
                //获取数据
                $client_name = $_SESSION['client_name'];
                $to_client_id = $message_data['to_client_id'];
                $to_client_name = $message_data['to_client_name'];
                $chat_content = $message_data['content'];
                $chat_time = date('Y-m-d H:i:s',time());
                //聊天信息加入数据库
                $insert_chat_sql = "INSERT INTO `chat` (`client_id`,`client_name`,`to_client_id`,`to_client_name`,`content`,`chat_time`) VALUE ('$client_id', '$client_name', '$to_client_id', '$to_client_name', '$chat_content', '$chat_time')";
                $res = self::$db->query($insert_chat_sql);
                //向指定客户端发送消息提示（向'接收客户端'发送'发送客户端'的id和名称，表示有消息需要接收）
                $prompt_message = array(
                    'type'=>'prompt',
                    'send_client_id'=>$client_id, //发送人的id
                    'send_client_name'=>$client_name //发送人的名称
                );
                Gateway::sendToClient($message_data['to_client_id'], json_encode($prompt_message));
                //向指定客户端发送消息，如果当前聊天窗口不是被发送客户端，则不处理
                $say_message = array(
                    'type'=>'say', 
                    'client_id'=>$client_id, 
                    'client_name'=>$client_name, 
                    'to_client_id'=>$message_data['to_client_id'],
                    'time'=>date('Y-m-d H:i:s'), 
                    'content'=>$message_data['content']
                );
                Gateway::sendToClient($message_data['to_client_id'], json_encode($say_message)); # 向to_client_id发送信息
                return;
            //选择会员，获取聊天记录
            case 'content':
                $to_client_id = $message_data['to_client_id'];
                $content_sql = "SELECT * FROM `chat` WHERE (`client_id`='$to_client_id' OR `to_client_id`='$to_client_id') AND (`client_id`='$client_id' OR `to_client_id`='$client_id')";
                $content_res = self::$db->query($content_sql);
                $content_message = array(
                    'type'=>'content',
                    'content'=>$content_res
                );
                Gateway::sendToClient($client_id, json_encode($content_message));
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
        
        $client_name = $_SESSION['client_name'];
        $outlogin_message = array(
            'type'=>'outlogin',
            'client_id'=>$client_id,
            'client_name'=>$client_name
        );
        Gateway::sendToGroup(self::$group_id, json_encode($outlogin_message));
    }
}
