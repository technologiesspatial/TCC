<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: User controller
* * * * * * * * * * * * * * * * * * * * * */
namespace Admin\Controller;

use Admin\Model\AdminTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Model\Email;

class UserController extends AbstractActionController
{
	private $AbstractModel,$UserModel,$Adapter,$adminMsgsession,$EmailModel,$authService;

	/* Constructor of the controller */
    public function __construct($AbstractModel,$UserModel,$Adapter,$adminMsgsession,$config_data,$EmailModel,$authService)
    {
        $this->SuperModel = $AbstractModel;
		$this->UserModel = $UserModel;
		$this->Adapter = $Adapter;
		$this->adminMsgsession = $adminMsgsession;		
		$this->config_data = $config_data;	
		$this->EmailModel = $EmailModel;
		$session = new Container(ADMIN_AUTH_NAMESPACE);
		$this->adminuser = $session['adminData'];	
		$this->authService = $authService;	
	}
	
	/* User login via admin panel */
	public function accessaccountAction()
	{
		$user_id = paramValDecode($this->params()->fromRoute('user_id', 0),'user');
		
		$user_info = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id='".$user_id."' and ".T_USERS_CONST."_email_verified='1' and ".T_USERS_CONST."_type in('user','member')","fetch",array(),$joinArr=array());

		if(empty($user_info))
		{
			$this->adminMsgsession['infoMsg'] = $this->layout()->translator->translate("no_record_found");
			return $this->redirect()->toUrl($_SERVER['HTTP_REFERER']);
		}

		$user_info["isLoggedbyAdmin"] = 1;
		$this->authService->getStorage()->write((object)$user_info);
		
		return $this->redirect()->toUrl(APPLICATION_URL);
		exit();
	}

	/* List page to show added users */
	public function indexAction()
    {
		$pageHeading = "Users";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$userId = $this->params()->fromRoute('user_id');

		$view = new ViewModel(array('page_icon'=>'fa fa-users','userId'=>$userId));
		return $view;
    }

    /* Ajax request - Fetch records of added users */
	public function getuserslistAction()
	{
		$dbAdapter = $this->Adapter;
		$userId = $this->params()->fromRoute('user_id');

		if($userId!=''){
			$userId = myurl_decode($userId);
		}

		$aColumns = array(
			T_USERS_CONST.'_id',
			T_USERS_CONST.'_type',
			T_USERS_CONST.'_image',
			T_USERS_CONST.'_name',
			T_USERS_CONST.'_email',
			T_USERS_CONST.'_email_verified',
			T_USERS_CONST.'_status', 			
			T_USERS_CONST.'_created',
			T_USERS_CONST.'_coupon',
			T_USERS_CONST."_username",
			T_USERS_CONST.'_birthday',
			T_USERS_CONST.'_gender',
			T_USERS_CONST.'_address'
  		);

		
		$sTable = T_USERS;
		$sIndexColumn = T_USERS_CONST.'_id';
		//$sTable = 'users';
  		
		/*Table Setting*/
		{
	
			/* Paging */
			$sLimit = "";
			if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
			{
				$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
			}
			
			/* Ordering */
			$sOrder = "";
			if ( isset( $_GET['iSortCol_0'] ) )
			{
				$sOrder = "ORDER BY  ";
				for ( $i = 0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
				{
					if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
					{
						$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
							($_GET['sSortDir_'.$i] === 'asc' ? 'asc' : 'desc') .", ";
					}
				}
				
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" )
				{
					$sOrder = "";
				}
			}
			
			/* 
			 * Filtering
			 * NOTE this does not match the built-in DataTables filtering which does it
			 * word by word on any field. It's possible to do here, but concerned about efficiency
			 * on very large tables, and MySQL's regex functionality is very limited
			 */
			$sWhere = "";
			if ( isset($_GET['sSearch']) and $_GET['sSearch'] != "" )
			{
				$sWhere = "WHERE (";
				for ( $i = 0 ; $i<count($aColumns) ; $i++ )
				{
					//$sWhere .= "".$aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
					$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
				}
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
			}
			
			/* Individual column filtering */
			for ( $i = 0 ; $i<count($aColumns) ; $i++ )
			{
				if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
				{
					if ( $sWhere == "" )
					{
						$sWhere = "WHERE ";

					} else {
						$sWhere .= " AND ";
					}
					//$sWhere .= "".$aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
					$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
				}
			}
		
		}
		/* End Table Setting */
		
		$type = 'user';
		if($sWhere == ''){
			$sWhere = " where(".T_USERS_CONST."_type='".$type."')";

		} else {
			$sWhere .= " and(".T_USERS_CONST."_type='".$type."')";

		}

		if($userId != ''){
			$sWhere .= " and (".T_USERS_CONST."_id='".$userId."')";
		}

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();
		$qry = $results->getResource()->fetchAll();

		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal = $results->getResource()->fetchAll();
		//$aResultFilterTotal =  $this->dbObj->query($sQuery)->fetchAll(); 
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		
		
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable $sWhere";
		
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal = $results->getResource()->fetchAll();
		//$rResultTotal = $this->dbObj->query($sQuery)->fetchAll(); 
		$iTotal = $rResultTotal[0]['cnt'];
		
		/* Output */
 		$output = array(
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
		$j = 1;
		if(isset($_GET['sEcho'])){
			if($_GET['sEcho'] != '1'){
				$j = (($_GET['sEcho'] -1 ) * $_GET['iDisplayStart'] /($_GET['sEcho'] -1 )  )+1;
			}else{
				$j = 1;
			}
		}
		
		$current_date = date('Y-m-d');
		foreach($qry as $row1)
		{
			$chat_record = $this->SuperModel->Super_Get(T_CHAT,"chat_by = '".$row1[$sIndexColumn]."' and chat_to = '1' and chat_readstatus != '1'","fetchAll");
			$row = array();
			
			$row[] = $j;
  			$row[] = '<div class="text-center"><input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label></div>';
			
			switch($row1[T_USERS_CONST.'_email_verified'])
			{
				case '0':$verification_status = "<span class='btn btn-danger btn-xs'>Unverified</span>";break;
				default :$verification_status = "<span class='btn btn-info btn-xs'>Verified</span>";break;
			}

			$row[] = $row1[T_USERS_CONST.'_name']."<br />$verification_status";	

   			$row[] = $row1[T_USERS_CONST.'_email'];

			$row[] = date('M d, Y H:i a',strtotime($row1[T_USERS_CONST.'_created']));

			$checkClass = ""; $chkcount = '';

			if($row1[T_USERS_CONST.'_status'] == '1'){
				$checkClass = "checked";
			} 
			if(count($chat_record) > 0) {
				$chkcount = '<span class="cntlbl">'.count($chat_record).'</span>';
			}

				$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1[T_USERS_CONST.'_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";
			$row[] = $row1[T_USERS_CONST."_username"];
			$row[] = $row1[T_USERS_CONST."_birthday"];
			if($row1[T_USERS_CONST."_gender"] == '0') {
				$row[] = 'Male';
			} else if($row1[T_USERS_CONST."_gender"] == '1') {
				$row[] = 'Female';
			} else {
				$row[] = 'Other';
			}
			$row[] = $row1[T_USERS_CONST."_address"];
			
			$coupontg = '';
			if($row1[T_USERS_CONST.'_coupon'] == '1') {
				$coupontg = '<br/><a href="'.APPLICATION_URL.'/'.BACKEND.'/cancel-coupon/'.myurl_encode($row1[$sIndexColumn]).'">Cancel Coupon</a>';
			}
			if($row1[T_USERS_CONST.'_email_verified'] == '0'){
				$row[] =  '<a href="'.APPLICATION_URL.'/'.BACKEND.'/view-account/'.myurl_encode($row1[$sIndexColumn]).'">  View</a>';
			} else {
				$row[] =  '<a href="'.APPLICATION_URL.'/'.BACKEND.'/view-account/'.myurl_encode($row1[$sIndexColumn]).'">  View</a><br><a href="'.APPLICATION_URL.'/'.BACKEND.'/access-account/'.paramValEncode($row1[$sIndexColumn],'user').'" target="_blank">'.$this->layout()->translator->translate("access_acc_txt").'</a><br><a href="'.APPLICATION_URL.'/'.BACKEND.'/chat/'.str_replace("=","",myurl_encode($row1[$sIndexColumn])).'" style="color:#010101">'.$this->layout()->translator->translate("Messaging").$chkcount.'</a>'.$coupontg;
			}
			
 			$output['aaData'][] = $row;
			$j++;
		}	
		
		echo json_encode( $output );
		exit();
 	} 
	
	public function chatAction() {
		$this->layout()->setVariable('backUrl', 'users');
		$user_id = $this->params()->fromRoute('user_id');
		if($user_id == ''){
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		$user_id = myurl_decode($user_id);
		$joinArr = array(
			/*'0'=> array('0'=>'countries','1'=>T_USERS_CONST.'_country=country_id','2'=>'Left','3'=>array('country_name')),*/
		);
		$notify_data["notification_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data,"notification_type = '5' and notification_to = '1'");
		$getUserWhere = T_USERS_CONST."_id=:userpostid and ".T_USERS_CONST."_type IN ('user')";

		$user_info = $this->SuperModel->Super_Get(T_USERS,$getUserWhere,"fetch",array("warray"=>array("userpostid"=>$user_id)),$joinArr);
		if(empty($user_info))
		{
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:TID and chat_to =:UID) or (chat_to =:TID and chat_by =:UID)","fetchAll",array('order'=>'chat_id desc','limit'=>10,'warray'=>array('TID'=>$this->adminuser[T_CLIENT_VAR.'client_id'],'UID'=>$user_id)),$joinArr);
		array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
		$chk_data["chat_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_CHAT,$chk_data,"chat_by = '".$user_id."' and chat_to = '1'");
		$this->layout()->setVariable('pageHeading','Messaging ('.$user_info[T_CLIENT_VAR.'client_name'].')');	
		$view = new ViewModel();
		$view->setVariable('user_info',$user_info);
		$view->setVariable('message_record',$message_record);
		$view->setVariable('adminuser',$this->adminuser);
		$view->setVariable('user_id',$user_id);
		return $view;
	}
	
	public function cancelcouponAction() {
		$user_id = $this->params()->fromRoute('user_id');
		if($user_id == ''){
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		$user_id = myurl_decode($user_id);
		$user_info = $this->SuperModel->Super_Get(T_USERS,T_CLIENT_VAR."client_id =:UID","fetch",array("warray"=>array("UID"=>$user_id)),$joinArr);

		if(empty($user_info))
		{
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		$clt_data[T_CLIENT_VAR."client_coupon"] = '2';
		$clt_data[T_CLIENT_VAR."client_couponcode"] = '';
		$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
		$this->SuperModel->Super_Insert(T_USERS,$clt_data,T_CLIENT_VAR."client_id = '".$user_id."'");
		$this->adminMsgsession['successMsg'] = 'Coupon has been cancelled successfully.';
		
		$data["store_approval"] = '2';
		$data["store_verifydeclinetxt"] = 'Coupon for monthly subscription has been cancelled.';
		$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$user_id."'");
		
		return $this->redirect()->toRoute('admin_users');
	}	
	
 	/* View account detail of particular user */
	public function accountAction()
	{
		$this->layout()->setVariable('backUrl', 'users');
		$user_id = $this->params()->fromRoute('user_id');
		if($user_id == ''){
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		$user_id = myurl_decode($user_id);
		$joinArr = array(
			/*'0'=> array('0'=>'countries','1'=>T_USERS_CONST.'_country=country_id','2'=>'Left','3'=>array('country_name')),*/
		);

		$getUserWhere = T_USERS_CONST."_id=:userpostid and ".T_USERS_CONST."_type IN ('user')";

		$user_info = $this->SuperModel->Super_Get(T_USERS,$getUserWhere,"fetch",array("warray"=>array("userpostid"=>$user_id)),$joinArr);

		if(empty($user_info))
		{
			$this->adminMsgsession['infoMsg']  = "No Such User Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/users');
		}
		
	 	$this->layout()->setVariable('pageHeading','Account Information');	

	  	$this->layout()->setVariable('getutype',$user_info[T_USERS_CONST.'_type']);	

		$view = new ViewModel(array('user_information'=>$user_info,'pageHeading'=>'Account Information -'.$user_info[T_USERS_CONST.'_name']));

		return $view;
	}

	/* Delete Users */
	public function removeuserlistAction()
	{
		$imagePlugin = $this->Image();
		$request = $this->getRequest();

		if ($request->isPost()) {
			$del = $request->getPost(T_USERS);

			foreach($del as $key=>$ids)
			{  	
			 	$UserData = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST.'_id ="'.$ids.'"',"fetch");
				if(!empty($UserData)){
					//unlink old video
					$imagePlugin->universal_unlink($UserData[T_USERS_CONST."_image"],array("directory"=>PROFILE_IMAGES_PATH));
					$imagePlugin->universal_unlink($UserData[T_USERS_CONST."_image"],array("directory"=>PROFILE_IMAGES_PATH.'/60'));
					$imagePlugin->universal_unlink($UserData[T_USERS_CONST."_image"],array("directory"=>PROFILE_IMAGES_PATH.'/160'));
					$imagePlugin->universal_unlink($UserData[T_USERS_CONST."_image"],array("directory"=>PROFILE_IMAGES_PATH.'/thumb'));					
				}
				$this->SuperModel->Super_Delete("yurt90w_clients_photos","cp_client_id = '".$ids."'");
				$isdeleted = $this->SuperModel->Super_Delete(T_USERS,T_USERS_CONST.'_id ="'.$ids.'"');
			} 
		}

		$this->adminMsgsession['successMsg'] = 'Account Deleted Successfully.';
		return $this->redirect()->toRoute('admin_users');
	}


}