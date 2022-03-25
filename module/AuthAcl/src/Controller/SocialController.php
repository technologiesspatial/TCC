<?php

namespace AuthAcl\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;

use AuthAcl\Form;

use AuthAcl\Form\UserForm;

use Zend\Session\Container;





use Zend\Db\Sql\Expression;

use Zend\Db\Sql\Select;



use Zend\Paginator\Adapter\DbSelect;

use Zend\Paginator\Paginator;



class SocialController extends AbstractActionController

{



	private $AbstractModel,$Adapter,$UserModel,$EmailModel,$authService;

    public function __construct($AbstractModel,$Adapter,$UserModel,$frontSession,$authService,$config_data)

    {	

        $this->SuperModel = $AbstractModel;

		$this->Adapter = $Adapter;

		$this->UserModel = $UserModel;		

		$this->frontSession = $frontSession;	

		$this->loggedUser = $authService->getIdentity();

		$this->authService = $authService;	

		$this->SITE_CONFIG=$config_data;

    }

	/* Insta login */

	/*Google Login*/

	public function googleloginAction(){
		unset($_SESSION['google_info']);

		if(isset($_GET['url'])){
			$_SESSION['callback'] = $_GET['url']; 
 		}
		require_once ROOT_PATH."/vendor/google/src/Google_Client.php";

		require_once ROOT_PATH."/vendor/google/src/contrib/Google_Oauth2Service.php";

		

		

		

		########## Google Sttings.. Client ID, Client Secret #############

		$configData=$this->SuperModel->Super_Get("yurt90w_config","config_key='google_key' || config_key='google_secret' || config_key='google_clientid'","fetchAll"); 

	

		$google_redirect_url 	= APPLICATION_URL.'/google';

		$google_client_id 		=$configData[2]['config_value'];// '894720517749-07a3ui6gs4t1o8hb3pdlm85a2s5dk2eq.apps.googleusercontent.com' ;

		$google_client_secret 	=$configData[1]['config_value'];//'HA_m75ZODeL1I6NsWafH6oaC';

		$google_developer_key 	= $configData[0]['config_value'];//'AIzaSyDGHhAkm9QgchF3T16MxMai6pd91k_UN8U' ;
		if(isset($_REQUEST['error']) && $_REQUEST['error']=='access_denied'){

			$this->frontSession['errorMsg'] ="Google Process Cancelled.Please try again.";

			return $this->redirect()->toUrl(APPLICATION_URL.'/login');

				

		}
		$gClient = new \Google_Client();

		$gClient->setApplicationName('Login');
		$gClient->setClientId($google_client_id);
		$gClient->setClientSecret($google_client_secret);
		$gClient->setRedirectUri($google_redirect_url);
		$gClient->setDeveloperKey($google_developer_key);
		$google_oauthV2 = new \Google_Oauth2Service($gClient);
		 $AuthCode='';
		 
		if(!empty($_SERVER['REQUEST_URI']) and preg_match('/code=/',$_SERVER['REQUEST_URI'])){
			$sp1 = array_filter(explode('/google?code=',urldecode($_SERVER['REQUEST_URI']))); 
		    if(is_array($sp1)){
				$sp12 = array_filter(explode('&scope=',$sp1[1]));
				if(is_array($sp12) and !empty($sp12)){
					$AuthCode = $sp12[0];
				}
			}
		}
		//If user wish to log out, we just unset Session variable
		if(!empty($AuthCode)){ 

			  $gClient->authenticate($AuthCode);

			  $this->frontSession['gtoken'] = $gClient->getAccessToken();

			// return $this->redirect()->toUrl($google_redirect_url);

			 header("Location: $google_redirect_url");	exit();

			 

        } 

		if (isset($this->frontSession['gtoken'])){ 

		

			$gClient->setAccessToken($this->frontSession['gtoken']);
		}
		if ($gClient->getAccessToken()){ 

		//echo 'in 4';die;

			//Get user details if user is logged in

			$user 				= $google_oauthV2->userinfo->get();

			$user_id 			= $user['id'];

			$user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);

			$email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);

			$this->frontSession['gtoken'] 	= $gClient->getAccessToken();

		}

		else {

			//echo 'in 5';die;

			

		    $authUrl = $gClient->createAuthUrl();

			//echo $authUrl;die;

		}           
		if(isset($authUrl)){

         //  return $this->redirect()->toUrl($authUrl);

			 header("Location: $authUrl");	exit();

			

        } 

		else{ 

		

			//$user['user_type']=$objSession->userType;

			//unset($objSession->userType);

			/* for Already Exists */

				$isExists=$this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$user['email']."'","fetch");

		/*	$isExists = $this->SuperModel->Super_Get("users","user_oauth_provider='googleplus' and user_oauth_id='".$user['id']."'","fetch") ;*/

 			

			

			if(empty($isExists)){//&& $user['user_type']!='LC'

			

			$user['social_by']='google';

				$is_insert = $this->save_g_data($user);

				

				

				  $isExists = (array)$is_insert ;

			}

			elseif($isExists[T_USERS_CONST.'_status']=='0'){

				$this->frontSession['errorMsg'] = "Your account is blocked by administrator";

				return $this->redirect()->toUrl(APPLICATION_URL.'/login');

			}elseif($isExists[T_USERS_CONST.'_type']=='admin'){

				$this->frontSession['errorMsg'] = 'Invalid Login';

				return $this->redirect()->toUrl(APPLICATION_URL.'/login');

			

			}

			

			 

			$this->authService->getStorage()->write((object)$isExists);
			$_SESSION["logstat"] = '2';
			
			if(!empty($_SESSION["cartArr"])) {
				foreach($_SESSION["cartArr"] as $cart_key => $cart_val) {
				    $product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_clientid','warray'=>array('PID'=>$cart_val["product_cart_prodid"])));
					if($isExists[T_USERS_CONST.'_id'] != $product_details["product_clientid"]) {
						$cart_data["product_cart_prodid"] = $cart_val["product_cart_prodid"];
						$cart_data["product_cart_price"] = $cart_val["product_cart_price"];
						$cart_data["product_cart_qty"] = $cart_val["product_cart_qty"];
						$cart_data["product_cart_coupon"] = $cart_val["product_cart_coupon"];
						$cart_data["product_cart_date"] = $cart_val["product_cart_date"];
						$cart_data["product_cart_discount"] = $cart_val["product_cart_discount"];
						$cart_data["product_cart_color"] = $cart_val["product_cart_color"];
						$cart_data["product_cart_size"] = $cart_val["product_cart_size"];
						if(!empty($cart_val["product_cart_delivery"])) {
						    $cart_data["product_cart_delivery"] = $cart_val["product_cart_delivery"];
						}
						$cart_data["product_cart_clientid"] = $isExists[T_USERS_CONST.'_id'];
						$this->SuperModel->Super_Insert(T_PRODCART,$cart_data);
					}
				}
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			
			if(isset($_SESSION['callback'])){
				$yurl = $_SESSION['callback'];
				unset($_SESSION['callback']);
				return $this->redirect()->tourl(APPLICATION_URL . $yurl);
			}
			return $this->redirect()->toUrl(APPLICATION_URL.'/profile'); 

			}

        exit();

	}	

	/* Twitter Login  */

	public function twitterloginAction(){

 		$oauth_verifier  = '';

		if(isset($_GET['oauth_verifier'])){

			$oauth_verifier  = $_GET['oauth_verifier'];

		}

		if(isset($_GET['denied']) && $_GET['denied']!=''){

			$this->frontSession['errorMsg'] = "Twitter Process Cancelled.Please try again";

			return $this->redirect()->tourl(APPLICATION_URL.'/login');

			

		}

		 if ($this->authService->hasIdentity()) {

			return $this->redirect()->tourl(APPLICATION_URL.'/profile');

        }	

		

		require_once ROOT_PATH."/vendor/Twitter/twitteroauth.php";

		require_once ROOT_PATH."/vendor/Twitter/twconfig.php";

		

		$configData=$this->SuperModel->Super_Get("yurt90w_config","config_key='twitter_key' || config_key='twitter_secret'","fetchAll"); 
		$TwitterOAuth = new\TwitterOAuth($configData[0]['config_value'],$configData[1]['config_value'],"https://www.demoserver.live/thecollectivecoven/twitter"); 

		

		//$oauth_verifier  = $_GET['oauth_verifier'];

		

 		if(empty($oauth_verifier)||!isset($_SESSION['socail_login'])){

			

   			$request_token = $TwitterOAuth->getRequestToken(APPLICATION_URL."/twitter"); 
			

			$_SESSION['oauth_token'] = $request_token['oauth_token'];

			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

  			if ($TwitterOAuth->http_code == 200){

				$_SESSION['socail_login'] = true ;

				$url = $TwitterOAuth->getAuthorizeURL($request_token['oauth_token']);
   				header("Location: $url");	
			
			}else{

				$this->frontSession['errorMsg'] = " Twitter Configuration failed . ";

				return $this->redirect()->toUrl(APPLICATION_URL);

				

			}

  			

		}else{ /* Get Verifier */

			

			

			$TwitterOAuth = new\TwitterOAuth($configData[0]['config_value'],$configData[1]['config_value'],$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']); 

   			try{

					$access_token = $TwitterOAuth->getAccessToken($oauth_verifier);	

						

			}catch(\Exception $ex){ 

				$this->frontSession['errorMsg'] = "Error Occurred.";

				return $this->redirect()->toUrl(APPLICATION_URL);

			}

			

			$user_info = $TwitterOAuth->get('account/verify_credentials');


 			if (isset($user_info->errors)){

				$this->frontSession['errorMsg'] =  "Error Occurred.";

				return $this->redirect()->toUrl(APPLICATION_URL);

			} 

 			 

 			 $isExists = $this->SuperModel->Super_Get("yurt90w_clients","yurt90w_client_oauth_provider='twitter' and yurt90w_client_oauth_id='".$user_info->id."'","fetch") ;

		

			  //$isExists = $this->modelUser->get(array("where"=>"user_email='".$user_info->email."'")) ;

			if(!empty($isExists)){

				if($isExists['yurt90w_client_status']=='0'){

						//$this->authService->clearIdentity();

						$this->frontSession['errorMsg'] = 'Your account is blocked by administrator';

						return $this->redirect()->toUrl(APPLICATION_URL.'/login');

					}

				$this->authService->getStorage()->write((object)$isExists);

 				return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

 			}

			

			$this->frontSession['twitter_login'] = true ;

			$this->frontSession['twitter_data'] = $user_info ;

 			

 			/* Get User Email Addresss  */

			return $this->redirect()->toUrl(APPLICATION_URL.'/twitterhandler');

 		}

		 exit();

  	}

	private function save_twitter_data($received = false){

 		

  		$generated_password = genratePassword($received['name']);

		

		$image_name = $this->receive_profile_image($received , "twitter");

 		 $nameList=explode(" ",$received['name']);$first_name=$last_name='';

  		if(isset($nameList[0]) && !empty($nameList[0])){

			$first_name=$nameList[0];	

		}

		if(isset($nameList[1]) && !empty($nameList[1])){

			$last_name=$nameList[1];	

		}

		

		$data_to_save = array(

			'client_oauth_id' =>$received['id_str'],

			'client_oauth_provider'=>'twitter',

			'client_signup_type'=>'social',

 			'client_image'=>$image_name,

			//'user_reset_status'=>'1',

			'client_status'=>'1',

			'client_password'=>md5($generated_password),

			'client_email'=>$received['email'],

			'client_email_verified'=>'1',

			'client_name'=>$first_name.' '.$last_name,

			'client_created'=>date("Y-m-d H:i:s")

		);

		$data_to_save = GetFormElementsName($data_to_save,T_CLIENT_VAR);

		$is_insert = $this->SuperModel->Super_Insert(T_CLIENTS,$data_to_save);
				unset($this->frontSession['twitter_login']);

				unset($this->frontSession['twitter_data']);

		if(is_object($is_insert) and $is_insert->success){

			$up_insert = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$is_insert->inserted_id."'","fetch");	

			$this->authService->getStorage()->write((object)$up_insert);

			return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

		}

				

		$this->frontSession['errorMsg'] = "Enable to login.Please try again";

 		return $this->redirect()->toUrl(APPLICATION_URL);

	  

  		

 

 	}

	

	/* Get Email Address From the User  */

	public function twitterhandlerAction(){

		    

		 if ($this->authService->hasIdentity()) {

			return $this->redirect()->tourl(APPLICATION_URL.'/profile');

        }	

		if(!isset($this->frontSession['twitter_login'])){

			$this->frontSession['errorMsg'] = "Please Login "; 

			return $this->redirect()->toUrl(APPLICATION_URL.'');

		}
		

		
		
		$form = new UserForm($this->layout()->translator);
		$form->twitter_email();

		

		

				

					

		if($this->getRequest()->isPost()){

			$posted_values = $this->params()->fromPost(); 

			

			$form->setData($posted_values);

			 if($form->isValid()) {

				

				$form_data = $form->getData(); 

				unset($form_data['bttnsubmit']);

				

				//$allUser = 	$this->SuperModel->Super_Get(T_CLIENTS,'1',"fetch");
				$isExists = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email='".$form_data['client_email']."'","fetch");	
				if(!empty($isExists)){

					   

					if($isExists[T_CLIENT_VAR.'client_oauth_provider']!='twitter'){
						
						$this->frontSession['errorMsg'] = "Email address already exists";

 						return $this->redirect()->toUrl(APPLICATION_URL);

					}

					else{

						$this->authService->getStorage()->write((object)$isExists);

						return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

						}

				}

				else{    

					$received_data  = (array) $this->frontSession['twitter_data'] ;

					$received_data['email'] = $form_data['client_email'] ;

  					$is_insert = $this->save_twitter_data($received_data);
					

					}

 			}



		}  

		

		// Pass form variable to view

		

		return new ViewModel(array(

            'form' => $form,

            'twitter_user'=>$this->frontSession['twitter_data'],

			'pageHeading'=>"Twitter Sign Up",

			

        ));
		
 	}

	

	/* Instagram Login  */

	public function instagramloginAction(){ 

		 

	   global $serviceSession;

	   

	   $configData=$this->SuperModel->Super_Get("yurt90w_config","config_key='insta_client_id' || config_key='insta_client_secret'","fetchAll");

	  

	   $instagram_client_id = $configData[0]['config_value'];

	   $instagram_client_secret = $configData[1]['config_value']; 

	   $instagram_redirect_uri = SITE_HTTP_URL.'/instagramlogin';

	  

	   $login_url = 'https://api.instagram.com/oauth/authorize/?client_id=' . $instagram_client_id . '&redirect_uri=' . urlencode($instagram_redirect_uri) . '&response_type=code&scope=basic';

		  if(isset($_GET['code'])) {

			

			

			require_once ROOT_PATH."/vendor/instagram/instagram.class.php";

			try {

				

				 $instagram_ob = new\Instagram(array(

							'apiKey'      => $instagram_client_id,//'Client_ID',

							'apiSecret'   => $instagram_client_secret,//'Client_Secret',

							'apiCallback' => $instagram_redirect_uri // must point to success.php

				 ));

			

			 

			// Get the access token 

			 $getuserInfo = $instagram_ob->getOAuthToken($_GET['code'],false); 

			 

			 $isExists = $this->SuperModel->Super_Get("yurt90w_clients","yurt90w_client_oauth_provider='insta' and yurt90w_client_oauth_id='".$getuserInfo->user->id."'","fetch") ;

						

			 //Insert entry in database to create a new user

			 if(!empty($isExists)){

				if($isExists['yurt90w_client_status']=='0'){

						$this->frontSession['errorMsg'] = 'Your account is blocked by administrator';

						return $this->redirect()->toUrl(APPLICATION_URL.'/login');

					}

				$this->authService->getStorage()->write((object)$isExists);

 				return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

 			}

			

			$this->frontSession['instagram_login'] = true ;

			$this->frontSession['instagram_data'] = $getuserInfo ;

 			

 			/* Get User Email Addresss  */

			return $this->redirect()->toUrl(APPLICATION_URL.'/instagramhandler');

			

		}

		catch(Exception $e) {

			echo $e->getMessage();

			exit;

		}

	  }else{

		 	 

			 return $this->redirect()->toUrl($login_url);

		 }

	

		}

	

	private function save_instagram_data($received = false){

	

			

			$generated_password = genratePassword($received['name']);

			

			$image_name = $this->receive_profile_image($received['user']->profile_picture, "instagram");

			

			 $nameList=explode(" ",$received['user']->full_name);$first_name=$last_name='';

			if(isset($nameList[0]) && !empty($nameList[0])){

				$first_name=$nameList[0];	

			}

			if(isset($nameList[1]) && !empty($nameList[1])){

				$last_name=$nameList[1];	

			}

			

			$data_to_save = array(

				'yurt90w_client_oauth_id' =>$received['user']->id,

				'yurt90w_client_oauth_provider'=>'insta',

				'yurt90w_client_signup_type'=>'social',

				'yurt90w_client_image'=>$image_name,

				//'client_type'=>"",

				'yurt90w_client_status'=>'1',

				'yurt90w_client_email_verified'=>'1',

				'yurt90w_client_password'=>md5($generated_password),

				'yurt90w_client_email'=>$received['email'],

				'yurt90w_client_first_name'=>$first_name,

				'yurt90w_client_last_name'=>$last_name,

				'yurt90w_client_created'=>date("Y-m-d H:i:s")

			);

			

			 $is_insert = $this->UserModel->add($data_to_save);

				unset($this->frontSession['instagram_login']);

				unset($this->frontSession['instagram_data']);

			if(is_object($is_insert) and $is_insert->success){

				$up_insert = $this->SuperModel->Super_Get("yurt90w_clients","yurt90w_client_id='".$is_insert->inserted_id."'","fetch");		

				$this->authService->getStorage()->write((object)$up_insert);

				$this->frontSession['successMsg']='Logged In Successfully.';

				return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

			}

			

					

			$this->frontSession['errorMsg'] = "Enable to login.Please try again";

			return $this->redirect()->toUrl(APPLICATION_URL);

		  

			

	 

		}

	

	public function instagramhandlerAction(){

		

		 if ($this->authService->hasIdentity()) {

			return $this->redirect()->tourl(APPLICATION_URL.'/profile');

        }	

		if(!isset($this->frontSession['instagram_login'])){

			$this->frontSession['errorMsg'] = "Please Login "; 

			return $this->redirect()->toUrl(APPLICATION_URL);

		}

		

		$form = new UserForm();

		$form->instagram_username();

		

		if($this->getRequest()->isPost()){

			$posted_values = $this->params()->fromPost(); 

		

			$form->setData($posted_values);

			 if($form->isValid()) {

				

				$form_data = $form->getData(); 

				unset($form_data['bttnsubmit']);

				

				$received_data  = (array) $this->frontSession['instagram_data'] ;

				

				

				$received_data['email'] = $form_data['client_email'] ;

				

			

				

  				$is_insert = $this->save_instagram_data($received_data);

				

				return $this->redirect()->tourl(APPLICATION_URL.'/profile');

 			}

			else{

					echo "sadas";exit;

			}

		}

		

		// Pass form variable to view

		

		return new ViewModel(array(

            'form' => $form,

            'instagram_data'=>$this->frontSession['instagram_data'],

			'pageHeading'=>"Instagram Sign Up",

			

        ));

		

 		

 		

 	}

	

	

 	/*Social media sign up*/

	public function fbloginAction(){  	

	

	

		 if ($this->authService->hasIdentity()) {

			return $this->redirect()->tourl(APPLICATION_URL.'/profile');

        }	

		if(isset($_REQUEST['error']) && $_REQUEST['error']!=''){ 

			$this->frontSession['errorMsg'] ="Facebook Process Cancelled.Please try again";

			return $this->redirect()->toUrl(APPLICATION_URL);

		}

 		require_once ROOT_PATH."/vendor/Facebook/src/Facebook/autoload.php";

		require_once ROOT_PATH."/vendor/Facebook/src/Facebook/Facebook.php";

		//require_once ROOT_PATH.'/vendor/Facebook/facebook.php';		

		

		$pagetype = $this->params()->fromRoute('pagetype');	

	

		$configData=$this->SuperModel->Super_Get("yurt90w_config","config_key='facebook_appid' || config_key='facebook_secretid'","fetchAll");	

		

		$callback_url=SITE_HTTP_URL.'/facebook';

		

		$your_facebook_page=SITE_HTTP_URL.'/facebook';

		

		$fb = new\Facebook\Facebook(array(

		  'app_id' => $configData[0]['config_value'],

		  'app_secret' => $configData[1]['config_value'],

		  'default_graph_version' => 'v2.8',

		));

		

		

		

		$helper = $fb->getRedirectLoginHelper();

		if(isset($_REQUEST['code']) && $_REQUEST['code']!=''){

			$helper = $fb->getRedirectLoginHelper();

			

			try {

			 

			  $accessToken = $helper->getAccessToken($callback_url);

			  

			  try {

				  

				  // Get the \Facebook\GraphNodes\GraphUser object for the current user.

				  // If you provided a 'default_access_token', the '{access-token}' is optional.

				  $response = $fb->get('/me?fields=first_name,last_name,email,id', $accessToken);

				  $_SESSION['fb_accessToken'] = $accessToken;

				 

				  $user = $response->getGraphUser();

				  //prd($user);

				 

				} 

				catch(\Facebook\Exceptions\FacebookResponseException $e) {

				  // When Graph returns an error

				  $this->frontSession['errorMsg']= 'Graph returned an error: ' . $e->getMessage();

				  return $this->redirect()->toUrl(APPLICATION_URL);

				} 

				catch(\Facebook\Exceptions\FacebookSDKException $e) {

				  // When validation fails or other local issues

				  $this->frontSession['errorMsg'] = 'Facebook SDK returned an error: ' . $e->getMessage();

				   return $this->redirect()->toUrl(APPLICATION_URL);

			}

				catch(\Exception $ex){

					 $this->frontSession['errorMsg'] ="Error Occurred";

					 return $this->redirect()->toUrl(APPLICATION_URL);

				}

			} catch(Facebook\Exceptions\FacebookResponseException $e) {

			  // When Graph returns an error

			 $this->frontSession['errorMsg'] = 'Graph returned an error: ' . $e->getMessage();

			  return $this->redirect()->toUrl(APPLICATION_URL);

			} 

			catch(Facebook\Exceptions\FacebookSDKException $e) {

			  // When validation fails or other local issues

			  $this->frontSession['errorMsg'] = 'Facebook SDK returned an error: ' . $e->getMessage();

			  return $this->redirect()->toUrl(APPLICATION_URL);

			}

			catch(\Exception $ex){

					 $this->frontSession['errorMsg'] ="Error Occurred";

					 return $this->redirect()->toUrl(APPLICATION_URL);

				}

		}

		else{

			

			$permissions = array('email'); // Optional permissions

			$loginUrl = $helper->getLoginUrl($callback_url, $permissions);

			header("Location: " . $loginUrl);

			exit();

		}

			

		$user_profile=array('first_name'=>$user['first_name'],'last_name'=>$user['last_name'],'email'=>$user['email'],'id'=>$user['id']);

		

	

		if($user_profile['email']==''){

			$this->frontSession['errorMsg'] = "Unable to Fetch Email Address from your Account.Please check access for the information you provide while authentication.";

			return $this->redirect()->toUrl(APPLICATION_URL.'/signup');

		}

		

		

		

 		if(!$user){

			

			//exit; 

		}else{

			

			

		  	$isExists=$this->SuperModel->Super_Get("yurt90w_clients","yurt90w_client_email='".$user_profile['email']."'","fetch");

			

			if(!empty($user_profile) && empty($isExists)){

			

				$is_insert = $this->save_fb_data($user_profile);

				$is_objins = (object)$is_insert;

				$this->authService->getStorage()->write((object)$is_insert);
				$_SESSION["logstat"] = '2';
                if(!empty($_SESSION["cartArr"])) {
				    foreach($_SESSION["cartArr"] as $cart_key => $cart_val) {
					    $product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_clientid','warray'=>array('PID'=>$cart_val["product_cart_prodid"])));
						if($is_objins->yurt90w_client_id != $product_details["product_clientid"]) {
						    $cart_data["product_cart_prodid"] = $cart_val["product_cart_prodid"];
							$cart_data["product_cart_price"] = $cart_val["product_cart_price"];
							$cart_data["product_cart_qty"] = $cart_val["product_cart_qty"];
							$cart_data["product_cart_coupon"] = $cart_val["product_cart_coupon"];
							$cart_data["product_cart_date"] = $cart_val["product_cart_date"];
							$cart_data["product_cart_discount"] = $cart_val["product_cart_discount"];
							$cart_data["product_cart_color"] = $cart_val["product_cart_color"];
							$cart_data["product_cart_size"] = $cart_val["product_cart_size"];
							if(!empty($cart_val["product_cart_delivery"])) {
							    $cart_data["product_cart_delivery"] = $cart_val["product_cart_delivery"];
							}
							$cart_data["product_cart_clientid"] = $is_objins->yurt90w_client_id;
							$this->SuperModel->Super_Insert(T_PRODCART,$cart_data);
						}
					}
					return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
				}
				//$this->frontSession['successMsg']='Logged In Successfully.';
				
				return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

			}

			

			if($isExists[T_CLIENT_VAR.'client_status']==0){

				$this->frontSession['errorMsg'] = 'Your account is blocked by administrator';

						return $this->redirect()->toUrl(APPLICATION_URL.'/login');

			}



			$this->authService->getStorage()->write((object)$isExists);
			$_SESSION["logstat"] = '2';
			if(!empty($_SESSION["cartArr"])) {
				foreach($_SESSION["cartArr"] as $cart_key => $cart_val) {
				    $product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_clientid','warray'=>array('PID'=>$cart_val["product_cart_prodid"])));
					if($isExists[T_CLIENT_VAR.'client_id'] != $product_details["product_clientid"]) {
						$cart_data["product_cart_prodid"] = $cart_val["product_cart_prodid"];
						$cart_data["product_cart_price"] = $cart_val["product_cart_price"];
						$cart_data["product_cart_qty"] = $cart_val["product_cart_qty"];
						$cart_data["product_cart_coupon"] = $cart_val["product_cart_coupon"];
						$cart_data["product_cart_date"] = $cart_val["product_cart_date"];
						$cart_data["product_cart_discount"] = $cart_val["product_cart_discount"];
						$cart_data["product_cart_color"] = $cart_val["product_cart_color"];
						$cart_data["product_cart_size"] = $cart_val["product_cart_size"];
						if(!empty($cart_val["product_cart_delivery"])) {
						    $cart_data["product_cart_delivery"] = $cart_val["product_cart_delivery"];
						}
						$cart_data["product_cart_clientid"] = $isExists[T_CLIENT_VAR.'client_id'];
						$this->SuperModel->Super_Insert(T_PRODCART,$cart_data);
					}
				}
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			
			return $this->redirect()->toUrl(APPLICATION_URL.'/profile');

		}

 	}

	

	private function save_fb_data($received = false){		

 		

		$image_name = $this->receive_profile_image($received , "facebook");

		$generated_password = generatePassword($received['first_name'].$received['last_name']);

		

 		$data_to_save = array(

			'client_oauth_id' =>$received['id'],

			'client_oauth_provider'=>'facebook',

			'client_signup_type'=>'social',

			'client_type'=>'user',

 			'client_image'=>$image_name,	

			//'user_reset_status'=>'1',

			'client_status'=>'1',

			//'user_name'=>($received['first_name'].$received['last_name']),

			'client_name'=>$received['first_name'].' '.$received['last_name'],

			'client_password'=>md5($generated_password),

			'client_email'=>$received['email'],

			'client_email_verified'=>'1',

			'client_created'=>date('Y-m-d H:i:s')

		);

		$data_to_save = GetFormElementsName($data_to_save,T_CLIENT_VAR);

		foreach (array_keys($data_to_save, '') as $key) {

			unset($data_to_save[$key]);

		}

		

		$inserted = $this->SuperModel->Super_Insert(T_CLIENTS,$data_to_save); 

		

	   	$user_data=$this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$inserted->inserted_id."'","fetch");			

		return  $user_data ;



 	}
	
	
	public function save_g_data($received = false){
		/* if($received['picture'] && $received['picture']!=''){
			$profile_image=$this->receive_profile_image($received,'googleplus');
			$data_to_save[T_CLIENT_VAR.'client_image']=$profile_image;
		}    */
		/* 	prd($received); */
		$generated_password = genratePassword($received['name']);
			
		$data_to_save = array(
			T_CLIENT_VAR.'client_oauth_id' =>$received['id'],
			T_CLIENT_VAR.'client_oauth_provider'=>'google',
			T_CLIENT_VAR.'client_signup_type'=>'social',
			/* 'client_image'=>$image_name,	 */
			T_CLIENT_VAR.'client_status'=>'1',
			T_CLIENT_VAR.'client_name'=>$received['given_name'].' '.$received['family_name'],
			// T_CLIENT_VAR.'client_password'=>md5($generated_password),
			T_CLIENT_VAR.'client_email'=>$received['email'],
			T_CLIENT_VAR.'client_email_verified'=>'1',
			T_CLIENT_VAR.'client_created'=>date('Y-m-d H:i:s')
		);
		$inserted = $this->UserModel->add($data_to_save);
		
		if(is_object($inserted) and $inserted->error){
			$this->frontSession['errorMsg'] = "Some error occurred";
			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}
		$user_data=$this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$inserted->inserted_id."'","fetch");			
		return  (object)$user_data ;
	   
   }	

	

	/* Code to Receive Profile Image  */

 	private function receive_profile_image($received , $provider){

		switch($provider){

			

			case 'facebook':

				$image_url ="https://graph.facebook.com/".$received['id']."/picture?width=400&height=400";
				$profile_image = time().'_'.$received['first_name'].'.png';

				

			 break;	

			 case 'twitter':

				 $image_url = str_replace("_normal","",$received['profile_image_url_https']);

				 $extension = getFileExtension($image_url);

  				 $profile_image=time().'_'.$received['screen_name'].'.'.$extension;

 			break;

			case 'googleplus': 

				$image_url =$received['picture'];

				$profile_image=time().'_'.$received['given_name'].'.png';

			break;		

			case 'instagram': 

				$image_url =$received;

				$profile_image=time().'_'.$received['first-name'].'.png';

			break;

			default : "";

		}

 		

		$content = file_get_contents($image_url);

		file_put_contents(PROFILE_IMAGES_PATH.'/'.$profile_image,$content);

		

		$thumb_config = array("source_path"=>PROFILE_IMAGES_PATH,"name"=> $profile_image);

		$ImageCrop = $this->ImageCrop();   

		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/300","size"=>300)));

		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/60","crop"=>true ,"size"=>60,"ratio"=>false)));

		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/100","crop"=>true ,"size"=>60,"ratio"=>false)));

		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/120","crop"=>true ,"size"=>60,"ratio"=>false)));

		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/160","crop"=>true ,"size"=>160,"ratio"=>false)));
		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/206x137","crop"=>false ,"width"=>206,"height"=>137,"ratio"=>true)));
		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/412x274","crop"=>false ,"width"=>412,"height"=>274,"ratio"=>true)));
		$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>PROFILE_IMAGES_PATH."/thumb","crop"=>true ,"size"=>300,"ratio"=>false)));		

		return $profile_image ;

	}

	

	private function write_auth($data)

	{

		$Front_User_Session = new Container(DEFAULT_AUTH_NAMESPACE);			

		$Front_User_Session['loggedUser']=$data;

	}

	

	

}