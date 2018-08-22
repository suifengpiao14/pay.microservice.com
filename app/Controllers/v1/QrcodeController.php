<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:51
 */

namespace App\Controllers\v1;


use App\Services\QrcodeService;
use GuzzleHttp\Client;
use Slim\Http\Request;
use Slim\Http\Response;
class QrcodeController
{
    private  $_app=null;
    public function __construct()
    {
        $this->_app=app();
    }

    /**
     *
     * 判断扫码应用
     * @SWG\Get(
     *   path="/api/v0/qrcode/pay",
     *   tags={"api"},
     *   summary="支付转换接口",
     *   description="扫码后调用支付转换接口",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="User-Agent",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="支付宝、微信(qq)用户代理头",
     *     enum={
     *     "UCBrowser/11.5.0.939 UCBS/2.10.1.6 Mobile Safari/537.36 AliApp(AP/10.0.15.051805) AlipayClient/10.0.15.051805 Language/zh-Hans",
     *     "MQQBrowser/6.2 TBS 043220 Safari/537.36 MicroMessenger/6.5.8.1060 NetType/4G Language/zh_CN",
     *     "MQQBrowser/6.2 TBS/043221 Safari/537.36 QQ/7.0.0.3135",
     *     },
     *   ),
     *   @SWG\Parameter(
     *     name="ali",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="支付宝支付短连接",
     *     default="FKX03995ZBXJHD0F1LR4C8"
     *   ),
     *   @SWG\Parameter(
     *     name="wx",
     *     in="query",
     *     type="string",
     *     description="微信支付短连接",
     *     required=true,
     *     default="f2f0FJElGMW2DwR6IjVg7NvY9q0Phz7oOFDn"
     *   ),
     *   @SWG\Response(response="400", description="bad request ",ref="#/responses/BadRequest"),
     *   @SWG\Response(response="404", description="not found",ref="#/responses/NotFound"),
     *   @SWG\Response(response="200", description="ok",ref="#"),
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $arguments
     * @return $this|static
     */
    public function pay(Request $request,Response $response,$arguments=[]){
        $userAgentArr = $request->getHeader('User-Agent');
        $userAgent = reset($userAgentArr);
        return $this->wxPay($request,$response,$arguments);
        // 优先判断微信，因为微信的user-agent中也含有qq
        if (false!==strpos($userAgent, 'MicroMessenger')) {
            return $this->wxPay($request,$response,$arguments);
        }elseif (false!==strpos($userAgent, 'QQ/')) {
            return $this->qqPay($request,$response,$arguments);
        }elseif(false!==strpos($userAgent, 'AlipayClient')){
            return $this->aliPay($request,$response,$arguments);
        }
        // 默认使用支付宝付款，网页扫描时，可通过浏览器调起支付宝app
        return $this->aliPay($request,$response,$arguments);
    }

    protected function aliPay(Request $request,Response $response,$arguments=[]){
        //支付宝链接
        $ali = $request->getParam('ali');
        $url = sprintf('https://qr.alipay.com/%s',$ali);
        return $response->withRedirect($url,302);
    }

    protected function wxPay(Request $request,Response $response,$arguments=[]){
        //微信支付链接（返回原始二维码）
        $wx=$request->getParam('wx');
        $filename=md5($wx);
        $img = strtr('/qrcode/{filename}.jpg',[
            '{filename}'=>$filename,
        ]);
        $title='微信支付';
        /** @var \Slim\Views\Twig $view */
        $view = container('view');
        return $view->render($response, 'qrcode/wxPay.html', [
            'qrcode_img' => $img,
            'title'=>$title,
        ]);
    }

    /**
     *
     * qq支付，有待完善
     * @param Request $request
     * @param Response $response
     * @param array $arguments
     * @return $this
     */
    protected function qqPay(Request $request,Response $response,$arguments=[]){
        //QQ钱包支付链接（返回原始二维码）
        $originalQrcode=$request->getParam('qq');
        $name='QQ钱包支付';
        $img = strtr('<img src="{src}" width=48px height=48px alt="{alt}"',[
            '{src}'=>$originalQrcode,
            '{alt}'=>$name,
        ]);
        return $response->write($img);
    }

    /**
     *
     * 生成二合一二维码
     * @SWG\Post(
     *   path="/api/v1/qrcode/create-pay-qrcode",
     *   tags={"api"},
     *   summary="生成二合一二维码",
     *   description="生成二合一二维码",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="ali",
     *     in="formData",
     *     type="file",
     *     required=true,
     *     description="支付宝二维码",
     *   ),
     *   @SWG\Parameter(
     *     name="wx",
     *     in="formData",
     *     type="file",
     *     required=true,
     *     description="微信二维码",
     *   ),
     *   @SWG\Parameter(
     *     name="log",
     *     in="formData",
     *     type="file",
     *     description="logo",
     *   ),
     *   @SWG\Response(response="400", description="bad request ",ref="#/responses/BadRequest"),
     *   @SWG\Response(response="404", description="not found",ref="#/responses/NotFound"),
     *   @SWG\Response(response="200", description="ok",ref="#"),
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $arguments
     * @return $this|static
     */
    public function createPayQrcode(Request $request,Response $response,$arguments=[]){
        $files=$request->getUploadedFiles();
        $config=[
            //'timeout'=>20,
            'proxy'=>'127.0.0.1:8888'
        ];
        $httpClient = new Client($config);
        /** @var \Slim\Http\UploadedFile $aliQrcode */
        $aliQrcode = $files['ali']??null;
        /** @var \Slim\Http\UploadedFile $wxQrcode */
        $wxQrcode = $files['wx']??null;
        /** @var \Slim\Http\UploadedFile $logo */
        $logo = $files['logo']??null;
        // 解码微信、支付宝二维码
        $decodeQrcodeUrl = env('API_QRCODE_DECODE');
        $aliCode= $httpClient->post($decodeQrcodeUrl,[
            'multipart' => [
                [
                    'name'     => 'ali',
                    'contents' => fopen($aliQrcode->file, 'r')
                ],
            ]
        ])->getBody();
        $wxCode=$httpClient->post($decodeQrcodeUrl,[
            'multipart' => [
                [
                    'name'     => 'wx',
                    'contents' => fopen($wxQrcode->file, 'r')
                ],
            ]
        ])->getBody();
        $route = container('router');
        $url = $route->getName('qrcode_pay');
        $text = strtr('{url}?ali={ali_code}&wx={wx_code}',[
            '{url}'=>$url,
            '{ali_code}'=>$aliCode,
            '{wx_code}'=>$wxCode,
        ]);

        //根据text生成二维码
        $createQrcodeUrl = env('API_QRCODE_CREATE');
        $qrcode =$httpClient->post($createQrcodeUrl,[
            'debug' => true,
            'text' => $text,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ])->getBody();

        $response->write($qrcode);



    }


}