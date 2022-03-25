<?php
/* * * * * * * * * * * * * * * * * * * * * * * * 
* Member profile form
* * * * * * * * * * * * * * * * * * * * * * * */

namespace AuthAcl\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class MemberprofileForm extends Form
{
	/* Constructor of the form */
    // public function __construct($name = null)
    public function __construct($translator)
    { 
        parent::__construct('user_profile');
        $this->setAttribute('method', 'post');		
		$this->setAttribute('id', 'user_profile_form');
		$this->setAttribute('class', 'profile_form');
		$this->translator = $translator;
	}

	/* Update profile form */
	public function profile()
	{
		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		$this->add(array(
			'name' => 'client_image',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
				'label' => "Profile Image",
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default',  
				"id" => "client_image",  
				"accept"=>"image/*"
			 ),
		));	

		$this->add(array(
			'name' => 'client_images_six',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
				'label' => "Profile Image",
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'form-control',  
				"id" => "client_images_six",  
				"accept"=>"image/jpeg, image/png"
			 ),
		));

		$this->add(array(
            'name' => 'client_images_name',
            'type' => 'hidden',
            'attributes' => array(
                'id' => 'client_images_name',
				'style'=>'width:100%;'
            ),
            'options' => array(
                /*'label' => 'Your Name',*/
            )
        ));	

		$this->add(array(
            'name' => 'client_name',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_name',
                'class' => 'form-control required lettersonly',
                'placeholder' => $this->translator->translate("Full Name"),
				'required' => 'required',			
				'maxlength'=>TEXT_CONST_LENGTH
            ),
            'options' => array(
                'label' => 'Your Name',				
            )
        ));	

		$this->addInputFilter("client_name",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
            'name' => 'client_email',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_email',
                'class' => 'form-control email required checkemail_exclude',
                'placeholder' => $this->translator->translate("Email"),
				'required' => 'required',
				'maxlength'=>TEXT_CONST_LENGTH	
			),
            'options' => array(
                'label' => 'Email',				
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
				'maxlength'=>16
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
            'name' => 'client_email_password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_email_password',
                'class' => 'form-control passcheck',
				'placeholder' => "Password",
				'maxlength'=>30
            ),
            'options' => array(
				//'label' => $this->translator->translate("pass_new_txt"),
            )
        ));  
		
		$this->addInputFilter("client_email_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array('HtmlEntities')),$inputFilter);


		$this->add(array(
            'name' => 'client_default_image_name',
            'type' => 'hidden',
            'attributes' => array(
                'id' => 'client_default_image_name',
                'class' => 'form-control',
                'placeholder' => $this->translator->translate("Default image name"),
				'maxlength'=>TEXT_CONST_LENGTH
            ),
            'options' => array(
                /*'label' => 'Your Name',	*/			
            )
        ));	



		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'bttnsubmit',	
            'attributes'=> array(
				'type'=>'submit',
				'class' => 'btn submit-btns',
				'id' => 'startedbtn',		
            ),
            'options'=> array(
                'label'=>'<span>Submit</span>',
				'label_options' => array(
                	'disable_html_escape' => true,
           	 )
				
            ),
        ));

		$this->postcsrf($inputFilter,"post_csrf");
	}

	/* Change password form */
	public function changepassword()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);

		$this->add(array(
            'name' => 'client_old_password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_old_password',
                'class' => 'form-control required passcheck',	//check_old_password
				'placeholder' => "Old Password",
				'maxlength'=>30
            ),
            'options' => array(
				//'label' => $this->translator->translate("old_pass_new_txt"),
            )
        )); 

		$this->addInputFilter("client_old_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array('HtmlEntities')),$inputFilter); 
	
		$this->add(array(
            'name' => 'client_password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_password',
                'class' => 'form-control required check_new_password passcheck',
				'placeholder' => "New Password",
				'maxlength'=>30
            ),
            'options' => array(
				//'label' => $this->translator->translate("pass_new_txt"),
            )
        ));  
		
		$this->addInputFilter("client_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array('HtmlEntities')),$inputFilter);


		$this->add(array(
            'name' => 'client_rpassword',
            'type' => 'password',
            'attributes' => array(
			 	'id' => 'client_rpassword',
                'class' => 'form-control required',
				'placeholder'=>'Retype New Password',
				'maxlength'=>30               
            ),
            'options' => array(
				'label' => 'Retype New Password',	               
            )
        ));  

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

		$this->postcsrf($inputFilter,"password_csrf");

		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'btnchpasswordsubmit',	
            'attributes'=> array(
				'type'=>'button',
				'class' => 'btn submit-btns',
				'id' => 'btnchpasswordsubmit',		
            ),
            'options'=> array(
                'label'=>'<span>Submit</span>',
				'label_options' => array(
                	'disable_html_escape' => true,
           	 	)
            ),
        ));

	}

	/* profile image form */
	public function profileimage()
	{
		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		$this->add(array(
			'name' => 'client_image',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
				'label' => "Profile Image",
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default',  
				"id" => "client_image",  
				"accept"=>"image/*"
			 ),
		));	 

		$this->postcsrf($inputFilter,"post_csrf");
	}

	/* Stripe connect form */
	public function stripeconnect()
	{
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
		
        $this->add(array(
			'name' => 'client_ssnnumber',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_ssnnumber ',
				'class' => 'form-control required digits',
				'required'	=> 'required',
				'maxlength'=>'9',
				'placeholder' => 'SSN Number eg. 000000000',
			),
			'options'=>array(
				'label'=>'SSN Number',
			),
		));  
		
		$this->addInputFilter("client_ssnnumber",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(0,9)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_dob',
			'type' => 'text',
			'attributes' => array(
				'id'		=> 'client_paydob',
				'class'		=> 'form-control required',
				'required'	=> 'required',
				'readonly'	=> true,
				'placeholder'=> 'DD/MM/YYYY'
			),
			'options'	=> array(
				'label'		=> 'Date Of Birth(DD-MM-YYYY)',
			),
		));  
		
		$this->addInputFilter("client_dob",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
            'name' => 'client_accnumber',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_accnumber',
                'class' => 'form-control required digits',
				'maxlength'=>'12',
				'placeholder' => 'Account Number eg. 000123456789',
				/*"onkeyup"=>"checkAccNumVal(this)",
				"onpaste"=>"checkAccNumVal(this)",*/
            ),
			'options'=>array(
				'label'=>'Account Number',
			),
        ));  
		
		$this->addInputFilter("client_accnumber",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,12)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);		
		
		$this->add(array(
			'name' => 'client_routenumber',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_routenumber',
				'class' => 'form-control required digits',
				'required' => 'required',
				'maxlength'=>9,
				'minlength'=>0,
				'placeholder' => 'Routing Number eg. 110000000',
			),
			'options'=>array(
				'label'=>'Routing Number',
			),
		));  
		
		$this->addInputFilter("client_routenumber",array("required"=>true,"validators"=>array(array("StringLength"=>array("length"=>array(0,9)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->postcsrf($inputFilter,"payment_csrf");
		
		
	}

	/* Post CSRF function */
	public function postcsrf($inputFilter,$formField)
	{
		$this->add([
				'type' => Element\Csrf::class,
				'name' => $formField,
				'options' => [
				'csrf_options' => [
				   'timeout' => CSRF_OPTIONS_TIMEOUT,
				],
			],
		 ]);

		 $this->addInputFilter($formField,array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter);
	}

	/* Input filter function */
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

					} else {

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

		if(isset($validation['required']) && $validation['required']==1)
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