<?php
namespace App\Api;

use Mailgun\Mailgun;
use DateTime;

class LeadMail extends AbstractMail{
	
	use LeadTrait;
	
	function sendEmail($data){
        $param = $this->getMailgunParam($data);
		
		$lead_id = $data['lead_id'];
		
		$lead = $this->db['lead'][$lead_id];
		
        $email = [
            'message'=>$data['message'],
            'user_id'=>$this->user_id,
            'lead_id'	 =>$lead_id,
            'template_id' =>$data['template_id'],
            '_many_email_expediteur'=>[
                [
                    'user_id'=>$data['expediteur'],
                ]
            ],
            
            '_many_email_destinataire'=>[
				[
					'type' => 'to',
					'lead_id' => $lead_id,
					'email' => $lead->debit_email,
				],
			],
        ];


        $entity = $this->db['email']->simpleEntity($email);
        $entity->_one_instance = ['_type'=>'user','id'=>$this->instance_id];
        $entity->message = $param['html'];
        $this->db['email'][] = $entity;

        if (!empty($param)) {
            $mg = new Mailgun($this->mailGunApiKey);
            $mg->sendMessage($this->mailGunDomain, $param);
            $result = $mg->get($this->mailGunDomain.'/log', ['limit' => 25, 'skip' => 0]);
            $httpResponseCode = $result->http_response_code;
            return ['status' => $httpResponseCode];
        } else {
            return ['error'=>"Votre email est vide."];
        }
    }
    
    protected function getMailTo($datas){
		$lead = $this->db['lead'][ $datas['lead_id'] ];
        return $lead->debit_email;
	}
  
	
    function getTemplateRender($message, $datas){
        $lead_id = $datas['lead_id'];
        $lead = $this->db['lead'][$lead_id];
        
        $message = $this->formatTemplate($message, $lead, [
            'BOUTON_PROMESSE' => $this->button_promise($lead_id),
             'TABLEAU' => $this->build_table($lead->_many_lead_invoice),
        ] );
        
        return $message;
    }


    protected function button_promise($id){
        $token = bin2hex(random_bytes(32));
        $this->db['promise_token'][] = ['token' => $token, 'lead_id' => $id];
        $button = '<table cellspacing="0" cellpadding="0"><tr><td align="center" width="300" height="40" bgcolor="#0da4e5" style="-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block;"><a href="'.$this->urlBaseHref.'promise/add-lead?token='.$token.'" style="font-size:16px; font-weight: bold; font-family: Helvetica, Arial, sans-serif; text-decoration: none; line-height:40px; width:100%; display:inline-block"><span style="color: #FFFFFF">Faites une promesse de réglement !</span></a></td></tr></table>';
        return $button;
    }
}
