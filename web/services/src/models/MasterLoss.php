<?php

namespace App\Model;

class MasterLoss extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'master_loss';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('id'
        , 'name'
        , 'actives'
        , 'create_date'
        , 'update_date'
    );

}
