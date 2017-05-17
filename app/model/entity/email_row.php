<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\HistorizeTrait;
class Email_Row extends EntityModel{
    use HistorizeTrait;
    
    protected $validateProperties = [
        'id',
        'debtor_id',
        'template_id',
        'message',
        'ctime',
        'instance_id',
        'timer',
        'mail_subject',
    ];
    protected $validateAllowHtml = [
        'message',
    ];
    protected $validateFilters = [
        'message'=>[
            ['htmlFilter',
                [ 'a', 'b', 'p', 'strong','button', 'style', 'br', 'table', 'tr', 'th', 'td', 'tbody', 'thead'], /* allowed tags */
                [ 'title', 'style' ] /* allowed attributes on all tags */,

                [
                    'a'=> ['href'],
                    'img' => ['src'],
                    'table' =>['border', 'cellpadding', 'cellspacing' ]

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

    function getDynamic(){

        $expediteur = $this->_many_email_expediteur->getRow();
        if($expediteur) {
            $user = $expediteur->_one_user;
            $data['v_expediteur'] = $user->first_name.' '.$user->last_name;
        }
        else{
            $data['v_expediteur'] = '';
        }

        return $data;
    }

}
