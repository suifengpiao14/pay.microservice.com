<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:51
 */

namespace App\Controllers;


use Slim\Http\Request;
use Slim\Http\Response;
class SwaggerController
{
    private  $_app=null;
    public function __construct()
    {
        $this->_app=app();
    }

    /**
     * @SWG\Swagger(
     *   schemes={"http"},
     *   @SWG\Info(
     *     title="支付微服务接口",
     *     version="1.0.0",
     *     description="地址：http://pay.microservice.com",
     *     @SWG\Contact(
     *          email="2912150017@qq.com"
     *      ),
     *   ),
     *  @SWG\Tag(name="api",description="客户端 api 接口"),
     *
     * )
     *
     * @SWG\Response(response="NotFound",description="not found",ref="#/definitions/error")
     * @SWG\Response(response="BadRequest",description="not found",ref="#/definitions/error")
     * @SWG\Response(response="NoContent",description="no content",ref="#/definitions/noContent")
     * @SWG\Response(response="Normal",description="OK",ref="#/definitions/Normal")
     * @SWG\Definition(
     *      definition="error",
     *      required={"status_code","message"},
     *      @SWG\Property(
     *          property="status_code",
     *          type="integer",
     *          format="int32",
     *      ),
     *      @SWG\Property(
     *          property="message",
     *          type="string",
     *          description="错误提示"
     *      )
     *   )
     *
     * @SWG\Definition(
     *      definition="noContent",
     *   )
     *
     *
     *
     * @SWG\Definition(
     *     definition="Normal",
     *      required={"status_code","message"},
     *      @SWG\Property(
     *          property="status_code",
     *          type="integer",
     *          format="int32",
     *          description="200"
     *      ),
     *      @SWG\Property(
     *          property="message",
     *          type="string",
     *          description="OK"
     *      )
     * )
     *
     * @SWG\Definition(
     *     definition="link",
     *      @SWG\Property(
     *          property="first",
     *          type="string",
     *          description="第一页链接地址"
     *      ),
     *      @SWG\Property(
     *          property="last",
     *          type="string",
     *          description="最后一页链接地址"
     *      ),
     *     @SWG\Property(
     *          property="prev",
     *          type="string",
     *          description="上一页链接地址"
     *      ),
     *      @SWG\Property(
     *          property="next",
     *          type="string",
     *          description="下一页链接地址"
     *      ),
     * )
     *
     *
     *
     * @SWG\Definition(
     *     definition="meta",
     *      @SWG\Property(
     *          property="current_page",
     *          type="integer",
     *          format="int32",
     *          description="当前页面",
     *          default=1,
     *      ),
     *      @SWG\Property(
     *          property="last_page",
     *          type="integer",
     *          format="int32",
     *          description="1",
     *      ),
     *      @SWG\Property(
     *          property="from",
     *          type="integer",
     *          format="int32",
     *          description="前一页",
     *      ),
     *      @SWG\Property(
     *          property="to",
     *          type="integer",
     *          format="int32",
     *          description="后一页",
     *      ),
     *      @SWG\Property(
     *          property="path",
     *          type="string",
     *          description="请求地址",
     *      ),
     *      @SWG\Property(
     *          property="per_page",
     *          type="integer",
     *          description="没页请求数量",
     *          default="20",
     *      ),
     *      @SWG\Property(
     *          property="total",
     *          type="integer",
     *          description="总数",
     *      ),
     * )
     */
    public function json(Request $request,Response $response,$arguments=[]){
        $paths=app_path();
        $swagger = \Swagger\scan($paths);
        return $response->write($swagger)->withAddedHeader('cache',36000);
    }


}