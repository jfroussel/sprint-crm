<?php
namespace App\Api;

use DateTime;

class DebtorSms extends AbstractSms{
   
    protected function encours($debtor){
        $debtor_primary = $debtor['primary'];
        //$this->db->debug();
        if ($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
            $result = $this->db['paperwork']
                ->unselect()
                ->select("SUM(credit) - SUM(debit)")
                ->where('debtor_primary = ? AND lettrage IS NULL AND type_ecriture IN ?', [$debtor_primary, ['FACT', 'AVOIR']])
                ->getCell();
            return $result;
        }
    }

    function sendSms($datas){
		
		if(!empty($datas)) {
			
            $contact = $this->db['contact'][$datas['contact_id']];
            $gsm = $contact->portable;
            
            $message = $this->getTemplateRender($datas['message'],$datas);
           
            if(empty($message)||strlen($message)<2){
                return json_encode(array('error' => 'Message vide.'));
            }

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
                    "name" => "MyCreance.com",
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
				'debtor_primary'=>$this->db['debtor'][$datas['debtor_id']]->primary,
				'debtor_id'	 =>$datas['debtor_id'],
				'template_id' =>$datas['template_id'],
				'timer' =>$datas['timer'] ?? '',
			])->store();
            
            return $r;
        }
        else{
            return "Vous n'avez rien envoyé.";
        }
    }
	
	protected function getTemplateRender($message, $datas){
        $id = $datas['debtor_id'];
        $contact = $datas['contact_id'];
        if($contact){
            $contact = $this->db['contact'][$contact];
        }
        $debtor = $this->db['debtor'][$id];
        $paperworks = $this->db['paperwork']->reporting($debtor->primary);
        $creancier = $this->db['user'][$this->instance_id];
        $date = new DateTime();
        $encours = $this->encours($debtor);

        $varData = [
            'DATE_JOUR' => $date->format('d/m/Y'),
            'C_RAISON_SOCIALE' => strtoupper($creancier->company_name),
            'C_ID',
            'C_ADRESSE' => $creancier->address,
            'C_VILLE' => $creancier->city,
            'C_CODE_POSTAL' => $creancier->postal_code,
            'C_FORME_JURIDIQUE',
            'C_SIRET',
            'C_NR_TVA',
            'GEST_TITRE',
            'GEST_NOM' => $creancier->last_name,
            'GEST_PRENOM' => $creancier->first_name,
            'GEST_TELEPHONE' => $creancier->phone,
            'GEST_MOBILE',
            'GEST_EMAIL' => $creancier->email,
            'GEST_FONCTION',
            'GEST_ADRESSE' ,
            'GEST_CODE_POSTAL' => $debtor->code_postal,
            'GEST_VILLE',
            'D_ID',
            'D_CIVILITE',
            'D_RAISON_SOCIALE' => $debtor->nom_client,
            'D_NOM' => $contact?$contact->first_name:null,
            'D_PRENOM' => $contact?$contact->last_name:null,
            'D_BLOC_ADRESSE' => $debtor->adresse,
            'D_VILLE' => $debtor->ville,
            'D_CODE_POSTAL' => $debtor->code_postal,
            'D_FORME_JURIDIQUE',
            'D_SIRET',
            'D_NR_TVA',
            'D_EMAIL',
            'D_TELEPHONE',
            'D_FAX',
            'D_CAPITAL',
            'D_PROCEDURE_COLLECTIVE',
            'D_DATE_PROCEDURE_COLLECTIVE',
            'D_PRIVILEGE_TRESOR_PUBLIC',
            'D_PRIVILEGE_URSSAF',
            'D_NOTE_SCORING',
            'D_LETTRE_SCORING',
            'D_LIMITE_DE_CREDIT',
            'ENCOURS',
            'ENCOURS ECHU',
            'ENCOURS NON ECU',
            'INTERET DE RETARD',
            'CLAUSE PENALE',
            'PENALITES DE RETARD',
            'IFR',
            'FR',
            'TOTAL FRAIS DE RETARD',
            'BOUTON_PROMESSE'
        ];


        foreach ($varData as $k => $v) {
            $message = str_replace('{{' . $k . '}}', $v, $message);
        }

        return $message;
    }

}
