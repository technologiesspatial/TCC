<?php

namespace Admin\Form;

use Zend\Form\Form;

use Zend\Form\Element;

use Zend\InputFilter\InputFilter;





class MasterForm extends Form

{

    public function __construct($translator)

    {

        parent::__construct('admin');



		$this->setAttribute('class','profile_form form-horizontal form-material');

		$this->setAttribute('autocomplete', 'off');

		$this->translator=$translator;

		

    }

	public function mastermanage($pagefields,$type=false,$superModel=false){

		 $inputFilter = new InputFilter();        

         $this->setInputFilter($inputFilter);

	

		if(!empty($pagefields)){

			

			foreach($pagefields as $pKey=>$pValue){	

				if($pValue=='select'){

					$onChange="";

					$catData=array();

					

					

					$this->add(array(

						'name' => $pKey,

						'type' => $pValue,

						"required"=>true,

						'options' =>array(

							'label' =>$this->translator->translate("table_title_txt"),

							'value_options' =>$catData,

							'disable_inarray_validator' => true,   

						),

						'attributes' =>array(        // Array of attributes

							'class'  => 'form-control required',    

							"placeholder" => $this->translator->translate("table_title_txt"), 

							"autocomplete" =>"off",

							"onchange"=>$onChange, 

							'id' => $pKey,   

						 ),

					));

				}

				else{

					$maxlengthAttr="";

					if($pValue=="text"){$maxlengthAttr=TEXT_CONST_LENGTH;}

					$title = $this->translator->translate("table_title_txt");

					if($pKey=='category_image')

					$title = "Upload Image";

					$elementclass  = 'form-control required';

					if($pKey=='category_name_en'){

						

						$elementclass  = 'form-control required check_new_category';

					}

					$this->add(array(

						'name' => $pKey,

						'type' => $pValue,

						"required"=>true,

						'options' =>array(

							'label' =>$title,

						),

						'attributes' =>array(        // Array of attributes

							'class'  => $elementclass,    

							"placeholder" => $this->translator->translate("table_title_txt"), 

							"autocomplete" =>"off",  

							'id' => $pKey,  

							'maxlength'=>$maxlengthAttr

						 ),

					));

					$this->addInputFilter($pKey,array("required"=>true,"validators"=>array("NotEmpty"),"filters"=>array('StripTags','StringTrim')),$inputFilter);	

				}

				

			}

		

		}

		$this->addcsrf();	

		 

		$this->submitbtn($this->translator->translate("save_txt"));

	}

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

	

	public function addcsrf(){

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