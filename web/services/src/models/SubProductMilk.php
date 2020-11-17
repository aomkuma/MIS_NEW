<?php

namespace App\Model;

class SubProductMilk extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'subproduct_milk';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
        ,'product_milk_id'
        , 'name'
        , 'product_character'
        , 'agent'
        , 'actives'
        , 'create_date'
        , 'update_date'
        , 'create_by'
        , 'update_by'
    );
    
    public function subProductMilkDetail()
    {
        return $this->hasMany('App\Model\ProductMilkDetail', 'sub_product_milk_id');
    }

}
