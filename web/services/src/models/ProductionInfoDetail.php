<?php  

namespace App\Model;
class ProductionInfoDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'production_info_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'production_info_id'
                  , 'production_info_type1'
                  , 'production_info_type2'
                  , 'production_info_type3'
                  , 'package_amount'
                  , 'amount'
                  , 'price_value'
                  , 'create_date'
                  , 'update_date'
                );
  	
}