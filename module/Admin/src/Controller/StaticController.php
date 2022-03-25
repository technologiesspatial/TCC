<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Static controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container;
use Admin\Form\StaticForm;
use Zend\Db\Sql\Expression;
use Admin\Form\MasterForm;
use PaypalPayoutsSDK\Core\PayPalHttpClient;
use PaypalPayoutsSDK\Core\SandboxEnvironment;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use PaypalPayoutsSDK\Payouts\PayoutsGetRequest;

class StaticController extends AbstractActionController
{
    
	private $AbstractModel,$Adapter,$EmailModel;

    public function __construct($AbstractModel,Adapter $Adapter,$adminMsgsession,$EmailModel,$config_data)

    {

        $this->SuperModel = $AbstractModel;

		$this->Adapter = $Adapter;

		$this->EmailModel = $EmailModel;

		$this->adminMsgsession = $adminMsgsession;	

		$this->view = new ViewModel();

		$this->SITE_CONFIG = $config_data;

    }

	

	public function __invoke(ContainerInterface $container, $name, array $options = null)

    {

        $session = $container->get(SessionContainer::class);

        $db = $container->get(DbAdapter::class);

	}
	
	public function completedrefundsAction() {
		$pageHeading="View Completed Requests";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getcompletedrefundsAction() {
		$dbAdapter = $this->Adapter;
	  	$aColumns = array('refund_id','refund_type','refund_amount','refund_description','refund_date','refund_sellerid',T_CLIENT_VAR.'client_name','refund_status');
		$sIndexColumn = 'refund_id';
		$sTable = T_REFUND;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		if($sWhere == ''){
			$sWhere = " where(refund_status = '1')";

		} else {
			$sWhere .= " and(refund_status = '1')";

		}
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (refund_sellerid = yurt90w_client_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] = $j;
			if($row1["refund_type"] == '1') {
				$row[] = 'Full Refund';
			} else {
				$row[] = 'Partial Refund';
			}
			$row[] = "$".$row1["refund_amount"];			
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $row1["refund_date"];
			$row[] = $row1["refund_description"];
			if($row1["refund_status"] == '2') {
				$row[] = '<span class="btn btn-info btn-xs" style="cursor:default;">Pending</span>';
			} else {
				$row[] = '<span class="btn btn-success btn-xs" style="cursor:default;">Approved</span>';
			}
			if($row1["refund_status"] == '2') {
				$row[] = '<a href="javascript:void(0)" class="order-refundbtn" data-id="'.str_replace("=","",base64_encode($row1["refund_id"])).'"><span class="btn btn-black btn-xs">Order Info</span></a>';
			} else {
				$row[] = '<a href="javascript:void(0)" class="order-refundbtn" data-id="'.str_replace("=","",base64_encode($row1["refund_id"])).'"><span class="btn btn-black btn-xs">Order Info</span></a>';
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function refundrequestsAction() {
		$pageHeading="View & Process Refund Requests";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getrefundrequestsAction() {
		$dbAdapter = $this->Adapter;
	  	$aColumns = array('refund_id','refund_type','refund_amount','refund_description','refund_date','refund_sellerid',T_CLIENT_VAR.'client_name','refund_status');
		$sIndexColumn = 'refund_id';
		$sTable = T_REFUND;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		if($sWhere == ''){
			$sWhere = " where(refund_status = '2')";

		} else {
			$sWhere .= " and(refund_status = '2')";

		}
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (refund_sellerid = yurt90w_client_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] = $j;
			if($row1["refund_type"] == '1') {
				$row[] = 'Full Refund';
			} else {
				$row[] = 'Partial Refund';
			}
			$row[] = "$".$row1["refund_amount"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $row1["refund_date"];
			$row[] = $row1["refund_description"];
			if($row1["refund_status"] == '2') {
				$row[] = '<span class="btn btn-info btn-xs" style="cursor:default;">Pending</span>';
			} else {
				$row[] = '<span class="btn btn-success btn-xs" style="cursor:default;">Approved</span>';
			}
			if($row1["refund_status"] == '2') {
				$row[] = '<a href="javascript:void(0)" class="accept-refundbtn" data-id="'.str_replace("=","",base64_encode($row1["refund_id"])).'"><span class="btn btn-success btn-xs">Approve</span></a><br/><a href="javascript:void(0)" class="order-refundbtn" data-id="'.str_replace("=","",base64_encode($row1["refund_id"])).'"><span class="btn btn-black btn-xs">Order Info</span></a>';
			} else {
				$row[] = '';
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function orderinfoAction()  {
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() ) {
			 if($request->isPost()) {
			 	$posted_data = $this->getRequest()->getPost();
				$joinArr = array(
					'0'=> array('0'=>T_PRODORDER,'1'=>'refund_orderid=order_id ','2'=>'Left','3'=>array('order_product','order_clientid','order_color','order_size','order_total','order_shipping','order_qty')),
					'1'=> array('0'=>T_CLIENTS,'1'=>'order_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name')),
					'2'=> array('0'=>T_PRODUCTS,'1'=>'order_product=product_id','2'=>'Left','3'=>array('product_id','product_title','product_photos'))
				); 
				$order_details = $this->SuperModel->Super_Get(T_REFUND,"refund_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["tid"]))),$joinArr);
				$view = new ViewModel();
				$view->setVariable("order_details",$order_details);
				$view->setTerminal(true); 
				return $view; 
			 }
		}
	}
	
	public function approverefundAction() {
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() ) {
			 if($request->isPost()) {
			 	$posted_data = $this->getRequest()->getPost();
				$joinArr = array(
					'0'=> array('0'=>T_CLIENTS,'1'=>'refund_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name','yurt90w_client_email')),
				);
				$refund_record = $this->SuperModel->Super_Get(T_REFUND,"refund_id =:TID and refund_status = '2'","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["tid"]))),$joinArr);
				if(empty($refund_record)) {
					echo "error";
					exit();
				} 
				$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$refund_record["refund_sellerid"]))); 
				$refund_data["refund_status"] = "1";
				$this->SuperModel->Super_Insert(T_REFUND,$refund_data,"refund_id = '".base64_decode($posted_data["tid"])."'");
				
				if(!empty($seller_details[T_CLIENT_VAR.'client_email'])) { 
					$mail_const_data2 = array(
						  "user_name" => $seller_details[T_CLIENT_VAR.'client_name'],
						  "user_email" => $seller_details[T_CLIENT_VAR.'client_email'],
						  "message" => "Admin has paid the refund to the ".$refund_record[T_CLIENT_VAR."client_name"]." and approved your refund request of amount $".$refund_record["refund_amount"].".",
						  "subject" => "Your refund request has been accepted."
					);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				}
				
				if(!empty($refund_record[T_CLIENT_VAR.'client_email'])) { 
					$mail_const_data3 = array(
						  "user_name" => $refund_record[T_CLIENT_VAR.'client_name'],
						  "user_email" => $refund_record[T_CLIENT_VAR.'client_email'],
						  "message" => "Admin has paid the refund to you and approved the refund request from ".$seller_details[T_CLIENT_VAR.'client_name']." of amount $".$refund_record["refund_amount"].".",
						  "subject" => "Amount has been refunded to you."
					);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data3);	 
				}
				echo "success";
				exit(); 
			 }
		}
	}
	
	public function withdrawrequestsAction() {
		$notify_data["notification_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data,"notification_to = '1' and notification_type = '6'");
		$pageHeading="View & Process Payouts";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function exportpayoutsAction() {
		$datetg = $this->params()->fromRoute('tid');
		$expl = explode("~",$datetg);
		$filename = "Payouts:" . str_replace("~", " - ", $datetg);
		$fieldsArr = array(
			"order_serial"=> "Order No.",
			"seller_email"=> "Seller Email",
			"shop_name" => "Shop Name",
			"payment_received" => "Date payment received",
			"total_order"=> "Total Order Amount",
			"coven_fee" => "Collective Coven 8%",
			"status" => "Action",
			"released_date" => "Released Date"
		);
		$where = '';
		if(!empty($expl[0])) {
			$start_datetg = explode("-",$expl[0]);
			$start_date = $start_datetg[0].'-'.$start_datetg[2].'-'.$start_datetg[1].' 00:00:00';
		}
		if(!empty($expl[1])) {
			$end_datetg = explode("-",$expl[1]);
			$end_date = $end_datetg[0].'-'.$end_datetg[2].'-'.$end_datetg[1].' 00:00:00';
		}
		$joinArr = array(
			'0'=> array('0'=>T_CLIENTS,'1'=>'order_sellerid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_email')			
			),
			'1'=> array('0'=>T_STORE,'1'=>'order_sellerid=store_clientid','2'=>'Left','3'=>array('store_name')			
			),
		);
		if(!empty($start_date)) {
			$orders_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_date > '".$start_date."' and order_date <= '".$end_date."'","fetchAll",array('order'=>'order_id desc'),$joinArr);
		} else {
			$orders_data = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array('order'=>'order_id desc'));
		}
		if(empty($orders_data)) {
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("No Orders found for the selected date range.");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/withdrawal-requests');
		}
		$release_date = '';
		foreach ($orders_data as $key => $value) {	
			if(!empty($value["order_date"])) {
				$release_date = date("Y-m-d",strtotime("+14 days", strtotime($value["order_date"]))); 
			}
			if(strtotime(date("Y-m-d")) > strtotime($release_date)) {
				$release_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_date >= '".$release_date."'","fetch");
				if(!empty($release_data["withdrawal_date"])) {
					$status = 'Approved';
				} else {
					$status = 'Pending';
				}
			} else {
				$status = 'Pending';
			}
			if(!empty($release_data["withdrawal_date"])) {
				$released_date = date("m-d-Y",strtotime($release_data["withdrawal_date"]));
			} else {
				$released_date = '';
			}
			$exportArr[$key] = array(
				$fieldsArr['order_serial'] =>  $value["order_serial"],
				$fieldsArr['seller_email'] =>  $value[T_CLIENT_VAR."client_email"],
				$fieldsArr['shop_name'] => $value["store_name"],
				$fieldsArr['payment_received'] => $value["order_date"],
				$fieldsArr['total_order'] =>  "$".$value["order_total"],
				$fieldsArr['coven_fee'] => "$".round($value["order_sitefee"],2),
				$fieldsArr['status'] => $status,
				$fieldsArr['released_date'] => $released_date
			);
		}
		$newSheetName = "Payouts";
		if(!empty($exportArr)){
			$var = exportData($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	
	public function getwithdrawrequestsAction() {
		$dbAdapter = $this->Adapter;
	  	$aColumns = array('order_id',T_CLIENT_VAR.'client_name','store_name','order_date','order_total','order_sitefee','order_serial');
		$sIndexColumn = 'order_id';
		$sTable = T_PRODORDER;
		{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
				}
			}
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}
		}
		$sWhere = "";
		if ( isset($_GET['sSearch']) and $_GET['sSearch'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (order_sellerid = yurt90w_client_id) INNER JOIN '.T_STORE.' ON (order_sellerid = store_clientid)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] = $row1["order_serial"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $row1['store_name'];
			$row[] = $row1['order_date'];
			$row[] = "$".$row1['order_total'];
			$row[] = "$".round($row1['order_sitefee'],2);
			$release_date = '';
			if(!empty($row1['order_date'])) {
				$release_date = date("Y-m-d",strtotime("+14 days", strtotime($row1["order_date"]))); 
			}
			if(strtotime(date("Y-m-d")) > strtotime($release_date)) {
				$release_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_date >= '".$release_date."'","fetch");
				if(!empty($release_data["withdrawal_date"])) {
					$row[] = '<span class="btn btn-success btn-xs" style="cursor:default;">Approved</span><br/>Released Date: '.date("m-d-Y",strtotime($release_data["withdrawal_date"]));
				} else {
					$row[] = '<span class="btn btn-info btn-xs" style="cursor:default;">Pending</span>';
				}
			} else {
				$row[] = '<span class="btn btn-info btn-xs" style="cursor:default;">Pending</span>';
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function couponsAction() {
		$pageHeading="Manage Coupons";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getcouponsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('merchantcoupon_id','merchantcoupon_title','merchantcoupon_code','merchantcoupon_discount');
		$sIndexColumn = 'merchantcoupon_id';
		$sTable = T_MERCHANTCOUPON;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[] = $row1["merchantcoupon_title"];
  			$row[]= $row1['merchantcoupon_code'];
			$row[] = $row1["merchantcoupon_discount"];
			$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/manage-coupon/'.myurl_encode($row1['merchantcoupon_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function managecouponAction() {
		$edit_id = $this->params()->fromRoute('id');
	    $form = new StaticForm($this->layout()->translator);
		$form->coupon();
		$PageHeading=$this->layout()->translator->translate("Update Coupon");
		if($edit_id != ''){
			$edit_id=myurl_decode($edit_id);
			$PageHeading=$this->layout()->translator->translate("Update Coupon");
			$data=$this->SuperModel->Super_Get(T_MERCHANTCOUPON,'merchantcoupon_id=:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/coupons');	
			}else{
				$form->populateValues($data);
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();
				$coupon_check = $this->SuperModel->Super_Get(T_MERCHANTCOUPON,"merchantcoupon_code =:TID and merchantcoupon_id != '".$edit_id."'","fetch",array('warray'=>array('TID'=>trim($Formdata["merchantcoupon_code"]))));
				if(!empty($coupon_check)) {
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("Coupon code already exists.");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/coupons');
				}
				unset($Formdata['bttnsubmit']);
				unset($Formdata['post_csrf']);   
				if(!empty($edit_id)){
					$isInserted=$this->SuperModel->Super_Insert(T_MERCHANTCOUPON,$Formdata,"merchantcoupon_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Coupon has been updated successfully.");
				}else{
					//$isInserted=$this->SuperModel->Super_Insert(T_MERCHANTCOUPON,$Formdata);
					//$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Coupon has been added.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/coupons');	
		} else {
			prd($form->getMessages());
		}
        }
		$this->layout()->setVariable('pageHeading',$PageHeading);
		$this->layout()->setVariable('pageHeading', $PageHeading);
		$this->layout()->setVariable('pageDescription', $PageHeading);
		$this->layout()->setVariable('backUrl', 'coupons');
		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading));
		$view->setTemplate('admin/admin/add.phtml');
		return $view;
	}
	
	public function sellerapplicationsAction() {
		$notify_data["notification_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data,"notification_to = '1' and notification_type = '1'");
		$pageHeading="View Seller Applications";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function verificationbadgesAction() {
		$notify_data["notification_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data,"notification_to = '1' and notification_type = '2'");
		$pageHeading="View Verification Badge Requests";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function viewratingAction() {
		$pageHeading = "Manage Review Rating";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getallratingAction() {
	  $dbAdapter = $this->Adapter;
	  $aColumns = array('review_id','review_from','review_prodid','review_to','review_starrating','review_text','review_date',T_CLIENT_VAR.'client_name','product_id','product_title','review_photos');
		$sIndexColumn = 'review_id';
		$sTable = T_REVIEWS;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (review_from = yurt90w_client_id) INNER JOIN '.T_PRODUCTS.' ON (review_prodid = product_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$seller_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name'),'warray'=>array('UID'=>$row1["review_to"])));
			$row=array();
 			$row[] = $j;
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $row1["product_title"];
			$row[] = $seller_data[T_CLIENT_VAR.'client_name'];
			$row[] = '<div class="star-panel" data-rating="'.$row1["review_starrating"].'"></div>';
			$row[] = nl2br($row1["review_text"]);
			$photos = [];
			$review_photos = unserialize($row1["review_photos"]);
			if(!empty($review_photos)) {
				foreach($review_photos as $review_photos_key => $review_photos_val) {
					$photos[] = '<img src="'.HTTP_REVIEW_PATH.'/200/'.$review_photos_key.'" style="width:100px;height:100px;" >';
				}
			}
			if(!empty($photos)) {
				$row[] = implode("&nbsp;",$photos);
			} else {
				$row[] = '-';
			}
			$row[] = $row1["review_date"];
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function viewordersAction() {
		$pageHeading = "View Orders";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status != 5","fetch",array('fields'=>array('total' =>new Expression('SUM(order_sitefee)'))));
		return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading,'total_fee'=>$all_orders["total"]));
	}
	
	public function getallordersAction() {
		$dbAdapter = $this->Adapter;
	    $aColumns = array('order_serial','order_id','order_product','order_total','order_sitefee','order_date','order_sellerid','order_clientid','order_status',T_CLIENT_VAR.'client_name','product_id','product_title','order_shipping','refund_type','refund_amount','refund_status','order_tracking');
		$sIndexColumn = 'order_id';
		$sTable = T_PRODORDER;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (order_clientid = yurt90w_client_id) INNER JOIN '.T_PRODUCTS.' ON (order_product = product_id) LEFT JOIN '.T_REFUND.' ON (refund_orderid = order_id)';
		if($sWhere == ''){
			$sWhere = " where (order_status != 5)";
		} else {
			$sWhere .= " and (order_status != 5)";
		}
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$seller_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name'),'warray'=>array('UID'=>$row1["order_sellerid"])));
			$url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';   
			$order_tracking = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $row1["order_tracking"]);
			$row=array();
 			$row[] = $row1["order_serial"];
			$row[] = $row1["product_title"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $seller_data[T_CLIENT_VAR.'client_name'];
			$row[] = "$".bcdiv($row1["order_total"],1,2);
			$row[] = $row1["order_date"];
			$row[] = "$".bcdiv($row1["order_sitefee"],1,2);
			$seller_share = round($row1["order_total"] - $row1["order_sitefee"],2);
			$row[] = "$".$seller_share;
			$row[] = "$".bcdiv($row1["order_shipping"],1,2);
			if(!empty($row1["refund_type"])) {
				if($row1["refund_type"] == '1') {
					$type = 'Full Refund';	
				} else {
					$type = 'Partial Refund';
				}
				if($row1["refund_status"] == '1') {
					$refstatus = 'Approved';
				} else {
					$refstatus = 'Pending';
				}
				$row[] = "$".$row1["refund_amount"]." (".$type.")"." Status: ".$refstatus;
			} else {
				$row[] = '-';
			}
			if(!empty($order_tracking) && ($row1["order_status"] == '3' || $row1["order_status"] == '4')) {
				$row[] = '<p class="track-para">'.$order_tracking.'</p>';
			} else {
				$row[] = '-';
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function reviewreportsAction() {
		$pageHeading="Reported Reviews";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getreportedreviewsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('review_report_id','review_text','review_report_rateid','review_report_date','review_report_uid','review_report_text',T_CLIENT_VAR.'client_name','product_id','product_title');
		$sIndexColumn = 'review_report_id';
		$sTable = T_REVREPORT;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (review_report_uid = yurt90w_client_id) INNER JOIN '.T_REVIEWS.' ON (review_report_rateid = review_id) INNER JOIN '.T_PRODUCTS.' ON (review_prodid = product_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[] = nl2br($row1["review_text"]);
			$row[] = $row1["product_title"];
			$row[] = $row1["review_report_date"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = nl2br($row1["review_report_text"]);
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function commentreportsAction() {
		$pageHeading="Reported Comments";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getreportedcommentsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('comment_report_id','procomment_text','comment_report_cid','comment_report_date','comment_report_uid','comment_report_text',T_CLIENT_VAR.'client_name','product_id','product_title');
		$sIndexColumn = 'comment_report_id';
		$sTable = T_CMTREPORT;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (comment_report_uid = yurt90w_client_id) INNER JOIN '.T_PRODCOMMENT.' ON (comment_report_cid = procomment_id) INNER JOIN '.T_PRODUCTS.' ON (procomment_pid = product_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[] = nl2br($row1["procomment_text"]);
			$row[] = $row1["product_title"];
			$row[] = $row1["comment_report_date"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = nl2br($row1["comment_report_text"]);
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function getbadgerequestsAction() {
		
		$dbAdapter = $this->Adapter;
		$aColumns = array('store_id',T_CLIENT_VAR.'client_name','store_doc1','store_doc2','store_doc3','store_verification',T_CLIENT_VAR.'client_id');
		$sIndexColumn = 'store_id';

		$sTable = T_STORE;
		/*Table Setting*/{
		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}
		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "".$aColumns[$i]." LIKE '%".trim(addslashes($_GET["sSearch"]))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";

				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (store_clientid = yurt90w_client_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sJoin $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];
		/*

		 * Output

		 */

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<a style="text-decoration:none" target="_blank" href="'.APPLICATION_URL.'/'.BACKEND.'/view-account/'.myurl_encode($row1[T_CLIENT_VAR.'client_id']).'">'.$row1[T_CLIENT_VAR.'client_name'].'</a>';
  			$row[] = '<a href="'.HTTP_STORE_DOC_PATH.'/'.$row1["store_doc1"].'" download>'.$row1["store_doc1"].'</a>';
			$row[] = '<a href="'.HTTP_STORE_DOC_PATH.'/'.$row1["store_doc2"].'" download>'.$row1["store_doc2"].'</a>';
			//$row[] = '<a href="'.HTTP_STORE_DOC_PATH.'/'.$row1["store_doc3"].'" download>'.$row1["store_doc3"].'</a>';
			if($row1["store_verification"] == '3') {
				$row[] = '<span class="btn btn-info btn-xs" style="cursor:default;">Pending</span>';
			} else if($row1["store_verification"] == '1') {
				$row[] = '<span class="btn btn-success btn-xs" style="cursor:default;">Accepted</span>';
			} else {
				$row[] = '<span class="btn btn-danger btn-xs" style="cursor:default;">Declined</span>';
			}
			if($row1["store_verification"] == '3') {
				$row[] ='<a href="javascript:void(0)" class="accept-verifybtn btn btn-xs btn-success" data-id="'.$row1["store_id"].'">Accept</a> <br/> <a href="javascript:void(0)" class="decline-verifybtn btn btn-xs btn-danger" data-id="'.$row1["store_id"].'">Decline</a>';	
			} else {
				$row[] = '';
			}
  			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function declinebadgerequestAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["id"] = strip_tags($posted_data["id"]);
			if(empty($posted_data["id"])) {
				echo "error";
				exit();
			} else {
				$badge_request = $this->SuperModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$posted_data["id"])));
				$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id=:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$badge_request["store_clientid"])));
				if(empty($badge_request)) {
					echo "error";
					exit();
				}
				$store_data["store_verification"] = '2';
				$store_data["store_badgedeclinetxt"] = strip_tags($posted_data["decltxt"]);
				$this->SuperModel->Super_Insert(T_STORE,$store_data,"store_id = '".$posted_data["id"]."'");
				
				$mail_const_data2 = array(
							"user_name" => $client_details[T_CLIENT_VAR.'client_name'],
							"user_email" => $client_details[T_CLIENT_VAR.'client_email'],
							"message" => "Admin has declined your badge verification request with reason ".strip_tags($posted_data["decltxt"]).".",
							"subject" => "Badge verification"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
				
				$notify_data["notification_type"] = '2';
				$notify_data["notification_by"] = '1';
				$notify_data["notification_to"] = $badge_request["store_clientid"];
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_subscriberid"] = $badge_request["store_id"];
				$notify_data["notification_status"] = '0';
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				
				echo "success";
				exit();
			}
		}
	}
	
	public function acceptbadgerequestAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["id"] = strip_tags($posted_data["id"]);
			if(empty($posted_data["id"])) {
				echo "error";
				exit();
			} else {
				$badge_request = $this->SuperModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$posted_data["id"])));
				$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id=:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$badge_request["store_clientid"])));
				if(empty($badge_request)) {
					echo "error";
					exit();
				}
				$mail_const_data2 = array(
							"user_name" => $client_details[T_CLIENT_VAR.'client_name'],
							"user_email" => $client_details[T_CLIENT_VAR.'client_email'],
							"message" => "Admin has accepted your badge verification request.",
							"subject" => "Badge verification"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
				$store_data["store_verification"] = '1';
				$this->SuperModel->Super_Insert(T_STORE,$store_data,"store_id = '".$posted_data["id"]."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function declinestorerequestAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["id"] = strip_tags($posted_data["id"]);
			if(empty($posted_data["id"])) {
				echo "error";
				exit();
			} else {
				$store_request = $this->SuperModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$posted_data["id"])));
				$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id=:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$store_request["store_clientid"])));
				if(empty($store_request)) {
					echo "error";
					exit();
				}
				$store_data["store_approval"] = '2';
				$store_data["store_verifydeclinetxt"] = strip_tags($posted_data["decltxt"]);
				$this->SuperModel->Super_Insert(T_STORE,$store_data,"store_id = '".$posted_data["id"]."'");
				
				$mail_const_data2 = array(
							"user_name" => $client_details[T_CLIENT_VAR.'client_name'],
							"user_email" => $client_details[T_CLIENT_VAR.'client_email'],
							"message" => "Admin has declined your seller application request with reason ".strip_tags($posted_data["decltxt"]).".",
							"subject" => "Seller application"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
				
				$notify_data["notification_type"] = '1';
				$notify_data["notification_by"] = '1';
				$notify_data["notification_to"] = $store_request["store_clientid"];
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_subscriberid"] = $store_request["store_id"];
				$notify_data["notification_status"] = '0';
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				echo "success";
				exit();
			}
		}
	}
	
	public function acceptstorerequestAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["id"] = strip_tags($posted_data["id"]);
			if(empty($posted_data["id"])) {
				echo "error";
				exit();
			} else {
				$store_request = $this->SuperModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$posted_data["id"])));
				$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id=:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$store_request["store_clientid"])));
				if(empty($store_request)) {
					echo "error";
					exit();
				}
				$store_data["store_approval"] = '1';
				$this->SuperModel->Super_Insert(T_STORE,$store_data,"store_id = '".$posted_data["id"]."'");
				
				$clt_data[T_CLIENT_VAR."client_nextbilling"] = date('Y-m-d', strtotime("+1 month"));
				$clt_data[T_CLIENT_VAR."client_stripe_id"] = '1';
				$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$store_request["store_clientid"]."'");
				
				$mail_const_data2 = array(
							"user_name" => $client_details[T_CLIENT_VAR.'client_name'],
							"user_email" => $client_details[T_CLIENT_VAR.'client_email'],
							"message" => "Admin has approved your seller application request.",
							"subject" => "Seller application"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
				
				echo "success";
				exit();
			}
		}
	}
	
	public function getsellerrequestsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('store_id','store_name','store_company','store_contact',T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_id','store_approval','store_favorite','store_clientid',T_CLIENT_VAR.'client_bestseller');
		$sIndexColumn = 'store_id';

		$sTable = T_STORE;
		/*Table Setting*/{
		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}
		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "".$aColumns[$i]." LIKE '%".trim(addslashes($_GET["sSearch"]))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";

				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}
		}/* End Table Setting */
		
		if($sWhere == ''){
			$sWhere = " where (store_name != '')";

		} else {
			$sWhere .= " and (store_name != '')";

		}
		
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (store_clientid = yurt90w_client_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sJoin $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];
		/*

		 * Output

		 */

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<a style="text-decoration:none" target="_blank" href="'.APPLICATION_URL.'/'.BACKEND.'/view-account/'.myurl_encode($row1[T_CLIENT_VAR.'client_id']).'">'.$row1[T_CLIENT_VAR.'client_name'].'</a>';
  			$row[]=$row1['store_name'];
			$row[]=$row1['store_company'];
			$row[]=$row1['store_contact'];
			if($row1["store_approval"] == '2') {
				$row[] = '<span class="store-stat btn btn-danger btn-xs" data-id="'.$row1['store_id'].'" style="cursor:default;">Declined</span>';
			} else if($row1["store_approval"] == '4' || $row1["store_approval"] == '' || $row1["store_approval"] == '3') {
				$row[] = '<span class="store-stat btn btn-info btn-xs" data-id="'.$row1['store_id'].'" style="cursor:default;">Pending</span>';
			} else if($row1["store_approval"] == '1') {
				$row[] = '<span class="store-stat btn btn-success btn-xs" data-id="'.$row1['store_id'].'" style="cursor:default;">Approved</span>';
			} else {
				$row[] = '<span class="store-stat btn btn-info btn-xs" data-id="'.$row1['store_id'].'" style="cursor:default;">Pending</span>';
			}
			$checkClass = '';
			if($row1['store_approval'] == '1'){
				$checkClass = "checked";
			} 
			$favClass = '';
			if($row1['store_favorite'] == '1'){
				$favClass = "checked";
			}	
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['store_approval']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalstoreStatus(this);' />";
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['store_favorite']."' ".$favClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalfavStatus(this);' />";
			$bestClass = "";
			if($row1[T_CLIENT_VAR."client_bestseller"] == '1') {
				$bestClass = "checked";
			}
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1[T_CLIENT_VAR."client_bestseller"]."' ".$bestClass." id='".T_CLIENTS."-".$row1["store_clientid"]."' onChange='globalbestStatus(this);'>";
			if($row1["store_approval"] == '4' || $row1["store_approval"] == '' || $row1["store_approval"] == '3') {
				$row[] ='<a href="'.ADMIN_APPLICATION_URL.'/view-seller/'.myurl_encode($row1['store_id']).'" class="btn btn-xs btn-info">View</a> <br/> <a href="javascript:void(0)" class="approve-applbtn btn btn-xs btn-success" data-id="'.$row1["store_id"].'">Approve</a> <br/> <a href="javascript:void(0)" class="decline-applbtn btn btn-xs btn-danger" data-id="'.$row1["store_id"].'">Decline</a><br/> <a href="'.APPLICATION_URL.'/'.BACKEND.'/access-account/'.paramValEncode($row1["store_clientid"],'user').'" class="btn btn-xs btn-success" target="_blank">Access Account</a>';
			} else {
				$row[] ='<a href="'.ADMIN_APPLICATION_URL.'/view-seller/'.myurl_encode($row1['store_id']).'" class="btn btn-xs btn-info">View</a><br/><a href="'.APPLICATION_URL.'/'.BACKEND.'/access-account/'.paramValEncode($row1["store_clientid"],'user').'" class="btn btn-xs btn-success" target="_blank">Access Account</a>';
			}
  			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );

		exit();

 	}
	
	public function viewsellerAction() {
		$seller_id = $this->params()->fromRoute('seller');
		$joinArr = array(
			'0'=> array('0'=>T_CLIENTS,'1'=>'store_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name','yurt90w_client_paypal_email')),
		);
		$seller_data = $this->SuperModel->Super_Get(T_STORE,"store_id =:SID","fetch",array('warray'=>array('SID'=>myurl_decode($seller_id))),$joinArr);
		$store_members = $this->SuperModel->Super_Get(T_STORE_MEMBERS,"member_storeid =:TID","fetchAll",array('warray'=>array('TID'=>$seller_data["store_id"])));
		$pageHeading="View Seller Applications";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('backUrl', 'seller-applications');
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading,'seller_data'=>$seller_data));
	}
	
	public function exportsellerAction() {
		$fieldsArr = array(
			"full_name"=> "Full Name",
			"store_name"=> "Store Name",
			"company_name" => "Company Legal Name",
			"contact_person"=> "Contact Person",
			"location"=> "Location",
			"paypal_email" => "Paypal Email"
		);
		$where = '';
		$joinArr = array(
			'0'=> array('0'=>T_CLIENTS,'1'=>'store_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name','yurt90w_client_paypal_email')),
		);
		$seller_data = $this->SuperModel->Super_Get(T_STORE,T_CLIENT_VAR."client_name != ''","fetchAll",array('warray'=>array('SID'=>myurl_decode($seller_id))),$joinArr);
		foreach ($seller_data as $key => $value) {	
			$exportArr[$key] = array(
				$fieldsArr['full_name'] =>  $value[T_CLIENT_VAR."client_name"],
				$fieldsArr['store_name'] =>  $value["store_name"],
				$fieldsArr['company_name'] => $value["store_company"],
				$fieldsArr['contact_person'] =>  $value["store_contact"],
				$fieldsArr['location'] => $value["store_location"],
				$fieldsArr['paypal_email'] => $value[T_CLIENT_VAR.'client_paypal_email']
			);
		}
		$newSheetName = "Stores Data";
		if(!empty($exportArr)){
			$var = exportData($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	
	/* Team module { */

	public function teamlistAction()

    {

		$pageHeading="Team Members";

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

		

	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));

		

    }

	public function getteamlistAction(){

  			

		$dbAdapter = $this->Adapter;

  

		$aColumns = array('team_member_id','team_member_name','team_member_desc','team_member_image');



		$sIndexColumn = 'team_member_id';

		$sTable = T_TEAM_LIST;

  		

		/*Table Setting*/{

		

		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "".$aColumns[$i]." LIKE '%".trim(addslashes($_GET["sSearch"]))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		

		}/* End Table Setting */

		

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/*

		 * Output

		 */

		 

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

		

 			$row[] =$j;

			

			$row[]='<input class="elem_ids checkboxes"  type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'">';

			

  			$row[]=$row1['team_member_name'];

			

			$row[]=nl2br($row1['team_member_desc']);

			$row[] ='<a href="'.ADMIN_APPLICATION_URL.'/manage-team/'.myurl_encode($row1['team_member_id']).'">

			<span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

							

  			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

 	} 

	public function addteamAction(){

		$pageHeading="Add Team";

		$this->layout()->setVariable('backUrl', 'team');

		$team_member_id = $this->params()->fromRoute("member");

		$imagePlugin = $this->Image();

		$form = new StaticForm($this->layout()->translator);

		$form->team();

		$TeamData=array();

		if($team_member_id!=''){$pageHeading="Edit Team";

			$team_member_id=myurl_decode($team_member_id);

			$TeamData = $this->SuperModel->Super_Get(T_TEAM_LIST,"team_member_id=:member_id","fetch",array("warray"=>array("member_id"=>$team_member_id)));

			if(empty($TeamData)){

				$this->adminMsgsession['errorMsg']='Invalid Record';

				return $this->redirect()->toRoute('admin_team');

			}

			

			$form->populateValues(removePrefixFromFieldValue($TeamData,"team_"));

			$form->get("member_image")->setAttribute("class","blockelement");

			$form->getInputFilter()->get("member_image")->setRequired(FALSE)->setAllowEmpty(TRUE);

		}

		

		$request = $this->getRequest();

		

		 if($request->isPost()) {

            $form->setData(array_merge($request->getPost()->toArray(),$request->getFiles()->toArray()));

            if($form->isValid()){

                $Formdata = $form->getData();

				unset($Formdata['bttnsubmit'],$Formdata['post_csrf']);

				$Formdata=GetFormElementsName($Formdata,"team_");

				

				$files =  $request->getFiles()->toArray();	

	

					if(!empty($files)){

						

					

						$is_uploaded=$imagePlugin->universal_upload(array("directory"=>TEAM_IMAGES_PATH,"files_array"=>$files,"thumb"=>false));	

						

						if($is_uploaded->success){

							$Formdata['team_member_image']=$is_uploaded->media_path;

						}

						if($is_uploaded->error){

							$this->adminMsgsession['errorMsg']=$is_uploaded->message;

							return $this->redirect()->toRoute('admin_team');

						}

					}

					

						

				if($team_member_id!=''){

					if($Formdata['team_member_image']==''){$Formdata['team_member_image'] = $TeamData['team_member_image'];}

					else{

						/* image is uploaded remove existing image */

					$imagePlugin->universal_unlink($TeamData['team_member_image'],array("directory"=>TEAM_IMAGES_PATH));

					$imagePlugin->universal_unlink($TeamData['team_member_image'],array("directory"=>TEAM_IMAGES_PATH.'/60'));

					$imagePlugin->universal_unlink($TeamData['team_member_image'],array("directory"=>TEAM_IMAGES_PATH.'/160'));

					$imagePlugin->universal_unlink($TeamData['team_member_image'],array("directory"=>TEAM_IMAGES_PATH.'/thumb'));

					}

					$isInserted=$this->SuperModel->Super_Insert(T_TEAM_LIST,$Formdata,"team_member_id='".$team_member_id."'");

				}else{

					

					$isInserted=$this->SuperModel->Super_Insert(T_TEAM_LIST,$Formdata);

				}

				

				if(!empty($isInserted)){

					$this->adminMsgsession['successMsg']="Member added Successfully.";

					

					return $this->redirect()->toRoute('admin_team');

				}else{

					$this->adminMsgsession['errorMsg']='Member could not be added, please try again.';

				}

            }else{

				$this->adminMsgsession['errorMsg']='Please check entered information again.';

			}

        }

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

       	$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-plus','pageHeading'=>'Add Team Member','TeamData'=>$TeamData,'team_member_id'=>$team_member_id));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	}

	public function removeteamlistAction(){

		

		$request = $this->getRequest();

		$imagePlugin = $this->Image();	

		if ($request->isPost()) {

			 $del = $request->getPost(T_TEAM_LIST);

			 

			 foreach($del as $key=>$ids)

			 {  

			 	$isgetData=$this->SuperModel->Super_Get(T_TEAM_LIST,'team_member_id ="'.$ids.'"');	

				

				if($isgetData['team_member_image']!=''){

					$imagePlugin->universal_unlink($isgetData["team_member_image"],array("directory"=>TEAM_IMAGES_PATH));

					$imagePlugin->universal_unlink($isgetData['team_member_image'],array("directory"=>TEAM_IMAGES_PATH.'/60'));

					$imagePlugin->universal_unlink($isgetData["team_member_image"],array("directory"=>TEAM_IMAGES_PATH.'/160'));

					$imagePlugin->universal_unlink($isgetData["team_member_image"],array("directory"=>TEAM_IMAGES_PATH.'/thumb'));

				}

				

				$isdeleted=$this->SuperModel->Super_Delete(T_TEAM_LIST,'team_member_id ="'.$ids.'"');	 

			 } 

		}

		$this->adminMsgsession['successMsg']='Member Deleted Successfully.';

		return $this->redirect()->toRoute('admin_team');

		

	}

	/* }*/
	
	/* Newsletter module */
	
	public function subscribersAction() {
		$pageHeading = "Newsletter Subscribers";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	/* Blog module */
	
	public function blogsAction() {
		$pageHeading = "Manage Blogs";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function blogcommentsAction() {
		$pageHeading = "Manage Blog Comments";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));
	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getblogcommentsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('comment_id',T_CLIENT_VAR.'client_name','comment_text','blog_title','comment_clientid','comment_date','comment_blogid','comment_status');
		$sIndexColumn = 'comment_id';
		$sTable = T_BLOG_COMMENT;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}

		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (comment_clientid = yurt90w_client_id) INNER JOIN '.T_BLOG.' ON (comment_blogid = blog_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin  $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[]=$row1['blog_title'];
			$row[] = $row1["comment_text"]; 			
			
			$checkClass = "";

			if($row1['comment_status'] == '1'){
				$checkClass = "checked";
			} 
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['comment_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";
  			$row[]=date('d M, Y h:i a',strtotime($row1['comment_date']));
			$output['aaData'][] = $row;			
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function getblogsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('blog_id','blog_catid','blog_title','blog_text','blog_pic','blog_status','blog_date');
		$sIndexColumn = 'blog_id';
		$sTable = T_BLOG;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}

		}/* End Table Setting */
		
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
  			$row[]=$row1['blog_title'];
			$category_data = $this->SuperModel->Super_Get(T_BLOG_CATEGORY,"1","fetchAll");
			$blog_categories = array();
			if(!empty($category_data)) {
				foreach($category_data as $category_data_key => $category_data_val) {
					$blog_categories[$category_data_val["blog_category_id"]] = $category_data_val["blog_category_title"];
				}
			}
			if(array_key_exists($row1["blog_catid"],$blog_categories)) { $row[] = $blog_categories[$row1["blog_catid"]];}
			if(!empty($row1["blog_pic"])) {
				$row[] = '<img src="'.HTTP_BLOG_IMAGES_PATH.'/160/'.$row1["blog_pic"].'" class="img-responsive" />';
			} else {
				$row[] = '';
			}
			$checkClass = "";

			if($row1['blog_status'] == '1'){
				$checkClass = "checked";
			} 

				$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['blog_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";
			$row[]=date('d M, Y h:i a',strtotime($row1['blog_date']));
			$row[] = $row1["blog_text"];
			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-blog/'.myurl_encode($row1['blog_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
  			$output['aaData'][] = $row;			
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function viewproductAction() {
		$this->layout()->setVariable('backUrl', 'products');
		$product_id = $this->params()->fromRoute('id');
		if(empty($product_id)) {
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("No such product found.");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/products');
		}
		$joinArr = array(
			'0'=> array('0'=>T_CLIENTS,'1'=>'product_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name')),
			'1'=> array('0'=>T_CATEGORY_LIST,'1'=>'product_category=category_id','2'=>'Left','3'=>array('category_feild')),
			'2'=> array('0'=>T_SUBCATEGORY_LIST,'1'=>'product_subcategory=subcategory_id','2'=>'Left','3'=>array('subcategory_title')),
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($product_id))),$joinArr);
		if(empty($product_data)) {
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("No such product found.");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/products');
		}
		$this->layout()->setVariable('pageHeading','Product Information');		
		$view = new ViewModel(array('product_info'=>$product_data,'pageHeading'=>'Product Information'));

		return $view;
	}
	
	public function addblogAction() {
		$this->layout()->setVariable('backUrl', 'blogs');
		$edit_id = $this->params()->fromRoute('id');
		$blog_categories = $this->SuperModel->Super_Get(T_BLOG_CATEGORY,"1","fetchAll");
		$blog_catarr = array(); $data = array();
		if(empty($blog_categories)) {
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("Please add blog category to proceed.");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');	
		} else {
			foreach($blog_categories as $blog_categories_key => $blog_categories_val) {
				$blog_catarr[$blog_categories_val["blog_category_id"]] = $blog_categories_val["blog_category_title"];
			}
		}
	    $form = new StaticForm($this->layout()->translator);
		if($edit_id != ''){
			$form->blog($blog_catarr,1);
		} else {
			$form->blog($blog_catarr);
		}
		$PageHeading=$this->layout()->translator->translate("blog_category_title");
		if($edit_id != ''){
			$edit_id=myurl_decode($edit_id);
			$PageHeading=$this->layout()->translator->translate("Edit Blog");
			$data=$this->SuperModel->Super_Get(T_BLOG,'blog_id=:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');	
			}else{
				$form->populateValues($data);
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();
				$files =  $request->getFiles()->toArray();	
				if(!empty($_FILES['blog_image']['name']) && $_FILES['blog_image']['size'] > 10485760) {
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("Please upload image of size upto 10MB.");
					if(!empty($edit_id)){
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog/'.myurl_encode($edit_id));	
					} else {
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog');
					}
				}
				if(!empty($_FILES['blog_image']['name'])) {
					if (strpos(file_get_contents($_FILES['blog_image']['tmp_name']), '<?php') !== false) 
					{
						$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
						if(!empty($edit_id)){
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog/'.myurl_encode($edit_id));	
					} else {
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog');
					}
					}

					if (strpos(file_get_contents($_FILES['blog_image']['tmp_name']), '<?=') !== false) 
					{
						$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
						if(!empty($edit_id)){
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog/'.myurl_encode($edit_id));	
					} else {
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog');
					}
					}

					if (strpos(file_get_contents($_FILES['blog_image']['tmp_name']), '<? ') !== false) 
					{
						$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
						if(!empty($edit_id)){
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog/'.myurl_encode($edit_id));	
					} else {
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-blog');
					}
					}
				}
				$imagePlugin = $this->Image();
				$is_uploaded=$imagePlugin->universal_upload(array("directory"=>BLOG_IMAGES_PATH,"files_array"=>$files,"multiple"=>"0",'thumbs'=>array(
					 'thumb'=>array(
						"width"=>260,
						"height"=>260,
					  ),
					 '160'=>array(
					   "width"=>160,
					   "height"=>160,
				),),));
				$postData["blog_catid"] = $Formdata["blog_catid"]; 
				$postData["blog_title"] = $Formdata["blog_title"];
				$postData["blog_text"] = xss_clean($Formdata["blog_text"]);
				$postData['blog_date'] = date('Y-m-d H:i:s');
				unset($Formdata['bttnsubmit']);
				if(!empty($edit_id)){
					if(!empty($is_uploaded->media_path)) {
						$blog_pic = $is_uploaded->media_path;
					} else {
						$blog_pic = $data["blog_pic"];
					}
					$postData['blog_pic'] = $blog_pic;
					$isInserted=$this->SuperModel->Super_Insert(T_BLOG,$postData,"blog_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog has been updated.");
				}else{
					if(!empty($is_uploaded->media_path)) {
						$blog_pic = $is_uploaded->media_path;
					}
					$postData['blog_pic'] = $blog_pic;
					$isInserted=$this->SuperModel->Super_Insert(T_BLOG,$postData);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog has been added.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blogs');	
		} else {
			prd($form->getMessages());
		}
        }
		$this->layout()->setVariable('pageHeading',$PageHeading);
		$this->layout()->setVariable('pageHeading', $PageHeading);
		$this->layout()->setVariable('pageDescription', $PageHeading);
		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading,'blog_data'=>$data));
		$view->setTemplate('admin/admin/add.phtml');
		return $view;
	}
	
	public function removeblogAction() {
		$request = $this->getRequest();
		if ($request->isPost()) {
			 $del = $request->getPost(T_BLOG);
			 foreach($del as $key=>$ids)
			 {  
				$isdeleted=$this->SuperModel->Super_Delete(T_BLOG,'blog_id ="'.$ids.'"');	 
			 } 
		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog deleted successfully.");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blogs');
	}
	
	public function removeratingAction() {
		$request = $this->getRequest();
		if($request->isPost()) {
			$del = $request->getPost(T_REVIEWS);
		   foreach($del as $key=>$ids)
		   {  
			  $isdeleted=$this->SuperModel->Super_Delete(T_REVIEWS,'review_id ="'.$ids.'"');	 
		   } 
		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Review deleted successfully.");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/review-rating');
	}
	
	public function removeproductAction() {
		$request = $this->getRequest();
		if($request->isPost()) {
		   $del = $request->getPost(T_PRODUCTS);
		   foreach($del as $key=>$ids)
		   {  
			  $isdeleted=$this->SuperModel->Super_Delete(T_PRODUCTS,'product_id ="'.$ids.'"');	 
		   } 
		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Product deleted successfully.");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/products');
	}
	
	public function removecommentAction() {
		$request = $this->getRequest();
		if ($request->isPost()) {
			 $del = $request->getPost(T_BLOG_COMMENT);
			 foreach($del as $key=>$ids)
			 {  
				$isdeleted=$this->SuperModel->Super_Delete(T_BLOG_COMMENT,'comment_id ="'.$ids.'"');	 
			 } 
		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog comment deleted successfully.");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-comments');
	}
	
	/* Blog categories module */
	
	public function blogcategoryAction() {
		$pageHeading = "Manage Blog Categories";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function photogalleryAction() {
		$pageHeading = "Manage Photo Gallery";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function getphotogalleryAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('photogallery_id','photogallery_image');
		$sIndexColumn = 'photogallery_id';
		$sTable = T_PHOTO_GALLERY;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}

		}/* End Table Setting */
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
			if(!empty($row1["photogallery_image"])) {
				$row[] = '<img src="'.HTTP_SLIDER_IMAGES_PATH.'/'.$row1['photogallery_image'].'" style="max-width:240px;">';
			} else {
				$row[] = '';
			}
			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-photo-gallery/'.myurl_encode($row1['photogallery_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
  			$output['aaData'][] = $row;			
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function managephotogalleryAction() {
		$this->layout()->setVariable('backUrl', 'photo-gallery');
		$edit_id = $this->params()->fromRoute('id');
	    $form = new StaticForm($this->layout()->translator);
		$form->photogallery($edit_id);
		
		$PageHeading=$this->layout()->translator->translate("Add Photo Gallery");

		if($edit_id != ''){

			$edit_id=myurl_decode($edit_id);
			
			$PageHeading=$this->layout()->translator->translate("Edit Photo Gallery");
			$data=$this->SuperModel->Super_Get(T_PHOTO_GALLERY,'photogallery_id =:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photo-gallery');	
			}else{
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();    
				$imagePlugin = $this->Image();		

				$files =  $this->getRequest()->getFiles()->toArray();

				$newName="";

				$invalidimage=0; 
				if($files['gallery_image']["tmp_name"]!=''){
					if (strpos(file_get_contents($files['gallery_image']['tmp_name']), '<?php') !== false) 
					{
						$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photo-gallery');
					}
					if($files['gallery_image']['tmp_name']!=''){

						$finfo = finfo_open(FILEINFO_MIME_TYPE); 

						$uploaded_image_extension = getFileExtension($files['gallery_image']['name']);

						$typeval=finfo_file($finfo, $files['gallery_image']['tmp_name']);

						finfo_close($finfo);

						if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png'))){
							$this->adminMsgsession['errorMsg']='Please upload valid image.';
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photo-gallery');	
						}
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>SLIDER_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>0,'thumbs'=>array(

							'160'=>array(

								 "width"=>160,

								 "height"=>160,

							  ),

							'300'=>array(

								 "width"=>300,

								 "height"=>300,

							  ),					  

						)));

						if($is_uploaded_icon->success=="1" && $is_uploaded_icon->media_path!=''){

							$newName=$is_uploaded_icon->media_path;
							$postData["photogallery_image"] = $newName;
						}
					}
				} else {
					$postData["photogallery_image"] = $data["photogallery_image"];
				} 
				unset($Formdata['bttnsubmit']);
				if(!empty($edit_id)){
					$isInserted=$this->SuperModel->Super_Insert(T_PHOTO_GALLERY,$postData,"photogallery_id ='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Photo has been updated.");
				}else{
					$isInserted=$this->SuperModel->Super_Insert(T_PHOTO_GALLERY,$postData);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Photo has been added to gallery.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photo-gallery');	

		} else {
			prd($form->getMessages());
		}

        }
		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading,'data'=>$data));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;
	}
	
	public function howitworksAction() {
		$pageHeading = "Manage How It Works";
		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));
		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));
	}
	
	public function gethowitworksAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('content_id','content_heading','content_image','content_video');
		$sIndexColumn = 'content_id';
		$sTable = T_CONTENT;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}

		}/* End Table Setting */
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
  			$row[] = $row1['content_heading'];
			if(!empty($row1["content_image"])) {
				$row[] = '<img src="'.HTTP_SLIDER_IMAGES_PATH.'/'.$row1['content_image'].'" style="max-width:240px;">';
			} else if(!empty($row1["content_video"])) {
				$row[] = '<iframe src="'.getYoutubeEmbedUrl($row1["content_video"]).'" width="320" height="200">';
			} else {
				$row[] = '';
			}
			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-how-it-works/'.myurl_encode($row1['content_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
  			$output['aaData'][] = $row;			
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function managehowworksAction() {
		$this->layout()->setVariable('backUrl', 'how-it-works-page');

		$edit_id = $this->params()->fromRoute('id');
	    $form = new StaticForm($this->layout()->translator);
		$form->howitworks($edit_id);
		
		$PageHeading=$this->layout()->translator->translate("Add Content");

		if($edit_id != ''){

			$edit_id=myurl_decode($edit_id);
			
			$PageHeading=$this->layout()->translator->translate("Edit How It Works");
			$data=$this->SuperModel->Super_Get(T_CONTENT,'content_id =:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/how-it-works-page');	
			}else{
				$form->get('content_heading')->setValue($data['content_heading']);
				$form->get('content_description')->setValue($data['content_description']);
				if(!empty($data["content_shortdesc"])) {
					$form->get('content_shortdesc')->setValue($data['content_shortdesc']);
				}
				if($edit_id == '2') {
					$form->get('content_fburl')->setValue($data['content_fburl']);
					$form->get('content_instaurl')->setValue($data['content_instaurl']);
					$form->get('content_networkurl')->setValue($data['content_networkurl']);
				}
				if($edit_id == '4') {
					$form->get('content_video')->setValue($data['content_video']);
				}
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();   
				$imagePlugin = $this->Image();		

				$files =  $this->getRequest()->getFiles()->toArray();

				$newName="";

				$invalidimage=0; 	
				if($files['content_image']!=''){
					if (strpos(file_get_contents($files['content_image']['tmp_name']), '<?php') !== false) 
					{
						$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/how-it-works-page');
					}	
					if($files['content_image']['tmp_name']!=''){

						$finfo = finfo_open(FILEINFO_MIME_TYPE); 

						$uploaded_image_extension = getFileExtension($files['content_image']['name']);

						$typeval=finfo_file($finfo, $files['content_image']['tmp_name']);

						finfo_close($finfo);

						if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png'))){
							$this->adminMsgsession['errorMsg']='Please upload valid image.';
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/how-it-works-page');	
						}
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>SLIDER_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>0,'thumbs'=>array(

							'160'=>array(

								 "width"=>160,

								 "height"=>160,

							  ),

							'300'=>array(

								 "width"=>300,

								 "height"=>300,

							  ),					  

						)));

						if($is_uploaded_icon->success=="1" && $is_uploaded_icon->media_path!=''){

							$newName=$is_uploaded_icon->media_path;
							$postData["content_image"] = $newName;
						}
					}
				} else {
					$postData["content_image"] = $data["content_image"];
				} 
				$postData["content_heading"] = $Formdata["content_heading"];
				$postData["content_shortdesc"] = $Formdata["content_shortdesc"];
				$postData["content_description"] = $Formdata["content_description"];   
				$postData["content_video"] = $Formdata["content_video"];
				$postData["content_fburl"] = $Formdata["content_fburl"];
				$postData["content_instaurl"] = $Formdata["content_instaurl"];
				$postData["content_networkurl"] = $Formdata["content_networkurl"];
				unset($Formdata['bttnsubmit']);
				if(!empty($edit_id)){
					$isInserted=$this->SuperModel->Super_Insert(T_CONTENT,$postData,"content_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Content has been updated.");
				}else{
					$isInserted=$this->SuperModel->Super_Insert(T_CONTENT,$postData);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Content has been saved.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/how-it-works-page');	

		} else {
			prd($form->getMessages());
		}

        }
		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading,'data'=>$data));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;
	}
	
	public function addblogcategoryAction() {
		$this->layout()->setVariable('backUrl', 'blog-categories');
		$edit_id = $this->params()->fromRoute('id');
	    $form = new StaticForm($this->layout()->translator);
		$form->blogcategory();
		$PageHeading=$this->layout()->translator->translate("blog_category_title");
		if($edit_id != ''){
			$edit_id=myurl_decode($edit_id);
			$PageHeading=$this->layout()->translator->translate("blog_category_title");
			$data=$this->SuperModel->Super_Get(T_BLOG_CATEGORY,'blog_category_id=:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');	
			}else{
				$form->get('blog_category_title')->setValue($data['blog_category_title']);
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();
				$blog_check = $this->SuperModel->Super_Get(T_BLOG_CATEGORY,"blog_category_title =:TID and blog_category_id != '".$edit_id."'","fetch",array('warray'=>array('TID'=>trim($Formdata["blog_category_title"]))));
				if(!empty($blog_check)) {
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("This category already exists.");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');
				}
				$postData=array(
					'blog_category_title'=>$Formdata['blog_category_title']
				);
				$postData['blog_category_date'] = date('Y-m-d H:i:s');
				unset($Formdata['bttnsubmit']);
				if(!empty($edit_id)){
					$isInserted=$this->SuperModel->Super_Insert(T_BLOG_CATEGORY,$postData,"blog_category_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog Category has been updated.");
				}else{
					$isInserted=$this->SuperModel->Super_Insert(T_BLOG_CATEGORY,$postData);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Blog Category has been added.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');	
		} else {
			prd($form->getMessages());
		}
        }
		$this->layout()->setVariable('pageHeading',$PageHeading);
		$this->layout()->setVariable('pageHeading', $PageHeading);
		$this->layout()->setVariable('pageDescription', $PageHeading);
		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading));
		$view->setTemplate('admin/admin/add.phtml');
		return $view;
	}
	
	public function getblogcategoriesAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('blog_category_id','blog_category_title','blog_category_date');
		$sIndexColumn = 'blog_category_id';
		$sTable = T_BLOG_CATEGORY;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}

		}/* End Table Setting */
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
  			$row[]=$row1['blog_category_title'];
			$row[]=date('d M, Y h:i a',strtotime($row1['blog_category_date']));
			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-blog-category/'.myurl_encode($row1['blog_category_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
  			$output['aaData'][] = $row;			
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function removeblogcategoryAction(){
		$request = $this->getRequest();
		if ($request->isPost()) {
			 $del = $request->getPost(T_BLOG_CATEGORY);
			 foreach($del as $key=>$ids)
			 {  
				$isdeleted=$this->SuperModel->Super_Delete(T_BLOG_CATEGORY,'blog_category_id ="'.$ids.'"');	 
			 } 
		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("blogcat_deleted_txt");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/blog-categories');
	}

	/* Photos module{*/

	public function allphotosAction(){

		$pageHeading="All Photos";

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading));

	}

	public function addphotosAction(){

	

		$pageHeading="Manage Photos";

		

		$this->layout()->setVariable('backUrl', 'photos-list');

		$photoid=$this->params()->fromRoute("id"); 

		if($photoid==''){return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photos-list');	}

		

		$photoid=myurl_decode($photoid);

		$getCategorydata=$this->SuperModel->Super_Get(T_CATEGORY,"pi_cat_id=:category","fetch",array("warray"=>array("category"=>$photoid)));

		if(empty($getCategorydata)){return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photos-list');}

		$pageHeading.=' of '.$getCategorydata['pi_cat_name'];

		

		$getCategoryImages=$this->SuperModel->Super_Get(T_PIC_LIST,"pis_lis_categoryid=:category","fetchall",array("warray"=>array("category"=>$photoid)));

	

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	$form = new StaticForm($this->layout()->translator);

		$form->phtogallery(); 

		$request = $this->getRequest();

			 if($request->isPost()) {

					 $form->setData($request->getPost());

					  if($form->isValid()){

						$Formdata = $form->getData();

						

						$uploadedhotelimages=$Formdata['sch_fileupload_image']; 

						if($uploadedhotelimages!=''){

							

							$mediarray=explode(",",$uploadedhotelimages);

							if(!empty($mediarray)){

								foreach($mediarray as $mdKey=>$mdValue){

									if(file_exists(PHOTOS_IMAGES_PATH.'/'.$mdValue))

									$this->SuperModel->Super_Insert(T_PIC_LIST,array("pis_lis_categoryid"=>$photoid,"pis_lis_name"=>$mdValue,"pis_lis_addedon"=>date("Y-m-d H:i:s")));

			 					}	

							}

							

						  }

						  $this->adminMsgsession['successMsg']  = "Photos has been added to gallery.";

							return $this->redirect()->toUrl(ADMIN_APPLICATION_URL.'/photos-list');	

					  }else{

						$this->adminMsgsession['errorMsg']="some error occurred";

					}

			 }

		

		return new ViewModel(array('page_icon'=>'fa fa-list','pageHeading'=>$pageHeading,'getCategoryImages'=>$getCategoryImages,"getCategorydata"=>$getCategorydata,"form"=>$form));

	

	

	}

	public function photosmediaremoveAction(){

		$request = $this->getRequest();

		if ($request->isXmlHttpRequest() ) {

			 if($request->isPost()) {

			 	$posted_data = $this->getRequest()->getPost();

			 	if($posted_data["imgpost"]!=''){

					$imgpost=myurl_decode($posted_data["imgpost"]);

					/* get image name */

					$picsList=$this->SuperModel->Super_Get(T_PIC_LIST,"pis_lis_id='".$imgpost."'","fetch");

					$imagePlugin = $this->Image();

					$is_PostedEvent=$this->SuperModel->Super_Delete(T_PIC_LIST,"pis_lis_id='".$imgpost."'");

					if(is_object($is_PostedEvent) && $is_PostedEvent->success){

							if(!empty($picsList["pis_lis_name"])){

								/*remove image in all folders */

								 $imagePlugin->universal_unlink($picsList["pis_lis_name"],array("directory"=>PHOTOS_IMAGES_PATH));

								

					$fileArr=explode(".",$picsList['pis_lis_name']);

					$getExt=@array_pop($fileArr);

					if($getExt=='mp4'){

						/* remove images also for videos */

						

						$attachimage='img_'.$fileArr[0].'.jpg';

						$imagePlugin->universal_unlink($attachimage,array("directory"=>PHOTOS_IMAGES_PATH));

						$imagePlugin->universal_unlink($attachimage,array("directory"=>PHOTOS_IMAGES_PATH.'/160'));	

						$imagePlugin->universal_unlink($attachimage,array("directory"=>PHOTOS_IMAGES_PATH.'/thumb'));			

					}

					else{

					$imagePlugin->universal_unlink($picsList["pis_lis_name"],array("directory"=>PHOTOS_IMAGES_PATH.'/160'));

					$imagePlugin->universal_unlink($picsList["pis_lis_name"],array("directory"=>PHOTOS_IMAGES_PATH.'/thumb'));

					}

							}

						echo 1;exit();

					}

						

					

				}

			 }

			 echo 0;exit();

		}

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

	}

	public function photosmediaAction(){

		$request = $this->getRequest();

		

		if ($request->isXmlHttpRequest() ) {

		 if($request->isPost()) {

			 $isposteddata=$request->getPost();

			 $posted_data = $this->getRequest()->getPost();  

			$files =  $request->getFiles()->toArray();	

			$imagePlugin = $this->Image();

			//prd(getFileExtension($files["file"]["name"]));

			$is_uploaded=$imagePlugin->universal_upload(array("directory"=>PHOTOS_IMAGES_PATH,"files_array"=>$files,"multiple"=>"0",'thumbs'=>array(

												 'thumb'=>array(

												 	"width"=>260,

												 	"height"=>260,

												  ),

												 '160'=>array(

												   "width"=>160,

												   "height"=>160,

												  ),

        										

           										 ),));

												

				//prd(getFileExtension());								

			if(is_object($is_uploaded) && $is_uploaded->success){

				if(getFileExtension($files["file"]["name"])=='mp4'){

					$video=$is_uploaded->media_path;

					$filename = pathinfo($is_uploaded->media_path, PATHINFO_FILENAME);

					$video_extension = pathinfo($video, PATHINFO_EXTENSION);

					$new_name = 'VIDthumb_'.rand(10,10000).time();

					/* Create Thumb images  { */

				

				$pathThumb = PHOTOS_IMAGES_PATH;

				$oo = pathinfo($video, PATHINFO_FILENAME); 

				$second = '00:00:02.000';

				$image_name = 'img_'.$oo."";

				$image  = $pathThumb.'/'.$image_name;

				$videoName = PHOTOS_IMAGES_PATH.'/'.$video;

				$thumb = $image_name.'.jpg';  

				$ffmpeg='ffmpeg';

				$cmd	=  exec($ffmpeg.' -i '.$videoName.' -ss '.$second.' -vframes 1   '.$image.'.jpg');

				

				

				$thumb_config = array("source_path"=>$pathThumb,"name"=>$thumb);       

				$mainPath  = $pathThumb;

				$imageCrop = $this->ImageCrop();

				$imageCrop->uploadThumb(array_merge($thumb_config,array("width"=>260,"height"=>260,"crop"=>false,"ratio"=>false,"destination_path"=>$pathThumb."/thumb")));

				$imageCrop->uploadThumb(array_merge($thumb_config,array("width"=>160,"height"=>160,"crop"=>false,"ratio"=>false,"destination_path"=>$pathThumb."/160")));     

				

		

			

				

				

				

				

				/* } */

				

				}

				echo $is_uploaded->media_path; exit();

			}

			else{

				header("HTTP/1.0 400 Bad Request");

				echo "Invalid File";	exit();

			}

		 }

		 echo 0;exit();

		}return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

	}

	

	public function checkexistcategoryAction(){

		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {

		$category_name=$this->params()->fromQuery('category_name_en');

		$category_id=$this->params()->fromQuery('category');

		$category_data=array();

		if($category_id==''){

			$category_data= $this->SuperModel->Super_Get(T_CATEGORY, "LOWER(pi_cat_name)=:catname","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($category_name))))));

		}else{

			$category_id=myurl_decode($category_id);

			$category_data= $this->SuperModel->Super_Get(T_CATEGORY, "LOWER(pi_cat_name)=:catname and pi_cat_id!=:categoryid","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($category_name))),"categoryid"=>$category_id)));

		}

		if(!empty($category_data)){

				echo json_encode("Category name already exist");

			}else{

				echo json_encode("true");	

			}

			exit();

		}

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL);

	}

	public function addphotocategoryAction(){

		

		$submaster_id=$this->params()->fromRoute("id");  

		$issubwhere=$masterTable=$matchWordField=$dateField=$masterTable_keyfield=$updateElement='';

		$form = new MasterForm($this->layout()->translator);

		$pag_title='';

		$masterData=array();

				$updateElement='Category';

				$pag_title='Add Category';

				$pagefields=array("category_name_en"=>"text");//,"category_image"=>"file"

				$form->mastermanage($pagefields); 

				$masterTable=T_CATEGORY;$matchWordField='pi_cat_name';$dateField="pi_cat_addedon";

				$masterTable_keyfield='pi_cat_id';

				if($submaster_id!=''){

					

					$pag_title='Edit Category';

					$submaster_id=@myurl_decode($submaster_id);

			

					$issubwhere=' and pi_cat_id!="'.$submaster_id.'"';

					

					$masterData= $this->SuperModel->Super_Get($masterTable, "pi_cat_id=:categoryid","fetch",array("warray"=>array("categoryid"=>$submaster_id)));

					

					

					if(empty($masterData)){

						$this->adminMsgsession['infoMsg']  = $this->layout()->translator->translate("no_record_found");

						return $this->redirect()->toUrl(ADMIN_APPLICATION_URL.'/photos-category');		

					}

					$form->populateValues(array("category_name_en"=>$masterData["pi_cat_name"]));

					

				}

			if($pag_title==''){return $this->redirect()->toUrl(ADMIN_APPLICATION_URL);	}

			$this->layout()->setVariable('pageHeading',$pag_title);

			$request = $this->getRequest();

			 if($request->isPost()) {

					$form->setData($request->getPost());

					if($form->isValid()){

						$Formdata = $form->getData();

						

							/*$imagePlugin = $this->Image();		

							$files =  $this->getRequest()->getFiles()->toArray();

							if($files['category_image']!=''){

								$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>CATEGORIES_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>0,"thumb"=>true));

								if($is_uploaded_icon->success=="1" && $is_uploaded_icon->media_path!=''){

									$Formdata['category_image']=$is_uploaded_icon->media_path;

								}

								else

								{

									$invalidimage=1;

									$this->adminMsgsession['errorMsg']='Invalid image';

								//$Formdata['act_cat_image']=$masterData['category_image'];		

								}	

								

							}

							else{

								$Formdata['category_image']=$masterData['category_image'];

							}

						*/

						$formelement=array("pi_cat_name"=>"category_name_en");

						$ismatch_data=array();

						if($submaster_id==''){

						$ismatch_data= $this->SuperModel->Super_Get($masterTable, "LOWER(".$matchWordField.")=:catname","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata[$formelement[$matchWordField]]))))));

						}else{

							

						$ismatch_data= $this->SuperModel->Super_Get($masterTable, "LOWER(".$matchWordField.")=:catname and pi_cat_id!=:categoryid","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata[$formelement[$matchWordField]]))),"categoryid"=>$submaster_id)));

						}

						

						

						if(empty($ismatch_data)){

							if($submaster_id!=''){

								$is_updated=$this->SuperModel->Super_Insert($masterTable,array($matchWordField=>$Formdata[$formelement[$matchWordField]],$dateField=>date("Y-m-d H:i:s")),$masterTable_keyfield."='".$submaster_id."'");

								if(is_object($is_updated) && $is_updated->success){

									$this->adminMsgsession['successMsg']=$updateElement.' Updated Successfully.';

								}else{

									$this->adminMsgsession['errorMsg']='Some error occurred';

									return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photos-category');		

								}

							}else{

								

								$Formdata["pi_cat_addedon"] = date("Y-m-d H:i:s");

								$Formdata["pi_cat_name"] = $Formdata["category_name_en"];

								unset($Formdata["category_name_en"]);

								unset($Formdata["bttnsubmit"]);unset($Formdata["post_csrf"]);

								$is_Inserted=$this->SuperModel->Super_Insert($masterTable,$Formdata);		

								if(is_object($is_Inserted) && $is_Inserted->success){

								$this->adminMsgsession['successMsg']=$updateElement.' Added Successfully.';

								}else{

									$this->adminMsgsession['errorMsg']='Some error occurred';

								}

							}

							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photos-category');		

						}else{

							$this->adminMsgsession['errorMsg']=$updateElement.' name already exist';

						}

					} else {

						$this->adminMsgsession['errorMsg']='Please check entered information again.';

					}

			 }

			 $this->layout()->setVariable('backUrl', 'photos-category');

			 $view = new ViewModel(array('form'=>$form,'pageHeading'=>$pag_title,'masterData'=>$masterData,"submaster_id"=>$submaster_id));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;



	}

	public function removephotoscategoryAction(){

		$updateElement='Photo Categories';

		$masterTable=T_CATEGORY;$masterTable_keyfield='pi_cat_id';

		$request = $this->getRequest();

		if ($request->isPost()) {

			 $del = $request->getPost($masterTable);

			 $delIds=implode(",",$del);

			/*

				foreach(explode(',',$delIds) as $allVal){

					$getData=$this->SuperModel->Super_Get($masterTable,$masterTable_keyfield.'="'.$allVal.'"');

					unlink(CATEGORIES_IMAGES_PATH.'/60/'.$getData['category_image']);

					unlink(CATEGORIES_IMAGES_PATH.'/160/'.$getData['category_image']);

					unlink(CATEGORIES_IMAGES_PATH.'/thumb/'.$getData['category_image']);

					unlink(CATEGORIES_IMAGES_PATH.'/'.$getData['category_image']);

				}*/

			$isdeleted=$this->SuperModel->Super_Delete($masterTable,$masterTable_keyfield.' IN('.$delIds.')');	 

		}

		$this->adminMsgsession['successMsg']=$updateElement.' Deleted Successfully.';

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photos-category');

	}

	public function photocategoryAction(){

		$pageHeading="Photos Category";

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-question-list','pageHeading'=>$pageHeading));

	}

	public function getphotocategorieslistAction()

	{

		$pg_type = $this->params()->fromRoute('type',1);

		

		$dbAdapter = $this->Adapter;

		

		$aColumns = array('pi_cat_id','pi_cat_name','pi_cat_addedon');



		$sIndexColumn = 'pi_cat_id';

		$sTable = T_CATEGORY;

  		

		/*Table Setting*/{

		

		/* Paging */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "".$aColumns[$i]." LIKE '%".trim(addslashes($_GET["sSearch"]))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE  ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		}/* End Table Setting */

		

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit";  

		if($pg_type==2){

			$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)).",(select count(pis_lis_categoryid) from yurt90w_pics_list where pis_lis_categoryid=pi_cat_id) as categorycount FROM  $sTable $sWhere $sOrder group by pi_cat_id $sLimit"; 

			

			

		}

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/* Output */

		 

 		$output = array(

			"iTotalRecords" => $iTotal,

			"iTotalDisplayRecords" => $iFilteredTotal,

			"aaData" => array()

		);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

 			$row[] = $j;

			if($pg_type==1){

			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';

			}

			$row[] = $row1['pi_cat_name'];

			if($pg_type==2){

			$row[]=$row1['categorycount'];	

			}else{

			$row[]=date('d M, Y h:i a',strtotime($row1['pi_cat_addedon']));

			}

			if($pg_type==1){

			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-photocategory/'.myurl_encode($row1[$sIndexColumn]).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

			}else{

				$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-photos/'.myurl_encode($row1[$sIndexColumn]).'"><span class="label label-success">'.$this->layout()->translator->translate("Manage").'</a>';

			}

			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

	}

	/*End Photos}*/
	
	/* products */
	public function productlistAction() {
		$this->layout()->setVariable('pageHeading',"Manage Products");
		return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));
	}
	
	public function reportedproductsAction() {
		$this->layout()->setVariable('pageHeading',"Reported Products");
		return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));
	}
	
	public function getreportedproductsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('product_report_id','product_title','product_report_pid','product_report_date','product_report_uid','product_report_text',T_CLIENT_VAR.'client_name');
		$sIndexColumn = 'product_report_id';
		$sTable = T_PRODREPORT;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (product_report_uid = yurt90w_client_id) INNER JOIN '.T_PRODUCTS.' ON (product_report_pid = product_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$row=array();
 			$row[] =$j;
			$row[] = $row1["product_title"];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			$row[] = $row1["product_report_date"];
			$row[] = nl2br($row1["product_report_text"]);
			$row[] = '<a href="'.APPLICATION_URL.'/product/'.slugify($row1["product_title"]).'~'.str_replace("=","",base64_encode($row1["product_report_pid"])).'" target="_blank"><span class="btn btn-xs btn-info waves-effect waves-classic">View Product</span></a>';
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function getfavproductlistAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('product_id','product_title','product_photos','product_category','product_price','product_clientid','product_status','product_favstat','product_date','category_id','category_feild',T_CLIENT_VAR.'client_name','product_isdigital');
		$sIndexColumn = 'product_id';
		$sTable = T_PRODUCTS;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		if($sWhere==''){

			$sWhere=" where product_favstat='1'";

		}else{

			$sWhere=" and product_favstat='1'";
		}
		
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (product_clientid = yurt90w_client_id) INNER JOIN '.T_CATEGORY_LIST.' ON (product_category = category_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$price_data = $this->layout()->AbstractModel->Super_Get(T_PROQTY,"color_productid =:PID","fetch",array('warray'=>array('PID'=>$row1["product_id"])));
			$row=array();
 			$row[] =$j;
			$digital_box = '';
			if($row1["product_isdigital"] == '1') {
				$digital_box = '<br/><span class="btn btn-xs btn-info">Digital Product</span>';
			}
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
			$row[] = $row1["product_title"].$digital_box;
			$product_picarr = explode(",",$row1["product_photos"]);
			$row[] = '<img src="'.HTTP_PRODUCT_PIC_PATH.'/thumb/'.$product_picarr[0].'" style="max-width:150px;">';
  			$row[]= $row1['category_feild'];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			if($row1["product_isdigital"] == '1') {
				$row[] = "$".$row1["product_price"];
			} else {
				$row[] = "$".$price_data["color_price"];
			}
			$checkClass = "";

			if($row1['product_status'] == '1'){
				$checkClass = "checked";
			} 
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['product_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";
			$row[] = $row1["product_date"];
			/*if($row1["product_status"] == '1') {
				$product_status = '<span class="btn btn-success btn-xs" style="cursor:default;">Approved</span>';
			} else if($row1["product_status"] == '0') {
				$product_status = '<span class="btn btn-warning btn-xs" style="color:#fff;cursor:default;">Pending</span>';
			} else {
				$product_status = '<span class="btn btn-danger btn-xs" style="cursor:default;">Declined</span>';
			}
			$row[] = $product_status;*/
			if($row1["product_status"] == '0') {
				$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="approve-prodtg btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Approve</a><br/><a href="javascript:void(0)" class="decline-prodtg btn btn-xs btn-danger" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Decline</a>';
			} else {
				if($row1["product_status"] == '1') {
					if($row1["product_favstat"] == '0') {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="addfav-btn btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Add to Favorite</a>';
					} else if($row1["product_favstat"] == '1') {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="removefav-btn btn btn-xs btn-danger" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Remove from Favorite</a>';
					} else {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="addfav-btn btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Add to Favorite</a>';
					}
				} else {
					$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a>';
				}
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
	}
	
	public function getproductsAction() {
		$dbAdapter = $this->Adapter;
		$aColumns = array('product_id','product_title','product_photos','product_category','product_price','product_clientid','product_status','product_favstat','product_date','category_id','category_feild',T_CLIENT_VAR.'client_name','product_isdigital');
		$sIndexColumn = 'product_id';
		$sTable = T_PRODUCTS;
		/*Table Setting*/{
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
		}
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
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
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}
		}
		}/* End Table Setting */
		$sJoin = 'INNER JOIN '.T_CLIENTS.' ON (product_clientid = yurt90w_client_id) INNER JOIN '.T_CATEGORY_LIST.' ON (product_category = category_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry=$results->getResource()->fetchAll();
 		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as fcnt";
		$results = $dbAdapter->query($sQuery)->execute();
		$aResultFilterTotal=$results->getResource()->fetchAll();
		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */
		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";
		$results = $dbAdapter->query($sQuery)->execute();
		$rResultTotal=$results->getResource()->fetchAll();
		$iTotal = $rResultTotal[0]['cnt'];
		/*
		 * Output
		 */
 		$output = array(
 				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
		$j=1;
		foreach($qry as $row1)
		{
			$price_data = $this->layout()->AbstractModel->Super_Get(T_PROQTY,"color_productid =:PID","fetch",array('warray'=>array('PID'=>$row1["product_id"])));
			$row=array();
 			$row[] =$j;
			$digital_box = '';
			if($row1["product_isdigital"] == '1') {
				$digital_box = '<br/><span class="btn btn-xs btn-info">Digital Product</span>';
			}
			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
			$row[] = $row1["product_title"].$digital_box;
			$product_picarr = explode(",",$row1["product_photos"]);
			$row[] = '<img src="'.HTTP_PRODUCT_PIC_PATH.'/thumb/'.$product_picarr[0].'" style="max-width:150px;">';
  			$row[]= $row1['category_feild'];
			$row[] = $row1[T_CLIENT_VAR.'client_name'];
			if($row1["product_isdigital"] == '1') {
				$row[] = "$".$row1["product_price"];
			} else {
				$row[] = "$".$price_data["color_price"];
			}
			$checkClass = "";

			if($row1['product_status'] == '1'){
				$checkClass = "checked";
			} 
			$row[] = "<input type='checkbox' class='js-switch status-".(int)$row1['product_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";
			$row[] = $row1["product_date"];
			/*if($row1["product_status"] == '1') {
				$product_status = '<span class="btn btn-success btn-xs" style="cursor:default;">Approved</span>';
			} else if($row1["product_status"] == '0') {
				$product_status = '<span class="btn btn-warning btn-xs" style="color:#fff;cursor:default;">Pending</span>';
			} else {
				$product_status = '<span class="btn btn-danger btn-xs" style="cursor:default;">Declined</span>';
			}
			$row[] = $product_status;*/
			if($row1["product_status"] == '0') {
				$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="approve-prodtg btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Approve</a><br/><a href="javascript:void(0)" class="decline-prodtg btn btn-xs btn-danger" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Decline</a>';
			} else {
				if($row1["product_status"] == '1') {
					if($row1["product_favstat"] == '0') {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="addfav-btn btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Add to Favorite</a>';
					} else if($row1["product_favstat"] == '1') {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="removefav-btn btn btn-xs btn-danger" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Remove from Favorite</a>';
					} else {
						$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a><br/><a href="javascript:void(0)" class="addfav-btn btn btn-xs btn-success" data-id="'.str_replace("=","",myurl_encode($row1["product_id"])).'">Add to Favorite</a>';
					}
				} else {
					$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/view-product/'.myurl_encode($row1['product_id']).'"><span class="btn btn-xs btn-info waves-effect waves-classic">View</span></a>';
				}
			}
			$output['aaData'][] = $row;
			$j++;
		}	
		echo json_encode( $output );
		exit();
 	}
	
	public function declinewithdrawalAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if($request->isPost()) {
				$posted_data = $this->getRequest()->getPost();
				$withdraw_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["withdraw"]))));
				if(empty($withdraw_data)) {
					echo "error";
					exit();
				}
				if($withdraw_data["withdrawal_type"] == '1') {
					echo "already_approved";
					exit();
				} elseif($withdraw_data["withdrawal_type"] == '3') {
					echo "already_declined";
					exit();
				}
				if(empty($posted_data["txt"])) {
					echo "empty";
					exit();
				}
				$withtx_data["withdrawal_type"] = '3';
				$withtx_data["withdrawal_declinetxt"] = strip_tags($posted_data["txt"],'<br/>');
				$this->SuperModel->Super_Insert(T_WITHDRAWAL,$withtx_data,"withdrawal_id = '".base64_decode($posted_data["withdraw"])."'");
				$seller_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$withdraw_data["withdrawal_clientid"])));
				$notify_data["notification_type"] = '8';
				$notify_data["notification_by"] = '1';
				$notify_data["notification_to"] = $withdraw_data["withdrawal_clientid"];
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_status"] = '0';
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				$mail_const_data2 = array(
					  "user_name" => $seller_data[T_CLIENT_VAR.'client_name'],
					  "user_email" => $seller_data[T_CLIENT_VAR.'client_email'],
					  "message" => "Admin has declined your withdrawal request of amount $".$withdraw_data["withdrawal_amount"].".",
					  "subject" => "Withdrawal request"
				);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				echo "success";
				exit();
			}
		}
	}
	
	public function approvewithdrawalAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if($request->isPost()) {
				$posted_data = $this->getRequest()->getPost();
				$joinArr = array(
					'0'=> array('0'=>T_CLIENTS,'1'=>'withdrawal_clientid=yurt90w_client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_stripe_id',T_CLIENT_VAR.'client_paypal_email')),
				);
				$withdraw_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["withdraw"]))),$joinArr);
				if(empty($withdraw_data)) {
					$sendData['response_code'] = 'error';
					$sendData["message"] = 'No such withdrawal requested.';
					echo json_encode($sendData);
					exit();
				}
				if(empty($withdraw_data[T_CLIENT_VAR."client_paypal_email"])) {
					$sendData['response_code'] = 'not_connected';
					$sendData["message"] = 'Seller has not added his paypal account details.';
					echo json_encode($sendData);
					exit();
				}
				if($withdraw_data["withdrawal_type"] == '1') {
					$sendData['response_code'] = 'already_approved';
					$sendData["message"] = 'Withdrawal has already been approved.';
					echo json_encode($sendData);
					exit();
				} elseif($withdraw_data["withdrawal_type"] == '3') {
					$sendData['response_code'] = 'already_declined';
					$sendData["message"] = 'Withdrawal has already been declined.';
					echo json_encode($sendData);
					exit();
				}
				require_once(ROOT_PATH.'/vendor/Payouts-PHP-SDK-master/autoload.php');
				// Creating an environment
				$clientId = "AYbjXUvmsss_qnAaDdDGoRFEIdR7eu4OoxRpBvBSGVLNjMY4VbNFZNPccLYKlwkf7BB7xtHJ7d71keAs";
				$clientSecret = "EFqrY1SazJnRoT2uWWfeS_r3bb_rkkmFfmnyymosOGWwogzEQ_bm52KfyFhd5BU3kGFB7W-LxgcUOc9p";
				//$clientId = "AV793uzzxutmhd4XB2faoxE0jQSPQb2pdzyxSzEw8YO92w7nLa9dJgiNmt9ZnqvqyZsMb0QEEtRlrP1o";
				//$clientSecret = "EMN8dOTZF8AWo73T7wwBIG6MIq1j996LtlecZDXCtTmzHgcE05yS18sZTcy6UF57smW4GE4CAw1CmItF";
				// Get access token from PayPal client Id and secrate key
				try {
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);

					$headers = array();
					$headers[] = "Accept: application/json";
					$headers[] = "Accept-Language: en_US";
					$headers[] = "Content-Type: application/x-www-form-urlencoded";
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$results = curl_exec($ch);
					$getresult = json_decode($results);


					// PayPal Payout API for Send Payment from PayPal to PayPal account
					curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/payments/payouts");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

					$array = array('sender_batch_header' => array(
							"sender_batch_id" => time(),
							"email_subject" => "You have a payout!",
							"email_message" => "You have received a payout."
						),
						'items' => array(array(
								"recipient_type" => "EMAIL",
								"amount" => array(
									"value" => $withdraw_data["withdrawal_amount"],
									"currency" => "USD"
								),
								"note" => "Thanks for the payout!",
								"sender_item_id" => time(),
								"receiver" => $withdraw_data[T_CLIENT_VAR."client_paypal_email"]
							))
					);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
					curl_setopt($ch, CURLOPT_POST, 1);

					$headers = array();
					$headers[] = "Content-Type: application/json";
					$headers[] = "Authorization: Bearer $getresult->access_token";
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$payoutResult = curl_exec($ch);
					//print_r($result);
					$getPayoutResult = json_decode($payoutResult);
					if($getPayoutResult->name == 'VALIDATION_ERROR') {
						$sendData['response_code'] = 'payout_error';
						$sendData["message"] = $getPayoutResult->details[0]->issue;
						echo json_encode($sendData);
						exit();
					} else if($getPayoutResult->name == 'INSUFFICIENT_FUNDS') {
						$sendData['response_code'] = 'payout_error';
						$sendData["message"] = $getPayoutResult->message;
						echo json_encode($sendData);
						exit();
					} else if($getPayoutResult->response_code == 'payout_error') {
						$sendData['response_code'] = 'payout_error';
						$sendData["message"] = $getPayoutResult->message;
						echo json_encode($sendData);
						exit();
					} else if($getPayoutResult->batch_header->batch_status == 'PENDING') {
						$withtx_data["withdrawal_transferid"] = $getPayoutResult->batch_header->payout_batch_id;
						$withtx_data["withdrawal_type"] = '1';
						$withtx_data["withdrawal_resp"] = json_encode($getPayoutResult);
						$this->SuperModel->Super_Insert(T_WITHDRAWAL,$withtx_data,"withdrawal_id = '".base64_decode($posted_data["withdraw"])."'");
					}
					if (curl_errno($ch)) {
						echo 'Error:' . curl_error($ch);
					}
					curl_close($ch);
				} catch(\Exception $e) {
					prd($e->getMessage());
				}
				/*require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
				\Stripe\Stripe::setApiKey($this->SITE_CONFIG["site_secret_key"]);
				$withdrawal_amt = $withdraw_data["withdrawal_amount"] * 100;
				try {
					$transfer = \Stripe\Transfer::create([
					  "amount" => $withdrawal_amt,
					  "currency" => "usd",
					  "destination" => $withdraw_data[T_CLIENT_VAR."client_stripe_id"],
					]);
					$response_body = accessProtected($transfer,'_lastResponse');
					if($response_body->code == '200') {
						$resp_arr = json_decode($response_body->body);
						$withtx_data["withdrawal_transferid"] = $resp_arr->id;
						$withtx_data["withdrawal_type"] = '1';
						$this->SuperModel->Super_Insert(T_WITHDRAWAL,$withtx_data,"withdrawal_id = '".base64_decode($posted_data["withdraw"])."'");
					} else {
						$sendData['response_code'] = 'stripe_error';
						$sendData["message"] = "Withdrawal cannot be approved as transfer of amount failed.";
						echo json_encode($sendData);
						exit();
					}
				}  catch(Stripe_CardError $e) {
					$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				} catch (Stripe_InvalidRequestError $e) {
				  	$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				} catch (Stripe_AuthenticationError $e) {
				  	$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				} catch (Stripe_ApiConnectionError $e) {
				  	$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				} catch (Stripe_Error $e) {
				  // Display a very generic error to the user, and maybe send
				  // yourself an email
				  	$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				} catch (Exception $e) {
				  // Something else happened, completely unrelated to Stripe
				  	$sendData['response_code'] = 'stripe_error';
					$sendData["message"] = $e->getMessage();
					echo json_encode($sendData);
					exit();
				}*/
				$seller_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$withdraw_data["withdrawal_clientid"])));
				$notify_data["notification_type"] = '7';
				$notify_data["notification_by"] = '1';
				$notify_data["notification_to"] = $withdraw_data["withdrawal_clientid"];
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_status"] = '0';
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				$mail_const_data2 = array(
					  "user_name" => $seller_data[T_CLIENT_VAR.'client_name'],
					  "user_email" => $seller_data[T_CLIENT_VAR.'client_email'],
					  "message" => "Admin has approved your withdrawal request of amount $".$withdraw_data["withdrawal_amount"].".",
					  "subject" => "Withdrawal request"
				);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);					
				$sendData['response_code'] = 'success';
				$sendData["message"] = 'Withdrawal has been approved successfully.';
				echo json_encode($sendData);
				exit();
			}
		}
 	}
	
	public function removeprodfavAction() {
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest() ) {
			if($request->isPost()) {
				$posted_data = $this->getRequest()->getPost();
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($posted_data["prod"]))));
				if(empty($product_data)) {
					echo "not_exists";
					exit();
				}
				$pro_data["product_favstat"] = '2';
				$this->SuperModel->Super_Insert(T_PRODUCTS,$pro_data,"product_id = '".myurl_decode($posted_data["prod"])."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function addprodfavAction() {
		$request = $this->getRequest();

		if ($request->isXmlHttpRequest() ) {
			if($request->isPost()) {
				$posted_data = $this->getRequest()->getPost();
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($posted_data["prod"]))));
				if(empty($product_data)) {
					echo "not_exists";
					exit();
				}
				$favorite_list = $this->SuperModel->Super_Get(T_PRODUCTS,"product_favstat = '1'","fetchAll");
				if(count($favorite_list) >= 32) {
					echo "denied";
					exit();
				}
				$pro_data["product_favstat"] = '1';
				$this->SuperModel->Super_Insert(T_PRODUCTS,$pro_data,"product_id = '".myurl_decode($posted_data["prod"])."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function acceptproductAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(empty($posted_data["id"])) {
				echo "error";
				exit();	
			}
			$joinArr = array(
				'0'=> array('0'=>T_CLIENTS,'1'=>'product_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name','yurt90w_client_email')),
			);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($posted_data["id"]))),$joinArr);
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				$prod_data["product_status"] = '1';
				$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_id = '".myurl_decode($posted_data["id"])."'");
				$mail_const_data2 = array(
					  "user_name" => $product_data[T_CLIENT_VAR.'client_name'],
					  "user_email" => $product_data[T_CLIENT_VAR.'client_email'],
					  "message" => "Admin has accepted your product request with title <b>".$product_data['product_title']."</b>.",
					  "subject" => "Product request"
				);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
				echo "success";
				exit();
			}			
		}
	}
	
	public function declineproductAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(empty($posted_data["id"])) {
				echo "error";
				exit();
			}
			$joinArr = array(
				'0'=> array('0'=>T_CLIENTS,'1'=>'product_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name','yurt90w_client_email')),
			);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($posted_data["id"]))),$joinArr);
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				$prod_data["product_status"] = '2';
				$prod_data["product_declinetxt"] = strip_tags($posted_data["desc"]);
				$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_id = '".myurl_decode($posted_data["id"])."'");
				$mail_const_data2 = array(
					  "user_name" => $product_data[T_CLIENT_VAR.'client_name'],
					  "user_email" => $product_data[T_CLIENT_VAR.'client_email'],
					  "message" => "Admin has declined your product request with title <b>".$product_data['product_title']."</b>.",
					  "subject" => "Product request"
				);
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				echo "success";
				exit();
			}
		}
	}
	
	/* subcategorylist */
	public function subcategorylistAction() {
		$this->layout()->setVariable('pageHeading',"Manage Product Sub Categories");
		return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));
	}
	
	public function getsubcategoryAction(){
		$dbAdapter = $this->Adapter;
		

		$aColumns = array('subcategory_id','category_feild','subcategory_title','subcategory_categoryid','subcategory_date');
		$sIndexColumn = 'subcategory_id';

		$sTable = T_SUBCATEGORY_LIST;
		/*Table Setting*/{

		$sLimit = "";
			


		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )



		{



			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );



		}



		$sOrder = "";



		if ( isset( $_GET['iSortCol_0'] ) )



		{



			$sOrder = "ORDER BY  ";



			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )



			{



				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )



				{



					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".



					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";



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



			for ( $i=0 ; $i<count($aColumns) ; $i++ )



			{



				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";

			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}

		/* Individual column filtering */



		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
			}

		}

		}/* End Table Setting */


		$sJoin = 'INNER JOIN '.T_CATEGORY_LIST.' ON (subcategory_categoryid = category_id)';
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable $sJoin $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();


 		/* Data set length after filtering */



		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];
		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		$results = $dbAdapter->query($sQuery)->execute();


		$rResultTotal=$results->getResource()->fetchAll();
		

		$iTotal = $rResultTotal[0]['cnt'];

		/*
		 * Output
		 */


 		$output = array(
			 


 				"iTotalRecords" => $iTotal,


				"iTotalDisplayRecords" => $iFilteredTotal,


				"aaData" => array()


			);
			// prd($output);

		$j=1;


		foreach($qry as $row1)

		{

			$row=array();


 			$row[] =$j;


			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';
  			$row[]=$row1['category_feild'];
			$row[]=$row1['subcategory_title'];
			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/managesubcategory/'.myurl_encode($row1['subcategory_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';
  			$output['aaData'][] = $row;
			$j++;
		}
		echo json_encode( $output );
		exit();
 	}
	
	public function managesubcategoryAction(){
		$this->layout()->setVariable('backUrl', 'category');

		$edit_id = $this->params()->fromRoute('subcategory');
	    $form = new StaticForm($this->layout()->translator);
		$categories = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"1","fetchAll");
		$category_arr = array(''=>"Please select");
		if(!empty($categories)) {
			foreach($categories as $categories_key => $categories_val) {
				$category_arr[$categories_val["category_id"]] = $categories_val["category_feild"];
			}
		}
		$form->subcategory($category_arr);
		
		$PageHeading=$this->layout()->translator->translate("Add Sub Category");

		if($edit_id != ''){

			$edit_id=myurl_decode($edit_id);
			
			$PageHeading=$this->layout()->translator->translate("Edit Sub Category");

			$data=$this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,'subcategory_id=:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			if(empty($data)){
				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/category');	
			}else{
				$form->get('subcategory_categoryid')->setValue($data['subcategory_categoryid']);
				$form->get('subcategory_title')->setValue($data['subcategory_title']);
			}
		}
        $request = $this->getRequest();
        if($request->isPost()) {
		   	 	$form->setData($request->getPost());
			   if($form->isValid()){
				$Formdata = $form->getData();     
				$postData=array(
					'subcategory_title'=>$Formdata['subcategory_title'],
					'subcategory_categoryid'=>$Formdata['subcategory_categoryid'],	
				);
				$postData['subcategory_date'] = date('Y-m-d H:i:s');   
				unset($Formdata['bttnsubmit']);
				if(!empty($edit_id)){
					$isInserted=$this->SuperModel->Super_Insert(T_SUBCATEGORY_LIST,$postData,"subcategory_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Sub Category has been updated.");
				}else{
					$isInserted=$this->SuperModel->Super_Insert(T_SUBCATEGORY_LIST,$postData);
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Sub Category has been saved.");
				}
				if(!empty($isInserted)){
					
				}else{
					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/subcategories');	

		} else {
			prd($form->getMessages());
		}

        }

		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	}
	
	/* categorylist */
	
	public function categorylistAction(){

		$this->layout()->setVariable('pageHeading',"Manage Product Categories");

		 return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));

	}

	public function managecategoryAction(){
		$this->layout()->setVariable('backUrl', 'category');

		$edit_id = $this->params()->fromRoute('category');

	    $form = new StaticForm($this->layout()->translator);
		$form->category();
		
		$PageHeading=$this->layout()->translator->translate("Add Product Category");

		if($edit_id != ''){

			$edit_id=myurl_decode($edit_id);

			$PageHeading=$this->layout()->translator->translate("Edit Product Category");

			$data=$this->SuperModel->Super_Get(T_CATEGORY_LIST,'category_id=:categoryids','fetch',array("warray"=>array("categoryids"=>$edit_id)));
			// prd($data);
			if(empty($data)){

			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/category');	

			}else{

				$form->get('category_feild')->setValue($data['category_feild']);
                $form->get('category_description')->setValue($data['category_description']);

			}

		}

		

        $request = $this->getRequest();

	

        if($request->isPost()) {

		   	 	$form->setData($request->getPost());
			   if($form->isValid()){

				$Formdata = $form->getData();

				

				$postData=array(

					'category_feild'=>$Formdata['category_feild'],
                    'category_description'=>strip_tags($Formdata['category_description'])
					//'faq_answer_en'=>$Formdata['postfq_answer_en'],

					//'faq_fh_id' => $Formdata['postfq_fh_id'],

				);

				

				$postData['category_date'] = date('Y-m-d H:i:s');

				unset($Formdata['bttnsubmit']);

				if(!empty($edit_id)){
					$category_record = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_feild = '".trim($postData["category_feild"])."' and category_id != '".$edit_id."'","fetch");
					if(!empty($category_record)) {
						$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("Category already exists. Please enter different category title.");
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/managecategory/'.myurl_encode($edit_id));
					}
					$isInserted=$this->SuperModel->Super_Insert(T_CATEGORY_LIST,$postData,"category_id='".$edit_id."'");
					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Category has been updated.");
				}else{
					$category_record = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_feild = '".trim($postData["category_feild"])."'","fetch");
					if(!empty($category_record)) {
						$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("Category already exists. Please enter different category title.");
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/managecategory');	
					} else {
						$postData["category_status"] = '1';
						$postData["category_date"] = date("Y-m-d H:i:s");
						$isInserted=$this->SuperModel->Super_Insert(T_CATEGORY_LIST,$postData);
						$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Category has been saved.");
					}
				}
				   
				  

				if(!empty($isInserted)){

				}else{

					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/category');	

		} else {
			prd($form->getMessages());
		}

        }

		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	

		

		

	}	

	public function getcategoryAction(){
		$dbAdapter = $this->Adapter;
		

		$aColumns = array('category_id','category_status','category_feild','category_date');
		$sIndexColumn = 'category_id';

		$sTable = T_CATEGORY_LIST;
		/*Table Setting*/{

		$sLimit = "";
			


		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )



		{



			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );



		}



		$sOrder = "";



		if ( isset( $_GET['iSortCol_0'] ) )



		{



			$sOrder = "ORDER BY  ";



			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )



			{



				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )



				{



					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".



					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";



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



			for ( $i=0 ; $i<count($aColumns) ; $i++ )



			{



				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";



			}



			$sWhere = substr_replace( $sWhere, "", -3 );



			$sWhere .= ')';



		}



		



		/* Individual column filtering */



		for ( $i=0 ; $i<count($aColumns) ; $i++ )



		{



			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )



			{



				if ( $sWhere == "" )



				{



					$sWhere = "WHERE ";



				}



				else



				{



					$sWhere .= " AND ";



				}



				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";



			}



		}



		



		



		}/* End Table Setting */



		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 



		$results = $dbAdapter->query($sQuery)->execute();

		

		$qry=$results->getResource()->fetchAll();


 		/* Data set length after filtering */



		$sQuery = "SELECT FOUND_ROWS() as fcnt";



		



		$results = $dbAdapter->query($sQuery)->execute();



		$aResultFilterTotal=$results->getResource()->fetchAll();



		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];



		



		



		/* Total data set length */



		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";



		



		$results = $dbAdapter->query($sQuery)->execute();


		$rResultTotal=$results->getResource()->fetchAll();
		

		$iTotal = $rResultTotal[0]['cnt'];

		/*
		 * Output
		 */


 		$output = array(
			 


 				"iTotalRecords" => $iTotal,


				"iTotalDisplayRecords" => $iFilteredTotal,


				"aaData" => array()


			);
			// prd($output);

		$j=1;


		foreach($qry as $row1)

		{

			$row=array();


 			$row[] =$j;


			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';


  			$row[]=$row1['category_feild'];

			//$row[]=$row1['category_status'];

			//$row[]=nl2br($row1['faq_answer_'.$_COOKIE['currentLang']]);


			$row[]=date('d M, Y h:i a',strtotime($row1['category_date']));


			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/managecategory/'.myurl_encode($row1['category_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';


  			$output['aaData'][] = $row;
			// prd($output);
			
			$j++;

		}	

		echo json_encode( $output );

		exit();



 	} 

	public function removecategoryAction(){
		$request = $this->getRequest();	
		if ($request->isPost()) {

			 $del = $request->getPost(T_CATEGORY_LIST);
			 foreach($del as $key=>$ids)
			 {  
				$isdeleted=$this->SuperModel->Super_Delete(T_CATEGORY_LIST,'category_id ="'.$ids.'"');	 
			 } 

		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("cat_deleted_txt");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/category');
	}

	
	public function removesubcategoryAction() {
		$request = $this->getRequest();	
		if ($request->isPost()) {

			 $del = $request->getPost(T_SUBCATEGORY_LIST);
			 foreach($del as $key=>$ids)
			 {  
				$isdeleted=$this->SuperModel->Super_Delete(T_SUBCATEGORY_LIST,'subcategory_id ="'.$ids.'"');	 
			 } 

		}
		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Sub category deleted successfully.");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/subcategories');
	}
	
	/* Faqs module{*/

	

	public function faqslistAction(){

		$this->layout()->setVariable('pageHeading',"FAQ");

		 return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));

	}

	public function managefaqsAction(){

		

		$this->layout()->setVariable('backUrl', 'faqs');

		$edit_id = $this->params()->fromRoute('faq');

	    $form = new StaticForm($this->layout()->translator);

		

		$faq_heading_options = $this->SuperModel->prepareselectoptionwhere(T_FAQ_HEADING,'fh_id','fh_name','1');		

		$form->faqs($faq_heading_options);	

		$PageHeading=$this->layout()->translator->translate("add_faq_txt");

		if($edit_id != ''){

			$edit_id=myurl_decode($edit_id);

			$PageHeading=$this->layout()->translator->translate("edit_faq_txt");

			$data=$this->SuperModel->Super_Get(T_FAQ,'faq_id=:fqids','fetch',array("warray"=>array("fqids"=>$edit_id)));

			if(empty($data)){

			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faqs');	

			}else{

				$form->get('postfq_question_en')->setValue($data['faq_question_en']);

				$form->get('postfq_answer_en')->setValue($data['faq_answer_en']);	


				

			}

		}

		

        $request = $this->getRequest();

	

        if($request->isPost()) {

		   	 	$form->setData($request->getPost());

			   if($form->isValid()){

				$Formdata = $form->getData();
				$Formdata["postfq_answer_en"] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $Formdata["postfq_answer_en"]);
				$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
				//$Formdata["postfq_answer_en"] = preg_replace($url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $Formdata["postfq_answer_en"]); 	
				

				$postData=array(

					'faq_question_en'=>$Formdata['postfq_question_en'],

					'faq_answer_en'=>$Formdata['postfq_answer_en'],

					//'faq_fh_id' => $Formdata['postfq_fh_id'],

				);

				

				$postData['faq_added_date'] = date('Y-m-d H:i:s');

				unset($Formdata['bttnsubmit']);

				if($edit_id!=''){

					$isInserted=$this->SuperModel->Super_Insert(T_FAQ,$postData,"faq_id='".$edit_id."'");

				}else{

					$isInserted=$this->SuperModel->Super_Insert(T_FAQ,$postData);

				}
				   
				  

				if(!empty($isInserted)){

					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("faq_saved_txt");

				}else{

					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faqs');	

		}

        }

		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-question-circle','pageHeading'=>$PageHeading));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	

		

		

	}	

	public function getfaqsAction(){



  			



		$dbAdapter = $this->Adapter;



  



		$aColumns = array('faq_id','faq_id','faq_question_'.$_COOKIE['currentLang'],'faq_answer_'.$_COOKIE['currentLang'],'faq_added_date');







		$sIndexColumn = 'faq_id';



		$sTable = T_FAQ;

		/*Table Setting*/{

		$sLimit = "";



		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )



		{



			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );



		}



		$sOrder = "";



		if ( isset( $_GET['iSortCol_0'] ) )



		{



			$sOrder = "ORDER BY  ";



			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )



			{



				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )



				{



					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".



						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";



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



			for ( $i=0 ; $i<count($aColumns) ; $i++ )



			{



				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";



			}



			$sWhere = substr_replace( $sWhere, "", -3 );



			$sWhere .= ')';



		}



		



		/* Individual column filtering */



		for ( $i=0 ; $i<count($aColumns) ; $i++ )



		{



			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )



			{



				if ( $sWhere == "" )



				{



					$sWhere = "WHERE ";



				}



				else



				{



					$sWhere .= " AND ";



				}



				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";



			}



		}



		



		



		}/* End Table Setting */



		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM  $sTable  $sWhere $sOrder $sLimit"; 



		$results = $dbAdapter->query($sQuery)->execute();



		$qry=$results->getResource()->fetchAll();



		



		



 		/* Data set length after filtering */



		$sQuery = "SELECT FOUND_ROWS() as fcnt";



		



		$results = $dbAdapter->query($sQuery)->execute();



		$aResultFilterTotal=$results->getResource()->fetchAll();



		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];



		



		



		/* Total data set length */



		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";



		



		$results = $dbAdapter->query($sQuery)->execute();



		$rResultTotal=$results->getResource()->fetchAll();



		$iTotal = $rResultTotal[0]['cnt'];



		



		/*



		 * Output



		 */



		 



 		$output = array(



 				"iTotalRecords" => $iTotal,



				"iTotalDisplayRecords" => $iFilteredTotal,



				"aaData" => array()



			);



		



		$j=1;



		foreach($qry as $row1)



		{



			$row=array();



		



 			$row[] =$j;



		



			



			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';



  			$row[]=nl2br($row1['faq_question_'.$_COOKIE['currentLang']]);





			$row[]=nl2br($row1['faq_answer_'.$_COOKIE['currentLang']]);

			


			

			$row[]=date('d M, Y h:i a',strtotime($row1['faq_added_date']));



			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/managefaqs/'.myurl_encode($row1['faq_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';



						



  			$output['aaData'][] = $row;





			$j++;



		}	



		



		echo json_encode( $output );



		exit();



 	} 

	public function removefaqsAction(){

		

		$request = $this->getRequest();

			

		if ($request->isPost()) {

			 $del = $request->getPost(T_FAQ);

			

			 foreach($del as $key=>$ids)

			 {  

				$isdeleted=$this->SuperModel->Super_Delete(T_FAQ,'faq_id ="'.$ids.'"');	 

			 } 

		}

		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("faq_deleted_txt");

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faqs');

		

	}

	
	public function faqheadingAction(){

		

		 $this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("Faq Category"));

		 return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));

	}

	
	public function addfaqheadingAction(){

		 $this->layout()->setVariable('backUrl', 'faq-category');

		$submaster_id=$this->params()->fromRoute("faq");$pag_title='Add FAQ Category';

		$form = new StaticForm($this->layout()->translator);

		$form->addfaqheading();

		if($submaster_id!=''){

		$pag_title='Edit FAQ Category';

		$submaster_id=myurl_decode($submaster_id);

		$masterData= $this->SuperModel->Super_Get(T_FAQ_HEADING, "fh_id=:categoryid","fetch",array("warray"=>array("categoryid"=>$submaster_id)));

		if(empty($masterData)){

			$this->adminMsgsession['infoMsg']  = $this->layout()->translator->translate("no_record_found");

			return $this->redirect()->toUrl(ADMIN_APPLICATION_URL.'/faq-category');		

		}

		$form->populateValues(array("fqheadname"=>$masterData["fh_name"]));

		}

		  

	   $ismatch_data=array();

		$this->layout()->setVariable('pageHeading',$pag_title);

		$request = $this->getRequest();	

        if($request->isPost()) {

			

			$form->setData($request->getPost());

			

			 if($form->isValid()){

				 $Formdata = $form->getData();

			$ismatch_data=array();

			

			if($submaster_id==''){

			$ismatch_data= $this->SuperModel->Super_Get(T_FAQ_HEADING, "LOWER(fh_name)=:catname","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata["fqheadname"]))))));

			}else{

				

			$ismatch_data= $this->SuperModel->Super_Get(T_FAQ_HEADING, "LOWER(fh_name)=:catname and fh_id!=:categoryid","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata["fqheadname"]))),"categoryid"=>$submaster_id)));

			

					

			}

			

			if(empty($ismatch_data)){

							$posted_data = array();

							$posted_data['fh_name'] = $Formdata['fqheadname'];

							$posted_data['fh_created'] = date('Y-m-d h:i:s');

							if($submaster_id!=''){

								$is_updated=$this->SuperModel->Super_Insert(T_FAQ_HEADING,$posted_data,"fh_id='".$submaster_id."'");

								if(is_object($is_updated) && $is_updated->success){

									$this->adminMsgsession['successMsg']='Faq category Updated Successfully.';

								}else{

									$this->adminMsgsession['errorMsg']='Some error occurred';

									return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faq-category');		

								}

							}else{

								

								

								

								$is_Inserted=$this->SuperModel->Super_Insert(T_FAQ_HEADING,$posted_data);		

								if(is_object($is_Inserted) && $is_Inserted->success){

								$this->adminMsgsession['successMsg']='Faq category Added Successfully.';

								}else{

									$this->adminMsgsession['errorMsg']='Some error occurred';

								}

							}

							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faq-category');		

						}else{

						$this->adminMsgsession['errorMsg']='Faq category already exist';

			}	

			

			

		}else{

		

		}

			

		}

		$view = new ViewModel();

		$view->setTemplate('admin/admin/add.phtml');

		$view->setVariable('form',$form);

		return $view;

	}

	
	public function getfaqheadingAction(){

		

  			

		$dbAdapter = $this->Adapter;

  

		$aColumns = array('fh_id','fh_name','fh_created');



		$sIndexColumn = 'fh_id';

		$sTable = T_FAQ_HEADING;

  		

		/*Table Setting*/{

		

		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		

		}/* End Table Setting */

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/*

		 * Output

		 */

		 

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

		

 			$row[] =$j;

		

			

			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';

  			$row[]=nl2br($row1['fh_name']);			

			$row[]=date('d M, Y h:i a',strtotime($row1['fh_created']));

			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/manage-faq-category/'.myurl_encode($row1['fh_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

						

  			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

 	

		

	}
	
	public function removefaqsheadingAction(){

		

		$request = $this->getRequest();

			

		if ($request->isPost()) {

			 $del = $request->getPost(T_FAQ_HEADING);

			

			 foreach($del as $key=>$ids)

			 {  

				$isdeleted=$this->SuperModel->Super_Delete(T_FAQ_HEADING,'fh_id ="'.$ids.'"');	 

			 } 

		}

		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("faq_deleted_txt");

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/faq-category');

	

	}

	/* Faqs module }*/

	

	/* subscribers module {  */

	public function subscriberslistAction(){

		$pageHeading="Newsletter Subscribers";

		$subscriber_id = $this->params()->fromRoute('subscriber');

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate($pageHeading));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate($pageHeading));

	 	return new ViewModel(array('page_icon'=>'fa fa-question-circle','pageHeading'=>$pageHeading,'subscriber_id'=>$subscriber_id));

   	}

	

	public function getsubscriberslistAction()

	{

		$dbAdapter = $this->Adapter;

		$subscriber_id = $this->params()->fromRoute('subscriber');

		$aColumns = array('newsletter_sub_id','newsletter_sub_name','newsletter_sub_email','newsletter_sub_date');



		$sIndexColumn = 'newsletter_sub_id';

		$sTable = T_NEWSSUBSCRIBERS;

  		

		/*Table Setting*/{

		

		/* Paging */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "".$aColumns[$i]." LIKE '%".trim(addslashes($_GET["sSearch"]))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE  ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		}/* End Table Setting */

		if($subscriber_id!=''){

			$subscriber_id=@myurl_decode($subscriber_id);

			if($sWhere==''){

				$sWhere="where newsletter_sub_id='".$subscriber_id."'";

			}else{

			$sWhere=" and newsletter_sub_id='".$subscriber_id."'";

			}

		}

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit";      

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/* Output */

		 

 		$output = array(

			"iTotalRecords" => $iTotal,

			"iTotalDisplayRecords" => $iFilteredTotal,

			"aaData" => array()

		);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

 			$row[] = $j;

			$row[] = $row1['newsletter_sub_name'];	

  			$row[] = $row1['newsletter_sub_email'];

			$row[] = $row1['newsletter_sub_date'];

			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

	}

	/* subscribers module }*/

	/* slider module { */

	public function sliderAction(){

		$sliderHeading=$this->layout()->translator->translate("Home Page Slider");

		$this->layout()->setVariable('pageHeading', $sliderHeading);

		$this->layout()->setVariable('pageDescription', $sliderHeading);

	 	return new ViewModel(array('page_icon'=>'fa fa-question-circle','pageHeading'=>$sliderHeading));

   	}

	

	public function getsliderAction(){

  			

		$dbAdapter = $this->Adapter;

  		$slidetype=$this->params()->fromRoute('slidetype');

		

		$aColumns = array('slider_id','slider_id','slider_image','slider_status','slider_added_date','slider_type');



		$sIndexColumn = 'slider_id';

		$sTable = T_SLIDER;

  		

		/*Table Setting*/{

		

		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		

		}/* End Table Setting */

		

		if($sWhere==''){$sWhere='where 1';}

		if($slidetype=='1'){

			$sWhere.=' and slider_type="home"';

		}elseif($slidetype=='2'){

			$sWhere.=' and slider_type="lounge"';

		}

		

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit"; 

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/*

		 * Output

		 */

		 

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		

		

		foreach($qry as $row1)

		{

			$row=array();

		

 			$row[] =$j;

		

			

			$row[]='<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';

			$row[]="<img src='".HTTP_SLIDER_IMAGES_PATH."/160/".$row1['slider_image']."' class='img-responsive'  />";

			$checkClass="";

			if($row1['slider_status']=='1'){$checkClass="checked";} 

			$row[]="<input type='checkbox' class='js-switch status-".(int)$row1['slider_status']."' ".$checkClass." id='".$sTable."-".$row1[$sIndexColumn]."'  onChange='globalStatus(this);' />";

			

			$row[]=date('d M, Y h:i a',strtotime($row1['slider_added_date']));

			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/addslider/'.myurl_encode($row1['slider_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

  			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

 	} 

	

	public function addsliderAction(){

		

		$slider_id = $this->params()->fromRoute('slider_id');

	    $form = new StaticForm($this->layout()->translator);

		if($slider_id!=''){

			$slider_id=myurl_decode($slider_id);

			$form->slider(array('id'=>$slider_id));

		} else {

			$form->slider();

		}$data=array();

		$PageHeading=$this->layout()->translator->translate("add_slider_txt");

		if($slider_id!=''){

			$PageHeading=$this->layout()->translator->translate("edit_slider_txt");

			

		$data= $this->SuperModel->Super_Get(T_SLIDER, "slider_id=:slideid and slider_type=:slidetype","fetch",array("warray"=>array("slideid"=>$slider_id,"slidetype"=>"home")));

			if(empty($data)){

				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/slider');	

			}else{

				$form->populateValues($data);

			}

		}

		

        $request = $this->getRequest();

	

        if($request->isPost()) {

			$form->setData(array_merge($this->getRequest()->getFiles()->toArray(),$this->getRequest()->getPost()->toArray()));

			

				if($form->isValid()){

		   	 	

			    $Formdata = $request->getPost();

				$imagePlugin = $this->Image();		

				$files =  $this->getRequest()->getFiles()->toArray();

				$newName="";

				$invalidimage=0;

				if($files['slider_image']!=''){

					if($files['slider_image']['tmp_name']!=''){

					$finfo = finfo_open(FILEINFO_MIME_TYPE); 

					$uploaded_image_extension = getFileExtension($files['slider_image']['name']);

					$typeval=finfo_file($finfo, $files['slider_image']['tmp_name']);

					finfo_close($finfo);

					if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png'))){

					$this->adminMsgsession['errorMsg']='Please upload valid image.';

					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/slider');	

					}

				}

					

					

					$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>SLIDER_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>0,'thumbs'=>array(

												 	'160'=>array(

														 "width"=>160,

														 "height"=>160,

													  ),

													 

												 	'300'=>array(

														 "width"=>300,

														 "height"=>300,

													  ),

													  

						)));

					if($is_uploaded_icon->success=="1" && $is_uploaded_icon->media_path!=''){

						$newName=$is_uploaded_icon->media_path;

						if($slider_id!=''){

							

							$imagePlugin->universal_unlink($data['slider_image'],array("directory"=>SLIDER_IMAGES_PATH));

							$imagePlugin->universal_unlink($data['slider_image'],array("directory"=>SLIDER_IMAGES_PATH.'/160'));

							$imagePlugin->universal_unlink($data['slider_image'],array("directory"=>SLIDER_IMAGES_PATH.'/300'));

						}

					}

					else

					{//$newName=$data['slider_image'];

						$invalidimage=1;

						$this->adminMsgsession['errorMsg']='Invalid image';	

					}	

					

				}

				else{

					$newName=$data['slider_image'];

				}

				if($invalidimage==0){

				$postData=array(

					//'slider_title'=>$Formdata['slider_title'],

					'slider_image'=>$newName,

					'slider_added_date'=>date('Y-m-d H:i:s')

				);

				

				//unset($Formdata['bttnsubmit']);

				if($slider_id!=''){

					$isInserted=$this->SuperModel->Super_Insert(T_SLIDER,$postData,"slider_id='".$slider_id."'");

				}else{

					$isInserted=$this->SuperModel->Super_Insert(T_SLIDER,$postData);

				}

				

				if(!empty($isInserted)){

					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("slider_saved_txt");

				}else{

					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

				}

				return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/slider');

				}else{

							$this->adminMsgsession['errorMsg']='Invalid Image';

						

						}

			}

			else{

					$this->adminMsgsession['errorMsg'] = "Please Check Information Again";	

				}

        }

		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('backUrl', 'slider');

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'pageHeading'=>$PageHeading,'data'=>$data));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	

		

		

	}
	
	
	public function removephotogalleryAction() {
		$imagePlugin = $this->Image();

		$request = $this->getRequest();

		if ($request->isPost()) {

			 $del = $request->getPost(T_PHOTO_GALLERY);

			 foreach($del as $key=>$ids)

			 {  

			 	$isgetData=$this->SuperModel->Super_Get(T_PHOTO_GALLERY,'photogallery_id ="'.$ids.'"');	

				if($isgetData['photogallery_image']!=''){

					$imagePlugin->universal_unlink($isgetData["photogallery_image"],array("directory"=>SLIDER_IMAGES_PATH));

					$imagePlugin->universal_unlink($isgetData["photogallery_image"],array("directory"=>SLIDER_IMAGES_PATH.'/160'));

					$imagePlugin->universal_unlink($isgetData["photogallery_image"],array("directory"=>SLIDER_IMAGES_PATH.'/300'));

				}

				

				$isdeleted=$this->SuperModel->Super_Delete(T_PHOTO_GALLERY,'photogallery_id ="'.$ids.'"');	

			 } 

		}

		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Photo successfully deleted");

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/photo-gallery');
	}
	

	public function removesliderAction(){

		$imagePlugin = $this->Image();

		$request = $this->getRequest();

			

		if ($request->isPost()) {

			 $del = $request->getPost(T_SLIDER);

			 foreach($del as $key=>$ids)

			 {  

			 	$isgetData=$this->SuperModel->Super_Get(T_SLIDER,'slider_id ="'.$ids.'"');	

				if($isgetData['slider_image']!=''){

					$imagePlugin->universal_unlink($isgetData["slider_image"],array("directory"=>SLIDER_IMAGES_PATH));

					$imagePlugin->universal_unlink($isgetData["slider_image"],array("directory"=>SLIDER_IMAGES_PATH.'/160'));

					$imagePlugin->universal_unlink($isgetData["slider_image"],array("directory"=>SLIDER_IMAGES_PATH.'/300'));

				}

				

				$isdeleted=$this->SuperModel->Super_Delete(T_SLIDER,'slider_id ="'.$ids.'"');	

			 } 

		}

		$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("slider_deleted_txt");

		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/slider');

		

	}

	/* slider module  } */

	/* Home module { */

	public function homepageAction(){

		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");

		$contentData=$this->SuperModel->Super_Get(T_HOMEPAGE,"1","fetchAll");
		$homeData = $this->SuperModel->prepareselectoptionwhere(T_HOMEPAGE,"home_key","home_content","1");  

		$form = new StaticForm($this->layout()->translator);
		$form->homepage($contentData);	
		$PageHeading=$this->layout()->translator->translate("homepage_txt");

		$allData=array();

		foreach($homeData as $key=>$allContent){

			$allData[$key]=str_ireplace(array('{img_url}','{site_path}','{price_symbol}','{site_images}','{site_link}'),array(HTTP_IMG_PATH,APPLICATION_URL,PRICE_SYMBOL,HTTP_IMG_PATH,APPLICATION_URL),$allContent);

		}

		$form->populateValues($allData);

        $request = $this->getRequest();

	

        if($request->isPost()) {

				$Formdata = $request->getPost();

		   	 	$form->setData($Formdata);

			    

				if($form->isValid()){
				    
			    $Formdata = $form->getData(); 
				$imagePlugin = $this->Image();		

				$files =  $this->getRequest()->getFiles()->toArray();

				$newName="";

				 

				if(!empty($files)){ 

					foreach($files as $fkey=>$fValue){

							if($fValue['tmp_name']!=''){

							

								$finfo = finfo_open(FILEINFO_MIME_TYPE); 

								$uploaded_image_extension = getFileExtension($fValue['name']);

								$typeval=finfo_file($finfo, $fValue['tmp_name']);

			

									finfo_close($finfo);

								if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png'))){

							$this->adminMsgsession['errorMsg']='Please upload valid image.';

							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/homepage');	

									}

							

							}

					}

					$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>SLIDER_IMAGES_PATH.'/',"files_array"=>$files,"multiple"=>1,"thumb"=>true));

					

						if(!empty($is_uploaded_icon->media_path)){

							
                            \Tinify\setKey(TINY_KEY);
							foreach($is_uploaded_icon->media_path as $mkey=>$mvalue){

							$newName=$mvalue['media_path'];
                            if(file_exists(SLIDER_IMAGES_PATH."/".$newName)) {	
								$source = \Tinify\fromFile(SLIDER_IMAGES_PATH."/".$newName);
								$source->toFile(SLIDER_IMAGES_PATH."/".$newName);
							}
							if($newName!=''){

							$imagePlugin->universal_unlink($homeData[$mkey],array("directory"=>SLIDER_IMAGES_PATH)); $imagePlugin->universal_unlink($homeData[$mkey],array("directory"=>SLIDER_IMAGES_PATH.'/60')); 

							 $imagePlugin->universal_unlink($homeData[$mkey],array("directory"=>SLIDER_IMAGES_PATH.'/160')); 

							 $imagePlugin->universal_unlink($homeData[$mkey],array("directory"=>SLIDER_IMAGES_PATH.'/thumb')); 								$imgInserted=$this->SuperModel->Super_Insert(T_HOMEPAGE,array('home_content'=>$newName),"home_key='".$mkey."'"); 

							 unset($Formdata[$mkey]);

							}

							}

						}

					

				}

				unset($Formdata["post_csrf"]);unset($Formdata["bttnsubmit"]);

				


				foreach($Formdata as $key=>$allContent){ 

					if($allContent!=''){
					$allContent = xss_clean($allContent);	
					$isInserted=$this->SuperModel->Super_Insert(T_HOMEPAGE,array('home_content'=>$allContent),"home_key='".$key."'");

					}

				}

				if(!empty($isInserted)){

					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("content_saved_txt");

				}else{

					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

				}

					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/homepage');	

				}else{

					$this->adminMsgsession['errorMsg'] = "Please Check Information Again";	

				}

        }

		$this->layout()->setVariable('pageHeading',$PageHeading);

		$this->layout()->setVariable('pageHeading', $PageHeading);

		$this->layout()->setVariable('pageDescription', $PageHeading);

		$view = new ViewModel(array('form'=>$form,'pageHeading'=>$PageHeading,'data'=>$homeData,"contentData"=>$contentData));

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	

		

		

	}

	/* }*/

	



	/* email module { */

	public function emailtemplateAction(){

		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("email_tmp_txt"));

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate("email_tmp_txt"));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate("email_tmp_txt"));

		$view = new ViewModel(array('page_icon'=>'fa fa-envelope','pageHeading'=>$this->layout()->translator->translate("email_tmp_txt")));

		return $view;

	}

	

	public function getemailtemplateAction()

	{

  			

		$dbAdapter = $this->Adapter;

		$aColumns = array('emailtemp_key','emailtemp_title','emailtemp_subject_'.$_COOKIE['currentLang'],'emailtemp_modified','emailtemp_content_'.$_COOKIE['currentLang']);



		$sIndexColumn = 'emailtemp_key';

		$sTable = T_EMAIL;

  		

		/*Table Setting*/{

		

		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

				}

			}

			

			$sOrder = substr_replace( $sOrder, "", -2 );

			if ( $sOrder == "ORDER BY" )

			{

				$sOrder = "";

			}

		}

		

		

		$sWhere = "";

		if ( isset($_GET['sSearch']) and $_GET['sSearch'] != "" )

		{

			$sWhere = "WHERE (";

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE  ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		

		}/* End Table Setting */

		

		

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit";      

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/*

		 * Output

		 */

		 

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

 			$row[] =$j;

  			$row[]=$row1['emailtemp_title'];

			$row[]=$row1['emailtemp_subject_'.$_COOKIE['currentLang']];

			$row[]=date('d M, Y h:i a',strtotime($row1['emailtemp_modified']));

			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/editemailtemplate/'.$row1['emailtemp_key'].'">

			<span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

 	} 

	

	public function editemailtemplateAction(){

		$edit_id = $this->params()->fromRoute('emailtemp_key');

		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("edit_tmp_txt"));

		if(!$edit_id){

			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/emailtemplate');	

		}

		

        $data=$this->SuperModel->Super_Get(T_EMAIL,'emailtemp_key="'.$edit_id.'"','fetchAll');

		if(!$data){

			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/emailtemplate');	

		}



        $form = new StaticForm($this->layout()->translator);

		$form->emailtemplates();

		

		

		foreach($data as $key=>$data)

		{

			$form->get('emailtemp_title')->setValue($data['emailtemp_title']);

			$form->get('emailtemp_subject_en')->setValue($data['emailtemp_subject_en']);	

			$ext = getFileExtension($this->layout()->SITE_CONFIG['site_logo']);

			$logo_url = HTTP_IMG_PATH.'/logo.'.$ext;

			$data['emailtemp_content_en']=str_ireplace(array('{site_images}','{site_link}','{site_logo}'),array(HTTP_IMG_PATH,APPLICATION_URL, $logo_url),$data['emailtemp_content_en']);

			$form->get('emailtemp_content_en')->setValue($data['emailtemp_content_en']);

		}

		

        $request = $this->getRequest();

	

        if($request->isPost()) {

            $form->setData($request->getPost());

			

			

            if($form->isValid()){

              

			    $Formdata = $form->getData();

				

				unset($Formdata['Submit']);	

				$ext = getFileExtension($this->layout()->SITE_CONFIG['site_logo']);
				$logo_url = HTTP_IMG_PATH.'/logo.'.$ext;

				$Formdata['emailtemp_content_en'] = str_ireplace(array(HTTP_IMG_PATH,APPLICATION_URL,$logo_url),array('{site_images}','{site_link}','{site_logo}'),$Formdata['emailtemp_content_en']);
				$Formdata['emailtemp_content_en'] = xss_clean($Formdata['emailtemp_content_en']);
				//$Formdata['emailtemp_content_en']=str_replace(APPLICATION_URL,"{site_link}",$Formdata['emailtemp_content_en']);

				$Formdata['emailtemp_modified'] = date('Y-m-d H:i:s');

				unset($Formdata['bttnsubmit']);

				unset($Formdata["post_csrf"]);

				

				$isInserted=$this->SuperModel->Super_Insert(T_EMAIL,$Formdata,"emailtemp_key='".$edit_id."'");

				

				if(!empty($isInserted)){

					$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("temp_saved_txt");

				}else{

					$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

				}

            }else{

				

				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");

			}

			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/emailtemplate');	

        }

		$view = new ViewModel(array('form'=>$form,'page_icon'=>'fa fa-edit','pageHeading'=>$this->layout()->translator->translate("edit_temp_txt")));

		$this->layout()->setVariable('backUrl', 'emailtemplate');

		$view->setTemplate('admin/admin/add.phtml');

		return $view;

	}

	/* email module } */
	
	/* manage wicked shop */
	public function wickedshopAction() {
		$type = $this->params()->fromRoute('type');

		$getCgroup="";

		$dbAdapter =$this->Adapter;

		$this->layout()->setVariable('pageHeading', $this->layout()->translator->translate("Manage Wicked Shop"));

		$this->layout()->setVariable('pageDescription', $this->layout()->translator->translate("Manage Wicked Shop"));
		$wicked_Data = $this->SuperModel->Super_Get(T_WICKED,"1","fetch");

		if(empty($wicked_Data)){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL);
		}
		$form = new StaticForm($this->layout()->translator);
		$form->wickedshop($configData);	

		$homeVideo='';
		foreach($configData as $key=>$data){
			$form->get($data['config_key'])->setValue($data['config_value']);
		}

		$request = $this->getRequest();

	    if($request->isPost()) {

           $form->setData($request->getPost());

			

            if ($form->isValid()) { 

                $Formdata = $form->getData();


				$site_logo_error = 0;
				$imagePlugin = $this->Image();				
				$files =  $this->getRequest()->getFiles()->toArray();

				// prd($files);

				/* Start site_logo section */
				if(isset($files['wicked_banner']['name']) and $files['wicked_banner']['name']!=''){
					if($files['wicked_banner']['tmp_name']!=''){
						$allowed_exts = explode(',', IMAGE_VALID_EXTENTIONS);
						$ext = getFileExtension($files['wicked_banner']['name']);
						if(!in_array($ext, $allowed_exts)){
							$site_logo_error = 1;
							$this->adminMsgsession['errorMsg'] = 'Please upload valid image for site logo. Allowed extensions are jpg, jpeg and png.';
						}
						if (strpos(file_get_contents($files['wicked_banner']['tmp_name']), '<?php') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
					}
				}
				else{
					$Formdata['wicked_banner'] = $wicked_Data["wicked_banner"];
				}
				/* end site_logo section */
				/* upload files code */
				if(!empty($files)){ 
					if($site_logo_error==0 and isset($files['wicked_banner']['name']) and !empty($files['wicked_banner']['name'])){
						// site logo
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH.'/logo',"files_array"=>$files,"multiple"=>false,"thumbs"=>false));
						$Formdata["wicked_banner"] = $is_uploaded_icon->media_path;
					} 
				}

				unset($Formdata['bttnsubmit']);
				unset($Formdata['post_csrf']);
				$is_error = 0;
                if($is_error==0 and $site_logo_error==0){
					$this->SuperModel->Super_Insert(T_WICKED,$Formdata,'wicked_id="1"');
                	$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("Wicked banner saved successfully.");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/manage-wickedshop');
				} 
			}
        }
		
		$view = new ViewModel(array('form'=>$form,'pageHeading'=>$this->layout()->translator->translate("Wicked Shop"),'wickedData'=>$wicked_Data));		
		$view->setTemplate('admin/admin/add.phtml');		
		
		return $view;
	}
	
	/* config {*/

	public function indexAction()

    {
		$type = $this->params()->fromRoute('type');

		$getCgroup="";

		$dbAdapter =$this->Adapter;

		switch($type){

			case 1: $configWhere="config_group=:congroup";$configText=$this->layout()->translator->translate("site_confg_txt");$configPath='settings';$getCgroup='SITE_CONFIG';break;

			case 2: $configWhere="config_group=:congroup";$configText=$this->layout()->translator->translate("payment_confg_txt");$configPath='config-payment';$getCgroup='SITE_PAYMENT';break;

			case 3: $configWhere="config_group=:congroup";$configText=$this->layout()->translator->translate("social_confg_txt");$configPath='social';$getCgroup='SITE_SOCIAL';break;

			//case 4: $configWhere="config_group='SITE_TWILIO'";$configText=$this->layout()->translator->translate("sms_confg_txt");$configPath='sms';break;

			//case 5: $configWhere="config_group='SITE_OTHER'";$configText=$this->layout()->translator->translate("other_confg_txt");$configPath='other';break;

		}

		$this->layout()->setVariable('pageHeading', $configText);

		$this->layout()->setVariable('pageDescription', $configText);

		

		$configData = $this->SuperModel->Super_Get(T_CONFIG,$configWhere,"fetchAll",array("warray"=>array("congroup"=>$getCgroup),"order"=>"config_displaying_order asc"));

		// prd($configData);

		if(empty($configData)){
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL);
		}

		$currData = $this->SuperModel->prepareselectoptionwhere(T_CURRENCY,"currency_id","currency_code","1");
		$form = new StaticForm($this->layout()->translator);
		$form->siteconfig($configData,$currData);	

		$homeVideo='';
		foreach($configData as $key=>$data){
			$form->get($data['config_key'])->setValue($data['config_value']);
		}

		$request = $this->getRequest();

	    if($request->isPost()) {

           $form->setData($request->getPost());

			

            if ($form->isValid()) { 

                $Formdata = $form->getData();

				

				//if(isset($Formdata['site_address'])){

					//$locData=$this->SuperModel->postData($Formdata['site_address'],$this->SITE_CONFIG);

					//$Formdata['site_latitude']=$locData['latitude'];

					//$Formdata['site_longitude']=$locData['longitude'];

				//}

				/*$imagePlugin = $this->Image();				
				$files =  $this->getRequest()->getFiles()->toArray();

				$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH.'/logo',"files_array"=>$files,"multiple"=>1,"thumb"=>false));
				$mediaPath=$is_uploaded_icon->media_path;

				if(empty($mediaPath['site_logo'])){
					$Formdata['site_logo'] = $this->SITE_CONFIG['site_logo'];

				} else if($mediaPath['site_logo']!=''){
					$Formdata['site_logo'] = $mediaPath['site_logo']['name'];
				}*/


				$site_logo_error = 0;
				$site_logo_mobile_error = 0;
				$site_favicon_error = 0;
				$imagePlugin = $this->Image();				
				$files =  $this->getRequest()->getFiles()->toArray();

				// prd($files);

				/* Start site_logo section */
				if(isset($files['site_logo']['name']) and $files['site_logo']['name']!=''){
					if($files['site_logo']['tmp_name']!=''){
						$allowed_exts = explode(',', IMAGE_VALID_EXTENTIONS);
						$ext = getFileExtension($files['site_logo']['name']);
						if(!in_array($ext, $allowed_exts)){
							$site_logo_error = 1;
							$this->adminMsgsession['errorMsg'] = 'Please upload valid image for site logo. Allowed extensions are jpg, jpeg and png.';
						}
						if (strpos(file_get_contents($files['site_logo']['tmp_name']), '<?php') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
	
						if (strpos(file_get_contents($files['site_logo']['tmp_name']), '<?=') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
	
						if (strpos(file_get_contents($files['site_logo']['tmp_name']), '<? ') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
					}
				}
				else{
					$Formdata['site_logo'] = $this->layout()->SITE_CONFIG['site_logo'];
				}
				/* end site_logo section */

				/* Start site_favicon section */
				if(isset($files['site_favicon']['name']) and $files['site_favicon']['name']!=''){ 
					if($files['site_favicon']['tmp_name']!=''){ 
						$allowed_exts = explode(',', IMAGE_VALID_EXTENTIONS);
						$ext = getFileExtension($files['site_favicon']['name']);
						
						if(!in_array($ext, $allowed_exts)){ 
							
							$site_favicon_error = 1;
							$this->adminMsgsession['errorMsg'] = 'Please upload valid image for favicon icon. Allowed extensions are jpg, jpeg and png.';
						}
						if (strpos(file_get_contents($files['site_favicon']['tmp_name']), '<?php') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
	
						if (strpos(file_get_contents($files['site_favicon']['tmp_name']), '<?=') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
	
						if (strpos(file_get_contents($files['site_favicon']['tmp_name']), '<? ') !== false) 
						{
							$this->adminMsgsession[ 'errorMsg' ] = "File is infected";
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
						}
					}
				}
				else{
					$Formdata['site_favicon'] = $this->layout()->SITE_CONFIG['site_favicon'];
				}

				/* upload files code */
				if(!empty($files)){ 
					if($site_logo_error==0 and isset($files['site_logo']['name']) and !empty($files['site_logo']['name'])){
						// site logo
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH.'/logo',"files_array"=>array('site_logo'=>$files['site_logo']),"multiple"=>true,"thumbs"=>false));
						if(isset($is_uploaded_icon->media_path)){
							foreach($is_uploaded_icon->media_path as $key=>$allVal){
								if(!empty($allVal['media_path'])){
									$newName = $allVal['media_path']; 
									$Formdata[$key] = $newName;

									$ext = getFileExtension($newName);
									@copy(PROFILE_IMAGES_PATH.'/logo/'.$newName, LOGO_IMAGES_PATH.'/logo.'.$ext);
									$imagePlugin->universal_unlink($this->layout()->SITE_CONFIGS['site_logo'],array("directory"=>PROFILE_IMAGES_PATH.'/logo')); 
									unset($_FILES['site_logo']);
								}
							}
						}
					}


					if($site_logo_mobile_error==0 and isset($files['site_logo_mobile']['name']) and !empty($files['site_logo_mobile']['name'])){
						// site logo mobile
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH.'/logo',"files_array"=>array('site_logo_mobile'=>$files['site_logo_mobile']),"multiple"=>true,"thumbs"=>false));
						if(isset($is_uploaded_icon->media_path)){
							foreach($is_uploaded_icon->media_path as $key=>$allVal){
								if(!empty($allVal['media_path'])){
									$newName = $allVal['media_path']; 
									$Formdata[$key] = $newName;

									$ext = getFileExtension($newName);
									@copy(PROFILE_IMAGES_PATH.'/logo/'.$newName, LOGO_IMAGES_PATH.'/logo_mobile.'.$ext);
									$imagePlugin->universal_unlink($this->layout()->SITE_CONFIGS['site_logo_mobile'],array("directory"=>PROFILE_IMAGES_PATH.'/logo')); 
									unset($_FILES['site_logo_mobile']);
								}
							}
						}
					} 


					if($site_favicon_error==0 and isset($files['site_favicon']['name']) and !empty($files['site_favicon']['name'])){
						// favicon icon
						$is_uploaded_icon = $imagePlugin->universal_upload(
							
							array(
								"directory"=>FAVICON_IMAGES_PATH,
								"files_array"=>array('site_favicon'=>$files['site_favicon']),
								"multiple"=>true, 
								'thumbs'=>array(
									'16'=>array(
										"width"=>16,
										"height"=>16,
										"crop"=>true,
										"ratio"=>true
									)
								)
						));
						// echo 'testing'.rand();exit;
						if(isset($is_uploaded_icon->media_path)){
							foreach($is_uploaded_icon->media_path as $key=>$allVal){
								if(!empty($allVal['media_path'])){
									
									$newName = $allVal['media_path']; 
									$Formdata[$key] = $newName;
									$imagePlugin->universal_unlink($this->layout()->SITE_CONFIGS['site_favicon'],array("directory"=>FAVICON_IMAGES_PATH)); 
									unset($_FILES['site_favicon']);
								}
							}
						}

					}
				}
				// prd($Formdata);
				/* end site_favicon section */

				unset($Formdata['bttnsubmit']);
				
				$is_error = 0;
				foreach($Formdata as $key=>$data)
				{
					if($key=="site_commission" && ($data > 99 || $data < 1)){
						$is_error=1;
						$this->adminMsgsession['errorMsg']="Invalid commission value";
						break;

					} else {
						$update_arr['config_value']=preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
						$configData=$this->SuperModel->Super_Insert(T_CONFIG,$update_arr,'config_key="'.$key.'"');
					}
				}
				
				
                if($is_error==0 and $site_logo_error==0 and $site_logo_mobile_error==0 and $site_favicon_error==0){
                	$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("setting_saved_txt");
					return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/'.$configPath);
				} 
			}
        }
		
		$view = new ViewModel(array('form'=>$form,'pageHeading'=>$this->layout()->translator->translate("confg_txt")));		
		$view->setTemplate('admin/admin/add.phtml');		
		
		return $view;

	}

	/* config module  } */

	/* Pages module  { */

	public function pagesAction()

    {

		

		 $this->layout()->setVariable('pageHeading',"Pages");

		 return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));

    }

	public function getpagesAction(){

  			

		$dbAdapter = $this->Adapter;

  		$page_type = $this->params()->fromRoute('page');

	

		$aColumns = array('page_id','page_title_'.$_COOKIE['currentLang'],'page_updated','page_content_'.$_COOKIE['currentLang']);

		$sIndexColumn = 'page_id';

		$sTable = T_PAGES;

  		

		/*Table Setting*/{

		

		/* 

		 * Paging

		 */

		$sLimit = "";

		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )

		{

			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );

		}

		

		/*

		 * Ordering

		 */

		$sOrder = "";

		if ( isset( $_GET['iSortCol_0'] ) )

		{

			$sOrder = "ORDER BY  ";

			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )

			{

				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )

				{

					$sOrder .= "".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".

						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";

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

			for ( $i=0 ; $i<count($aColumns) ; $i++ )

			{

				$sWhere .= "LOWER(".$aColumns[$i].") LIKE '%".strtolower(trim(addslashes($_GET["sSearch"])))."%' OR ";

			}

			$sWhere = substr_replace( $sWhere, "", -3 );

			$sWhere .= ')';

		}

		

		/* Individual column filtering */

		for ( $i=0 ; $i<count($aColumns) ; $i++ )

		{

			if ( isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '' )

			{

				if ( $sWhere == "" )

				{

					$sWhere = "WHERE ";

				}

				else

				{

					$sWhere .= " AND ";

				}

				$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";

			}

		}

		

		

		}/* End Table Setting */

		

		if($page_type==''){

			$page_type='0';

		}

		if($sWhere==''){

			$sWhere='where page_type="'.$page_type.'"';	

		}else{

			$sWhere.=' and page_type="'.$page_type.'"';

		}

		

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit"; //echo $sQuery;die;

		$results = $dbAdapter->query($sQuery)->execute();

		$qry=$results->getResource()->fetchAll();

		

		

 		/* Data set length after filtering */

		$sQuery = "SELECT FOUND_ROWS() as fcnt";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$aResultFilterTotal=$results->getResource()->fetchAll();

		$iFilteredTotal = $aResultFilterTotal[0]['fcnt'];

		

		

		/* Total data set length */

		$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as cnt FROM $sTable ";

		

		$results = $dbAdapter->query($sQuery)->execute();

		$rResultTotal=$results->getResource()->fetchAll();

		$iTotal = $rResultTotal[0]['cnt'];

		

		/*

		 * Output

		 */

		 

 		$output = array(

 				"iTotalRecords" => $iTotal,

				"iTotalDisplayRecords" => $iFilteredTotal,

				"aaData" => array()

			);

		

		$j=1;

		foreach($qry as $row1)

		{

			$row=array();

		

 			$row[] =$j;

  			$row[]=$row1['page_title_'.$_COOKIE['currentLang']];

			$row[]=date('d M, Y h:i a',strtotime($row1['page_updated']));

			$row[] =  '<a href="'.ADMIN_APPLICATION_URL.'/editpages/'.myurl_encode($row1['page_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

  			$output['aaData'][] = $row;

			$j++;

		}	

		

		echo json_encode( $output );

		exit();

 	} 

	public function editpagesAction()
	{
		$this->layout()->setVariable('backUrl', 'pages');
		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("edit_page_txt"));
		$page_id = $this->params()->fromRoute('id');
		
		if($page_id==''){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/pages');
		}

		$page_id=myurl_decode($page_id);
		
		$data=$this->SuperModel->Super_Get(T_PAGES,'page_id=:pageids','fetch',array("warray"=>array("pageids"=>$page_id)));

		if(empty($data)){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/pages');
		}

		$contentdata = $this->SuperModel->Super_Get( T_PAGE_CONTENT, 'page_content_page_id=:pageids', 'fetchAll', array("warray" => array("pageids" => $page_id )));
		
        $form = new StaticForm($this->layout()->translator); 
		$form->pages($page_id, $contentdata);
		$form->get('page_title_en')->setValue($data['page_title_en']);
		$form->get('page_meta_keyword')->setValue($data['page_meta_keyword']);
		$form->get('page_meta_desc')->setValue($data['page_meta_desc']);
		$form->get('page_meta_title')->setValue($data['page_meta_title']);
		
		foreach ( $contentdata as $value ) {

			$form->get( $value[ 'page_content_section_key' ] )->setValue( $value[ 'section_content' ] );

		}
		
		$page_content=$data['page_content_en'];

		$page_content=str_ireplace(array('{last_updated}','{img_url}','{site_path}','{price_symbol}','{site_images}','{site_link}'),array(date("d F, Y ",strtotime($data['page_updated'])),HTTP_IMG_PATH,APPLICATION_URL,PRICE_SYMBOL,HTTP_IMG_PATH,APPLICATION_URL),$page_content);
		if($page_id!=20 && $page_id!=17){
			$form->get('page_content_en')->setValue($page_content);
        }
		
        $request = $this->getRequest();

        if($request->isPost()) {
            $form->setData($request->getPost());
			if ( $form->isValid() ) {
				$Formdata = $form->getData();

				$page_image_error = 0;
				$imagePlugin = $this->Image();
				$files = $this->getRequest()->getFiles()->toArray();
				$imagePlugin = $this->Image();
				if ( $page_image_error == 0 ) {

					$Formdata[ 'page_updated' ] = date( "Y-m-d H:i:s" );

					unset( $Formdata[ 'bttnsubmit' ] );
					unset( $Formdata[ 'post_csrf' ] );

					$data_to_update = array();
					$data_to_update['page_title_en' ]		= $Formdata['page_title_en'];
					$data_to_update['page_meta_keyword' ]	= $Formdata['page_meta_keyword'];
					$data_to_update['page_meta_title' ]		= $Formdata['page_meta_title'];
					$data_to_update['page_meta_desc' ]		= $Formdata['page_meta_desc'];
					
					if($page_id!=17 && $page_id!=20){
						$data_to_update['page_content_en']		= xss_clean($Formdata['page_content_en']);
					}
					
					$data_to_update['page_updated']			= $Formdata['page_updated'];
					
					unset( $Formdata[ 'page_title_en' ], $Formdata[ 'page_meta_keyword' ], $Formdata[ 'page_meta_title' ], $Formdata[ 'page_meta_desc' ], $Formdata[ 'page_content_en' ] );
					
					$isInserted = $this->SuperModel->Super_Insert( T_PAGES, $data_to_update, "page_id='" . $page_id . "'" );
					//prd($isInserted);
					
					/*page content updation*/

					$is_uploaded = $imagePlugin->universal_upload( array( "directory" => SERVICE_IMAGES_PATH, "files_array" => $files, "url" => HTTP_SERVICE_IMAGES_PATH, "crop" => false, "multiple" => true ) );
				

					if ( isset( $is_uploaded->media_path ) && !empty( $is_uploaded->media_path ) ) {
						foreach ( $is_uploaded->media_path as $key => $allVal ) {


							if ( !empty( $allVal[ 'media_path' ] ) ) {
								$image = $allVal[ 'media_path' ];

								$Formdata[ $key ] = $image;

							}

						}
					}
					foreach ( $Formdata as $key => $value ) {

						if ( $value == '' ) {

							unset( $Formdata[ $key ] );

						}
					}
					//prd($Formdata);

					foreach($Formdata as $key => $value){
						$update = array();
						$value = xss_clean($value);
						$update[ 'section_content' ] = $value;
						$isInserted1 = $this->SuperModel->Super_Insert( T_PAGE_CONTENT, $update, "page_content_page_id='" . $page_id . "' and page_content_section_key='" . $key . "'" );
					}
					
					if ( !empty( $isInserted ) ) {
						$this->adminMsgsession[ 'successMsg' ] = $this->layout()->translator->translate( "page_saved_txt" );
						return $this->redirect()->tourl( ADMIN_APPLICATION_URL . '/pages' );

					} else {
						$this->adminMsgsession[ 'errorMsg' ] = $this->layout()->translator->translate( "check_info_txt" );
					}

				} else {

				}
			} else {
				$this->adminMsgsession[ 'errorMsg' ] = $this->layout()->translator->translate( "check_info_txt" );
			}
        }

		$icon='fa fa-edit';
		$pag_title=$this->layout()->translator->translate("edit_page_txt");
		$view = new ViewModel(array('form'=>$form,'page_icon'=>$icon,'pageHeading'=>$pag_title,"page_id"=>$page_id,'data'=>$data,'content' => $contentdata));
		$view->setTemplate('admin/admin/add.phtml');
		
		return $view;		
	}

	public function editpagesOldAction()
	{
		$this->layout()->setVariable('backUrl', 'pages');
		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("edit_page_txt"));
		$page_id = $this->params()->fromRoute('id');
		
		if($page_id==''){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/pages');
		}
		$page_id=myurl_decode($page_id);
		$data=$this->SuperModel->Super_Get(T_PAGES,'page_id=:pageids','fetch',array("warray"=>array("pageids"=>$page_id)));
		if(empty($data)){
			$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("no_record_found");
			return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/pages');
			
		}

	
        $form = new StaticForm($this->layout()->translator);
		$form->pages($page_id);

		$form->get('page_title_en')->setValue($data['page_title_en']);
		$form->get('page_meta_keyword')->setValue($data['page_meta_keyword']);
		$form->get('page_meta_desc')->setValue($data['page_meta_desc']);
		$form->get('page_meta_title')->setValue($data['page_meta_title']);

		$page_content=$data['page_content_en'];

		$page_content=str_ireplace(array('{last_updated}','{img_url}','{site_path}','{price_symbol}','{site_images}','{site_link}'),array(date("d F, Y ",strtotime($data['page_updated'])),HTTP_IMG_PATH,APPLICATION_URL,PRICE_SYMBOL,HTTP_IMG_PATH,APPLICATION_URL),$page_content);

		$form->get('page_content_en')->setValue($page_content);
        $request = $this->getRequest();

        if($request->isPost()) {
            $form->setData($request->getPost());
            if($form->isValid()){
                $Formdata = $form->getData();
				
				$Formdata['page_content_en']=str_ireplace(array(date("d F, Y ",strtotime($data['page_updated'])),HTTP_IMG_PATH,APPLICATION_URL,PRICE_SYMBOL,HTTP_IMG_PATH,APPLICATION_URL),array('{last_updated}','{img_url}','{site_path}','{price_symbol}','{site_images}','{site_link}'),$Formdata['page_content_en']);

				$page_image_error = 0;
				$imagePlugin = $this->Image();				
				$files =  $this->getRequest()->getFiles()->toArray();

				// prd($files);

				/* Start page_image section */
				if(isset($files['page_image']['name']) and $files['page_image']['name']!=''){
					if($files['page_image']['tmp_name']!=''){
						$allowed_exts = explode(',', BANNER_IMAGE_VALID_EXTENTIONS);
						$ext = getFileExtension($files['page_image']['name']);
						if(!in_array($ext, $allowed_exts)){
							$page_image_error = 1;
							$this->adminMsgsession['errorMsg'] = 'Please upload valid image for site logo. Allowed extensions are jpg, jpeg and png.';
						}
					}
				}
				/* end page_image section */


				/* upload files code */
				if(!empty($files)){ 
					if($page_image_error==0 and isset($files['page_image']['name']) and !empty($files['page_image']['name'])){
						// page_image
						$is_uploaded_icon = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH,"files_array"=>array('page_image'=>$files['page_image']),"multiple"=>true,"thumbs"=>false));
						if(isset($is_uploaded_icon->media_path)){
							foreach($is_uploaded_icon->media_path as $key=>$allVal){
								if(!empty($allVal['media_path'])){
									$newName = $allVal['media_path']; 
									// $Formdata[$key] = $newName;
									$Formdata['page_image'] = $newName;

									$ext = getFileExtension($newName);
									@copy(PROFILE_IMAGES_PATH.'/'.$newName, LOGO_IMAGES_PATH.'/top-bg.jpg');
									$imagePlugin->universal_unlink($this->layout()->SITE_CONFIGS['page_image'],array("directory"=>PROFILE_IMAGES_PATH)); 
									unset($_FILES['page_image']);
								}
							}
						}
					}					
				}
				// prd($Formdata);
				/* end site_favicon section */

				if($page_image_error==0){

					$Formdata['page_updated']=date("Y-m-d H:i:s");

					unset($Formdata['bttnsubmit']);unset($Formdata['post_csrf']);

					$isInserted=$this->SuperModel->Super_Insert(T_PAGES,$Formdata,"page_id='".$page_id."'");

					if(!empty($isInserted)){
						$this->adminMsgsession['successMsg']=$this->layout()->translator->translate("page_saved_txt");
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/pages');

					} else {
						$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
					}

				} else {

				}

            } else {
				$this->adminMsgsession['errorMsg']=$this->layout()->translator->translate("check_info_txt");
			}
        }

		$icon='fa fa-edit';
		$pag_title=$this->layout()->translator->translate("edit_page_txt");
		$view = new ViewModel(array('form'=>$form,'page_icon'=>$icon,'pageHeading'=>$pag_title,"page_id"=>$page_id,'data'=>$data));
		$view->setTemplate('admin/admin/add.phtml');
		return $view;		
	}
	/* Pages module  } */

}