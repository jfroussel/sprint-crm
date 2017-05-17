<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
use App\Model\PaperworkStateChangeTrait;
use DateTime;
use DateInterval;

class Schedule_Row extends EntityModel{
    use HistorizeTrait;
    use PaperworkStateChangeTrait;

    protected $validateProperties = [
        'id',
        'author',
        'amount',
        'debtor_id',
        'type',
        'message',
        'ctime',
        'mtime',
        'base_schedule',
        'nbschedule',
        'timer',
        'user_name',
        'late'
    ];

    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){
    }
    function beforeValidate() {
    }
    function beforeRead(){}
    function beforeUpdate(){
        $this->mtime = $this->now();
    }
    function beforeDelete(){
		$this->unsetRelatedPaperworksState('schedule');
	}
    function afterPut(){}
    function afterCreate(){
        $this->historize();
        $this->setRelatedPaperworksState('schedule');
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
