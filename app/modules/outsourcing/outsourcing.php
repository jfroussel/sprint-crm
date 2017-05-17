<?php

namespace App\Modules\Outsourcing;

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

class Outsourcing extends AbstractController
{

    use CallTrait;
    protected $db;
    function __construct(Db $db){
        $this->db = $db;
    }
    function addOutsourcing($post, Request $request, Db $db, Templix $templix){

        $data = [];

        $outsourcing = $db->simpleEntity('outsourcing',$post,[
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

        //$db['outsourcing'][] = $outsourcing;
        $db->create($outsourcing);
        $data['outsourcing'] = $outsourcing;

        list($user, $requestId) = $this->stepFinish($outsourcing, $db);
        $data['user'] = $user;
        $data['requestId'] = $requestId;

        $templix('outsourcing/confirmation.tml',$data);

        return false;
    }

    protected function stepFinish($outsourcing,  Db $db){
        $user = $db['user']
            ->unSelect()
            ->select('id')
            ->select('active')
            ->where('email = ?',[$outsourcing->email])->getRow();
        if($user){
            if($user->active){
                throw new InvalidArgumentException('You areallready registered');
            }
            unset($db['user'][$user->id]);
        }
        $user = $db->simpleEntity('user');
        $user->type = 'outsourcing';
        $user->email = $outsourcing->email;
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
        $outsourcing->_one_user_x_ = $user;

        try{
            $db['outsourcing'][] = $outsourcing;
        }
        catch(ValidationException $e){
            $data['error'] = $e->getMessage();
        }


        $this->callSendMail($token, $user, $outsourcing, $request->id);

        return [$user, $request->id];
    }
    protected function _sendMail($token, $user, $outsourcing, $requestId, SilentProcess $silentProcess, PHPMailer $phpMailer, Templix $templix, Url $url){
        //$silentProcess->debug();
        $silentProcess->register(function()use($outsourcing,$user,$phpMailer,$templix,$url,$token){
            $subject = 'Création de votre compte sur la plateforme Mycreance';
            $mailData = [
                'user'=>$user,
                'token'=>$token,
                'outsourcing'=>$outsourcing,
                'baseHref'=>$url->getBaseHref(),
            ];
            $message = $templix->fetch('mail/outsourcingConfirmation.tml',$mailData);
            $phpMailer->mail($user->email,$subject,$message);
        });
    }
}
