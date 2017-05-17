<?php

namespace App\Modules\Lead;
use App\Model\Db;
use App\Templix\Templix;
use RedCat\Route\Request;
use RedCat\Route\Url;
use RedCat\Route\SilentProcess;
use FoxORM\MainDb;
use RedCat\FileIO\Uploader;
use RedCat\Identify\PHPMailer;
use InvalidArgumentException;
use App\Route\Route;
use App\Model\PriceCalculator;
use Stripe\Stripe;
use App\Model\Lead;

use App\Lead\Lead as LeadController;

class Simulator extends LeadController {
	
	protected $filesPath = 'content/root/lead_invoice/';
    function __invoke(Templix $templix, Request $request, MainDb $db, Route $route, PriceCalculator $priceCalculator){
		$data = [];
		
		$token = $request->lead;
		
		$lead = $this->token2row($token);
		$this->tokenSetCookie($lead->email,$token);
        
        $data['lead'] = $lead;
        
        switch ($lead->category) {
            case Lead::CHEQUE_IMPAYE:
				$data['procedureCosts'] = $priceCalculator->getCiJudiciaireProcedure($lead->amount);
				$templix('lead/simulatorCi.tml',$data);
            break;
            case Lead::LOYER_IMPAYE:
				$data['procedureCosts'] = [
					'A'=>$priceCalculator->getLiPreContentieuxProcedure($lead->amount),
					'B'=>$priceCalculator->getLiAssignationSaisieProcedure($lead->amount),
					'C'=>$priceCalculator->getLiAssignationExpulsionProcedure($lead->amount),
				];
				$data['honoraryCosts'] = [
					'A'=>$priceCalculator->getLiPreContentieuxHonorary($lead->amount),
					'B'=>$priceCalculator->getLiAssignationSaisieHonorary($lead->amount),
					'C'=>$priceCalculator->getLiAssignationExpulsionHonorary($lead->amount),
				];
                $templix('lead/simulatorLi.tml',$data);
            break;
            case Lead::CREANCE_COMMERCIALE:
				$data['procedureCosts'] = [
					'A' => $priceCalculator->getCcAmiableProcedure($lead->amount,$lead->seniority),
					'B' => $priceCalculator->getCcPreContentieuxProcedure($lead->amount,$lead->seniority),
					'C' => $priceCalculator->getCcInjonctionDePayerProcedure($lead->amount,$lead->seniority),
					'D' => $priceCalculator->getCcAssignationEnRefereProcedure($lead->amount,$lead->seniority),
				];
				$data['honoraryCosts'] = [
					'A' => $priceCalculator->getCcAmiableHonorary($lead->amount,$lead->seniority),
					'B' => $priceCalculator->getCcPreContentieuxHonorary($lead->amount,$lead->seniority),
					'C' => $priceCalculator->getCcInjonctionDePayerHonorary($lead->amount,$lead->seniority),
					'D' => $priceCalculator->getCcAssignationEnRefereHonorary($lead->amount,$lead->seniority),
				];
                $templix('lead/simulatorCc.tml',$data);
            break;
            case Lead::REACTIVATION_CREANCE:
				$data['honoraryCosts'] = [
					'A' => $priceCalculator->getRcAmiableHonorary($lead->amount,$lead->seniority),
					'B' => $priceCalculator->getRcPreContentieuxHonorary($lead->amount,$lead->seniority),
					'C' => $priceCalculator->getRcInjonctionDePayerHonorary($lead->amount,$lead->seniority),
					'D' => $priceCalculator->getRcAssignationAuFondHonorary($lead->amount,$lead->seniority),
				];
				$data['procedureCosts'] = [
					'A' => $priceCalculator->getRcAmiableProcedure($lead->amount,$lead->seniority),
					'B' => $priceCalculator->getRcPreContentieuxProcedure($lead->amount,$lead->seniority),
					'C' => $priceCalculator->getRcInjonctionDePayerProcedure($lead->amount,$lead->seniority),
					'D' => $priceCalculator->getRcAssignationAuFondProcedure($lead->amount,$lead->seniority),
				];
                $templix('lead/simulatorRc.tml',$data);
            break;
            default:
                $route->redirect('');
            break;
        }
        return false;
    }
	
	function selectLeadType($token, $type, MainDb $db){
		$lead = $this->token2row($token);
		$lead->type = $type;
		$db['lead'][] = $lead;
	}

    function addStep1(Request $request, MainDb $db){
		$id = $this->token2id($request->lead);
		
        $data = [];

        //$lead = $db->simpleEntity('lead');
        $lead = $db['lead'][$id];
        $lead->profil_type = $request->profil_type;
        $lead->corporate_name = $request->corporate_name;
        $lead->last_name = $request->last_name;
        $lead->first_name = $request->first_name;
        $lead->address = $request->address;
        $lead->zip_code = $request->zip_code;
        $lead->city = $request->city;
        $lead->country = $request->country;
        $lead->corporate_email = $request->corporate_email;
        
        $lead->phone = $request->phone;
        $lead->mobile_phone = $request->mobile_phone;
        $lead->fax = $request->fax;
        
        $lead->phone_prefix = $request->phone_prefix;
        $lead->mobile_phone_prefix = $request->mobile_phone_prefix;
        $lead->fax_prefix = $request->fax_prefix;
        
        $lead->price_category = $request->price_category;
        
        $lead->mandat_number = $this->mandat($lead->category);
		
        try{
            $db['lead'][$id] = $lead;
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }
		
        $data['lead'] = $lead;
        return $data;

    }
	
	function addStep2(Request $request, MainDb $db){
		$id = $this->token2id($request->lead);
		$data = [];
		$lead = $db->simpleEntity('lead');
		$lead->deb_type = $request->deb_type;
        $lead->debit_name = $request->debit_name;
        $lead->debit_address = $request->debit_address;
        $lead->debit_zip_code = $request->debit_zip_code;
        $lead->debit_city = $request->debit_city;
        $lead->debit_country = $request->debit_country;
        $lead->debit_siren = $request->debit_siren;
        $lead->debit_tva = $request->debit_tva;
        $lead->debit_last_name = $request->debit_last_name;
        $lead->debit_first_name = $request->debit_first_name;
        $lead->debit_capacity = $request->debit_capacity;
        $lead->debit_email = $request->debit_email;
        $lead->debit_phone = $request->debit_phone;
        try{
            $db['lead'][$id] = $lead;
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }
        $data['lead'] = $lead;
        return $data;
	}
	
	function addStep3(Request $request, MainDb $db, Uploader $uploader){

		$id = $this->token2id($request->lead);
		
		$data = [];
        $lead = $db->simpleEntity('lead');
        $lead->invoice_comments = $request->invoice_comments;


		if(!empty($request->lead_invoice)){
			$lead->_xmany_lead_invoice = [];
			foreach($request->lead_invoice as $request_lead_invoice){
				$lead_invoice = $db->simpleEntity('lead_invoice');
				$lead_invoice->date = $request_lead_invoice->date;
				$lead_invoice->echeance = $request_lead_invoice->echeance;
				$lead_invoice->montant = $request_lead_invoice->montant;
                $lead_invoice->restant = $request_lead_invoice->restant;
				$lead_invoice->facture = $request_lead_invoice->facture;
                $lead_invoice->libelle = $request_lead_invoice->libelle;
				$lead_invoice->documents = $request_lead_invoice->documents;
				$lead->_xmany_lead_invoice[] = $lead_invoice;
			}
		}

		try{
            $db['lead'][$id] = $lead;
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }

		$i = 1;
		foreach($lead->_xmany_lead_invoice as $lead_invoice){
			$dir = $this->filesPath.$lead_invoice->id.'/';
			$key = 'lead_invoice_'.$i.'_files';
			$mime = null;
			$uploader->files($dir,$key,$mime);
			$i++;
		}

        $data['lead'] = $lead;

        return $data;
	}
	
	function addStep4(Request $request, MainDb $db){
		$data = [];
		$lead = $this->token2row($request->lead);
		$lead->type_frais = $request->type_frais;
		$lead->cgu_accepted = $request->cgu_accepted;
		$lead->validated = $request->validated;

		if($request->validated){
			try{
				$this->callStepFinish($lead);
			}
			catch(ValidationException $e){
				$data['error'] = $e->getMessage();
			}
		}
		else{
			try{
                $db['lead'][] = $lead;
			}
			catch(ValidationException $e){
				$data['error'] = $e->getMessage();
			}
		}
        $data['lead'] = $lead;
        return $data;
	}
	
	function addStep5(Request $request, MainDb $db){
		$data = [];
		$lead = $this->token2row($request->lead);
		$lead->validated = $request->validated;
		try{
            $this->callStepFinish($lead);
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }
		return $data;
	}
	
	protected function stepFinish($lead, Request $request, MainDb $db){

		$user = $db['user']->where('email = ?',[$lead->email])->getRow();
		if(!$user){
			$user = $db->simpleEntity('user');
			$create = true;
		}
		else{
			if($user->type!='lead'){
				throw new InvalidArgumentException('A user with type '.$user->type.' is already registered and it\'s not compatible with lead');
			}
			$create = false;
		}
		
		$token = null;
		if($create){
			$user->type = 'lead';
			$user->email = $lead->email;
			$user->active = null;
			$token = bin2hex(random_bytes(32));
			$user->_xmany_auth_request = [
				[
					'type'=>'reset',
					'rkey'=>$token,
					'expire'=> date("Y-m-d H:i:s", strtotime("+1 year")),
				]
			];
		}
		
		$lead->_xone_user = $user;


		
		try{
            $db['user'][] = $user;
            $db['lead'][] = $lead;
            foreach($lead->_many_lead_invoice as $lead_invoice){
                $lead_invoice->user_id = $user->id;
                $lead_invoice->store();
            }
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }
        
        $this->removeCookie($lead->email);
        
		$this->callSendMail($token, $user, $lead, $create);
	}
	
	protected function _sendMail($token, $user, $lead, $create, Request $request, MainDb $db, SilentProcess $silentProcess, PHPMailer $phpMailer, Templix $templix, Url $url){
		//$silentProcess->debug();
		$silentProcess->register(function()use($lead,$user,$create,$phpMailer,$templix,$url,$token){
			$subject = 'Lead confirmation';
			$mailData = [
				'user'=>$user,
				'token'=>$token,
				'lead'=>$lead,
				'create'=>$create,
				'baseHref'=>$url->getBaseHref(),
			];
			$message = $templix->fetch('mail/leadConfirmation.tml',$mailData);
			
			foreach($lead->_many_lead_invoice as $lead_invoice){
				$dir = $this->filesPath.$lead_invoice->id.'/';
				foreach(glob($dir.'*') as $file){
					//$phpMailer->addStringAttachment(file_get_contents($file),basename($file));
					$phpMailer->addAttachment($file,basename($file));
				}
			}
			$phpMailer->mail($user->email,$subject,$message);
		});
	}
	function resendMail($token, MainDb $db){
		$lead = $this->token2row($token);
		$user = $db['user']->where('email = ?',[$lead->email])->getRow();
		$this->callSendMail($token, $user, $lead, true);
	}
	
	function getTab4($token, MainDb $db, Templix $templix, PriceCalculator $priceCalculator){
		$lead = $this->token2row($token);
		
		$total = $lead->getInvoiceTotalUnpaid();
		$data['total'] = $total;
		
		$honorary = $priceCalculator->get($total,'honorary',$lead->category,$lead->type,$lead->seniority);
		$procedure = $priceCalculator->get($total,'procedure',$lead->category,$lead->type,$lead->seniority);
		$honoraryPercent = $priceCalculator->get($total,'honoraryPercent',$lead->category,$lead->type,$lead->seniority);
		$procedurePercent = $priceCalculator->get($total,'procedurePercent',$lead->category,$lead->type,$lead->seniority);
		
		$data['honorary'] = $honorary;
		$data['costs'] = $procedure;
		$data['honoraryPercent'] = $honoraryPercent;
		$data['costsPercent'] = $procedurePercent;
		
		//dd($data);
		
		$data['lead'] = $lead;
		
		$templix('lead/simulator-tab4.tml',$data);
		return false;
	}
	function getTab6($token, MainDb $db, Templix $templix, PriceCalculator $priceCalculator){
		$lead = $this->token2row($token);
		
		$total = $lead->getInvoiceTotalUnpaid();
		$data['total'] = $total;
		
		$honorary = $priceCalculator->get($total,'honorary',$lead->category,$lead->type,$lead->seniority);
		$procedure = $priceCalculator->get($total,'procedure',$lead->category,$lead->type,$lead->seniority);
		$honoraryPercent = $priceCalculator->get($total,'honoraryPercent',$lead->category,$lead->type,$lead->seniority);
		$procedurePercent = $priceCalculator->get($total,'procedurePercent',$lead->category,$lead->type,$lead->seniority);
		
		$data['honorary'] = $honorary;
		$data['costs'] = $procedure;
		$data['honoraryPercent'] = $honoraryPercent;
		$data['costsPercent'] = $procedurePercent;
		
		//dd($data);
		
		$data['lead'] = $lead;
		$data['create'] = !$lead->_one_user||!$lead->_one_user->valid;
		
		$templix('lead/simulator-tab6.tml',$data);
		return false;
	}

    protected function mandat($category){
        $initial = [
            Lead::CHEQUE_IMPAYE         => 'CI',
            Lead::LOYER_IMPAYE          => 'LI',
            Lead::CREANCE_COMMERCIALE   => 'CC',
            Lead::REACTIVATION_CREANCE  => 'RC'
        ];
        $rand = mt_rand(5,15);
        $date = date('Ymd');
        $type = $initial[$category];
        return $date.'/'.$type.'-'.$rand;
    }


    public function payment($datas, MainDb $db)
    {

        if( isset($datas) && !empty($datas) ){

            Stripe::setApiKey("sk_test_bqEyuMF6v8p6DJ1FWfHKeMDr");

            $token = $datas['token'];
            $amount = $datas['amount'];
            $email = $datas['email'];

            $user = $db['user']->read($email);

            if( !isset($user['id_customer']) || empty($user['id_customer']) ) {
                $customer = \Stripe\Customer::create(array(
                    "email" => $email,
                    "source" => $token
                ));
            }else{
                $customer = new \stdClass();
                $customer->id = $user['id_customer'];
            }

            $charge = \Stripe\Charge::create(array(
                "amount" => $amount,
                "currency" => "eur",
                "description" => "SAY SOMETHING ABOUT PAYMENT",
                "customer" => $customer->id,
            ));

            if( $charge->status === 'succeeded' ) {
                $user['id_customer'] = $customer->id;
                $db['user'][] = $user;
                return array('success' => 'Paiement accepté.');
            }else
                return array('error' => 'Paiement non effectué.');

        }
    }
}
