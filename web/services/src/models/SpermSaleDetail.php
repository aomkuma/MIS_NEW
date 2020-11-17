<?php  

namespace App\Model;
class SpermSaleDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'Sperm_sale_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'sperm_sale_id'
                  , 'sperm_sale_type_id'
  								, 'amount'
                  , 'price'
                  , 'values'
                  , 'create_date'
                  , 'update_date'
  							);
  }