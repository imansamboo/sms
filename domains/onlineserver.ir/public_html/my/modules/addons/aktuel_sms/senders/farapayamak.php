<?php

class farapayamak extends AktuelSms {
      function __construct($message,$gsmnumber){
        $this->message = $this->utilmessage($message);
        $this->gsmnumber = $this->utilgsmnumber($gsmnumber);
    }
	
    function send(){
        if($this->gsmnumber == "numbererror"){
            $log[] = ("Number format error.".$this->gsmnumber);
            $error[] = ("Number format error.".$this->gsmnumber);
            return null;
        }
        $params = $this->getParams();

            

 ini_set("soap.wsdl_cache_enabled", "0");
 $url = new SoapClient('http://api.payamak-panel.com/post/send.asmx?wsdl', array('encoding'=>'UTF-8'));

 $parameters['username'] = $params->user;
    $parameters['password'] = $params->pass;
    $parameters['to'] = $this->gsmnumber;
    $parameters['from'] = $params->senderid;
    $parameters['text'] =$this->message;
    $parameters['isflash'] =false;

return $Data =  $url->SendSimpleSMS2($parameters)->SendSimpleSMS2Result;

        
  $result = file_get_contents($Data);
   file_put_contents('log', print_r('ok', true)); 
        $return = $result;
        $log[] = ("Sunucudan dönen cevap: ".$result);

        $result = explode("|",$result);
        if($result >="1"){
            $log[] = ("Message sent.");
        }else{
            $log[] = ("پیامک شما با موفقیت ارسال شد: $return");
            $error[] = ("پیامک شما با موفقیت ارسال شد: $return");
        }
			
        return array(
            'log' => $log,
            'error' => $error,
            'msgid' => $Data,
        );
    }

        function balance(){
        $params = $this->getParams();

        if($params->user && $params->pass){
            $url = "http://api.payamak-panel.com/get/getcredit.ashx?username=$params->user&password=$params->pass";
            $result = file_get_contents($url);
            $result = explode(" ",$result);
            return $result[1];
        }else{
            return null;
        }
    }

    function report($msgid){
        return null;
    }

    //You can spesifically convert your gsm number. See netgsm for example
    function utilgsmnumber($number){
        return $number;
    }
    //You can spesifically convert your message
    function utilmessage($message){
        return $message;
    }
}

return array(
    'value' => 'farapayamak',
    'label' => 'Farapayamak',
    'fields' => array(
        'user','pass'
    )
);
