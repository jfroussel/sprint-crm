<?php
namespace App\Api;

use Mailgun\Mailgun;
use DateTime;

class PromiseMail extends AbstractMail{


    protected function getMailTo($data){

        $contact = $this->db['contact'][$data['promise']['contact_id']];
        return $contact->email;
    }

    protected function getPromiseParam($data){

        if ($data) {

            $message = $data['promise']['message'];

            if (empty($message) || strlen($message) < 2) {
                return ['error' => 'Message vide.'];
            }

            $user_id = $this->user_id ?? $this->instance_id;
            $from = $this->db['user'][$user_id]->email;
            $to = $this->getMailTo($data);

            $subject = 'Information promesse de reglement';
            $param = [
                'from' => '<' . $from . '>',
                'to' => $to,
                'cc'    => $from,
                'subject' => $subject,
                'html' => $message
            ];


            return $param;
        }
    }

    function sendEmail($data){

        $param = $this->getPromiseParam($data);

        /**
         * insert push to db mail
         */


        if (!empty($param)) {
            $mg = new Mailgun($this->mailGunApiKey);
            $mg->sendMessage($this->mailGunDomain, $param);
            $result = $mg->get($this->mailGunDomain.'/log', ['limit' => 25, 'skip' => 0]);
            $httpResponseCode = $result->http_response_code;
            return ['status' => $httpResponseCode];
        } else {
            return ['error'=>"Votre email est vide."];
        }
    }

}
