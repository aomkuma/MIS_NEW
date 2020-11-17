<?php  

namespace App\Model;
class MaterialDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'material_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'material_id'
                  , 'material_type_id'
                  , 'amount_unit'
  								, 'amount'
                  , 'price'
                  , 'create_date'
                  , 'update_date'
  							);
  }