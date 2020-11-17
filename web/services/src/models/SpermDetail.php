<?php  

namespace App\Model;
class SpermDetail extends \Illuminate\Database\Eloquent\Model {  
    protected $table = 'sperm_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
                  , 'sperm_id'
                  , 'sperm_item_id'
                  , 'amount'
                  , 'amount_unit'
                  , 'price'
                  , 'create_date'
                  , 'update_date'
                );
  }