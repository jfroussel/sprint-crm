<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Billing_Row extends EntityModel{
    protected $validateProperties = [
        'id',
        'ref',
        'billingDate',
        'firstdayOfMonth',
        'lastDayOfMonth',
        'sms',
        'smsPrice',
        'email',
        'emailPrice',
        'simpleletter',
        'simpleletterPrice',
        'registeredletter',
        'registeredletterPrice',
        'plan',
        'total',
        'tva',
        'totalttc',
        'active',

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
    function afterCreate(){}
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
