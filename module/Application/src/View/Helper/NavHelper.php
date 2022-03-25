<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class NavHelper extends AbstractHelper {

   public function __invoke() 
   {
       $return_nav=$this->getNavArray();
	   return $return_nav;
   }

   public function getNavArray(){
	  
		 return $pages;
	}
  

}
