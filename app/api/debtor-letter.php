<?php
namespace App\Api;
use mikehaertl\wkhtmlto\Pdf;

class DebtorLetter extends AbstractLetter{
    
    protected function encours($debtor){
        $debtor_primary = $debtor['primary'];
        //$this->db->debug();
        if ($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
            $result = $this->db['paperwork']
                ->unselect()
                ->select("SUM(credit) - SUM(debit)")
                ->where('debtor_primary = ? AND lettrage IS NULL AND type_ecriture IN ?', [$debtor_primary, ['FACT', 'AVOIR', 'REGLT', 'REGUL', 'OD']])
                ->getCell();
            return $result;
        }
    }


    function sendLetter($debtor_id,$type,$message,$contact_id,$template_id=null,$timer=null){

        if(!trim($message)){
            return ['error'=>"Votre courrier est vide."];
        }

        $instance = $this->db['user'][$this->instance_id];
        $debtor = $this->db['debtor'][$debtor_id];

        $message = $this->getTemplateRender($message, [
            'debtor_id'=>$debtor_id,
            'contact_id'=>$contact_id,
        ]);



        $entity = $this->db['letter']->simpleEntity([
            'debtor_id'=>$debtor_id,
            'type'=>$type,
            'message'=>$message,
            'contact_id'=>$contact_id,
            'template_id'=>$template_id,
            'timer'=>$timer,
        ]);


        $param = [
            'debtor'=>$debtor,
            'message'=>$message,
            'contact'=>$contact_id,
            'user'=>$instance,
        ];
        $ref = $debtor_id.'/'.$this->instance_id;


        $exp =[
            "",
            "",
            $instance->company_name,
            $instance->address,
            "",
            $instance->postal_code,
            $instance->city,
            $instance->country
        ];

        $dest =[
            "",
            "",
            $debtor->nom_client,
            $debtor->adresse,
            "",
            $debtor->code_postal,
            $debtor->ville,
            //$debtor->pays
        ];

        $content = $this->templix->fetch('letters.tml', $param);
        $PDF = new Pdf();
        $PDF->setOptions(['user-style-sheet' =>realpath('css/pdf/bootstrap4.css')]);
        $PDF->addPage($content);

        $dir = '.data/content/'.$this->instance_id.'/letter/';
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }

        $file = $dir.uniqid('tmp').'.pdf';
        $PDF->saveAs($file);
        $this->servicePostal($ref, $type, $file, $exp, $dest);

        $entity->job_primary = $this->jobID;
        $entity->job_url = $this->jobURL;
        $this->db['letter'][] = $entity;

        rename($file, $dir.$entity->id.'.pdf');

        return $file;
        // cablé api courrier

    }

    function getTemplateRender($message, $datas){

        $id = $datas['debtor_id'];
        $contact = $datas['contact_id'];
        if($contact){
            $contact = $this->db['contact'][$contact];
        }
        $debtor = $this->db['debtor'][$id];
        $paperworks = $this->db['paperwork']->reporting($debtor->primary);
        $creancier = $this->db['user'][$this->instance_id];
        $date = new \DateTime();
        $encours = $this->encours($debtor);
		
		$debtorInfo = $this->db['paperwork']->getOneDebtorInfo($id);
        $logo = realpath('content/user/'.$creancier->instance_id.'/avatar.png');
        $varData = [
            'DATE_JOUR' => $date->format('d/m/Y'),
            'LOGO' => '<img src="'.$logo.'" width="120"  title="Logo" alt="Logo" />',
            'BAS_DE_PAGE' => 'bas de page de test',
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
            'D_FORME_JURIDIQUE',
            'D_SIRET' =>$debtor->siret,
            'D_NR_TVA',
            'D_EMAIL' =>$contact?$contact->email:null,
            'D_TELEPHONE' =>$contact?$contact->tel:null,
            'D_FAX' =>$contact?$contact->fax:null,
            'D_CAPITAL'=>$contact?$contact->fax:null,
            'D_PROCEDURE_COLLECTIVE' =>$debtor->procedures_collectives,
            'D_DATE_PROCEDURE_COLLECTIVE' =>$debtor->procedures_date,
            'D_PRIVILEGE_TRESOR_PUBLIC',
            'D_PRIVILEGE_URSSAF',
            'D_NOTE_SCORING' =>$debtor->score,
            'D_LETTRE_SCORING' =>$debtor->letter,
            'D_LIMITE_DE_CREDIT',
            'ENCOURS' =>$encours,
            
            'ENCOURS ECHU' =>$debtorInfo['oneSoldeEchu'],
            'ENCOURS NON ECHU' =>$debtorInfo['oneSoldeEchu'],
            'INTERET DE RETARD' =>$debtorInfo['oneIr'],
            'CLAUSE PENALE'=>'',
            'PENALITES DE RETARD' =>$debtorInfo['onePr'],
            'IFR' =>$debtorInfo['oneIfr'],
            'FR' =>$debtorInfo['oneFr'],
            'TOTAL FRAIS DE RETARD' =>$debtorInfo['totalPenalities'],
            
            'TABLEAU' => $this->build_table($paperworks)
        ];

        foreach ($varData as $k => $v) {
            $message = str_replace('{{' . $k . '}}', $v, $message);
        }
        return $message;
    }
}
