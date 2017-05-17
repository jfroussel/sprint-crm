<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Letter_Row extends EntityModel{
    use HistorizeTrait;
    
    protected $validateProperties = [
        'id',
        'category',
        'debtor_id',
        'template_id',
        'type',
        'message',
        'destinataire',
        'modele',
        'job_primary',
        'job_url',
        'ctime',
        'instance_id',
        'timer',
        'ctime',
        'mtime',
        'affected_time',
    ];
    protected $validateAllowHtml = [
        'message',
    ];
    protected $validateFilters = [
        'message'=>[
            ['htmlFilter',
                [ 'a', 'b', 'p', 'strong', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'em', 'hr', 'br', 'img', 'span', 'div' ], /* allowed tags */
                [ 'title', 'style' ] /* allowed attributes on all tags */,
                [ 'a'=> ['href'],
                    'img' => ['src'],
                ] /* allowed attributes by tag */,
            ]
        ],
    ];
    
    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){

        $this->ctime = $this->now();
    }
    function beforeRead(){}
    function beforeUpdate(){
        $this->mtime = $this->now();
        
        switch($this->status){
			case 'affected':
				$this->affected_time = $this->now();
			break;
		}
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
