<?php
namespace App\Api;

use Mailgun\Mailgun;
use DateTime;

class DebtorMail extends AbstractMail{

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
	
	protected function getMailTo($datas){
		$contact = $this->db['contact'][$datas['contact_id']];
        return $contact->email;
	}

    function sendEmail($data){
        $param = $this->getMailgunParam($data);

        $email_destinataire = [
            [
                'type' => 'to',
                'contact_id' => $data['contact_id'],
            ],
        ];
        if ($data['destinataireCC']) {
            $email_destinataire[] = [
                'type' => 'cci',
                'contact_id' => $data['destinataireCCI'],
            ];
        }
        if ($data['destinataireCCI']) {
            $email_destinataire[] = [
                'type' => 'cc',
                'contact_id' => $data['destinataireCCI'],
            ];
        }

        $email = [
            'message'=>$data['message'],
            'user_id'=>$this->user_id,
            'debtor_primary'=>$this->db['debtor'][$data['debtor_id']]->primary,
            'debtor_id'	 =>$data['debtor_id'],
            'template_id' =>$data['template_id'],
            '_many_email_expediteur'=>[
                [
                    'user_id'=>$data['expediteur'],
                ]
            ],
            '_many_email_destinataire'=>$email_destinataire,
            'timer'=>$data['timer'] ?? '',

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

    function getTemplateRender($message, $datas){
        $id = $datas['debtor_id'];
        $contact = $datas['contact_id'];
        //ddj($datas);
        if($contact){

            $contact = $this->db['contact'][$contact];
        }
        $debtor = $this->db['debtor'][$id];

        $creancier = $this->db['user'][$this->instance_id];
        $date = new DateTime();
        $encours = $this->encours($debtor);
		


        $varData = [
            'DATE_JOUR' => $date->format('d/m/Y'),
            'C_RAISON_SOCIALE' => strtoupper($creancier->company_name),
            'C_ADRESSE' => $creancier->address,
            'C_VILLE' => $creancier->city,
            'C_CODE_POSTAL' => $creancier->postal_code,
            'C_FORME_JURIDIQUE' => $creancier->legal_form,
            'C_SIRET' => $creancier->siret_code,
            'C_NR_TVA' => $creancier->tva_code,
            'GEST_TITRE' => $creancier->civility,
            'GEST_NOM' => $creancier->last_name,
            'GEST_PRENOM' => $creancier->first_name,
            'GEST_TELEPHONE' => $creancier->phone,
            'GEST_MOBILE' => $creancier->cellphone,
            'GEST_EMAIL' => $creancier->email,
            'GEST_FONCTION' => $creancier->function,
            'GEST_ADRESSE' => $creancier->address,
            'GEST_CODE_POSTAL' => $creancier->postal_code,
            'GEST_VILLE' => $creancier->city,
            'D_ID' =>$debtor->primary,
            'D_RAISON_SOCIALE' => $debtor->nom_client,
            'D_NOM' => $contact?$contact->nom:null,
            'D_PRENOM' => $contact?$contact->prenom:null,
            'D_BLOC_ADRESSE' => $debtor->adresse,
            'D_VILLE' => $debtor->ville,
            'D_CODE_POSTAL' => $debtor->code_postal,
            'D_FORME_JURIDIQUE' => '',
            'D_SIRET' =>$debtor->siret,
            'D_NR_TVA'=>'',
            'D_EMAIL' =>$contact?$contact->email:null,
            'D_TELEPHONE' =>$contact?$contact->tel:null,
            'D_FAX' =>$contact?$contact->fax:null,
            'D_CAPITAL'=>'',
            'D_PROCEDURE_COLLECTIVE' =>$debtor->procedures_collectives,
            'D_DATE_PROCEDURE_COLLECTIVE' =>$debtor->procedures_date,
            'D_PRIVILEGE_TRESOR_PUBLIC'=>'',
            'D_PRIVILEGE_URSSAF'=>'',
            'D_NOTE_SCORING' =>$debtor->score,
            'D_LETTRE_SCORING' =>$debtor->letter,
            'D_LIMITE_DE_CREDIT'=>'',
            'ENCOURS' =>$encours,           
           

            
            'BOUTON_PROMESSE' => $this->button_promise($id),

        ];

        foreach ($varData as $k => $v) {
            $message = str_replace('{{' . $k . '}}', $v, $message);
        }

        return $message;
    }

    protected function button_promise($id){
        $token = bin2hex(random_bytes(32));
        $this->db['promise_token'][] = ['token' => $token, 'debtor_id' => $id];
        $button = '<table cellspacing="0" cellpadding="0"><tr><td align="center" width="300" height="40" bgcolor="#0da4e5" style="-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block;"><a href="'.$this->urlBaseHref.'promise/add?token='.$token.'" style="font-size:16px; font-weight: bold; font-family: Helvetica, Arial, sans-serif; text-decoration: none; line-height:40px; width:100%; display:inline-block"><span style="color: #FFFFFF">Promesse de paiement !</span></a></td></tr></table>';
        return $button;
    }
}
