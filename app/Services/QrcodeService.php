<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 17:18
 */
namespace App\Services;
use Guzzle\Common\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use QL\QueryList;
use App\Common\Helpers;


class QrcodeService
{
   const ALI_PAY='ali_pay';
   const WEIXIN_PAY='weixin_pay';
   const QQ_PAY='qq_pay';
}