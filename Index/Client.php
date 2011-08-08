<?php 
require_once 'Zend/Http/Client.php';


class IndexTank_Index_Client {
    protected $_indexName;
    protected $_public;
    protected $_code;
    protected $_creationDate;
    protected $_started;
    protected $_size;
    protected $_client;
    public function __construct($client = null, $indexName = null, $options = null){
        
        if(!($client instanceof IndexTank_Client)){
            $indexName = $client;
            $metaData = $indexName;
        } else {
            $this->_setClient( $client );
        }
        if($indexName !== null){
            $this->setIndexName($indexName);
        }
        
        $this->setOptions($options);
    }
    
    private function _setClient(IndexTank_Client $client){
        $this->_client = $client;        
    }
    
    public function getClient(){
        return $this->_getClient();
    }
    private function _getClient(){
        if(!isset($this->_client)){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("The Client has not been set yet for the IndexTank_Index_Client");
        }
        return $this->_client;
    }
  
    
    public function setIndexName($indexName){
          $this->_indexName = str_replace('/','',$indexName);     
    }
    
    public function getIndexName(){
          return $this->_indexName;    
    }
    
    public function clearOptions(){
        $this->_code = null;
        $this->_status = null;
        $this->_creationDate = null;
        $this->_public = null;
        $this->_size = null;
        $this->_started = null;
    }
    public function setOptions($metaData){
          $this->clearOptions();
           if ($metaData instanceof Zend_Config){
            $metaData = $metaData->toArray();
          }
          
          
          if(is_bool($metaData)){
              $this->_options['public_search'] = $metaData;
          } elseif ( is_null($metaData)) {
              $this->_options['public_search'] = false;
          } elseif ( is_array($metaData)){
              if(array_key_exists("public_search", $metaData)){
                 $this->setPublic ( $metaData['public_search'] );
              }
              if(array_key_exists("status", $metaData)){
                  $this->setStatus( $metaData['status'] );
              }
              if(array_key_exists("code", $metaData)){
                 $this->setCode( $metaData['code'] );
              }
              if(array_key_exists("creation_time", $metaData)){
                 $this->setCreationDate( $metaData['creation_time'] );
              }
              
              if(array_key_exists("size", $metaData)){
                  $this->setSize($metaData['size'] );
              }
              
              if(array_key_exists("started", $metaData)){
                  $this->setStarted ( $metaData['started'] );
              }
          }
    }
    
    public function setPublic($option){
      $this->_public = $option;  
    }
    
    public function setCode($option){
        $this->_code = (string) $option;    
    }
    
    public function setCreationDate($option){
         $this->_creationDate = $option;    
    }
    
    public function setSize($option){
        $this->_size = (integer) $option;     
    }
    
    public function setStatus($option){
          $this->_size = (boolean) $option;   
    }
    public function setStarted($option){
          $this->_started = (boolean) $option;   
    }
    
    public function isStarted(){
        return ( $this->_started == true );
    }
    
    public function useIndex($name){
        if(is_string($name)){
            $this->setIndexName( $name ); 
        }
        return $this;
    }
    public function getIndex($name){
        if(is_string($name)){
            $this->setIndexName( $name ); 
        }
                
        if(!$this->indexNameSet()){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("The IndexName you are attempting to retrieve is not valid.");
        }
       $uri = $this->_getClient()->getUri( 'indexes',  $this->getIndexName( ));
       $response = $this->_getClient()->get( $uri );  
       
       $this->setOptions( $response );
       return( $this );
    }
    
    public function addDocument($docId, $fields, $variables = null, $categories = null, $removeEmptyFields = false){
         $this->_checkDocId($docId);      
         $this->_checkIndexNameIsSet();
        
         if(!$this->indexNameSet()){
                require_once 'IndexTank/Exception/IndexTank.php';
                throw new IndexTank_Exception_IndexTank("The IndexName you are attempting to retrieve is not valid.");
         }
         
         // Remove Empty Field Results
         if($removeEmptyFields){
             foreach($fields as $key=>$item){
                 if($item == "" || is_null($item)){
                     unset($fields[$key]);
                 }
             }
         }
         
        
         foreach($variables as $key=>$item){
             if($item == "" || is_null($item)){
                 unset($variables[$key]);
             }
         }
      
         // Need to check for the length of $fields so that the sum does not exceed 100kbytes.         
         $uri = $this->_getClient()->getUri( 'indexing',  $this->getIndexName( ));
         $document = array(
                                                    	'docid'  => $docId,
                                                    	'fields' => $fields,
                                                        'variables'	=> $variables,
         												'categories'	=> $categories,
                                                      );
         if($variables == null || empty($variables)){
             unset($document['variables']);             
         }
         
         if($categories == null || empty($categories)){
             unset($document['categories']);
         }
         $response = $this->_getClient()->put( $uri, $document
                                                 );  
         return $response;
        
    }
    
    public function deleteDocument($docId){
        $this->_checkDocId($docId);      
        $this->_checkIndexNameIsSet();
        
        $uri = $this->_getClient()->getUri( 'indexing',  $this->getIndexName( ));
        $response = $this->_getClient()->delete( $uri, array('docid'  => $docId));
        
    }
    
    public function addVariables($docId, $variables){  
        $this->_checkDocId($docId);      
        $this->_checkIndexNameIsSet();
        
        $uri = $this->_getClient()->getUri( 'variables',  $this->getIndexName( ));
        
        $response = $this->_getClient()->put( $uri, array(
                                                    	'docid'  => $docId,
                                                        'variables'	=> $variables,
                                                      )
                                                 );  
         return $response;   
    }
    
    public function addCategories($docId, $categories){  
        $this->_checkDocId($docId);      
        $this->_checkIndexNameIsSet();
        
        $uri = $this->_getClient()->getUri( 'categories',  $this->getIndexName( ));
        
        $response = $this->_getClient()->put( $uri, array(
                                                    	'docid'  => $docId,
                                                        'categories'	=> $categories,
                                                      )
                                                 );  
         return $response;   
    }
    
    public function getSearchQueryBuilder(){
        return new IndexTank_Index_Search($this);        
    }
    
    public function search( $query, array $fetch = array('text'), $options = array()){        
        $options = $this->_processQueryOptions($options);        
        
    }
    
    private function _processQueryOptions($options){
        $item = array();
        if(isset($options["function"])){
            if(is_int((int) $options["function"]) && $options["function"] <= 5){
            $item['function'] = (string) (int) $options['function'];
            } else {
                 require_once 'IndexTank/Exception/IndexTank.php';
                 throw new IndexTank_Exception_IndexTank("Function Options must be an integer between 0 and 5.");
            }
        } 
        
        if(isset($options["fetch_variables"])){
             $item['fetch_variables'] = (boolean) $options['fetch_variables'];
        }
        
        if(isset($options["fetch_categories"])){
             $item['fetch_categories'] = (boolean) $options['fetch_categories'];
        }
        
        if(isset($options["snippet_fields"])){
             $item['snippet_fields'] = (string) $options['snippet_fields'];
        }
        if(isset($options["variables"])){
          
                foreach( $options["variables"] as $k => $v)
                {
                    $options["var".strval($k)] = $v;
                }
          unset($options['variables']);
           
        }
        
        if(isset($options["category_filters"])){
             $item['category_filters'] = (string) $options['category_filters'];
        }
        
        return $item;
        
    }
    
    private function _checkIndexNameIsSet(){
        if(!$this->indexNameSet()){
                        require_once 'IndexTank/Exception/IndexTank.php';
                        throw new IndexTank_Exception_IndexTank("The IndexName you are attempting to retrieve is not valid.");
        }
    }
    
    
    public function indexNameSet(){ 
        return ( $this->_indexName !== "" && isset( $this->_indexName ) );
    }
    
    private function _checkDocId($docId){
        if(strlen($docId) < 1){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("Your document identifier a non-empty string no longer than 1024 bytes");
        }
        
        if( mb_strlen($docId, '8bit') > 1024){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank(sprintf("Your document identifier %s is longer than 1024 bytes.", $docId));
        }

        
    }
    
    public function toArray()
    {
        return array(
            'name'         => $this->_indexName,
            'started'      => $this->_started,
            'code'         => $this->_code,
            'creationDate' => $this->_creationDate,
            'size'         => $this->_size
        );
    }


}