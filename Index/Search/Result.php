<?php 
class IndexTank_Index_Search_Result implements RecursiveIterator, Countable{    
    protected $_matches;
    protected $_query;
    protected $_facets;
    protected $_search_time;
    protected $_results = array();
    
    public function setResult($result){
        if(!isset($result['matches'])){
             require_once 'IndexTank/Exception/Result/MissingIndex.php';
            throw new IndexTank_Exception_Result_MissingIndex("The Result is missing the 'matches' index.");
        }
        if(!isset($result['query'])){
            require_once 'IndexTank/Exception/Result/MissingIndex.php';
            throw new IndexTank_Exception_Result_MissingIndex("The Result is missing the 'query' index.");
        }   
        if(!isset($result['facets'])){
             require_once 'IndexTank/Exception/Result/MissingIndex.php';
            throw new IndexTank_Exception_Result_MissingIndex("The Result is missing the 'facets' index.");
        }   
        if(!isset($result['search_time'])){
            require_once 'IndexTank/Exception/Result/MissingIndex.php';
            throw new IndexTank_Exception_Result_MissingIndex("The Result is missing the 'search_time' index.");
        }       
        
        if(!isset($result['results'])){
            require_once 'IndexTank/Exception/Result/MissingIndex.php';
            throw new IndexTank_Exception_Result_MissingIndex("The Result is missing the 'results' index.");
        }   
        
        $this->setMatches( $result['matches']);
        $this->setQuery( $result['query']);
        $this->setFacets( $result['facets']);
        $this->setSearchTime( $result['search_time']);
        $this->setDocuments( $result['results']);
        
    }
    // This inverses facet results, so that you can support grouped facets, where you might want to put a document
    // Into multiple categories, but you couldn't because you can't save multiple values under the same category.
    /*
     *  	[Wilderness] => Array
                (
                    [theme] => 2
                )
 
            [Public Villas & Gardens] => Array
                (
                    [attraction] => 1
                )
 
            [Countryside] => Array
                (
                    [type] => 2
                )
            [Adventure] => Array
                (
                    [theme] => 1
                )
 
            [Budget] => Array
                (
                    [theme] => 1
                )
 
           Will create :
           Theme => Wilderness => 2
           		 => Adventure => 1
           		 => Budget => 1
           Attraction => Public ... => 1
           Type => Countryside =>2    
     */
    public function getInversedFacets($sort = true, $limit = false){        
        $facetGroups = array();
        foreach($this->getFacets() as $key=>$facet){
            $facetGroups[key($facet)][$key] = current($facet);
        }
        foreach($facetGroups as &$facet){
            arsort($facet);
        }
        return $facetGroups;
    }
    
    public function setMatches($matches){
        $this->_matches = $matches;
    }
    
    public function setQuery($query){
        $this->_query = $query;
    }
    
    public function setFacets($facets){
        $this->_facets = $facets;
    }
    
    public function setSearchTime($searchTime){
        $this->_search_time = $searchTime;
    }
    
    public function setDocuments(array $documents = array()){
       $this->_results = $documents;
    }
    
    public function getFacets(){
        return $this->_facets;
    }
    
    public function getSearchTime(){
        return $this->_searchTime;
    }
    
    public function getQuery(){
        return $this->_query;
    }
    
    public function getMatches(){
        return $this->_matches;
    }
    
    
    public function hasResults(){        
         return count($this->_results) > 0;        
    }
    
    public function getResults(){
       
       
       return $this->_results;
      
        return null;
    }
    
    
    // RecursiveIterator interface:

    public function current()
    {
        
        current($this->_results);
       
    }

    public function key()
    {
        return key($this->_results);
    }

  
    public function next()
    {
        next($this->_results);
    }

  
    public function rewind()
    {
        reset($this->_results);
    }

   
    public function valid()
    {
        return current($this->_results) !== false;
    }

  
    public function hasChildren()
    {
       return  $this->hasResults();
    }

  
    public function getChildren()
    {
      return $this->getResults();
    }

    // Countable interface:  
    public function count()
    {
        return count($this->_results);
    }
    
    
}