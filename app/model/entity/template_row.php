<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Template_Row extends EntityModel{
	 protected $validateProperties = [
        'id',
        'name',
        'category',
        'type',
        'active',
        'message',
        'ctime',
        'mtime',
        'mail_subject',
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
    
    function beforePut(){
		if($this->type=='sms'){
			$this->message = html_entity_decode($this->message, ENT_QUOTES);
			$this->message = str_replace(['<br>','<br />','<br/>'],"\n\r",$this->message);
		}
		if($this->type!='email' && isset($this->mail_subject)){
		    $this->mail_subject = null;
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
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){
        
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
