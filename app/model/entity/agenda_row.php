<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
use App\Model\NormalizeTrait;

class Agenda_Row extends EntityModel{
    use HistorizeTrait;
    use NormalizeTrait;

    protected $validateProperties = [
        'id',
        'debtor_id',
        'contact_id',
        'type',
        'title',
        'todo_date',
        'message',
        'message_for',
        'status',
        'ctime',
        'instance_id',
        'timer',
        'user_id',
        'linked_by',
    ];

    protected $validateAllowHtml = [
        'message',
    ];
    protected $validateFilters = [
        'message'=>[
            ['htmlFilter',
                [ 'a', 'b', 'p', 'strong','br' ], /* allowed tags */
                [ 'title' ] /* allowed attributes on all tags */,
                [ 'a'=> ['href','j-href'] ] /* allowed attributes by tag */,
            ]
        ],
    ];

    function beforePut(){
        $this->todo_date = $this->normalizeDate($this->todo_date);
    }
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
        $this->status = 0;
//        if(PHP_SAPI != 'cli'){
//            $this->user_id = $this->_user->id;
//        }
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
    function afterUpdate(){
        $this->historize();
    }
    function afterDelete(){}
    function afterRecursive(){}

}
