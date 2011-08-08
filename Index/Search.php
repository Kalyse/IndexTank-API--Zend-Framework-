<?php
class IndexTank_Index_Search {
    protected $_q;
    protected $_start;
    protected $_len;
    protected $_function;
    protected $_fetch = null;
    protected $_fetch_variables;
    protected $_fetch_categories;
    protected $_snippet;
    protected $_var0;
    protected $_var1;
    protected $_var2;
    protected $_category_filters;
    protected $_filter_docvar0;
    protected $_filter_docvar1;
    protected $_filter_docvar2;
    
    protected $_index;
    
    public function __construct($index){        
        $this->_index = $index;
    }
    
    public function setQuery($q){
        $this->_q = $q;
        return $this;
    }
    
    public function setStart($start){
        $this->_start = $start;
        return $this;
    }
    public function setLength($length){
        $this->_len = $length;
        return $this;
    }
    public function setFunction($function){
        $this->_function = $function;
        return $this;
    }
    public function setFetchFields($fields){
        $this->_fetch = $fields;
        return $this;
    }
    public function setFetchVariables($variables){
        $this->_fetch_variables = $variables;
        return $this;
    }
    public function setFetchCategories($categories){
        $this->_fetch_categories = $categories;
        return $this;
    }
    public function setSnippet($snippet){
        $this->_snippet = $snippet;
        return $this;
    }
    
    public  function mapRange($range) {
        return sprintf("%s:%s",($range[0] == NULL ? "*": $range[0]), ($range[1] == NULL ? "*": $range[1]));
    }
    
    public function setFilterDocVar0($bottom, $top = null){
       $this->_setFilterDoc(0, $bottom, $top);  
       return $this;
    }
    
    public function setFilterDocVar1($bottom, $top = null){
       $this->_setFilterDoc(1, $bottom, $top); 
       return $this; 
    }
    
    public function setFilterDocVar2($bottom, $top = null){
          $this->_setFilterDoc(2, $bottom, $top);  
          return $this;    
    }
    
    public function addCategoryFilters($categories){
        
        foreach($categories as $key=>$values){
            $this->addCategoryFilter($key, $values);
        }
        return $this;
    }
    
    public function addCategoryFilter($category, $values = array("1")){
        if(!is_array($values)){
            $this->_category_filters[$category] = array( $values );
        } else {
            $this->_category_filters[$category] = $values;
        } 
        return $this;
        
    }
    
    public function getCategoryFilters(){
       return  Zend_Json::encode($this->_category_filters);
    }
    
    
    private function _setFilterDoc($var, $bottom, $top = null){
      
        if(!is_int($var) || $var > 3){
            require_once 'IndexTank/Exception/IndexTank.php';
            throw new IndexTank_Exception_IndexTank("You are trying to set a variable which is larger than 3.");
        }
        if(is_array( $bottom )){
            // Assume lots of Filters.
            $this->{"_filter_docvar" . $var} = implode(array_map( array( $this, 'mapRange' ), $bottom), ",");
            return; 
        } elseif(isset($bottom) && isset($top)) {
              $this->{"_filter_docvar" . $var} = implode(array_map( array( $this, 'mapRange' ) , array( array( $bottom, $top )) ), ",");             
        }
      
    }
    
    public function setVar0($var){
        $this->_var0 = $var;
        return $this;
    }
    
    public function setVar1($var){
        $this->_var1 = $var;
        return $this;
    }
    
    public function setVar2($var){
        $this->_var2 = $var;
        return $this;
    }
    
    public function __toString(){
        
        $q = array(
            'q' => $this->_q,
            'start'	=> $this->_start,
            'len'	=> $this->_len,
            
        );
        $query = Zend_Json::encode($q);
        return $query;
        
    }
    
    public function toArray(){
       
       $search = array(
            'q' => $this->_q,
            'start'	=> $this->_start,
            'len'	=> $this->_len,
        	'filter_docvar0'	=> $this->_filter_docvar0,
       		'filter_docvar1'	=> $this->_filter_docvar1,
            'filter_docvar2'	=> $this->_filter_docvar2,
            'fetch'	=>    $this->_fetch,
            'category_filters'	=> $this->getCategoryFilters()
        );
        
        if($search['category_filters'] == null || $search['category_filters'] == "null"){
            unset($search['category_filters']);
        }
       
        return $search;
        
    }
    
    public function execute(){
        $response = $this->_index->getClient()->get( $this->_index->getClient()->getUri( 'search',  $this->_index->getIndexName( )), $this->toArray()  );
        // Build a Search Result Object from the Response
        $result = new IndexTank_Index_Search_Result();
        $result->setResult($response);
        return $result;        
    }
    
    

}