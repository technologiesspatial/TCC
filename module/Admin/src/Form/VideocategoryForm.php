<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Video Category form
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

class VideocategoryForm extends Form
{
	/* Form constructor */
    public function __construct($translator)
    {
        parent::__construct('admin');
		$this->setAttribute('class','profile_form form-horizontal form-material');
		$this->setAttribute('enctype','multipart/form-data');
		$this->setAttribute('autocomplete','off');
		$this->translator=$translator;
    }

	/* Add / update video category form */
	public function addvideocategory()
	{
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'vc_name',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>'Category Name',
			),
			'attributes' =>array(
				'class'  => 'form-control required',    
				"placeholder" =>'Category Name', 
				"rows"=>"3",  
				'maxlength'=>TEXT_CONST_LENGTH
			 ),
		));	

		$this->addInputFilter("vc_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}

	/* Create CSRF for form (security reasons) */
	public function addcsrf()
	{
		$this->add (array(
			   	'type' => 'Zend\Form\Element\Csrf',
			   	'name' => 'post_csrf',
			   	'options' => array(
				   'csrf_options' => array(
						   'timeout' => CSRF_OPTIONS_TIMEOUT
					   )
			   	)
		   ));		
	}
	
	/* Create submit button to submit form */
	public function submitbtn($label=""){
		$this->add(array(
			'name' => 'bttnsubmit',
			'type' => 'submit',
			'ignore' => true,
			'options' =>array(
				'label' =>$label,
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'btn btn-raised btn-primary waves-effect waves-classic',	
			 ),
		));
	}

	/* Create validation term */
	private function getValidate($inputFilter,$ValidateElement)
	{
			$arrayValidate = array(
				"EmailAddress" => array(
					'name' => 'EmailAddress',
					'options' => [
					  'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
					  'useMxCheck' => false,                            
					],
				),
				"StringLength" => array(
					'name' => 'StringLength',
					 'options' => [
						'min' => 1,
						'max' => 3
					  ],
				),
				"NotEmpty" => array(
					'name' => "NotEmpty",
				)
			);


		if(!empty($ValidateElement)){
			foreach($ValidateElement as $key=>$value){
				 $inputFilter->add([
       				 'name'     => $key,
        			 'required' => true,
        			 'filters'  => [
         				  ['name' => 'StringTrim'],                    
        				],                
        			 'validators' => [
							$arrayValidate[$value],
						],
     				 ]
	   		 	);	
		 	}
		}
	}

	/* Create input filter terms */
	public function addInputFilter($element,$validation,$inputFilter)
	{		
		$validators=array(
			"EmailAddress"=> array(
				'name' => 'EmailAddress',
				'options' => [
				  'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
				  'useMxCheck' => false,                            
				],
			),
			"StringLength"=> array(
				'name' => 'StringLength',
				 'options' => [
					'min' => 1,
					'max' => 3
				  ],
			),
			"NotEmpty"=>array(
				'name'=>"NotEmpty",
			),
			"Identical"=> array(
				'name' => 'Identical',
				 'options' => [
					'token' => 'element_name',
				  ],
			),
		);


		$filters=array(
			  ['name' => 'StripTags'],   
			  ['name' => 'StripNewlines'],    
			  ['name' => 'StringTrim'],    
			  ['name' => 'HtmlEntities'], 
		);

		$newValidArr=array();
		if(isset($validation['validators'])){
			foreach($validation['validators'] as $allValid){
				if(is_array($allValid)){
					$keyVal=key($allValid);
					$mainKey=key($allValid[$keyVal]);
					if($mainKey=='length'){ // LENGTH
						$newValidArr[]=$validators[$keyVal];
						$newValidArr[count($newValidArr)-1]['options']['min']=$allValid[$keyVal][$mainKey][0];
						$newValidArr[count($newValidArr)-1]['options']['max']=$allValid[$keyVal][$mainKey][1];

					} else if($mainKey=='match'){ // MATCH
						$newValidArr[]=$validators[$keyVal];
						$newValidArr[count($newValidArr)-1]['options']['token']=$allValid[$keyVal][$mainKey];

					} else{

					}

				} else {
					$newValidArr[]=$validators[$allValid];
				}
			}
		}

		
		$newFilterArr=array();
		if(isset($validation['filters'])){
			foreach($validation['filters'] as $allValid){
				$newFilterArr[]=array('name'=>$allValid);
			}
		}

		if(isset($validation['required']) && $validation['required']==1){
			$inputFilter->add([
				'name'     => $element,
				'required' => true,
				'filters'  => $newFilterArr,                
				'validators' => $newValidArr
			  ]
			);

		} else {
			$inputFilter->add([
				'name'     => $element,
				'required' => false,
				'filters'  => $newFilterArr,                
				'validators' => $newValidArr
			  ]
			);
		}

	}

	

}