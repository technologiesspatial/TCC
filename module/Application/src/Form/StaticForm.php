<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Static forms of entire website
* eg. newsletter, contact etc.
* * * * * * * * * * * * * * * * * * * * * */
namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

class StaticForm extends Form
{
    public function __construct()
    {
		parent::__construct('application');
		$this->setAttribute('class','profile_form');
		/*$this->translator=$translator;*/
	}
	
	
	public function newsletter()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

	 	$this->add(array(
            'name' => 'subscribe_email',
            'type' => 'text',
			'required'=>true,
            'options' =>array(
            ),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required email getsubscriber',    
				'placeholder' => "",
				'id'=>'subscribe_email',
				'required'=>true,
				'maxlength'=>TEXT_CONST_LENGTH
			 ),
        ));

		$this->addInputFilter("subscribe_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		/*$this->add([
				'type' => Element\Csrf::class,
				'name' => 'post_csrf',
				'options' => [
				'csrf_options' => [
				   'timeout' => CSRF_OPTIONS_TIMEOUT,
				],
			],
		 ]);	*/
	}

	/* Contact us - Enquiry form */
	public function contact()
    {
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->add([
				'type' => Element\Csrf::class,
				'name' => 'post_csrf',
				'options' => [
				'csrf_options' => [
				   'timeout' => CSRF_OPTIONS_TIMEOUT,
				],
			],
		 ]);

		 $this->addInputFilter("post_csrf",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		$this->add(array(
			'name' => 'user_name',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',	//lettersonly
				'id' => 'user_name',
				'placeholder' => 'Name* ',
				'maxlength'=>TEXT_CONST_LENGTH
			 ),
			 'value_options' =>array(        // Array of attributes
				'label'=>'aa'
			 ),
		));

		$this->addInputFilter("user_name",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);
		
		$this->add(array(
			'name' => 'user_email',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control email',    
				 'id' => 'user_email',
				 'placeholder' => 'Email*',
				 'maxlength'=>TEXT_CONST_LENGTH
			),
		));
		
		$this->addInputFilter("user_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);	

		$this->add(array(
			'name' => 'user_subject',
			'type' => 'text',
			'options' => array(
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',    
				'id'=>'user_subject',
				'placeholder'=>'Subject',
				'maxlength'=>TEXT_CONST_LENGTH
			),
		));

		$this->add(array(
			'name' => 'user_message',
			'type' => 'textarea',
 			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',    
				'rows'  => '5', 
				'id' => 'user_message',
				'placeholder' => 'Message*',
				'maxlength'=>TEXTAREA_CONST_LENGTH
			),
		));
		
		$this->addInputFilter("user_message",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

    }

	public function addInputFilter($element,$validation,$inputFilter)
	{
		$validators = array(
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
			"Alpha"=> array(
				'name' => 'Alpha',
				 'options' => [
					'allowWhiteSpace' => true,
				  ],
			),
			"Identical"=> array(
				'name' => 'Identical',
				 'options' => [
					'token' => 'element_name',
				  ],
			),
		);
		
		$filters = array(
			  ['name' => 'StripTags'],   
			  ['name' => 'StripNewlines'],    
			  ['name' => 'StringTrim'],    
			  ['name' => 'HtmlEntities'], 
		);

		$newValidArr = array();
		if(isset($validation['validators'])){
			foreach($validation['validators'] as $allValid){
				if(is_array($allValid)){
					$keyVal = key($allValid);
					$mainKey = key($allValid[$keyVal]);
					if($mainKey == 'length'){ // LENGTH
						$newValidArr[] = $validators[$keyVal];
						$newValidArr[count($newValidArr)-1]['options']['min'] = $allValid[$keyVal][$mainKey][0];
						$newValidArr[count($newValidArr)-1]['options']['max'] = $allValid[$keyVal][$mainKey][1];

					} else if($mainKey == 'match'){ // MATCH
						$newValidArr[] = $validators[$keyVal];
						$newValidArr[count($newValidArr)-1]['options']['token'] = $allValid[$keyVal][$mainKey];

					} else {

					}

				} else {
					$newValidArr[] = $validators[$allValid];
				}
			}
		}
		
		$newFilterArr = array();
		if(isset($validation['filters'])){
			foreach($validation['filters'] as $allValid){
				$newFilterArr[] = array('name'=>$allValid);
			}
		}
		
		if(isset($validation['required']) && $validation['required'] == 1){
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