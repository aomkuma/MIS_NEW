<?php  

namespace App\Model;
class UploadLog extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'upload_log';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'menu_type'
                  , 'data_id'
  								, 'file_date'
  								, 'file_name'
  								, 'file_path'
  								, 'create_date'
  							);
}