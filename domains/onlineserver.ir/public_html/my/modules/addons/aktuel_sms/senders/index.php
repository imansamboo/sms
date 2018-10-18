<?php

class index extends AktuelSms {
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

              $url = "http://api.payamak-panel.com/post/sendsms.ashx?username=$params->user&password=$params->pass&to=$this->gsmnumber&from=$params->senderid&text=".urlencode($this->message)."";
        
  $result = file_get_contents($url);
  file_put_contents("log", "ok");
        $return = $result;
        $log[] = ("Sunucudan dönen cevap: ".$result);

        $result = explode("|",$result);
        if($result[0]=="1"){
            $log[] = ("Message sent.");
        }else{
            $log[] = ("پیامک شما با موفقیت ارسال شد: $return");
            $error[] = ("پیامک شما با موفقیت ارسال شد: $return");
        }
			
        return array(
            'log' => $log,
            'error' => $error,
            'msgid' => $msgid,
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
    'value' => 'index',
    'label' => 'انتخاب کنید',
    'fields' => array(
        'user','pass'
    )
);