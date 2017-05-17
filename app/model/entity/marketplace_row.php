<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Marketplace_Row extends EntityModel{
	protected $validateProperties = [
        'id',
        'name',
        'type',
        'category',
        'active',
        'ctime'
    ];
    
    function beforeCreate(){
        $this->ctime = $this->now();
    }
    function beforeUpdate(){
        $this->mtime = $this->now();
    }

}
