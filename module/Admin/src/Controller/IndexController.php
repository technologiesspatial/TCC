<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Index controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller;

use Admin\Model\AdminTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Admin\Form\StaticForm;
use Admin\Form\ProfileForm;
use Admin\Form\MasterForm;
use Zend\Session\Container;
use Admin\Model\Admin;
use Admin\Model\User;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class IndexController extends AbstractActionController
{

	private $AbstractModel,$Adapter,$UserModel,$EmailModel;

    public function __construct($AbstractModel,Adapter $Adapter,User $UserModel,$EmailModel,$adminMsgsession,$config_data)
    {
        $this->SuperModel = $AbstractModel;
		$this->Adapter = $Adapter;
		$this->UserModel = $UserModel;
		$this->EmailModel = $EmailModel;
		$this->adminMsgsession = $adminMsgsession;
		$session = new Container(ADMIN_AUTH_NAMESPACE);
		$this->adminData=$session['adminData'];
		$this->SITE_CONFIG = $config_data;
    }

	public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $session = $container->get(SessionContainer::class);

        $db = $container->get(DbAdapter::class);
	}

	public function verifiedemailAction()
	{ 
		$key = $this->params()->fromRoute('key'); 

		if($key==''){
			return $this->redirect()->toUrl(ADMIN_APPLICATION_URL);
		}

		$user_info =$this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_pass_resetkey = :KEY and ".T_CLIENT_VAR."client_type='admin'",'fetch',array("warray"=>array("KEY"=>$key)));

		if(empty($user_info)){
			return $this->redirect()->toUrl(ADMIN_APPLICATION_URL);
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("invalid_req");	

		}

		$data_to_update = array();
		$data_to_update[T_CLIENT_VAR.'client_email'] = $user_info[T_CLIENT_VAR.'client_email_update'];
		$data_to_update[T_CLIENT_VAR.'client_pass_resetkey'] = NULL;
		$data_to_update[T_CLIENT_VAR.'client_email_update'] = NULL;

		$user_update =$this->SuperModel->Super_Insert(T_CLIENTS,$data_to_update,T_CLIENT_VAR."client_id= '".$user_info[T_CLIENT_VAR.'client_id']."'");

		$session = new Container(ADMIN_AUTH_NAMESPACE);		
		if(isset($session['adminData']) && !empty($session['adminData'])){

			$admin_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$session['adminData'][T_CLIENT_VAR.'client_id']."'","fetch");
			$session = new Container(ADMIN_AUTH_NAMESPACE);
			$session['adminData']=$admin_data;	
		}
	
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Email Address is verified");	
		return $this->redirect()->toUrl(ADMIN_APPLICATION_URL);	
	}
	
	public function exportuserAction() {
		$Id = $this->params()->fromRoute('sdfsd');
		$fieldsArr = array(
			"full_name"=> "Full Name",
			"email_address"=> "Email Address",
			"user_name" => "User Name",
			"birthday"=> "Birthday",
			"gender"=> "Gender",
			"location"=> "Location",
			"member_since"=> "Member Since",
			"paypal_email" => "Paypal Email"
		);
		$getType = $this->params()->fromRoute('sdfsd');
		$where = '';
		if(empty($Id)) {
			$GetData = $this->SuperModel->Super_Get(T_CLIENTS,'1','fetchAll',array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email",T_CLIENT_VAR."client_username",T_CLIENT_VAR."client_birthday",T_CLIENT_VAR."client_address",T_CLIENT_VAR."client_created",T_CLIENT_VAR."client_paypal_email"),'order'=>'yurt90w_client_id  desc'));
		} else {
			$client_arr = explode(",",$Id);
			foreach($client_arr as $client_key => $client_val) {
				$client_wh[] = "yurt90w_client_id = ".$client_val;				
			}
			$client_ids= implode(" or ",$client_wh);
			$client_where = $client_ids;
			$GetData = $this->SuperModel->Super_Get(T_CLIENTS,$client_where,'fetchAll',array('order'=>'yurt90w_client_id desc'));
		}
		foreach ($GetData as $key => $value) {
			if($value[T_CLIENT_VAR."client_gender"] == '0') {
				$gender = "Male";
			} else if($value[T_CLIENT_VAR."client_gender"] == '1') {
				$gender = "Female";
			} else {
				$gender = "Other";
			}	
			$exportArr[$key] = array(
				$fieldsArr['full_name'] =>  $value[T_CLIENT_VAR."client_name"],
				$fieldsArr['email_address'] =>  $value[T_CLIENT_VAR."client_email"],
				$fieldsArr['user_name'] => $value[T_CLIENT_VAR."client_username"],
				$fieldsArr['birthday'] =>  $value[T_CLIENT_VAR."client_birthday"],
				$fieldsArr['gender'] =>  $gender,
				$fieldsArr['location'] => $value[T_CLIENT_VAR."client_address"],
				$fieldsArr['member_since'] => $value[T_CLIENT_VAR.'client_created'],
				$fieldsArr['paypal_email'] => $value[T_CLIENT_VAR.'client_paypal_email']
			);
		}
		$newSheetName = "Users Data";
		if(!empty($exportArr)){
			$var = exportData($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	

   /* Editor Upload Image */
	public function uploadfilesAction()
	{
		if(isset($_FILES['upload'])){
			$allowedExts = array("gif","jpeg","jpg", "png","JPG","JPEG","PNG","GIF");
			$response=cheakfiletype($_FILES['upload']['name'], $allowedExts);
			
			//$funcNum = $_GET['CKEditorFuncNum'] ;
			if($response)
			{
				if($_FILES['upload']['tmp_name']!=''){
					$finfo = finfo_open(FILEINFO_MIME_TYPE); 
					$uploaded_image_extension = getFileExtension($_FILES['upload']['name']);
					$typeval=finfo_file($finfo, $_FILES['upload']['tmp_name']);
					
					finfo_close($finfo);
					if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png'))){

						$message = 'Please upload valid image.';
						echo json_encode(array("uploaded"=>false,"error"=>array("message"=>$message)));exit();
					}
				}

				$filen = $_FILES['upload']['tmp_name']; 
				$mysize = $_FILES['upload']['size'];
				$name = time().rand(1,500).$_FILES['upload']['name'] ;
				$con_images = MEDIA_IMAGES_PATH."/".$name;
				move_uploaded_file($filen, $con_images );
				$url = HTTP_MEDIA_IMAGES_PATH."/".$name;
				echo json_encode(array("uploaded"=>true,"url"=>$url));exit();

			} else {
				$message = 'Please upload valid image.';
				echo json_encode(array("uploaded"=>false,"error"=>array("message"=>$message)));exit();
			}
		}
		exit();
 	}
	

    public function indexAction()
    {
		$dbAdapter = $this->Adapter;
		$session = new Container(ADMIN_AUTH_NAMESPACE);

		if(isset($session['adminData']) && !empty($session['adminData'])){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/dashboard');

		} else {
			return $this->redirect()->toRoute('adminlogin', array('action'=>'/adminlogin'));
		}

    }

	public function errorpageAction()
    {

    }

	public function dashboardAction()
	{
		
		$session = new Container(ADMIN_AUTH_NAMESPACE); 		
		if(!isset($session['adminData']) && empty($session['adminData']))
		{
			return $this->redirect()->toRoute('adminlogout');
		}
		
		$view = new ViewModel();
		$email_count=$this->SuperModel->Super_Get(T_EMAIL,"1","fetch",array("fields"=>array("email_count"=>new Expression("count(emailtemp_key)"))));

		$page_count=$this->SuperModel->Super_Get(T_PAGES,"1","fetch",array("fields"=>array("page_count"=>new Expression("count(page_id)"))));
		
		$getFieldList=array(
			
			"template"=>array('emailtemp_key',T_EMAIL,$this->layout()->translator->translate("Email Templates"),1,T_EMAIL),
			"pages"=>array('page_id',T_PAGES,$this->layout()->translator->translate("Pages"),1,T_PAGES),
			"subscribers"=>array('newsletter_sub_id',T_NEWSSUBSCRIBERS,$this->layout()->translator->translate("Subscribers"),"1",T_NEWSSUBSCRIBERS),
			"users"=>array(T_USERS_CONST.'_id',T_USERS,$this->layout()->translator->translate("Users"),T_USERS_CONST."_type='user'",T_USERS)
			
		);
		$listCount = array();
		$verified_udata = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_type='user' and yurt90w_client_email_verified = '1'","fetchAll",array('fields'=>'yurt90w_client_id'));
		$verified_users = count($verified_udata);
		$unverified_udata = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_type='user' and yurt90w_client_email_verified != '1'","fetchAll",array('fields'=>'yurt90w_client_id'));
		$unverified_users = count($unverified_udata);
		if(!empty($getFieldList)){
			foreach($getFieldList as $fild_Key=>$fieldValue){
				$getwhere = "1";
				if(isset($fieldValue[3]) && $fieldValue[3]!=''){
					$getwhere = $fieldValue[3];
				}

				$table_name = $fild_Key;
				if(isset($fieldValue[4]) && $fieldValue[4]!=''){
					$table_name = $fieldValue[4];
				}
				
				$element_count = $this->SuperModel->Super_Get($table_name,$getwhere,"fetch",array(
					"fields"=>array($fild_Key."_count" => new Expression("count(".$fieldValue[0].")"))));

				$listCount[$fild_Key]['count'] = $element_count[$fild_Key."_count"];
				$listCount[$fild_Key]['url'] = $fieldValue[1];

				if(!isset($fieldValue[2]) || $fieldValue[2] == ''){
					$listCount[$fild_Key]['label']=ucwords(implode(" ",(explode("_",$fild_Key))));

				} else {
					$listCount[$fild_Key]['label']=$fieldValue[2];
				}

				switch($fild_Key){
					case "pages":$iconName="icon-list";$routeName="pages";break;
					case "template":$iconName="icon-envelope";$routeName="emailtemplate";break;			
					
					case "subscribers":$iconName="icon-list";$routeName="subscribers";break;
					case "users":$iconName="icon-users";$routeName="users";break;
					case "members":$iconName="icon-users";$routeName="members";break;
					case "storerequests":$iconName="icon-list";$routeName="seller-applications";break;
					case "badgerequests":$iconName="icon-list";$routeName="badge-requests";break;
					case "productrequests":$iconName="icon-list";$routeName="products";break;
				}

				$listCount[$fild_Key]['icon']=$iconName;
				$listCount[$fild_Key]['url']=$routeName;
			}
		}
		$pending_seller = $this->SuperModel->Super_Get(T_STORE,"store_name != '' and (store_approval = '4' || store_approval = '' || store_approval = '3')","fetchAll");
		$pending_dseller = $this->SuperModel->Super_Get(T_STORE,"store_name != '' and (store_approval = '4')","fetchAll");
		$declined_seller = $this->SuperModel->Super_Get(T_STORE,"store_name != '' and store_approval = '2'","fetchAll");
		$approved_seller = $this->SuperModel->Super_Get(T_STORE,"store_name != '' and store_approval = '1'","fetchAll");
		$pending_badges = $this->SuperModel->Super_Get(T_STORE,"store_doc1 != '' and store_verification = '3'","fetchAll");
		$declined_badges = $this->SuperModel->Super_Get(T_STORE,"store_doc1 != '' and store_verification = '2'","fetchAll");
		$approved_badges = $this->SuperModel->Super_Get(T_STORE,"store_doc1 != '' and store_verification = '1'","fetchAll");
		$pending_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '0'","fetchAll");
		$declined_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '2'","fetchAll");
		$approved_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1'","fetchAll");
		
		$process_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '1'","fetchAll");
		$ready_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '2'","fetchAll");
		$shipped_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '3'","fetchAll");
		$delivered_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '4'","fetchAll");
		
		$days_before = date('Y-m-d', strtotime('-6 days'));
		
		$getUsersState = $this->SuperModel->Super_Get(
				T_USERS,
				T_USERS_CONST."_type='user' and YEAR(DATE(yurt90w_client_created)) =  ".date("Y"),
				'fetchAll',
				array(
					'group'=>new Expression('MONTH(DATE(yurt90w_client_created))'),'fields'=>array('users'=>new Expression('COUNT(*)'),'date'=>new Expression('MONTH(DATE(yurt90w_client_created))'),'date-2'=>new Expression('yurt90w_client_created'),'year'=>new Expression('YEAR(DATE(yurt90w_client_created))'))
				)
		);
		
		$getallUsers = $this->SuperModel->Super_Get(
				T_USERS,
				T_USERS_CONST."_type='user' and YEAR(DATE(yurt90w_client_created)) =  ".date("Y"),
				'fetchAll',
				array(
					'group'=>new Expression('MONTH(DATE(yurt90w_client_created))'),'fields'=>array('users'=>new Expression('COUNT(*)'),'date'=>new Expression('MONTH(DATE(yurt90w_client_created))'),'date-2'=>new Expression('yurt90w_client_created'))
				)
			);
			
		$user_state = array();
		$total_users = array();
		$minus_year = date("Y");
		foreach($getUsersState as $key => $data){$user_state[$data['date']] = $data['users'];}
		$tot_user = 0;
		foreach($getallUsers as $user_key => $user_val){ /*$getallUsers[$user_key]['users'] += $user_val["users"];*/ $tot_user += $user_val["users"];   $total_users[$user_val['date']] = $user_val["users"];}
		$current_month = date("m");
		if($current_month > 9) {
			$current_month = $current_month;
		} else {
			$current_month = str_replace("0","",$current_month);
		}
		for($i=1;$i<=($current_month+1);$i++) {
			if($i < 10) {
				$month_disArr[] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
				$month_checkArr[$i] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
			} else {
				/*if($i == '13') {
					$month_disArr[] = 'JAN '.($minus_year+1);
					$month_checkArr[$i] = 'JAN '.($minus_year+1);
					
				} else {*/
					$month_disArr[] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
					$month_checkArr[$i] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
				/*}*/
			}
		}
		foreach($month_disArr as $key=>$val){
			//users stats
			if(!array_key_exists($key,$user_state)){$user_state[$key] = (int)0;}
			else{$user_state[$key] = (int)$user_state[$key];}
			
			if(!array_key_exists($key,$total_users)){$total_users[$key] = (int)0;}
			else{$total_users[$key] = (int)$total_users[$key];}
		}
		ksort($user_state);
		ksort($total_users);
		foreach ($user_state as $key => $value) {
			if($key > 0){
				$user_state[$key-1] = (int)$value;
				$total_users[$key-1] = (int)$total_users[$key];
				
			}
		}
		array_pop($user_state);
		array_pop($total_users);
		$tot = 0;
		foreach($total_users as $total_users_key => $total_users_val) {
			$tot += $total_users_val;
			$total_users[$total_users_key]	= $tot;
		}
		$cat = json_encode($month_disArr);
		$view->setVariable('cat',$cat);
		$view->setVariable('total_users',json_encode($total_users));
		$view->setVariable('user_state',json_encode($user_state));	
		
		
		/* Earning graph calculation */
		$getOrdersState = $this->SuperModel->Super_Get(
				T_PRODORDER,
				"YEAR(DATE(order_date)) =  ".date("Y"),
				'fetchAll',
				array(
					'group'=>new Expression('MONTH(DATE(order_date))'),'fields'=>array('amount'=>new Expression('SUM(order_sitefee)'),'date'=>new Expression('MONTH(DATE(order_date))'),'date-2'=>new Expression('order_date'),'year'=>new Expression('YEAR(DATE(order_date))'))
				)
		);
		
		$getallOrders = $this->SuperModel->Super_Get(
				T_PRODORDER,
				"YEAR(DATE(order_date)) =  ".date("Y"),
				'fetchAll',
				array(
					'group'=>new Expression('MONTH(DATE(order_date))'),'fields'=>array('amount'=>new Expression('SUM(order_sitefee)'),'date'=>new Expression('MONTH(DATE(order_date))'),'date-2'=>new Expression('order_date'))
				)
			);	
		$order_state = array();
		$total_orders = array();
		$month_disArr = array();
		$minus_year = date("Y");
		foreach($getOrdersState as $key => $data){$order_state[$data['date']] = $data['amount'];}
		$tot_order = 0;
		foreach($getallOrders as $order_key => $order_val){ /*$getallUsers[$user_key]['users'] += $user_val["users"];*/ $tot_order += $order_val["amount"];   $total_orders[$order_val['date']] = $order_val["amount"];}
		$current_month = date("m");
		if($current_month > 9) {
			$current_month = $current_month;
		} else {
			$current_month = str_replace("0","",$current_month);
		}
		for($i=1;$i<=($current_month+1);$i++) {
			if($i < 10) {
				$month_disArr[] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
				$month_checkArr[$i] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
			} else {
					$month_disArr[] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
					$month_checkArr[$i] = strtoupper(date('M', strtotime("2012-$i-01"))).' '.$minus_year;
			}
		}
		foreach($month_disArr as $key=>$val){
			//users stats
			if(!array_key_exists($key,$order_state)){$order_state[$key] = (int)0;}
			else{$order_state[$key] = (float)bcdiv($order_state[$key],1,2);}
			
			if(!array_key_exists($key,$total_orders)){$total_orders[$key] = (int)0;}
			else{$total_orders[$key] = (float)bcdiv($total_orders[$key],1,2);}
		}
		ksort($order_state);
		ksort($total_orders);
		foreach ($order_state as $key => $value) {
			if($key > 0){
				$order_state[$key-1] = (float)bcdiv($value,1,2);
				$total_orders[$key-1] = (float)bcdiv($total_orders[$key],1,2);	
			}
		}
		array_pop($order_state);
		array_pop($total_orders);
		$tot = 0;
		foreach($total_orders as $total_orders_key => $total_orders_val) {
			$tot += $total_orders_val;
			$total_orders[$total_orders_key]	= bcdiv($tot,1,2);
		}
		$caty = json_encode($month_disArr);
		$view->setVariable('caty',$caty);
		$view->setVariable('total_orders',json_encode($total_orders));
		$view->setVariable('order_state',json_encode($order_state));
		/* */
			
		
		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("dash_head_txt"));
		$view->setVariable('pageHeading',$this->layout()->translator->translate("dash_head_txt"));	
		$view->setVariable('listCount',$listCount);
		$view->setVariable('pending_seller',count($pending_seller));
		$view->setVariable('declined_seller',count($declined_seller));
		$view->setVariable('approved_seller',count($approved_seller));
		$view->setVariable('pending_badges',count($pending_badges));
		$view->setVariable('declined_badges',count($declined_badges));
		$view->setVariable('approved_badges',count($approved_badges));
		$view->setVariable("pending_products",count($pending_products));
		$view->setVariable("declined_products",count($declined_products));
		$view->setVariable("approved_products",count($approved_products));
		$view->setVariable("verified_users",$verified_users);
		$view->setVariable("unverified_users",$unverified_users);
		$view->setVariable("process_orders",count($process_orders));
		$view->setVariable("ready_orders",count($ready_orders));
		$view->setVariable("shipped_orders",count($shipped_orders));
		$view->setVariable("delivered_orders",count($delivered_orders));
		return $view;    
    }
	
	public function loadmoremessagesAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$view = new ViewModel();
			$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"chat_to =:TID and chat_by =:UID and chat_readstatus = '2'","fetchAll",array('order'=>'chat_id desc','limit'=>10,'warray'=>array('TID'=>'1','UID'=>base64_decode($data["chat"]))),$joinArr);
			array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
			$chk_data["chat_readstatus"] = '1';
			$this->SuperModel->Super_Insert(T_CHAT,$chk_data,"chat_by = '".base64_decode($data["chat"])."' and chat_to = '1'");
			$view = new ViewModel();
			$view->setVariable('adminuser', $this->adminData);
			$view->setVariable('message_record',$message_record);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function postmessageAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$last_chat = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:UID and chat_to =:TID)","fetch",array('warray'=>array('TID'=>base64_decode($data["chat"]),'UID'=>'1'),'order'=>'chat_id desc'));
			$chat_data["chat_by"] = '1';
			$chat_data["chat_to"] = base64_decode($data["chat"]);
			$chat_data["chat_text"] = strip_tags($data["chat_txt"],'<br>');
			$chat_data["chat_date"] = date("Y-m-d H:i:s");
			$is_Inserted = $this->SuperModel->Super_Insert(T_CHAT,$chat_data); 
			$chat_record = $this->SuperModel->Super_Get(T_CHAT,"chat_id = '".$is_Inserted->inserted_id."'","fetch");
			
			$current_timer = strtotime(date("Y-m-d H:i:s"));
			$prev_timer = strtotime($last_chat["chat_date"]);
			$diff_timer = bcdiv(abs($current_timer - $prev_timer) / 60,1,2);
			if($diff_timer > 60 || empty($last_chat)) {
				$user_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>base64_decode($data["chat"]))));
				$message = "Admin has sent you a message.<br/>".nl2br($chat_data["chat_text"]);
				
				$notify_data["notification_type"] = '5';
				$notify_data["notification_by"] = '1';
				$notify_data["notification_to"] = base64_decode($data["chat"]);
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				
				$mail_const_data2 = array(
					"user_name" => $user_details[T_CLIENT_VAR.'client_name'],
					"user_email" => $user_details[T_CLIENT_VAR.'client_email'],
					"message" => $message,
					"subject" => "Message notification"
				);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
			}
			$view = new ViewModel();
			$view->setVariable('message_record',$chat_record);
			$view->setVariable('adminuser', $this->adminData);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function prevmessagesAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
			);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:TID and chat_to =:UID) or (chat_to =:TID and chat_by =:UID)","fetchAll",array('offset'=>$data["start"],'order'=>'chat_id desc','limit'=>10,'warray'=>array('TID'=>$this->adminData[T_CLIENT_VAR.'client_id'],'UID'=>base64_decode($data["chat"]))),$joinArr);
			array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
			$view = new ViewModel();
			$view->setVariable('message_record',$message_record);
			$view->setVariable('adminuser',$this->adminData);
			$view->setTerminal(true);
			return $view;
		}
	}
	
    public function loginAction()
    {
		$this->layout('layout/login');
		$session = new Container(ADMIN_AUTH_NAMESPACE);		
		if(isset($session['adminData']) && !empty($session['adminData'])){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/dashboard');
		}
		$view = new ViewModel();
        $form = new StaticForm($this->layout()->translator);
		$form->login();
		
        // Check if user has submitted the form
        if($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();   
        	$data = decryptPswFields($data);
            $form->setData($data);
          
            // Validate form
            if($form->isValid()) {
                // Get filtered and validated data
                $data = $form->getData();
				$data['client_email'] = strtolower($data['client_email']);
				$admin = new Admin(); 
				$admin->exchangeArray2($data); 
				
				$userDetails=$this->UserModel->chkLogin($admin);	
				if(!empty($userDetails))
				{
					$client_where = T_CLIENT_VAR."client_id=:client_db_id";
					$userdata =	$this->SuperModel->Super_Get(T_CLIENTS,$client_where,"fetch",array("warray"=>array("client_db_id"=>$userDetails[0][T_CLIENT_VAR.'client_id']))); 
					
					$session->offsetSet('admin_id', $userdata[T_CLIENT_VAR.'client_id']);
					$session->offsetSet('adminData', $userdata);
			
					$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("welcome_txt")." ".$this->SITE_CONFIG['site_name'];
					if(isset($_GET['url'])){ 
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.urldecode($_GET['url']));
					}				
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

				} else {
					$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("invalid_email_txt");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

				}

            } else{
				$this->adminMsgsession['errorMsg'] = "Please enter correct login credentials.";

			}
        }         

       	$view->setVariable('form', $form);
		$view->setVariable('pageHeading',$this->layout()->translator->translate("get_login_txt"));	
		$this->layout()->form = $form;
		$this->layout()->type = 'login';
		$this->layout()->pageHeading = $this->layout()->translator->translate("get_login_txt");	
		return $view;    
    }

	public function forgotpasswordAction()
    {
		global $msg_session;
		$this->layout('layout/login');
		$session = new Container(ADMIN_AUTH_NAMESPACE);
		if(isset($session['adminData']) && !empty($session['adminData'])){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/dashboard');
		}
		
        $form = new StaticForm($this->layout()->translator);
		$form->forgotpassword();
		$this->layout()->form = $form;
		$this->layout()->type = 'forgotpassword';
		$this->layout()->pageHeading = $this->layout()->translator->translate("forget_pass_txt");
		if($this->getRequest()->isPost()) {
			$posted_data  =  $this->getRequest()->getPost();
			$form->setData($posted_data);
			if($form->isValid()){
 				$received_data = $form->getData();

 				$received_data['client_email'] = strtolower($received_data['client_email']);
				
				$checkEmail=$this->UserModel->checkAdminEmail(strtolower($received_data['client_email']),false,true);
				if(!empty($checkEmail))
				{
					$userdata=	$this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email='".strtolower($received_data['client_email'])."'",'fetch',array('fields'=>array(T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_created',T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email')));

					$reset_password_key = md5($userdata[T_CLIENT_VAR.'client_id']."!@#$%^".$userdata[T_CLIENT_VAR.'client_created'].time());

					$data_to_update = array(T_CLIENT_VAR."client_email"=>strtolower($received_data['client_email']),T_CLIENT_VAR."client_reset_key"=>$reset_password_key);		

					$this->SuperModel->Super_Insert(T_CLIENTS,$data_to_update,T_CLIENT_VAR.'client_id = '.$userdata[T_CLIENT_VAR.'client_id']);

					$user['pass_resetkey'] = $reset_password_key ;
					$user['user_name'] = $userdata[T_CLIENT_VAR.'client_name'];
					$user['user_email'] = $userdata[T_CLIENT_VAR.'client_email'];
					$user['user_type'] = 'admin';
					$isSend = $this->EmailModel->sendEmail('reset_password',$user);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("forget_email_txt");	
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

				} else {
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("invalid_email_id_txt");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/forgot-password');
				}

			} else {
				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("invalid_email_id_txt");

  			}
		}

		// Pass form variable to view
        return new ViewModel(array(
            'form' => $form
        ));
	}

	public function resetpasswordAction()
    {
		global $msg_session;
		$this->layout('layout/login');
		$session = new Container(ADMIN_AUTH_NAMESPACE);
		
		$this->layout('layout/login');
		if(isset($session['adminData']) && !empty($session['adminData'])){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/dashboard');
		}
		
		$view = new ViewModel();
		
		$form = new StaticForm($this->layout()->translator);
		$form->resetPassword();
		
		$key = $this->params()->fromRoute('key');
		if(empty($key)){
 			$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("invalid_req_txt");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/forgot-password');
		}
		 
		$user_info= $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_reset_key=:clreskey","fetch",array("warray"=>array("clreskey"=>$key)));

		if(!$user_info){
			$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("invalid_req_reset_txt");			 
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/forgot-password');
		}

		if($this->getRequest()->isPost()) {
			$posted_data  =  $this->getRequest()->getPost(); 
			
			$request = $this->getRequest();
			$data = $request->getPost();
			$data = decryptPswFields($data);
			$form->setData($data);

            if ($form->isValid()) { 
				$data_to_updates = $data;
				$data_to_update[T_CLIENT_VAR.'client_reset_key']="";
				$data_to_update[T_CLIENT_VAR.'client_password'] = md5($data_to_updates['client_password']);

				$ischeck=$this->SuperModel->Super_Insert(T_CLIENTS,$data_to_update,T_CLIENT_VAR.'client_id="'.$user_info[T_CLIENT_VAR.'client_id'].'"');

				if($ischeck){
					$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("pass_change_txt");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL);
				}	

			} else {
				$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("check_info_txt");
			}
		}		

		$view->setVariable('form', $form);
		$view->setVariable('key', $key);
		$view->setVariable('pageHeading',$this->layout()->translator->translate("reset_pass_txt"));	
		$this->layout()->form = $form;
		$this->layout()->type = 'resetpassword';
		$this->layout()->pageHeading = $this->layout()->translator->translate("reset_pass_txt");
		return $view;    
	}


	public function checkemailAction()
	{
		$email_address = $this->params()->fromQuery('client_email');
		$exclude = strtolower($this->params()->fromQuery('exclude'));
		$user_info= $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email=:clemailaddress and ".T_CLIENT_VAR."client_type='admin'","fetch",array("warray"=>array("clemailaddress"=>$email_address)));

		/*$user_info = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR.'client_email="'.$email_address.'" and '.T_CLIENT_VAR.'client_type="admin"','fetch');*/
		if(!$user_info)
			echo json_encode("`$email_address`"." ".$this->layout()->translator->translate("not_valid_txt"));
		else
			echo json_encode("true");
		exit();
	}
	
	public function logoutAction()
	{
		$session = new Container(ADMIN_AUTH_NAMESPACE);
		$session->user_id = "";			
        Container::setDefaultManager(null);

		$session->offsetUnset('adminData');
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL);		
	}

}