<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class RenderForm extends AbstractHelper {

    public function __invoke($form) {
        $form->prepare();
        $html = $this->view->form()->openTag($form) . PHP_EOL;       
        $html .= $this->renderElements($form->getElements());
        $html .= $this->view->form()->closeTag($form) . PHP_EOL;
        return $html;
    }

    public function renderElements($elements) {
        $html = '';
        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }
        return $html;
    }

    public function renderElement($element) {
         $html = '';
		
       
		$input_type_name=$element->getAttribute('name');
	
        if($element->getAttribute('type')!='submit'){
			$html = '<div class="form-group">';        
			$html .= '<label class="form-label" for="' . $element->getAttribute('id') . '">' . $element->getLabel() . '</label>'; 
			$html .= $this->view->formElement($element);
			$html .= $this->view->FormElementErrors($element, array('class' => 'form-validation-error'));
			$html .='</div>';
			$html .='<div class="clearfix"></div>';				
		}
		
		if($element->getAttribute('type')=='submit'){			
			$html .= '<div class="text-center"><button type="submit" class="btn fcbtn btn-outline btn-info btn-1e btn btn-default">'.$element->getLabel().'</button></div>';
		}
        return $html . PHP_EOL;
    }

}