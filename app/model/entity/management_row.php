<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
use App\Model\PaperworkStateChangeTrait;

class Management_Row extends EntityModel{
    use HistorizeTrait;
    use PaperworkStateChangeTrait;
    
    protected $validateProperties = [
        'debtor_id',
        'user_id',
        'lead_id',

    ];
    
    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
    }
    function beforeRead(){}
    function beforeUpdate(){
        $this->mtime = $this->now();
    }
    function beforeDelete(){
		
	}
    function afterPut(){}
    function afterCreate(){
        
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
