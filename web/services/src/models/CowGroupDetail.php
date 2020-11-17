<?php  

namespace App\Model;
class CowGroupDetail extends \Illuminate\Database\Eloquent\Model {  
    protected $table = 'cow_group_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
                  , 'cow_group_id'
                  , 'cow_group_name'
                  , 'cow_group_item_id'
                  , 'cow_group_type_id'
                  , 'beginning_period'
                  , 'beginning_period_total_values'
                  , 'total_born'
                  , 'total_born_values'
                  , 'total_movein'
                  , 'total_movein_values'
                  , 'total_buy'
                  , 'total_buy_values'
                  , 'total_die'
                  , 'total_die_values'
                  , 'total_sell'
                  , 'total_sell_values'
                  , 'total_sell_carcass'
                  , 'total_sell_carcass_values'
                  , 'total_moveout'
                  , 'total_moveout_values'
                  , 'total_cutout'
                  , 'total_cutout_values'
                  , 'last_period'
                  , 'last_period_total_values'
                  , 'create_date'
                  , 'update_date'
                );
  }