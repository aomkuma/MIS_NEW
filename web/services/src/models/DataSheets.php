<?php  

namespace App\Model;
class DataSheets extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'data_sheets';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	
  	protected $fillable = ['id', 'name', 'seq', 'attch_id'];

}
