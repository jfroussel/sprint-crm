<?php

namespace App\Modules\Promise;

use RedCat\Route\Request;
use FoxORM\MainDb as Db;

use App\AbstractController;

class Add extends AbstractController{

    function __invoke(Request $request, Db $db){

        if($request->posted&&!$request->amount){
			$data['state'] = 'ok';
			return $data;
		}
		
        $token = $request->token;


        $data =  [
			'state'=>null,
			'token'=>$token,

		];

        if($token){
			$row = $db['promise_token']->where('token=?', [$token])->getRow();
		}

        if(!($token&&$row)){
			$data['error'] = 'invalid or allready used token';
			$data['state'] = 'error';
			return $data;
		}

        $debtor_id = $row->debtor_id;
        $debtor = $db['debtor'][$debtor_id];
        $instance_id = $row->instance_id;
        $data['logo'] = '../content/user/'.$instance_id.'/avatar.png';

        $paperworks = $db['paperwork']->reporting($debtor->primary);
        $data['paperworks'] = $paperworks;


        
        $paperworks_id = [];
        foreach($paperworks as $id=>$paperwork){
			$paperworks_id[] = $id;
		}
        
        if($request->amount){
			
			$posted_paperworks = array_filter($request->paperwork->getArray(),function($id)use($paperworks_id){
				return in_array($id,$paperworks_id);
			});

            $db['promise'][] = [
				'debtor_id' => $debtor_id,
                'amount' => $request->amount,
                'instance_id' => $instance_id,
                'date_reglement' =>$request->date_reglement,
                'payment_type' =>$request->payment_type,
                '_xmany2many_paperwork'=>$posted_paperworks,
            ];

            $row->delete();
            
            $data['state'] = 'ok';
        }

        return $data;
    }

}
