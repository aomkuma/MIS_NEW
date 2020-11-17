<?php  

namespace App\Model;
class DataRows extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'data_rows';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	
  	protected $fillable = ['id', 'sheet_id','seq', 'positiontype','department','director',
                                'lv1','lv2','lv3','lv4','lv5','lv6','lv7','lv8','lv9','lv10','summary'];

}
