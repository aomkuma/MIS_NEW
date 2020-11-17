<?php  

namespace App\Model;
class Page extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'page';
  	protected $primaryKey = 'id';
  	public $timestamps = false;

  	public function attachFiles()
    {
  		return $this->hasMany('App\Model\AttachFile', 'parent_id');
    }
    
}
