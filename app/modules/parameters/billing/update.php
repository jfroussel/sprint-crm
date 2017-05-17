<?php

namespace App\Modules\Parameters\Billing;

use App\AbstractController;
use App\Templix\Templix;
use mikehaertl\wkhtmlto\Pdf;

class Update extends AbstractController{

	protected $needAuth = true;
	
	function load($id){
		return [
			'billing'=>$this->db['billing'][$id],
			'company'=>$this->getUserInstance(),
		];
	}
	
	function html2pdf(){
		$templix = $this->di->get(Templix::class);
        $data = [];
        $data['billing'] = $this->db['billing'][$this->request->id];
        $data['company'] = $this->getUserInstance();
        $content = $templix->fetch('billings.tml', $data);
        $filename = urlencode('facture.pdf');
        header("Content-Disposition: attachment; filename=$filename");
        $PDF = new Pdf();
        $PDF->setOptions(['user-style-sheet' =>realpath('css/pdf/bootstrap4.css')]);
        $PDF->addPage($content);
        $PDF->send();
        exit;
    }
    
    protected function getUserInstance(){
		$user = $this->db['user'][$this->user->id];
		return $this->db['user'][$user->instance_id];
	}
}
