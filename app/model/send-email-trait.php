<?php
namespace App\Model;

use App\Api\AbstractMail;
use Mailgun\Mailgun;


trait SendEmailTrait {

    protected function getDebtorInfo($data){
        $debtor = $this->db['debtor']->where('id = ?', [$data['debtor_id']]);
        $debtorInfo = [];
        foreach($debtor as $v){
            $debtorInfo = [
                'company_name'  => $v['nom_client'],
                'ref_client'    => $v['primary'],
                'address'       => $v['adresse'],
                'postal_code'   => $v['code_postal'],
                'city'          => $v['ville'],
            ];
        }
        return $debtorInfo;
    }

    protected function getSenderInfo($data){
        $sender = $this->db['user'][$data['instance_id']];
        $senderInfo = [
            'company_name'  => $sender['company_name'],
            'address'       => $sender['address'],
            'postal_code'   => $sender['postal_code'],
            'city'          => $sender['city'],
        ];
        return $senderInfo;
    }

    protected function getContactInfo($data){
        if($data['contact_id']){
            $contact = $this->db['contact']->where('id = ?', [$data['contact_id']]);
            $contactInfo = ['first_name','last_name','email'];
            foreach ($contact as $v){
                if(!empty($v)){
                    $contactInfo = [
                        'first_name' => $v['nom'],
                        'last_name' => $v['prenom'],
                        'email' => $v['email']
                    ];
                }
            }
            return $contactInfo;
        }else{
            $contactPrincipal = $this->getContactPrincipal($data);
            $contactInfo = [];
            foreach($contactPrincipal as $v){
                $contactInfo = [
                    'first_name' => $v['nom'],
                    'last_name' => $v['prenom'],
                    'email' => $v['email']
                ];
            }
            return $contactInfo;
        }
    }

    protected function getContactPrincipal($data){
        $principal = 1;
        $contactPrincipal = $this->db['contact']
            ->where('debtor_id = ?', [$data['debtor_id']])
            ->where('principal = ?', [$principal]);
        return $contactPrincipal;
    }

    protected function getManagerInfo($data){
        $management = $this->db['management']->where('debtor_id = ?', [$data['debtor_id']]);
        $debtorManager = '';

        foreach($management as $v){
            $debtorManager = $v['user_id'];
        }
        $manager = $this->db['user']->where('id = ?', [$debtorManager]);
        $managerInfo = [];

        foreach($manager as $v){
            $managerInfo = [
                'first_name' => $v['first_name'],
                'last_name' => $v['last_name'],
                'phone' => $v['phone'],
                'email' => $v['email']
            ];
        }
        return $managerInfo;
    }

    function sendEmail($data){
        $debtorInfo = $this->getDebtorInfo($data);
        $senderInfo = $this->getSenderInfo($data);
        $contactInfo = $this->getContactInfo($data);
        $managerInfo = $this->getManagerInfo($data);

        $templatePromise = ' <b>Expediteur</b> :  <br>société : '. $senderInfo['company_name'].'<br> 
        adresse : '. $senderInfo['address'] . '<br> 
        code postal :  '. $senderInfo['postal_code']. ' <br>
        ville :  '. $senderInfo['city']. ' <br>
        <b>Votre interlocuteur</b> :  '.$managerInfo['first_name'].' '.$managerInfo['last_name'].' tel: '.$managerInfo['phone'].' email: '.$managerInfo['email'].' <br><br>
        <b>Destinataire :</b><br>
        '.$contactInfo['first_name'].'  '.$contactInfo['last_name'].'  '.$contactInfo['email'].'<br>
        société : '.$debtorInfo['company_name'].' <br>
        adresse : '.$debtorInfo['address'].'<br>
        code postal :'.$debtorInfo['postal_code'].' <br>
        ville : '.$debtorInfo['city'].'<br><br>
        <b>Message</b> : <br> '.$data['message'].'<br><br>
        <p>Votre engagement à payer est bien enregistrée
        Vous ne recevrez plus de relance jusqu’à la date paiement.
        Nous nous réservons le droit de reprendre la procédure de recouvrement si le paiement n’est pas reçu dans le délai que vous avez indiqué.</p>
        ';

        $subject = 'Promesse de paiemment';
        $param = [
            'from' => '<'.$managerInfo['email'].'>',
            'to' => $contactInfo['email'],
            'cc'    => $managerInfo['email'],
            'subject' => $subject,
            'html' => $templatePromise,
        ];

        if (!empty($param)) {
            $mailGunDomain = 'mg.mycreance.com';
            $mg = new Mailgun('key-667619638d09aabc4527d03bb86235a2');
            $mg->sendMessage($mailGunDomain, $param);
            $result = $mg->get($mailGunDomain.'/log', ['limit' => 25, 'skip' => 0]);
            $httpResponseCode = $result->http_response_code;
            return ['status' => $httpResponseCode];
        } else {
            return ['error'=>"Votre email est vide."];
        }
    }

}
