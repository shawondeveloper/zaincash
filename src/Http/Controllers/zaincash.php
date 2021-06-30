<?php
namespace codignwithshawon\zaincash\Http\Controllers;

use \Firebase\JWT\JWT;
use \GuzzleHttp\Client as HTTPClient;

use \Exception;
use \DateTime;

class ZainCash
{
  
  //Wallet Number   (ZainCash IT will provide it)
    //ex: 9647800624733
    private $msisdn="";

    //Secret   (ZainCash IT will provide it)
    //ex: 1zaza5a444a6e8723asd123asd123sfeasdase12312davwqf123123xc2ego
    private  $secret='';

    //Merchant ID   (ZainCash IT will provide it)
    //ex: "57f3a0635a726a48ee912866"
    private  $merchantid='';

    //Test credentials or Production credentials (true=production , false=test)
    private $production_cred=false;

    //Language 'ar'=Arabic / 'en'=english
    private  $language='ar';
    //--------------------------------------------

    // After a successful or failed order, the user will get redirected to this url
    // ex: 'https://example.com/redirect.php'
    // or  'https://example.com/order/redirect';
    // NOTE api will return with GET parameter (token): https://example.com/redirect.php?token=XXXXXXXXXXXXXX
    private $redirection_url = '';
	
	
	//Local certificate verification
	private $verifycert=false;



    //--------------------------------------------

    /**
     * Transaction Endpoints
     * will be set automatically after credentials are set in __construct
     */
    private  $transactionInitURL='';
    private  $transactionRedirectURL='';

    /**
     * Status flag to check if should perceed on setTransactionEndpoints() / initiatingTransaction()
     * will be set to true in setCredentials()
     */
    private $gotCredentials = false;

    public function __construct(array $credentials)
    {
      if ($this->verifyCredentials($credentials) ) {
        $this->setCredentials($credentials);
        $this->setTransactionEndpoints();
      }
    }

    /**
     * Verifys that credentials are set with correct types
     * @param array
     * @return boolean , when all credentials are set with the correct type.
     */
    private function verifyCredentials(array $creds)
    {
      if ( !isset($creds['msisdn']) || !is_string($creds['msisdn']) ){
        throw new Exception("ERROR: msisdn is not set or has an invalid type, it should be a string.", 1);
        return false;
      }
      if ( !isset($creds['secret']) || !is_string($creds['secret']) ){
        throw new Exception("ERROR: secret is not set or has an invalid type, it should be a string.", 1);
        return false;
      }
      if ( !isset($creds['merchantid']) || !is_string($creds['merchantid']) ){
        throw new Exception("ERROR: merchantid is not set or has an invalid type, it should be a string.", 1);
        return false;
      }
      if ( !isset($creds['production_cred']) || !is_bool($creds['production_cred']) ){
        throw new Exception("ERROR: production_cred is not set or has an invalid type, it should be a boolean.", 1);
        return false;
      }
      if ( !isset($creds['language']) || !is_string($creds['language']) || !in_array($creds['language'] , ['ar','en']) ){
        throw new Exception("ERROR: language is not set or has an invalid type, it should be a string with value of 'ar' or 'en'.", 1);
        return false;
      }
      if ( !isset($creds['redirection_url']) || !is_string($creds['redirection_url']) ){
        throw new Exception("ERROR: redirection_url is not set or has an invalid type, it should be a string.", 1);
        return false;
      }
      return true;
    }

    /**
     * Sets credentials to the object instance.
     * @param array
     * @return void
     */
    private function setCredentials(array $creds)
    {
      $this->gotCredentials = true;

      $this->msisdn = $creds['msisdn'];
      $this->secret = $creds['secret'];
      $this->merchantid = $creds['merchantid'];
      $this->production_cred = $creds['production_cred'];
      $this->language = $creds['language'];
      $this->redirection_url = $creds['redirection_url'];
    }

    /**
     * Sets $transactionInitURL , $transactionRedirectURL according to $production_cred value
     */
    private function setTransactionEndpoints()
    {
      $this->checkCredentials();

      if($this->production_cred){
        $this->transactionInitURL = 'https://api.zaincash.iq/transaction/init';
        $this->transactionRedirectURL = 'https://api.zaincash.iq/transaction/pay?id=';
      }else{
        $this->transactionInitURL = 'https://test.zaincash.iq/transaction/init';
        $this->transactionRedirectURL = 'https://test.zaincash.iq/transaction/pay?id=';
      }
    }

    /**
     * Checks if the instace has the needed credentials
     * @return boolean
     */
    private function checkCredentials()
    {
      if($this->gotCredentials === false) throw new Exception("ERROR: Credentials not provided", 1);
    }

    /**
     * @param integer $amount, amount of money in Iraqi Dinars
     * @param string $service_type, a Merchant defined string to describe the transaction
     *        ex: 'Product purchase', 'Subscription fees', 'Hosting fees'
     * @param string $order_id,MAX:512 chars,  a Merchant defined string to label the transaction
     *        ex: 'Bill_1234567890' , 'Receipt_004321'
     */
    public function charge(int $amount, string $service_type, string $order_id)
    {
      //encodes data to JWt token
      $jwt_token = $this->encode($amount, $service_type, $order_id);

      //prepares JWT and other data for http
      $http_context = $this->prepareHttpRequest($jwt_token);

      //sends http request
      $http_response = $this->sendHttpRequest($http_context);
	  

      //handles http response and return redirection url
      $redirect_url = $this->handleHttpResponse($http_response);

      //redirects to redirection url
      $this->redirect($redirect_url);
    }

    /**
     * Encodes the data to JWT
     * @param integer $amount, amount of money in Iraqi Dinars
     * @param string $service_type, a Merchant defined string to describe the transaction
     *        ex: 'Product purchase', 'Subscription fees', 'Hosting fees'
     * @param string $order_id,MAX:512 chars,  a Merchant defined string to label the transaction
     *        ex: 'Bill_1234567890' , 'Receipt_004321'
     * @return string(JWT) $token
     */
    private function encode(int $amount, string $service_type, string $order_id)
    {
      $now = new DateTime();
      $payload = [
        'amount'  => $amount,
        'serviceType'  => $service_type,
        'msisdn'  => $this->msisdn,
        'orderId'  => $order_id,
        'redirectUrl'  => $this->redirection_url,
        'iat'  => $now->getTimestamp(),
        'exp'  => $now->getTimestamp()+60*60*4
      ];

      $token = JWT::encode(
        $payload,      //Data to be encoded in the JWT
        $this->secret,
        'HS256'
      );

      return $token;
    }

    /**
     * @param string $token, JWT token from redirection $_GET
     * @return array $result
     */
    public function decode(string $token)
    {
	       $result= (array) JWT::decode($token, $this->secret, array('HS256'));
     		/*
        Example of $result
     		array(5) {
     			["status"]=>
     			string(7) "success"
     			["orderid"]=>
     			string(9) "Bill12345"
     			["id"]=>
     			string(24) "58650f0f90c6362288da08cf"
     			["iat"]=>
     			int(1483018052)
     			["exp"]=>
     			int(1483032452)
     		}
     		*/

         return $result;
    }
    /**
     * @param string $token, JWT token from encode()
     * @return array $requestBody
     */
    private function  prepareHttpRequest($token){

      $requestBody = [
        'form_params' => [ //this option automatically sets header ': application/x-www-form-urlencoded'
            'token' => urlencode($token), // JWT Token
            'merchantId' => $this->merchantid,
            'lang' => $this->language,
        ],'verify' => $this->verifycert
      ];

      return $requestBody;
    }


    /**
     * sends http request and return the response body string
     * @param HTTPRequest $request
     * @return (string)JSON response body
     */
    private function sendHttpRequest(array $requestBody){
      $client = new HTTPClient();

      $response = $client->request('POST', $this->transactionInitURL, $requestBody);

      if($response === false || $response === null) throw new Exception("ERROR: Failing to contact api, communication layer issue.");

      return $response->getBody();
    }

    /**
     * handles http response and return the redirection url
     * @param (string)JSON @response
     * @return (string)URL redirection-url
     */
    private function handleHttpResponse(string $response){
      $array = json_decode($response, true);

      if(isset($array['err'])) throw new Exception("ERROR: Transaction request failed (".$array['err']['msg'] .")" );

      $transaction_id = $array['id'];
      $newurl = $this->transactionRedirectURL . $transaction_id;

      return $newurl;
    }

    /**
     * Redirects to the api callback url
     * @param string $url
     */
    private function redirect($url){
      header('Location: '.$url);
      exit();
    }


}