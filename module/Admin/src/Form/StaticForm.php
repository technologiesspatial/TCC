<?php

namespace Admin\Form;

use Zend\Form\Form;

use Zend\Form\Element;

use Zend\InputFilter\InputFilter;



class StaticForm extends Form

{

    public function __construct($translator)

    {

        parent::__construct('admin');



		$this->setAttribute('class','profile_form form-horizontal form-material');

		$this->setAttribute('enctype','multipart/form-data');

		$this->setAttribute('autocomplete','off');

		$this->translator=$translator;

		

    }

	public function addfaqheading(){

		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'fqheadname',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Category Name',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" =>'Category Name', 

				"rows"=>"3",  

				'maxlength'=>TEXT_CONST_LENGTH

			 ),

		));	

		$this->addInputFilter("fqheadname",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);

		$this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	

	public function slider($id=false)

	{

		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		

		/*$this->add(array(

			'name' => 'slider_title',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Title',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => 'Title',

				"autocomplete" =>"off",  

				'maxlength'=>TEXT_CONST_LENGTH  

			 ),

		));*/

		

		/*if(!empty($id)) {

		  $this->add(array(

			  'name' => 'slider_image',

			  'type' => 'file',

			  'options' =>array(

				  'label' =>$this->translator->translate("upload_image_txt")." (For better resolution please upload image of size 1920 * 1280 px)",

			  ),

			  'attributes' =>array(  

				  'id'=>"slider_image",  

				  "placeholder" => $this->translator->translate("upload_image_txt"), 

			   ),

		  ));	

		} else {*/

			$this->add(array(

			  'name' => 'slider_image',

			  'type' => 'file',

			  "required"=>true,

			  'options' =>array(

				  'label' =>$this->translator->translate("upload_image_txt")." <span style='font-size:11px;'>Supported Files Type(jpg,jpeg,png)</span>",

			  ),

			  'attributes' =>array(        // Array of attributes

				  'class'  => 'blockelement required',  

				  'id'=>"slider_image",  

				  "placeholder" => $this->translator->translate("upload_image_txt"), 

			   ),

		  ));	

		/*}*/

		$this->addInputFilter("slider_image",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);

		$this->addcsrf();

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	
    public function faqs($faq_heading_options = NULL)

	{

		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$faq_heading = array('Select')+$faq_heading_options;

		/*$this->add(array(

				'name' => 'postfq_fh_id',

				'type' => 'select',

				"required"=>true,

				'options' =>array(

					'label' => 'Select Category',

					'value_options' => $faq_heading,

				),

				'attributes' =>array(        // Array of attributes

					'class'  => 'form-control',    

					"id" => 'postfq_fh_id',

					

				 ),

			));

		
*/
		

		$this->add(array(

			'name' => 'postfq_question_en',

			'type' => 'textarea',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("ques_txt"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => $this->translator->translate("ques_txt"), 

				"rows"=>"3",  

			 ),

		));	

		$this->addInputFilter("postfq_question_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);

		$this->add(array(

			'name' => 'postfq_answer_en',

			'type' => 'textarea',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("ans_name_txt"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" =>$this->translator->translate("ans_name_txt"),

				"rows"=>"6",  

			 ),

		));

		$this->addInputFilter("postfq_answer_en",array("required"=>true,"validators"=>array("NotEmpty")),$inputFilter);

		$this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}
	
	public function coupon() {
		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'merchantcoupon_title',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Coupon Title"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Coupon Title"), 
				"rows"=>"3",  
				"maxlength" => "60"
			 ),
		));	
		$this->addInputFilter("merchantcoupon_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		
		$this->add(array(
			'name' => 'merchantcoupon_code',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Coupon Code"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Coupon Code"), 
				"rows"=>"3",  
				"maxlength" => "30"
			 ),
		));	
		$this->addInputFilter("merchantcoupon_code",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		
		$this->add(array(
			'name' => 'merchantcoupon_discount',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Coupon Discount (In %)"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Coupon Discount"), 
				"rows"=>"3",  
				"maxlength" => "6"
			 ),
		));	
		$this->addInputFilter("merchantcoupon_discount",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}
	
	public function photogallery($edit_id = false) {
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		if(!empty($edit_id)) {
			$piccls = '';
		} else {
			$piccls = 'required';
		}
		$this->add(array(
			'name' => 'gallery_image',
			'type' => 'file',			
			'options' =>array(
				'label' => 'Upload Image',
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'default blockelement '.$piccls,  
				"id" => 'gallery_image',
				"accept" => 'image/jpeg',

			 ),
		));
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}
	
	public function howitworks($sectionId) {
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		$sectionId = myurl_decode($sectionId);
		$this->add(array(
			'name' => 'content_heading',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Heading"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Heading"), 
				"rows"=>"3",  
				"maxlength" => "200"
			 ),
		));	
		$this->addInputFilter("content_heading",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		if($sectionId == '1') {
			$this->add(array(
				'name' => 'content_shortdesc',
				'type' => 'text',
				"required"=>true,
				'options' =>array(
					'label' =>$this->translator->translate("Short Description"),
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'form-control required',    
					"placeholder" => $this->translator->translate("Short Description"), 
					"rows"=>"3",  
					"maxlength" => "655"
				 ),
			));	
			$this->addInputFilter("content_shortdesc",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		}
		
		$this->add(array(
			'name' => 'content_description',
			'type' => 'textarea',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Description"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Description"),
				"autocomplete" =>"off",  
				"rows"=>"6",  
				"id" => "content_description",
			 ),
		));	
	  
		$this->addInputFilter("content_description",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);
		if($sectionId != '4') {
		$this->add(array(
			'name' => 'content_image',
			'type' => 'file',			
			'options' =>array(
				'label' => 'Upload Image',
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'default blockelement '.$piccls,  
				"id" => 'content_image',

			 ),
		));	
		}
		
		if($sectionId == '2') {
			$this->add(array(
				'name' => 'content_fburl',
				'type' => 'text',
				'options' =>array(
					'label' =>$this->translator->translate("Facebook URL"),
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'form-control',    
					"placeholder" => $this->translator->translate("Enter Facebook URL"), 
					"rows"=>"3",  
					"maxlength" => "400"
				 ),
			));	
			$this->addInputFilter("content_fburl",array("filters"=>array('StripTags')),$inputFilter);
			
			$this->add(array(
				'name' => 'content_instaurl',
				'type' => 'text',
				'options' =>array(
					'label' =>$this->translator->translate("Instagram URL"),
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'form-control',    
					"placeholder" => $this->translator->translate("Enter Instagram URL"), 
					"rows"=>"3",  
					"maxlength" => "400"
				 ),
			));	
			$this->addInputFilter("content_instaurl",array("filters"=>array('StripTags')),$inputFilter);
			
			$this->add(array(
				'name' => 'content_networkurl',
				'type' => 'text',
				'options' =>array(
					'label' =>$this->translator->translate("Network URL"),
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'form-control',    
					"placeholder" => $this->translator->translate("Enter Network URL"), 
					"rows"=>"3",  
					"maxlength" => "400"
				 ),
			));	
			$this->addInputFilter("content_networkurl",array("filters"=>array('StripTags')),$inputFilter);
		}
		
		if($sectionId == '4') {
			$this->add(array(
				'name' => 'content_video',
				'type' => 'text',
				'options' =>array(
					'label' =>$this->translator->translate("YouTube URL"),
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'form-control',    
					"placeholder" => $this->translator->translate("Enter YouTube URL"), 
					"rows"=>"3",  
					"maxlength" => "200"
				 ),
			));	
			$this->addInputFilter("content_video",array("validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		}
		
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}
	
	public function blog($categories=false,$id=false) {
		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);
		$blog_options = $categories;
		if(!empty($blog_options)) {
			$blog_categories = array(''=>'Select')+$blog_options;
		} else {
			$blog_categories = array(''=>'Select');
		}
		$this->add(array(
			'name' => 'blog_catid',
			'type' => 'select',
			"required"=>true,
			'options' =>array(
				'label' => 'Blog Category',
				'value_options' => $blog_categories,
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"id" => 'postfq_fh_id',				
			 ),
		));
		
		$this->add(array(

			'name' => 'blog_title',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Blog Title"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Blog Title"), 
				"rows"=>"3",  
				"maxlength" => "200"
			 ),
		));	
		$this->addInputFilter("blog_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		
		$this->add(array(
			'name' => 'blog_text',
			'type' => 'textarea',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Blog Description"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Blog Description"),
				"autocomplete" =>"off",  
				"rows"=>"20",  
				"id" => "blog_text",
				"maxlength" => "2000"
			 ),
		));		  
		$this->addInputFilter("blog_text",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);
		
		if(!empty($id)) {
			$piccls = '';
		} else {
			$piccls = 'required';
		}
		
		$this->add(array(
			'name' => 'blog_image',
			'type' => 'file',			
			'options' =>array(
				'label' => 'Upload Blog Image',
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'default blockelement '.$piccls,  
				"id" => 'blog_image',
				"accept" => 'image/jpeg',

			 ),
		));	
		
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}
	
	public function blogcategory() {
		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'blog_category_title',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("blog_category_title"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("blog_category_title"), 
				"rows"=>"3",  
			 ),
		));	
		$this->addInputFilter("blog_category_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		$this->addcsrf();	 
		$this->submitbtn($this->translator->translate("save_txt"));
	}
	
	/**/
	
	 public function category()

	{
		
		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'category_feild',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("category_title"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => $this->translator->translate("category_title"), 

				"rows"=>"3",  

			 ),

		));	

		$this->addInputFilter("category_feild",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
		
		$this->add(array(
			'name' => 'category_description',
			'type' => 'textarea',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Category Description"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Category Description"),
				"autocomplete" =>"off",  
				"rows"=>"5",  
				"id" => "category_description",
				"maxlength" => "800"
			 ),
		));	
	  
		$this->addInputFilter("category_description",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);

		$this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}
	
	public function subcategory($categories=false)
	{
		
		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);
		
		$this->add(array(
			'name' => 'subcategory_categoryid',
			'type' => 'select',
			"required"=>true,
			'options' =>array(
				'label' => 'Category',
				'value_options' => $categories,
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"id" => 'subcategory_categoryid',				
			 ),
		));

		$this->add(array(

			'name' => 'subcategory_title',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("Sub Category Title"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => $this->translator->translate("Sub Category Title"), 

				"rows"=>"3",  

			 ),

		));
		$this->addInputFilter("subcategory_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);

		$this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}
	
	
	/**/
	
	public function pages($page_id,$contentdata)
	{ 
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'page_title_en',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("page_title_txt"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("page_title_txt"),
				"autocomplete" =>"off", 
				'maxlength'=>TEXT_CONST_LENGTH   
			 ),
		));

		$this->addInputFilter("page_title_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'page_meta_title',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Title"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Title"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_title",
			 ),
		));
	
		$this->addInputFilter("page_meta_title",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'page_meta_keyword',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Keyword"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Keyword"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_keyword",
			 ),
		));
			
		$this->addInputFilter("page_meta_keyword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
			
		$this->add(array(
			'name' => 'page_meta_desc',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Description"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Description"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_desc",
			 ),
		));
	
		
		$this->addInputFilter("page_meta_desc",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		
		if($page_id!=20 &&  $page_id!=17){
	   
		$this->add(array(
			'name' => 'page_content_en',
			'type' => 'textarea',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("page_content_txt"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required ckeditor',    
				"placeholder" => $this->translator->translate("page_content_txt"),
				"autocomplete" =>"off",  
				"rows"=>"6",  
				"id" => "page_content_en",
			 ),
		));	
	  
		$this->addInputFilter("page_content_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);
		}
		
		foreach($contentdata as $value){
			$accept='';
				if($value['section_type']=='image'){
					$accept='image/*';
				}
				else if($value['section_type']=='video'){
					$accept='video/*';
				}
			
			if($value['section_type']=='file'|| $value['section_type']=='image'||$value['section_type']=='video'){
					$this->add( array(
					'name' =>$value['page_content_section_key'],
					//'enctype'=>'multipart/form-data',
					'type' => 'file',
					'options' => array(
						'label' =>$value['section_title'],

					),
					'attributes' => array( // Array of attributes
						'class' => 'default blockelement',
						"id" => $value['page_content_section_key'],
						
						"accept" => $accept,
	
					),
				) );
			}
			else if($value['section_type']=='textarea'){
				$this->add(array(
					'name' => $value['page_content_section_key'],
					'type' => 'textarea',
					"required"=>true,
					'options' =>array(
						'label' =>$value['section_title'],
					),
					'attributes' =>array(        // Array of attributes
						'class'  => 'form-control required $ckeditor',    //ckeditor
						"placeholder" =>$value['section_title'],
						"autocomplete" =>"off",  
						"rows"=>"6",  
						"id" =>  $value['page_content_section_key'],
					 ),
				));	

				$this->addInputFilter($value['page_content_section_key'],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);
			}
			else	 {
				$this->add( array(
				'name' => $value['page_content_section_key'],
				'type' => 'text',
				"required" => true,
				'options' => array(
				'label'=>$value['section_title'],
					
				),
				'attributes' => array( // Array of attributes
					'class' => 'form-control required',
					"placeholder" => $this->translator->translate( "Page Content" ),
					"autocomplete" => "off",
					"maxlength" => "500",
					"id" => $value['page_content_section_key'],
				),
			) );
					
			$this->addInputFilter( $value['page_content_section_key'], array( "required" => true, "validators" => array("NotEmpty"), "filters" => array( 'StringTrim' ) ), $inputFilter );
			
			
		}
			}
			

		if($page_id=='13'){
			// home page banner
			$this->add(array(
				'name' => 'page_image',
				'type' => 'file',			
				'options' =>array(
					'label' => 'Upload Banner Image of Home Page',
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'default blockelement',  
					"id" => 'page_image',
					"accept" => 'image/jpeg',

				 ),
			));	
		}

	    $this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}


	public function pagesOld($page_id)
	{
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'page_title_en',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("page_title_txt"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("page_title_txt"),
				"autocomplete" =>"off", 
				'maxlength'=>TEXT_CONST_LENGTH   
			 ),
		));

		$this->addInputFilter("page_title_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
			'name' => 'page_meta_title',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Title"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Title"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_title",
			 ),
		));
	
		$this->addInputFilter("page_meta_title",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
		
		$this->add(array(
			'name' => 'page_meta_keyword',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Keyword"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Keyword"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_keyword",
			 ),
		));
			
		$this->addInputFilter("page_meta_keyword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);
			
		$this->add(array(
			'name' => 'page_meta_desc',
			'type' => 'text',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("Meta Description"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required',    
				"placeholder" => $this->translator->translate("Meta Description"),
				"autocomplete" =>"off",   
				"maxlength" =>"255", 
				"id" => "page_meta_desc",
			 ),
		));
	
		
		$this->addInputFilter("page_meta_desc",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(0,255)))),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);


		$this->add(array(
			'name' => 'page_content_en',
			'type' => 'textarea',
			"required"=>true,
			'options' =>array(
				'label' =>$this->translator->translate("page_content_txt"),
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required ckeditor',    
				"placeholder" => $this->translator->translate("page_content_txt"),
				"autocomplete" =>"off",  
				"rows"=>"6",  
			 ),
		));	

		$this->addInputFilter("page_content_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);

		if($page_id=='13'){
			// home page banner
			$this->add(array(
				'name' => 'page_image',
				'type' => 'file',			
				'options' =>array(
					'label' => 'Upload Banner Image of Home Page',
				),
				'attributes' =>array(        // Array of attributes
					'class'  => 'default blockelement',  
					"id" => 'page_image',
					"accept" => 'image/jpeg',

				 ),
			));	
		}

	    $this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	public function emailtemplates()

	{

		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'emailtemp_title',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("email_title_txt"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => $this->translator->translate("email_title_txt"),

				"autocomplete" =>"off",  

			 	'maxlength'=>TEXT_CONST_LENGTH

			 ),

			 

		));	

		$this->addInputFilter("emailtemp_title",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(

			'name' => 'emailtemp_subject_en',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("email_subject_txt"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => $this->translator->translate("email_subject_txt"),

				"autocomplete" =>"off", 

				'maxlength'=>TEXT_CONST_LENGTH 

			 ),

		));	

		$this->addInputFilter("emailtemp_subject_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		

		$this->add(array(

			'name' => 'emailtemp_content_en',

			'type' => 'textarea',

			"required"=>true,

			'options' =>array(

				'label' =>$this->translator->translate("email_content_txt"),

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control  required ckeditor',    

				"placeholder" => $this->translator->translate("email_content_txt"),

				"rows" =>"6",  

				'id' => 'emailtemp_content_en',

			 ),

		));	

		

		$this->addInputFilter("emailtemp_content_en",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);

		$this->addcsrf();	 

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	
	public function login()

	{

		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		$this->add(array(

            'name' => 'client_email',

            'type' => 'text',

			"required"=>true,

            'options' =>array(

                'label' => "Email address",

            ),

			'attributes' =>array(        // Array of attributes

				'id' => 'client_email',

				'class'  => 'form-control empty required email',    

				 'maxlength'=>TEXT_CONST_LENGTH

			 ),

        ));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(

            'name' => 'client_password',

            'type' => 'password',

			"required"=>true,

            'options' =>array(

                'label' => "Password",

            ),

			'attributes' =>array(

				'id' => 'client_password',        // Array of attributes

				'class'  => 'form-control empty required',    

				'maxlength'=>30

			 ),

        ));

		/*$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);*/

		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,30)))),"filters"=>array()),$inputFilter);

		$this->addcsrf();	 

	}

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

	public function forgotpassword()

	{

		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'client_email',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' => "Email Address",

			),

			

			'attributes' =>array(        // Array of attributes

				'id' => 'client_email',

				'class'  => 'form-control required email empty',    //checkemail

				  'maxlength'=>TEXT_CONST_LENGTH

			 ),

		));

		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array("NotEmpty","EmailAddress"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);	

		$this->addcsrf();					

	}

	

	public function resetPassword(){

		

		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

		$this->add(array(

            'name' => 'client_password',

            'type' => 'password',

			"required"=>true,

            'options' =>array(

                'label' => "Enter Password",

            ),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control login-frm-input required passcheck',  

				'id'  => 'client_password',    

				"autocomplete" =>"off", 

				'maxlength'=>30

			 ),

        ));

		

		$this->add(array(

            'name' => 'client_rpassword',

            'type' => 'password',

			"required"=>true,

            'options' =>array(

                'label' => "Re Type Password",

            ),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control login-frm-input required',  

				'id'  => 'client_rpassword',    

					'maxlength'=>30,

				"autocomplete" =>"off", 

			 ),

        ));

		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,30)))),"filters"=>array()),$inputFilter);

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array("NotEmpty",array("StringLength"=>array("length"=>array(8,30))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

		$this->addcsrf();	

	}

	

	public function profile()
    {
      	$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);
		$this->add(array(
            'name' => 'client_name',
            'type' => 'text',
			"required"=>true,
            'options' =>array(
                'label' => "Name",
            ),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required lettersonly',    
				"placeholder" => "Name",
				'id' => 'client_name',
				'maxlength'=>TEXT_CONST_LENGTH     
			 ),
        ));

		$this->addInputFilter("client_name",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

		$this->add(array(
            'name' => 'client_address',
            'type' => 'text',
            'attributes' => array(
                'id' => 'client_address',
                'class' => 'form-control required',
				"required"=>"required",	
				'placeholder' => 'Address',	      
            	'maxlength'=>TEXT_CONST_LENGTH
			),
            'options' => array(
                  'label' => 'Address',					        
            )

        ));

		$this->addInputFilter("client_address",array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StripNewlines','StringTrim')),$inputFilter);

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

		$this->addcsrf();	 
    }

	public function changeEmail()
	{
		$inputFilter = new InputFilter();        
		$this->setInputFilter($inputFilter);

		$this->add(array(
            'name' => 'client_email',
            'type' => 'text',
			"required"=>true,
            'options' =>array(
                'label' => "Email",
            ),
			'attributes' =>array(        // Array of attributes
				'class'  => 'form-control required email',	//adminchangeemail
				//"placeholder" => "Email Address",     
				'maxlength'=>TEXT_CONST_LENGTH,
				'id' => 'client_email'
			 ),
        ));
		
		$this->addInputFilter("client_email",array("required"=>true,"validators"=>array(),"filters"=>array('StripTags','StripNewlines','StringTrim','HtmlEntities')),$inputFilter); 

		$this->add(array(
            'name' => 'client_email_password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'client_email_password',
                'class' => 'form-control required',	//check_old_password
            	'maxlength'=>TEXT_CONST_LENGTH
			),
            'options' => array(
				'label' => $this->translator->translate("Confirm Password"),				
            )
        )); 
	
		
		$this->addInputFilter("client_email_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,16)))),"filters"=>array('HtmlEntities')),$inputFilter); 
			

		
	}

	public function changepassword(){

		

		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		

		$this->add(array(

            'name' => 'client_old_password',

            'type' => 'password',

            'attributes' => array(

                'id' => 'client_old_password',

                'class' => 'form-control required check_old_password',

				'maxlength'=>30

            ),

            'options' => array(

				'label' => $this->translator->translate("Old Password"),

            )

        )); 

		

		$this->addInputFilter("client_old_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array()),$inputFilter); 

		

		$this->add(array(

            'name' => 'client_password',

            'type' => 'password',

            'attributes' => array(

                'id' => 'client_password',

                'class' => 'form-control required passcheck ',

					'maxlength'=>30

            ),

            'options' => array(

				'label' => $this->translator->translate("New Password"),

            )

        ));  

		

		$this->addInputFilter("client_password",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array()),$inputFilter);

		

		$this->add(array(

            'name' => 'client_rpassword',

            'type' => 'password',

            'attributes' => array(

                'id' => 'client_rpassword',

                'class' => 'form-control required',

					'maxlength'=>30

            ),

            'options' => array(

				'label' => $this->translator->translate("Confirm Password"),

				                

            )

        ));   

		

		$this->addInputFilter("client_rpassword",array("required"=>false,"validators"=>array(array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

		

	}

	

	public function password()

	{

        parent::__construct('admin');

		$inputFilter = new InputFilter();        

        $this->setInputFilter($inputFilter);

       

		$this->add(array(

            'name' => 'client_old_password',

            'type' => 'password',

			"ignore"=>true,

			"required"=>true,

            'options' =>array(

                'label' => "Enter Old Password",

            ),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => "Enter Old Password", 

					'maxlength'=>30    

			 ),

        ));

		$this->addInputFilter("client_old_password",array("required"=>true,"validators"=>array(array("StringLength"=>array(

		"length"=>array(8,255)))),"filters"=>array()),$inputFilter); 

		$this->add(array(

            'name' => 'client_password',

            'type' => 'password',

			"required"=>true,

            'options' =>array(

                'label' => "Enter Password",

            ),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required passcheck',  

				'id'  => 'client_password',    

				"placeholder" => "Enter Password", 

				"autocomplete" =>"off", 

					'maxlength'=>30

			 ),

        ));

		$this->addInputFilter("client_password",array("required"=>true,"validators"=>array(array("StringLength"=>array("length"=>array(8,255)))),"filters"=>array()),$inputFilter);

		$this->add(array(

            'name' => 'client_rpassword',

            'type' => 'password',

			"ignore"=>true,

			"required"=>true,

            'options' =>array(

                'label' => "Re Type Password",

            ),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => "Re Type Password", 

				"autocomplete" =>"off", 

					'maxlength'=>30 

				  

			 ),

        ));

		$this->addInputFilter("client_rpassword",array("required"=>true,"validators"=>array(array("StringLength"=>array("length"=>array(8,255))),array("Identical"=>array("match"=>"client_password"))),"filters"=>array()),$inputFilter);

		$this->addcsrf();	 

    }

	public function wickedshop($wickedData){
		$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
		$this->add(array(
			'name' => 'wicked_banner',
			'type' => 'file',			
			'options' =>array(
				'label' => 'Wicked Banner',
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'default blockelement',  
				"id" => 'wicked_banner',

			 ),
		));
		$this->addcsrf();	
		$this->formsubmitbtn($this->translator->translate("save_txt"));
	}

	public function siteconfig($configData,$currData)

    { 
    	$inputFilter = new InputFilter();        
        $this->setInputFilter($inputFilter);
		
		global $paymentModeArr;
		foreach($configData as $key=>$values){	
			
			$addClass="required";
			if($values['config_group']=='SITE_SOCIAL'){
				if($values['config_key']!='google_key'){}
				elseif($values['config_key']!='facebook_appid'){}
				elseif($values['config_key']!='facebook_secretid'){}
				elseif($values['config_key']!='twitter_key'){}
				elseif($values['config_key']!='insta_client_id'){}
				elseif($values['config_key']!='insta_client_secret'){}
				elseif($values['config_key']!='twitter_secret'){}
				else { $addClass="url"; }
				if($values['config_key']=="facebook_link" || $values['config_key']=="twitter_link" || $values['config_key']=="linkedin_link" || $values['config_key']=="instagram_link" || $values['config_key']=="google_link" || $values['config_key']=="youtube_link" ){
				$addClass.=" url";
				}

			} else if($values['config_key']=='site_automatic_release' || $values['config_key']=='site_feedback_edit'){
				$addClass="digits";

			} else if($values['config_key']=='site_commission' || $values['config_key']=='site_per_credit'){
				$addClass="number";

			}
			if($values['config_key']=='coven_artist_info')
			{
				
				$this->add(array(

						'name' => $values['config_key'],

						'type' => 'textarea',

						"required"=>true,

						'options' =>array(

							'label' => $values['config_title_'.$_COOKIE['currentLang']],

						),

						'attributes' =>array(        // Array of attributes

							'class'  => 'form-control required',    

							"placeholder" => $values['config_title_'.$_COOKIE['currentLang']], 

							"rows"=>"6",  
							"maxlength" => '2000'

						 ),

					));	

					$this->addInputFilter($values['config_key'],array("required"=>true,"validators"=>array("NotEmpty")),$inputFilter);
				

			}
			else if($values['config_key']=='site_currency')
			{
				$currData=array(''=>'Select Currency')+$currData;
				$this->add(array(
					'name' => $values['config_key'],
					'type' => 'select',
					"required"=>true,
					'options' =>array(
						'label' => $values['config_title_'.$_COOKIE['currentLang']],
						'value_options' => $currData,
					),
					'attributes' =>array(        // Array of attributes
						'class'  => 'form-control',    
						"id" => $values['config_key'],
					 ),
				));

				$this->addInputFilter($values['config_key'],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);

			} else if($values['config_key'] == 'autoapproval_date') {
				$this->add(array(
						'name' => $values['config_key'],
						'type' => 'text',
						"required"=>true,
						'options' =>array(
							'label' => '',
						),
						'attributes' =>array(        // Array of attributes
							'class'  => 'form-control d-none '.$addClass,    
							"placeholder" => $values['config_title_'.$_COOKIE['currentLang']], 
							"autocomplete" =>"off",    "data-title"=>$values['config_group'],
							"id" => $values['config_key'],
							 'maxlength'=>$values['config_maxlength'],	
						 ),
					));
			} else if($values['config_key']!='site_logo' && $values['config_key']!='site_logo_mobile' && $values['config_key']!='site_favicon' && $values['config_key']!='site_email_logo' && $values['config_key']!='site_address')
			{
				if($values['config_key'] == 'digital_text') {
					$this->add(array(

						'name' => $values['config_key'],

						'type' => 'textarea',

						"required"=>true,

						'options' =>array(

							'label' =>$this->translator->translate("Digital Product Description"),

						),

						'attributes' =>array(        // Array of attributes

							'class'  => 'form-control required',    

							"placeholder" => $this->translator->translate("Digital Product Description"), 

							"rows"=>"3",  
							"maxlength" => '400'

						 ),

					));	

					$this->addInputFilter($values['config_key'],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags')),$inputFilter);
				}
				else if(in_array($values['config_key'], array('site_latitude','site_longitude')))
				{
					$this->add(array(
						'name' => $values['config_key'],
						'type' => 'text',
						"required"=>true,
						'options' =>array(
							'label' => $values['config_title_'.$_COOKIE['currentLang']],
						),
						'attributes' =>array(        // Array of attributes
							'class'  => 'form-control '.$addClass,    
							"placeholder" => $values['config_title_'.$_COOKIE['currentLang']], 
							"autocomplete" =>"off",    "data-title"=>$values['config_group'],
							"id" => $values['config_key'],
							'maxlength'=>$values['config_maxlength'],
							/*'readonly' => 'readonly',
							'disabled' => 'disabled',*/
						 ),
					));

				} else {

					$this->add(array(
						'name' => $values['config_key'],
						'type' => 'text',
						"required"=>true,
						'options' =>array(
							'label' => $values['config_title_'.$_COOKIE['currentLang']],
						),
						'attributes' =>array(        // Array of attributes
							'class'  => 'form-control '.$addClass,    
							"placeholder" => $values['config_title_'.$_COOKIE['currentLang']], 
							"autocomplete" =>"off",    "data-title"=>$values['config_group'],
							"id" => $values['config_key'],
							 'maxlength'=>$values['config_maxlength']
						 ),
					));

				}
				

				$this->addInputFilter($values['config_key'],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);

			} elseif($values['config_key']=='site_address'){//
	
				$this->add(array(
					'name' => $values['config_key'],
					'type' => 'text',	//textarea
					"required"=>true,
					'options' =>array(
						'label' => $values['config_title_'.$_COOKIE['currentLang']],
					),
					'attributes' =>array(        // Array of attributes
						'class'  => 'form-control '.$addClass,    
						"placeholder" => $values['config_title_'.$_COOKIE['currentLang']], 
						"autocomplete" =>"off",    
						"data-title"=>$values['config_group'],
						"id" => $values['config_key'],
						'maxlength'=>$values['config_maxlength'],
						'onchange'=>'GetLatLong(this.value)',
					 ),
				));

				$this->addInputFilter($values['config_key'],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);	

			} else {
				$this->add(array(
					'name' => $values['config_key'],
					'type' => 'file',			
					'options' =>array(
						'label' => $values['config_title_'.$_COOKIE['currentLang']],
					),
					'attributes' =>array(        // Array of attributes
						'class'  => 'default blockelement',  
						"id" => $values['config_key'],
						
					 ),
				));	
			}
		}

		$this->addcsrf();	
		// $this->submitbtn($this->translator->translate("save_txt"));
		$this->formsubmitbtn($this->translator->translate("save_txt"));

    }

	public function phtogallery()
	{

		$inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'sch_fileupload_image',

			'type' => 'hidden',

			"required"=>true,

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

			  	'id'=>'sch_fileupload_image'

			 ),

		));	

		

	}

	

	public function team()

	{ $inputFilter = new InputFilter();        

		$this->setInputFilter($inputFilter);

		$this->add(array(

			'name' => 'member_name',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Member Name',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => 'Member Name', 

				"autocomplete" =>"off", 

				"id"=>"member_name",   

			 	'maxlength'=>TEXT_CONST_LENGTH

			 ),

		));

		

		$this->addInputFilter('member_name',array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);	

		

		$this->add(array(

			'name' => 'member_desc',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'About Member',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control required',    

				"placeholder" => 'About Member', 

				"autocomplete" =>"off", 

				"id"=>"member_desc",   

				'maxlength'=>TEXT_CONST_LENGTH

			 ),

		));

		

		$this->addInputFilter('member_desc',array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);	

		$this->add(array(

			'name' => 'member_image',

			'type' => 'file',

			"required"=>true,	

			'options' =>array(

				'label' => "Member Picture",

			),

			"accept"=>"image/*",

			'attributes' =>array(        // Array of attributes

				'class'  => 'blockelement required',  

				"id" => "member_image",

				  

			 ),

		));	

		$this->addInputFilter('member_image',array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);

		

		$this->add(array(

			'name' => 'member_fblink',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Facebook Link',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control url',     

				"placeholder" => 'Facebook Link', 

				"id"=>"member_fblink",  

				'maxlength'=>TEXT_CONST_LENGTH 

			 ),

		));

		$this->add(array(

			'name' => 'member_twlink',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Twitter Link',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control url',    

				"placeholder" => 'Twitter Link', 

				"id"=>"member_twlink", 

				'maxlength'=>TEXT_CONST_LENGTH  

			 ),

		));

		

		$this->add(array(

			'name' => 'member_glink',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'Google Link',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control url',    

				"placeholder" => 'Google Link', 

				"id"=>"member_glink",  

				'maxlength'=>TEXT_CONST_LENGTH 

			 ),

		));

		

		$this->add(array(

			'name' => 'member_lnlink',

			'type' => 'text',

			"required"=>true,

			'options' =>array(

				'label' =>'LinkedIn Link',

			),

			'attributes' =>array(        // Array of attributes

				'class'  => 'form-control url',    

				"placeholder" => 'LinkedIn Link', 

				"id"=>"member_lnlink",

				'maxlength'=>TEXT_CONST_LENGTH   

			 ),

		));

		

		$this->addcsrf();	

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	public function submitbtn($label="")
	{
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

	public function formsubmitbtn($label="")
	{
		$this->add(array(
			'name' => 'bttnsubmit',
			'type' => 'button',
			'ignore' => true,
			'options' =>array(
				'label' =>$label,
			),
			'attributes' =>array(        // Array of attributes
				'class'  => 'btn btn-raised btn-primary waves-effect waves-classic',    
				'id'	=> 'btnformsubmit',
			 ),
		));
	}

	

	

	

	public function homepage($content)

	{

		 $inputFilter = new InputFilter();        

         $this->setInputFilter($inputFilter);

		 foreach($content as $ckey=>$cValue){

			$element_class="blockelement required";

			if($cValue["home_elementtype"]=="text" || $cValue["home_elementtype"]=="textarea"){

				$element_class="form-control required";

			}

				$this->add(array(

					'name' => $cValue["home_key"],

					'type' => $cValue["home_elementtype"],

					"required"=>true,

					'options' =>array(

						'label' =>$cValue["home_title"], 

					),

					'attributes' =>array(        // Array of attributes

						'class'  => $element_class,  

						'id'=>$cValue["home_key"],

						 

					 ),

				 ));	

				 if( $cValue["home_elementtype"]=="textarea"){

				  /* if element is textarea : set its rows*/

				  $this->get($cValue["home_key"])->setAttribute("rows",6);

				 }

				  if( $cValue["home_elementtype"]!="file"){

					  

					  $this->get($cValue["home_key"])->setAttribute("maxlength",$cValue["home_elementlength"]);

					 

				  }

			if($cValue["home_elementtype"]!="file"){	

			$this->addInputFilter($cValue["home_key"],array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array()),$inputFilter);

			}

			//

		

		}

		

	

			

		/*$this->add(array(

				'name' => 'section_three_block',

				'type' => 'textarea',

				"required"=>true,

				'options' =>array(

					'label' =>'Page Content ',

				),

				'attributes' =>array(        // Array of attributes

					'class'  => 'form-control required ckeditor',    

					"rows"=>"6",  

					'id'=>'section_three_block'

				 ),

			));

			*/

		

		$this->addcsrf();

		$this->submitbtn($this->translator->translate("save_txt"));

	}

	

	

	private function getValidate($inputFilter,$ValidateElement){

			

			$arrayValidate=array(

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

	public function addInputFilter($element,$validation,$inputFilter){

			

		

		

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

					}

					else if($mainKey=='match'){ // MATCH

						$newValidArr[]=$validators[$keyVal];

						$newValidArr[count($newValidArr)-1]['options']['token']=$allValid[$keyVal][$mainKey];

					}

					else{

					}

					

				}

				else{

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

		}

		else{

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