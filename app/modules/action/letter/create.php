<?php
namespace App\Modules\Action\Letter;

use App\Model\Db;
use RedCat\Route\Request;
use App\Route\User;

use RedCat\Strategy\Di;
use App\AbstractController;
use App\Api\DebtorLetter;

class Create extends AbstractController
{
	protected $needAuth = true;
	
    function __construct(Di $di, Db $db ,Request $request, User $user){
        $this->di = $di;
        $this->db = $db;
        $this->request = $request;
        $this->user = $user;
        
        
    }

	function load($debtor_id){		
		return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),
			'templatesList'	=>	$this->db['template']->where('type = ? OR type = ? OR type = ? OR type = ? OR type = ?',['LETTRE_VERTE','LETTRE_ECOPLI', 'LETTRE_PRIORITAIRE','LETTRE_RECOMMANDEE_AVEC_AR', 'LETTRE_RECOMMANDEE']),
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'letter'         =>  [
				'debtor_id'       =>$debtor_id,
			 ],
		];		
	}
	
    function send($debtor_id,$type,$message,$contact_id,$template_id=null,$timer=null){
        return $this->getLetter()->sendLetter($debtor_id,$type,$message,$contact_id,$template_id,$timer);
    }

    function getTemplateRender($message, $datas){
        return $this->getLetter()->getTemplateRender($message, $datas);
    }

	protected function getLetter(){
		return $this->di->get(DebtorLetter::class,[
            'db'=>$this->db,
            'instance_id'=>$this->user->instance_id,
        ]);
	}
	
    function getUserInfo(){
        //$this->db->debug();
        $user = $this->db['user'][$this->user->instance_id];
        return $user;
    }

}
