<?php

namespace App\Modules\Marketplace;

use RedCat\Route\Url;
use RedCat\Route\SilentProcess;
use App\Route\Route;
use RedCat\Route\Request;
use App\Templix\Templix;
use App\Model\Db;
use RedCat\Strategy\CallTrait;
use RedCat\Identify\PHPMailer;
use InvalidArgumentException;

use App\AbstractController;

class Marketplace extends AbstractController{

    use CallTrait;
    protected $db;
    function __construct(Db $db){
        $this->db = $db;
    }
    function addMarketplace($post, Db $db, Templix $templix){

        $data = [];

        $marketplace = $db->simpleEntity('marketplace',$post,[
            'nom',
            'prenom',
            'email',
            'tel',
            'raison_sociale',
            'siren',
            'naf',
            'adresse',
            'code_postal',
            'ville',
            'specialite',
        ]);

        //$db['marketplace'][] = $marketplace;
        $db->create($marketplace);
        $data['marketplace'] = $marketplace;


        list($user, $request) = $this->stepFinish($marketplace, $db);

        $data['requestId'] = $request->id;
        $data['user'] = $user;

        $templix('marketplace/confirmation.tml',$data);

        return false;
    }

    protected function stepFinish($marketplace,  Db $db){
        $user = $db['user']
            ->unSelect()
            ->select('id')
            ->select('active')
            ->where('email = ?',[$marketplace->email])->getRow();
        if($user){
            if($user->active){
                throw new InvalidArgumentException('You areallready registered');
            }
            unset($db['user'][$user->id]);
        }
        $user = $db->simpleEntity('user');
        $user->type = 'marketplace';
        $user->email = $marketplace->email;
        $user->active = null;
        $token = bin2hex(random_bytes(32));
        $request = $db->simpleEntity('request',[
            'type'=>'reset',
            'rkey'=>$token,
            'expire'=> date("Y-m-d H:i:s", strtotime("+1 year")),
        ]);
        $user->_xmany_auth_request = [
            $request
        ];
        $marketplace->_one_user_x_ = $user;

        try{
            $db['marketplace'][] = $marketplace;
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }


        $this->callSendMail($token, $user, $marketplace, $request->id);

        return [$user, $request];
    }
    protected function _sendMail($token, $user, $marketplace, $requestId, SilentProcess $silentProcess, PHPMailer $phpMailer, Templix $templix, Url $url){
        //$silentProcess->debug();
        $silentProcess->register(function()use($marketplace,$user,$phpMailer,$templix,$url,$token){
            $subject = 'Création de votre compte sur la plateforme desico.Sprint-CRM';
            $mailData = [
                'user'=>$user,
                'token'=>$token,
                'marketplace'=>$marketplace,
                'baseHref'=>$url->getBaseHref(),
            ];
            $message = $templix->fetch('mail/marketplaceConfirmation.tml',$mailData);
            $phpMailer->mail($user->email,$subject,$message);
        });
    }
    function resendMail($requestId, Db $db){
        $request = $db['request'][$requestId];
        $token = $request->token;
        $user = $request->_one_user;
        $marketplace = $user->_many_marketplace->getRow();
        $this->callSendMail($token, $user, $marketplace, $requestId);
        return true;
    }
}
