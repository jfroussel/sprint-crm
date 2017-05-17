<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Journaux_Row extends EntityModel{
    use HistorizeTrait;

    protected $validateProperties = [
        'id',
        'code_journal',
        'category_journal',
        'if_debit',
        'if_credit',
        'ctime'
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
