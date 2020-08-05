<?php
/**
 *
 * User: yanghaoliang
 * Date: 2019-04-16
 * Email: <haoliang.yang@gmail.com>
 */

namespace app\v3\model\Shop;


use app\v3\model\BaseModel;

class Coupon extends BaseModel
{

    public function code()
    {
        return $this->belongsTo(CouponCode::class, 'id', 'coupon_id');
    }

    public function hotelRoomType()
    {
        return $this->belongsToMany(HotelRoomType::class,'coupon_product','product_id','coupon_id');
    }

    public function product()
    {
        return $this->belongsToMany(Product::class,'coupon_product','product_id','coupon_id');
    }

}