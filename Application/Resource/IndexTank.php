<?php 
require_once 'IndexTank/Client.php';
class IndexTank_Application_Resource_IndexTank extends Zend_Application_Resource_ResourceAbstract
{
    protected $_apiClient;
    
    public function init() {
        
        return $this->getIndexTankApiClient();
    }
    
    private function getIndexTankApiClient(){
        $options = $this->getOptions();
        $this->_apiClient = new IndexTank_Client($options);
        Zend_Registry::set('IndexTank', $this->_apiClient);
        return $this->_apiClient;
    }
}