<?php

namespace app\v3\handle\logic;

use app\v3\handle\query\CouponQuery;
use app\v3\model\BaseModel;
use app\v3\Services\BaseService;
use lib\MyLog;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18 0018
 * Time: 下午 15:28
 */
class CouponLogic extends BaseService
{
    private $query;

    public function __construct()
    {
        $this->query = new CouponQuery();
    }

    public function detail($channels, $params, $users)
    {
        $channel = encrypt($params['channel'], 3, false);
        $groups = $this->query->getChannlGroup($channel);
        //未登陆获取券的数据
        if (empty($users)) {
            if (!isset($params['id'])) {
                error(40000, '券id必传！');
            }
            $id = encrypt($params['id'], 9, false);
            $couponDetail = $this->query->getCoupon($id, $channel);
            if (empty($couponDetail)) {
                error(40000, '该券不存在！');
            }
            $status = 3;
        } //用于登陆用户通过code获取券详情
        else {
            if (!isset($params['code'])) {
                error(40000, '券code必传！');
            }
            $couponDetail = $this->query->getCouponBycode($params['code'], $channel, $users);
            if (!$couponDetail) {
                error(40000, '该券不存在！');
            }
            if ($couponDetail['status'] == '1') {
                $status = 2;
            } elseif ($couponDetail['start'] > NOW && !empty($couponDetail['start'])) {
                $status = 4;
            } elseif ($couponDetail['end'] < NOW && !empty($couponDetail['end'])) {
                $status = 1;
            } else {
                $status = 0;
            }
        }
        $product_total = $this->query->getProductTotal($couponDetail['coupon_id']);
        $shop_name = $this->query->getChannelForCou($couponDetail['shop_id']);
        if ($couponDetail['type'] == '2') {
            $couponDetail['value'] = $couponDetail['value'] * 100;
        }
        $detail = [
            'id' => encrypt($couponDetail['coupon_id'], 9),
            'name' => $couponDetail->coupon['name'],
            'shop_name' => empty($groups['group']) ? '' : $shop_name['name'],
            'shop_id' => encrypt($couponDetail['shop_id'], 4),
            'type' => $couponDetail['type'], //1为面值2为折扣
            'value' => floatval($couponDetail['value']),//是折扣的话(0.1~0.99)
            'limit' => floatval($couponDetail['limit']),
            'start' => $couponDetail['start'],
            'end' => $couponDetail['end'],
            'day' => $couponDetail->coupon['day'],
            'max' => $couponDetail->coupon['max_geted'],
            'desc' => $couponDetail->coupon['intro'],
            'num' => $couponDetail->coupon['count'] - $couponDetail->coupon['geted'],
            'product_total' => $product_total,
            'status' => $status,//用于控制券状态，0是未使用，1已过期，2已使用,3未领取,4未到使用时间
        ];
        success($detail);
    }

    //根据产品拉取优惠券
    public function coupon_list($channels, $params, $users)
    {
        $channel = encrypt($params['channel'], 3, false);
        if (!isset($params['id'])) {
            error(40000, '产品id必传！');
        }
        if (!isset($params['price']) || $params['price'] <= 0) {
            error(40000, '产品价格必传！');
        }
        if (!isset($params['type'])) {
            error(40000, '产品类型必传！');
        }
        if (empty($params['sub_shop_id'])) {
            $shop_id = BaseModel::validSubId($channel);
            if ($shop_id === false) {
                error(40000, '门店错误！');
            }
        } else {
            $shop_id = encrypt($params['sub_shop_id'], 4, false);//门店id
        }
        if ($params['type'] == '1') {
            $id = encrypt($params['id'], 6, false);
        } else {
            $id = encrypt($params['id'], 1, false);
        }
        $coupon_list = $this->query->getCouponByProAndPrice($channel, $id, $users, $params['price'], $params['type'], $shop_id);
        MyLog::debug('[DEBUG] ' . \think\Db::getLastSql());
        $coupon_list_noProduct = $this->query->getCouponByNoProAndPrice($channel, $id, $users, $params['price'], $params['type'], $shop_id);
        MyLog::debug('[DEBUG] ' . \think\Db::getLastSql());
        foreach ($coupon_list_noProduct as $v) {
            if ($v['totalNum'] === 0) {
                $coupon_list[] = $v;
            }
        }
        $list = [];
        foreach ($coupon_list as $v) {
            if ($v->coupon_code['type'] == '1' && $v->coupon_code['value'] >= $params['price']) {
                continue;
            }
            if ($v->coupon_code['type'] == '2') {
                $v->coupon_code['value'] = $v->coupon_code['value'] * 100;
            }
            $list[] = [
                'id' => encrypt($v->coupon_code['coupon_id'], 9),
                'name' => $v->coupon_code->coupon['name'],
                'code' => $v->coupon_code['id'],
                'type' => $v->coupon_code['type'], //1为面值2为折扣
                'value' => floatval($v->coupon_code['value']),//是折扣的话(0.1~0.99)
                'limit' => floatval($v->coupon_code['limit']),
                'start' => $v->coupon_code['start'],
                'end' => $v->coupon_code['end'],
                'desc' => $v->coupon_code->coupon['intro'],
                'status' => 0 //此处券状态似乎没有用
            ];
        }
        success(['list' => $list]);
    }

    //我的优惠券
    public function lists($channels, $params, $users)
    {
        $channel = encrypt($params['channel'], 3, false);
        $groups = $this->query->getChannlGroup($channel);
        if (isset($params['status'])) {
            $coupon_list = $this->query->getCouponByUidForUser($channel, $users, $params['status']);
        } else {
            $coupon_list = $this->query->getCouponByUid($channel, $users);
        }

        $list = [];
        if (!isset($coupon_list[0])) {
            $total = 0;
        } else {
            foreach ($coupon_list as $v) {
                if ($v['type'] == '2') {
                    $v['value'] = $v['value'] * 100;
                }


                if ($v['status'] == '1') {
                    $status = 2;
                } elseif ($v['start'] > NOW) {
                    $status = 4;
                } elseif ($v['end'] < NOW) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $shop_name = $this->query->getChannelForCou($v['shop_id']);
                $list[] = [
                    'id' => encrypt($v['id'], 9),
                    'name' => $v['name'],
                    'shop_name' => empty($groups['group']) ? '' : $shop_name['name'],
                    'shop_id' => encrypt($v['shop_id'], 4),
                    'code' => $v['code'],
                    'type' => $v['type'], //1为面值2为折扣
                    'value' => floatval($v['value']),//是折扣的话(0.1~0.99)
                    'limit' => floatval($v['limit']),
                    'start' => $v['start'],
                    'end' => $v['end'],
                    'desc' => $v['intro'],
                    'status' => $status, //状态跟前端定 并且根据是否有用户数据进行判断
                    'product_total' => $v['num'],
                ];
                $total = count($coupon_list);
            }
        }
        success(['list' => $list, 'total_count' => $total]);
    }


    //可使用优惠券的产品列表
    public function product_list($channels, $params, $users)
    {
        $channel = encrypt($params['channel'], 3, false);
        if (!isset($params['id'])) {
            error(40000, '券id必传！');
        }
        $id = encrypt($params['id'], 9, false);
        $product_list = $this->query->getProductByCoupon($channel, $id);
        $product_list_room = $this->query->getProductByCouponForRoom($channel, $id);

        $list = [];
        foreach ($product_list_room as $v) {
            $list[] = [
                'id' => encrypt($v['id'], 6),
                'cover' => picture($v['bucket'], $v['pic']),
                'shop_id' => encrypt($v['shop_id'], 4),
                'name' => $v['name'],
                'desc' => $v['feature'],
                'price' => floatval($v['default_price']),
                'type' => 1
            ];
        }
        foreach ($product_list as $v) {
            $list[] = [
                'id' => encrypt($v['id'], 1),
                'cover' => picture($v['bucket'], $v['pic']),
                'shop_id' => encrypt($v['shop_id'], 4),
                'name' => $v['name'],
                'desc' => $v['title'],
                'price' => floatval($v['price']),
                'type' => $v['type']
            ];
        }

        $total = count($list);
        // 分页处理
        $pageInfo = startLimit($params);
        $list = array_slice($list, $pageInfo['start'], $pageInfo['limit']);

        success(['list' => $list, 'total_count' => $total]);
    }

    //可使用优惠券的产品列表
    public function draw($channels, $params, $users)
    {
        $channel = encrypt($params['channel'], 3, false);
        if (!isset($params['id'])) {
            error(40000, '券id必传！');
        }
        $id = encrypt($params['id'], 9, false);
        $couponDetail = $this->query->getCoupon($id, $channel);
        $userCoupon = $this->query->getCouponByUidAndId($channel, $users, $couponDetail['id']);
        //用于控制券状态，0是未使用，1已过期，2已使用,3未领取,4未到使用时间,5领取次数超限,6已领完
        if (empty($couponDetail) || $couponDetail['status'] == '0' || ($couponDetail['end'] < NOW && !empty($couponDetail['end']))) {
            success(['status' => 1]);
        }
        if ($couponDetail['max_geted'] <= count($userCoupon)) {
            success(['status' => 5]);
        }
        if (($couponDetail['count'] - $couponDetail['geted']) <= 0) {
            success(['status' => 6]);
        }
        if ($couponDetail['start'] < NOW) {
            $couponDetail['start'] = NOW;
        }

        //规则:如果有天数限制，1天为当天有效，到23点59分59秒
        if ($couponDetail['day'] != '0') {
            $couponDetail['end'] = strtotime(date('Y-m-d 23:59:59', $couponDetail['start'] + (($couponDetail['day'] - 1) * 86400)));
        }
        $this->query->setCoupon($users, $couponDetail);
        success(['status' => 0]);
    }
}
