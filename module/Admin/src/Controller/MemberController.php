<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Member controller
* * * * * * * * * * * * * * * * * * * * * */
namespace Admin\Controller;

use Admin\Model\AdminTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Model\Email;

class MemberController extends AbstractActionController
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
	
	/* List page to show added members */
	public function indexAction()
    {
		$pageHeading = "Members";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$userId = $this->params()->fromRoute('user_id');

		$view = new ViewModel(array('page_icon'=>'fa fa-users','userId'=>$userId));
		return $view;
    }

    /* Ajax request - Fetch records of added members */
	public function getmemberslistAction()
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
		
		$type = 'member';
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

			$checkClass = "";

			if($row1[T_USERS_CONST.'_status'] == '1'){
				$checkClass = "checked";
			} 

				$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1[T_USERS_CONST.'_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";

			if($row1[T_USERS_CONST.'_email_verified'] == '0'){

				$row[] =  '<a href="'.APPLICATION_URL.'/'.BACKEND.'/view-member-account/'.myurl_encode($row1[$sIndexColumn]).'">  View</a>';

			} else {
				$row[] =  '<a href="'.APPLICATION_URL.'/'.BACKEND.'/view-member-account/'.myurl_encode($row1[$sIndexColumn]).'">  View</a><br><a href="'.APPLICATION_URL.'/'.BACKEND.'/access-account/'.paramValEncode($row1[$sIndexColumn],'user').'" target="_blank">'.$this->layout()->translator->translate("access_acc_txt").'</a>';
			}
			
 			$output['aaData'][] = $row;
			$j++;
		}	
		
		echo json_encode( $output );
		exit();
 	} 

 	/* View account detail of particular member */
	public function memberaccountAction()
	{
		$this->layout()->setVariable('backUrl', 'members');
		$user_id = $this->params()->fromRoute('user_id');
		if($user_id == ''){
			$this->adminMsgsession['infoMsg']  = "No Such Member Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/members');
		}
		$user_id = myurl_decode($user_id);
		$joinArr = array(
			/*'0'=> array('0'=>'countries','1'=>T_USERS_CONST.'_country=country_id','2'=>'Left','3'=>array('country_name')),*/
		);

		$getUserWhere = T_USERS_CONST."_id=:userpostid and ".T_USERS_CONST."_type IN ('member')";
		$user_info = $this->SuperModel->Super_Get(T_USERS,$getUserWhere,"fetch",array("warray"=>array("userpostid"=>$user_id)),$joinArr);

		if(empty($user_info))
		{
			$this->adminMsgsession['infoMsg']  = "No Such Member Exists in the database...!";
			return $this->redirect()->toUrl(APPLICATION_URL.'/'.BACKEND.'/members');
		}

		$getUserPhotoWhere = "cp_client_id=:cpClientId and cp_is_profile_image='2'";
		$userphoto_info = $this->SuperModel->Super_Get(T_CLIENTS_PHOTOS,$getUserPhotoWhere,"fetchAll",array("warray"=>array("cpClientId"=>$user_id)) );

	 	$this->layout()->setVariable('pageHeading','Account Information');	

	  	$this->layout()->setVariable('getutype',$user_info[T_USERS_CONST.'_type']);	

		$view = new ViewModel(array('user_information'=>$user_info,'pageHeading'=>'Account Information -'.$user_info[T_USERS_CONST.'_name'], 'userphoto_info'=>$userphoto_info));

		return $view;
	}

	/* Delete Users */
	public function removememberlistAction()
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

				$isdeleted = $this->SuperModel->Super_Delete(T_USERS,T_USERS_CONST.'_id ="'.$ids.'"');	 
			} 
		}

		$this->adminMsgsession['successMsg'] = 'Account Deleted Successfully.';
		return $this->redirect()->toRoute('admin_members');
	}

}