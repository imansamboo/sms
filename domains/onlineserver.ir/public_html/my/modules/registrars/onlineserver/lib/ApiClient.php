<?php

namespace WHMCS\Module\Registrar\onlineserver;

use WHMCS\Database\Capsule as DB;
/**
 * Sample Registrar Module Simple API Client.
 *
 * A simple API Client for communicating with an external API endpoint.
 */
class ApiClient
{
    const API_URL = 'https://onlineserver.ir/my/osAPI.php?action=';

    public $results = array();

	/**
     * Make external API call to registrar API.
     *
     * @param string $action
     * @param array $postfields
     *
     * @throws \Exception Connection error
     * @throws \Exception Bad API response
     *
     * @return array
     */
    public function call($action, $postfields)
    {

    	$postString=json_encode($postfields);
    	//var_dump($postfields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $action);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    //'Content-Length: ' . strlen($postString)
		    )
	    );
	    $response = curl_exec($ch);
	    error_log(date("Y-m-d H:i:s").' '.$action.' '.str_replace('\\','',json_encode($response))."\t".json_encode($postfields['data']['domain'])."\r\n", 3, "/home/onlinese/domains/onlineserver.ir/public_html/my/111111111.log");
	    if (curl_errno($ch)) {
		    throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
	    curl_close($ch);

	    //return false;
	    $this->results = $this->processResponse($response);

	    logModuleCall(
            'OnlineServer',
            $action,
            $postfields,
            $response,
            $this->results,
            array(
                $postfields['username'], // Mask username & password in request/response data
                $postfields['password'],
            )
        );

	    if ($this->results === null && json_last_error() !== JSON_ERROR_NONE) {
		    if (is_string($response)){
			    throw new \Exception($response);
		    }
		    throw new \Exception('Bad response received from API');
	    }elseif (in_array('errors',array_keys($this->results)) && $this->results['errors']!=null){
		    throw new \Exception($this->errorsHandlers($this->results['errors']));
	    }

	    return $this->results;
    }
    public function translate($str){
	    global $_OSLANG;
	    if (in_array($str,array_keys($_OSLANG))){
		    return $_OSLANG[$str];
	    }else{
	    	$gt=new \gtranslate();
		    return $gt->translate($str, 'fa','en',true);
	    }
	    //return $str;
    }
	public function errorsHandlers($errors){
    	$msg='<br>';//'Errors : <br>';

    	if (is_array($errors)){
		    foreach ($errors as $key=>$error){
		    	if (!is_numeric($key) && ($key=='your credit is not enough!')){
				    DB::table('tbltodolist')->insert(['date'=>date('Y-m-d'),'title'=>'موجودی آنلاین سرور کم است','description'=>"موجودی ثبت دامنه شما در آنلاین سرور کم است . لطفا برای ادامه روند ثبت دامنه در انلاین سرو پنل خود را شارژ کنید . موجودی فعلی شما : {$error}",'status'=>'Pending','duedate'=>date('Y-m-d')]);
				    //continue;
				    $error=$key;
			    }
			   // echo $errors;
			    $msg.='<b> - '.$this->translate($error).'</b><br>';
		    }
	    }else{
		    $msg.=$this->translate($errors);
	    }
	    return $msg;
	}
    /**
     * Process API response.
     *
     * @param string $response
     *
     * @return array
     */
    public function processResponse($response)
    {
        return json_decode($response, true);
    }

    /**
     * Get from response results.
     *
     * @param string $key
     *
     * @return string
     */
    public function getFromResponse($key)
    {
        return isset($this->results[$key]) ? $this->results[$key] : '';
    }
}
