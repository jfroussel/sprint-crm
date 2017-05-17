<?php
namespace App\Api;

use DateTime;

trait LeadTrait{
	protected function encours($lead){
		$result = $this->db['lead_invoice']
			->unselect()
			->select("SUM(restant)")
			->where('lead_id = ?', [ $lead->id ])
			->getCell();
		return $result;
    }
    protected function formatTemplate($message,$lead,array $extend=[]){
		$varData = $extend+$this->getCommonTemplateVars($lead);
        foreach ($varData as $k => $v) {
            $message = str_replace('{{' . $k . '}}', $v, $message);
        }
        return $message;
	}
    protected function getCommonTemplateVars($lead){
		$user = $lead->_one_user;
		
		$date = new DateTime();
		$data = [
            'DATE_JOUR' => $date->format('d/m/Y'),
            'C_RAISON_SOCIALE' => strtoupper($lead->corporate_name),
            
            'C_ADRESSE' => $lead->address,
            'C_VILLE' => $lead->city,
            'C_CODE_POSTAL' => $lead->zip_code,
            'C_FORME_JURIDIQUE' => $lead->profile_type,
            'C_SIRET' => $lead->siren,
            'C_NR_TVA' => $lead->tva,
            'GEST_TITRE' => '',
            'GEST_NOM' => $lead->last_name,
            'GEST_PRENOM' => $lead->first_name,
            'GEST_TELEPHONE' => $lead->phone,
            'GEST_MOBILE' => $lead->mobile_phone,
            'GEST_EMAIL' => $user->email,
            'GEST_FONCTION' => '',
            'GEST_ADRESSE' => $lead->address,
            'GEST_CODE_POSTAL' => $lead->zip_code,
            'GEST_VILLE' => $lead->city,
            
            'D_ID' =>$lead->id,
            'D_RAISON_SOCIALE' => $lead->debit_name,
            'D_NOM' => $lead->debit_last_name,
            'D_PRENOM' => $lead->debit_first_name,
            'D_BLOC_ADRESSE' => $lead->debit_address,
            'D_VILLE' => $lead->debit_city,
            'D_CODE_POSTAL' => $lead->debit_zip_code,
            'D_FORME_JURIDIQUE' => '',
            'D_SIRET' =>$lead->debit_siren,
            'D_NR_TVA'=>$lead->debit_tva,
            'D_EMAIL' =>$lead->debit_email,
            'D_TELEPHONE' =>$lead->debit_phone,
            'D_FAX' =>'',
            'D_CAPITAL'=>'',
            'D_PROCEDURE_COLLECTIVE' =>'',
            'D_DATE_PROCEDURE_COLLECTIVE' =>'',
            'D_PRIVILEGE_TRESOR_PUBLIC'=>'',
            'D_PRIVILEGE_URSSAF'=>'',
            'D_NOTE_SCORING' =>'',
            'D_LETTRE_SCORING' =>'',
            'D_LIMITE_DE_CREDIT'=>'',
            'ENCOURS' =>$this->encours($lead),           
           
            'CLAUSE PENALE'=>'',
            
            'ENCOURS ECHU' =>'',
            'ENCOURS NON ECHU' =>'',
            'INTERET DE RETARD' =>'',
            'PENALITES DE RETARD' =>'',
            'IFR' =>'',
            'FR' =>'',
            'TOTAL FRAIS DE RETARD' =>'',
           
        ];
        return $data;
	}
	protected function getSuperrootInstanceId(){
		return $this->db['user']->unSelect()->select('id')->where('is_superroot = 1')->getCell();
	}
}
