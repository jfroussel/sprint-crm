<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Scenario_Row extends EntityModel{
    protected $validateProperties = [
        'id',
        'name',
        'letter',
        'type',
        'category',
        'active',
        'start_day',
        'template_id',
        'ctime',
        'mtime',
    ];
    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
    }
    function beforeRead(){}
    function beforeUpdate(){
        $this->ctime = $this->now();
    }
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){}
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
