<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 16:23
 */
return [
    'errorHandler'=>function ($c) {
        return new App\Exceptions\Error($c['settings']['displayErrorDetails'],$c['logger']);
    },
    'logger'=>function($c) {
        $logger = new Monolog\Logger('logger');
        $filename = logs_path().'/'.date('Y-m-d').'.log';
        $stream = new Monolog\Handler\StreamHandler($filename, Monolog\Logger::DEBUG);
        $fingersCrossed = new Monolog\Handler\FingersCrossedHandler(
            $stream, Monolog\Logger::ERROR);
        $logger->pushHandler($fingersCrossed);

        return $logger;
    },
    'cache'=>function ($c) {
        if(APP_ENV_DEV){//开发环境使用虚拟缓存
            return new \Symfony\Component\Cache\Adapter\NullAdapter();
        }
        $redis = new \Redis();
        $host = env('REDIS_HOST')??'127.0.0.1';
        $port = env('REDIS_PORT')??6379;
        $auth = env('REDIS_PASSWORD')??'';
        $redis->connect($host,$port,2);//2秒连接不上就报错
        $auth and $redis->auth($auth);
        return new Symfony\Component\Cache\Adapter\RedisAdapter($redis);
    },
    'view'=>function($c){
        $view = new \Slim\Views\Twig(view_path(), [
            'cache' => env('VIEW_CACHE')?cache_path():false,
        ]);
        // Instantiate and add Slim specific extension
        $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), web_path()));
        return $view;
    },
];