<?php 
require_once 'IndexTank/Index/Client.php';
require_once 'IndexTank/Index/Search.php';
require_once 'IndexTank/Index/Search/Result.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Json.php';
class IndexTank_Client
{
    
    const INDEX = "/v1/indexes/%s";
    const INDEXING = "/v1/indexes/%s/docs";
    const VARIABLES = "/v1/indexes/%s/docs/variables";
    const CATEGORIES = "/v1/indexes/%s/docs/categories";
    const FUNCTIONS = "/v1/indexes/%s/functions";
    const SCORING = "/v1/indexes/%s/functions/%d";
    const SEARCH = "/v1/indexes/%s/search";
    const PROMOTE = "/v1/indexes/%s/promote";
    const AUTOCOMPLETE = "/v1/indexes/%s/autocomplete";
    const API_HOSTNAME = "api.indextank.com";
    
    public function __construct($options = null){
        
        if($options == null){
            require_once 'IndexTank/Exception/NoConfigOptions.php';
            throw new IndexTank_Exception_NoConfigOptions();    
        }
        
        if(is_string($options)){
            // Assume that the $options is the privateUrl config option. 
            $this->setPrivateUrl($options);
        } elseif ( $options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif ( is_array($options)){
            if(!array_key_exists("privateUrl", $options)){
                require_once 'IndexTank/Exception/PrivateUrlNotSet.php';
                throw new IndexTank_Exception_PrivateUrlNotSet();    
            } else {
                $this->setPrivateUrl($options['privateUrl']);
            }
        } else {
           require_once 'IndexTank/Exception/PrivateUrlNotSet.php';
           throw new IndexTank_Exception_PrivateUrlNotSet();     
        }
        
       
    }
    public function getUri($endpoint, $name = false){
        return $this->_getUriAccessPoint($endpoint, $name);
    }
    private function _getUriAccessPoint( $endpoint, $name = false){        
     
        if(( strtolower( $endpoint ) != "indexes" ) && ($name == "" || $name === false)){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("You have not provided an IndexName for the URI endpoint.");
        }
        
        switch(strtolower( $endpoint)){
            case "indexes": 
                if($name == false){               
                    $uri = sprintf( self::INDEX, urlencode(str_replace('/', '', "")) );
                } else {
       
                    $uri = sprintf( self::INDEX, urlencode(str_replace('/', '', $name)) );
                }
            break;
            case "indexing":                
                $uri = sprintf( self::INDEXING, urlencode(str_replace('/', '', $name)) );
            break;
            case "variables":                
                $uri = sprintf( self::VARIABLES, urlencode(str_replace('/', '', $name)) );
            break;
            case "categories":                
                $uri = sprintf( self::CATEGORIES, urlencode(str_replace('/', '', $name)) );
            break;
            case "search":                
                $uri = sprintf( self::SEARCH, urlencode(str_replace('/', '', $name)) );
            break;
            default:
                require_once 'IndexTank/Exception/IndexTank.php';
                throw new IndexTank_Exception_IndexTank(sprintf( "The API access point: \"%s\" you are trying to use is not valid.", $endpoint));
            break;
        }
        
        return $uri;
        
    }
    
    public function setPrivateUrl($url){
        
       $apiUrl  = parse_url($url);
       if(!array_key_exists("pass",  $apiUrl)){
           require_once 'IndexTank/Exception/PrivateUrlPasswordNotSet.php';
           throw new IndexTank_Exception_PrivateUrlPasswordNotSet();     
       }
       $this->setPassword($apiUrl['pass']);   
       list($key,) = explode('.',  $apiUrl['host']);
       $this->setApiKey($key);       
       $this->setSslClient( $apiUrl['scheme'] == 'https' );
        
    }
    
    private function setApiKey( $key ){
        $this->_apiKey = $key;
        
    }
    
    private function _getApiKey( ){
        return $this->_apiKey;
    }
    
    private function setSslClient( $sslRequest ){
        if(!is_bool($sslRequest)){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("Argument passed to setSslClient is not of boolean type.");
        }
        $this->_useSsl = $sslRequest;  
    }
    

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }
    
    public function createIndex($name, $public = false, $waitUntilStarted = true){        
        $name = str_replace('/','',$name);
        if(strlen($name) < 1){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("The Index Name must not be empty and must not contain forward slashes '/'.");
        } 
      $this->_put( $this->_getUriAccessPoint( 'indexes', $name ), array( "public_search" => (boolean) $public ) );
           
      
        if($waitUntilStarted){
            
            $i = 0;
            while (!$this->getIndex($name)->isStarted() && $i < 5) {
                $i++;
                sleep(2);
            }
           
        }
         
       return $this->getIndex($name);
    }
    
    public function deleteIndex($name)
    {
        $this->_delete( $this->_getUriAccessPoint( 'indexes', $name ));
    }
    
    public function getIndex($name){
        $index = new IndexTank_Index_Client($this);
        return $index->getIndex($name);
        
    }
    
    public function useIndex($name){
        $index = new IndexTank_Index_Client($this);
        return $index->useIndex($name);
    }
    
    
    public function getAllIndexes(){
        return $this->_get( $this->_getUriAccessPoint( 'indexes' ));     
    }
    
    public function deleteAllIndexes(){        
        $indexes = $this->getAllIndexes();
    
        foreach($indexes as $indexName=>$index){
            $this->deleteIndex($indexName);
        }
    }
    
  public function delete($uri, $options = array()){
       return  $this->_delete( $uri, $options);    
    }
   private function _delete($uri, $options = array()){
       return  $this->_call( $uri, $options, 'DELETE');    
    }
    
    public function put($uri, $params = array()){
        return $this->_put( $uri, $params);    
    }
    
    private function _put($uri, $meta = array()){
       return  $this->_call( $uri, $meta, 'PUT');    
    }
    
    public function get($uri, $params = array()){
       
        return $this->_get( $uri, $params);    
    }
    
    private function _get($uri, $params = array()){
        return $this->_call( $uri, $params, 'GET');    
    }
    
    private function _call ( $uri , $params = array(), $method){
        $key = $this->_getApiKey();
        if(!isset($key)){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("API is not set for this IndexTank Client");
        }
        
        $url = ($this->_useSsl ? 'https' : 'http') . '://' . $this->_getApiKey() . "." . self::API_HOSTNAME;
        
        $client = $this->getHttpClient();
        $client->setAuth("", $this->getPassword());
        $client->setUri($url . $uri);
      
        if ($method == 'GET') {
            $client->setParameterGet($params);
        } else {
         
            $client->setRawData(Zend_Json::encode($params), 'application/json');
        }
       
        try {
            
            $response = $client->request($method);
        } catch (Zend_Http_Exception $e) {
          
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("The API Request failed with: " . $e->getMessage());
        }
       
        if (!$response->isSuccessful()) {
              
         
            $this->_checkCallResponse($response, $method, $uri);
            
            
            
            require_once 'IndexTank/Exception/HttpClient.php';
            throw new IndexTank_Exception_HttpClient('Http Request failed: (' . $response->getStatus() . ') ' .
                $response->getMessage() . ': ' . $response->getBody());
        } else {
            $this->_checkCallResponse($response, $method, $uri);
        }
        $data = Zend_Json::decode($response->getBody());
     
        return $data;
        
       
    }
    
    private function _checkCallResponse($response, $method, $uri){
      
        if($response->getStatus() == "404"){
            require_once 'IndexTank/Exception/HttpClient/Response/NoIndex.php'; 
            throw new IndexTank_Exception_HttpClient_Response_NoIndex("The Index does not exist");
        }
        
        if($response->getStatus() == "204"){
            if($method == "PUT") {
                require_once 'IndexTank/Exception/HttpClient/Response/IndexAlreadyExists.php'; 
                throw new IndexTank_Exception_HttpClient_Response_IndexAlreadyExists("The Index you are trying to create already exists");
            } else {
                require_once 'IndexTank/Exception/HttpClient/Response/IndexDoesNotExist.php'; 
                throw new IndexTank_Exception_HttpClient_Response_IndexDoesNotExist("The Index you are trying to delete does not exist");
            }
        }
        
        if($response->getStatus() == "409"){
           if(dirname($uri) == dirname(self::INDEX)){              
           
                require_once 'IndexTank/Exception/HttpClient/Response/TooManyIndexes.php'; 
                throw new IndexTank_Exception_HttpClient_Response_TooManyIndexes("The Index you are trying to create already exists");
           }
        }
    }
    
    protected static $httpClient = null;
    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }
    // Singleton HTTP Client.
    public static function getHttpClient()
    {
        if (!isset(self::$httpClient)) {
            self::$httpClient = new Zend_Http_Client;
           
        } else {
            self::$httpClient->resetParameters();
        }
        return self::$httpClient;
    }
    
    
    
    
    
}