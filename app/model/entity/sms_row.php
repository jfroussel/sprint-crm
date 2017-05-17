<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Sms_Row extends EntityModel{
    use HistorizeTrait;
    
    protected $validateProperties = [
        'id',
        'template_id',
        'debtor_id',
        'expediteur',
        'contact_id',
        'message',
        'ctime',
        'mtime',
        'instance_id',
        'timer',
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
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){
        $this->historize();
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
