<?php
namespace RootBundle\Service;


/**
 * 
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class MailerService{
    
    public function __construct(private NodeApp $nodeApp){}

    /**
     * @param string|array $receivers
     * @param string $subject
     * @param string $body
     * @return bool false if there is an error while sending the email
     */
    public function sendEmail(string|array $receivers, string $subject, string $body): bool
    {
        if($subject == "") throw new \Exception("The subject cannot be empty");
        if(is_string($receivers))
            $receivers = [$receivers];
        $verfiedReceivers = [];
        foreach($receivers as $r){
            if($r != "" && $r != null) $verfiedReceivers[] = $r;
        }
        return $this->nodeApp->sendEmail($receivers, $subject, $body);
    }

}