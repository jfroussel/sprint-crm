<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;
use Symfony\Component\Console\Helper\ProgressBar;

use Exception;

use SimpleXMLElement;

class MyDebtorUpdatescore extends ArtistPlugin{
	
	protected $description = "Update score routine";
	protected $args = [];
	protected $opts = [];
	protected $boolOpts = [];
	protected $errors = [];

	function __construct($name = null, Redis $redis, MainDb $db){
		parent::__construct($name);
		$this->redis = $redis;
		$this->db = $db;
		$this->user = 'getdata1';
		$this->pw = 'credit';
		$this->url = 'https://www.creditsafe.fr/getdata/service/CSFRServices.asmx/GetData';
	}
	
	protected function exec(){
		
		$c = count($this->db['debtor']->where('siret IS NOT NULL'));
		$progress = new ProgressBar($this->output, $c);
		$progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

		
		foreach($this->db['debtor']->unSelect()->select('id, siret')->where('siret IS NOT NULL') as $id=>$debtor) {

			
			$progress->advance();

            $siret = trim($debtor->siret);
			if(!$siret) continue;

			$siret = preg_replace('/\s+/u', '',$siret);
			$xmlString = "<xmlrequest><header><username>".$this->user."</username><password>".$this->pw."</password><operation>getcompanyinformation</operation><language>FR</language><country>FR</country><chargereference></chargereference></header><body><package>standard</package><companynumber>".$siret."</companynumber></body></xmlrequest>";

			$result = $this->getData($this->url, $xmlString);

			if(!$result){
				continue;
			}
			
			try{
				$datas = simplexml_load_string($result);			
				$contentChildren = $datas->body->children();
			}
			catch(Exception $e){
				$this->logParseError($this->url,$xmlString,$result,$e);
			}
			
			if($contentChildren->errors){
				$details = $contentChildren->errors->errordetail;
				$this->errors[] = "code: {$details->code} desc: {$details->desc}";
				continue;
			}
				
			$score = $contentChildren->company->ratings2013->currentscore[0] ? $contentChildren->company->ratings2013->currentscore[0] : 'NC';
			$letter = $this->parseLetter($score);
			$summary = $contentChildren->company->summary;
            $score_mtime = $summary->time;
			$adress = $summary->numberinroad.' '.$summary->typeofroad.' '.$summary->nameofroad;
			$city = $summary->municipality;
			$postal_code = $summary->postcode;
			$procedures_collectives = '';
			$procedures_date = '';
			$privilege = '';
			$tresor_public = '';
			$urssaf = '';

			if(!empty($score)  && !empty($letter) ) {
				if($score == 'NC') $score = 0;

				$this->db['debtor'][$id] = [
					'score'=>(string)$score,
                    'score_mtime' =>(string)$score_mtime,
					'letter'=>(string)$letter, 
					'adresse'=>(string)$adress,
					'ville'=>(string)$city,
					'code_postal'=>(string)$postal_code,
					'procedures_collectives' =>(string)$procedures_collectives,
					'procedures_date'=>(string)$procedures_date,
					'privilege'=>(string)$privilege,
					'tresor_public'=>(string)$tresor_public,
					'urssaf'=>(string)$urssaf
				];
			}
		}
		
		$progress->finish();
		if(!empty($this->errors)){
			$this->output->writeln('');
			$this->output->writeln('there was '.count($this->errors).' errors :');
			foreach($this->errors as $error){
				$this->output->writeln($error);
			}
		}
		$this->output->writeln('');
		$this->output->writeln('-- Score updated --');
		$this->output->writeln('');
	}

	public function parseLetter($nb){
		if(70 < $nb && $nb < 101){
			return 'A';
		}else if(50 < $nb && $nb < 71){
			return 'B';
		}else if(29 < $nb && $nb < 51){
			return 'C';
		}else if( 20 < $nb && $nb < 30){
			return 'D';
		}else if(0 < $nb && $nb < 21 ){
			return 'E';
		}else if($nb==0){
			return 'F';
		}else if($nb=='NC'){
			return 'G';
		}else{
			return 'G';
		}
	}

	public function getData($url, $data) {
		$defaults = array(
			CURLOPT_POST => 1, 
			CURLOPT_HEADER => "X-Requested-With: XMLHttpRequest",
			CURLOPT_URL => $url, 
			CURLOPT_FRESH_CONNECT => 1, 
			CURLOPT_RETURNTRANSFER => 1, 
			//CURLOPT_FORBID_REUSE => 1, 
			CURLOPT_TIMEOUT => 25,
			CURLOPT_POSTFIELDS => "requestXmlStr={$data}",
			CURLOPT_ENCODING => 'UTF-8',
		);

		$ch = curl_init();
		curl_setopt_array($ch, $defaults);


		if(!$result = @curl_exec($ch)){
			$error = curl_error($ch);
			if($error){
                error_log("$error\n",3,'.tmp/curl-errors.log');
                $this->errors[] = $error;
            }
		}
		curl_close($ch);

        if($result){
			
			try{
				$result = new SimpleXMLElement($result);
				$result->addAttribute('encoding', 'UTF-8');
				$result = str_replace(['&#x0;','l&#x19;'],'',$result);
			}
			catch(Exception $e){
				$this->logParseError($url,$data,$result,$e);
			}
        }


        return $result;
	}
	
	protected function logParseError($url,$data,$result,$e){
		file_put_contents('.tmp/xml_parse_error.log',$e->getMessage()."\n".$url."\n".$data."\n".$result."\n\n",FILE_APPEND);
	}
	
}
