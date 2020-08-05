<?php
/**
 * Created by PhpStorm.
 * User: 总裁
 * Date: 2018/6/20
 * Time: 18:37
 */

namespace app\index\handle\V1_0_1\hook\hotel;


class OrderLogic
{
    static function pay($getOrder,$param){
        return OrderModel::pay($getOrder,$param);
    }

    static function smsPaySuccess($order)
    {
    	return OrderModel::smsPaySuccess($order);
    }

    static function sendWxTmp($order)
    {
    	return OrderModel::sendWxTmp($order);
    }

    static function smsApplyRefund($order)
    {
    	return OrderModel::smsApplyRefund($order);
    }

    static function informPayInfo($order)
    {
        return OrderModel::informPayInfo($order);
    }

    static function informPay($order,$tpl,$errcode,$errmsg)
    {
        return OrderModel::informPay($order,$tpl,$errcode,$errmsg);
    }
}