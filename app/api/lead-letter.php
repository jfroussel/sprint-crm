<?php
namespace App\Api;
use mikehaertl\wkhtmlto\Pdf;

class LeadLetter extends AbstractLetter{
    
    use LeadTrait;

    function sendLetter($lead_id,$type,$message,$template_id=null,$timer=null){

        $instance = $this->db['user'][$this->instance_id];
        $lead = $this->db['lead'][$lead_id];

        $message = $this->getTemplateRender($message, [
            'lead_id'=>$lead_id,
            'instance_id'=>$this->instance_id,
        ]);

        $entity = $this->db['letter']->simpleEntity([
            'lead_id'=>$lead_id,
            'type'=>$type,
            'message'=>$message,
            'template_id'=>$template_id,
        ]);


        $param = [
            'lead'=>$lead,
            'message'=>$message,
            'user'=>$instance,
        ];
        $ref = $lead_id.'/'.$this->instance_id;


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
            $lead->debit_first_name,
            $lead->debit_last_name,
            $lead->debit_name,
            $lead->debit_address,
            "",
            $lead->debit_zip_code,
            $lead->debit_city,
            //$lead->country
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
        
        if(trim($message)){
			$this->servicePostal($ref, $type, $file, $exp, $dest);
        }

        $entity->job_primary = $this->jobID;
        $entity->job_url = $this->jobURL;
        $this->db['letter'][] = $entity;

        rename($file, $dir.$entity->id.'.pdf');

        return $file;
        // cablé api courrier

    }

    function getTemplateRender($message, $data){
		
		$lead = $this->db['lead'][ $data['lead_id'] ];
		
        $logo = realpath('content/user/'.$data['instance_id'].'/avatar.png');
        
        $message = $this->formatTemplate($message, $lead, [
            'LOGO' => '<img src="'.$logo.'" width="120"  title="Logo" alt="Logo" />',
            'BAS_DE_PAGE' => 'bas de page de test',
             'TABLEAU' => $this->build_table($lead->_many_lead_invoice),
        ] );

        return $message;
    }
}
