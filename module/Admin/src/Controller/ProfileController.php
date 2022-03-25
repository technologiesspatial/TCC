<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Profile controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller;

use Admin\Model\AdminTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\StaticForm;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;

use Application\Model\AbstractModel;
use Admin\Model\UserModel;
use Zend\Mvc\Plugin\FlashMessenger;

class ProfileController extends AbstractActionController
{

	private $table,$AbstractModel,$UserModel,$Adapter,$EmailModel;

	

	public function __construct(AbstractModel $AbstractModel,$adminMsgsession,$UserModel,$Adapter,$EmailModel)

	{

		$this->UserModel = $UserModel;

		$this->SuperModel = $AbstractModel;

		$this->adminMsgsession = $adminMsgsession;

		$this->Adapter = $Adapter;

		$session = new Container(ADMIN_AUTH_NAMESPACE);

		$this->adminuser = $session['adminData'];

		$this->EmailModel=$EmailModel;

    }

	public function readnotifyAction()

	{

		

		$isUpdated=$this->SuperModel->Super_Insert(T_NOTIFICATION,array('notification_readstatus'=>'1'),"notification_to='1'");

		exit;

	}

   	public function indexAction()
    { 

		$pactivetab = $this->params()->fromRoute('tabtype',1);
		if($pactivetab!="1" && $pactivetab!="2" && $pactivetab!="3" && $pactivetab!="4"){
			$pactivetab="1";
		}	
		$form = new StaticForm($this->layout()->translator);
		$form->profile();
		
		$form1 = new StaticForm($this->layout()->translator);
		$form1->changepassword();
		
		$form2 = new StaticForm($this->layout()->translator);
		$form2->changeEmail();
		
		try {
			$admindata = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."' and ".T_CLIENT_VAR."client_type='admin'","fetch");

        } catch (\Exception $e) {
             return $this->redirect()->toRoute('adminprofile');
        }
		
		$userData = removePrefixFromFieldValue($admindata,T_CLIENT_VAR);
	
		unset($userData['client_password']);
		$form->populateValues($userData);
			
		if($this->getRequest()->isPost())
		{
 			$data = $this->getRequest()->getPost();
			$data = decryptPswFields($data);
			$form->setData($data);

			if($form->isValid())
			{
				$data_to_update = $data;	
				unset($data_to_update['post_csrf']);

				$data_to_update=GetFormElementsName($data_to_update,T_CLIENT_VAR);	

				$is_update=$this->SuperModel->Super_insert(T_CLIENTS,$data_to_update,T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."'");

				if(isset($is_update) && $is_update->success)
				{

					$admin_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."'","fetch");
					$session = new Container(ADMIN_AUTH_NAMESPACE);
					$session['adminData']=$admin_data;
					$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("profile_change_txt");
				  	return $this->redirect()->toRoute('adminprofile');

				}

			} else { 	
				$this->adminMsgsession['errorMsg'] =$this->layout()->translator->translate("check_info_txt");			

			}

  		}

		$this->layout()->pageHeading =$this->layout()->translator->translate("my_acc_txt");	

        return new ViewModel(array(
            'form' => $form,
			'form1' => $form1,
			'form2' => $form2,
			'adminData' => $admindata,
			'pageHeading' =>$this->layout()->translator->translate("my_acc_txt"),
			'pactivetab'=>$pactivetab
        ));

    }

	public function checkchangeemailAction(){

		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {

		$session = new Container(ADMIN_AUTH_NAMESPACE);

		 $admin_user = (object) $session['adminData'];

		$email_address = $this->params()->fromQuery('client_email');

		$user_info = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email=:EMAIL",'fetch',array("warray"=>array("EMAIL"=>$email_address)));

		// and ".T_CLIENT_VAR."client_id!='".$admin_user->{T_CLIENT_VAR.'client_id'}."'

		if($user_info)

			echo json_encode("`$email_address`"." ".$this->layout()->translator->translate(" already exists!!"));

		else

			echo json_encode("true");

		exit();

		}

		return $this->redirect()->toRoute('adminprofile');

	}

	public function changeemailAction()
    {
		$form = new StaticForm($this->layout()->translator);
		$form->changeEmail();

		try {
			$session = new Container(ADMIN_AUTH_NAMESPACE);
			$adminuser = $session['adminData'];
			$admindata = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."'","fetch");
			//prd($admindata);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('adminprofile',array("tabtype"=>4));
        }
		
		if($this->getRequest()->isPost())
		{
 			$data = $this->getRequest()->getPost();

 			// prd($data);	
			$data = decryptPswFields($data);
			$form->setData($data);

			if($form->isValid())
			{ 
				$data_to_update = $data;			
				$emailPsw = $data_to_update['client_email_password'];
				unset($data_to_update['client_email_password']);
				$data_to_update['client_email'] = strtolower($data_to_update['client_email']);
				$dataToUpdate[T_CLIENT_VAR.'client_email'] = $data_to_update['client_email'];

				$id = $admindata[T_CLIENT_VAR.'client_id'];
				//echo T_CLIENT_VAR."client_email='".$data_to_update['client_email']."' and ".T_CLIENT_VAR."client_id!='".$id."'";die;
				$checkEmail = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email='".$data_to_update['client_email']."' and ".T_CLIENT_VAR."client_id!='".$id."'","fetch");
				//pr($admindata[T_CLIENT_VAR.'client_password']);
				// echo $checkEmail;exit;

				if(empty($checkEmail) && md5($emailPsw) == $admindata[T_CLIENT_VAR.'client_password'])
				{
					if($admindata[T_CLIENT_VAR.'client_email']!= $dataToUpdate[T_CLIENT_VAR.'client_email'] )
					{
						$resetKey = md5($admindata[T_CLIENT_VAR.'client_id']."!@#$%^".$admindata[T_CLIENT_VAR.'client_created'].time());
						$UpdateArr = array(
							T_CLIENT_VAR.'client_email_update' => $dataToUpdate[T_CLIENT_VAR.'client_email'],
							T_CLIENT_VAR.'client_pass_resetkey' => $resetKey,
						);
						
						
						$is_update=$this->SuperModel->Super_Insert(T_CLIENTS,$UpdateArr,T_CLIENT_VAR."client_id='".$id."'");
						
						if(is_object($is_update) and $is_update->success){
							$user_os        =   getOS();
							$user_browser   =   getBrowser();
							$user_browser_name = '';
							if(isset($user_browser['name']) and !empty($user_browser['name'])){
								$user_browser_name = $user_browser['name'].' (Version: '.$user_browser['version'].')';
							}	

							$ip = $_SERVER['REMOTE_ADDR'];	

							$device_details =   "<strong style='color:#191919'>Browser: </strong>".$user_browser_name."<br /><strong style='color:#191919'>IP: </strong>".$ip."";

							$oldMailText = '<p style="color:#191919;font-size: 15px;">Your email has been updated with '.$dataToUpdate[T_CLIENT_VAR.'client_email'].'. </p><p><strong style="color:#191919;">Device Details: </strong></p><p>'.$device_details.'</p>';	

							$oldMailArr = array(
								"user_name" => ucwords($admindata[T_CLIENT_VAR."client_name"]),
								"user_email" => $admindata[T_CLIENT_VAR."client_email"],
								"message" => $oldMailText,
								"subject" => "Email address has been updated."
							);	

							$isSend = $this->EmailModel->sendEmail('notification_email',$oldMailArr);

							/* send an email to new email */
							$verifyLink = ADMIN_APPLICATION_URL."/verify-email/".$resetKey;

							$newMailText = '<p style="color:#191919;font-size: 15px;">Please verify your email address via below link: </p><p style="text-align:center"><a href="'.$verifyLink.'" style="display: inline-block; padding: 11px 30px; margin: 20px 0px 30px; font-size: 15px; color: #fff; background: #010101; text-decoration:none;">Verify Email</a></p>';	
			
							$newMailArr = array(
								"user_name" => ucwords($admindata[T_CLIENT_VAR."client_name"]),
								"user_email" => $dataToUpdate[T_CLIENT_VAR.'client_email'],
								"message" => $newMailText,
								"subject" => "Verify your email address"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$newMailArr);	

							$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Email verification link has been sent to the updated email. Please verify to update it.");

						} else {	

							$this->adminMsgsession['errorMsg'] =$this->layout()->translator->translate("check_info_txt");

						}

					}

				} else{

					if(!empty($checkEmail)){
						$this->adminMsgsession['errorMsg'] ="Email address already exists.";

					} else {
						$this->adminMsgsession['errorMsg'] ="Invalid old password";

					}

				}

			}

  		}

		return $this->redirect()->toRoute('adminprofile',array("tabtype"=>4));

    }

	

	public function changepasswordAction()
    {
		$form = new StaticForm($this->layout()->translator);
		$form->changepassword();
		
		try {
			$session = new Container(ADMIN_AUTH_NAMESPACE);
			$adminuser = $session['adminData'];
			$admindata=(array)$adminuser;
        } catch (\Exception $e) {
             return $this->redirect()->toRoute('adminprofile',array("tabtype"=>3));
        }
		
		if($this->getRequest()->isPost())
		{
 			$data = $this->getRequest()->getPost();
			$data = decryptPswFields($data);
			$form->setData($data);

			$id = $admindata['yurt90w_client_id'];

			if($form->isValid())
			{
				$data_to_update = $data;
				if(trim($data_to_update['client_old_password'])==trim($data_to_update['client_password']))
				{
					$this->adminMsgsession['errorMsg']='New password should be different from old password';
					return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));
				}

				if($data_to_update['client_password'] == $data_to_update['client_rpassword'])
				{
					$password = md5($data_to_update['client_old_password']);
					$passWhere = T_USERS_CONST."_id=:clientId and ".T_USERS_CONST."_password=:clientPwd";
					$isCheck = $this->SuperModel->Super_Get(T_CLIENTS,$passWhere,"fetch",array("warray"=>array("clientId"=>$id,'clientPwd'=>$password)));

					if($isCheck)
					{
						$updateData = array();
						$updateData[T_USERS_CONST.'_password'] = md5($data_to_update['client_password']);

						unset($data_to_update['client_old_password']);
						unset($data_to_update['client_password']);
						unset($data_to_update['client_rpassword']);
						unset($data_to_update['post_csrf']);						
						
						$is_update = $this->SuperModel->Super_insert(T_CLIENTS,$updateData,"yurt90w_client_id='".$id."'");
						
						if(is_object($is_update) and $is_update->success)
						{ 
							$this->EmailModel->sendEmail('admin_password_update',array());
							$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("pass_change_txt");
							return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));
						}

					} else {
						$this->adminMsgsession['errorMsg'] = 'Old password is mismatched';
						return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));
					}

				} else {
					$this->adminMsgsession['errorMsg'] = 'New password and confirm password are mismatched';
					return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));
				}

			} else {
				$this->adminMsgsession['errorMsg'] = 'Please enter correct information.';
				return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));	
			}
  		}

		$this->layout()->pageHeading =$this->layout()->translator->translate("Change Password");
		return $this->redirect()->toRoute('adminprofile');	
		// Pass form variable to view
        /*return new ViewModel(array(
            'form' => $form,
			'adminData' => $admindata,
			'passwordBox' => 'yes',
			'pageHeading' =>$this->layout()->translator->translate("Change Password")
        ));*/
    }

    

	public function checkoldpassAction()
	{

		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {

		$session = new Container(ADMIN_AUTH_NAMESPACE);

		$admin_user = $session['adminData'];

		

		if($admin_user){

			

			$admindata=(array)$admin_user;

		 	$user_id = $admindata['yurt90w_client_id'];	

			$user_password = md5($_REQUEST['client_password']);

			$user =$this->UserModel->getAdminUser($user_id);

			

			if($user['yurt90w_client_password']==$user_password){

				echo json_encode($this->layout()->translator->translate("old_pass_txt"));

			}else{

				echo json_encode("true");	

			}

		}else{ 

			echo json_encode("true");	

		}

 		exit();

		}

		return $this->redirect()->toRoute('adminprofile', array('tabtype' => '3'));

	}

	

	/* 	Ajax Call For Checking the Old Password for the Logged User */
	public function checkpasswordAction()
	{ 
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest()) 
		{ 
			$session = new Container(ADMIN_AUTH_NAMESPACE);
			$admin_user = $session['adminData'];

			$admindata=(array)$admin_user;
			$user_id = $admindata[T_CLIENT_VAR.'client_id'];
			
			$emailPsw = 0;

			if(isset($_REQUEST['client_old_password']))
			{
				$user_password = md5($_REQUEST['client_old_password']);

			} else {
				$emailPsw = 1;
				$user_password = md5($_REQUEST['client_email_password']);
			}

 			$user = $this->UserModel->profilecheckoldpassword($user_password,$user_id);

 			if(!$user){
				echo $emailPsw == 0 ? json_encode($this->layout()->translator->translate("oldpass_match_txt")) : json_encode($this->layout()->translator->translate("Wrong Password"));

			} else { 
				echo json_encode("true");	
			}

 			exit();
		}
		return $this->redirect()->toRoute('adminprofile');
	}

	

	public function checkemailAction(){

		$email_address = strtolower($_REQUEST['user_email']);

		$exclude = strtolower($_REQUEST['exclude']);

		

		$user_id = false ;

		if(!empty($exclude))

		{

			$session = new Container(ADMIN_AUTH_NAMESPACE);

			$adminuser = $session['adminData'];

			

			$admindata=(array)$adminuser;

			$user_id = $admindata['user_id'];

		}

		

		$email = $this->UserModel->checkEmail($email_address,$user_id);

		

		if($email)

			echo json_encode("`$email_address` ".$this->layout()->translator->translate("already_exist_txt"));

		else

			echo json_encode("true");

		exit();

	}

	

	

	

	public function checkotheremailAction(){

		$email_address = strtolower($_REQUEST['user_email']);

		$exclude = strtolower($this->params()->fromQuery('user'));

		

		$user_id = false;

		if(!empty($exclude))

		{

			$userData = $this->SuperModel->Super_Get(T_CLIENTS,"user_id='".$exclude."'",'fetch');

			$user = $userData;

			$user_id =$userData['user_id'];

		}

		

		$email = $this->UserModel->checkEmail($email_address,$user_id);

		

		if($email)

			echo json_encode("`$email_address` ".$this->layout()->translator->translate("already_exist_txt"));

		else

			echo json_encode("true");

		exit();

	}

	

	public function uploadavatarAction(){

			$request = $this->getRequest();

			if ($request->isXmlHttpRequest() ) {
		$extarr=explode(".",$_FILES['client_image']['name']);

		$imagesize=getimagesize($_FILES['client_image']['tmp_name']);

		$ext=explode(",",IMAGE_VALID_EXTENTIONS);

		if(!in_array($extarr[count($extarr)-1],$ext)){

			echo json_encode(0);

		}

		else if(empty($imagesize)){

			echo json_encode(0);

		}

		else{

			$imagePlugin = $this->Image();		

			$files =  $this->getRequest()->getFiles()->toArray();

			$filename = $files['client_image']['name'];

			if($filename)

				{

					$ext = pathinfo($filename, PATHINFO_EXTENSION);

					

					if(!in_array($ext,explode(',',IMAGE_VALID_EXTENTIONS)))

					{

						/*$this->adminMsgsession['errorMsg']='Please Upload Valid Image File';

						return $this->redirect()->toRoute('adminprofile', array('action' => 'index'));*/

						echo json_encode(0);			

					}
					else if (strpos(file_get_contents($files['client_image']['tmp_name']), '<?php') !== false) 
						{
							echo json_encode(0);
						}
	
						else if (strpos(file_get_contents($files['client_image']['tmp_name']), '<?=') !== false) 
						{
							echo json_encode(0);
						}
	
						else if (strpos(file_get_contents($files['client_image']['tmp_name']), '<? ') !== false) 
						{
							echo json_encode(0);
						}

					else

					{

			$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>0,"thumb"=>true));

			if($is_uploaded_icon->success=="1" && $is_uploaded_icon->media_path!=''){

			$is_update=$this->SuperModel->Super_insert(T_CLIENTS,array(T_CLIENT_VAR.'client_image'=>$is_uploaded_icon->media_path),T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."'");

			$admin_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$this->adminuser[T_CLIENT_VAR.'client_id']."'","fetch");

			$session = new Container(ADMIN_AUTH_NAMESPACE);

			$session['adminData']=$admin_data;

			$mediaPath=$is_uploaded_icon->media_path;

			echo ($mediaPath);

			}

			else{

					echo json_encode(0);

				}

					}

				}else{

					echo json_encode(0);

				}

		}

		exit();

		}

		return $this->redirect()->toRoute('adminprofile');

	}

	

}