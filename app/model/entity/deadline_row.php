<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\NormalizeTrait;

class Deadline_Row extends EntityModel{

    use NormalizeTrait;

    protected $validateProperties = [
        'id',
        'schedule_id',
        'amount',
        'date',
        'taux',
        'capital',
        'interets',
        'solde',
        'active',
        'ctime',
        'mtime'
    ];

    function beforePut(){
        $this->date = $this->normalizeDate($this->date);
    }
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
