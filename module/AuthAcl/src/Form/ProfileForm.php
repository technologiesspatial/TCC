<?php
/* * * * * * * * * * * * * * * * * * * * * * * * 
* User profile form
* * * * * * * * * * * * * * * * * * * * * * * */

namespace AuthAcl\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class ProfileForm extends Form
{
	/* Constructor of the form */
    public function __construct($name = null)
    { 
        parent::__construct('user_profile');
        $this->setAttribute('method', 'post');		
		$this->setAttribute('id', 'user_profile_form');
		$this->setAttribute('class', 'profile_form');
	}
	
	public function shipping($countries) {
		$inputFilter = new InputFilter();
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'shipping_name',
			'type' => 'text',
			'attributes' => array(
				'id' => 'shipping_name',
				'class' => 'form-control required',
				'placeholder' => 'Shipping Name',
				'maxlength'=>'50'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("shipping_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$processing_time = array('1 business day'=>"1 business day",'1-2 business days'=>"1-2 business days",'1-3 business days'=>"1-3 business days",'3-5 business days'=>"3-5 business days",'1-2 weeks'=>"1-2 weeks",'2-3 weeks'=>"2-3 weeks",'3-4 weeks'=>"3-4 weeks",'4-6 weeks'=>"4-6 weeks",'6-8 weeks'=>"6-8 weeks",'Unknown'=>"Unknown");
		$this->add(array(
			'name' => 'shipping_time',
			'type' => 'select',
			'options' => array(
				'value_options' => $processing_time,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control ',
				'id' => 'shipping_time',
			),
		));
		$this->addInputFilter("shipping_time", array("filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		
		
		$this->add(array(
			'name' => 'shipping_rate',
			'type' => 'text',
			'attributes' => array(
				'id' => 'shipping_rate',
				'class' => 'form-control',
				'placeholder' => 'Shipping Rate',
				'maxlength'=>'10'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("shipping_rate",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'shipping_free',
			'type' => Element\Checkbox::class,
			'attributes' => array(
				'id' => 'shipping_free',
				'class' => 'form-control',
			),
			 'options' => array(
				/*'label' => 'I have read and accept terms',*/
			)
		));

		$this->addInputFilter("shipping_free",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'shipping_addrate',
			'type' => 'text',
			'attributes' => array(
				'id' => 'shipping_addrate',
				'class' => 'form-control',
				'placeholder' => 'Additional Item Price',
				'maxlength'=>'10'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("shipping_rate",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'shipping_globalrate',
			'type' => 'text',
			'attributes' => array(
				'id' => 'shipping_globalrate',
				'class' => 'form-control',
				'placeholder' => 'Shipping Global Rate',
				'maxlength'=>'10'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("shipping_globalrate",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'shipping_countries',
			'type' => 'select',
			'options' => array(
				'value_options' => $countries,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control required',
				'id' => 'shipping_countries',
				'multiple' => 'multiple',
			),
		));
		$this->addInputFilter("shipping_countries", array("filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		
		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'startedbtn',	
            'attributes'=> array(
				'type'=>'button',
				'class' => 'btn view-btn send-btn submit-btns',
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
	
	/* Product form */
	public function product($category_list,$subcategory_list=false,$add=false) {
		$inputFilter = new InputFilter();
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'product_title',
			'type' => 'text',
			'attributes' => array(
				'id' => 'product_title',
				'class' => 'form-control required',
				'placeholder' => 'Product Title',
				'maxlength'=>'80'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		if(!empty($add)) {
			$req = 'required';
		} else {
			$req = '';
		}
		$this->add(array(
			'name' => 'product_category',
			'type' => 'select',
			'options' => array(
				'value_options' => $category_list,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control required',
				'id' => 'product_category',
			),
		));
		$this->addInputFilter("product_category", array("required" => true, "validators" => array("NotEmpty"), "filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		$this->add(array(
			'name' => 'product_subcategory',
			'type' => 'select',
			'options' => array(
				'value_options' => $subcategory_list,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control required',
				'id' => 'product_subcategory',
			),
		));
		$this->addInputFilter("product_subcategory", array("required" => true, "validators" => array("NotEmpty"), "filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		$this->add(array(
			'name' => 'product_description',
			'type' => 'textarea',
			'attributes' => array(
				'id' => 'product_description',
				'class' => 'form-control required',
				'placeholder' => 'Description',
				'rows' => '6',
				'maxlength'=>'1500'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_description",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		$this->add(array(
			'name' => 'product_price',
			'type' => 'text',
			'attributes' => array(
				'id' => 'product_price',
				'class' => 'form-control number',
				'placeholder' => 'Price',
				'maxlength'=>'10'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_price",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		/*$this->add(array(
			'name' => 'product_qty',
			'type' => 'text',
			'attributes' => array(
				'id' => 'product_qty',
				'class' => 'form-control required number',
				'placeholder' => 'Available Quantity',
				'maxlength'=>'10'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_qty",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);*/
		$this->add(array(
			'name' => 'product_shipping',
			'type' => 'text',
			'attributes' => array(
				'id' => 'product_shipping',
				'class' => 'form-control required',
				'placeholder' => 'Product Shipping Rate',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_shipping",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		/*$this->add(array(
			'name' => 'product_photos',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'product_photos',
				'class' => 'form-control required',
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_photos",array("required"=>true,"validators"=>array("NotEmpty")),$inputFilter);*/
		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'startedbtn',	
            'attributes'=> array(
				'type'=>'button',
				'class' => 'btn view-btn send-btn submit-btns',
				'id' => 'startedbtn',		
            ),
            'options'=> array(
                'label'=>'<span>Submit</span>',
				'label_options' => array(
                	'disable_html_escape' => true,
           	 )
				
            ),
        ));
		
		$this->add(array(
			'name' => 'product_tags',
			'type' => 'text',
			'attributes' => array(
				'id' => 'product_tags',
				'class' => 'form-control required',
				'placeholder' => 'Product Tags',
				'maxlength'=>'600'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("product_tags",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->postcsrf($inputFilter,"post_csrf");
	}
	
	/* Coupon form */
	public function coupon() {
		$inputFilter = new InputFilter();   
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'coupon_title',
			'type' => 'text',
			'attributes' => array(
				'id' => 'coupon_title',
				'class' => 'form-control required',
				'placeholder' => 'Name of Coupon',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("coupon_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		$this->add(array(
			'name' => 'coupon_discount',
			'type' => 'text',
			'attributes' => array(
				'id' => 'coupon_discount',
				'class' => 'form-control required',
				'placeholder' => 'Discount',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("coupon_discount",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		$this->add(array(
			'name' => 'coupon_code',
			'type' => 'text',
			'attributes' => array(
				'id' => 'coupon_code',
				'class' => 'form-control required code-txtbox',
				'placeholder' => 'Coupon Code',
				'maxlength'=> '50'
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("coupon_code",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		$this->add(array(
			'name' => 'coupon_start_date',
			'type' => 'text',
			'attributes' => array(
				'id' => 'coupon_start_date',
				'class' => 'form-control required datepicker',
				'placeholder' => 'Coupon Start Date',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("coupon_start_date",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		$this->add(array(
			'name' => 'coupon_end_date',
			'type' => 'text',
			'attributes' => array(
				'id' => 'coupon_end_date',
				'class' => 'form-control required datepicker',
				'placeholder' => 'Coupon Valid Upto',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("coupon_end_date",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'startedbtn',	
            'attributes'=> array(
				'type'=>'submit',
				'class' => 'btn view-btn send-btn submit-btns',
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
	
	/* Seller form */
	public function seller($uid = false,$tid = false)
	{
		$inputFilter = new InputFilter();   
		if(!empty($uid)) {
			$req = '';
		} else {
			$req = 'required';
		}
        $this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'seller_storename',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_storename',
				'class' => 'form-control required lettersonly chkstore',
				'placeholder' => 'Store Name',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_storename",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_companyname',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_companyname',
				'class' => 'form-control required lettersonly',
				'placeholder' => 'Company Legal Name',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_companyname",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_contact',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_contact',
				'class' => 'form-control required lettersonly',
				'placeholder' => 'Contact Person',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_contact",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_location',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_location',
				'class' => 'form-control required',
				'placeholder' => 'Location',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_location",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_paypal',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_paypal',
				'class' => 'form-control required email',
				'placeholder' => 'Paypal Email',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_paypal",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);		
		
		
		$this->add(array(
			'name' => 'seller_storetitle',
			'type' => 'text',
			'attributes' => array(
				'id' => 'seller_storetitle',
				'class' => 'form-control required',
				'placeholder' => 'Store Title',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("seller_storetitle",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'store_headline',
			'type' => 'text',
			'attributes' => array(
				'id' => 'store_headline',
				'class' => 'form-control',
				'placeholder' => 'Store Headline',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));
		$this->addInputFilter("store_headline",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'store_description',
			'type' => 'textarea',
			'attributes' => array(
				'id' => 'store_description',
				'class' => 'form-control',
				'placeholder' => 'Description of items you would like to sell on the site and add Instagram/Facebook handle or link',
				'rows' => '6',
				'maxlength'=>'2000',
				'data-gramm' =>"false",
				'spellcheck' => "false"
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("store_description",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'store_policy',
			'type' => 'textarea',
			'attributes' => array(
				'id' => 'store_policy',
				'class' => 'form-control',
				'placeholder' => 'Here you can add return Policies and  other FAQ',
				'rows' => '6',
				'maxlength'=>'1500',
				'data-gramm' =>"false",
				'spellcheck' => "false"
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("store_policy",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		if(!empty($tid)) {
			$this->add(array(
			'name' => 'seller_logo',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default ',  
				"id" => "seller_logo",  
				"accept"=>"image/*"
			 ),
		));
		$this->addInputFilter("seller_logo",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_banner',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default ',  
				"id" => "seller_banner",  
				"accept"=>"image/*"
			 ),
		));
		$this->addInputFilter("seller_banner",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		} else {
		$this->add(array(
			'name' => 'seller_logo',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default required',  
				"id" => "seller_logo",  
				"accept"=>"image/*"
			 ),
		));
		$this->addInputFilter("seller_logo",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'seller_banner',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			"accept"=>"image/*",
			'attributes' =>array(       
				'class'  => 'default required',  
				"id" => "seller_banner",  
				"accept"=>"image/*"
			 ),
		));
		$this->addInputFilter("seller_banner",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);	
		}
		$this->add(array(
			'name' => 'seller_doc1',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			'attributes' =>array(       
				'class'  => 'default '.$req,  
				"id" => "seller_doc1",
			 ),
		));
		
		$this->add(array(
			'name' => 'seller_doc2',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			'attributes' =>array(       
				'class'  => 'default '.$req,  
				"id" => "seller_doc2",
			 ),
		));
		
		$this->add(array(
			'name' => 'seller_doc3',
			'type' => 'file',
			"required"=>true,
			'options' =>array(
			),
			'attributes' =>array(       
				'class'  => 'default',  
				"id" => "seller_doc3",
			 ),
		));
		
		 
	}

	/* Update profile form */
	public function profile($country_list=false)
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
			'name' => 'client_firstname',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_firstname',
				'class' => 'form-control required lettersonly',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_firstname",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'client_lastname',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_lastname',
				'class' => 'form-control required lettersonly',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_lastname",array("required"=>true,"validators"=>array("NotEmpty","Alpha"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_username',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_username',
				'class' => 'form-control required checkuname',
				'maxlength'=>'40'
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_username",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
            'name' => 'client_email',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_email',
                'class' => 'form-control email required checkemail_exclude',
				'required' => 'required',
				'maxlength'=>TEXT_CONST_LENGTH	
			),
            'options' => array(
                'label' => 'Email',				
            )
        ));	

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

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
			'name' => 'client_address',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_address',
				'class' => 'form-control',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_address",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_apartment',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_apartment',
				'class' => 'form-control',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_apartment",array("filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_city',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_city',
				'class' => 'form-control required',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_city",array("required" => true, "validators" => array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_postcode',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_postcode',
				'class' => 'form-control required',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_postcode",array("required" => true, "validators" => array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_state',
			'type' => 'text',
			'attributes' => array(
				'id' => 'client_state',
				'class' => 'form-control required',
				'maxlength'=>TEXT_CONST_LENGTH
			),
			 'options' => array(
				//  'label' => "First Name",	      
			)
		));

		$this->addInputFilter("client_state",array("required" => true, "validators" => array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'client_country',
			'type' => 'select',
			'options' => array(
				'value_options' => $country_list,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control',
				'id' => 'client_country',
			),
		));
		$this->addInputFilter("client_country", array("required" => true, "validators" => array("NotEmpty"), "filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
				
		/*$this->add(array(
			'name' => 'client_country',
			'type' => 'hidden',
			'attributes' => array(
				'id' => 'client_country',
				'class' => 'form-control',
			),
			 'options' => array(	      
			)
		));
		$this->addInputFilter("client_country",array(),$inputFilter);*/
		
		$user_list = array('0'=>"Male",'1'=>"Female",'2'=>"Other");
		$this->add(array(
			'name' => 'client_gender',
			'type' => 'select',
			'options' => array(
				'value_options' => $user_list,
			),
			'attributes' => array(        // Array of attributes
				'class'  => 'custom-select form-control',
				'id' => 'client_gender',
			),
		));
		$this->addInputFilter("client_gender", array("required" => true, "validators" => array("NotEmpty"), "filters" => array('StripTags', 'StripNewlines', 'StringTrim', 'HtmlEntities')),$inputFilter);
		
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

		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'startedbtn',	
            'attributes'=> array(
				'type'=>'button',
				'class' => 'btn view-btn send-btn submit-btns',
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
				'placeholder' => "",
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
				'placeholder' => "",
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
				'placeholder'=>'',
				'maxlength'=>30
            ),
            'options' => array(
				'label' => 'Confirm New Password',	               
            )
        ));  

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

		$this->postcsrf($inputFilter,"password_csrf");

		$this->add(array( //Button envio
            'type'=>'Zend\Form\Element\Button',
            'name'=>'btnchpasswordsubmit',	
            'attributes'=> array(
				'type'=>'button',
				'class' => 'btn view-btn send-btn submit-btns',
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