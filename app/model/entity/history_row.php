<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class History_Row extends EntityModel{

    protected $validateAllowHtml = [
        'message',
    ];
    protected $validateFilters = [

    ];

    function beforePut(){ }
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();

    }
    function beforeRead(){}
    function beforeUpdate(){}
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){
	}
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

    function getDynamic(){
        switch ($this->table){
            case 'letter':
                $str = '<a href="'.$this->job_url.'" target="_blank" class="comment-link">Visualiser le document</a>';
                break;
            case 'email':
                $str = '<a href="#history/update?id='.$this->id.'"  class="comment-link">Visualiser l\'email envoyé</a>';
                break;
            default:
                $str = $this->message;
                break;

        }
        if($this->user_id){
            $user = $this->db['user'][$this->user_id];
            $this->dynamic->virtual_user_name = $user->last_name.' '.$user->first_name;
        }



        $this->dynamic->virtual_comment = $str;

        switch($this->table){
            case 'letter':
                $this->dynamic->virtual_category = 'courrier';
                break;
            case 'sms':
                $this->dynamic->virtual_category = 'sms';
                break;
            case 'email':
                $this->dynamic->virtual_category = 'email';
                break;
            case 'litige':
                $this->dynamic->virtual_category = 'litige';
                break;
            case 'note':
                $this->dynamic->virtual_category = 'note';
                break;
            case 'agenda':
                $this->dynamic->virtual_category = 'agenda';
                break;
            case 'promise':
                $this->dynamic->virtual_category = 'promesse';
                break;
            case 'payment':
                $this->dynamic->virtual_category = 'reglement';
                break;
        }
        return (array)$this->dynamic;
    }

}
