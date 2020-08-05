<?php
namespace app\index\handle\V1_0_1\logic;

use lib\Log;
use third\S;
use app\common\model\Room;

/**
 * 订单相关逻辑
 * X-Wolf
 * 2018-6-14
 */
class OrderLogic
{

    private $handle;
    private $api_version;


    function __construct( $api_version )
    {
        $this->api_version = $api_version;
        $model_path = $api_version."model\OrderModel";
        $this->handle = new $model_path();
    }

    function create($channels,$params,$users)
    {
        if(!isset($params['room_id'] )||!isset($params['contact_id'] )||!isset($params['count'] )||!isset($params['price_map'] )||!isset($params['guest_info'])||!isset($params['total_price'] )) error(40000,'参数不全');
        $user_id    = (int)$users;
        $channel    = (int)$channels['channel'];
        if(empty($params['sub_shop_id']))
        {
            $shop_id = Room::validSubId($channel);
            if($shop_id === false) error(40000,'门店错误！');
        }
        else
        {
            $shop_id= encrypt($params['sub_shop_id'],4,false);//门店id
        }
        $room_id    = (int)encrypt($params['room_id'] ,6,false);
        $contact_id = (int)$params['contact_id'];
        $count      = (int)$params['count'];
        $price_map  = $params['price_map'];
        $guest_info = $params['guest_info'];
        $total_price= $params['total_price'];
        $remark     = isset($params['remark'])?filterEmoji(trim($params['remark'])):'';
        $order = makeOrder($channel);
        $product_type = 1;//产品类型
        //校验参数
        foreach ($guest_info as $k=>$v)
        {
            $guest_info[$k]['name'] = filterEmoji($v['name']);
        }
        if(!$this->vaildPost(json_encode($remark),200)) error(40000,'参数长度超限');
        $room       = $this->handle->getRoomType($channel,$shop_id,$room_id);
        if($count<$room['min_limit']||$count>$room['max_limit']) error(40000,'购买数量有误！');
        $shop_arr = $this->handle->getShop($room['shop_id']);
        if(empty($shop_arr))  error(50000,'没有找到店铺');
        $shop = $shop_arr[0];

        $vaildTime  = $this->vaildTime($price_map);
        $price_maps  = $vaildTime['price_map'];
        $total  = $vaildTime['total'];
        if(bcmul($total,$count,2) != $total_price) error(40000,'价格不正确');
        $data_map = array_keys($price_maps);
        $where = ['channel'=>$channel, 'room'=>$room_id,'status'=>1];
        $where['date'] = $data_map;
        $hotel_booking =$this->handle->getBooking($where);
        if(empty($room) || count($hotel_booking) != count($data_map)) error(50000,'预约房不存在');
        //校验库存和价格是否一致

        foreach ($hotel_booking as $item) {
            $getOrderCount  = $this->handle->getOrderCount($channel,$item['room'],$item['date'],2,NOW-1800);
            if(!empty($getOrderCount)) $usedCount = $getOrderCount[0]['cou'];
            else  $usedCount = 0;
            if( $item['allot']-$item['used']-$count-$usedCount < 0) error(40800,'没有库存了');
            if( !isset($price_maps[$item['date']]) || $item['price'] != $price_maps[$item['date']] ) error(40800,'价格不正确');
        }
        $contact    =  $this->handle->getContact($contact_id);
        if(empty($contact)) error(40800,'联系人不存在!');
        $pic        =  $this->handle->getPic($shop_id,1);
        $data       =  [
            'order'         =>$order,
            'total'         =>$total_price,//订房价格
            'count'         =>$count,//订房数
            'channel'       =>$channel,
            'shop_id'       =>$shop_id,
            'product'       =>$room_id,
            'product_name'  =>$room['name'],
            'type'          =>$product_type,
            'contact'       =>$contact['name'],
            'mobile'        =>$contact['mobile'],
            'uid'           =>$user_id,
            'status'        =>2,
            'ip'            =>getIp(),
            'pv_from'       =>'微信小程序',
            'remark'        =>$remark,
            'adult'         =>count($guest_info),
            'people'        =>json_encode($guest_info,JSON_UNESCAPED_UNICODE),
            'bed'           =>$room['bed_type'],
            'room_id'       =>$room['id'],
            'room_num'      =>$count,

        ];

        $night = count($data_map);
        $avg_price = (float)$total_price / ( $count * $night );

        $checkin = $data_map[0];
        $checkout = end($data_map)+86400;
        $snap       =  [
            'name'          =>$room['name'],
            'feature'       =>$room['feature'],
            'room_area'     =>$room['room_area'],
            'uppermost_floor'=>$room['uppermost_floor'],
            'adult_total'   =>$room['adult_total'],
            'child_total'   =>$room['child_total'],
            'bed_type'      =>$room['bed_type'],
            'is_smoke'      =>$room['is_smoke'],
            'is_take_pet'   =>$room['is_take_pet'],
            'is_add_bed'    =>$room['is_add_bed'],
            'user_data'     =>$room['user_data'],
            'checkin'       =>$checkin,
            'checkout'      =>$checkout,
            'night'         =>$night,
            'avg_price'     =>$avg_price,
            'count'         =>$count,
            'bucket'        =>$room['bucket'],
            'cover'         =>$room['cover'],
            'price_map'     =>$price_map,
            'price_total'   =>bcmul($total,$count,2), //总价格
            'pay_total'     =>$total_price,//实付金额
            'guest_info'    =>$guest_info,//入住人信息
            'shop_name'     =>$shop['shop_name'],
            'sub_shop_name' =>$shop['sub_shop_name'],
            'shop_group'    =>$shop['group'],
            'pic'           =>$pic        //详细描述图片
        ];
        $order_id   =  $this->handle->create($data,$snap);

        success(['order_id'=>$order]);


    }

    //订单创建校验数据
    public function vaildPost($data,$length)
    {
        if(strlen($data) <= $length) return true;
        else return false;
    }

    //校验时间
    public function vaildTime($price_map)
    {
        $price_map_count = count($price_map);


        sort_array($price_map,'date', 'asc', 'string');
        $price_maps = [];
        $total = 0;
        foreach ($price_map as $k=>$item) {
            if($k>=1)
            {
                if((strtotime($item['date'])-$date_time) != '86400') error(40000,'时间不正确');
            }
            $date_time = strtotime($item['date']);
            if(empty($date_time)) error(40000,'时间不正确');
            $price_maps[$date_time] = $item['price'];
            $total = add($total,$item['price']);
        }

        if($price_map_count != count($price_maps)) error(40000,'时间价格不正确');
        return ['price_map'=>$price_maps,'total'=>$total];
    }




    function lists($channels,$params,$users)
    {

        $shop_arr   = $this->handle->getShopName($channels['channel']);
        foreach ($shop_arr as $value)
        {
            $shopName[$value['id']] = $value['sub_shop_name'];

        }
        $room       = $this->handle->getAllRoom($channels['channel']);
        foreach ($room as $value)
        {
            $roomName[$value['id']]['name'] = $value['name'];
            $roomName[$value['id']]['cover'] = picture($value['bucket'],$value['cover']);

        }
        if($shop_arr[0]['channel'] != $channels['channel']) error(40000,'shop_id错误！');
        $startLimit = startLimit($params);
        $status     = isset($params['status'])?(int)$params['status']:0;
        $getOrders  = $this->handle->getOrders($channels,$users,$startLimit,$status,'desc');
        $count      = $this->handle->getOrdersCount($channels,$users,$status);
        $list = [];
        if(!empty($getOrders)){
            foreach ($getOrders as $getOrder) {
                $data   = json_decode($getOrder['data'],true);
                if($getOrder['status']==3&&($getOrder['refund_status']==0||$getOrder['refund_status']==2)) $refund = true;
                else                      $refund = false;
                $expire = NOW - 1800;
                if($getOrder['status'] ==2&&$getOrder['create']<$expire)  $getOrder['status'] = 9;
                if($status == '2'&&$getOrder['status'] == 9) continue;
                $list[] =  array(
                    "order_id"      => $getOrder['order'],
                    "order_time"    => date('Y-m-d',$getOrder['create']), // 下单时间,精确到天
                    "order_status"  => $getOrder['status'],
                    "pay_total"     => floatval(add($getOrder['total'],-$getOrder['rebate'],-$getOrder['sales_rebate'])),
                    "order_count"   => $getOrder['count'],
                    "room_cover"    => $roomName[$getOrder['product']]['cover'],
                    "shop_name"     => $shopName[$getOrder['shop_id']],
                    "room_name"     => $roomName[$getOrder['product']]['name'],
                    "expire"        => date('Y-m-d',$data['checkin'])."至".date('Y-m-d',$data['checkout']), // 入住有效期
                    'product_id'    => encrypt($getOrder['product'],6),
                    "is_refundable" => $refund, // 是否可退款
                    'shop_id'       => encrypt($getOrder['shop_id'],4),

                );
            }
        }
        success(['list'=>$list,'total_count'=>$count[0]['count']]);

    }

    function detail($channels,$params,$users)
    {
        if(!isset($params['order_id'])) error(40000,'参数不全！');
        $getOrder   = $this->handle->getOrderById($channels,$users,$params['order_id']);
        $list       = [];
        $refundReason =  $this->handle->getRefundReason(1);
        if(!empty($getOrder))
        {
            $getOrder   =  $getOrder[0];
            $data       =  json_decode($getOrder['data'],true);
            if($getOrder['status']==3&&($getOrder['refund_status']==0||$getOrder['refund_status']==2)) $refund = true;
            else                      $refund = false;
            $expire = NOW - 1800;
            if($getOrder['status'] ==2&&$getOrder['create']<$expire)  $getOrder['status'] = 9;
            $list = [
                "order_id"      => $getOrder['order'],
                "order_time"    => date('Y-m-d H:i:s',$getOrder['create']), // 下单时间,精确到秒
                "order_status"  => $getOrder['status'],
                "room_name"     => $data['name'],
                "shop_name"     => $data['sub_shop_name'],
                "room_id"       => encrypt($getOrder['product'],6),
                "room_cover"    => picture($data['bucket'],$data['cover']),
                "order_count"   => $data['count'], // 订单件数
                "order_price"   => floatval($data['avg_price']), // 该订单预定期间的均价
                "price_map"     => $data['price_map'],
                "guest_info"    => $data['guest_info'],
                "order_total"   => floatval($data['price_total']),
                "pay_total"     => floatval(add($getOrder['total'],-$getOrder['rebate'],-$getOrder['sales_rebate'])),
                "coupon"        => 0,  // 未使用优惠券显示0
                "contact"       => ['name'=>$getOrder['contact'],'tel'=>$getOrder['mobile']],
                "order_remark"  => $getOrder['remark'],
                "refund"        => [
                    'is_refundable' => $refund,
                    'status'        => $getOrder['refund_status'],
                    'reason_map'    => $refundReason
                ],
                'shop_id'       =>encrypt($getOrder['shop_id'],4),


            ];
        }
        success($list);

    }


    //订单申请退款
    public  function refund($channels,$params,$users)
    {
        if(!isset($params['order_id'])) error(40000,'参数不全！');
        $order      = $params['order_id'];
        $getOrder   = $this->handle->getOrderById($channels,$users,$order);
        if(empty($getOrder)) error(40000,'不存在该订单！');
        if($getOrder['0']['status']  != '3'||$getOrder['0']['refund_status']==1||$getOrder['0']['refund_status']==3) error(40000,'该订单无法进行退款操作！');
        $data       =  json_decode($getOrder[0]['data'],true);
        $getOrder   = $getOrder[0];
        $refund     = [
            'channel'         => $getOrder['channel'],
            'order_id'        => $getOrder['id'],
            'order'           => $params['order_id'],
            'num'             => createRefundNum(),
            'status'          => 1,
            'apply_total'     => $data['pay_total'],
            'refund_reason'   => isset($params['remark'])?$params['remark']:'',
            'refund_type'     => $params['refund_reason'],
            'sponsor'         => 1,
            'source'          => 1,
            'create'          => NOW,
            'update'          => NOW,
        ];
        $user      = $this->handle->getContactByUid($users);
        if(empty($user)) error(40000,'不存在联系人！');
        $get_refund_type =  $this->handle->getRefundType('1');
        $type = [];
        foreach ($get_refund_type as $v)
        {
            $type[$v['id']] = $v['name'];
        }
        $typeName = $type[$params['refund_reason']];
        $remark   = isset($params['remark'])?$params['remark']:'';
        $refund_log = [
            'type'      => 1,
            'reason'    => $typeName.'，'.$remark,
            'userid'    => $users,
            'username'  => $user['name'],
            'identity'  => 1,
            'create'    => NOW,
        ];
        $reOrder = $this->handle->RefundOrder($getOrder['id']);
        if(empty($reOrder['order_id']))
        {
            $this->handle->refund($refund,$refund_log);
        }
        else
        {
            $this->handle->refundAgain($refund,$refund_log,$reOrder['id']);
        }
        $this->refundApplySms($getOrder);
        
        success(['operation'=>1]);
    }
    
    //退款申请短信
    private function refundApplySms($order)
    {
        $classes = $this->api_version."hook\OrderInit";
        $ret = $classes::factory($order['type'])->apply('smsApplyRefund',$order);    
        if($ret){
            $res = S::exec($order['order']);
            S::log('退款申请 - 及时发送短信结果:'.json_encode($res,JSON_UNESCAPED_UNICODE));
        }
    }

}