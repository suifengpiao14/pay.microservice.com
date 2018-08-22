<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:36
 */
use Slim\Http\Request;
use Slim\Http\Response;
$app = app();


/**
 * 文档html页面
 */
$app->get('/',function(Request $request,Response $response,$arguments=[]){
    return $response->withRedirect('/swagger',301);
});

// 扫码后调用支付转换接口
$app->get('/api/v1/qrcode/pay',"App\\Controllers\\v1\\QrcodeController:pay")->setName('qrcode_pay');
// 生成合并后的支付二维码
$app->post('/api/v1/qrcode/create-pay-qrcode',"App\\Controllers\\v1\\QrcodeController:createPayQrcode")->setName('qrcode_create_pay_qrcode');

/**
 *  文档json地址
 */
$app->get('/swagger/json',"App\\Controllers\\SwaggerController:json")->setName('swagger_json');
