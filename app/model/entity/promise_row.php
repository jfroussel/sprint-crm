<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
use App\Model\PaperworkStateChangeTrait;
use App\Model\NormalizeTrait;
use App\Model\SendEmailTrait;

class Promise_Row extends EntityModel{
    use HistorizeTrait;
    use PaperworkStateChangeTrait;
    use NormalizeTrait;
    use SendEmailTrait;
    
    protected $validateProperties = [
        'id',
        'instance_id',
        'debtor_id',
        'contact_id',
        'amount',
        'date_reglement',
        'solutionner',
        'payment_type',
        'message',
        'ctime',
        'timer',
        'user_id',
    ];


    function beforePut(){
		$this->date_reglement = $this->normalizeDate($this->date_reglement);
		$this->solutionner = $this->normalizeDate($this->solutionner);
		if(isset($this->debit)){
			$this->debit = $this->normalizeDecimal($this->debit);
		}
		if(isset($this->credit)){
			$this->credit = $this->normalizeDecimal($this->credit);
		}
		if(isset($this->amount)){
			$this->amount = $this->normalizeDecimal($this->amount);
		}
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
		$this->unsetRelatedPaperworksState('promise');
	}
    function afterPut(){}
    function afterCreate(){
        $this->historize();
		$this->setRelatedPaperworksState('promise');
		$data = [
		    'id' => $this->id,
            'instance_id' => $this->instance_id,
            'debtor_id' => $this->debtor_id,
            'contact_id' => $this->contact_id,
            'amount' =>$this->amount,
            'date_reglement' =>$this->date_reglement,
            'solutionner' =>$this->solutionner,
            'payment_type' =>$this->payment_type,
            'message' =>$this->message,
            'ctime' =>$this->ctime,
            'user_id' =>$this->user_id,
        ];

		$this->sendEmail($data);

    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){
	}
    function afterRecursive(){}

}
