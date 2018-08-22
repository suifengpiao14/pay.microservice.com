<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:34
 */


$appEnv=$_SERVER['APP_ENV']??'prod';
defined('APP_ENV') or define('APP_ENV',$appEnv);
defined('APP_ENV_DEV') or define('APP_ENV_DEV',$appEnv=='dev');
defined('APP_ENV_TEST') or define('APP_ENV_TEST',$appEnv=='test');
defined('APP_ENV_PROD') or define('APP_ENV_PROD',$appEnv=='prod' || !$appEnv);

require '../vendor/autoload.php';

/**
 * 加载环境变量
 */
$envFile = '.env';
// 开发、测试环境加载对应的环境变量文件
APP_ENV_PROD or $envFile =$envFile.'.'.$appEnv;
$dotenv = new Dotenv\Dotenv(__DIR__.'/../',$envFile);
$dotenv->load();

$settings= include_once '../config/config.php';
$container= include_once '../config/container.php';
$app = app($settings,$container);

include '../routes/api.php';
$app->run();