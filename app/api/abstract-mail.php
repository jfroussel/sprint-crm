<?php
namespace App\Api;

use Mailgun\Mailgun;
use DateTime;
use App\AbstractController;

abstract class AbstractMail extends AbstractController{
    protected $user_id;
    protected $instance_id;

    function __construct($db, $instance_id, $urlBaseHref, $user_id = null, $mailGunApiKey, $mailGunDomain){
        $this->db = $db;
        $this->user_id = $user_id;
        $this->instance_id = $instance_id;
        $this->urlBaseHref = $urlBaseHref;
        $this->mailGunApiKey = $mailGunApiKey;
        $this->mailGunDomain = $mailGunDomain;
    }


    protected function build_table($array){
		if(empty($array)){
			return '';
		}
        $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"5\" >";
        $html .= '<tr>';
        foreach (current($array) as $key => $value) {
            $html .= '<th>' . $key . '</th>';
        }
        $html .= '</tr>';
        foreach ($array as $id => $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    protected function getMailgunParam($datas){

        if ($datas) {

            $message = $datas['message'];
			if (empty($message) || strlen($message) < 2) {
				return ['error' => 'Message vide.'];
			}
            $message = $this->getTemplateRender($message, $datas);
            $user_id = $this->user_id ?? $this->instance_id;
            $from = $this->db['user'][$user_id]->email;
            $to = $this->getMailTo($datas);
            if($datas['mail_subject']){
                $subject = $datas['mail_subject'];
            }else{
                $subject = $this->db['template'][$datas['template_id']]->mail_subject;
            }

            //ddj($subject);

            $param = [
				'from' => '<' . $from . '>',
                'to' => $to,
                'subject' => $subject,
                'html' => $message
            ];

            
            if(isset($datas['destinataireCC'])&&$datas['destinataireCC']){
                $contactCC = $this->db['contact'][$datas['destinataireCC']];
                $param['cc'] = $contactCC->email;
            }
            if(isset($datas['destinataireCCI'])&&$datas['destinataireCCI']){
                $contactBCC = $this->db['contact'][$datas['destinataireCCI']];
                $param['bcc'] = $contactBCC->email;
            }
            
            return $param;
        }
    }
}
