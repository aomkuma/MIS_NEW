<?php  

namespace App\Model;
class ProductionSaleInfoDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'production_sale_info_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'production_sale_info_id'
                  , 'production_sale_info_type1'
                  , 'production_sale_info_type2'
                  , 'production_sale_info_type3'
                  , 'sale_chanel_id'
                  , 'customer_name'
                  , 'amount'
                  , 'addon'
                  , 'price_value'
                  , 'package_amount'
                  , 'addon_package_amount'
                  , 'create_date'
                  , 'update_date'
                );
  	
}