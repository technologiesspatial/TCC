<?php
/* * * * * * * * * * * * * * * * * * * * * *
* User / Member Account form
* * * * * * * * * * * * * * * * * * * * * */
namespace AuthAcl\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class UserForm extends Form
{
	/* Form constructor */
    public function __construct($translator)
    {
        parent::__construct('auth');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'profile_form');
		//$this->setAttribute('autocomplete', 'off');
		$this->translator = $translator;
		
    }

    /* Form CSRF token */
	public function postcsrf($inputFilter){
		$this->add([
				'type' => Element\Csrf::class,
				'name' => 'post_csrf',
				'options' => [
				'csrf_options' => [
				   'timeout' => CSRF_OPTIONS_TIMEOUT,
				],
			],
		 ]);

		 $this->addInputFilter("post_csrf",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
	}

	/* User / Member login Form */
	public function login()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);

		$this->add(array(
            'name' => 'client_email',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_email',
                'class' => 'form-control required',
				'placeholder' => $this->translator->translate(""),	     
            ),
			 'options' => array(
                // 'label' => $this->translator->translate("email_txt"),	      
            )
        ));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

        $this->add(array(
            'name' => 'client_password',
            'type' => 'password',
            'attributes' => array(
			 	'id' => 'client_password',
                'class' => 'form-control required',
				'placeholder' => $this->translator->translate(""),
				'maxlength'=>30	     
			),
            'options' => array(
				//'label' => $this->translator->translate("pass_txt"),	               
            )
        ));

		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))) ),"filters"=>array()),$inputFilter);

	}
public function twitter_email(){
	$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);
		
		$this->add(array(
			'name' => 'client_email',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_email',
				'class' => 'form-control email required',	//checkemail
				'placeholder' => $this->translator->translate("Your Email Address"),
				'maxlength'=>TEXT_CONST_LENGTH	  
			),
			'options' => array(
				//'label' => $this->translator->translate("email_txt"),	      
			)
		));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add( array(
			'name' => 'bttnsubmit',
			'type' => 'submit',
			'ignore' => true,
			'options' => array(
				'label' => 'Save',
			),
			'attributes' => array( // Array of attributes
				'class' => 'btn submit-btns hvr-grow-shadow back-homebtn',

			),
		) );
}
	/* User registation */
	public function user_register()
	{	
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);
		
		$this->add(array(
			'name' => 'first_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'first_name',
				'class' => 'form-control required lettersonly',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("first_name",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'last_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'last_name',
				'class' => 'form-control required lettersonly',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("last_name",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'user_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'user_name',
				'class' => 'form-control required checkuname',
				'maxlength'=>'40',
				'autocomplete'=>'user-name'
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("user_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_email',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_email',
				'class' => 'form-control email checkemail required',	//checkemail
				'maxlength'=>TEXT_CONST_LENGTH,	  
			),
			'options' => array(
				//'label' => $this->translator->translate("email_txt"),	      
			)
		));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_password',
			'type' => 'password',
			'attributes' => array(
				'id' => 'client_password',
				'class' => 'form-control required passcheck',
				'maxlength'=>30,
				'autocomplete'=>'new-password'
			),
			'options' => array(
				'autocomplete'=>'new-password'
				//'label' => $this->translator->translate("pass_txt"),	
			)
		));  
		
		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))) ),"filters"=>array()),$inputFilter);

		/*$this->add(array(
            'name' => 'client_rpassword',
            'type' => 'password',
            'attributes' => array(
			 	'id' => 'client_rpassword',
                'class' => 'form-control required',
				'maxlength'=>30
            ),
            'options' => array(
				'label' => 'Confirm Password',	               
            )
        ));  

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);*/
		
		$this->add(array(
			'name' => 'user_location',
			'type' => 'text',
			'attributes' => array(
				'id' => 'user_location',
				'class' => 'form-control',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("user_location",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'user_country',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'user_country',
				'class' => 'form-control',
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("user_country",array(),$inputFilter);
		
		$user_list = array('0'=>"Male",'1'=>"Female",'2'=>"Other");
		$this->add(array(
			'name' => 'user_gender',
			'type' => 'select',
			'options' => array(
				'value_options' => $user_list,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control',
				'id' => 'user_gender',
			),
		));
		$this->addInputFilter("user_gender", array("required" => true, "validators" => array("NotEmpty"), "filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_birthday',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_birthday',
				'class' => 'form-control required',
				'maxlength'=>TEXT_CONST_LENGTH,
				'autocomplete' => 'newDate'
			),
			'options' => array(	      
			)
		));
		$this->addInputFilter("client_birthday",array("required" => true, "validators" => array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'chkbox',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'chkbox',
				'class' => 'form-control required',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("chkbox",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

	}

	/* Member registation */
	public function member_register()
	{	
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);

		$this->add(array(
			'name' => 'client_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_name',
				'class' => 'form-control required lettersonly',
				'placeholder' => $this->translator->translate("Full Name"),
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_name",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_email',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_email',
				'class' => 'form-control email required',	//checkemail
				'placeholder' => $this->translator->translate("Your Email Address"),
				'maxlength'=>TEXT_CONST_LENGTH	  
			),
			'options' => array(
				//'label' => $this->translator->translate("email_txt"),	      
			)
		));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);


		$this->add(array(
			'name' => 'client_company_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_company_name',
				'class' => 'form-control required',
				'placeholder' => $this->translator->translate("Company Name"),
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_company_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_company_address',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_company_address',
				'class' => 'form-control required',
				'onchange' => 'GetLatLong(this.value)',
				'placeholder' => $this->translator->translate("Company Address"),
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_company_address",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->addInputFilter("client_company_address",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_address_lat',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'client_address_lat',
			),
			 'options' => array(
				//  'label' => "First Name",
			)
		));

		$this->addInputFilter("client_address_lat",array("required"=>false,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_address_long',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'client_address_long',
			),
			 'options' => array(
				//  'label' => "First Name",
			)
		));

		$this->addInputFilter("client_address_long",array("required"=>false,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_phone',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_phone',
				'class' => 'form-control required',
				'placeholder' => $this->translator->translate("Telephone Number"),
				'minlength'=>10,
				'maxlength'=>20
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_phone",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(10,16)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_website_url',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_website_url',
				'class' => 'form-control required url',
				'placeholder' => $this->translator->translate("Website"),
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_website_url",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_password',
			'type' => 'password',
			'attributes' => array(
				'id' => 'client_password',
				'class' => 'form-control required passcheck',
				'placeholder' => $this->translator->translate("Password"),
				'maxlength'=>30	
			),
			'options' => array(
				//'label' => $this->translator->translate("pass_txt"),	
			)
		));  
		
		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))) ),"filters"=>array()),$inputFilter);

		$this->add(array(
            'name' => 'client_rpassword',
            'type' => 'password',
            'attributes' => array(
			 	'id' => 'client_rpassword',
                'class' => 'form-control required',
				'placeholder'=>'Confirm Password',
				'maxlength'=>30				              
            ),
            'options' => array(
				'label' => 'Confirm Password',	               
            )
        ));  

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

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
	
	/* User / Member Forgot Password Form */
	public function forgotpassword()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);

		$this->add(array(
            'name' => 'client_email',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_email',
                'class' => 'form-control email required',	//checkemailexist
				'placeholder' => $this->translator->translate("email_txt"),
				'maxlength'=>TEXT_CONST_LENGTH	
            ),
            'options' => array(
				//'label' => $this->translator->translate("email_txt"),	      
            )
        ));
		
		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
	}

	

	
	/* Forgot Password -> Reset Password */
	public function resetpassword()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->postcsrf($inputFilter);

		$this->add(array(
            'name' => 'client_password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_password',
                'class' => 'form-control required passcheck',
				'placeholder' => "New Password",	
				'maxlength'=>30
            ),
            'options' => array(
				 //'label' => $this->translator->translate("new_pass_txt"),	                 
            )
        ));
		
		$this->add(array(
            'name' => 'client_rpassword',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_rpassword',
                'class' => 'form-control required',
				'placeholder' => "Confirm New Password",
				'maxlength'=>30	   
            ),
            'options' => array(
				//'label' => $this->translator->translate("confirm_new_pass_txt"),	      
            )
        ));
		
		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array()),$inputFilter);

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

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
			"Digits"=>array(
				'name'=>"Digits",
			),
			"Between"=> array(
				'name' => 'Between',
				 'options' => [
					'min' => 1,
                    'max' => 9999999999,
				  ],
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
					$newValidArr[]=$validators[$allValid];
				}
			}
		}
		
		$newFilterArr = array();
		if(isset($validation['filters'])){
			foreach($validation['filters'] as $allValid){
				$newFilterArr[] = array('name'=>$allValid);
			}
		}
		
		if(isset($validation['required']) && $validation['required'] == 1)
		{
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