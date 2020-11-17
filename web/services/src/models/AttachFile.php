<?php  

namespace App\Model;
class AttachFile extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'AttachFiles';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	
  	protected $fillable = ['parent_id', 'page_type', 'file_name', 'file_code', 'file_path', 'content_type', 'file_size'];

}
