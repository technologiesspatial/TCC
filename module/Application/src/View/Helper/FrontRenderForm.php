<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class FrontRenderForm extends AbstractHelper {

    public function __invoke($form,$view='full') {
        $form->prepare();
        $html = $this->view->form()->openTag($form) . PHP_EOL;       
        $html .= $this->renderElements($form->getElements(),$view);
        $html .= $this->view->form()->closeTag($form) . PHP_EOL;
        return $html;
    }


    public function renderElements($elements,$view) {
		global $subset;
        $html = '';$allele=1;$countele=1;//$subset=array();
        foreach ($elements as $element) {
          $html .= $this->renderElement($element,$allele,$view,$subset,$countele);
			$allele++;
        }
        return $html;
    }

     public function renderElement($element,$allele,$view,$subset,$countele) {
		global $subset;
        // FORM ROW
		
         $html = '';$breakheading='';
		if($element->getAttribute('data-title')!=''){
			$exp_elements=explode("SITE_",$element->getAttribute('data-title'));
			$breakheading=trim($exp_elements[1]);
			
		}
   
		$input_type_name=$element->getAttribute('name');
		
		
        if($element->getAttribute('type')!='submit'){
			
			if($element->getAttribute('type')=='button'){
				$html .= '<div class="text-center"><button type="button" id="' . $element->getAttribute('id') . '"  class="'.$element->getAttribute('class').'">'.$element->getLabel().'</button></div>';
			}
			else{

				if($breakheading!='' && $subset!='' && !array_search($breakheading,$subset)){
					
					$html.=' <div class="clearfix"></div><h3 class="box-title">'.str_replace("_"," ",$breakheading).'</h3> <hr>';
					$subset[$countele]=$breakheading;
					$countele++;$allele=1;
				}
				if($view!='full'){ 
					if(strpos($element->getAttribute('class'),'ckeditor') || strpos($element->getAttribute('class'),"w-100")){
						$html .= '<div class="col-sm-12">';
					}
					else{
						$html .= '<div class="col-sm-6">'; 
					}
					$html .= '<div class="form-group">';    
				}else{
					$html .= '<div class="form-group">';    
				}
				     
				$html .= '<label class="form-label" for="' . $element->getAttribute('id') . '">' . $element->getLabel() . '</label>'; 
				$html .= $this->view->formElement($element);
				$html .= $this->view->FormElementErrors($element, array('class' => 'form-validation-error'));
				$html .='</div>';
				if($view=='full'){ 
				$html .='<div class="clearfix"></div>';				
				}else{
					
						$html .='</div>';
				
					if($allele%2==0){
						$html .='<div class="clearfix"></div>';	
					}
				
				}
			}
		}

		if($element->getAttribute('type')=='submit'){
			if($view!='full'){ 
				$html .='<div class="clearfix"></div>';	
			}
			$html .= '<div class="text-center"><button type="submit" class="'.$element->getAttribute('class').'">'.$element->getLabel().'</button></div>';
		}
        return $html . PHP_EOL;
    }

}