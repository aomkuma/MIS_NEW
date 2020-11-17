<?php  

namespace App\Model;
class DataRowsheet3 extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'data_rowsheet3';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	
  	protected $fillable = ['id', 'sheet_id','seq', 'detail','nolv',
                                'lv1','lv2','lv3','lv4','lv5','lv6','lv7','lv8','lv9','lv10'];

}
