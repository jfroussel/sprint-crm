<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Payment_Row extends EntityModel{
    use HistorizeTrait;

    protected $validateProperties = [
        'id',
        'debtor_id',
        'type',
        'folder_number',
        'invoice_number',
        'amount',
        'payment_type',
        'payment_ref',
        'message',
        'ctime',
        'timer',
        'user_id',
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
