<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
use App\Model\PaperworkStateChangeTrait;

class Litige_Row extends EntityModel{
    use HistorizeTrait;
    use PaperworkStateChangeTrait;
    
    protected $validateProperties = [
        'id',
        'debtor_id',
        'type',
        'amount',
        'solutionner',
        'contact_id',
        'message',
        'ctime',
        'timer',
        'user_id',
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
    function beforeDelete(){
		$this->unsetRelatedPaperworksState('litigation');
	}
    function afterPut(){}
    function afterCreate(){
        $this->setRelatedPaperworksState('litigation');
        $this->historize();
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
