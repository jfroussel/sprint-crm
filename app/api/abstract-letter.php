<?php

namespace App\Api;

use SP\Session as SP;
use App\Templix\Templix;

use SP\Options\{Couleur, EnveloppeImprimanteMode,Enveloppe,Recto,PorteAdresse,Affranchissement};

use App\AbstractController;

use RedCat\Strategy\Di;
 
abstract class AbstractLetter extends AbstractController{
    public $jobID;
    public $jobURL;

    function __construct($db, $instance_id, Templix $templix, Di $di){
        $this->db = $db;
        $this->instance_id = $instance_id;
        $this->templix = $templix;
        $this->di = $di;
    }

    protected function build_table($array){
		if(empty($array)){
			return '';
		}
//        $html = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"5\" style='font-size: 10px' >";
        $html = "<table class='table table-bordered'>";

        $html .= '<tr>';
        //ddj($array);
        foreach (current($array) as $key => $value) {

            $html .= '<th>' . $key . '</th>';
        }
        $html .= '</tr>';
        foreach ($array as $key => $value) {
            $html .= '<tr>';
            foreach ($value as $key2 => $value2) {
                $html .= '<td>' . $value2 . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    protected function servicePostal($ref, $type, $file, $exp, $dest){
		
		$this->sp = $this->di->get(SP::class);
		
		switch($type){
			case 'LETTRE_RECOMMANDEE':
				$affranchissement = Affranchissement::LETTRE_RECOMMANDEE;
			break;
			case 'LETTRE_RECOMMANDEE_AVEC_AR':
				$affranchissement = Affranchissement::LETTRE_RECOMMANDEE_AVEC_AR;
			break;
			case 'LETTRE_PRIORITAIRE':
				$affranchissement = Affranchissement::LETTRE_PRIORITAIRE;
			break;
			case 'LETTRE_ECOPLI':
				$affranchissement = Affranchissement::LETTRE_ECOPLI;
			break;
			default:
			case 'LETTRE_VERTE':
				$affranchissement = Affranchissement::LETTRE_VERTE;
			break;
        }


        
		$job = $this->sp->nouveauLettreJob()
			->setImpression(
			    Couleur::NOIR_ET_BLANC,
                Enveloppe::AUTO,
                EnveloppeImprimanteMode::PRINTED,
				Recto::RECTO_VERSO,
				PorteAdresse::INACTIF)
			->setAffranchissement($affranchissement)
			->setReferenceExterne($ref)
			->setExpediteur(...$exp)
			->setDestinataire(...$dest)
			->setDocument($file);

        $letterPreviewResult = $job->preparer();

        $this->jobID = $job->jobID;

        $this->jobURL = $letterPreviewResult->spOutputFile->spURL;


        $job->envoyerDirectement();

        //echo "Le courrier a été envoyé sur la plate-forme Service postal et porte le numéro ", $job->jobID;
        $this->sp->logout();

    }
}
