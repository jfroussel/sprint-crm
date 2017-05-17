<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Note_Row extends EntityModel{
    use HistorizeTrait;
    
    protected $validateProperties = [
        'id',
        'debtor_id',
        'type',
        'title',
        'todo_date',
        'message',
        'message_for',
        'user_id',
        'status',
        'ctime',
        'timer',

    ];
    
    protected $validateAllowHtml = [
		'message',
	];
    protected $validateFilters = [
		'message'=>[
			['htmlFilter',
				[ 'a', 'b', 'p', 'strong' ], /* allowed tags */
				[ 'title' ] /* allowed attributes on all tags */,
				[ 'a'=> ['href'] ] /* allowed attributes by tag */,
			]
		],
    ];
    
    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
        $this->status = 0;
        
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
