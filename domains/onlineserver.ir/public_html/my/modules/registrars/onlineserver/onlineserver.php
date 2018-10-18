<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Module\Registrar\onlineserver\ApiClient;

// Require any libraries needed for the module to function.
require_once __DIR__ . '/lib/src/gtranslate.php';
require_once __DIR__ . '/lang/farsi.php';
require_once __DIR__ . '/op.php';


/**
 * Define module related metadata
 *
 * Provide some module information including the display name and API Version to
 * determine the method of decoding the input values.
 *
 * @return array
 */
function onlineserver_MetaData()
{
    return array(
        'DisplayName' => 'Online Server domain registrar',
        'APIVersion' => '0.5',
    );
}

/**
 * Define registrar configuration options.
 *
 * The values you return here define what configuration options
 * we store for the module. These values are made available to
 * each module function.
 *
 *
 * @return array
 */
function onlineserver_getConfigArray()
{
    return array(
        // Friendly display name for the module
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Online Server',
        ),
        'Url' => array(
            'Type' => 'text',
            'Size' => '',
            'Default' => '',
            'Description' => 'Enter OnlineServer API Url, Include https://',
        ),
        // a text field type allows for single line text input
        'Username' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your OnlineServer username Or Email',
        ),
        // a password field type allows for masked text input
        'Password' => array(
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your OnlineServer password',
        ),
        "WhoisPrivacyProtectionPrice"     => array
        (
            "FriendlyName"  => "Whois privacy protection Price",
            "Type"          => "text",
            "Size"          => "100",
            "Description"   => "Price For Whois privacy protection , (Dollar)",
            "Default"       => "1.5"
        ),
        "SpamFilteringPrice"     => array
        (
            "FriendlyName"  => "Spam Filtering Price",
            "Type"          => "text",
            "Size"          => "100",
            "Description"   => "Price For Spam filtering , (Dollar)",
            "Default"       => "1.5"
        ),
    );
}



/*---------------------------------------------------------*/
/*iman api for modifing domain dns after registering domain*/
/*---------------------------------------------------------*/
function modifyDomain($params)
{
    for($i=1; $i < 5; $i++){
        if(isset($_POST['ns'.$i.'_ip'])) {
            $params['ns'.$i.'_ip'] = $_POST['ns'.$i.'_ip'];
        }else{
            $params['ns'.$i.'_ip'] = null;
        };
    }
    $nsgroupName = $params['sld'].rand();
    $api = new Iman_API ('https://api.openprovider.eu');

    $username = "onlineserver";
    $password = "3gpGN4fbB7emJ9wo";

    $request = new Iman_Request;
    $request->setCommand('createNsGroupRequest')
        ->setAuth(array('username' => $username, 'password' => $password))
        ->setArgs(array(
            'nsGroup' => $nsgroupName,
            'nameServers' => array(
                array('name' => $params['ns1'], 'ip' => $params['ns1_ip']),
                array('name' => $params['ns2'], 'ip' => $params['ns2_ip']),
                array('name' => $params['ns3'], 'ip' => $params['ns3_ip']),
                array('name' => $params['ns4'], 'ip' => $params['ns4_ip']),
            )
        ));
    $replys = $api->process($request);
    $request = new Iman_Request;
    $request->setCommand('modifyDomainRequest')
        ->setAuth(array('username' => $username, 'password' => $password))
        ->setArgs(array(
            'domain' => array(
                'name' => $params['sld'],
                'extension' => $params['tld']
            ),
            'nsGroup' => $nsgroupName
        ));
    $replys = $api->process($request);

   // header('location: https://onlineserver.ir/my/clientarea.php?action=domains');
}
/*---------------------------------------------------------*/
/*-------------------End of Iman api-----------------------*/
/*---------------------------------------------------------*/

/**
 * Register a domain.
 *
 * Attempt to register a domain with the domain registrar.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */

function onlineserver_RegisterDomain($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];


    // registration parameters
    $sld = $params['sld'];
    $tld = $params['tld'];
    $registrationPeriod = $params['regperiod'];

    /**
     * Nameservers.
     *
     * If purchased with web hosting, values will be taken from the
     * assigned web hosting server. Otherwise uses the values specified
     * during the order process.
     */
    $nameserver1 = $params['ns1'];
    $nameserver2 = $params['ns2'];
    $nameserver3 = $params['ns3'];
    $nameserver4 = $params['ns4'];
    $nameserver5 = $params['ns5'];

    $params["address1"]=validate($params["address1"]);
    $params["address2"]=validate($params["address2"]);
    $params["city"]=validate($params["city"]);
    $params["state"]=validate($params["state"]);


    $firstName = translate($params["firstname"]) ;
    $lastName = translate($params["lastname"]);
    $fullName = translate($params["fullname"]); // First name and last name combined
    $companyName = translate($params["companyname"]);
    $email = $params["email"];
    $address1 = translate($params["address1"]);
    $address2 = translate($params["address2"]);
    $city = translate($params["city"]);
    $state = translate($params["state"]); // eg. TX
    $stateFullName =translate($params["fullstate"]);// eg. Texas
    $postcode = $params["postcode"]; // Postcode/Zip code
    $countryCode = $params["countrycode"]; // eg. GB
    $countryName = translate($params["countryname"]); // eg. United Kingdom
    $phoneNumber = $params["phonenumber"]; // Phone number as the user provided it
    $phoneCountryCode = $params["phonecc"]; // Country code determined based on country
    $phoneNumberFormatted = $params["fullphonenumber"]; // Format: +CC.xxxxxxxxxxxx


    // domain addon purchase status
    $enableDnsManagement = (bool) $params['dnsmanagement'];
    $enableEmailForwarding = (bool) $params['emailforwarding'];
    $enableIdProtection = (bool) $params['idprotection'];

    /**
     * Premium domain parameters.
     *
     * Premium domains enabled informs you if the admin user has enabled
     * the selling of premium domain names. If this domain is a premium name,
     * `premiumCost` will contain the cost price retrieved at the time of
     * the order being placed. The premium order should only be processed
     * if the cost price now matches the previously fetched amount.
     */
    $premiumDomainsEnabled = (bool) $params['premiumEnabled'];
    $premiumDomainsCost = $params['premiumCost'];

    // Build Phone Number
    $pos                    =   strpos($phoneNumberFormatted, '.');
    $countryCode2           =   substr($phoneNumberFormatted, 0, $pos);
    $areaCode               =   substr($phoneNumberFormatted, $pos + 1, 3);
    $phoneNumber            =   substr($phoneNumberFormatted, $pos + 1 + 3);



    // Build post data
    $initials       =   substr($firstName, 0, 1) . '.' . substr($lastName, 0, 1);
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'owner'=>[
                'companyName'=>$companyName,
                'name'=>[
                    'initials' => $initials,
                    'firstName' => $firstName,
                    'lastName' => $lastName
                ],
                'gender'=>'m',
                "phone"=>[
                    'countryCode' => $countryCode2,
                    'areaCode' => $areaCode,
                    'subscriberNumber'=> $phoneNumber
                ],
                'address' => [
                    'street' => $address1,
                    'number' => "1",
                    'zipcode' => $postcode,
                    'city' => $city,
                    'country' => $countryCode
                ],
                "email" => $email,
            ],
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'nameserver'=>[
                [
                    'name'=>$nameserver1
                ],
                [
                    'name'=>$nameserver2
                ],
                [
                    'name'=>$nameserver3
                ],
                [
                    'name'=>$nameserver4
                ],
                [
                    'name'=>$nameserver5
                ]
            ],
            'additionalData'=>$params['additionalfields'],
            'idprotection'=>$enableIdProtection,
        ]
    ];


    if ($premiumDomainsEnabled && $premiumDomainsCost) {
        $postfields['accepted_premium_cost'] = $premiumDomainsCost;
    }

    try {
        $api = new ApiClient();
        $api->call('registerDomain', $postfields);
        modifyDomain($params);
        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Initiate domain transfer.
 *
 * Attempt to create a domain transfer request for a given domain.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain transfer order
 * * When a pending domain transfer order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_TransferDomain($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // registration parameters
    $sld = $params['sld'];
    $tld = $params['tld'];
    $registrationPeriod = $params['regperiod'];
    $eppCode = base64_encode($params['eppcode']);
    //$eppCode = $params['eppcode'];

    /**
     * Nameservers.
     *
     * If purchased with web hosting, values will be taken from the
     * assigned web hosting server. Otherwise uses the values specified
     * during the order process.
     */
    $nameserver1 = $params['ns1'];
    $nameserver2 = $params['ns2'];
    $nameserver3 = $params['ns3'];
    $nameserver4 = $params['ns4'];
    $nameserver5 = $params['ns5'];

    $params["address1"]=validate($params["address1"]);
    $params["address2"]=validate($params["address2"]);
    $params["city"]=validate($params["city"]);
    $params["state"]=validate($params["state"]);


    $firstName = translate($params["firstname"]) ;
    $lastName = translate($params["lastname"]);
    $fullName = translate($params["fullname"]); // First name and last name combined
    $companyName = translate($params["companyname"]);
    $email = $params["email"];
    $address1 = translate($params["address1"]);
    $address2 = translate($params["address2"]);
    $city = translate($params["city"]);
    $state = translate($params["state"]); // eg. TX
    $stateFullName =translate($params["fullstate"]);// eg. Texas
    $postcode = $params["postcode"]; // Postcode/Zip code
    $countryCode = $params["countrycode"]; // eg. GB
    $countryName = translate($params["countryname"]); // eg. United Kingdom
    $phoneNumber = $params["phonenumber"]; // Phone number as the user provided it
    $phoneCountryCode = $params["phonecc"]; // Country code determined based on country
    $phoneNumberFormatted = $params["fullphonenumber"]; // Format: +CC.xxxxxxxxxxxx



    /**
     * Premium domain parameters.
     *
     * Premium domains enabled informs you if the admin user has enabled
     * the selling of premium domain names. If this domain is a premium name,
     * `premiumCost` will contain the cost price retrieved at the time of
     * the order being placed. The premium order should only be processed
     * if the cost price now matches that previously fetched amount.
     */
    $premiumDomainsEnabled = (bool) $params['premiumEnabled'];
    $premiumDomainsCost = $params['premiumCost'];

    // Build Phone Number
    $pos                    =   strpos($phoneNumberFormatted, '.');
    $countryCode2           =   substr($phoneNumberFormatted, 0, $pos);
    $areaCode               =   substr($phoneNumberFormatted, $pos + 1, 3);
    $phoneNumber            =   substr($phoneNumberFormatted, $pos + 1 + 3);




    $initials       =   substr($firstName, 0, 1) . '.' . substr($lastName, 0, 1);
    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'owner'=>[
                'name'=>[
                    'initials' => $initials,
                    'firstName' => $firstName,
                    'lastName' => $lastName
                ],
                'gender'=>'m',
                "phone"=>[
                    'countryCode' => $countryCode2,
                    'areaCode' => $areaCode,
                    'subscriberNumber'=> $phoneNumber
                ],
                'address' => [
                    'street' => $address1,
                    'number' => "1",
                    'zipcode' => $postcode,
                    'city' => $city,
                    'country' => $countryCode
                ],
                "email" => $email,
            ],
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'nameserver'=>[
                [
                    'name'=>$nameserver1
                ],
                [
                    'name'=>$nameserver2
                ],
                [
                    'name'=>$nameserver3
                ],
                [
                    'name'=>$nameserver4
                ],
                [
                    'name'=>$nameserver5
                ]
            ],
            "authCode" => $eppCode
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('transferDomain', $postfields);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_RenewDomain($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // registration parameters
    $sld = $params['sld'];
    $tld = $params['tld'];
    $registrationPeriod = $params['regperiod'];

    // domain addon purchase status
    $enableDnsManagement = (bool) $params['dnsmanagement'];
    $enableEmailForwarding = (bool) $params['emailforwarding'];
    $enableIdProtection = (bool) $params['idprotection'];

    /**
     * Premium domain parameters.
     *
     * Premium domains enabled informs you if the admin user has enabled
     * the selling of premium domain names. If this domain is a premium name,
     * `premiumCost` will contain the cost price retrieved at the time of
     * the order being placed. A premium renewal should only be processed
     * if the cost price now matches that previously fetched amount.
     */
    $premiumDomainsEnabled = (bool) $params['premiumEnabled'];
    $premiumDomainsCost = $params['premiumCost'];

    // Build post data.

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            "period" => $registrationPeriod
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('renewDomainRequest', $postfields);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Fetch current nameservers.
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_GetNameservers($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];
    $registrationPeriod = $params['regperiod'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ]
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('GetNameservers', $postfields);

        //var_dump($api->results);
        return array(
            'success' => true,
            'ns1' => $api->results[0]['name'],
            'ns2' => $api->results[1]['name'],
            'ns3' => $api->results[2]['name'],
            'ns4' => $api->results[3]['name'],
            'ns5' => $api->results[4]['name'],
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request to the
 * domain registrar.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_SaveNameservers($params)
{


    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // submitted nameserver values
    $nameserver1 = $params['ns1'];
    $nameserver2 = $params['ns2'];
    $nameserver3 = $params['ns3'];
    $nameserver4 = $params['ns4'];
    $nameserver5 = $params['ns5'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'nameServers'=>[
                [ "name"=> $nameserver1 ],
                [ "name"=> $nameserver2 ],
                [ "name"=> $nameserver3 ],
                [ "name"=> $nameserver4 ],
                [ "name"=> $nameserver5 ],
            ],
        ]

    ];

    try {
        /*$api = new ApiClient();
        $api->call('SetNameservers', $postfields);*/
        modifyDomain($params);
        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get the current WHOIS Contact Information.
 *
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_GetContactDetails($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // Build post data

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ]
        ]
    ];
    try {
        $api = new ApiClient();
        $api->call('getWhoisInformation', $postfields);
        $info=[
            'Registrant'=>'ownerHandle',
            'Technical'=>'techHandle',
            'Billing'=>'billingHandle',
            'Admin'=>'adminHandle'
        ];
        $apiType=[
            'ownerHandle'=>'Registrant',
            'techHandle'=>'Technical',
            'billingHandle'=>'Billing',
            'adminHandle'=>'Admin'
        ];

        foreach ($apiType as $key=>$type){
            $info[$type]=$api->results[$key];
            $info[$type]=[
                'First Name' =>$api->results[$key]['name']['firstName'],
                'Last Name' => $api->results[$key]['name']['lastName'],
                'Company Name' =>$api->results[$key]['companyName'],
                'Email Address' => $api->results[$key]['email'],
                'Address 1' => $api->results[$key]['address']['street'],
                'City' => $api->results[$key]['address']['city'],
                'State' => $api->results[$key]['address']['state'],
                'Postcode' => $api->results[$key]['address']['zipcode'],
                'Country' => $api->results[$key]['address']['country'],
                'Phone Number' => $api->results[$key]['phone']['countryCode'].$api->results[$key]['phone']['areaCode'].$api->results[$key]['phone']['subscriberNumber'],
            ];
        }
        return $info;


    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}
function phoneNumber($phone){
    $pos                              =  3 ;// (strpos($phone, '.')) ? strpos($phone, '.') : 3;
    $return['countryCode2']           =   substr($phone, 0, $pos);
    $return['areaCode']               =   substr($phone, $pos , 3);
    $return['phoneNumber']            =   substr($phone, $pos + 2);

    return $return;
}
function translate($array,$toTranslate=null){
    $alefba='ا	ب	پ	ت	ث	ج	چ	‌ ح	خ	د	ذ	ر	ز	‌ ژ	س	‌ ش	ص	ض	ط	ظ	ع	غ	ف	ق	ک	گ	ل	م	ن	و	ه	ی';
    $alefba=explode('	',$alefba);
    $gt=new gtranslate();
    if (is_string($array)){
        $flag = false;
        foreach ($alefba as $letter)
        {
            if (strpos($array, $letter))
            {
                $flag = true;
            }
        }
        if ($flag){
            return $gt->translate($array, 'en','fa',true);
        }
        return $array;
    }
    foreach ($array as $key=>$value){
        $do=true;
        if (!is_null($toTranslate)){
            if (!in_array($key,$toTranslate)){
                $do=false;
            }
        }
        if ($do){

            $array[$key]=$gt->translate($value, 'en','fa',true);
        }
    }
    return $array;
}
function validate($string){
    $invalid=[':','\\','(',')','[',']','{','}','/','*','!','@','#','$','%','^','&'];
    return str_replace($invalid,'',$string);
}
/**
 * Update the WHOIS Contact Information for a given domain.
 *
 * Called when a change of WHOIS Information is requested within WHMCS.
 * Receives an array matching the format provided via the `GetContactDetails`
 * method with the values from the users input.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_SaveContactDetails($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // whois information
    $contactDetails = $params['contactdetails'];


    // Build post data
    $toTranslate=['First Name','Last Name','Company Name','Address 1','City'];
    $types=['Registrant','Technical','Billing','Admin'];
    foreach ($types as $type){
        foreach ($contactDetails[$type] as $key=>$detail){
            if(in_array($detail,$toTranslate)){
                $contactDetails[$type][$key]=translate($detail);
            }
        }
        $contactDetails[$type]['phoneNumber']=phoneNumber($contactDetails[$type]['Phone Number']);
        $contactDetails[$type]['initials']=   substr($contactDetails[$type]['First Name'], 0, 1) . '.' . substr($contactDetails[$type]['Last Name'], 0, 1);
    }
    $contact=[];
    foreach ($types as $type){
        $contact[$type]=[
            'companyName'=>$contactDetails[$type]['Company Name'],
            'name'=>[
                'initials' => $contactDetails[$type]['initials'],
                'firstName' => $contactDetails[$type]['First Name'],
                'lastName' => $contactDetails[$type]['Last Name']
            ],
            'gender'=>'m',
            "phone"=>[
                'countryCode' => $contactDetails[$type]['phoneNumber']['countryCode2'],
                'areaCode' => $contactDetails[$type]['phoneNumber']['areaCode'],
                'subscriberNumber'=> $contactDetails[$type]['phoneNumber']['phoneNumber']
            ],
            'address' => [
                'street' => $contactDetails[$type]['Address 1'],
                'number' => "1",
                'zipcode' => $contactDetails[$type]['Postcode'],
                'state' => $contactDetails[$type]['State'],
                'city' => $contactDetails[$type]['City'],
                'country' => $contactDetails[$type]['Country']
            ],
            "email" => $contactDetails[$type]['Email Address'],
        ];
    }
    //$companyName=
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'contact'=>$contact,
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('UpdateWhoisInformation', $postfields);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Check Domain Availability.
 *
 * Determine if a domain or group of domains are available for
 * registration or transfer.
 *
 * @param array $params common module parameters
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 *
 * @throws Exception Upon domain availability check failure.
 *
 * @return \WHMCS\Domains\DomainLookup\ResultsList An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 */
function onlineserver_CheckAvailability($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    //return false;
    // availability check parameters
    $searchTerm = $params['searchTerm'];
    $punyCodeSearchTerm = $params['punyCodeSearchTerm'];
    $tldsToInclude = $params['tldsToInclude'];
    $isIdnDomain = (bool) $params['isIdnDomain'];
    $premiumEnabled = (bool) $params['premiumEnabled'];

    $sld=$params['sld'];
    $tld=$params['tldsToInclude'][0];
    if ($tld){
        //var_dump($params);
        $postfields=[
            'username'=>$username,
            'password'=>$password,
            'data'=>[
                'domain' => array(
                    'name' => $sld,
                    'extension' => ltrim($tld,'.')
                )
            ]
        ];

        try {
            $api = new ApiClient();
            $api->call('checkDomainRequest', $postfields);


            $results = new ResultsList();
            foreach ($api->results as $domain) {


                $domain_sld = explode('.', $domain['domain'])[0];
                $domain_tld = substr(str_replace($domain_sld, '', $domain['domain']), 1);

                // Instantiate a new domain search result object
                $searchResult = new SearchResult($domain_sld, $domain_tld);

                // Determine the appropriate status to return
                if($domain['status'] == 'free')
                    $status = SearchResult::STATUS_NOT_REGISTERED;
                else
                    $status = SearchResult::STATUS_REGISTERED;

                $searchResult->setStatus($status);

                // Return premium information if applicable
                /*if ($domain['isPremiumName']) {
                    $searchResult->setPremiumDomain(true);
                    $searchResult->setPremiumCostPricing(
                        array(
                            'register' => $domain['premiumRegistrationPrice'],
                            'renew' => $domain['premiumRenewPrice'],
                            'CurrencyCode' => 'USD',
                        )
                    );
                }*/

                // Append to the search results list
                $results->append($searchResult);
            }

            return $results;

        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }
}

/**
 * Domain Suggestion Settings.
 *
 * Defines the settings relating to domain suggestions (optional).
 * It follows the same convention as `getConfigArray`.
 *
 * @see https://developers.whmcs.com/domain-registrars/check-availability/
 *
 * @return array of Configuration Options
 */
function onlineserver_DomainSuggestionOptions() {
    return array(
        'includeCCTlds' => array(
            'FriendlyName' => 'Include Country Level TLDs',
            'Type' => 'yesno',
            'Description' => 'Tick to enable',
        ),
    );
}

/**
 * get Domain suggestions
 *
 * This is not available in OpenProvider yet.
 */
function onlineserver_GetDomainSuggestions($params)
{
    $results = new ResultsList();

    return $results;
}

/**
 * Get registrar lock status.
 *
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return string|array Lock status or error message
 */
function onlineserver_GetRegistrarLock($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ]
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('getLockStatus', $postfields);
        if ($api->results['locked']==1) {
            return 'locked';
        } else {
            return 'unlocked';
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Set registrar lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_SaveRegistrarLock($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // lock status
    $lockStatus = $params['lockenabled'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'isLocked'=> ($lockStatus == 'locked') ? 1 : 0,
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('setLockStatus', $postfields);
        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get DNS Records for DNS Host Record Management.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array DNS Host Records
 */
function onlineserver_GetDNS($params)
{

    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=>[
                'name'=>$sld,
                'extension'=>$tld
            ],
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('retrieveZoneDnsRequest', $postfields);

        // var_dump($api->results);
        $hostRecords = array();
        foreach ($api->results as $record) {
            $hostRecords[] = [
                "hostname" => $record['hostname'], // eg. www
                "type" => $record['type'], // eg. A
                "address" => $record['address'], // eg. 10.0.0.1
                "priority" => $record['priority'], // eg. 10 (N/A for non-MX records)
            ];
        }
        //var_dump($hostRecords);
        return $hostRecords;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Update DNS Host Records.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_SaveDNS($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // dns record parameters
    $dnsrecords = $params['dnsrecords'];
    $newRecords=[];
    foreach ($dnsrecords as $dnsrecord){
        if (empty($dnsrecord['address'])){
            continue;
        }
        $newRecords[]=[
            'type' => $dnsrecord['type'],
            'name' => $dnsrecord['hostname'],
            'value' => $dnsrecord['address'],
            'prio' => $dnsrecord['priority'],
            'ttl' => 86400
        ];
    }

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'records' => $newRecords,
        ]
    ];
    //var_dump(json_encode($postfields));
    try {
        $api = new ApiClient();
        $api->call('modifyZoneDnsRequest', $postfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Enable/Disable ID Protection.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_IDProtectToggle($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // id protection parameter
    $protectEnable = (bool) $params['protectenable'];

    // Build post data

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            'isPrivateWhoisEnabled'=>$protectEnable
        ]
    ];

    try {
        $api = new ApiClient();

        if ($protectEnable) {
            $api->call('enableIDProtection', $postfields);
        } else {
            $api->call('disableIDProtection', $postfields);
        }

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Request EEP Code.
 *
 * Supports both displaying the EPP Code directly to a user or indicating
 * that the EPP Code will be emailed to the registrant.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 *
 */
function onlineserver_GetEPPCode($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
            "authCodeType" => "external"
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('getAuthCode', $postfields);

        if ($api->getFromResponse('authCode')) {
            // If EPP Code is returned, return it for display to the end user
            return array(
                'eppcode' => $api->getFromResponse('authCode'),
            );
        } else {
            // If EPP Code is not returned, it was sent by email, return success
            return array(
                'success' => 'success',
            );
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Register a Nameserver.
 *
 * Adds a child nameserver for the given domain name.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_RegisterNameserver($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // nameserver parameters
    $nameserver = $params['nameserver'];
    $ipAddress = $params['ipaddress'];

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'name' => $nameserver,
            'ip'   => $ipAddress,
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
        ]
    ];
    try {
        $api = new ApiClient();
        $api->call('createNsRequest', $postfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Modify a Nameserver.
 *
 * Modifies the IP of a child nameserver.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_ModifyNameserver($params)
{

    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // nameserver parameters
    $nameserver = $params['nameserver'];
    $currentIpAddress = $params['currentipaddress'];
    $newIpAddress = $params['newipaddress'];


    $postfieldsget=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'name' => $nameserver,
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
        ]
    ];
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'name' => $nameserver,
            'ip'   => $newIpAddress,
        ]
    ];
    try {
        $api = new ApiClient();

        $api->call('retrieveNsRequest', $postfieldsget);
        if ($api->results['ip']==$currentIpAddress){
            $api->call('modifyNsRequest', $postfields);
        }else{
            throw new Exception($api->translate('Current ip wrong.'));
        }
        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Delete a Nameserver.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_DeleteNameserver($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // nameserver parameters
    $nameserver = $params['nameserver'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'name' => $nameserver,
            'domain'=> [
                'name' => $sld,
                'extension' => $tld
            ],
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('deleteNsRequest', $postfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Sync Domain Status & Expiration Date.
 *
 * Domain syncing is intended to ensure domain status and expiry date
 * changes made directly at the domain registrar are synced to WHMCS.
 * It is called periodically for a domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */

function onlineserver_Sync($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=>[
                'name'=>$sld,
                'extension'=>$tld
            ],
            'withAdditionalData'=> 0
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('retrieveDomainRequest', $postfields);

        return array(
            'expirydate' => $api->results['expirationDate'],
            'active' => (bool) $api->results['status']=='ACT',
            'expired' => (bool) $api->results['status']=='DEL',
            'pending' => (bool) $api->results['status']=='REQ',
            'Transferred Away' => (bool) $api->results['status']== null,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Incoming Domain Transfer Sync.
 *
 * Check status of incoming domain transfers and notify end-user upon
 * completion. This function is called daily for incoming domains.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function onlineserver_TransferSync($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    // Build post data
    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'domain'=>[
                'name'=>$sld,
                'extension'=>$tld
            ]
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('retrieveDomainRequest', $postfields);

        if($api->results['status'] == 'ACT')
        {
            return array
            (
                'completed'     =>  true,
                'expirydate'    =>  date('Y-m-d', strtotime($api->results['renewalDate'])),
            );
        }

        return array();
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Client Area Custom Button Array.
 *
 * Allows you to define additional actions your module supports.
 * In this example, we register a Push Domain action which triggers
 * the `onlineserver_push` function when invoked.
 *
 * @return array
 */
function onlineserver_ClientAreaCustomButtonArray()
{
    return array(
        'دیدن DNS های اختصاصی' => 'personalDNS',
    );
}

/**
 * Client Area Allowed Functions.
 *
 * Only the functions defined within this function or the Client Area
 * Custom Button Array can be invoked by client level users.
 *
 * @return array
 */
function onlineserver_ClientAreaAllowedFunctions()
{
    return array(
        'دیدن DNS های اختصاصی' => 'personalDNS',
    );
}

/**
 * Example Custom Module Function: Push
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array|mixed
 */
function onlineserver_personalDNS($params)
{
    // user defined configuration values
    $url = $params['Url'];
    $username = $params['Username'];
    $password = $params['Password'];

    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];

    $postfields=[
        'username'=>$username,
        'password'=>$password,
        'data'=>[
            'pattern'=>'*.'.$sld.'.'.$tld,
        ]
    ];

    try {
        $api = new ApiClient();
        $api->call('searchNsRequest', $postfields);

        $html='<br><br><h1>لیست DNS های اختصاصی</h1><table style="background: #fff;" class="table table-bordered"><tr><th>ادرس</th><th>ادرس IP</th></tr>';
        $result=$api->results;
        //var_dump($result);
        foreach ($result['results'] as $value){
            $html.=<<<HTML
				<tr>
					<td>{$value['name']}</td>
					<td>{$value['ip']}</td>
				</tr>
HTML;

        }
        $html.='</table>';

        return ['success' => $html];


    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Client Area Output.
 *
 * This function renders output to the domain details interface within
 * the client area. The return should be the HTML to be output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return string HTML Output
 */
function onlineserver_ClientArea($params)
{


    $id=(int)$_GET['id'];
    $output = <<<HTML
		<style>
			#tabOverview > ul,#tabOverview > h4{
				display: none;
			}
		</style>
        <div class="alert alert-info">
			 <a href="/my/clientarea.php?action=domains#tabRenew" class="btn btn-default"> تمدید دامنه </a>
             <a href="/my/#tabAutorenew" class="btn btn-default" data-toggle="tab">تمدید خودکار</a>
             <a href="/my/#tabNameservers" class="btn btn-default" data-toggle="tab">تغییر DNS</a>
             <a href="/my/#tabReglock" class="btn btn-default" data-toggle="tab">قفل کردن انتقال دامنه</a>
             <a href="/my/#tabAddons" class="btn btn-default" data-toggle="tab"> افزودنی ها</a>
             <a href="/my/clientarea.php?action=domaincontacts&domainid={$id}" class="btn btn-default">تغییر اطلاعات whois</a>
             <a href="/my/clientarea.php?action=domainregisterns&domainid={$id}" class="btn btn-default">مدیریت DNS اختصاصی</a>
             <a href="/my/clientarea.php?action=domaindns&domainid={$id}" class="btn btn-default">مدیریت DNS رکورد ها</a>
             <a href="/my/clientarea.php?action=domaingetepp&domainid={$id}" class="btn btn-default">دریافت کد انتقال</a>

HTML;
    if (isset($_GET['modop']) && isset($_GET['a']) && $_GET['a']=='personalDNS'){
        $output.=onlineserver_personalDNS($params)['success'];
    }
    $output.='</div>';
    return $output;
}
