<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 16:03
 */

function app($config=null,$container=[]){
    static $app=null;
    if(is_null($app)){
        $container['settings']=$config;
        $app = new \Slim\App($container);
    }
    return $app;
}

function container($name){
    static $container = null;
    if(is_null($container)){
        $container = app()->getContainer();
    }
    return $container->get($name);
}

function settings($key){
    static $settings=null;
    if(is_null($settings)){
        $settings = container('settings');
    }
    return $settings[$key]??null;
}

/**
* 获取 IP  地理位置
* 淘宝IP接口
* @property string $ip
* @Return: string
*/
function getCityByIp($ip)
{
    return '';//暂时直接返回，没有传入名称的地区基本是偏远地区
    $city='';
    $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
    try{
        $httpClient = new GuzzleHttp\Client([
            'timeout'  => 5.0,
        ]);
        $response=$httpClient->get($url);
        $json = $response->getBody()->getContents();
        if($json && $data=json_decode($json,true)){
            $city = isset($data['data']['city'])?$data['data']['city']:'';
        }
        $city = trim($city,'市');
    }catch (GuzzleHttp\Exception\TransferException $exception){
        /** @var Monolog\Logger $logger */
        $logger = container('logger');
        $logger->info($exception->getMessage(),['url'=>$url]);
    }

    return $city;
}

function getClientIp(){
    if (env("HTTP_CLIENT_IP") && strcasecmp(env("HTTP_CLIENT_IP"), "unknown")){
        $ip = env("HTTP_CLIENT_IP");
    }else if (env("HTTP_X_FORWARDED_FOR") && strcasecmp(env("HTTP_X_FORWARDED_FOR"), "unknown")){
        $ip = env("HTTP_X_FORWARDED_FOR");
    }else if (env("REMOTE_ADDR") && strcasecmp(env("REMOTE_ADDR"), "unknown")){
        $ip = env("REMOTE_ADDR");
    }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
        $ip = $_SERVER['REMOTE_ADDR'];
    }else{
        $ip = '';
    }
    return $ip;
}

/**
 * @param $key
 * @param int|string $expired int标识缓存多久，string标识缓存到的时间点
 * @param Closure $callback
 * @return mixed
 */
function remember($key,$expired,Closure $callback){
    static $cache = null;
    if(is_null($cache)){
        /** @var \Symfony\Component\Cache\Adapter\RedisAdapter $cache */
        $cache=app()->getContainer()->get('cache');
    }
    /** @var \Symfony\Component\Cache\CacheItem $cacheItem */
    $cacheItem=$cache->getItem($key);
    if(!$cacheItem->isHit()){
        $result =  $callback();
        if(is_int($expired)){//$expired为时间差
            $cacheItem->expiresAfter($expired);
        }else{
            $expiration=new \DateTime($expired);//设置时间点过期
            $cacheItem->expiresAt($expiration);
        }
        $cacheItem->set($result);
        $cache->save($cacheItem);
    }
    $output = $cacheItem->get();
    return $output;
};

function web_path(){
    return root_path().DIRECTORY_SEPARATOR.'public';
}

function root_path(){
    return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..';
}

function app_path(){
    return root_path().DIRECTORY_SEPARATOR.'app';
}

function config_path(){
    return root_path().DIRECTORY_SEPARATOR.'config';
}

function storage_path(){
    return root_path().DIRECTORY_SEPARATOR.'storage';
}
function logs_path(){
    return storage_path().DIRECTORY_SEPARATOR.'logs';
}
function cache_path(){
    return storage_path().DIRECTORY_SEPARATOR.'cache';
}

function source_path(){
    return root_path().DIRECTORY_SEPARATOR.'resources';
}
function view_path(){
    return source_path().DIRECTORY_SEPARATOR.'views';
}

/**
 * 拼接文件路径
 * @return string
 */
function get_dir(){
    return implode(DIRECTORY_SEPARATOR,func_get_args());
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}