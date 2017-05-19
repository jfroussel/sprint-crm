<?php
namespace App\Api;

use DateTime;

class LeadSms extends AbstractSms{
   
   use LeadTrait;

   function sendSms($datas){
		if(empty($datas)){
			return "Vous n'avez rien envoyé.";
		}
			
		$lead_id = $datas['lead_id'];
		$lead = $this->db['lead'][$lead_id];
		
		$gsm = $lead->debit_mobile;
		
		$message = $this->getTemplateRender($datas['message'],$datas);
	   
		//create header for cUrl request
		$header = array();
		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: ' . $this->apiKey;

		$b = substr($gsm, 0, 2);
		//create body for cUrl request
		$params = array(
			"details" => array(
				"unsubscribe_text" => "\r\n <small>desinscription</small>",
				"can_unsubscribe" => false,
				"name" => "desico.fr",
				"from_name" => '',
				"content" => $message
			),
			"scheduling" => array(
				"send_now" => true
			),
			"mobiles" => array(
				array(
					"phone_number" =>  $b == '06' || $b == '07' ? '+33'.substr($gsm, count($gsm) - 1) : $gsm
				)
			)
		);
		
		$r = $this->curlSender($this->apiUrl, $params,$header);
		
		$this->db['sms']->entity([
			'message'=>$message,
			'user_id'=>$this->user_id,
			'_one_instance'=>['_type'=>'user','id'=>$this->instance_id],
			'lead_id'	 =>$lead_id,
			'template_id' =>$datas['template_id'],
		])->store();
		
		return $r;
    }
	
	protected function getTemplateRender($message, $datas){
        $lead_id = $datas['lead_id'];
        $lead = $this->db['lead'][$lead_id];
        $message = $this->formatTemplate($message, $lead, [] );
        return $message;
    }
}
