<?php 
namespace third;

use lib\MyLog;
use think\facade\Request;
/**
 * 常用方法
 */
class S
{
	
	static $logErrorTypes = ['emergency','error','warn','notice','info','debug']; 

	/**
     * 日志记录
     * @param  string $level 错误等级 emergency > error > warn > notice > info > debug
     * @param  string $msg   错误信息
     */
	static public function recordLog($errMsg = '',$level = 'info',$recordIp = true)
	{
        //日志记录  类型 数据 (统一记录IP地址)
        if($errMsg && in_array($level, static::$logErrorTypes,true)){
            $ipMsg = '';
            if($recordIp){
                $ip = Request::ip();
                $ipMsg = ' IP地址 :'.$ip;
            }

            $msg = sprintf('记录日志 %s %s',$errMsg,$ipMsg);

	        MyLog::$level($msg);
        }
	}


	//记录日志
    static public function log($errMsg = '',$level = 'info',$logPath = '../runtime',$recordIp = true)
    {
        MyLog::set_config(['log_path'=>$logPath]);

        self::recordLog(is_array($errMsg) || is_object($errMsg) ? json_encode($errMsg,JSON_UNESCAPED_UNICODE ) : $errMsg , $level , $recordIp);
    }

    // 微信推送返回值
    static public function msgResponse($msg = 'success',$is_die = true)
    {
        echo $msg;

        if($is_die) die;
    }

    // 格式化权限列表
    static public function formatPrivileges($privileges)
    {
        $ids = '';
        if(empty($privileges)) return $ids;

        $idArr = array_map(function($v){ return $v['funcscope_category']['id']; }, $privileges);

        return implode(',', $idArr);
    }

    // 生成又拍云图片路径
    static public function generateUpImgName($type,$url = '',$prefix = '')
    {
        if(!$type) return false;

        $subDir = DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('y');
        if($url) {
            return DIRECTORY_SEPARATOR.$type.$subDir.DIRECTORY_SEPARATOR.$prefix.$url;
        }
        return DIRECTORY_SEPARATOR.$type.$subDir.DIRECTORY_SEPARATOR.self::uniqueId(32,$prefix);
    }

    // 生成唯一的id
    static function uniqueId($length = 16,$prefix = '')
    {
        $id = $prefix;
        $addLength = $length - 13;
        $id .= uniqid();
        if (function_exists('random_bytes')) {
            $id .= substr(bin2hex(random_bytes(ceil(($addLength) / 2))),0,$addLength);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $id .= substr(bin2hex(openssl_random_pseudo_bytes(ceil($addLength / 2))),0,$addLength);
        } else {
            $id .= mt_rand(1*pow(10,($addLength)),9*pow(10,($addLength)));
        }
        return $id;
    }

    // 执行exec
    static function exec($order)
    {
        $data = ['order'=>$order,'key'=>config('web.validate_key').APP_EVN];
        $url = DOMAIN_MP . '/sms/sender';
        return json_decode(curl_file_get_contents($url,$data),true);
    }

}


