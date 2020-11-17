<?php  

namespace App\Model;
class SaleChanel extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'sale_chanel';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
								, 'chanel_name'
  								, 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
  	
}