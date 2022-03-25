<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Advertisement request forms
* * * * * * * * * * * * * * * * * * * * * */
namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

class AdvertisementForm extends Form
{
	/* Constructor of the form */
    public function __construct()
    {
		parent::__construct('application');
		$this->setAttribute('class','advertisement_form');
		/*$this->translator = $translator;*/
	}
	
	/* Advertisement request form */
	public function advertisement_request()
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
				'class'  => 'required form-control',    
				'id' => 'user_name',
				'placeholder' => 'Full Name',
				'maxlength'=>TEXT_CONST_LENGTH
			 ),
		));

		$this->addInputFilter("user_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		$this->add(array(
			'name' => 'company_name',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',    
				'id' => 'company_name',
				'placeholder' => 'Company Name',
				'maxlength'=>TEXT_CONST_LENGTH
			 ),
		));

		$this->addInputFilter("company_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		$this->add(array(
			'name' => 'phone_number',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',    
				'id' => 'phone_number',
				'placeholder' => 'Phone Number',
				'maxlength'=>15
			 ),
		));

		$this->addInputFilter("phone_number",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		$this->add(array(
			'name' => 'email',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control email',    
				 'id' => 'email',
				 'placeholder' => 'Email Address',
				 'maxlength'=>TEXT_CONST_LENGTH
			),
		));
		
		$this->addInputFilter("email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);


		$position_options = array(Null => 'Choose Space') + array('1' => 'Home Page', '2' => 'Category Page');
		$this->add(array(
			'name' => 'ads_position',
			'type' => 'select',
			"required"=>true,
			'options' =>array(
				'label' => 'Choose Space',
				'value_options' => $position_options,
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control',
				"id" => 'ads_position',
			 ),
		));

		$this->addInputFilter("ads_position",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);

		$this->add(array(
			'name' => 'bid_price',
			'type' => 'text',
			'attributes' =>array(        // Array of attributes
				'class'  => 'required form-control number',
				'id' => 'bid_price',
				'placeholder' => 'Bid Price',
				'maxlength' => 6
			 ),
		));

		$this->addInputFilter("bid_price",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);

		$this->add(array(
			'name' => 'client_accepted_terms',
			'type' => Element\Checkbox::class,
			'attributes' => array(
				'id' => 'client_accepted_terms',
				'class' => 'form-control required',
				'required' => 'required'
			),
			 'options' => array(
				/*'label' => 'I have read and accept terms',*/
			)
		));

		$this->addInputFilter("client_accepted_terms",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

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