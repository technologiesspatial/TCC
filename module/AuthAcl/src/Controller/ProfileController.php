<?php
namespace AuthAcl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use AuthAcl\Form\ProfileForm;
use AuthAcl\Form\MemberprofileForm;
use AuthAcl\Form\ChangePasswordForm;
use Zend\Db\Sql\Expression;
use PHPExcel;
use UploadHandler;
//use PHPExcel_Worksheet_Drawing;
//use PHPExcel_Writer_Excel2007;

class ProfileController extends AbstractActionController
{
    private $AbstractModel,$Adapter,$UserModel,$EmailModel,$site_configs,$authService;

    public function __construct($AbstractModel,$Adapter,$UserModel,$frontSession,$EmailModel,$site_configs,$authService)
    {	
        $this->SuperModel = $AbstractModel;
		$this->Adapter = $Adapter;
		$this->UserModel = $UserModel;		
		$this->frontSession = $frontSession;
		$this->EmailModel = $EmailModel;
		$this->loggedUser = $authService->getIdentity();
		$this->site_configs = $site_configs;
    }
	
	public function managecouponAction() {
		$view = new ViewModel();
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$_SESSION["logstat"] = '1';
		$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$view->setVariable('show', 'front_profile');
		$view->setVariable('coupon_data', $coupon_data);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
	public function refundamountAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$refund_record = $this->SuperModel->Super_Get(T_PRODORDER,"order_id =:TID and order_sellerid =:UID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["refund_order"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
			if(empty($refund_record)) {
				echo "empty";
				exit();
			}
			$refund_info = $this->SuperModel->Super_Get(T_REFUND,"refund_orderid =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["refund_order"]))));
			if(!empty($refund_info)) {
				echo "refund_issued";
				exit();
			}
			if($posted_data["partial_amount"] > ($refund_record["order_total"] - ($refund_record["order_sitefee"] + $refund_record["order_shipping"]))) {
				echo "invalid_amount";
				exit();
			}
			$refund_data["refund_type"] = $posted_data["radio-group"];
			if($refund_data["refund_type"] == '1') {
				$refund_data["refund_amount"] = $refund_record["order_total"] - ($refund_record["order_sitefee"] + $refund_record["order_shipping"]);
			} else {
				$refund_data["refund_amount"] = $posted_data["partial_amount"];
			}
			$refund_data["refund_description"] = strip_tags($posted_data["refund_message"]);
			$refund_data["refund_orderid"] = base64_decode($posted_data["refund_order"]);
			$refund_data["refund_clientid"] = $refund_record["order_clientid"];
			$refund_data["refund_sellerid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
			$refund_data["refund_status"] = '2';
			$refund_data["refund_date"] = date("Y-m-d H:i:s");
			$this->SuperModel->Super_Insert(T_REFUND,$refund_data);
			
			$mail_const_data2 = array(
					"user_name" => 'Administrator',
					"user_email" => $this->site_configs['site_email'],
					"message" => $this->loggedUser->yurt90w_client_name." has sent you the refund request of amount $".$refund_data["refund_amount"].".",
					"subject" => "Refund request by ".$this->loggedUser->yurt90w_client_name
				);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
			
			echo "success";
			exit();
		}
	}
	
	public function removeselectedAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(!empty($posted_data["checkbox-tg"])) {
				foreach($posted_data["checkbox-tg"] as $checkbox_key => $checkbox_val) {
					$product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_clientid =:UID","fetch",array('fields'=>'product_id','warray'=>array('PID'=>$checkbox_val,'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
					if(!empty($product_details["product_id"])) {
						$this->SuperModel->Super_Delete(T_PRODUCTS,"product_id = '".$checkbox_val."'");
					}
				}
				$this->frontSession['successMsg'] = "Selected products deleted successfully.";
				return $this->redirect()->tourl(APPLICATION_URL.'/manage-products');
			}
		}
	}
	
	public function mywalletAction() {
		$view = new ViewModel();
		$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total_fee'=>new Expression('SUM(order_total)'),'site_fee'=>new Expression('SUM(order_sitefee)'))));
		if(!empty($seller_orders)) {
			$total_earned = $seller_orders["total_fee"] - $seller_orders["site_fee"];
		} else {
			$total_earned = '0';
		}
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$_SESSION["logstat"] = '1';
		$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'order_clientid = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name')),
			'1'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_id','product_title'))
		);
		$tx_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		$pending_funds = 0; $available_funds = 0;
		
		$withdrawal_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_clientid =:UID and withdrawal_type = '1'","fetch",array('fields'=>array('total'=>new Expression('SUM(withdrawal_amount)')),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
		if(!empty($withdrawal_data["total"])) {
			$total_withdrawal = $withdrawal_data["total"];
		} else {
			$total_withdrawal = '0';
		}
		if(!empty($tx_data)) {
			foreach($tx_data as $tx_data_key => $tx_data_val) {
				$release_date = date("Y-m-d",strtotime("+7 days", strtotime($tx_data_val["order_date"]))); 
				if(strtotime(date("Y-m-d")) < strtotime($release_date)) {
					$pending_funds += $tx_data_val["order_total"] - $tx_data_val["order_sitefee"];
				} else {
					$available_funds += $tx_data_val["order_total"] - $tx_data_val["order_sitefee"];
				}
			}
		}
		if($available_funds < 0) {
		    $available_funds = 0;
		}
		$withdrawal_tx = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
		$withtx_data = array();
		foreach($withdrawal_tx as $withdrawal_tx_key => $withdrawal_tx_val) {
			$withtx_data[$withdrawal_tx_key]["order_date"] = $withdrawal_tx_val["withdrawal_date"];
			$withtx_data[$withdrawal_tx_key]["type"] = 'You';
			$withtx_data[$withdrawal_tx_key]["withdrawal_amount"] = $withdrawal_tx_val["withdrawal_amount"];
			$withtx_data[$withdrawal_tx_key]["withdrawal_type"] = $withdrawal_tx_val["withdrawal_type"];
			$withtx_data[$withdrawal_tx_key]["withdrawal_declinetxt"] = $withdrawal_tx_val["withdrawal_declinetxt"];
		}
		if(!empty($withtx_data)) {
			//$trx_data = array_merge($tx_data,$withtx_data);
			$trx_data = $tx_data;
		} else {
			$trx_data = $tx_data;
		}
		$joinArr = array(
			'0'=> array('0'=>T_PRODORDER,'1'=>'refund_orderid=order_id ','2'=>'Inner','3'=>array('order_product','order_clientid','order_color','order_size','order_total')),
			'1'=> array('0'=>T_CLIENTS,'1'=>'refund_clientid=yurt90w_client_id','2'=>'Inner','3'=>array('yurt90w_client_name')),
			'2'=> array('0'=>T_PRODUCTS,'1'=>'order_product=product_id','2'=>'Inner','3'=>array('product_id','product_title'))
		); 
		$refund_tx = $this->SuperModel->Super_Get(T_REFUND,"refund_sellerid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})),$joinArr);
		$refund_amount = 0;
		foreach($refund_tx as $refund_tx_key => $refund_tx_val) {
			$refund_txdata[$refund_tx_key]["order_date"] = $refund_tx_val["refund_date"];
			$refund_txdata[$refund_tx_key]["type"] = 'Refund';
			$refund_txdata[$refund_tx_key]["refund_amount"] = $refund_tx_val["refund_amount"];
			$refund_txdata[$refund_tx_key]["refund_type"] = $refund_tx_val["refund_type"];
			$refund_txdata[$refund_tx_key]["refund_status"] = $refund_tx_val["refund_status"];
			$refund_txdata[$refund_tx_key]["refund_text"] = $refund_tx_val["refund_description"];
			$refund_txdata[$refund_tx_key][T_CLIENT_VAR."client_name"] = $refund_tx_val[T_CLIENT_VAR."client_name"];
			$refund_txdata[$refund_tx_key]["product_id"] = $refund_tx_val["product_id"];
			$refund_txdata[$refund_tx_key]["product_title"] = $refund_tx_val["product_title"];
			if($refund_tx_val["refund_status"] == '1') {
				$refund_amount += $refund_tx_val["refund_amount"];
			}
		}
		if(!empty($refund_txdata)) {
			//$trx_data = array_merge($trx_data,$refund_txdata);
		}
		array_multisort( array_column($trx_data, "order_date"), SORT_DESC, $trx_data );
		$available_funds = $available_funds - ($total_withdrawal + $refund_amount);
		$available_balance = $total_earned - ($total_withdrawal + $refund_amount);
		if($available_funds < 0) {
		    $available_funds = 0;
		}
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		
		
		$view->setVariable('total_earned', $total_earned);
		$view->setVariable('total_withdrawal',$total_withdrawal);
		$view->setVariable('available_balance',$available_balance);
		$view->setVariable('refund_amount',$refund_amount);
		$view->setVariable('tx_data',$trx_data);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('pending_funds',$pending_funds);
		$view->setVariable('available_funds',$available_funds);
		$view->setVariable('autorelease_date',$this->site_configs["autoapproval_date"]);
		return $view;
	}
	
	public function removefileAction() {
		$mainDir = PRODUCT_PIC_PATH;
		$HTTP_Dir = HTTP_PRODUCT_PIC_PATH;
		$mainPath = $mainDir;
		$tempFolder = $mainDir . '/';
		$httpTempFolder = $HTTP_Dir . '/';


		$imagePlugin = $this->Image();
		if (isset($_REQUEST['file'])) {
			$imageName = $_REQUEST['file'];
			$request = $this->getRequest();
			$uploadPathDir = $tempFolder;
			$isRemoved = $imagePlugin->universal_unlink($imageName, array("directory" => $uploadPathDir));
		}

		echo json_encode(true);
		exit();
	}
	
	/*public function postpriceAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$posted_data = $request->getPost();
			foreach($posted_data["qty_from"] as $qty_from_key => $qty_from_val) {
				$price_data["product_price_productid"] = $posted_data["product"][$qty_from_key];
				$price_data["product_qtyfrom"] = $qty_from_val;
				$price_data["product_qtyto"] = $posted_data["qty_to"][$qty_from_key];
				$price_data["product_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
				$price_data["product_price"] = $posted_data["range_price"][$qty_from_key];
				$colorsizes = explode(",",$posted_data["qty_colorsize"][$qty_from_key]);
				$price_data["product_price_color"] = $colorsizes[0];
				$price_data["product_price_size"] = $colorsizes[1];
				
			}
			echo "success";
			exit();
		}
	}*/
	
	public function updateprostatAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$posted_data = $request->getPost();
			if($posted_data["chkVal"] == '1') {
				$upd_stat["product_status"] = '1';
			} else {
				$upd_stat["product_status"] = '2';
			}
			$this->SuperModel->Super_Insert(T_PRODUCTS,$upd_stat,"product_id = '".myurl_decode($posted_data["tid"])."'");
			echo "success";
			exit();
		}
		exit();
	}
	
	public function quickeditAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
			if(empty($store_data) || $store_data["store_approval"] != '1') {
				echo "restricted";
				exit();
			}
			if(empty($this->loggedUser->{T_CLIENT_VAR."client_stripe_id"})) {
				echo "restricted";
				exit();
			}
			if($store_data["store_approval"] == '1') {
				$joinArr = array(
					'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				);
				$products_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_delstatus != '1'","fetchAll",array('order'=>'product_order asc','warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)),$joinArr);
				$category_data = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_status = '1'","fetchAll");
				if(!empty($category_data)) {
					foreach($category_data as $category_data_key => $category_data_val) {
						$category_arr[$category_data_val["category_id"]] = $category_data_val["category_feild"];
					}
				}
				$view = new ViewModel();
				$view->setVariable('loggedUser', $this->loggedUser);
				$view->setVariable('products_data',$products_data);
				$view->setVariable('category_arr',$category_arr);
				$view->setTerminal(true);
				return $view;
			}
		}
	}
	
	public function merchantcouponAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$coupon_data = $this->SuperModel->Super_Get(T_MERCHANTCOUPON,"LOWER(merchantcoupon_code) =:TID","fetch",array('warray'=>array('TID'=>strtolower($data["coupon_code"]))));
			if(empty($coupon_data)) {
				echo "error";
				exit();
			} else {
				$plan_price = $this->site_configs["plan_price"];
				$coupon_discount = ($plan_price * $coupon_data["merchantcoupon_discount"]) / 100;
				$coupon_amount = $plan_price - $coupon_discount;
			}
		}
		$view = new ViewModel();
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('coupon_amount',$coupon_amount);
		$view->setVariable('coupon_code',$data["coupon_code"]);
		$view->setTerminal(true);
		return $view;
	}
	
	public function toggleroleAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
			if(empty($store_data) || $store_data["store_approval"] != '1') {
				echo "restricted";
				exit();
			}
			if($store_data["store_approval"] == '1') {
				$_SESSION["logstat"] = $data["togval"];
				echo "success";
				exit();
			}
		}
	}
	
	public function returnplaceAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
			if(empty($store_data) || $store_data["store_approval"] != '1') {
				echo "restricted";
				exit();
			}
			if($store_data["store_approval"] == '1') {
				$_SESSION["logstat"] = '2';
				echo "success";
				exit();
			}
		}
	}
	
	public function addmyfavoriteAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			if(empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
				echo "nonlog";
				exit();				
			}
			$data = $request->getPost();
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('fields'=>'store_clientid','warray'=>array('TID'=>base64_decode($data["keycode"]))));
			if(empty($store_data)) {
				echo "empty";
				exit();
			}
			if($store_data["store_clientid"] == $this->loggedUser->{T_CLIENT_VAR.'client_id'}) {
				echo "restricted";
				exit();
			}
			$favorite_data = $this->SuperModel->Super_Get(T_FAVOURITE,"favourite_by = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."' and favourite_storeid =:TID","fetch",array('warray'=>array('TID'=>base64_decode($data["keycode"]))));
			if(!empty($favorite_data)) {
				$this->SuperModel->Super_Delete(T_FAVOURITE,"favourite_by = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."' and favourite_storeid = '".base64_decode($data["keycode"])."'");
				echo "removed";
				exit();
			}
			$fav_data["favourite_storeid"] = base64_decode($data["keycode"]);
			$fav_data["favourite_clientid"] = $store_data["store_clientid"];
			$fav_data["favourite_by"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
			$fav_data["favourite_date"] = date("Y-m-d H:i:s");
			$this->SuperModel->Super_Insert(T_FAVOURITE,$fav_data);
			echo "success";
			exit();
		} else {
			echo "empty";
			exit();
		}
	}
	
	public function postorderAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			foreach($data["MyTabs"] as $prod_key => $prod_val) {
				$product_record = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_id =:TID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'TID'=>$prod_val)));
				if(empty($product_record)) {
					echo "error";
					exit();
				} else {
					$prod_data["product_order"] = $prod_key + 1;
					$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_clientid = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."' and product_id = '".$prod_val."'");
				}
			}
			echo "success";
			exit();
		}
	}
	
	public function withdrawalAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total_fee'=>new Expression('SUM(order_total)'),'site_fee'=>new Expression('SUM(order_sitefee)'))));
			if(!is_numeric($data["withdraw"])) {
				echo "invalid_amount";
				exit();
			}
			if(!empty($seller_orders)) {
				$total_earned = $seller_orders["total_fee"] - $seller_orders["site_fee"];
			} else {
				$total_earned = '0';
			}
			if($data["withdraw"] < 1) {
				echo "invalid_amount";
				exit();
			}
			$data["withdraw"] = bcdiv($data["withdraw"],1,2);
			$withdrawal_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_clientid =:UID and withdrawal_type = '1'","fetch",array('fields'=>array('total'=>new Expression('SUM(withdrawal_amount)')),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
			if(!empty($withdrawal_data["total"])) {
				$total_withdrawal = $withdrawal_data["total"];
			} else {
				$total_withdrawal = '0';
			}
			$refund_tx = $this->SuperModel->Super_Get(T_REFUND,"refund_sellerid =:UID and refund_status = '1'","fetch",array('fields'=>array('total'=>new Expression('SUM(refund_amount)')),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
			if(empty($refund_tx["total"])) {
				$refund_tx["total"] = 0;
			}
			$available_balance = $total_earned - ($total_withdrawal + $refund_tx["total"]);
			$available_balance = round($available_balance,2);
			if($data["withdraw"] > $available_balance) {
				echo "insufficient";
				exit();
			} else {
				$previous_withdrawals = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_clientid =:UID AND withdrawal_type = '2'","fetch",array('fields'=>array('total'=>new Expression('SUM(withdrawal_amount)')),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				if(($data["withdraw"] + $previous_withdrawals["total"]) > $available_balance) {
					echo "insufficient";
					exit();
				}
				
				$withdw_data["withdrawal_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$withdw_data["withdrawal_amount"] = $data["withdraw"];
				$withdw_data["withdrawal_date"] = date("Y-m-d H:i:s");
				$withdw_data["withdrawal_type"] = '2';
				$is_Insert = $this->SuperModel->Super_Insert(T_WITHDRAWAL,$withdw_data);
				
				if($is_Insert->inserted_id) {
					$notify_data["notification_type"] = '6';
					$notify_data["notification_by"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
					$notify_data["notification_to"] = '1';
					$notify_data["notification_date"] = date("Y-m-d H:i:s");
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				
					$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has sent you the withdrawal request of amount $".$data["withdraw"].".",
							"subject" => "Withdrawal request"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
					echo "success";
					exit();
				}
			}
		}
	}
	
	public function exporttxAction() {
		$datetg = $this->params()->fromRoute('key');
		$expl = explode("~",$datetg);
		$filename = "Transaction History:" . str_replace("~", " - ", $datetg);
		$fieldsArr = array(
			"order_date"=> "Date Payment Received",
			"order_no"=> "Order No",
			"client_name" => "Client's Name",
			"product"=> "Product",
			"total_order" => "Total Order Amount",
			"action" => "Action",
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
		if(strtotime($start_date) > strtotime($end_date)) {
		    $this->frontSession['errorMsg']=$this->layout()->translator->translate("Please select the start date less than the end date.");
			return $this->redirect()->tourl(APPLICATION_URL.'/my-wallet');
		}
		$joinArr = array(
			'0'=> array('0'=>T_CLIENTS,'1'=>'order_clientid=yurt90w_client_id','2'=>'Left','3'=>array('yurt90w_client_name')			
			),
			'1'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_id','product_title'))
		);
		if(!empty($start_date)) {
			$orders_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_date > '".$start_date."' and order_date <= '".$end_date."' and order_sellerid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"}),'order'=>'order_id desc'),$joinArr);
		} else {
			$orders_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"}),'order'=>'order_id desc'));
		}
		if(empty($orders_data)) {
			$this->frontSession['errorMsg']=$this->layout()->translator->translate("No transaction found for the selected date range.");
			return $this->redirect()->tourl(APPLICATION_URL.'/my-wallet');
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
				$fieldsArr['order_date'] =>  $value["order_date"],
				$fieldsArr['order_no'] =>  $value["order_serial"],
				$fieldsArr['client_name'] => $value[T_CLIENT_VAR."client_name"],
				$fieldsArr['product'] =>  $value["product_title"],
				$fieldsArr['total_order'] => "$".round($value["order_total"] - $value["order_sitefee"],2),
				$fieldsArr['action'] => $status,
				$fieldsArr['released_date'] => $released_date
			);
		}
		$newSheetName = "Transaction History";
		if(!empty($exportArr)){
			$var = exportData($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	
	public function chatmessageAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if($this->loggedUser->{T_CLIENT_VAR.'client_id'} == base64_decode($data["chat"])) {
				echo "restricted";
				exit();
			}
			$last_chat = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:UID and chat_to =:TID)","fetch",array('warray'=>array('TID'=>base64_decode($data["chat"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'order'=>'chat_id desc'));
			$chat_data["chat_by"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
			if(empty(base64_decode($data["chat"]))) {
				echo "blocked";
				exit();
			}
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
				$message = $this->loggedUser->{T_CLIENT_VAR.'client_name'}." has sent you a message.<br/>".nl2br($chat_data["chat_text"]);
				
				$notify_data["notification_type"] = '5';
				$notify_data["notification_by"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$notify_data["notification_to"] = base64_decode($data["chat"]);
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				$mail_const_data2 = array(
					"user_name" => $user_details[T_CLIENT_VAR.'client_name'],
					"user_email" => $user_details[T_CLIENT_VAR.'client_email'],
					"message" => $message,
					"subject" => "You have a new message."
				);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
			}
			
			$view = new ViewModel();
			$view->setVariable('message_record',$chat_record);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function getsearchpersonAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$view = new ViewModel();
			if(!empty($data["search_txt"])) {
				$joinArr = array(
					'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
				);
				$message_record = $this->SuperModel->Super_Get(T_CHAT,"LOWER(yurt90w_client_name) like :CID and ".T_CLIENT_VAR."client_name != '".$this->loggedUser->{T_CLIENT_VAR.'client_name'}."' and (chat_by =:UID or chat_to =:UID)","fetchAll",array('offset'=>$data["start"],'order'=>'chat_date desc','group'=>'chat_by','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'CID'=>'%'.strtolower($data["search_txt"]).'%')),$joinArr);
				$joinArr2 = array(
					'0'=>array('0'=>T_CLIENTS,'1'=>'chat_to = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
				);
				$messager_record = $this->SuperModel->Super_Get(T_CHAT,"LOWER(yurt90w_client_name) like :CID and ".T_CLIENT_VAR."client_name != '".$this->loggedUser->{T_CLIENT_VAR.'client_name'}."' and (chat_by =:UID or chat_to =:UID)","fetchAll",array('offset'=>$data["start"],'order'=>'chat_date desc','group'=>'chat_to','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'CID'=>'%'.strtolower($data["search_txt"]).'%')),$joinArr2);
				foreach($messager_record as $messager_record_key => $messager_record_val) {
					if(in_array($messager_record_val["chat_to"], array_column($message_record, 'chat_by'))) {
						unset($messager_record[$messager_record_key]);
					} else {
						$chat_recdata = $this->SuperModel->Super_Get(T_CHAT,"chat_by = '".$message_record_val["chat_by"]."' and chat_to = '".$message_record_val["chat_to"]."'","fetch",array('order'=>'chat_date desc'));
						$message_record[$message_record_key]["chat_date"] = $chat_recdata["chat_date"];
					}
				}
				$message_record = array_unique(array_merge($message_record,$messager_record), SORT_REGULAR);
				array_multisort( array_column($message_record, "chat_date"), SORT_DESC, $message_record );
				$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
				if(!empty($store_data) && $store_data["store_approval"] == '1') {
					$mod_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id = '1'","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')));
					if(empty($data["search_txt"])) {
						$view->setVariable('mod_data',$mod_data);
						$view->setVariable('store_approval',1);
					} else if (strpos(strtolower($mod_data[T_CLIENT_VAR.'client_name']), strtolower($data["search_txt"])) !== false) {
						$view->setVariable('mod_data',$mod_data);
						$view->setVariable('store_approval',1);		
					}

				}
				if(in_array(1, array_column($message_record, 'chat_by'))) {
					$modchk = 1;
				} else if(in_array(1, array_column($message_record, 'chat_to'))) {
					$modchk = 1;
				} else {
					$modchk = '';
				}
				if(!empty($message_record)) {
					if(!empty($key)) { 
						$uid = base64_decode($key);
					} else {
					if($message_record[0]["chat_by"] != $this->loggedUser->{T_CLIENT_VAR.'client_id'}) {
						$uid = $message_record[0]["chat_by"];
					} else {
						$uid = $message_record[0]["chat_to"];
					} }
				}
				$view->setVariable("uid",str_replace("=","",base64_encode($uid)));
			} else {
				$joinArr = array(
					'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
				);
				$message_record = $this->SuperModel->Super_Get(T_CHAT,"chat_to =:UID","fetchAll",array('order'=>'chat_date desc','limit'=>10,'group'=>'chat_by','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
				if(!empty($message_record)) {
					foreach($message_record as $message_record_key => $message_record_val) {
						$chat_recdata = $this->SuperModel->Super_Get(T_CHAT,"chat_by = '".$message_record_val["chat_by"]."' and chat_to = '".$message_record_val["chat_to"]."'","fetch",array('order'=>'chat_date desc'));
					$message_record[$message_record_key]["chat_date"] = $chat_recdata["chat_date"];
					}
				}

				$joinArr2 = array(
					'0'=>array('0'=>T_CLIENTS,'1'=>'chat_to = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
				);
				$messager_record = $this->SuperModel->Super_Get(T_CHAT,"chat_by =:UID","fetchAll",array('order'=>'chat_date desc','limit'=>10,'group'=>'chat_to','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr2);
				foreach($messager_record as $messager_record_key => $messager_record_val) {
					if(in_array($messager_record_val["chat_to"], array_column($message_record, 'chat_by'))) {
						unset($messager_record[$messager_record_key]);
					}
				}
				$message_record = array_unique(array_merge($message_record,$messager_record), SORT_REGULAR);
				array_multisort( array_column($message_record, "chat_date"), SORT_DESC, $message_record );
				$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
				if(!empty($store_data) && $store_data["store_approval"] == '1') {
					$mod_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id = '1'","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')));
					$view->setVariable('mod_data',$mod_data);
					$view->setVariable('store_approval',1);	
				}
				if(in_array(1, array_column($message_record, 'chat_by'))) {
					$modchk = 1;
				} else if(in_array(1, array_column($message_record, 'chat_to'))) {
					$modchk = 1;
				} else {
					$modchk = '';
				}
				$chat_record = array();
				if(!empty($message_record)) {
					if(!empty($key)) { 
						$uid = base64_decode($key);
					} else {
					if($message_record[0]["chat_by"] != $this->loggedUser->{T_CLIENT_VAR.'client_id'}) {
						$uid = $message_record[0]["chat_by"];
					} else {
						$uid = $message_record[0]["chat_to"];
					} }
				}
				$view->setVariable("uid",str_replace("=","",base64_encode($uid)));
			}
			$view->setVariable('message_record',$message_record);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('modchk',$modchk);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function getscrolllistAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$view = new ViewModel();
			$joinArr = array(
				'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
			);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_to =:UID and chat_by =:TID) || (chat_by =:UID  and chat_to =:TID)","fetchAll",array('offset'=>$data["start"],'order'=>'chat_date desc','limit'=>10,'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'TID'=>base64_decode($data["tid"]))),$joinArr);
			$upd_data["chat_readstatus"] = '1';
			$this->SuperModel->Super_Insert(T_CHAT,$upd_data,"chat_by = '".base64_decode($data["tid"])."' and chat_to = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
			$view->setVariable('chat_record',$message_record);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('uid',$data["tid"]);
			$view->setTerminal(true);
			return $view;	
		}
	}
	
	public function messagesAction() {
		$key = $this->params()->fromRoute("key");
		$view = new ViewModel();
		$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$message_record = $this->SuperModel->Super_Get(T_CHAT,"chat_to =:UID","fetchAll",array('order'=>'chat_date desc','limit'=>10,'group'=>'chat_by','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		if(!empty($message_record)) {
			foreach($message_record as $message_record_key => $message_record_val) {
				$chat_recdata = $this->SuperModel->Super_Get(T_CHAT,"chat_by = '".$message_record_val["chat_by"]."' and chat_to = '".$message_record_val["chat_to"]."'","fetch",array('order'=>'chat_date desc'));
				$message_record[$message_record_key]["chat_date"] = $chat_recdata["chat_date"];
			}
		}
		
		$joinArr2 = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_to = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$messager_record = $this->SuperModel->Super_Get(T_CHAT,"chat_by =:UID","fetchAll",array('order'=>'chat_date desc','limit'=>10,'group'=>'chat_to','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr2);
		foreach($messager_record as $messager_record_key => $messager_record_val) {
			if(in_array($messager_record_val["chat_to"], array_column($message_record, 'chat_by'))) {
				unset($messager_record[$messager_record_key]);
			}
		}
		$message_record = array_unique(array_merge($message_record,$messager_record), SORT_REGULAR);
		array_multisort( array_column($message_record, "chat_date"), SORT_DESC, $message_record );
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(!empty($store_data) && $store_data["store_approval"] == '1') {
			$mod_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id = '1'","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')));
			$view->setVariable('mod_data',$mod_data);
			$view->setVariable('store_approval',1);	
		}
		if(in_array(1, array_column($message_record, 'chat_by'))) {
			$modchk = 1;
		} else if(in_array(1, array_column($message_record, 'chat_to'))) {
			$modchk = 1;
		} else {
			$modchk = '';
		}
		$chat_record = array();
		if(!empty($key)) { 
			$uid = base64_decode($key);
		} else {
		if($message_record[0]["chat_by"] != $this->loggedUser->{T_CLIENT_VAR.'client_id'}) {
			$uid = $message_record[0]["chat_by"];
		} else {
			$uid = $message_record[0]["chat_to"];
		} }
		$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$chat_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by=:UID and chat_to =:TID) || (chat_by=:TID and chat_to =:UID)","fetchAll",array('order'=>'chat_id desc','limit'=>8,'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'TID'=>$uid)),$joinArr);
		if(!empty($uid)) {
			$key1 = array_search($uid, array_column($message_record, 'chat_by'));
			$key2 = array_search($uid, array_column($message_record, 'chat_to'));
			if(empty($key1) && empty($key2)) {
				$message_record[0]["chat_by"] = $uid;
				$message_record[0]["chat_to"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
			}
		}
		$upd_data["chat_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_CHAT,$upd_data,"chat_by = '".$uid."' and chat_to = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
		$view->setVariable('message_record',$message_record);
		$view->setVariable('modchk',$modchk);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable("chat_record",$chat_record);
		$view->setVariable("uid",str_replace("=","",base64_encode($uid)));
		if(!empty($key)) {
			$view->setVariable("usrid",$key);
		}
		return $view;
	}
	
	public function getmessagesAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
			);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:TID and chat_to =:UID) or (chat_to =:TID and chat_by =:UID)","fetchAll",array('order'=>'chat_id desc','limit'=>8,'warray'=>array('TID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'UID'=>base64_decode($data["tid"]))),$joinArr);
			$upd_data["chat_readstatus"] = '1';
			$this->SuperModel->Super_Insert(T_CHAT,$upd_data,"chat_by = '".base64_decode($data["tid"])."' and chat_to = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
			$view = new ViewModel();
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('chat_record',$message_record);
			$view->setVariable('uid',$data["tid"]);
			$view->setTerminal(true);
			return $view;
			
		}
	}
	
	public function getpreviousmsgAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$view = new ViewModel();
			$limit = 5;
			$joinArr = array(
				'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
			);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:TID and chat_to =:UID) or (chat_to =:TID and chat_by =:UID)","fetchAll",array('offset'=>$data["start"],'order'=>'chat_id desc','limit'=>$limit,'warray'=>array('TID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'UID'=>base64_decode($data["chat"]))),$joinArr);
			array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
			$view = new ViewModel();
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('message_record',$message_record);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function loadmessagesAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$view = new ViewModel();
			$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
			$message_record = $this->SuperModel->Super_Get(T_CHAT,"chat_to =:TID and chat_by =:UID and chat_readstatus = '2'","fetchAll",array('order'=>'chat_id desc','limit'=>10,'warray'=>array('TID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'UID'=>base64_decode($data["chat"]))),$joinArr);
			array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
			$chk_data["chat_readstatus"] = '1';
			$this->SuperModel->Super_Insert(T_CHAT,$chk_data,"chat_by = '".base64_decode($data["chat"])."' and chat_to = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
			$view = new ViewModel();
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('message_record',$message_record);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function messageAction() {
		$key = $this->params()->fromRoute("key");
		$disablechat = ''; $messages = array();
		if($key == 'admin') {
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
			if(empty($store_data) || $store_data["store_approval"] != '1') {
				$disablechat = '1';
			}
			$key = base64_encode('1');
		}
		if($this->loggedUser->yurt90w_client_id == base64_decode($key)) {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$joinArr = array(
			'0'=>array('0'=>T_STORE,'1'=>T_CLIENT_VAR.'client_id = store_clientid','2'=>'Left','3'=>array('store_name','store_company')),
		);
		$client_record = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_name'),'warray'=>array('UID'=>base64_decode($key))),$joinArr);
		if(empty($client_record)) {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL);
		}
		$chk_data["chat_readstatus"] = '1';
		$this->SuperModel->Super_Insert(T_CHAT,$chk_data,"chat_by = '".base64_decode($key)."' and chat_to = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
		$joinArr = array(
			'0'=>array('0'=>T_CLIENTS,'1'=>'chat_by = '.T_CLIENT_VAR.'client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$message_record = $this->SuperModel->Super_Get(T_CHAT,"(chat_by =:TID and chat_to =:UID) or (chat_to =:TID and chat_by =:UID)","fetchAll",array('order'=>'chat_id desc','limit'=>10,'warray'=>array('TID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'UID'=>base64_decode($key))),$joinArr);
		array_multisort( array_column($message_record, "chat_id"), SORT_ASC, $message_record );
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$view = new ViewModel();
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable("messages",$messages);
		$view->setVariable('client_record',$client_record);
		$view->setVariable('message_record',$message_record);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('disablechat',$disablechat);
		return $view;
	}
	
	public function shippingAction() {
		$view = new ViewModel();	
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$_SESSION["logstat"] = '1';
		$rate_data = $this->SuperModel->Super_Get(T_SHIPPING,"shipping_clientid = '".$this->loggedUser->yurt90w_client_id."'","fetchAll");
		$countries_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>array('country_id','country_name_en')));
		$country_arr = array();
		foreach($countries_data as $countries_data_key => $countries_data_val) {
			$country_arr[$countries_data_val["country_id"]] = $countries_data_val["country_name_en"];
		}
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if($posted_data["global_rate"] < 0) {
				$errorCase = 1;
				$this->frontSession['errorMsg'] = "Invalid global rate, please enter a valid global rate.";
			} 
			$global_data["store_globalrate"] = $posted_data["global_rate"];
			$this->SuperModel->Super_Insert(T_STORE,$global_data,"store_clientid = '".$this->loggedUser->yurt90w_client_id."'");
			$merger = array_merge($posted_data["country_name"],$posted_data["rate"]);
			$this->SuperModel->Super_Delete(T_SHIPPING,"shipping_clientid = '".$this->loggedUser->yurt90w_client_id."'");
			foreach($posted_data["country_name"] as $country_key => $country_val) {
				$shipping_data["shipping_country"] = $country_val;
				if($posted_data["rate"][$country_key] < 0) {
					$errorCase = 1;
					$this->frontSession['errorMsg'] = "Invalid shipping rate, please enter a valid shipping rate.";
				} 
				$shipping_data["shipping_rate"] = $posted_data["rate"][$country_key];
				$shipping_data["shipping_clientid"] = $this->loggedUser->yurt90w_client_id;
				$this->SuperModel->Super_Insert(T_SHIPPING,$shipping_data);
			}
			if(!empty($errorCase)) { } else {
			if(empty($store_data["store_globalrate"])) {
				$this->frontSession['successMsg'] = "Shipping rate added successfully.";
			} else {
				$this->frontSession['successMsg'] = "Shipping rate updated successfully.";
			}
			return $this->redirect()->tourl(APPLICATION_URL.'/shipping-rate');
			}
		}
		$view->setVariable('show', 'front_profile');
		$view->setVariable('global_rate', $store_data["store_globalrate"]);
		$view->setVariable('rate_data', $rate_data);
		$view->setVariable('country_arr',$country_arr);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);
		return $view;	
	}
	
	public function processpaymentAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$all_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total' =>new Expression('SUM(product_cart_price)'))));
			$joinArr = array(
				'0'=>array('0'=>T_PRODUCTS,'1'=>'product_cart_prodid = product_id','2'=>'Left','3'=>array('product_title')),
				'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			);
			$my_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
			if(!empty($my_carts)) {
				foreach($my_carts as $my_carts_key => $my_carts_val) {
					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
					$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
					$available_qty = 0;
					if(!empty($colorsize_data)) {
						foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
							$available_qty += $colorsize_data_val["color_qty"];
						}
					}
					if($my_carts_val["product_cart_qty"] > $available_qty) {
						$sendData['response_code'] = 'error';
						$sendData["message"] = "Payment failed as ".$my_carts_val["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
						$sendData["status"] = 'Q';
						echo json_encode($sendData);
						exit();
					}
					/* condition needs to update if($prod_data["product_qty"] < $my_carts_val["product_cart_qty"]) {
						$sendData['response_code'] = 'error';
						$sendData["message"] = "Payment failed as ".$my_carts_val["product_title"]." is out of stock.";
						$sendData["status"] = 'Q';
						echo json_encode($sendData);
						exit();
					}*/
					$cart_title[] = $my_carts_val["product_title"];
				}
			}
			if(empty($my_carts)) {
				$sendData['response_code'] = 'error';
				$sendData["message"] = "Please fill the form correctly.";
				$sendData["status"] = 'E';
				echo json_encode($sendData);
				exit();
			}
			if(strlen($data["credit_name"]) > 200) {
				$sendData['response_code'] = 'error';
				$sendData["message"] = "Please fill the form correctly.";
				$sendData["status"] = 'E';
				echo json_encode($sendData);
				exit();
			}
			if(strlen($data["credit_number"]) > 19) {
				$sendData['response_code'] = 'error';
				$sendData["message"] = "Please fill the form correctly.";
				$sendData["status"] = 'E';
				echo json_encode($sendData);
				exit();
			}
			if(strlen($data["credit_cvv"]) > 4) {
				$sendData['response_code'] = 'error';
				$sendData["message"] = "Please fill the form correctly.";
				$sendData["status"] = 'E';
				echo json_encode($sendData);
				exit();
			}
			$cart_title = implode(", ",$cart_title);
			$amount=bcdiv($all_carts["total"],1,2);
			$credit_card = strip_tags(str_replace(" ","",$data["credit_number"]));
			$exp_month = strip_tags(str_replace(" ","",$data["credit_month"]));
			$exp_year = strip_tags(str_replace(" ","",$data["credit_year"]));
			$exp_date = $exp_month.$exp_year;
			$cvv = strip_tags(str_replace(" ","",$data["credit_cvv"]));
			$user_name = strip_tags(str_replace(" ","",$data["credit_name"]));
			/* commented stripe charge require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
			$stripe = \Stripe\Stripe::setApiKey($this->site_configs["site_secret_key"]);
			$charge = $stripe->charges->create([
			  'amount' => $amount,
			  'currency' => 'usd',
			  'source' => 'tok_mastercard',
			  'description' => 'My First Test Charge (created for API docs)',
			]);*/
			
			
			// charge through paypal 
			$payrequest = paypalCall($data["credit_number"],$exp_date,$cvv,$user_name,$amount,$cart_title);
			$sendData = array();
			if($payrequest["ACK"] == 'Success') {
				$my_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array('group'=>'order_serial'));
				$serial_num = count($all_orders) + 1;
				foreach($my_carts as $my_carts_key =>$my_carts_val) {
					if(!empty($my_carts_val["product_cart_delivery"])) {
							$site_fee = ($this->site_configs["site_commission"] / 100) * ($my_carts_val["product_cart_price"] + $my_carts_val["product_cart_delivery"]);
						} else {
							$site_fee = ($this->site_configs["site_commission"] / 100) * $my_carts_val["product_cart_price"];
						}
					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
					$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$prod_data["product_clientid"])));
					$orderData = array(
							"order_product" => $my_carts_val["product_cart_prodid"],
							"order_qty" => $my_carts_val["product_cart_qty"],
							"order_total" => $my_carts_val['product_cart_price'] + $my_carts_val["product_cart_delivery"],
							"order_clientid" => $this->loggedUser->{T_CLIENT_VAR.'client_id'},
							"order_date" => date("Y-m-d H:i:s"),
							"order_sitefee" => $site_fee,
							"order_serial" => "51905296".$serial_num,
							"order_discount" => $my_carts_val["product_cart_discount"],
							"order_status" => 1,
							"order_sellerid" => $prod_data["product_clientid"],
							"order_sellerpaid" => 1,
							"order_shipping" => $my_carts_val["product_cart_delivery"],
							"order_color" => $my_carts_val["product_cart_color"],
							"order_size" => $my_carts_val["product_cart_size"],
							"order_address" => $_SESSION["shipping_addr"],
							"order_shiprate" => $my_carts_val["product_cart_shiprate"],
							"order_shipname" => $my_carts_val["product_cart_shipname"]
					);					
					$jj = $this->SuperModel->Super_Insert(T_PRODORDER,$orderData);
					$message = $this->loggedUser->{T_CLIENT_VAR.'client_name'}." has placed an order with order number 51905296".$serial_num;
					$mail_const_data2 = array(
						"user_name" => $seller_details[T_CLIENT_VAR.'client_name'],
						"user_email" => $seller_details[T_CLIENT_VAR.'client_email'],
						"message" => $message,
						"subject" => "Order placed"
					);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
					
					$mail_const_data3 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $message,
							"subject" => "Order placed"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data3);					
					
					$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_id = '".$my_carts_val["product_cart_id"]."'");
					
					
					$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetch",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
					$available_qty = 0;
					$prodqty = $colorsize_data["color_qty"] - $my_carts_val["product_cart_qty"];
					if($prodqty < 1) {
						$avl_data["color_qty"] = 0;
					} else {
						$avl_data["color_qty"] = $prodqty;
					}
					$this->SuperModel->Super_Insert(T_PROQTY,$avl_data,"color_productid = '".$my_carts_val["product_cart_prodid"]."' and color_slug = '".strtolower($my_carts_val["product_cart_color"])."' and color_size = '".$my_carts_val["product_cart_size"]."'");				
					
				}
				$sendData['response_code'] = 'success';
				$sendData["message"] = 'You have successfully paid the amount for order number 51905296'.$serial_num.'.';
				$sendData["status"] = 'S';
				echo json_encode($sendData);
				exit();	
			} else {
				$sendData['response_code'] = 'error';
				$sendData["message"] = $payrequest["L_LONGMESSAGE0"];
				$sendData["status"] = 'E';
				echo json_encode($sendData);
				exit();
			}
		}
	}
	
	public function downloaddigitalAction() {
		$digital_id = $this->params()->fromRoute('key');
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_digital')),
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_product =:PID and order_clientid =:UID","fetch",array('warray'=>array('PID'=>base64_decode($digital_id),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})),$joinArr);
		if(empty($product_data)) {
			$this->frontSession['errorMsg'] = "You cannot access this product.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$file_url = HTTP_DIGITAL_PATH.'/'.$product_data["product_digital"];
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
		readfile($file_url);
		return $this->redirect()->tourl(APPLICATION_URL.'/customer-orders');
	}
	
	public function sellerchkorderAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if($data["orderchk"] == 'All') {
				$order_where = 'order_status != "5"';	
				$order_message = "No orders found.";
			} else if($data["orderchk"] == '1') {
				$order_where = 'order_status = "1"';
				$order_message = "No orders are in processing.";
			} else if($data["orderchk"] == '2') {
				$order_where = 'order_status = "2"';
				$order_message = "No orders are ready to shipped.";
			} else if($data["orderchk"] == '3') {
				$order_where = 'order_status = "3"';
				$order_message = "No orders have been shipped.";
			} else {
				$order_where = 'order_status = "4"';
				$order_message = "No orders have been delivered.";
			}
			$view = new ViewModel();
			$joinArr = array(
				'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_isdigital')),
				'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				'2'=>array('0'=>T_CLIENTS,'1'=>'order_clientid = yurt90w_client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
			);
			$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and ".$order_where,"fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
			$seller_orderarr = array();
			$order_tot = 0;
			if(!empty($seller_orders)) {
				foreach($seller_orders as $seller_orders_key => $seller_orders_val) {
					if($seller_orders_val["order_serial"] == $_SESSION["orderserial"] && $seller_orders_val["order_clientid"] == $_SESSION["orderby"]) {
						$order_tot += $seller_orders_val["order_total"];
					} else {
						$order_tot = $seller_orders_val["order_total"];
					}
					$_SESSION["orderserial"] = $seller_orders_val["order_serial"];
					$_SESSION["orderby"] = $seller_orders_val["order_clientid"];
					$seller_orders_val["avgtotal"] = $order_tot;
					$seller_orders_val["orderdate"] = $seller_orders_val["order_date"];
					$seller_orderarr[$seller_orders_val["order_serial"]][$seller_orders_key] = $seller_orders_val;	
				}
			}
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('seller_orders',$seller_orderarr);
			$view->setVariable('order_message',$order_message);
			$view->setTerminal(true);
			return $view;	
		}
	}
	
	public function sellerordersAction() {
		$view = new ViewModel();
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_shippingid','product_isdigital')),
			'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			'2'=>array('0'=>T_CLIENTS,'1'=>'order_clientid = yurt90w_client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR.'client_address',T_CLIENT_VAR.'client_postcode',T_CLIENT_VAR.'client_city',T_CLIENT_VAR.'client_state',T_CLIENT_VAR.'client_phone'))
		);
		$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_status != 5","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		$seller_orderarr = array();
		$order_tot = 0;
		if(!empty($seller_orders)) {
			foreach($seller_orders as $seller_orders_key => $seller_orders_val) {
				if($seller_orders_val["order_serial"] == $_SESSION["orderserial"] && $seller_orders_val["order_clientid"] == $_SESSION["orderby"]) {
					$order_tot += $seller_orders_val["order_total"];
				} else {
					$order_tot = $seller_orders_val["order_total"];
				}
				$_SESSION["orderserial"] = $seller_orders_val["order_serial"];
				$_SESSION["orderby"] = $seller_orders_val["order_clientid"];
				$seller_orders_val["avgtotal"] = $order_tot;
				$seller_orders_val["orderdate"] = $seller_orders_val["order_date"];
				$seller_orderarr[$seller_orders_val["order_serial"]][$seller_orders_key] = $seller_orders_val;	
			}
		}
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$_SESSION["logstat"] = '1';
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('seller_orders',$seller_orderarr);
		$view->setVariable('store_data',$store_data);
		return $view;	
	}
	
	public function revieworderAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$order_record = $this->SuperModel->Super_Get(T_PRODORDER,"order_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($data["review"]))));
			if(empty($order_record)) {
				echo "error";
				exit();
			}
			if($data["star_rate"] < 0 || $data["star_rate"] > 5) {
				echo "invalid_rating";
				exit();
			}
			$review_record = $this->SuperModel->Super_Get(T_REVIEWS,"review_from =:UID and review_to =:TID and review_prodid =:PID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'TID'=>$order_record["order_sellerid"],'PID'=>$order_record["order_product"])));
			if(!empty($review_record)) {
				echo "already";
				exit();
			}
			if($this->loggedUser->{T_CLIENT_VAR.'client_id'} == $order_record["order_sellerid"]) {
				echo "restricted";
				exit();
			}
			$review_data["review_from"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
			$review_data["review_to"] = $order_record["order_sellerid"];
			$review_data["review_starrating"] = $data["review_rate"];
			$review_data["review_text"] = strip_tags($data["review_text"]);
			$review_data["review_prodid"] = $order_record["order_product"];
			$review_data["review_date"] = date("Y-m-d H:i:s");
			if(!empty($data["review_image"])) {
				$review_data["review_photos"] = serialize($data["review_image"]);
			}
			$this->SuperModel->Super_Insert(T_REVIEWS,$review_data);
			echo "success";
			exit();
		}
	}
	
	public function customerordersAction() {
		$_SESSION["logstat"] = '2';
		$view = new ViewModel();
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_shippingid','product_digital','product_isdigital')),
			'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			'2'=>array('0'=>T_SHIPPROFILES,'1'=>'product_shippingid = shipping_id','2'=>'Left','3'=>array('shipping_time')),
		);
		$customer_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_clientid =:UID and order_status != 5","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		$customer_orderarr = array();
		$order_tot = 0;
		if(!empty($customer_orders)) {
			foreach($customer_orders as $customer_orders_key => $customer_orders_val) {
				if(!empty($_SESSION["orderserial"]) && $customer_orders_val["order_serial"] == $_SESSION["orderserial"]) {
					$order_tot += $customer_orders_val["order_total"];
				} else {
					$order_tot = $customer_orders_val["order_total"];
				}
				$_SESSION["orderserial"] = $customer_orders_val["order_serial"];
				$customer_orders_val["avgtotal"] = $order_tot;
				$customer_orders_val["orderdate"] = $customer_orders_val["order_date"];
				$customer_orderarr[$customer_orders_val["order_serial"]][$customer_orders_key] = $customer_orders_val;	
			}
		}
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('store_data',$store_data);
		$view->setVariable('customer_orders',$customer_orderarr);
		return $view;	
	}
	
	public function orderchkstatAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if($data["orderchk"] == 'All') {
				$order_where = 'order_status != "5"';	
				$order_message = "No orders found.";
			} else if($data["orderchk"] == '1') {
				$order_where = 'order_status = "1"';
				$order_message = "No orders are in processing.";
			} else if($data["orderchk"] == '2') {
				$order_where = 'order_status = "2"';
				$order_message = "No orders are ready to shipped.";
			} else if($data["orderchk"] == '3') {
				$order_where = 'order_status = "3"';
				$order_message = "No orders have been shipped.";
			} else {
				$order_where = 'order_status = "4"';
				$order_message = "No orders have been delivered.";
			}
			$view = new ViewModel();
			$joinArr = array(
				'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic')),
				'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			);
			$customer_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_clientid =:UID and ".$order_where,"fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
			$customer_orderarr = array();
			$order_tot = 0;
			if(!empty($customer_orders)) {
				foreach($customer_orders as $customer_orders_key => $customer_orders_val) {
					if($customer_orders_val["order_serial"] == $_SESSION["orderserial"]) {
						$order_tot += $customer_orders_val["order_total"];
					} else {
						$order_tot = $customer_orders_val["order_total"];
					}
					$_SESSION["orderserial"] = $customer_orders_val["order_serial"];
					$customer_orders_val["avgtotal"] = $order_tot;
					$customer_orders_val["orderdate"] = $customer_orders_val["order_date"];
					$customer_orderarr[$customer_orders_val["order_serial"]][$customer_orders_key] = $customer_orders_val;	
				}
			}
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('customer_orders',$customer_orderarr);
			if(empty($customer_orderarr)) {
				$view->setVariable('order_message',$order_message);
			}
			$view->setTerminal(true);
			return $view;	
		}
	}
	
	public function removeallcartAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$cart_data = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
			if(empty($cart_data)) {
				echo "failed";
				exit();
			}
			$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_clientid = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
			echo "success";
			exit();
		}
	}
	
	public function getcountriesAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$sel_country = $data["selcountry"];
			if($sel_country[0] == 'Select Country') {
				exit();
			}
			$countries_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>array('country_id','country_name_en')));
			$country_arr = array();
			foreach($countries_data as $countries_data_key => $countries_data_val) {
				if( in_array( $countries_data_val["country_id"] ,$sel_country ) )
				{} else {
					$country_arr[$countries_data_key]["id"] = $countries_data_val["country_id"];
					$country_arr[$countries_data_key]["country"] = $countries_data_val["country_name_en"];
				}
			}
			echo json_encode($country_arr);
			exit();
		}
	}
	
	public function updateorderAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$order_record = $this->SuperModel->Super_Get(T_PRODORDER,"order_serial =:TID","fetch",array('warray'=>array('TID'=>$data["order"])));
			if(empty($order_record)) {
				echo "error";
				exit();
			}
			if($data["orderchk"] == '1' || $data["orderchk"] == '2' || $data["orderchk"] == '3' || $data["orderchk"] == '4')		{
			}else {
				echo "invalid_input";
				exit();
			}
			$order_data["order_status"] = $data["orderchk"];
			if(!empty($data["tracktxt"])) {
				$order_data["order_tracking"] = strip_tags($data["tracktxt"]);
			}
			if($order_data["order_status"] == '3') {
				$order_data["order_shippeddate"] = date("Y-m-d H:i:s");
			} else if($order_data["order_status"] == '4') {
				$order_data["order_deliverdate"] = date("Y-m-d H:i:s");
			}
			$this->SuperModel->Super_Insert(T_PRODORDER,$order_data,"order_serial = '".$data["order"]."'");
			$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$order_record["order_clientid"])));
			if($order_data["order_status"] == '2' || $order_data["order_status"] == '3' || $order_data["order_status"] == '4') {
				$admin_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_type ='admin'","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email")));
				if($order_data["order_status"] == '2') {
					$message = "Your order with order number ".$order_record["order_serial"]." is ready to ship.";
					$admin_message = "Order with order number ".$order_record["order_serial"]." is ready to ship.";
				} else if($order_data["order_status"] == '3') {
					$url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';   
					$order_tracking = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $order_data["order_tracking"]);
					$message = "Your order with order number ".$order_record["order_serial"]." has been shipped. Shipping details:<br/>".nl2br($order_tracking);					
					$admin_message = "Order with order number ".$order_record["order_serial"]." has been shipped. Shipping details:<br/>".nl2br($order_tracking);
				} else {
					$message = "Your order with order number ".$order_record["order_serial"]." has been delivered.";
					$admin_message = "Order with order number ".$order_record["order_serial"]." has been delivered.";
				}
				$mail_const_data2 = array(
				"user_name" => $client_details[T_CLIENT_VAR.'client_name'],
				"user_email" => $client_details[T_CLIENT_VAR.'client_email'],
				"message" => $message,
				"subject" => "Order status"	
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				
				$mail_const_data3 = array(
				"user_name" => $admin_data[T_CLIENT_VAR.'client_name'],
				"user_email" => $admin_data[T_CLIENT_VAR.'client_email'],
				"message" => $admin_message,
				"subject" => "Order status"	
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data3);
				
				$notify_data["notification_type"] = '4';
				$notify_data["notification_by"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$notify_data["notification_to"] = $order_record["order_clientid"];
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_order"] = $data["order"];
				$notify_data["notification_status"] = '0';
				$isIns = $this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
			}
			echo "success";
			exit();
		}
	}
	
	public function couponstatAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_id =:CID and coupon_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'CID'=>myurl_decode($data["coupon"]))));
			if(empty($coupon_data)) {
				echo "error";
				exit();
			}
			if($data["tid"] == '1') {
				$stat_data["coupon_status"] = '1';
				$this->SuperModel->Super_Insert(T_COUPONS,$stat_data,"coupon_id = '".myurl_decode($data["coupon"])."'");
			} else {
				$stat_data["coupon_status"] = '0';
				$this->SuperModel->Super_Insert(T_COUPONS,$stat_data,"coupon_id = '".myurl_decode($data["coupon"])."'");
			}
			echo "success";
		}
		exit();
	}
	
	public function trashcouponAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_id =:CID and coupon_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'CID'=>myurl_decode($data["coupon"]))));
			if(empty($coupon_data)) {
				echo "error";
				exit();
			}
			$this->SuperModel->Super_Delete(T_COUPONS,"coupon_id = '".myurl_decode($data["coupon"])."'");
			echo "success";
		}
		exit();
	}
	
	public function resetcouponAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$product_cart = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["tid"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			}
			
		}
	}
	
	public function cartdetailsAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$joinArr = array(
				'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				'1' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval','store_closed','store_closed_date','store_closed_tilldate','store_acceptorder')),
			);
			$product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["tid"]))),$joinArr);
			if($product_details["store_closed"] == '1' && $product_details["store_acceptorder"] == '2') {
				echo "closed";
				exit();
			}
			if(!empty($product_details["product_defaultpic"])) {
				$product_pic = HTTP_PRODUCT_PIC_PATH.'/240/'.$product_details["product_defaultpic"];
			} else {
				$product_picarr = explode(",",$product_details["product_photos"]);
				if(file_exists(PRODUCT_PIC_PATH.'/240/'.$product_picarr[0]) && !empty($product_picarr[0])) { 
					$product_pic = HTTP_PRODUCT_PIC_PATH.'/240/'.$product_picarr[0];	
				}
			}
			$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["tid"]))));
			$sizes_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_title =:CID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["tid"]),'CID'=>$data["color"])));
			$sizeprice_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_title =:CID and color_size =:SID","fetch",array('warray'=>array('PID'=>base64_decode($data["tid"]),'CID'=>$data["color"],'SID'=>$data["size"])));
			$best_offers = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_title =:CID and color_size =:SID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["tid"]),'CID'=>$data["color"],'SID'=>$data["size"])));
			unset($best_offers[0]);
			$colors_arr = array_column($colorsize_data,"color_title");
			$colors_arr = array_unique($colors_arr);
			$sizes_arr = array_column($sizes_data,"color_size");
			$sizes_arr = array_unique($sizes_arr);
			usort($sizes_arr, "sizecmp");
			$view = new ViewModel();
			$view->setVariable('product_details',$product_details);
			$view->setVariable('product_pic',$product_pic);
			$view->setVariable('loggedUser',$this->loggedUser);
			if(!empty($colors_arr)) {
				$view->setVariable('colors_arr',$colors_arr);
			}
			if(!empty($sizes_arr)) {
				$view->setVariable('sizes_arr',$sizes_arr);
			}
			$view->setVariable('sizeprice_data',$sizeprice_data);
			$view->setVariable('best_offers',$best_offers);
			$view->setTerminal(true);
			return $view;
		} else {
			echo "error";
			exit();
		}
	}
	
	public function shipprofilesAction() {
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$shipping_profiles = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
		$view = new ViewModel();
		$view->setVariable('show', 'front_profile');
		$view->setVariable('shipping_profiles', $shipping_profiles);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
	public function viewshippingAction() {
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$shipping_id = $this->params()->fromRoute('key');
		if(empty($shipping_id)) {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$shipping_details = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_id =:TID and shipping_clientid =:UID","fetch",array('warray'=>array('TID'=>base64_decode($shipping_id),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
		if(empty($shipping_details)) {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		if(!empty($shipping_details["shipping_countries"])) {
			$shipping_countries = explode(",",$shipping_details["shipping_countries"]);
			if(!empty($shipping_countries)) {
				foreach($shipping_countries as $shipping_countries_key => $shipping_countries_val) {
					$country_data = $this->SuperModel->Super_Get(T_COUNTRIES,"country_id =:TID","fetch",array('warray'=>array('TID'=>$shipping_countries_val)));
					$country_names[] = $country_data["country_name_en"];
				}
			}
		}
		$view = new ViewModel();
		$view->setVariable('form', $form);
		$view->setVariable('show', 'front_profile');
		$view->setVariable('shipping_details', $shipping_details);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('country_names',$country_names);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;
	}
	
	public function removeprofileAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$shipping_profile = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_id =:TID and shipping_clientid =:UID","fetch",array('warray'=>array('TID'=>base64_decode($data["tid"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
			if(empty($shipping_profile)) {
				$this->frontSession['errorMsg'] = "You cannot access this page.";
				echo "blocked";
				exit();
			}
			$this->SuperModel->Super_Delete(T_SHIPPROFILES,"shipping_id = '".base64_decode($data["tid"])."' and shipping_clientid = '".$this->loggedUser->{T_CLIENT_VAR."client_id"}."'");
			$this->frontSession['successMsg'] = "Shipping profile has been successfully deleted.";
			echo "success";
			exit();
		}
	}
	
	public function manageshippingAction() {
		$shipping_id = $this->params()->fromRoute('key');
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		if(!empty($shipping_id)) {
			$shipping_details = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_id =:TID and shipping_clientid =:UID","fetch",array('warray'=>array('TID'=>base64_decode($shipping_id),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
			if(empty($shipping_details)) {
				$this->frontSession['errorMsg'] = "You cannot access this page.";
				return $this->redirect()->tourl(APPLICATION_URL.'/profile');
			}
		}
		$countries_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>array('country_id','country_name_en')));
		$country_arr = array(); $subcategory_arr = array();
		foreach($countries_data as $countries_data_key => $countries_data_val) {
			$country_arr[$countries_data_val["country_id"]] = $countries_data_val["country_name_en"];
		}
		$select_countries = array('0'=>"Please Select");
		$country_arr = array_merge($select_countries,$country_arr);
		$form = new ProfileForm();
		$form->shipping($country_arr);
		$pageHeading = "Add Shipping Profile";
		if(!empty($shipping_details)) {
			$pageHeading = "Update Shipping Profile";
			$shipping_details["shipping_countries"] = explode(",",$shipping_details["shipping_countries"]);
			if(!empty($shipping_details["shipping_free"])) {
				$shipping_details["shipping_rate"] = '';
			}
			$form->populateValues($shipping_details);
		}
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			unset($posted_data["startedbtn"]);
			if(empty($posted_data["shipping_rate"]) && empty($posted_data["shipping_free"])) {
				$this->frontSession['errorMsg'] = "Please enter either Standard shipping rate or check the Free Shipping option.";
				return $this->redirect()->tourl(APPLICATION_URL.'/manage-shipping');
			}
			$posted_data["shipping_countries"] = implode(",",$posted_data["shipping_countries"]);
			$posted_data["shipping_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
			if(empty($posted_data["shipping_rate"])) {
				$posted_data["shipping_rate"] = '0';
			}
			if(!empty($shipping_id)) {
				$posted_data = (array) $posted_data;
				$posted_data["shipping_name"] = strip_tags($posted_data["shipping_name"]);
				$posted_data["shipping_rate"] = strip_tags($posted_data["shipping_rate"]);
				$posted_data["shipping_free"] = strip_tags($posted_data["shipping_free"]);
				$posted_data["shipping_globalrate"] = strip_tags($posted_data["shipping_globalrate"]);
				$posted_data["shipping_addrate"] = strip_tags($posted_data["shipping_addrate"]);
				$isInsert = $this->SuperModel->Super_Insert(T_SHIPPROFILES,$posted_data,"shipping_id = '".base64_decode($shipping_id)."'");
				$this->frontSession['successMsg'] = "Shipping profile has been updated.";
			} else {
				$posted_data = (array) $posted_data;
				$posted_data["shipping_name"] = strip_tags($posted_data["shipping_name"]);
				$posted_data["shipping_rate"] = strip_tags($posted_data["shipping_rate"]);
				$posted_data["shipping_free"] = strip_tags($posted_data["shipping_free"]);
				$posted_data["shipping_globalrate"] = strip_tags($posted_data["shipping_globalrate"]);
				$posted_data["shipping_addrate"] = strip_tags($posted_data["shipping_addrate"]);
				$isInsert = $this->SuperModel->Super_Insert(T_SHIPPROFILES,$posted_data);
				$this->frontSession['successMsg'] = "Shipping profile has been created.";
			}
			if($isInsert->error) {
				$this->frontSession['errorMsg'] = "Something went wrong, please check again.";
				return $this->redirect()->tourl(APPLICATION_URL.'/manage-shipping');
			}
			return $this->redirect()->tourl(APPLICATION_URL.'/shipping-profiles');
		}
		$view = new ViewModel();
		$view->setVariable('form', $form);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		$view->setVariable('pageHeading',$pageHeading);
		return $view;
	}
	
	public function manageproductsAction() {
		$view = new ViewModel();	
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$_SESSION["logstat"] = '1';
		$joinArr = array(
			'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Inner','3'=>array('category_feild')),
		);
		$products_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_delstatus != '1'","fetchAll",array('order'=>'product_order asc','warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)),$joinArr);
		$view->setVariable('show', 'front_profile');
		$view->setVariable('products_data', $products_data);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
	public function publishproductAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			foreach($data as $data_key => $data_val) {
				foreach($data_val as $dataval_key => $datavalue) {
					if($data_key == 'product_price') {
						if($datavalue < 0) {
							echo "priceerror";
							exit();
						}
						if (!is_numeric($datavalue)) {
							echo "priceerror";
							exit();
						}
						if(strlen($datavalue) > 10) {
							echo "priceerror";
							exit();
						}
					}
					if($data_key != 'color' && $data_key != 'size' && $data_key != 'qty' && $data_key != 'ids') {
						$new_array[$dataval_key][$data_key] = strip_tags($datavalue);
					}
				}
			}
			foreach($new_array as $newarr_key => $newarr_val) {
				$id = base64_decode($newarr_val["product_zone"]);
				$product_record = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:TID","fetch",array('warray'=>array('TID'=>$id)));
				if(!empty($product_record)) {
					if($product_record["product_isdigital"] != '1') {
						$newarr_val["product_price"] = $newarr_val["color_price"];
					    unset($newarr_val["product_zone"]);
					    unset($newarr_val["color_price"]);
					    $isInserted = $this->SuperModel->Super_Insert(T_PRODUCTS,$newarr_val,"product_id = '".$id."' and product_clientid = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
					    if(!empty($isInserted->success)) {
						    if(!empty($data["color"])) {
							    foreach($data["color"] as $color_key => $color_val) {
								    $colorprice_data = $this->SuperModel->Super_Get(T_PROQTY,"color_title =:TID and color_size =:SID and color_qty =:QID and color_productid =:PID and color_clientid=:UID and color_id =:ZID","fetch",array('warray'=>array('TID'=>$color_val,'SID'=>$data["size"][$color_key],'QID'=>$data["qty"][$color_key],'PID'=>$id,'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"},'ZID'=>$data["ids"][$color_key])));
								    if(!empty($colorprice_data)) {
									    $prod_pricez["color_price"] = $data["color_price"][$color_key];
									    $prod_pricez["color_fixedprice"] = $data["color_price"][$color_key];
									    $jj = $this->SuperModel->Super_Insert(T_PROQTY,$prod_pricez,"color_title = '".$color_val."' and color_size = '".$data["size"][$color_key]."' and color_qty = '".$data["qty"][$color_key]."' and color_productid = '".$id."' and color_clientid = '".$this->loggedUser->{T_CLIENT_VAR."client_id"}."'");
								    }
							    }
 						    }
					    }
					}
				}
			}
			echo "success";
			exit();
		}
	}
	
	public function trashproductAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'PID'=>myurl_decode($data["prod"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				if($product_data["product_status"] == '1') {
					$prod_data["product_delstatus"] = '1';
					$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_id = '".myurl_decode($data["prod"])."'");
					echo "success";
					exit();
				} else {
					$this->SuperModel->Super_Delete(T_PRODUCTS,"product_id = '".myurl_decode($data["prod"])."'");
					echo "success";
					exit();
				}
			}
		} else {
			echo "error";
			exit();
		}
	}
	
	public function viewproductAction() {
		$prod_id = $this->params()->fromRoute("key");
		if(empty($prod_id)) {
			$this->frontSession['errorMsg'] = "No such product found.";
			return $this->redirect()->tourl(APPLICATION_URL);	
		}
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$joinArr = array(
			'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'PID'=>myurl_decode($prod_id))),$joinArr);
		if(empty($product_data)) {
			$this->frontSession['errorMsg'] = "No such product found.";
			return $this->redirect()->tourl(APPLICATION_URL);
		}
		$view = new ViewModel();
		$view->setVariable('show', 'front_profile');
		$view->setVariable('product_data', $product_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
	public function badgerequestAction() {
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$imagePlugin = $this->Image();
		$request = $this->getRequest();
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$files =  $request->getFiles()->toArray();
			if($store_data["store_verification"] == '1') {
				$this->frontSession['errorMsg'] = "Store has already been verified.";
				return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
			}
			if($files['seller_doc1']['name']!=''){
				if (strpos(file_get_contents($files['seller_doc1']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc1']['tmp_name']), '<?=') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc1']['tmp_name']), '<? ') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				$is_uploaded3 = $imagePlugin->universal_upload(array("directory"=>STORE_DOC_PATH,"files_array"=>array('seller_doc1'=>$files['seller_doc1']),"multiple"=>false,"crop"=>false),'Both');	
				
				if($is_uploaded3->success=='1' && $is_uploaded3->media_path!=''){
					$store_doc1 = $is_uploaded3->media_path;
				}
			}
			if($files['seller_doc2']['name']!=''){
				if (strpos(file_get_contents($files['seller_doc2']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc2']['tmp_name']), '<?=') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc2']['tmp_name']), '<? ') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				$is_uploaded4 = $imagePlugin->universal_upload(array("directory"=>STORE_DOC_PATH,"files_array"=>array('seller_doc2'=>$files['seller_doc2']),"multiple"=>false,"crop"=>false),'Both');	
				if($is_uploaded4->success=='1' && $is_uploaded4->media_path!=''){
					$store_doc2 = $is_uploaded4->media_path;
				}
			}
			if($files['seller_doc3']['name']!=''){
				if (strpos(file_get_contents($files['seller_doc3']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc3']['tmp_name']), '<?=') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}

				if (strpos(file_get_contents($files['seller_doc3']['tmp_name']), '<? ') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				$is_uploaded5 = $imagePlugin->universal_upload(array("directory"=>STORE_DOC_PATH,"files_array"=>array('seller_doc3'=>$files['seller_doc3']),"multiple"=>false,"crop"=>false),'Both');
				if($is_uploaded5->success=='1' && $is_uploaded5->media_path!=''){
					$store_doc3 = $is_uploaded5->media_path;
				}
			}
			if(!empty($store_doc1)) {
				$data["store_doc1"] = $store_doc1;
			} else {
				$data["store_doc1"] = $store_data["store_doc1"];
			}
			if(!empty($store_doc2)) {
				$data["store_doc2"] = $store_doc2;
			} else {
				$data["store_doc2"] = $store_data["store_doc2"];
			}
			if(!empty($store_doc3)) {
				$data["store_doc3"] = $store_doc3;
			} else {
				$data["store_doc3"] = $store_data["store_doc3"];
			}
			$data["store_clientid"] = $this->loggedUser->yurt90w_client_id;
			$data["store_verification"] = '3';
			
			if(empty($store_data)) {
				$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data);	
				$notify_datas["notification_type"] = '2';
				$notify_datas["notification_by"] = $this->loggedUser->yurt90w_client_id;
				$notify_datas["notification_to"] = '1';
				$notify_datas["notification_readstatus"] = '0';
				$notify_datas["notification_date"] = date("Y-m-d H:i:s");
				$notify_datas["notification_subscriberid"] = $isInsert->inserted_id;
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_datas);
				
				$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has sent the badge verification request.",
							"subject" => "Badge verification"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
								
				if(!empty($isInsert->inserted_id)) {
					$this->frontSession['successMsg'] = 'Badge verification request has been successfully sent to the admin.';
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');	
				} else {
					$this->frontSession['errorMsg'] = 'Please check entered information again.';
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
			} else {
				if($data["store_doc1"] != $store_data["store_doc1"] || $data["store_doc2"] != $store_data["store_doc2"] || $data["store_doc3"] != $store_data["store_doc3"]) {
					$notify_datas["notification_type"] = '2';
					$notify_datas["notification_by"] = $this->loggedUser->yurt90w_client_id;
					$notify_datas["notification_to"] = '1';
					$notify_datas["notification_readstatus"] = '0';
					$notify_datas["notification_date"] = date("Y-m-d H:i:s");
					$notify_datas["notification_subscriberid"] = $store_data["store_id"];
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_datas);
					$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has sent the badge verification request.",
							"subject" => "Badge verification"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);	
					
					$data["store_verification"] = '3';
					$data["store_badgedeclinetxt"] = '';
					$this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$this->loggedUser->yurt90w_client_id."'");
					$this->frontSession['successMsg'] = 'Badge verification request has been successfully sent to the admin.';
				return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				} else {
					$this->frontSession['errorMsg'] = 'New request is same as the previous one.';
				return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
					
			}
		}
	}
	
	public function editcouponAction() {
		$coupon = $this->params()->fromRoute('key');
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$product_list = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID","fetchAll",array('fields'=>array('product_id','product_title'),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
		$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_id =:TID and coupon_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'TID'=>myurl_decode($coupon))));
		if(empty($coupon_data)) {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$form = new ProfileForm();
		$form->coupon();
		$coupon_data["coupon_start_date"] = date("m/d/Y",strtotime($coupon_data["coupon_start_date"]));
		$coupon_data["coupon_end_date"] = date("m/d/Y",strtotime($coupon_data["coupon_end_date"]));
		if(!empty($coupon_data)) {
			$form->populateValues($coupon_data);
		}
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$coupon_details = $this->SuperModel->Super_Get(T_COUPONS,"coupon_code =:TID and coupon_id != '".myurl_decode($coupon)."'","fetch",array('warray'=>array('TID'=>strip_tags(trim($posted_data["coupon_code"])))));
			if(!empty($coupon_details)) {
				$this->frontSession['errorMsg'] = 'Coupon code is already taken. Please use another code.';
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-coupon/'.$coupon);
			}
			$coupon_data["coupon_title"] = strip_tags($posted_data["coupon_title"]);
			$coupon_data["coupon_code"] = strip_tags(trim($posted_data["coupon_code"]));
			$coupon_data["coupon_discount"] = strip_tags($posted_data["coupon_discount"]);
			if($posted_data["coupon_discount"] > 100 || $posted_data["coupon_discount"] < 0) {
				$this->frontSession['errorMsg'] = 'Please enter valid coupon discount.';
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-coupon/'.$coupon);
			}
			if(!empty($posted_data["coupon_status"])) {
				$coupon_data["coupon_status"] = '0';
			} else {
				$coupon_data["coupon_status"] = '1';
			}
			if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$posted_data["coupon_start_date"])) {
				$coupon_data["coupon_start_date"] = $posted_data["coupon_start_date"];
			} else {
				$coupon_data["coupon_start_date"] = date("Y-m-d",strtotime($posted_data["coupon_start_date"]));
			}
			if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$posted_data["coupon_end_date"])) {
				$coupon_data["coupon_end_date"] = $posted_data["coupon_end_date"];
			} else {
				$coupon_data["coupon_end_date"] = date("Y-m-d",strtotime($posted_data["coupon_end_date"]));
			}
			if(strtotime($coupon_data["coupon_start_date"]) >= strtotime($coupon_data["coupon_end_date"])) {
				$this->frontSession['errorMsg'] = 'End date must be greater than start date.';
			} else {
			$coupon_data["coupon_clientid"] = $this->loggedUser->yurt90w_client_id;
			if($posted_data["radio-group"] == '1') {
				if(empty($posted_data["product_name"])) {
					$this->frontSession['errorMsg'] = 'Please select product.';
					return $this->redirect()->tourl(APPLICATION_URL.'/add-coupon');	
				}
				$coupon_data["coupon_product"] = implode(",",$posted_data["product_name"]);
				$coupon_data["coupon_type"] = '1';
			} else if($posted_data["radio-group"] == '2') {
				$coupon_data["coupon_product"] = 'ALL';
				$coupon_data["coupon_type"] = '2';
			} else if($posted_data["radio-group"] == '3') {
				if(empty($posted_data["product_name"])) {
					$this->frontSession['errorMsg'] = 'Please select product.';
					return $this->redirect()->tourl(APPLICATION_URL.'/add-coupon');	
				}
				$prod_names = $posted_data["product_name"];
				if(count($prod_names) > 1) {
					$this->frontSession['errorMsg'] = 'Please select valid product.';
					return $this->redirect()->tourl(APPLICATION_URL.'/add-coupon');
				}
				$coupon_data["coupon_product"] = implode(",",$posted_data["product_name"]);
				$coupon_data["coupon_type"] = '3';
			}
			$this->SuperModel->Super_Insert(T_COUPONS,$coupon_data,"coupon_id = '".myurl_decode($coupon)."'");
			$this->frontSession['successMsg'] = 'Coupon has been updated successfully.';
			return $this->redirect()->tourl(APPLICATION_URL.'/manage-coupon');	
			}
		}
		$view = new ViewModel();	
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('product_list',$product_list);
		$view->setVariable('coupon_data',$coupon_data);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
	public function uploadproductAction() {
		if(!empty($_FILES["file"])) {
		    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
	    	require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
	    	require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
			$file_size = $_FILES["file"]["size"] / 1024; 
			$file_mb = $file_size / 1024;
			if($file_mb > 10) {
				echo json_encode(false);
				exit();
			}
			$target_file = basename($_FILES['file']['name']);
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			if(in_array($imageFileType, array('png', 'jpg', 'jpeg'))) {
			    \Tinify\setKey(TINY_KEY);
				if (strpos(file_get_contents($_FILES['file']['tmp_name']), '<?php') !== false) 
				{
					echo json_encode(false);
					exit();
				}
				$imagePlugin = $this->Image();
				$is_uploaded = $imagePlugin->universal_upload(array("directory"=>PRODUCT_PIC_PATH,"files_array"=>$_FILES,"url"=>HTTP_PRODUCT_PIC_PATH,"ratio"=>true,"crop"=>false));	
				if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){
					$prod_name = $is_uploaded->media_path;
					if(file_exists(PRODUCT_PIC_PATH."/".$prod_name)) {	
						$source = \Tinify\fromFile(PRODUCT_PIC_PATH."/".$prod_name);
						$source->toFile(PRODUCT_PIC_PATH."/".$prod_name);
					}
					if(file_exists(PRODUCT_PIC_PATH."/240".$prod_name)) {	
						$source = \Tinify\fromFile(PRODUCT_PIC_PATH."/240/".$prod_name);
						$source->toFile(PRODUCT_PIC_PATH."/240/".$prod_name);
					}
					if(file_exists(PRODUCT_PIC_PATH."/270/".$prod_name)) {	
						$source = \Tinify\fromFile(PRODUCT_PIC_PATH."/270/".$prod_name);
						$source->toFile(PRODUCT_PIC_PATH."/270/".$prod_name);
					}
					if(file_exists(PRODUCT_PIC_PATH."/thumb/".$prod_name)) {	
						$source = \Tinify\fromFile(PRODUCT_PIC_PATH."/thumb/".$prod_name);
						$source->toFile(PRODUCT_PIC_PATH."/thumb/".$prod_name);
					}
					$_SESSION["propic"][] = $prod_name;
					echo json_encode($is_uploaded);
					exit();
				}
			} else {
				echo json_encode(false);
				exit();
			}
		}
	}
	
	public function trashpropicAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $request->getPost();
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'PID'=>myurl_decode($data["prod"]))));
			$pro_photos = explode(",",$product_data["product_photos"]);
			if (array_key_exists($data["tid"],$pro_photos))
  			{
				$imagePlugin = $this->Image();
				$imagePlugin->universal_unlink($pro_photos[$data["tid"]],array("directory"=>PRODUCT_PIC_PATH));
				$imagePlugin->universal_unlink($pro_photos[$data["tid"]],array("directory"=>PRODUCT_PIC_PATH.'/60'));
				$imagePlugin->universal_unlink($pro_photos[$data["tid"]],array("directory"=>PRODUCT_PIC_PATH.'/160'));
				$imagePlugin->universal_unlink($pro_photos[$data["tid"]],array("directory"=>PRODUCT_PIC_PATH.'/240'));
				$imagePlugin->universal_unlink($pro_photos[$data["tid"]],array("directory"=>PRODUCT_PIC_PATH.'/thumb'));
				if($product_data["product_defaultpic"] == $pro_photos[$data["tid"]]) {
					$prok_data["product_defaultpic"] = '';
					$this->SuperModel->Super_Insert(T_PRODUCTS,$prok_data,"product_id = '".myurl_decode($data["prod"])."'");
				}
				unset($pro_photos[$data["tid"]]);
			}
			if(!empty($pro_photos)) {
				$pro_photos = array_filter($pro_photos);
				foreach($pro_photos as $pro_photos_key => $pro_photos_val) {
					if(!file_exists(PRODUCT_PIC_PATH.'/240/'.$pro_photos_val)) {
						unset($pro_photos[$pro_photos_key]);
					}
				}
				$prod_photos = implode(",",$pro_photos);
			}
			$prod_updata["product_photos"] = $prod_photos;
			$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_updata,"product_id = '".myurl_decode($data["prod"])."'");
			echo implode(",",$pro_photos);
			exit();
		}
		
	}
	
	public function removefavoriteAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $request->getPost();
			$favorite_record = $this->SuperModel->Super_Get(T_FAVOURITE,"favourite_by =:UID and favourite_storeid =:SID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'SID'=>base64_decode($data["fav"]))));
			if(empty($favorite_record)) {
				echo "error";
				exit();
			}
			$this->SuperModel->Super_Delete(T_FAVOURITE,"favourite_by = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."' and favourite_storeid = '".base64_decode($data["fav"])."'");
			echo "success";
			exit();
		} else {
			echo "error";
			exit();
		}
	}
	
	public function favoriteshopsAction() {
		$_SESSION["logstat"] = '2';
		$joinArr = array(
			'0'=>array('0'=>T_STORE,'1'=>'favourite_storeid = store_id','2'=>'Inner','3'=>array('store_name','store_clientid','store_logo','store_banner')),
			'1'=>array('0'=>T_CLIENTS,'1'=>'store_clientid = '.T_CLIENT_VAR.'client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
		$favorite_shops = $this->SuperModel->Super_Get(T_FAVOURITE,"favourite_by =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)),$joinArr);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$view = new ViewModel();
		$view->setVariable('favorite_shops', $favorite_shops);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;
	}
	
	public function wishlistAction() {
		$_SESSION["logstat"] = '2';
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'wish_list_productid = product_id','2'=>'Inner','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_id'))
		);
		$wish_list = $this->SuperModel->Super_Get(T_WISHLIST,"wish_list_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$colorsize_record = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID","fetchAll",array('warray'=>array('TID')));
		$view = new ViewModel();
		$view->setVariable('wish_list', $wish_list);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;
	}
	
	public function trashwishlistAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["prod"] = strip_tags($posted_data["prod"]);
			$wishlist = $this->SuperModel->Super_Get(T_WISHLIST,"wish_list_productid =:PID and wish_list_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'PID'=>myurl_decode($posted_data["prod"]))));
			if(empty($wishlist)) {
				echo "error";
				exit();
			} else {
				$this->SuperModel->Super_Delete(T_WISHLIST,"wish_list_productid = '".myurl_decode($posted_data["prod"])."' and wish_list_clientid = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function replycommentAction() {
		$request = $this->getRequest();
		if($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()) {
				$data = $this->params()->fromPost();
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_clientid','warray'=>array('PID'=>base64_decode($data["prod"]))));
				if(empty($product_data)) {
					echo "noproduct";
					exit();
				}
				if($this->loggedUser->{T_CLIENT_VAR.'client_id'} != $product_data["product_clientid"]) {
					echo "invalid_user";
					exit();
				}
				if(empty(trim($data["replytxt"])) || empty($data["prod"])) {
					echo "error";
					exit();
				}
				$reply_record = $this->SuperModel->Super_Get(T_REPLIES,"comment_reply_clientid =:UID and comment_reply_product =:PID and comment_reply_cmtid =:CID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'PID'=>base64_decode($data["prod"]),'CID'=>base64_decode($data["tid"]))));
				if(!empty($reply_record)) {
					echo "already";
					exit();
				}
				$data["replytxt"] = strip_tags($data["replytxt"],'<br>');
				$reply_data["comment_reply_product"] = base64_decode($data["prod"]);
				$reply_data["comment_reply_cmtid"] = base64_decode($data["tid"]);
				$reply_data["comment_reply_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$reply_data["comment_reply_text"] = $data["replytxt"];
				$reply_data["comment_reply_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_REPLIES,$reply_data);
				echo $reply_data["comment_reply_text"];
				exit();
			}
		}
	}
	
	public function procommentAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$data["cmnt"] = strip_tags($data["cmnt"],'<br>');
				if(empty($data["cmnt"]) || empty($data["prod"])) {
					echo "error";
					exit();
				}
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["prod"]))));
				if(empty($product_data)) {
					echo "noproduct";
					exit();
				}
				$comment_data["procomment_pid"] = base64_decode($data["prod"]);
				$comment_data["procomment_uid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$comment_data["procomment_text"] = $data["cmnt"];
				$comment_data["procomment_date"] = date("Y-m-d H:i:s");
				$is_insert = $this->SuperModel->Super_Insert(T_PRODCOMMENT,$comment_data);
				$joinArr2 = array(
					'0' => array('0' => T_CLIENTS, '1' => 'procomment_uid = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
				);
				$last_comment = $this->SuperModel->Super_Get(T_PRODCOMMENT,"procomment_id =:PID","fetch",array('warray'=>array('PID'=>$is_insert->inserted_id)),$joinArr2); 
				$view = new ViewModel();
				$view->setVariable('last_comment', $last_comment);
				$view->setTerminal(true);
				return $view;
			}
		}
	}
	
	public function reportreviewAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$data["review_txt"] = strip_tags($data["review_txt"],'<br>');
				if(empty($data["review_txt"]) || empty($data["tid"])) {
					echo "error";
					exit();
				}
				$review_data = $this->SuperModel->Super_Get(T_REVIEWS,"review_id =:CID","fetch",array('warray'=>array('CID'=>base64_decode($data["tid"]))));
				if(empty($review_data)) {
					echo "noreview";
					exit();
				}
				$report_record = $this->SuperModel->Super_Get(T_REVREPORT,"review_report_rateid =:CID and review_report_uid =:UID","fetch",array('warray'=>array('CID'=>base64_decode($data["tid"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				if(!empty($report_record)) {
					echo "already";
					exit();
				}
				$report_data["review_report_rateid"] = base64_decode($data["tid"]);
				$report_data["review_report_uid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$report_data["review_report_text"] = $data["review_txt"];
				$report_data["review_report_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_REVREPORT,$report_data);
				
				$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has reported the review ".$review_data["review_text"].".",
							"subject" => "Reported review"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				
				echo "success";
				exit();
			}
		}
	}
	
	public function reportcommentAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$data["reptxt"] = strip_tags($data["reptxt"],'<br>');
				if(empty($data["reptxt"]) || empty($data["cmt"])) {
					echo "error";
					exit();
				}
				$comment_data = $this->SuperModel->Super_Get(T_PRODCOMMENT,"procomment_id =:CID","fetch",array('warray'=>array('CID'=>base64_decode($data["cmt"]))));
				if(empty($comment_data)) {
					echo "nocomment";
					exit();
				}
				$report_record = $this->SuperModel->Super_Get(T_CMTREPORT,"comment_report_cid =:CID and comment_report_uid =:UID","fetch",array('warray'=>array('CID'=>base64_decode($data["cmt"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				if(!empty($report_record)) {
					echo "already";
					exit();
				}
				$report_data["comment_report_cid"] = base64_decode($data["cmt"]);
				$report_data["comment_report_uid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$report_data["comment_report_text"] = $data["reptxt"];
				$report_data["comment_report_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_CMTREPORT,$report_data);
				
				$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has reported the comment ".$comment_data["procomment_text"].".",
							"subject" => "Reported comment"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				
				echo "success";
				exit();
			}
		}
	}
	
	public function reportproductAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$data["reptxt"] = strip_tags($data["reptxt"],'<br>');
				if(empty($data["reptxt"]) || empty($data["prod"])) {
					echo "error";
					exit();
				}
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["prod"]))));
				if(empty($product_data)) {
					echo "noproduct";
					exit();
				}
				if($this->loggedUser->{T_CLIENT_VAR.'client_id'} == $product_data["product_clientid"]) {
					echo "notvalid";
					exit();
				}
				$report_record = $this->SuperModel->Super_Get(T_PRODREPORT,"product_report_pid =:PID and product_report_uid =:UID","fetch",array('warray'=>array('PID'=>base64_decode($data["prod"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				if(!empty($report_record)) {
					echo "already";
					exit();
				}
				$report_data["product_report_pid"] = base64_decode($data["prod"]);
				$report_data["product_report_uid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
				$report_data["product_report_text"] = $data["reptxt"];
				$report_data["product_report_date"] = date("Y-m-d H:i:s");
				$this->SuperModel->Super_Insert(T_PRODREPORT,$report_data);
				
				$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has reported the product with title ".$product_data["product_title"].".",
							"subject" => "Reported product"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				
				echo "success";
				exit();
			}
		}
	}
	
	public function removewishAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["prod"] = strip_tags($posted_data["prod"]);
			$wishlist =	$this->SuperModel->Super_Get(T_WISHLIST,"wish_list_productid =:PID and wish_list_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'PID'=>myurl_decode($posted_data["prod"]))));	
			if(empty($wishlist)) {
				echo "error";
				exit();
			} else {
				$this->SuperModel->Super_Delete(T_WISHLIST,"wish_list_productid = '".myurl_decode($posted_data["prod"])."' and wish_list_clientid = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function addwishlistAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost(); 
			$posted_data["prod"] = strip_tags($posted_data["prod"]);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_id','product_clientid'),'warray'=>array('PID'=>myurl_decode($posted_data["prod"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				if($this->loggedUser->{T_CLIENT_VAR.'client_id'} == $product_data["product_clientid"]) {
					echo "blocked";
					exit();
				}
				$wishlist =	$this->SuperModel->Super_Get(T_WISHLIST,"wish_list_productid =:PID and wish_list_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'},'PID'=>myurl_decode($posted_data["prod"]))));	
				if(empty($wishlist)) {		
					$prod_rec["wish_list_productid"] = $product_data["product_id"];
					$prod_rec["wish_list_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
					$prod_rec["wish_list_date"] = date("Y-m-d H:i:s");
					$this->SuperModel->Super_Insert(T_WISHLIST,$prod_rec);
					echo "success";
					exit();
				} else {
					echo "already";
					exit();
				}
			}
		}
	}
	
	public function makepropicAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["propic"] = strip_tags($posted_data["propic"]);
			$posted_data["prod"] = strip_tags($posted_data["prod"]);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_id','warray'=>array('PID'=>myurl_decode($posted_data["prod"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				$upd_data["product_defaultpic"] = $posted_data["propic"];
				$this->SuperModel->Super_Insert(T_PRODUCTS,$upd_data,"product_id = '".myurl_decode($posted_data["prod"])."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function showpropicAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(empty($posted_data["prod"])) {
				echo "error";
				exit();
			} else {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>myurl_decode($posted_data["prod"]))));
				if(empty($product_data)) {
					echo "error";
					exit();
				}
				$view = new ViewModel();
				$view->setVariable('product_pics', explode(",",$product_data["product_photos"]));
				$view->setVariable('product_defpic', $product_data["product_defaultpic"]);
				$view->setTerminal(true);
				return $view;
			}
		}
	}
	
	public function subcategoriesAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$subcategory_arr = array();
				$subcategory_data = $this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,"subcategory_categoryid =:TID","fetchAll",array('warray'=>array('TID'=>$data["category"])));
				if(!empty($subcategory_data)) {
					foreach($subcategory_data as $subcategory_data_key => $subcategory_data_val) {
						$subcategory_arr[$subcategory_data_key]["id"] = $subcategory_data_val["subcategory_id"];
						$subcategory_arr[$subcategory_data_key]["title"] = $subcategory_data_val["subcategory_title"];
					}
				}
				echo json_encode($subcategory_arr);
				exit();
			}
		}
	}
	
	public function uploadscreenshotAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
			    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
				require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
				require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
				require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
				require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
				require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
				$data = $this->params()->fromPost();
				$files =  $request->getFiles()->toArray();
				if(isset($files['file']['name']) and !empty($files['file']['name'])){
					$data = array_merge($request->getPost()->toArray(),$files);
				}
				$data['chat_image']=$data['file'];	
				if(isset($files['file']['name']) and $files['file']['name']!=''){
					$target_file = basename($files['file']['name']);
					$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

					if(Isinfectedfile($files['file'])){
						echo json_encode(array("status" => 400,"message" => "File is infected.", "data" => array() ));
						exit;
					}
					if($files['file']["size"] > 10485760) {
						echo json_encode(array("status" => 400,"message" => "Invalid Image.", "data" => array() ));
						exit();
					}
					// start uploading work
					if(in_array($imageFileType, array('png', 'jpg', 'jpeg'))) {
						$imagePlugin = $this->Image();
						$is_uploaded = $imagePlugin->universal_upload(array("directory"=>REVIEW_PATH,"files_array"=>$files,"url"=>HTTP_REVIEW_PATH,"ratio"=>true,"crop"=>true,'thumbs'=>array('200'=>array("width"=>200,"height"=>200,"crop"=>false,"ratio"=>true,"proper"=>true),'300'=>array("width"=>300,"height"=>300,"crop"=>false,"ratio"=>true,"proper"=>true),'900x600'=>array("width"=>900,"height"=>600,"proper"=>true))));	
						if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){
						    \Tinify\setKey(TINY_KEY);
							if(file_exists(REVIEW_PATH."/".$is_uploaded->media_path)) {	
								$source = \Tinify\fromFile(REVIEW_PATH."/".$is_uploaded->media_path);
								$source->toFile(REVIEW_PATH."/".$is_uploaded->media_path);
							}
							echo json_encode(array("status" => 200,"message" => "Image updated successfully.", "data" => array('image'=>$is_uploaded->media_path) ));
						} elseif($is_uploaded->error=='1' ){
							echo json_encode(array("status" => 400,"message" => "Invalid Image.", "data" => array() ));
						}

					} else {
						echo json_encode(array("status" => 400,"message" => "Invalid Image.", "data" => array() ));
					}
				} else {
					echo json_encode(array("status" => 400,"message" => "Something went wrong, please try again.", "data" => array() ));
				}
			}
			exit;

		} else {
			$this->frontSession['errorMsg'] = 'Unauthorized access!!';
			return $this->redirect()->tourl(APPLICATION_URL."/".$client_url);
		}
	}
	
	public function removescreenshotAction()
	{
		$mainDir = JOB_IMAGES_PATH;
		$HTTP_Dir = HTTP_JOB_IMAGES_PATH;
		$mainPath = $mainDir;
		$tempFolder = $mainDir . '/';
		$httpTempFolder = $HTTP_Dir . '/';

		$imagePlugin = $this->Image();
		if (isset($_REQUEST['file'])) {
			$imageName = $_REQUEST['file'];
			$request = $this->getRequest();

			$uploadPathDir = $tempFolder;
			$isRemoved = $imagePlugin->universal_unlink($imageName, array("directory" => $uploadPathDir));
		} 
		echo json_encode(true);
		exit();
	}
	
	public function uploadpostfileAction()
	{
		require_once(ROOT_PATH.'/vendor/UploadHandler.php');
		$uploader = new UploadHandler();
		// Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$uploader->allowedExtensions = array(); // all files types allowed by default
		// Specify max file size in bytes.
		$uploader->sizeLimit = null;

		// Specify the input name set in the javascript.
		$uploader->inputName = "qqfile"; // matches Fine Uploader's default inputName value by default

		// If you want to use the chunking/resume feature, specify the folder to temporarily save parts.
		$uploader->chunksFolder = VIDEO_PATH;
		
		$method = get_request_method();
		
		if ($method == "POST") {
			header("Content-Type: text/plain");
			// Assumes you have a chunking.success.endpoint set to point here with a query parameter of "done".
			// For example: /myserver/handlers/endpoint.php?done
			$id = $this->params()->fromRoute('id');
			$filesize = $_FILES["qqfile"]["size"];
			if(!empty($id)) {
				$result = $uploader->combineChunks(VIDEO_PATH);
				chmod(VIDEO_PATH.'/'.$result["uuid"],0777);
				$directory = VIDEO_PATH.'/'.$result["uuid"];
				$files = scandir ($directory);
				$firstFile = $files[2];
				chmod(VIDEO_PATH.'/'.$result["uuid"].'/'.$firstFile,0777);
				$uploaded_image_extension = getFileExtension($firstFile);
				$new_name = time()."-".rand(1,100000).".".$uploaded_image_extension;
				rename(VIDEO_PATH.'/'.$result["uuid"].'/'.$firstFile, VIDEO_PATH.'/'.$new_name);
				rmdir(VIDEO_PATH.'/'.$result["uuid"]);
				$result["uploadName"] = $new_name;
				if($uploaded_image_extension == 'mov' || $uploaded_image_extension == 'MOV') {
				    $expl = explode(".",$result['uploadName']);
				    $command = "ffmpeg -i ".VIDEO_PATH."/".$expl[0].".mov -vcodec copy -acodec copy ".VIDEO_PATH."/".$expl[0].".mp4";
                    $ect = exec($command);
					$result["uploadName"] = $expl[0].".mp4";
				}
				if ($uploaded_image_extension == 'mp4' || $uploaded_image_extension == 'MP4' || $uploaded_image_extension == 'mov' || $uploaded_image_extension == 'MOV') {
					//$command = "ffmpeg -i ".VIDEO_PATH.'/'.$new_name." -b:v 2500k -bufsize $bitrate ".$new_name;
					//exec($command);
				} else {
					unlink(VIDEO_PATH . '/' . $result["uploadName"]);
					$result["uploadName"] = 'error.mp4';
				}
				if (strpos(file_get_contents(VIDEO_PATH.'/'.$result["uploadName"]), '<?php') !== false) 
				{
					unlink(VIDEO_PATH.'/'. $result["uploadName"]);	
					$result["uploadName"] = 'error.mp4';
				}
			}
			// Handles upload requests
			else {
				// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
				$result = $uploader->handleUpload(VIDEO_PATH);
				// To return a name used for uploaded file you can use the following line.
				$result["uploadName"] = $uploader->getUploadName();
				if($filesize < 726800) {
					chmod(VIDEO_PATH.'/'.$result["uuid"],0777);
					chmod(VIDEO_PATH.'/'.$result["uuid"].'/'.$result["uploadName"],0777);
					$uploaded_image_extension = getFileExtension(VIDEO_PATH.'/'.$result["uuid"].'/'.$result["uploadName"]);
					$new_name = time()."-".rand(1,100000).".".$uploaded_image_extension;
					rename(VIDEO_PATH.'/'.$result["uuid"].'/'.$result["uploadName"], VIDEO_PATH.'/'.$new_name);
					$result["uploadName"] = $new_name;
					if($uploaded_image_extension == 'mov' || $uploaded_image_extension == 'mp4') {
					} else {}
				}
			}
			echo json_encode($result);
			exit();
		}
		// for delete file requests
		else if ($method == "DELETE") {
			$result = $uploader->handleDelete(VIDEO_PATH);
			echo json_encode($result);
			exit();
		}else {
			header("HTTP/1.0 405 Method Not Allowed");
			exit();
		}
	}
	
	public function addproductAction() {
		unset($_SESSION["propic"]);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = 'You cannot access this page.';
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$category_data = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_status = '1'","fetchAll");
		if(!empty($category_data)) {
			$category_arr = array(''=>'Please Select');
			foreach($category_data as $category_data_key => $category_data_val) {
				$category_arr[$category_data_val["category_id"]] = $category_data_val["category_feild"];
			}
		}
		reset($category_arr);
		$first_key = key($category_arr);
		$subcategory_data = $this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,"subcategory_categoryid =:TID","fetchAll",array('warray'=>array('TID'=>$first_key)));
		$countries_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>array('country_id','country_name_en')));
		$country_arr = array(); $subcategory_arr = array();
		foreach($countries_data as $countries_data_key => $countries_data_val) {
			$country_arr[$countries_data_val["country_id"]] = $countries_data_val["country_name_en"];
		}
		if(!empty($subcategory_data)) {
			$subcategory_arr = array(''=>'Please Select');
			foreach($subcategory_data as $subcategory_data_key => $subcategory_data_val) {
				$subcategory_arr[$subcategory_data_val["subcategory_id"]] = $subcategory_data_val["subcategory_title"];
			}
		}
		$shipping_profile = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
		$form = new ProfileForm();
		$form->product($category_arr,$subcategory_arr,$add=1);
		$request = $this->getRequest();
		$imagePlugin = $this->Image();
		$files =  $request->getFiles()->toArray();
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$pro_photos = array_keys($posted_data["doc_org_name"]);
			$prod_photos = implode(",",$pro_photos);
			$qty = $posted_data["qty"];
			$colorsizes = $posted_data["colorsize"];
			$prices = $posted_data["price"];
			foreach($qty as $qty_key => $qty_val) {
				$qtyprices[] = $qty_val."~".$posted_data["price"][$qty_key];
			}
			$qty_arr = array_combine($colorsizes,$qtyprices);
			$is_uploaded1 = $imagePlugin->universal_upload(array("directory"=>DIGITAL_PATH,"files_array"=>array('digital_product'=>$files['digital_product']),"multiple"=>false,"crop"=>false),"Both");
			if(!empty($files["digital_product"]) && $is_uploaded1->media_path != '') {
				$product_rec["product_digital"] = $is_uploaded1->media_path;
			} else {
				$product_rec["product_digital"] = '';
			}
			if($posted_data["product_isdigital"] == 'on') {
				$product_rec["product_isdigital"] = '1';
			} else {
				$product_rec["product_isdigital"] = '2';
			}
			if(empty($_FILES["digital_product"]["tmp_name"]) && $posted_data["product_isdigital"] == 'on') {
				$this->frontSession['errorMsg'] = "Please upload digital product file.";
				$errorcase = 1;
			}
			if(!empty($_FILES["digital_product"]["name"])) {
				$phpArr=array('php','php3','php4','php5','phtml');
				$uploaded_image_extension = getFileExtension($_FILES["digital_product"]["name"]);
				if(in_array($uploaded_image_extension,$phpArr)){
					$this->frontSession['errorMsg'] = "Invalid file, please upload valid digital product file.";
					$errorcase = 1;
				}
				if(strpos(file_get_contents($_FILES["digital_product"]['tmp_name']), '<?php') !== false){
					$this->frontSession['errorMsg'] = "Invalid file, please upload valid digital product file.";
					$errorcase = 1;
				}
			}
			if(empty($posted_data["shipping_id"]) && $posted_data["product_isdigital"] != 'on') {
				$this->frontSession['errorMsg'] = "Shipping profile is required.";
				$errorcase = 1;
			}
			if(!empty($posted_data["shipping_rate"])) {
				$shipping_arr = array_combine($posted_data["shipping_country"],$posted_data["shipping_rate"]);
			}
			if(empty($posted_data["product_chk"])) {
				$this->frontSession['errorMsg'] = "Please upload a valid image.";
			} else {
			$decode_chk = base64_decode($posted_data["product_chk"]);
			if($decode_chk != 'coven') {
				$this->frontSession['errorMsg'] = "Please upload a valid image.";
			} else {
			if(!empty($posted_data["doc_org_name"])) {
				unset($posted_data["product_chk"]);
				$posted_data["product_clientid"] = $this->loggedUser->yurt90w_client_id;
				$posted_data["product_date"] = date("Y-m-d H:i:s");
				$pro_photos = explode(",",$posted_data["product_photos"]);
				if(count($posted_data["doc_org_name"]) > 7) {
					$this->frontSession['errorMsg'] = "You can maximum upload 7 product images.";
					$errorcase = 1;
				}
				if(isset($posted_data["startedbtn"])) {
					unset($posted_data["startedbtn"]);
				}
				$posted_data["product_title"] = strip_tags($posted_data["product_title"]);
				$posted_data["product_category"] = strip_tags($posted_data["product_category"]);
				$posted_data["product_subcategory"] = strip_tags($posted_data["product_subcategory"]);
				$posted_data["product_price"] = strip_tags($posted_data["product_price"]);
				$posted_data["product_description"] = strip_tags($posted_data["product_description"]);
				$posted_data["product_photos"] = strip_tags($prod_photos);
				if($posted_data["product_price"] < 0 && !empty($posted_data["product_price"])) {
					$this->frontSession['errorMsg'] = "Invalid product price, please enter valid product price.";
					$errorcase = 1;
				}
				if(strlen($posted_data["product_price"]) > 10 && !empty($posted_data["product_price"])) {
					$this->frontSession['errorMsg'] = "Invalid product price, please enter not more than 10 digits in price.";
					$errorcase = 1;
				}
				if (!is_numeric($posted_data["product_price"]) && !empty($posted_data["product_price"])) {
					$this->frontSession['errorMsg'] = "Invalid product price, please enter valid product price.";
					$errorcase = 1;
				}
				/*if(empty($posted_data["product_globalrate"]) || !is_numeric($posted_data["product_globalrate"])) {
					$this->frontSession['errorMsg'] = "Invalid global rate, please enter valid global shipping rate.";
					$errorcase = 1;
				}*/
				if(empty($posted_data["product_subcategory"])) {
					$this->frontSession['errorMsg'] = "Please enter sub category.";
					$errorcase = 1;
				}
				if($posted_data["product_isdigital"] != 'on') {
				if(empty($posted_data["qty"])) {
					$this->frontSession['errorMsg'] = "Invalid product quantity, please enter valid product quantity.";
					$errorcase = 1;
				} 
				if(!empty($qty_arr)) {
					foreach($qty_arr as $qty_arr_key => $qty_arr_val) {
						$qty_value = explode("~",$qty_arr_val); 
						if (!is_numeric($qty_value[0])) {
							$this->frontSession['errorMsg'] = "Invalid product quantity, please enter valid product quantity.";
							$errorcase = 1;
						}
					}
				} }
				if(!empty($errorcase)) {
					if(!empty($posted_data)) {
						$form->populateValues($posted_data);
					}
				} else {
				$product_rec["product_title"] = $posted_data["product_title"];
				$product_rec["product_price"] = $posted_data["product_price"];
				$product_rec["product_category"] = $posted_data["product_category"];
				$product_rec["product_subcategory"] = $posted_data["product_subcategory"];	
				$product_rec["product_description"] = $posted_data["product_description"];
				$product_rec["product_photos"] = $posted_data["product_photos"];
				$product_rec["product_clientid"] = $posted_data["product_clientid"];
				$product_rec["product_date"] = $posted_data["product_date"];
				$product_rec["product_globalrate"] = $posted_data["product_globalrate"];
				//$product_rec["product_status"] = '1';
				$product_rec["product_shippingid"] = $posted_data["shipping_id"];	
				$product_rec["product_order"] = '1';	
				if(empty($product_rec["product_price"])) {
					$product_rec["product_price"] = NULL;
				}
				if(!empty($posted_data["upload_tag"])) {
					$product_rec["product_video"] = $posted_data["upload_tag"];
				}
				if(!empty($posted_data["product_tags"])) {
					$product_rec["product_tags"] = strip_tags($posted_data["product_tags"]);
				}	
				$isInsert = $this->SuperModel->Super_Insert(T_PRODUCTS,(array)$product_rec);	
				if(!empty($isInsert->success)) {
					if(!empty($posted_data["colorsize"])) {
						foreach($posted_data["colorsize"] as $qty_arr_key => $qty_arr_val) {
							$expl = explode("~",$qty_arr_val);
							$prod_data["color_title"] = trim($expl[0]);
							$prod_data["color_size"] = trim($expl[1]);
							$prod_data["color_qty"] = $posted_data["qty"][$qty_arr_key];
							$prod_data["color_productid"] = $isInsert->inserted_id;
							$prod_data["color_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
							$prod_data["color_slug"] = strtolower(trim($expl[0]));
							$prod_data["color_price"] = $posted_data["fixed_price"][$qty_arr_val];
							$prod_data["color_fixedprice"] = $posted_data["fixed_price"][$qty_arr_val];
							if(!empty($prod_data["color_price"])) {
								$tt = $this->SuperModel->Super_Insert(T_PROQTY,$prod_data);								
							}
							if($qty_arr_key == 0) {
								$prod_upddata["product_price"] = $prod_data["color_price"];
								$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_upddata,"product_id = '".$isInsert->inserted_id."'");
							}
						}
					}
					if(!empty($shipping_arr)) {
						foreach($shipping_arr as $shipping_arr_key => $shipping_arr_val) {
							$ship_data["product_shipping_country"] = $shipping_arr_key;
							$ship_data["product_shipping_rate"] = $shipping_arr_val;
							$ship_data["product_shipping_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
							$ship_data["product_shipping_productid"] = $isInsert->inserted_id;
							$this->SuperModel->Super_Insert(T_PROSHIP,$ship_data);
						}
					}
					
					$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has posted a product with title ".$posted_data["product_title"].".",
							"subject" => "A new product posted."
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
					
					
					$this->frontSession['successMsg'] = "Product added successfully.";
					return $this->redirect()->tourl(APPLICATION_URL.'/manage-products');
				} else {
					$this->frontSession['errorMsg'] = "Something went wrong. Please check again.";
				}
				}
			} else {
				$this->frontSession['errorMsg'] = "Something went wrong. Please check again.";
			} } }	
		}
		$view = new ViewModel();
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('country_arr',$country_arr);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('shipping_profile',$shipping_profile);
		return $view;
	}
	
	public function editproductAction() {
		unset($_SESSION["propic"]);
		$product_id = $this->params()->fromRoute('key');
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = 'You cannot access this page.';
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$category_data = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_status = '1'","fetchAll");
		if(!empty($category_data)) {
			foreach($category_data as $category_data_key => $category_data_val) {
				$category_arr[$category_data_val["category_id"]] = $category_data_val["category_feild"];
			}
		}
		if(empty($product_id)) {
			$this->frontSession['errorMsg'] = 'You cannot access this page.';
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id,'PID'=>myurl_decode($product_id))));
		if(empty($product_data)) {
			$this->frontSession['errorMsg'] = 'You cannot access this page.';
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$subcategory_arr = array();
		$subcategory_data = $this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,"subcategory_categoryid =:TID","fetchAll",array('warray'=>array('TID'=>$product_data["product_category"])));
		if(!empty($subcategory_data)) {
			foreach($subcategory_data as $subcategory_data_key => $subcategory_data_val) {
				$subcategory_arr[$subcategory_data_val["subcategory_id"]] = $subcategory_data_val["subcategory_title"];
			}
		}
		$form = new ProfileForm();
		$form->product($category_arr,$subcategory_arr);
		if(!empty($product_data)) {
			$form->populateValues($product_data);
		}
		$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID","fetchAll",array('order'=>'color_title asc','warray'=>array( 'TID'=>myurl_decode($product_id))));
		if(!empty($colorsize_data)) {
			foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
				$all_colors[trim($colorsize_data_val["color_title"])][] = trim($colorsize_data_val["color_size"]);
			}
		}
		$shipping_data = $this->SuperModel->Super_Get(T_PROSHIP,"product_shipping_productid =:TID","fetchAll",array('warray'=>array('TID'=>myurl_decode($product_id))));
		if(!empty($shipping_data)) {
			foreach($shipping_data as $shipping_data_key => $shipping_data_val) {
				$all_shipping[$shipping_data_val["product_shipping_country"]] = $shipping_data_val["product_shipping_rate"];
			}
		}
		$countries_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>array('country_id','country_name_en')));
		$country_arr = array();
		foreach($countries_data as $countries_data_key => $countries_data_val) {
			$country_arr[$countries_data_val["country_id"]] = $countries_data_val["country_name_en"];
		}
		$shipping_profile = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
		$request = $this->getRequest();
		$imagePlugin = $this->Image();
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$pro_photos = array_keys($posted_data["doc_org_name"]);
			$prod_photos = implode(",",$pro_photos);
			$files =  $request->getFiles()->toArray();
			if(empty($_FILES["digital_product"]["tmp_name"]) && $posted_data["product_isdigital"] == 'on' && $product_data["product_digital"] == '') {
				$this->frontSession['errorMsg'] = "Please upload digital product file.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}
			if(!empty($_FILES["digital_product"]["name"])) {
				$phpArr=array('php','php3','php4','php5','phtml');
				$uploaded_image_extension = getFileExtension($_FILES["digital_product"]["name"]);
				if(in_array($uploaded_image_extension,$phpArr)){
					$this->frontSession['errorMsg'] = "Invalid file, please upload valid digital product file.";
					return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
				}
				if(strpos(file_get_contents($_FILES["digital_product"]['tmp_name']), '<?php') !== false){
					$this->frontSession['errorMsg'] = "Invalid file, please upload valid digital product file.";
					return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
				}
			}
			if(!empty($posted_data["doc_org_name"])) {				
				//$posted_data["product_photos"] = implode(",",$product_logo);
				if(!empty($product_data["product_photos"])) {
					/*$pro_photos = explode(",",$posted_data["product_photos"]);
					$pre_photos = explode(",",$product_data["product_photos"]);
					$all_photos = array_merge($pre_photos,$pro_photos);
					$all_photos = array_unique($all_photos);*/
					if(count($posted_data["doc_org_name"]) > 7) {
						$this->frontSession['errorMsg'] = "You can maximum upload 7 product images.";
						return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
					}
					//$posted_data["product_photos"] = implode(",",$all_photos);
					//$posted_data["product_photos"] = str_replace("Array,","",$posted_data["product_photos"]);
					$posted_data["product_photos"] = $prod_photos;
				} else {
					//$pro_photos = explode(",",$posted_data["product_photos"]);
					if(count($posted_data["doc_org_name"]) > 7) {
						$this->frontSession['errorMsg'] = "You can maximum upload 7 product images.";
						return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
					}
					//$posted_data["product_photos"] = str_replace("Array,","",$posted_data["product_photos"]);
					$posted_data["product_photos"] = $prod_photos;
				}
			} else {
					//$pre_photos = explode(",",$product_data["product_photos"]);
					if(count($posted_data["doc_org_name"]) > 7) {
						$this->frontSession['errorMsg'] = "You can maximum upload 7 product images.";
						return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
					}
					//$posted_data["product_photos"] = str_replace("Array,","",$product_data["product_photos"]);
					$posted_data["product_photos"] = $prod_photos;
			}
			$posted_data["product_clientid"] = $this->loggedUser->yurt90w_client_id;
			$posted_data["product_date"] = date("Y-m-d H:i:s");
			if(isset($posted_data["startedbtn"])) {
				unset($posted_data["startedbtn"]);
			}
			if(isset($posted_data["product_chk"])) {
				unset($posted_data["product_chk"]);
			}
			$is_uploaded1 = $imagePlugin->universal_upload(array("directory"=>DIGITAL_PATH,"files_array"=>array('digital_product'=>$files['digital_product']),"multiple"=>false,"crop"=>false),"Both");
			if($posted_data["product_isdigital"] == 'on') {
				$product_rec["product_isdigital"] = '1';
				if(!empty($files["digital_product"]) && $is_uploaded1->media_path != '') {
					$product_rec["product_digital"] = $is_uploaded1->media_path;
				} else {
					$product_rec["product_digital"] = $product_data["product_digital"];
				}
			} else {
				$product_rec["product_isdigital"] = '2';
				$product_rec["product_digital"] = '';
			}
			$posted_data["product_title"] = strip_tags($posted_data["product_title"]);
			$posted_data["product_category"] = strip_tags($posted_data["product_category"]);
			$posted_data["product_subcategory"] = strip_tags($posted_data["product_subcategory"]);
			$posted_data["product_price"] = strip_tags($posted_data["product_price"]);
			$posted_data["product_description"] = strip_tags($posted_data["product_description"]);
			$posted_data["product_photos"] = strip_tags($posted_data["product_photos"]);
			if($posted_data["product_isdigital"] == 'on') { 
			if($posted_data["product_price"] < 0) {
				$this->frontSession['errorMsg'] = "Invalid product price, please enter valid product price.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}
			if(strlen($posted_data["product_price"]) > 10) {
				$this->frontSession['errorMsg'] = "Invalid product price, please enter not more than 10 digits.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}
			if (!is_numeric($posted_data["product_price"])) {
				$this->frontSession['errorMsg'] = "Invalid product price, please enter valid product price.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			} }
			/*if(empty($posted_data["product_globalrate"]) || !is_numeric($posted_data["product_globalrate"])) {
				$this->frontSession['errorMsg'] = "Invalid global rate, please enter valid global shipping rate.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}*/
			if(empty($posted_data["product_subcategory"])) {
				$this->frontSession['errorMsg'] = "Please enter sub category.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}
			if(empty($posted_data["qty"]) && $posted_data["product_isdigital"] != 'on') {
				$this->frontSession['errorMsg'] = "Invalid product quantity, please enter valid product quantity.";
				return $this->redirect()->tourl(APPLICATION_URL.'/edit-product/'.str_replace("=","",$product_id));
			}
			$product_rec["product_title"] = $posted_data["product_title"];
			if(!empty($posted_data["product_price"])) {
				$product_rec["product_price"] = $posted_data["product_price"];
			} else {
				$product_rec["product_price"] = NULL;
			}
			$product_rec["product_category"] = $posted_data["product_category"];
			$product_rec["product_subcategory"] = $posted_data["product_subcategory"];
			$product_rec["product_description"] = $posted_data["product_description"];
			$product_rec["product_globalrate"] = $posted_data["product_globalrate"];
			$product_rec["product_photos"] = $posted_data["product_photos"];
			$product_rec["product_clientid"] = $posted_data["product_clientid"];
			$product_rec["product_date"] = $posted_data["product_date"];
			$product_rec["product_shippingid"] = $posted_data["shipping_id"];
			$product_rec["product_video"] = $posted_data["upload_tag"];
			$product_rec["product_tags"] = strip_tags($posted_data["product_tags"]);
			$isInsert = $this->SuperModel->Super_Insert(T_PRODUCTS,(array)$product_rec,"product_id = '".myurl_decode($product_id)."'");
			if(!empty($isInsert->success)) {
				$qty = $posted_data["qty"];
				foreach($qty as $qty_key => $qty_val) {
					$qtyprices[] = $qty_val."~".$posted_data["price"][$qty_key];
				}
				$colorsizes = $posted_data["colorsize"];
				$qty_arr = array_combine($colorsizes,$qtyprices);
				if(!empty($posted_data["fixed_price"])) {
					$this->SuperModel->Super_Delete(T_PROQTY,"color_productid = '".myurl_decode($product_id)."'");
					if(!empty($posted_data["colorsize"])) {
						foreach($posted_data["colorsize"] as $qty_arr_key => $qty_arr_val) {
							$expl = explode("~",$qty_arr_val);
							$prod_data["color_title"] = trim($expl[0]);
							$prod_data["color_size"] = trim($expl[1]);
							$prod_data["color_qty"] = $posted_data["qty"][$qty_arr_key];
							$prod_data["color_productid"] = myurl_decode($product_id);
							$prod_data["color_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
							$prod_data["color_slug"] = strtolower(trim($expl[0]));
							$prod_data["color_price"] = $posted_data["fixed_price"][$qty_arr_val];
							$prod_data["color_fixedprice"] = $posted_data["fixed_price"][$qty_arr_val];
							$this->SuperModel->Super_Insert(T_PROQTY,$prod_data);
							
							if($qty_arr_key == 0) {
								$prod_upddata["product_price"] = $prod_data["color_price"];
								$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_upddata,"product_id = '".myurl_decode($product_id)."'");
							}
						}
					}
				}
				$shipping_arr = array_combine($posted_data["shipping_country"],$posted_data["shipping_rate"]);
				if(!empty($shipping_arr)) {
				$this->SuperModel->Super_Delete(T_PROSHIP,"product_shipping_productid ='".myurl_decode($product_id)."'");	
				foreach($shipping_arr as $shipping_arr_key => $shipping_arr_val) {
					$ship_data["product_shipping_country"] = $shipping_arr_key;
					$ship_data["product_shipping_rate"] = $shipping_arr_val;
					$ship_data["product_shipping_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
					$ship_data["product_shipping_productid"] = myurl_decode($product_id);
					$this->SuperModel->Super_Insert(T_PROSHIP,$ship_data);
				} }
				$this->frontSession['successMsg'] = "Product updated successfully.";
				return $this->redirect()->tourl(APPLICATION_URL.'/manage-products');
			} else {
				$this->frontSession['errorMsg'] = "Something went wrong. Please check again.";
			}
		}
		$view = new ViewModel();
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('product_images', explode(",",$product_data["product_photos"]));
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('product_id',$product_id);
		$view->setVariable('all_colors',$all_colors);
		$view->setVariable('all_shipping',$all_shipping);
		$view->setVariable('colorsize_data',$colorsize_data);
		$view->setVariable('country_arr',$country_arr);
		$view->setVariable('shipping_id',$product_data["product_shippingid"]);
		$view->setVariable('global_rate',$product_data["product_globalrate"]);
		$view->setVariable('shipping_profile',$shipping_profile);
		$view->setVariable('product_isdigital',$product_data["product_isdigital"]);
		$view->setVariable('product_digital',$product_data["product_digital"]);
		$view->setVariable('product_video',$product_data["product_video"]);
		return $view;
	}
	
	public function managesizeAction() {
		$key = $this->params()->fromRoute("key");
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		if(!empty($key)) {
			$size_data = $this->SuperModel->Super_Get(T_SIZES,"size_id =:TID","fetch",array('warray'=>array('TID'=>myurl_decode($key))));
		}
		$form = new ProfileForm();
		$form->size();
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$size_data["size_title"] = $posted_data["size_title"];
			
		}
		$view = new ViewModel();	
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('product_list',$product_list);	
		return $view;	
	}
	
	public function addcouponAction() {
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if(empty($store_data) || $store_data["store_approval"] != '1') {
			$this->frontSession['errorMsg'] = "You cannot access this page.";
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}
		$product_list = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID","fetchAll",array('fields'=>array('product_id','product_title'),'warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
		$form = new ProfileForm();
		$form->coupon();
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$coupon_details = $this->SuperModel->Super_Get(T_COUPONS,"coupon_code =:TID","fetch",array('warray'=>array('TID'=>strip_tags(trim($posted_data["coupon_code"])))));
			if(!empty($coupon_details)) {
				$this->frontSession['errorMsg'] = 'Coupon code is already taken. Please use another code.';
				$errorCase = 1;	
			}
			$coupon_data["coupon_title"] = strip_tags($posted_data["coupon_title"]);
			$coupon_data["coupon_code"] = strip_tags(trim($posted_data["coupon_code"]));
			$coupon_data["coupon_discount"] = strip_tags($posted_data["coupon_discount"]);
			if($posted_data["coupon_discount"] > 100 || $posted_data["coupon_discount"] < 0) {
				$this->frontSession['errorMsg'] = 'Please enter valid coupon discount.';
				return $this->redirect()->tourl(APPLICATION_URL.'/add-coupon');
			}
			if(!empty($coupon_data["coupon_status"])) {
				$coupon_data["coupon_status"] = '0';
			} else {
				$coupon_data["coupon_status"] = '1';
			}
			$coupon_data["coupon_start_date"] = date("Y-m-d",strtotime($posted_data["coupon_start_date"]));
			$coupon_data["coupon_end_date"] = date("Y-m-d",strtotime($posted_data["coupon_end_date"]));
			$coupon_data["coupon_clientid"] = $this->loggedUser->yurt90w_client_id;
			if($posted_data["radio-group"] == '1') {
				if(empty($posted_data["product_name"])) {
					$this->frontSession['errorMsg'] = 'Please select product.';
					$errorCase = 1;	
				}
				$coupon_data["coupon_product"] = implode(",",$posted_data["product_name"]);
				$coupon_data["coupon_type"] = '1';
			} else if($posted_data["radio-group"] == '2') {
				$coupon_data["coupon_product"] = 'ALL';
				$coupon_data["coupon_type"] = '2';
			} else if($posted_data["radio-group"] == '3') {
				if(empty($posted_data["product_name"])) {
					$this->frontSession['errorMsg'] = 'Please select product.';
					$errorCase = 1;
				}
				$prod_names = $posted_data["product_name"];
				if(count($prod_names) > 1) {
					$this->frontSession['errorMsg'] = 'Please select valid product.';
					$errorCase = 1;
				}
				$coupon_data["coupon_product"] = implode(",",$posted_data["product_name"]);
				$coupon_data["coupon_type"] = '3';
			}
			if(strtotime($coupon_data["coupon_start_date"]) >= strtotime($coupon_data["coupon_end_date"])) {
				$this->frontSession['errorMsg'] = 'End date must be greater than start date.';
				$errorCase = 1;
			}
			if(!empty($errorCase)) {
				$form->populateValues($posted_data);
			} else {
				$this->SuperModel->Super_Insert(T_COUPONS,$coupon_data);
				$this->frontSession['successMsg'] = 'Coupon has been added successfully.';
				return $this->redirect()->tourl(APPLICATION_URL.'/manage-coupon');	
			}
		}
		$view = new ViewModel();	
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('product_list',$product_list);	
		return $view;	
	}
	
	public function generatecodeAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(!empty($posted_data["codez"])) {
				$code = generatePIN();
				$codex = str_replace("=","",strtoupper(base64_encode($code)));
				echo $codex;
			}
		}
		exit();
	}
	
	public function launchproductAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:TID and product_clientid =:UID","fetch",array('fields'=>'product_title','warray'=>array('TID'=>myurl_decode($data["tid"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
			if(!empty($product_details)) {
				$prod_data["product_status"] = '1';
				$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_id = '".myurl_decode($data["tid"])."' and product_clientid = '".$this->loggedUser->{T_CLIENT_VAR."client_id"}."'");
				echo "success";
			}
			exit();	
		}
	}
	
	public function statoverviewAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if(!empty($data["overview"])) {
				if($data["overview"] == '1') {
					$week_before = date("Y-m-d", strtotime("-1 week"));
					$store_viewdata = $this->SuperModel->Super_Get(T_STOREVIEWS,"storeview_ownerid =:UID and storeview_date > '".$week_before.' 00:00:00'."' and storeview_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>'storeview_id'));
					$store_orderdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$week_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
					$store_earningdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$week_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('share'=>new Expression('SUM(order_total) - SUM(order_sitefee)'))));					
				} else if($data["overview"] == '2') {
					//$month_before = date("Y-m-d", strtotime("-1 month"));
					$month_before = date('Y-m-01');					
					$store_viewdata = $this->SuperModel->Super_Get(T_STOREVIEWS,"storeview_ownerid =:UID and storeview_date > '".$month_before.' 00:00:00'."' and storeview_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>'storeview_id'));
					$store_orderdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$month_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
					$store_earningdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$month_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('share'=>new Expression('SUM(order_total) - SUM(order_sitefee)'))));		
				} else if($data["overview"] == '3') {
					$year_before = date("Y-m-d", strtotime("-1 year"));
					$store_viewdata = $this->SuperModel->Super_Get(T_STOREVIEWS,"storeview_ownerid =:UID and storeview_date > '".$year_before.' 00:00:00'."' and storeview_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>'storeview_id'));
					$store_orderdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$year_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
					$store_earningdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$year_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('share'=>new Expression('SUM(order_total) - SUM(order_sitefee)'))));
				} else {
					$year_before = date("Y-m-d", strtotime("-10 years"));
					$store_viewdata = $this->SuperModel->Super_Get(T_STOREVIEWS,"storeview_ownerid =:UID and storeview_date > '".$year_before.' 00:00:00'."' and storeview_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>'storeview_id'));
					$store_orderdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$year_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
					$store_earningdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$year_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('share'=>new Expression('SUM(order_total) - SUM(order_sitefee)'))));
				}
				$store_views = count($store_viewdata);
				$store_orders = count($store_orderdata);
				$store_earning = round($store_earningdata["share"],2);
				$view = new ViewModel();	
				$view->setTerminal(true);
				$view->setVariable('store_views', $store_views);
				$view->setVariable('store_orders', $store_orders);
				$view->setVariable('store_earning', $store_earning);
				return $view;	
			}
		} else {
			echo "error";
			exit();
		}
	}
	
	public function dashboardpageAction() {
		//require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
		//\Stripe\Stripe::setApiKey($this->site_configs["site_secret_key"]);
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
		\Tinify\setKey(TINY_KEY);
		$_SESSION["logstat"] = '1';
		unset($_SESSION["team_pics"]);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$store_viewdata = array(); $store_views = ''; $store_orders = ''; $store_earning = '';
		if($store_data["store_approval"] == '1') {
			$week_before = date("Y-m-d", strtotime("-1 week"));
			$store_viewdata = $this->SuperModel->Super_Get(T_STOREVIEWS,"storeview_ownerid =:UID and storeview_date > '".$week_before.' 00:00:00'."' and storeview_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id),'fields'=>'storeview_id'));
			$store_views = count($store_viewdata);
			$store_orderdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$week_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
			$store_orders = count($store_orderdata);
			$store_earningdata = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID and order_date > '".$week_before.' 00:00:00'."' and order_date < '".date("Y-m-d H:i:s")."'","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('share'=>new Expression('SUM(order_total) - SUM(order_sitefee)'))));
			$store_earning = round($store_earningdata["share"],2);
		}
		$form = new ProfileForm();
		if(!empty($store_data["store_doc1"])) {
			$uid = 1;
		} else {
			$uid = '';
		}
		if(!empty($store_data["store_name"])) {
			$tid = 1;
		} else {
			$tid = '';
		}
		$form->seller($uid,$tid);
		if(!empty($_SESSION["store_data"])) {
			$post_data["seller_storename"] = $_SESSION["store_data"]["store_name"];
			$post_data["seller_companyname"] = $_SESSION["store_data"]["store_company"];
			$post_data["seller_contact"] = $_SESSION["store_data"]["store_contact"];
			$post_data["seller_location"] = $_SESSION["store_data"]["store_location"];
			$post_data["seller_paypal"] = $_SESSION["store_data"]["store_paypal"];			
			$post_data["seller_storetitle"] = $_SESSION["store_data"]["store_title"];
			$post_data["store_headline"] = $_SESSION["store_data"]["store_headline"];
			$post_data["store_description"] = $_SESSION["store_data"]["store_description"];
			$post_data["store_policy"] = $_SESSION["store_data"]["store_policy"];
			$form->populateValues($post_data);
		}
		else if(!empty($store_data)) {
			$post_data["seller_storename"] = $store_data["store_name"];
			$post_data["seller_companyname"] = $store_data["store_company"];
			$post_data["seller_contact"] = $store_data["store_contact"];
			$post_data["seller_location"] = $store_data["store_location"];
			$post_data["seller_paypal"] = $this->loggedUser->{T_CLIENT_VAR."client_paypal_email"};	
			$post_data["seller_storetitle"] = $store_data["store_title"];
			$post_data["store_headline"] = $store_data["store_headline"];
			$post_data["store_description"] = $store_data["store_description"];
			$post_data["store_policy"] = $store_data["store_policy"];
			$form->populateValues($post_data);
		}
		$cusomerId = $this->loggedUser->{T_CLIENT_VAR.'client_customerid'};
		$imagePlugin = $this->Image();
		$request = $this->getRequest();
		$plan_amount = $this->site_configs["plan_price"];
		if(!empty($store_data["store_id"])) {
			$member_details = $this->SuperModel->Super_Get(T_STORE_MEMBERS,"member_storeid =:TID","fetchAll",array('warray'=>array('TID'=>$store_data["store_id"])));
		}
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			if(!empty($posted_data["coupon_code"])) {
				$coupon_data = $this->SuperModel->Super_Get(T_MERCHANTCOUPON,"LOWER(merchantcoupon_code) =:TID","fetch",array('warray'=>array('TID'=>strtolower($posted_data["coupon_code"]))));
			}
			$coupon_amount = bcdiv($plan_amount,1,2);
			if(empty($coupon_data)) {
			} else {
				$coupon_discount = bcdiv(($plan_amount * $coupon_data["merchantcoupon_discount"]) / 100,1,2);
				$coupon_amount = $plan_amount - $coupon_discount;
			}
			require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey($this->site_configs["site_secret_key"]);
			
			$files =  $request->getFiles()->toArray();
			$data["store_name"] = strip_tags($posted_data["seller_storename"]);
			$store_kdata = $this->SuperModel->Super_Get(T_STORE,"LOWER(store_name) =:SID and store_clientid != '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'","fetch",array('warray'=>array('SID'=>strtolower(trim(strip_tags($data["store_name"]))))));
			if(!empty($store_kdata)) {
				$this->frontSession['errorMsg'] = "Store name already exists. Please try with different name.";
				return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
			}
			$data["store_company"] = strip_tags($posted_data["seller_companyname"]);
			$data["store_contact"] = strip_tags($posted_data["seller_contact"]);
			$data["store_location"] = strip_tags($posted_data["seller_location"]);
			$data["store_title"] = strip_tags($posted_data["seller_storetitle"]);
			$data["store_headline"] = strip_tags($posted_data["store_headline"]);
			$data["store_description"] = strip_tags($posted_data["store_description"],'<br/>');
			$data["store_policy"] = strip_tags($posted_data["store_policy"],'<br/>');
			$latlng = $this->SuperModel->postData($data["store_location"],'');
			$data["store_latitude"] = $latlng["latitude"];
			$data["store_longitude"] = $latlng["longitude"];
			if($files['seller_logo']['name']!=''){	
				if (strpos(file_get_contents($files['seller_logo']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				$is_uploaded1 = $imagePlugin->universal_upload(array("directory"=>STORE_LOGO_PATH,"files_array"=>array('seller_logo'=>$files['seller_logo']),"multiple"=>false,"crop"=>false));	
				if($is_uploaded1->success=='1' && $is_uploaded1->media_path!=''){
					$store_logo = $is_uploaded1->media_path;
					if(file_exists(STORE_LOGO_PATH.'/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_LOGO_PATH."/".$store_logo);
						$source->toFile(STORE_LOGO_PATH."/".$store_logo);
					}
					if(file_exists(STORE_LOGO_PATH.'/60/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_LOGO_PATH."/60/".$store_logo);
						$source->toFile(STORE_LOGO_PATH."/60/".$store_logo);
					}
					if(file_exists(STORE_LOGO_PATH.'/thumb/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_LOGO_PATH."/thumb/".$store_logo);
						$source->toFile(STORE_LOGO_PATH."/thumb/".$store_logo);
					}
				}
			}
			if($files['seller_banner']['name']!=''){
				if (strpos(file_get_contents($files['seller_banner']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				$is_uploaded2 = $imagePlugin->universal_upload(array("directory"=>STORE_BANNER_PATH,"files_array"=>array('seller_banner'=>$files['seller_banner']),"multiple"=>false,"crop"=>false));
				if($is_uploaded2->success=='1' && $is_uploaded2->media_path!=''){
					$store_banner = $is_uploaded2->media_path;
					if(file_exists(STORE_BANNER_PATH.'/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_BANNER_PATH."/".$store_banner);
						$source->toFile(STORE_BANNER_PATH."/".$store_banner);
					}
					if(file_exists(STORE_BANNER_PATH.'/60/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_BANNER_PATH."/60/".$store_banner);
						$source->toFile(STORE_BANNER_PATH."/60/".$store_banner);
					}
					if(file_exists(STORE_BANNER_PATH.'/160/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_BANNER_PATH."/160/".$store_banner);
						$source->toFile(STORE_BANNER_PATH."/160/".$store_banner);
					}
					if(file_exists(STORE_BANNER_PATH.'/240/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_BANNER_PATH."/240/".$store_banner);
						$source->toFile(STORE_BANNER_PATH."/240/".$store_banner);
					}
					if(file_exists(STORE_BANNER_PATH.'/thumb/'.$store_logo)) {
						$source = \Tinify\fromFile(STORE_BANNER_PATH."/thumb/".$store_banner);
						$source->toFile(STORE_BANNER_PATH."/thumb/".$store_banner);
					}
				}
			}
			
			if(!empty($store_logo)) {
				$data["store_logo"] = $store_logo;
			} else {
				$data["store_logo"] = $store_data["store_logo"];
			}
			if(!empty($store_banner)) {
				$data["store_banner"] = $store_banner;
			} else {
				$data["store_banner"] = $store_data["store_banner"];
			}
			$data["store_clientid"] = $this->loggedUser->yurt90w_client_id;
			if(!empty($posted_data["seller_paypal"])) {
				$usr_data[T_CLIENT_VAR."client_paypal_email"] = $posted_data["seller_paypal"];
				$isp = $this->SuperModel->Super_Insert(T_CLIENTS,$usr_data,T_CLIENT_VAR."client_id = '".$this->loggedUser->{T_CLIENT_VAR."client_id"}."'");
				unset($posted_data["seller_paypal"]);
			}
			if(!empty($posted_data["chkbox"])) {
				$data["store_closed"] = '1';
				$data["store_closed_date"] = date("Y-m-d");
				$data["store_closed_tilldate"] = date("Y-m-d",strtotime($posted_data["closed_tilldate"]));
				if($posted_data["radio-group"] == '1') {
					$data["store_acceptorder"] = '1';
				} else {
					$data["store_acceptorder"] = '2';
				}
			} else {
				$data["store_closed"] = '2';
				$data["store_closed_date"] = NULL;
				$data["store_closed_tilldate"] = NULL;
				$data["store_acceptorder"] = '1';
			}
			if(empty($store_data)) {
				if(empty($coupon_amount)) {
					$data["store_approval"] = '3';
					$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data);
					if(!empty($posted_data["member_name"])) {
						$this->SuperModel->Super_Delete(T_STORE_MEMBERS,"member_storeid = '".$isInsert->inserted_id."'");
						foreach($posted_data["member_name"] as $member_name_key => $member_name_val) {
							if(!empty($member_name_val) && !empty($posted_data["member_role"][$member_name_key]) && !empty($posted_data["member_bio"][$member_name_key])) {
								$member_data["member_name"] = strip_tags($member_name_val);
								$member_data["member_role"] = strip_tags($posted_data["member_role"][$member_name_key]);
								$member_data["member_bio"] = strip_tags($posted_data["member_bio"][$member_name_key],'<br/>');
								$member_data["member_pic"] = strip_tags($posted_data["member_pic"][$member_name_key]);
								$member_data["member_storeid"] = $store_data["store_id"];
								$this->SuperModel->Super_Insert(T_STORE_MEMBERS,$member_data);
							}
						}
					}
					if(!empty($coupon_data)) {
						if(!empty($posted_data["coupon_code"])) {
							$clt_data[T_CLIENT_VAR."client_couponcode"] = $posted_data["coupon_code"];
						}
						if(!empty($coupon_data)) {
							$clt_data[T_CLIENT_VAR."client_coupon"] = '1';
						}
					}
					//$clt_data[T_CLIENT_VAR."client_planstatus"] = '1';
					$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$this->loggedUser->yurt90w_client_id."'");
					$this->frontSession['successMsg'] = 'Seller request has been successfully sent to the admin.';
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				} else {
					$data["store_approval"] = '3';
					$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data);
					foreach($posted_data["member_name"] as $member_name_key => $member_name_val) {
						if(!empty($member_name_val) && !empty($posted_data["member_role"][$member_name_key]) && !empty($posted_data["member_bio"][$member_name_key])) {
							$member_data["member_name"] = strip_tags($member_name_val);
							$member_data["member_role"] = strip_tags($posted_data["member_role"][$member_name_key]);
							$member_data["member_bio"] = strip_tags($posted_data["member_bio"][$member_name_key],'<br/>');
							$member_data["member_pic"] = strip_tags($posted_data["member_pic"][$member_name_key]);
							$member_data["member_storeid"] = $store_data["store_id"];
							$this->SuperModel->Super_Insert(T_STORE_MEMBERS,$member_data);
						}
					}
					unset($_SESSION["store_data"]);
					//$clt_data[T_CLIENT_VAR."client_customerid"] = $customer->id;
					//$clt_data[T_CLIENT_VAR."client_planstatus"] = '1';
					//$clt_data[T_CLIENT_VAR."client_nextbilling"] = date('Y-m-d', strtotime("+1 month"));

					$notify_data["notification_type"] = '1';
					$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
					$notify_data["notification_to"] = '1';
					$notify_data["notification_readstatus"] = '0';
					$notify_data["notification_date"] = date("Y-m-d H:i:s");
					$notify_data["notification_subscriberid"] = $isInsert->inserted_id;
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);


					

					if(!empty($isInsert->inserted_id)) {
					    $mail_const_data2 = array(
						"user_name" => 'Administrator',
						"user_email" => $this->site_configs['site_email'],
						"message" => $this->loggedUser->yurt90w_client_name." has sent the seller request.",
						"subject" => "Seller request"
					    );	
					    $isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
					    
						$this->frontSession['successMsg'] = 'Seller request has been successfully sent to the admin.';
						return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');	
					} else {
						$this->frontSession['errorMsg'] = 'Please check entered information again.';
						return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
					} 
				}
			} else {
				if($data["store_name"] != $store_data["store_name"] || $data["store_company"] != $store_data["store_company"] || $data["store_contact"] != $store_data["store_contact"] || $data["store_location"] != $store_data["store_location"] || $data["store_logo"] != $store_data["store_logo"] || $data["store_banner"] != $store_data["store_banner"] || $data["store_description"] != $store_data["store_description"] || !empty($posted_data["token"]) || $data["store_headline"] != $store_data["store_headline"] || $data["seller_storetitle"] != $store_data["store_title"]) {
					$notify_data["notification_type"] = '1';
					$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
					$notify_data["notification_to"] = '1';
					$notify_data["notification_readstatus"] = '0';
					$notify_data["notification_date"] = date("Y-m-d H:i:s");
					$notify_data["notification_subscriberid"] = $store_data["store_id"];
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
					//$data["store_approval"] = '3';
					//$data["store_verifydeclinetxt"] = '';
					if(empty($store_data)) {
							$data["store_approval"] = '3';
							$ins = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$this->loggedUser->yurt90w_client_id."'");
							if(!empty($posted_data["member_name"])) {
								$this->SuperModel->Super_Delete(T_STORE_MEMBERS,"member_storeid = '".$store_data["store_id"]."'");
								foreach($posted_data["member_name"] as $member_name_key => $member_name_val) {
									if(!empty($member_name_val) && !empty($posted_data["member_role"][$member_name_key]) && !empty($posted_data["member_bio"][$member_name_key])) {
										$$member_data["member_name"] = strip_tags($member_name_val);
										$member_data["member_role"] = strip_tags($posted_data["member_role"][$member_name_key]);
										$member_data["member_bio"] = strip_tags($posted_data["member_bio"][$member_name_key],'<br/>');
										$member_data["member_pic"] = strip_tags($posted_data["member_pic"][$member_name_key]);
										$member_data["member_storeid"] = $store_data["store_id"];
										$this->SuperModel->Super_Insert(T_STORE_MEMBERS,$member_data);
									}
								}
							}
							
							unset($_SESSION["store_data"]);		
							//$clt_data[T_CLIENT_VAR."client_customerid"] = $customer->id;
							//$clt_data[T_CLIENT_VAR."client_planstatus"] = '1';
							//$clt_data[T_CLIENT_VAR."client_nextbilling"] = date('Y-m-d', strtotime("+1 month"));
							if(!empty($posted_data["coupon_code"])) {
								$clt_data[T_CLIENT_VAR."client_couponcode"] = $posted_data["coupon_code"];
							}
							if(!empty($coupon_data)) {
								$clt_data[T_CLIENT_VAR."client_coupon"] = '1';
							}
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$this->loggedUser->yurt90w_client_id."'");

							$notify_data["notification_type"] = '1';
							$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
							$notify_data["notification_to"] = '1';
							$notify_data["notification_readstatus"] = '0';
							$notify_data["notification_date"] = date("Y-m-d H:i:s");
							$notify_data["notification_subscriberid"] = $isInsert->inserted_id;
							$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
							
							if($ins->success) {
							    $mail_const_data2 = array(
								    "user_name" => 'Administrator',
								    "user_email" => $this->site_configs['site_email'],
								    "message" => $this->loggedUser->yurt90w_client_name." has sent the seller request.",
								    "subject" => "Seller request"
							    );	
							    $isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							}
							$this->frontSession['successMsg'] = 'Seller request has been successfully sent to the admin.';
							return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
						
					} else {
						//$data["store_approval"] = '3';
						$insj = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$this->loggedUser->yurt90w_client_id."'");
						unset($_SESSION["store_data"]);
						
						if(!empty($posted_data["member_name"])) {
							$this->SuperModel->Super_Delete(T_STORE_MEMBERS,"member_storeid = '".$store_data["store_id"]."'");
							foreach($posted_data["member_name"] as $member_name_key => $member_name_val) {
								if(!empty($member_name_val) && !empty($posted_data["member_role"][$member_name_key]) && !empty($posted_data["member_bio"][$member_name_key])) {
									$member_data["member_name"] = strip_tags($member_name_val);
									$member_data["member_role"] = strip_tags($posted_data["member_role"][$member_name_key]);
									$member_data["member_bio"] = strip_tags($posted_data["member_bio"][$member_name_key],'<br/>');
									$member_data["member_pic"] = strip_tags($posted_data["member_pic"][$member_name_key]);
									$member_data["member_storeid"] = $store_data["store_id"];
									$this->SuperModel->Super_Insert(T_STORE_MEMBERS,$member_data);
								}
							}
						}
						if($insj->success) {
						$notify_data["notification_type"] = '1';
						$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
						$notify_data["notification_to"] = '1';
						$notify_data["notification_readstatus"] = '0';
						$notify_data["notification_date"] = date("Y-m-d H:i:s");
						$notify_data["notification_subscriberid"] = $isInsert->inserted_id;
						$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
						$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has sent the seller request.",
							"subject" => "Seller request"
						);	
						$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
						}
						$this->frontSession['successMsg'] = 'Seller request has been updated successfully.';
						return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
					}
				} else {
					$this->SuperModel->Super_Delete(T_STORE_MEMBERS,"member_storeid = '".$store_data["store_id"]."'");
					foreach($posted_data["member_name"] as $member_name_key => $member_name_val) {
						if(!empty($member_name_val)) {
							if(!empty($member_name_val) && !empty($posted_data["member_role"][$member_name_key]) && !empty($posted_data["member_bio"][$member_name_key])) {
								$member_data["member_name"] = strip_tags($member_name_val);
								$member_data["member_role"] = strip_tags($posted_data["member_role"][$member_name_key]);
								$member_data["member_bio"] = strip_tags($posted_data["member_bio"][$member_name_key],'<br/>');
								$member_data["member_pic"] = strip_tags($posted_data["member_pic"][$member_name_key]);
								$member_data["member_storeid"] = $store_data["store_id"];
								$isInsert = $this->SuperModel->Super_Insert(T_STORE_MEMBERS,$member_data);
							}
						}
					}
					return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
				}
				
			}
		}
		$view = new ViewModel();	
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('store_views', $store_views);
		$view->setVariable('store_orders', $store_orders);
		$view->setVariable('store_earning', $store_earning);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('configs',$this->site_configs);
		if(!empty($member_details)) {
			$view->setVariable('member_details',$member_details);
		}
		return $view;	
	}
	
	public function uploadteampicsAction() {
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
		\Tinify\setKey(TINY_KEY);
		$key = $_POST["cur_selct"];
		$current_key = $_FILES["member_photo"];
		$newArray = array();
		if(!empty($_FILES["member_photo"]["name"][$key])) {
			$file_size = $_FILES["member_photo"]["size"][$key] / 1024; 
			$file_mb = $file_size / 1024;
			if($file_mb > 10) {
				echo "limit_exceeded";
				exit();
			}
			$target_file = basename($_FILES['member_photo']['name'][$key]);
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			if(in_array($imageFileType, array('png', 'jpg', 'jpeg'))) {
				if (strpos(file_get_contents($_FILES['member_photo']['tmp_name'][$key]), '<?php') !== false) 
				{
					echo "infected";
					exit();
				}
				$imagePlugin = $this->Image();
				$newArray = array('name'=>$_FILES['member_photo']['name'][$key],'type'=>$_FILES['member_photo']['type'][$key],'tmp_name'=>$_FILES['member_photo']['tmp_name'][$key],'error'=>$_FILES['member_photo']['error'][$key],'size'=>$_FILES['member_photo']['size'][$key]);
				$is_uploaded = $imagePlugin->universal_upload(array("directory"=>TEAM_IMAGES_PATH,"files_array"=>array('0'=>$newArray),"url"=>HTTP_TEAM_IMAGES_PATH,"ratio"=>true,"crop"=>false));
				if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){
					$prod_name = $is_uploaded->media_path;
					if(file_exists(TEAM_IMAGES_PATH."/".$prod_name)) {	
						$source = \Tinify\fromFile(TEAM_IMAGES_PATH."/".$prod_name);
						$source->toFile(TEAM_IMAGES_PATH."/".$prod_name);
					}
					if(file_exists(TEAM_IMAGES_PATH."/60/".$prod_name)) {	
						$source = \Tinify\fromFile(TEAM_IMAGES_PATH."/60/".$prod_name);
						$source->toFile(TEAM_IMAGES_PATH."/60/".$prod_name);
					}
					if(file_exists(TEAM_IMAGES_PATH."/160/".$prod_name)) {	
						$source = \Tinify\fromFile(TEAM_IMAGES_PATH."/160/".$prod_name);
						$source->toFile(TEAM_IMAGES_PATH."/160/".$prod_name);
					}
					if(file_exists(TEAM_IMAGES_PATH."/thumb/".$prod_name)) {	
						$source = \Tinify\fromFile(TEAM_IMAGES_PATH."/thumb/".$prod_name);
						$source->toFile(TEAM_IMAGES_PATH."/thumb/".$prod_name);
					}
					echo $prod_name;
					exit();
				}
			} else {
				echo "invalidext";
				exit();
			}
		}
	}
	
	public function exportproductsAction() {
		$fieldsArr = array(
			"product_name" => "Product Name",
			"product_image" => "Product Image",
			"category" => "Category",
			"subcategory" => "Sub Category",
			"stock" => "Stock",
			"price" => "Price",
			"qty" => "Available Quantity",
			"shipping" => "Shipping Profile"
		);
		$joinArr = array(
			'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Inner','3'=>array('category_feild')),
			'1'=>array('0'=>T_SUBCATEGORY_LIST,'1'=>'product_subcategory = subcategory_id','2'=>'Inner','3'=>array('subcategory_title')),
			'2'=>array('0'=>T_SHIPPROFILES,'1'=>'product_shippingid = shipping_id','2'=>'Inner','3'=>array('shipping_name')),
		);
		require_once ROOT_PATH.'/vendor/PHPExcel.php';
    	require_once ROOT_PATH.'/vendor/PHPExcel/IOFactory.php';
		//require_once ROOT_PATH.'/vendor/PHPExcel/Worksheet/Drawing.php';
		//require_once ROOT_PATH.'/vendor/PHPExcel/Writer/Excel2007.php';
		$objPHPExcel = new PHPExcel();
		$GetData = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_delstatus != '1'","fetchAll",array('order'=>'product_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})),$joinArr);
		if(!empty($GetData)) {
			foreach($GetData as $GetData_key => $GetData_val) {
				$color_sizes = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID","fetchAll",array('warray'=>array('TID'=>$GetData_val["product_id"])));
				$colors_arr = array();
				$sizes_arr = array();
				$total_qty = 0; $used_qty = 0;
				if(!empty($color_sizes)) {
					foreach($color_sizes as $colors_data_key => $colors_data_val) {
						$total_qty += $colors_data_val["color_qty"]; 
						$allqty_data[] = $colors_data_val["color_title"].":".$colors_data_val["color_size"].":".$colors_data_val["color_qty"];
					}
					$colorsizes = implode(",",$allqty_data);
				}
				$available_qty = $total_qty;
				if(!empty($available_qty)) {
					$stock = "In Stock";
				} else {
					$stock = "Out of Stock";
				}
				$all_colors = array_column($color_sizes,"color_slug");
				$all_colors = array_unique($all_colors);
				$all_sizes = array_column($color_sizes,"color_size");
				$all_sizes = array_unique($all_sizes);
				$new_photoz = '';
				if(!empty($GetData_val["product_defaultpic"])) {
					$new_photoz = $GetData_val["product_defaultpic"];
				} else {
					if(!empty($GetData_val["product_photos"])) {
						$photos_arr = explode(",",$GetData_val["product_photos"]);
						$new_photoz = $photos_arr[0];
					} 
				}
				$exportArr[$GetData_key] = array(
					$fieldsArr['product_name'] =>  $GetData_val["product_title"],
					$fieldsArr['product_image'] => HTTP_PRODUCT_PIC_PATH.'/'.$new_photoz,
					$fieldsArr['category'] =>  $GetData_val["category_feild"],
					$fieldsArr['subcategory'] => $GetData_val["subcategory_title"],
					$fieldsArr['stock'] =>  $stock,
					$fieldsArr['price'] => $GetData_val["product_price"],
					$fieldsArr['qty'] => $colorsizes,
					$fieldsArr['shipping'] => $GetData_val["shipping_name"]				
				);
			}
		}
		//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	//$objWriter->save('php://output'); 
		$newSheetName = "All Products";
		if(!empty($exportArr)){
			$var = exportData($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	
	public function importproductsAction() {
		$request = $this->getRequest();
		$lead = myurl_decode($this->params()->fromRoute('lead'));		
		$iserror = '';
		if($request->isPost()) {
			if($_FILES["product_file"]["type"] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $_FILES["product_file"]["type"] == 'application/vnd.ms-excel')
			{
				$filePath = $_FILES["product_file"]["tmp_name"];
				$fileName = str_replace(" ","",$_FILES["product_file"]["name"]);	
				$content_type = $_FILES["product_file"]["type"];
				$imagePlugin = $this->Image();
				$extdata = explode(".",$_FILES["product_file"]["name"]);
				$new_name = time()."-".rand(1,100000).".".$extdata[1];
				if (move_uploaded_file($_FILES['product_file']['tmp_name'], PRODUCT_PIC_PATH.'/'. $new_name)) {
					$air_file = $new_name;
				}
				if($_FILES["product_file"]["type"] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
					$type = 'xlsx';
				} else {
					$type = 'xls';
				}
				$import = importData($air_file,$type);
				unset($_FILES["product_file"]);
				if($_FILES["product_pics"]["type"] == 'application/zip' || $_FILES["product_pics"]["type"] == 'application/x-zip-compressed') {
					$filepath1 = $_FILES["product_pics"]["tmp_name"];
					$filename = $_FILES["product_pics"]["name"];
					$source = $_FILES["product_pics"]["tmp_name"];
					$type = $_FILES["product_pics"]["type"];
					$name = explode(".", $filename);
					$accepted_types = array('application/zip', 'application/x-zip-compressed', 
					'multipart/x-zip', 'application/x-compressed');

					foreach($accepted_types as $mime_type) {
					if($mime_type == $type) {
					$okay = true;
					break;
					}
					}
					$continue = strtolower($name[1]) == 'zip' ? true : false;
					if(!$continue) {
						$msg_arr["type"] = 'failure';
						$msg_arr["message"] = "Please upload a valid zip file.";
						echo json_encode($msg_arr);
						exit();
					}
					$imagePlugin2 = $this->Image();
					$is_uploaded = $imagePlugin2->universal_upload(array("directory"=>PRODUCT_DOCUMENT_PATH.'/',"files_array"=>$_FILES,"multiple"=>0),'Doc',2);
					/* PHP current path */
					$path = dirname(PRODUCT_DOCUMENT_PATH).'/'; 
					$filenoext = basename ($filename, '.zip'); 
					$filenoext = basename ($filenoext, '.ZIP');

					$myDir = $path . $filenoext; // target directory
					$myFile = $path . $filename; // target zip file
					$zipfilename = PRODUCT_DOCUMENT_PATH . '/' . $is_uploaded->media_path;
					try{
						$zip = new \ZipArchive();
					}catch(\Exception $e){
						prd($e->getMessage());
					}
					try{
						$x = $zip->open($zipfilename); 
					}catch(\Exception $e){
						prd($e->getMessage());

					}
					if($zip->numFiles>0){
						$ext='';
						for( $i = 0; $i < $zip->numFiles; $i++ ){ 
							$stat = $zip->statIndex( $i ); 

						   $namee= trim(basename($stat['name']));
						   $imgname=explode('.',$namee);
						if(strpos(strtolower($namee), 'jpg') !== false) {
						   $ext = 'jpg';
						} else if((strpos(strtolower($namee), 'jpeg') !== false)) {
						   $ext = 'jpeg';
						} else if((strpos(strtolower($namee), 'png') !== false)) {
						   $ext = 'png';
						} else{
						  $ext='';
						  break;
						}
						}
								if($ext!='' && ($ext=='jpg' || $ext=='jpeg'  || $ext=='png')){
								if ($x === true) {
								  $zip->extractTo(PRODUCT_DOCUMENT_PATH.''); // place in the directory with same name
								  $zip->extractTo(PRODUCT_DOCUMENT_PATH);
								  $zip->close();	
								  unlink($zipfilename);
								}else{
								  $newerrorMsg[] =  "There was a problem with the upload.\r\n";
									$iserror  = '1';
								 }
							  }else{
								$newerrorMsg[] = "Invalid images .\r\n";
								$iserror = '1';
							  }
					}else{
						$newerrorMsg[] = "Zip should contain at least one image.\r\n";
						$iserror = '1';
					}
					
				}
         		if(!empty($import)) {
					$insert_count = 0;
					$errorMsg = array();
					$this->frontSession['errorMsg'] = '';
					$jct = 0;
					foreach($import as $import_key => $import_val) {
					$iserror = '';
					$import_val[0] = strip_tags($import_val[0]);
					$import_val[1] = strip_tags($import_val[1]);
					$import_val[2] = strip_tags($import_val[2]);
					$import_val[3] = strip_tags($import_val[3]);
					$import_val[4] = strip_tags($import_val[4]);
					$import_val[5] = strip_tags($import_val[5]);
					$import_val[6] = strip_tags($import_val[6]);
					if($import_key != 0 && !empty($import[$import_key][0])) {
							if(empty(trim($import_val[0]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the Product Title in row '.($import_key+1).' . Right now it is blank.'; 
							}
							if(empty(trim($import_val[2]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the Category in row '.($import_key+1).' . Right now it is blank.'; 
							}
							if(empty(trim($import_val[3]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the Sub Category in row '.($import_key+1).' . Right now it is blank.'; 
							}							
							if(empty(trim($import_val[4]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the Description in row '.($import_key+1).' . Right now it is blank.'; 
							}
							if(empty(trim($import_val[5]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the color & size of product with available quantity in row '.($import_key+1).' . Right now it is blank.';
							}
							if(empty(trim($import_val[6]))) {
								$iserror = '1';
								$newerrorMsg[] = 'Please enter the name of shipping profile in row '.($import_key+1).' . Right now it is blank.'; 
							}
							if(strlen($import_val[0]) > 50) {
								$iserror = '1';
								$newerrorMsg[] = trim($import_val[0]).' entered in row '.($import_key+1).' exceeding the max allowed characters.'; 					
							}
							if(!empty($import_val[2])) {
								$category_data = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"LOWER(category_feild)  like :CID","fetch",array('warray'=>array('CID'=>'%'.strtolower(trim($import_val[2])).'%')));
								if(empty($category_data)) {
									$iserror = '1';
									$newerrorMsg[] = 'No such category found as entered in row '.($import_key+1);
								}
							}
							if(!empty($import_val[3])) {
								$subcategory_data = $this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,"LOWER(subcategory_title) like :TID","fetch",array('warray'=>array('TID'=>'%'.strtolower(trim($import_val[3])).'%')));
								if(empty($subcategory_data)) {
									$iserror = '1';
									$newerrorMsg[] = 'No such sub category found as entered in row '.($import_key+1);
								}
							}
							if(strlen($import_val[4]) > 1500) {
								$iserror = '1';
								$newerrorMsg[] = trim($import_val[4]).' entered in row '.($import_key+1).' exceeding the max allowed characters.'; 					
							}
							if (strpos($import_val[5], ':') !== false) {
							} else {
								$iserror = '1';
								if(!empty($import_val[5])) {
									$newerrorMsg[] = trim($import_val[5]). ' entered in row '.($import_key+1).' is invalid.';
								}
							}
							$check_shipprofile = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_name =:TID and shipping_clientid =:UID","fetch",array('warray'=>array('TID'=>trim($import_val[6]),'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"})));
							if(empty($check_shipprofile)) {
								$iserror = '1';
								$newerrorMsg[] = 'No such shipping profile created by you as entered in row '.($import_key+1).'.';
							}
							$newpics = array();
							if(!empty($import_val[1])) {
								$pics_arr = explode(",",$import_val[1]);
								if(!empty($pics_arr)) {
									foreach($pics_arr as $pics_arr_key => $pics_arr_val) {
										if(file_exists(PRODUCT_DOCUMENT_PATH.'/'.$pics_arr_val) && !empty($pics_arr_val)) {
											$newpics[$import_key][] = $pics_arr_val;
										}
									}
								}
							}
							$product_data['product_title'] = trim(strip_tags($import_val[0]));
							$product_data['product_category'] = $category_data["category_id"];
							$product_data['product_subcategory'] = $subcategory_data["subcategory_id"];
							$product_data['product_description'] = $import_val[4];
							$product_data['product_clientid'] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
							$product_data['product_date'] = date("Y-m-d H:i:s");
							//$product_data['product_status'] = '1';
							$product_data['product_shippingid'] = $check_shipprofile["shipping_id"];
							if(empty($iserror)) {
								$is_Inserted = $this->SuperModel->Super_Insert(T_PRODUCTS,$product_data);
								if($is_Inserted->success) {
									if(!empty($import_val[5])) {
										$extArr = explode(",",$import_val[5]);
										if(!empty($extArr)) {
											foreach($extArr as $extArr_key => $extArr_val) {
												$title_tg = ''; $size_tg = ''; $qty_tg = '';
												$color_sizes = explode(":",$extArr_val);
												$title_tg = $color_sizes[0];
												$size_tg = $color_sizes[1];
												$qty_tg = $color_sizes[2];
												$price_tg = $color_sizes[3];
												$colorsize_data["color_title"] = $title_tg;
												$colorsize_data["color_size"] = $size_tg;
												$colorsize_data["color_qty"] = $qty_tg;
												$colorsize_data["color_productid"] = $is_Inserted->inserted_id;
												$colorsize_data["color_clientid"] = $this->loggedUser->{T_CLIENT_VAR."client_id"};
												$colorsize_data["color_slug"] = strtolower($color_sizes[0]);
												$colorsize_data["color_price"] = $price_tg;
												$colorsize_data["color_fixedprice"] = $price_tg;
												if(!empty($price_tg) && is_numeric($price_tg)) {
													$this->SuperModel->Super_Insert(T_PROQTY,$colorsize_data);
												} else {
													$newerrorMsg[] = 'Invalid amount entered for '.$product_data['product_title'].' with color '.$title_tg.' in row '.($import_key+1);
													$jct += 1;
												}
											}
											if(count($extArr) == $jct) {
												$this->SuperModel->Super_Delete(T_PRODUCTS,"product_id = '".$is_Inserted->inserted_id."'");
											}
										}
									}
									if(!empty($newpics)) {
										foreach($newpics[$import_key] as $keyss=>$photos_value){
											if(!empty($photos_value)) {
												$uploaded_image_extension = getFileExtension($photos_value);
												$new_name = time()."-".rand(1,100000).".".$uploaded_image_extension;
												if (copy(PRODUCT_DOCUMENT_PATH.'/'.$photos_value, PRODUCT_PIC_PATH.'/'.$new_name)) {	
												correctImageOrientation(PRODUCT_PIC_PATH.'/'.$new_name);	
												list($width, $height) = getimagesize(PRODUCT_PIC_PATH.'/'.$new_name);
												$crop_params = array(
													"source_directory" =>PRODUCT_PIC_PATH,
													"name"=>$new_name,
													"target_name"=>$new_name,
													'_w'=>$width,
													'_h'=>$height,
													'_x'=>0,
													'_y'=>0,
													'destination'=>array(
														"thumb"=>array("size"=>"400",'crop'=>false,'ratio'=>true),
														"240"=>array("width"=>"240","height"=>"250",'crop'=>false,'ratio'=>true),
														"160"=>array("size"=>"160",'crop'=>false,'ratio'=>true),
														"60"=>array("size"=>"60",'crop'=>true,'ratio'=>true),
													),
												);	
												$imagePlugin = $this->Image();
												$is_crop = $imagePlugin->universal_crop_image($crop_params);									
												$new_nameArr[$import_key][] = $new_name;
												}
											}	
										}
										//$prod_picdata["product_upl"] = '1';
										$prod_picdata["product_photos"] = implode(",",$new_nameArr[$import_key]);
										$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_picdata,"product_id = '".$is_Inserted->inserted_id."'");
									}
									if(count($extArr) != $jct) {
										$insert_count += 1;
									}
								}  
							}
						}
					}
				}
				if($insert_count == 0) {
					$error_msg = "No record inserted successfully.";
					$msg_arr["type"] = 'failure';
				} else {
					$error_msg = $insert_count." record inserted successfully.";
					$msg_arr["type"] = 'success';
				}
				foreach($newpics[$import_key] as $keyss=>$photos_value){
					if(file_exists(PRODUCT_PIC_PATH.'/'.$photos_value) && !empty($photos_value)) {
						//unlink(PRODUCT_PIC_PATH.'/'.$photos_value);
					}
				}
				if(!empty($newerrorMsg)) {
					$file = "Import_errors";
					$original_file = $file.'_'.time().".txt";
					//$txt = fopen($file, "w") or die("Unable to open file!");
					$txt = fopen(PRODUCT_DOCUMENT_PATH. "/".$original_file,"wb");
					$filepath=PRODUCT_DOCUMENT_PATH."/".$original_file;
					//fwrite($txt, $errorMsg);
					foreach($newerrorMsg as $errorMsg_key => $errorMsg_val) {						
						fwrite($txt, " ". $errorMsg_val ."\r\n");
					}
					fclose($txt);
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename='.basename($file));
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: ' . filesize($filepath));
					header("Content-Type: text/plain");
					//readfile($filepath);
					$msg_arr["type"] = 'error';
					$msg_arr["message"] = $error_msg.' Click <a style="font-weight:bold;text-decoration:underline" data-id="'.$original_file.'" class="download-log" download>here</a> to download the error log.';
					echo json_encode($msg_arr);
					exit();
				}				
				$msg_arr["message"] = $error_msg;
				echo json_encode($msg_arr);
				exit();
			} else {
				$msg_arr["type"] = 'failure';
				$msg_arr["message"] = "You have uploaded an invalid file";
				echo json_encode($msg_arr);
				exit();
			}
		}
	}
	
	public function exportsampleAction() {
		$fieldsArr = array(
			"product_name" => "Product Name",
			"product_image" => "Image",
			"category" => "Category",
			"subcategory" => "Sub Category",
			"description" => "Description",
			"quantity" => "Quantity",
			"shipping_profile" => "Shipping Profile"
		);
		//require_once ROOT_PATH.'/vendor/PHPExcel/Worksheet/Drawing.php';
		//require_once ROOT_PATH.'/vendor/PHPExcel/Writer/Excel2007.php';
		$exportArr = array(
			$fieldsArr['product_name'] =>  'Test Product',
			$fieldsArr['product_image'] => "product1.jpg,product2.jpg",
			$fieldsArr['category'] =>  'Spooky Apparel',
			$fieldsArr['subcategory'] => 'Unisex',
			$fieldsArr['description'] => 'Description for product',
			$fieldsArr['quantity'] => 'Red:S:4:40,Red:M:2:45,Yellow:M:4:50,Green:S:2:55',
			$fieldsArr['shipping_profile'] => "Name of your created shipping profile"
		);
		//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	//$objWriter->save('php://output'); 
		$newSheetName = "Sample File";
		if(!empty($exportArr)){
			$var = exportCsv($exportArr,false,false,$newSheetName);
		}
		exit;
	}
	
	public function becomesellerAction() {
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		$form = new ProfileForm();
		if(!empty($store_data["store_doc1"])) {
			$uid = 1;
		} else {
			$uid = '';
		}
		if(!empty($store_data["store_name"])) {
			$tid = 1;
		} else {
			$tid = '';
		}
		$form->seller($uid,$tid);
		if(!empty($store_data)) {
			$post_data["seller_storename"] = $store_data["store_name"];
			$post_data["seller_companyname"] = $store_data["store_company"];
			$post_data["seller_contact"] = $store_data["store_contact"];
			$post_data["seller_location"] = $store_data["store_location"];
			$post_data["store_description"] = $store_data["store_description"];
			$form->populateValues($post_data);
		}
		$imagePlugin = $this->Image();
		$request = $this->getRequest();
		if($this->getRequest()->isPost()) {
			if($store_data["store_approval"] == '1') {
				//$this->frontSession['errorMsg'] = "Store has already been approved";
				//return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
			}
			$posted_data = $this->getRequest()->getPost();
			$files =  $request->getFiles()->toArray();
			$data["store_name"] = strip_tags($posted_data["seller_storename"]);
			$store_kdata = $this->SuperModel->Super_Get(T_STORE,"LOWER(store_name) =:SID and store_clientid != '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'","fetch",array('warray'=>array('SID'=>strtolower(trim(strip_tags($data["store_name"]))))));
			if(!empty($store_kdata)) {
				$this->frontSession['errorMsg'] = "Store name already exists. Please try with different name.";
				return $this->redirect()->tourl(APPLICATION_URL.'/dashboard');
			}
			$data["store_company"] = strip_tags($posted_data["seller_companyname"]);
			$data["store_contact"] = strip_tags($posted_data["seller_contact"]);
			$data["store_location"] = strip_tags($posted_data["seller_location"]);
			$data["store_description"] = strip_tags($posted_data["store_description"],'<br>');
			$latlng = $this->SuperModel->postData($data["store_location"],'');
			$data["store_latitude"] = $latlng["latitude"];
			$data["store_longitude"] = $latlng["longitude"];
			if($files['seller_logo']['name']!=''){	
				if (strpos(file_get_contents($files['seller_logo']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}

				if (strpos(file_get_contents($files['seller_logo']['tmp_name']), '<?=') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}

				if (strpos(file_get_contents($files['seller_logo']['tmp_name']), '<? ') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}
				$is_uploaded1 = $imagePlugin->universal_upload(array("directory"=>STORE_LOGO_PATH,"files_array"=>array('seller_logo'=>$files['seller_logo']),"multiple"=>false,"crop"=>false));	
				if($is_uploaded1->success=='1' && $is_uploaded1->media_path!=''){
					$store_logo = $is_uploaded1->media_path;
				}
			}
			if($files['seller_banner']['name']!=''){
				if (strpos(file_get_contents($files['seller_banner']['tmp_name']), '<?php') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}

				if (strpos(file_get_contents($files['seller_banner']['tmp_name']), '<?=') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}

				if (strpos(file_get_contents($files['seller_banner']['tmp_name']), '<? ') !== false) 
				{
					$this->frontSession['errorMsg'] = "File is infected";
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}
				$is_uploaded2 = $imagePlugin->universal_upload(array("directory"=>STORE_BANNER_PATH,"files_array"=>array('seller_banner'=>$files['seller_banner']),"multiple"=>false,"crop"=>false));
				if($is_uploaded2->success=='1' && $is_uploaded2->media_path!=''){
					$store_banner = $is_uploaded2->media_path;
				}
			}
			
			if(!empty($store_logo)) {
				$data["store_logo"] = $store_logo;
			} else {
				$data["store_logo"] = $store_data["store_logo"];
			}
			if(!empty($store_banner)) {
				$data["store_banner"] = $store_banner;
			} else {
				$data["store_banner"] = $store_data["store_banner"];
			}
			$data["store_clientid"] = $this->loggedUser->yurt90w_client_id;
			if(empty($store_data)) {
				$data["store_approval"] = '3';
				$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data);		
				if($isInsert->success) {
				$notify_data["notification_type"] = '1';
				$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
				$notify_data["notification_to"] = '1';
				$notify_data["notification_readstatus"] = '0';
				$notify_data["notification_date"] = date("Y-m-d H:i:s");
				$notify_data["notification_subscriberid"] = $isInsert->inserted_id;
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
				
				
				$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $this->loggedUser->yurt90w_client_name." has sent the seller request.",
							"subject" => "Seller request"
						);	
				$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				}				
				if(!empty($isInsert->inserted_id)) {
					$this->frontSession['successMsg'] = 'Seller request has been successfully sent to the admin.';
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');	
				} else {
					$this->frontSession['errorMsg'] = 'Please check entered information again.';
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}
			} else {
				if($data["store_name"] != $store_data["store_name"] || $data["store_company"] != $store_data["store_company"] || $data["store_contact"] != $store_data["store_contact"] || $data["store_location"] != $store_data["store_location"] || $data["store_logo"] != $store_data["store_logo"] || $data["store_banner"] != $store_data["store_banner"] || $data["store_description"] != $store_data["store_description"]) {
					$notify_data["notification_type"] = '1';
					$notify_data["notification_by"] = $this->loggedUser->yurt90w_client_id;
					$notify_data["notification_to"] = '1';
					$notify_data["notification_readstatus"] = '0';
					$notify_data["notification_date"] = date("Y-m-d H:i:s");
					$notify_data["notification_subscriberid"] = $store_data["store_id"];
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
					$data["store_approval"] = '3';
					$data["store_verifydeclinetxt"] = '';
					
					$mail_const_data2 = array(
								"user_name" => 'Administrator',
								"user_email" => $this->site_configs['site_email'],
								"message" => $this->loggedUser->yurt90w_client_name." has sent the seller request.",
								"subject" => "Seller request"
							);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
					$this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$this->loggedUser->yurt90w_client_id."'");
					$this->frontSession['successMsg'] = 'Seller request has been successfully sent to the admin.';
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');	
				} else {
					$this->frontSession['errorMsg'] = 'New request is same as the previous one.';
					return $this->redirect()->tourl(APPLICATION_URL.'/become-seller');
				}
				/*if($data["store_doc1"] != $store_data["store_doc1"] || $data["store_doc2"] != $store_data["store_doc2"] || $data["store_doc3"] != $store_data["store_doc3"]) {
					$notify_datas["notification_type"] = '2';
					$notify_datas["notification_by"] = $this->loggedUser->yurt90w_client_id;
					$notify_datas["notification_to"] = '1';
					$notify_datas["notification_readstatus"] = '0';
					$notify_datas["notification_date"] = date("Y-m-d H:i:s");
					$notify_datas["notification_subscriberid"] = $store_data["store_id"];
					$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_datas);
					$data["store_verification"] = '3';
					$data["store_badgedeclinetxt"] = '';
				}*/
				
			}
			
		}
		$view = new ViewModel();	
		$view->setVariable('show', 'front_profile');
		$view->setVariable('form', $form);
		$view->setVariable('store_data', $store_data);
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;	
	}
	
    /* Update profile image: Side image */
	public function updateprofileimageAction() 
	{
	    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
		$form = new ProfileForm();
		$form->profileimage();

		$request = $this->getRequest();

		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$files =  $request->getFiles()->toArray();
				if(isset($files['client_image']['name']) and !empty($files['client_image']['name'])){
					$data = array_merge($request->getPost()->toArray(),$files);
				}
				$form->setData($data);
				
	            if($form->isValid()) {
					if($files['client_image']['name']!=''){
						$target_file = basename($files['client_image']['name']);
						$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

						if(in_array($imageFileType, array('png', 'jpg', 'jpeg'))) {
							if (strpos(file_get_contents($files['client_image']['tmp_name']), '<?php') !== false) 
							{
								$this->frontSession['errorMsg'] = "File is infected";
								return $this->redirect()->tourl(APPLICATION_URL.'/profile');
							}
		
							if (strpos(file_get_contents($files['client_image']['tmp_name']), '<?=') !== false) 
							{
								$this->frontSession['errorMsg'] = "File is infected";
								return $this->redirect()->tourl(APPLICATION_URL.'/profile');
							}
		
							if (strpos(file_get_contents($files['client_image']['tmp_name']), '<? ') !== false) 
							{
								$this->frontSession['errorMsg'] = "File is infected";
								return $this->redirect()->tourl(APPLICATION_URL.'/profile');
							}
							$imagePlugin = $this->Image();
							$is_uploaded = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH,"files_array"=>$files,"url"=>HTTP_PROFILE_IMAGES_PATH,"crop"=>false));	
							if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){
                                \Tinify\setKey(TINY_KEY);
                                if(file_exists(PROFILE_IMAGES_PATH."/".$is_uploaded->media_path)) {	
									$source = \Tinify\fromFile(PROFILE_IMAGES_PATH."/".$is_uploaded->media_path);
									$source->toFile(PROFILE_IMAGES_PATH."/".$is_uploaded->media_path);
								}
								$formData = $request->getPost();
								$var = json_decode($formData['cordinates']);
								$crop_params = array(
									"source_directory" =>PROFILE_IMAGES_PATH,
									"name"=>$is_uploaded->media_path,
									"target_name"=>$is_uploaded->media_path,
									'_w'=>$var->cordinates[2]->W,
									'_h'=>$var->cordinates[3]->H,
									'_x'=>$var->cordinates[0]->X,
									'_y'=>$var->cordinates[1]->Y,
									'quality'=>200,
									'destination'=>array(
										"900x600"=>array("width"=>"900","height"=>"600",'crop'=>false,'ratio'=>true),
										"412x274"=>array("width"=>"412","height"=>"274",'crop'=>false,'ratio'=>true),	
										"206x137"=>array("width"=>"206","height"=>"137",'crop'=>false,'ratio'=>true),
										"60"=>array("width"=>"66","height"=>"66",'crop'=>false,'ratio'=>true),	
									),
									'source'=>'200',
								);
								$imagePlugin = $this->Image();
								$is_crop = $imagePlugin->universal_crop_image($crop_params);
								/* start: 200 x 200 crop */
								$source_dir = PROFILE_IMAGES_PATH;
								$source_dir2 = PROFILE_IMAGES_PATH.'/900x600';
								if(file_exists($source_dir2.'/'.$is_uploaded->media_path)){
									$src_filename_with_path = $source_dir.'/900x600/'.$is_uploaded->media_path;
									$dest_filename_with_path = $source_dir.'/200/'.$is_uploaded->media_path;

									$is_crop2 = $imagePlugin->universal_image_resize($src_filename_with_path, $dest_filename_with_path, $width=200, $height=200, $crop=1);
								}
								/* end: 200 x 200 crop */

								if($is_crop->success=="1"){
                                    if(file_exists(PROFILE_IMAGES_PATH."/60/".$is_uploaded->media_path)) {	
										$source = \Tinify\fromFile(PROFILE_IMAGES_PATH."/60/".$is_uploaded->media_path);
										$source->toFile(PROFILE_IMAGES_PATH."/60/".$is_uploaded->media_path);
									}
									/*--------------------------------------*/
									if(isset($data['uploadImageType']) and $data['uploadImageType']=='mainprofile')
									{
										// mainprofile = for right side image

										$data_to_update[T_USERS_CONST.'_image'] = $is_uploaded->media_path;
										
										$this->SuperModel->Super_Insert(T_USERS,$data_to_update,T_USERS_CONST."_id='".$this->loggedUser->yurt90w_client_id."'");

										/* update user photos table */
										$Where = "cp_client_id=:cpClientId and cp_is_profile_image=:cpIsProfileImage";
										$userData = $this->SuperModel->Super_Get(T_CLIENTS_PHOTOS,$Where,"fetch",array("warray"=>array("cpClientId"=>$this->loggedUser->yurt90w_client_id, "cpIsProfileImage"=>'1' )));

										if(empty($userData)){
											// not exists default photo 
											$updateChildData = array();
											$updateChildData['cp_client_id'] = $this->loggedUser->yurt90w_client_id;
											$updateChildData['cp_photo_name'] = $is_uploaded->media_path;
											$updateChildData['cp_is_profile_image'] = '1';
											$updateChildData['cp_created'] = date('Y-m-d H:i:s');
											$this->SuperModel->Super_Insert(T_CLIENTS_PHOTOS,$updateChildData);

										} else {
											// exists default photo 
											if(isset($userData['cp_id']) and !empty($userData['cp_id'])){
												$updateChildData = array();
												$updateChildData['cp_photo_name'] = $is_uploaded->media_path;
												$updateChildData['cp_is_profile_image'] = '1';
												$this->SuperModel->Super_Insert(T_CLIENTS_PHOTOS,$updateChildData,"cp_id='".$userData['cp_id']."'");
											}
										}

										echo json_encode(array("status" => 200,"message" => "Profile photo uploaded successfully.", "data" => array('image'=>$is_uploaded->media_path) ));

									} else {
										// siximage = for one of six images
										// nothing to do because when form is submitted, information will be stored
										echo json_encode(array("status" => 200,"message" => "Profile photo uploaded successfully.", "data" => array('image'=>$is_uploaded->media_path) ));
									}

								} else {
									echo json_encode(array("status" => 400,"message" => "Problem occured in resize process", "data" => array() ));	
								}

							} elseif($is_uploaded->error=='1' ){
								echo json_encode(array("status" => 400,"message" => "Invalid Photo.", "data" => array() ));
							}

						} else {
							echo json_encode(array("status" => 400,"message" => "Invalid Photo.", "data" => array() ));
						}
					}				

				} else {
					echo json_encode(array("status" => 400,"message" => "Something went wrong, please try again.", "data" => array() ));
				}
				
			}
			exit;

		} else {
			$this->frontSession['errorMsg'] = 'Unauthorized access!!';
			return $this->redirect()->tourl(APPLICATION_URL);
		}
	}

	/* Update member profile images - 6 images */
	public function updatememberprofileimagesAction() 
	{
		$form = new MemberprofileForm($this->layout()->translator);
		$form->profileimage();

		$request = $this->getRequest();

		if ($this->getRequest()->isXmlHttpRequest()) {
			if($this->getRequest()->isPost()){
				$data = $this->params()->fromPost();
				$files =  $request->getFiles()->toArray();
				if(isset($files['client_images_six']['name']) and !empty($files['client_images_six']['name'])){
					$data = array_merge($request->getPost()->toArray(),$files);
				}
				$form->setData($data);

	            if($form->isValid()) {
					if($files['client_images_six']['name']!=''){
						$target_file = basename($files['client_images_six']['name']);
						$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

						if(in_array($imageFileType, array('png', 'jpg', 'jpeg'))) {
							$imagePlugin = $this->Image();
							$is_uploaded = $imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH,"files_array"=>$files,"url"=>HTTP_PROFILE_IMAGES_PATH,"crop"=>false));	

							if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){

								$formData = $request->getPost();
								$var = json_decode($formData['cordinates']);

								$crop_params = array(
									"source_directory" =>PROFILE_IMAGES_PATH,
									"name"=>$is_uploaded->media_path,
									"target_name"=>$is_uploaded->media_path,
									'_w'=>$var->cordinates[2]->W,
									'_h'=>$var->cordinates[3]->H,
									'_x'=>$var->cordinates[0]->X,
									'_y'=>$var->cordinates[1]->Y,
									'quality'=>200,
									'destination'=>array(
										"200"=>array("size"=>"200",'crop'=>true,'ratio'=>true),
									),
									'source'=>'200',
								);
								$imagePlugin = $this->Image();
								$is_crop = $imagePlugin->universal_crop_image($crop_params);


								if($is_crop->success=="1"){

									/*--------------------------------------*/

									/* insert in child table */
									$Where = "cp_client_id=:cpClientId";
									$userData = $this->SuperModel->Super_Get(T_CLIENTS_PHOTOS,$Where,"fetchall",array("warray"=>array("cpClientId"=>$this->loggedUser->yurt90w_client_id)));

									echo json_encode(array("status" => 200,"message" => "Profile photo uploaded successfully.", "filename" => $is_uploaded->media_path ));

								} else {
									echo json_encode(array("status" => 400,"message" => "Problem occured in resize process", "data" => array() ));	
								}

							} elseif($is_uploaded->error=='1' ){
								echo json_encode(array("status" => 400,"message" => "Invalid Photo.", "filename" => "" ));
							}

						} else {
							echo json_encode(array("status" => 400,"message" => "Invalid Photo.", "filename" => "" ));
						}
					}				

				} else {
					echo json_encode(array("status" => 400,"message" => "Something went wrong, please try again.", "filename" => "" ));
				}
				
			}
			exit;

		} else {
			$this->frontSession['errorMsg'] = 'Unauthorized access!!';
			return $this->redirect()->tourl(APPLICATION_URL);
		}
	}

	public function dashboardAction()
	{
		$pageid=5;
		$view = new ViewModel();	
		$page_content=$this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>$pageid)));
		
		$page_content['page_content_'.$_COOKIE['currentLang']]=str_ireplace(array('{img_url}','{site_path}'),array(HTTP_IMG_PATH,APPLICATION_URL),$page_content['page_content_'.$_COOKIE['currentLang']]);

		$view->setVariable('page_content',$page_content);	
		return $view;
	}


	public function readnotifyAction()
	{
		$this->SuperModel->Super_Insert(T_NOTIFICATION,array('notification_read_status'=>'1'),"notification_user_id='".$this->loggedUser->yurt90w_client_id."'");
		exit;
	}

	/* Update user profile information */
	public function indexAction()
    {
		$_SESSION["logstat"] = '2';
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->yurt90w_client_id)));
		if($store_data["store_approval"] == '1') { 
			$header_btn = 'Update Seller Profile';
		} else {
			$header_btn = 'Become a Seller';
		}
		$view = new ViewModel();	
		$userData = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id='".$this->loggedUser->yurt90w_client_id."'","fetch");

		$tabid = $this->params()->fromRoute("tabid");
		if(empty($tabid)){
			$tabid = 1;	// active first tab
		}

		$tabPageTitle = '';
		if($tabid==1){
			$tabPageTitle = "ACCOUNT SETTINGS";
		} else if($tabid==2){
			$tabPageTitle = "CHANGE PASSWORD";
		}
		if($tabid == 2) {
			if($this->loggedUser->yurt90w_client_signup_type == 'social') {
				$this->frontSession['errorMsg']='This feature is not available as your email was registered by doing Social Sign-in.';
				return $this->redirect()->tourl(APPLICATION_URL.'/profile');
			}
		}
		$view->setVariable('active_tabid', $tabid);
		$view->setVariable('header_btn', $header_btn);
		$view->setVariable('tabPageTitle', $tabPageTitle);
        $country_data = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll",array('fields'=>'country_name_en'));
		if(!empty($country_data)) {
			foreach($country_data as $country_data_key => $country_data_val) {
				$country_list[$country_data_val["country_name_en"]] = $country_data_val["country_name_en"];	
			}
		}
		/* User profile */
		$form = new ProfileForm();
		$form->profile($country_list);

		$form1 = new ProfileForm(); 
		$form1->changepassword();

		$popuserData = removePrefixFromFieldValue($userData,T_DB_CONST);
		$cltname = explode(" ",$popuserData["client_name"]);
		if(count($cltname) > 2) {
			if(!empty($cltname[2])) {
				$name = $cltname[2];	
			} if(!empty($cltname[3])) {
				$name.= " ".$cltname[3];
			}
			$popuserData["client_firstname"] = $cltname[0];
			$popuserData["client_lastname"] = $cltname[1]." ".$name;
		} else {
			$popuserData["client_firstname"] = $cltname[0];
			$popuserData["client_lastname"] = $cltname[1];
		}
		if(!empty($popuserData["client_birthday"]) && $popuserData["client_birthday"] != '0000-00-00') {
			$popuserData["client_birthday"] = date("m/d/Y",strtotime($popuserData["client_birthday"]));
		} else {
			$popuserData["client_birthday"] = '';
		}
		$form->populateValues($popuserData);

		$request = $this->getRequest();			

		if ($request->isPost())
		{ 
			$data = $request->getPost();
			foreach($data as $saveKey => $saveValue){
				$data[$saveKey] = htmlspecialchars($saveValue);
			}
			$PtformType = $form;
			$postedData = array();
			if(isset($data['password_csrf'])){
				$PtformType = $form1;
				$postedData = $data;
				
			} else {
				$files = $request->getFiles()->toArray();	
				if(isset($files['client_image']['name']) and $files['client_image']['name'] != ''){
					$postedData=array_merge($request->getPost()->toArray(),$files);

				} else {
					$postedData=$data;
				}
			}
		
			$isMsgUpdate = 0;
			$postedData = decryptPswFields($postedData);
			$PtformType->setData($postedData);	

			if($PtformType->isValid())
			{ 
				$isPostError = 0;
				$data_to_update = $postedData;	//$PtformType->getData();	
				/* change password */
				if(isset($data_to_update['password_csrf'])){
					unset($data_to_update['btnsubmit']);
					unset($data_to_update['password_csrf']);
					$data_to_update = GetFormElementsName($data_to_update,T_DB_CONST);
					if(trim($data_to_update[T_USERS_CONST.'_old_password'])==trim($data_to_update[T_USERS_CONST.'_password'])){
						$this->frontSession['errorMsg']='New password should be different from old password';
						return $this->redirect()->tourl(APPLICATION_URL.'/profile/2');
					}

					if($data_to_update[T_USERS_CONST.'_password'] == $data_to_update[T_USERS_CONST.'_rpassword'])
					{
						$password = md5($data_to_update[T_USERS_CONST.'_old_password']);
						$passWhere=T_USERS_CONST."_id=:clientId and ".T_USERS_CONST."_password=:clientPwd";
						$isCheck = $this->SuperModel->Super_Get(T_USERS,$passWhere,"fetch",array("warray"=>array("clientId"=>$this->loggedUser->yurt90w_client_id,'clientPwd'=>$password)));
						if($isCheck)
						{
							$dataToUpdate[T_USERS_CONST.'_password'] = md5($data_to_update[T_USERS_CONST.'_password']);

							$isUpdate=$this->SuperModel->Super_Insert(T_USERS,$dataToUpdate,T_USERS_CONST."_id='".$this->loggedUser->yurt90w_client_id."'");
							if($isUpdate->success)
							{
								$this->EmailModel->sendEmail('user_password_update',array("user_name"=>$this->loggedUser->yurt90w_client_name,'user_email'=>$this->loggedUser->yurt90w_client_email));

								$this->frontSession['successMsg'] = 'Password has been changed successfully.';
							    return $this->redirect()->tourl(APPLICATION_URL.'/change-password/2');
							}

						} else {
							$this->frontSession['errorMsg'] = 'Old password is mismatched';
							return $this->redirect()->tourl(APPLICATION_URL.'/change-password/2');
						}

					} else {
						$this->frontSession['errorMsg'] = 'New password and confirm password mismatch';
						return $this->redirect()->tourl(APPLICATION_URL.'/change-password/2');
					}
					/* end of password */

				} else {

					$posted_password = '';
					if($this->loggedUser->yurt90w_client_signup_type == 'social') {
					} else {
					if(isset($data_to_update['client_email_password']) and !empty($data_to_update['client_email_password'])){
						$posted_password = $data_to_update['client_email_password'];
					} }
					
					unset($data_to_update['client_image'],$data_to_update['startedbtn'],$data_to_update['post_csrf'],$data_to_update['client_email_password']);
					$data_to_update = GetFormElementsName($data_to_update,T_DB_CONST);
					$data_to_update[T_USERS_CONST.'_name'] = strip_tags($data_to_update[T_USERS_CONST.'_firstname']).' '.strip_tags($data_to_update[T_USERS_CONST.'_lastname']);
					$data_to_update[T_USERS_CONST.'_username'] = strip_tags($data_to_update[T_USERS_CONST.'_username']);
					if(!preg_match('/^\w{5,}$/', trim($data_to_update[T_USERS_CONST.'_username']))) { 
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Please enter username as alphanumeric & minimum 5 characters.");
							return $this->redirect()->toUrl(APPLICATION_URL.'/register');
						}
						if($this->loggedUser->yurt90w_client_signup_type == 'social') {
							$data_to_update[T_USERS_CONST.'_email'] = $this->loggedUser->yurt90w_client_email;
						} else {
					$data_to_update[T_USERS_CONST.'_email'] = strip_tags(strtolower($data_to_update[T_USERS_CONST.'_email']));
						}
					$data_to_update[T_USERS_CONST.'_address'] = strip_tags($data_to_update[T_USERS_CONST.'_address']);
					$data_to_update[T_USERS_CONST.'_gender'] = strip_tags($data_to_update[T_USERS_CONST.'_gender']);
					unset($data_to_update[T_USERS_CONST.'_firstname']); unset($data_to_update[T_USERS_CONST.'_lastname']);
					$checkName = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST.'_username="'.$data_to_update[T_USERS_CONST.'_username'].'" and '.T_USERS_CONST.'_id!="'.$this->layout()->loggedUser->{T_USERS_CONST.'_id'}.'"','fetch');
					if(!empty($checkName)){
						$this->frontSession['errorMsg'] = "The username '".$data_to_update[T_USERS_CONST.'_username']."' is already registered in our database.";
						return $this->redirect()->toUrl(APPLICATION_URL.'/profile');
					}
					/* check email already taken by any other user or not */
					$isEmailExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$data_to_update[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_id!='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'","fetch");

					if(!empty($isEmailExist)){
						$this->frontSession['errorMsg'] = 'Email already exists, please enter another email.';
						return $this->redirect()->tourl(APPLICATION_URL.'/profile/1');
					}

					/* if posted password does not match with current account password */
					if(!empty($posted_password) and md5($posted_password) != $this->layout()->loggedUser->{T_USERS_CONST.'_password'}){
						$this->frontSession['errorMsg'] = 'Your entered password and account password do not match.';
						return $this->redirect()->tourl(APPLICATION_URL.'/profile/1');
					}

					/*if email is updated send email */
					if(!empty($posted_password) and md5($posted_password) == $this->layout()->loggedUser->{T_USERS_CONST.'_password'} and $this->layout()->loggedUser->{T_USERS_CONST.'_email'} != $data_to_update[T_USERS_CONST.'_email'] )
					{

						/*$isEmailExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$data_to_update[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_id!='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'","fetch");*/

						$resetKey = md5($this->layout()->loggedUser->{T_USERS_CONST.'_id'}."!@#$%^".$this->layout()->loggedUser->{T_USERS_CONST.'_created'}.time());

						$UpdateArr = array(
							T_USERS_CONST.'_email_update' => $data_to_update[T_USERS_CONST.'_email'],
							T_USERS_CONST.'_reset_key' => $resetKey,
						);	
						$is_update=$this->SuperModel->Super_Insert(T_USERS,$UpdateArr,T_USERS_CONST."_id='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'");

						if(is_object($is_update) and $is_update->success){
							/* send email to new email holder to verify account email */
							$verifyLink = APPLICATION_URL."/verify-email/".$resetKey;

							$newMailText = '<p style="color:#191919;font-size: 15px;">Please verify your email address via below link: </p><p style="text-align:center"><a href="'.$verifyLink.'" style="display: inline-block; padding: 11px 30px; margin: 20px 0px 30px; font-size: 15px; color: #fff; background: #010101; text-decoration:none;">Verify Email</a></p>';	
			
							$newMailArr = array(
								"user_name" => ucwords(strtolower($this->layout()->loggedUser->{T_USERS_CONST.'_name'})),
								"user_email" => $data_to_update[T_USERS_CONST.'_email'],
								"message" => $newMailText,
								"subject" => "Verify email address"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$newMailArr);



							/* send email to old email holder to notify that email has been changed */
							$user_os        =   getOS();
							$user_browser   =   getBrowser();
							$user_browser_name = '';
							if(isset($user_browser['name']) and !empty($user_browser['name'])){
								$user_browser_name = $user_browser['name'].' (Version: '.$user_browser['version'].')';
							}	

							$ip = $_SERVER['REMOTE_ADDR'];

							$device_details =   "<strong style='color:#191919'>Browser: </strong>".$user_browser_name."<br /><strong style='color:#191919'>IP: </strong>".$ip."";

							$oldMailText = '<p style="color:#191919;font-size: 15px;" >Your email has been updated with '.$data_to_update[T_USERS_CONST.'_email'].'. </p><p><strong style="color:#191919;">Device Details: </strong></p><p>'.$device_details.'</p>';	

							$oldMailArr = array(
								"user_name" => ucwords(strtolower($this->layout()->loggedUser->{T_USERS_CONST.'_name'})),
								"user_email" => $this->layout()->loggedUser->{T_USERS_CONST.'_email'},
								"message" => $oldMailText,
								"subject" => "Email has been updated."
							);	

							$isSend = $this->EmailModel->sendEmail('notification_email',$oldMailArr);

							$this->frontSession['successMsg']=$this->layout()->translator->translate("Email verification link has been sent to the updated email address. Please verify to update it.");
							$isMsgUpdate = 1;

							// no need to update actual email right now
							unset($data_to_update[T_USERS_CONST.'_email']);

						} else {	
							$this->frontSession['errorMsg'] =$this->layout()->translator->translate("check_info_txt");

						}
					}

					if($isPostError == 0){
						$data_to_update["yurt90w_client_birthday"] = date("Y-m-d",strtotime($data_to_update["yurt90w_client_birthday"]));
						$isUpdate = $this->UserModel->add($data_to_update,$this->loggedUser->yurt90w_client_id);
						if($isMsgUpdate == 0){
							$this->frontSession['successMsg'] = 'Profile updated successfully.';
						}
						return $this->redirect()->tourl(APPLICATION_URL.'/profile');
					}
				}

		  } else {
			$error_msg = $PtformType->getMessages();
			if(isset($error_msg['client_name']['notAlpha']) and !empty($error_msg['client_name']['notAlpha'])){
				$msg = 'Entered full name contains non alphabetic characters.';
			} else {
				$msg = 'Please check entered information again.';
			}
			$this->frontSession['errorMsg'] = $msg;
		  }

		}

		$view->setVariable('form', $form);
		$view->setVariable('form1', $form1);
		$view->setVariable('show', 'front_profile');
		$view->setVariable('store_data',$store_data);	
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('site_configs', $this->site_configs);
		
		return $view;

	}

	/* Update member profile information */
	public function memberupdateprofileAction()
    {
		$view = new ViewModel();	
		$userData = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id='".$this->loggedUser->yurt90w_client_id."'","fetch");

		$tabid = $this->params()->fromRoute('tabid',1);
		if($tabid!="1" && $tabid!="2" && $tabid!="3"){
			$tabid="1";
			return $this->redirect()->toRoute('front_profile',array("tabid"=>1));
		}

		$tabPageTitle = '';
		if($tabid==1){
			$tabPageTitle = "ACCOUNT SETTINGS";
		} else if($tabid==2){
			$tabPageTitle = "CHANGE PASSWORD";
		} else if($tabid==3){
			$tabPageTitle = "STRIPE CONNECT";
		}

		$view->setVariable('active_tabid', $tabid);
		$view->setVariable('tabPageTitle', $tabPageTitle);


		/* photos data */
		$stored_photos = array();
		$stored_photos_name = '';
		$default_photo_name = '';
		$Where = "cp_client_id=:cpClientId and cp_is_profile_image=:cpIsProfileImage";
		$userPhotosData = $this->SuperModel->Super_Get(T_CLIENTS_PHOTOS,$Where,"fetchall",array("warray"=>array("cpClientId"=>$this->loggedUser->yurt90w_client_id,"cpIsProfileImage"=>"2"),'order'=> 'cp_id asc') );
		if(count($userPhotosData) > 0){
			foreach($userPhotosData as $data){
				$stored_photos[] = $data['cp_photo_name'];
				/*if($data['cp_is_default']=='1'){
					$default_photo_name = $data['cp_photo_name'];
				}*/
			}
			$stored_photos_name = implode(',', $stored_photos);
		}
		$view->setVariable('stored_photos_name', $stored_photos_name);
		$view->setVariable('default_photo_name', $default_photo_name);
		$view->setVariable('userPhotosData', $userPhotosData);


		/* User profile */
		$form = new MemberprofileForm($this->layout()->translator);
		$form->profile();

		$form1 = new MemberprofileForm($this->layout()->translator); 
		$form1->changepassword();

		$paymentform = new MemberprofileForm($this->layout()->translator); 
		$paymentform->stripeconnect();

		$popuserData = removePrefixFromFieldValue($userData,T_DB_CONST);

		$popuserData['client_images_name'] = $stored_photos_name;
		$popuserData['client_default_image_name'] = $default_photo_name;

		$form->populateValues($popuserData);

		/* Stripe */
		$stripe_secret_key = trim($this->site_configs['stripe_test_secret_key']);
		if(TEST===false){
			// live mode
			$stripe_secret_key = trim($this->site_configs['stripe_live_secret_key']);
		} 
		require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
		\Stripe\Stripe::setApiKey($stripe_secret_key);
		\Stripe\Stripe::setApiVersion("2019-05-16");
		
		if($this->layout()->loggedUser->yurt90w_client_account_id!=''){
			$detailsData = \Stripe\Account::retrieve($this->layout()->loggedUser->yurt90w_client_account_id);
			
			$infoData = $detailsData['external_accounts']['data'][0];
			$month = $detailsData['individual']['dob']['month'];
			$day = $detailsData['individual']['dob']['day'];
			if($detailsData['individual']['dob']['month']<10)
			$month = '0'.$detailsData['individual']['dob']['month'];
			if($detailsData['individual']['dob']['day']<10)
			$day = '0'.$detailsData['individual']['dob']['day'];
			$dob = $day.'-'.$month.'-'.$detailsData['individual']['dob']['year'];		
			$bankData=array(
				'client_accnumber'		=> 'XXXXXXXX'.$infoData->last4,
				'client_cnfaccnumber'	=> 'XXXXXXXX'.$infoData->last4,
				'client_routenumber'	=> $infoData->routing_number,
				'client_dob'			=> $dob,
			);
		}

		$request = $this->getRequest();			

		if ($request->isPost())
		{ 
			$data = $request->getPost();
			foreach($data as $saveKey => $saveValue){
				$data[$saveKey] = htmlspecialchars($saveValue);
			}

			$PtformType = $form;
			$postedData = array();
			if(isset($data['password_csrf'])){
				$PtformType = $form1;
				$postedData = $data;
				
			} else {
				$files = $request->getFiles()->toArray();	
				if(isset($files['client_image']['name']) and $files['client_image']['name'] != ''){
					$postedData=array_merge($request->getPost()->toArray(),$files);

				} else {
					$postedData=$data;
				}
			}

			if(isset($data['payment_csrf'])){
				$PtformType=$paymentform;
			}
		
			$isMsgUpdate = 0;
			$postedData = decryptPswFields($postedData);
			$PtformType->setData($postedData);	
			
			if($PtformType->isValid())
			{ 
				$isPostError = 0;
				$data_to_update = $postedData;	//$PtformType->getData();	

				if(isset($data_to_update['payment_csrf'])){
					/* Stripe connect */
					$acc_last4_digit = substr($data_to_update['client_accnumber'],-4);
					$newFlag = 0;
					if(!isset($infoData)){
						$newFlag=1;
					}
					else if((isset($infoData) && $acc_last4_digit!=$infoData->last4) || (isset($infoData) && $data_to_update['client_routenumber']!=$infoData->routing_number)){
						$newFlag=1;
					}
					
					if($newFlag==1){		
						try{
							$DYear = date('Y',strtotime($data_to_update['client_dob']));
							$DMonth = date('m',strtotime($data_to_update['client_dob']));
							$DDate = date('d',strtotime($data_to_update['client_dob']));
							
							$routing_number = $data_to_update['client_routenumber'];
							$curr="usd";	//"usd" or "gbp" 
							
							$ssn_number = $data_to_update['client_ssnnumber'];
							$ssn_last4_digit = substr($data_to_update['client_ssnnumber'],-4);
							$connectInfo=(
								array(
									"type" => "custom",
									'requested_capabilities' => array('card_payments'),
									
									"external_account" => array(
										"object" => "bank_account",
										"currency" => $curr,
										"country" => 'US',	//US or GB
										"routing_number" => $routing_number,
										"account_number" => $data_to_update['client_accnumber'],
									),
									"individual" => array(
										"email" => $this->layout()->loggedUser->yurt90w_client_email,
										'dob'=> array('day'=>$DDate,'month'=>$DMonth,'year'=>$DYear),
										
										'address'=>array(
										'line1'=>$this->layout()->loggedUser->yurt90w_client_address,
										'line2'=>$this->layout()->loggedUser->yurt90w_client_address),													
										'first_name'=> $this->layout()->loggedUser->yurt90w_client_name,
										'last_name'=> $this->layout()->loggedUser->yurt90w_client_name,
									),
									
									"business_type"=> "individual",
									/* 'legal_entity'=>array(
										'first_name'=> $this->layout()->loggedUser->fdec36e_clientele_first_name,
										'last_name'=> $this->layout()->loggedUser->fdec36e_clientele_last_name,
										'dob'=> array('day'=>$DDate,'month'=>$DMonth,'year'=>$DYear),
										'type'=>"individual",
										'ssn_last_4'=>$ssn_last4_digit,
										'address'=>array(
											'city'=>$cityInfoData['city_name_en'],
											'state'=>$cityInfoData['state_name_en'],
											'postal_code'=>$this->layout()->loggedUser->fdec36e_clientele_postal_code,
											'line1'=>$this->layout()->loggedUser->fdec36e_clientele_address1.', '.$this->layout()->loggedUser->fdec36e_clientele_address2
										),
									),*/
									"tos_acceptance" => array(
										"date"	=> time(),
										"ip"	=> $_SERVER['REMOTE_ADDR'],
									)
								)
							);
							
							// prd($connectInfo);

							try{
								$acct = \Stripe\Account::create($connectInfo); 
								// SAVE SUBS DETAILS
								$clientArr=array(
									T_CLIENT_VAR.'client_account_id'=> $acct->id,
								); 
								
								$this->SuperModel->Super_Insert(T_CLIENTS,$clientArr,T_CLIENT_VAR."client_id='".$this->layout()->loggedUser->yurt90w_client_id."'"); 
								$this->frontSession['successMsg']="Thanks, your bank account has now been added. You are now able to receive a payment.";
								return $this->redirect()->toRoute('front_memberupdateprofile',array("tabid"=>3));
							}
							catch (\Exception $e){ 
								$this->frontSession['errorMsg'] = $e->getMessage();
								$errorFlag = 1;
								return $this->redirect()->toRoute('front_memberupdateprofile',array("tabid"=>3));
							}
						}
						catch (\Exception $e){
							$this->frontSession['errorMsg'] = $e->getMessage();
							$errorFlag = 1;
							return $this->redirect()->toRoute('front_memberupdateprofile',array("tabid"=>3));
						}

					} else {

						$isStripeConnectExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."' and ".T_USERS_CONST."_account_id IS NOT NULL","fetch");
						if(!empty($isStripeConnectExist)){
							// update case 
							$this->frontSession['successMsg'] = "You have successfully updated your bank account.";	
						} else {
							// add case 
							$this->frontSession['successMsg'] = "You have successfully added your bank account.";
						}						
						return $this->redirect()->toRoute('front_memberupdateprofile',array("tabid"=>3)); 
					}

				} else if(isset($data_to_update['password_csrf'])){
					/* change password */
					unset($data_to_update['btnsubmit']);
					unset($data_to_update['password_csrf']);
					$data_to_update = GetFormElementsName($data_to_update,T_DB_CONST);
					if(trim($data_to_update[T_USERS_CONST.'_old_password'])==trim($data_to_update[T_USERS_CONST.'_password'])){
						$this->frontSession['errorMsg']='New password should be different from old password';
						return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/2');
					}

					if($data_to_update[T_USERS_CONST.'_password'] == $data_to_update[T_USERS_CONST.'_rpassword'])
					{
						$password = md5($data_to_update[T_USERS_CONST.'_old_password']);
						$passWhere=T_USERS_CONST."_id=:clientId and ".T_USERS_CONST."_password=:clientPwd";
						$isCheck = $this->SuperModel->Super_Get(T_USERS,$passWhere,"fetch",array("warray"=>array("clientId"=>$this->loggedUser->yurt90w_client_id,'clientPwd'=>$password)));

						if($isCheck)
						{
							$dataToUpdate[T_USERS_CONST.'_password'] = md5($data_to_update[T_USERS_CONST.'_password']);

							$isUpdate=$this->SuperModel->Super_Insert(T_USERS,$dataToUpdate,T_USERS_CONST."_id='".$this->loggedUser->yurt90w_client_id."'");	

							if($isUpdate->success)
							{
								$this->EmailModel->sendEmail('user_password_update',array("user_name"=>$this->loggedUser->yurt90w_client_name,'user_email'=>$this->loggedUser->yurt90w_client_email));

								$this->frontSession['successMsg'] = 'Password has been changed successfully.';
							    return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/2');
							}

						} else {
							$this->frontSession['errorMsg'] = 'Old password is mismatched';
							return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/2');
						}

					} else {
						$this->frontSession['errorMsg'] = 'New password and confirm password mismatch';
						return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/2');
					}
					/* end of password */

				} else {
					/* Account settings - update profile */

					$posted_password = '';
					if(isset($data_to_update['client_email_password']) and !empty($data_to_update['client_email_password'])){
						$posted_password = $data_to_update['client_email_password'];
					}

					unset($data_to_update['client_image'],$data_to_update['bttnsubmit'],$data_to_update['post_csrf'],$data_to_update['client_email_password']);
					$data_to_update = GetFormElementsName($data_to_update,T_DB_CONST);

					$data_to_update[T_USERS_CONST.'_email'] = strtolower($data_to_update[T_USERS_CONST.'_email']);

					$update_data = array();
					$update_data[T_USERS_CONST.'_name'] = $data_to_update[T_USERS_CONST.'_name'];
					$update_data[T_USERS_CONST.'_email'] = $data_to_update[T_USERS_CONST.'_email'];
					$update_data[T_USERS_CONST.'_company_name'] = $data_to_update[T_USERS_CONST.'_company_name'];
					$update_data[T_USERS_CONST.'_company_address'] = $data_to_update[T_USERS_CONST.'_company_address'];
					$update_data[T_USERS_CONST.'_phone'] = $data_to_update[T_USERS_CONST.'_phone'];
					$update_data[T_USERS_CONST.'_website_url'] = $data_to_update[T_USERS_CONST.'_website_url'];

					$update_data[T_USERS_CONST.'_address_lat'] = $data_to_update[T_USERS_CONST.'_address_lat'];
					$update_data[T_USERS_CONST.'_address_long'] = $data_to_update[T_USERS_CONST.'_address_long'];

					/*$isEmailExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$update_data[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_id!='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'","fetch");*/

					/* no need to update profile main image in user table because 6 images are separate
					from main profile image */
					unset($update_data[T_USERS_CONST.'_image']);

					/* delete 6 images, not main profile image */
					$isdeleted = $this->SuperModel->Super_Delete(T_CLIENTS_PHOTOS,'cp_client_id ="'.$this->loggedUser->yurt90w_client_id.'" and cp_is_profile_image="2"');

					/* insert new images */					
					if($data_to_update[T_USERS_CONST.'_images_name']!=''){
						$exp_files = explode(',', $data_to_update[T_USERS_CONST.'_images_name']);
						if(count($exp_files) > 0){
							foreach($exp_files as $data){
								$updateChildData = array();
								$updateChildData['cp_client_id'] = $this->loggedUser->yurt90w_client_id;
								$updateChildData['cp_photo_name'] = $data;
								$updateChildData['cp_is_profile_image'] = '2';
								$updateChildData['cp_created'] = date('Y-m-d H:i:s');
								$this->SuperModel->Super_Insert(T_CLIENTS_PHOTOS,$updateChildData);
							}
						}
					}

					/* check email already taken by any other user or not */
					$isEmailExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$data_to_update[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_id!='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'","fetch");

					if(!empty($isEmailExist)){
						$this->frontSession['errorMsg'] = 'Email already exists, please enter another email.';
						return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/1');
					}

					/* if posted password does not match with current account password */
					if(!empty($posted_password) and md5($posted_password) != $this->layout()->loggedUser->{T_USERS_CONST.'_password'}){
						$this->frontSession['errorMsg'] = 'Your entered password and account password do not match.';
						return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/1');
					}

					/*if email is updated send email */
					if(!empty($posted_password) and md5($posted_password) == $this->layout()->loggedUser->{T_USERS_CONST.'_password'} and $this->layout()->loggedUser->{T_USERS_CONST.'_email'} != $data_to_update[T_USERS_CONST.'_email'] )
					{

						/*$isEmailExist = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$data_to_update[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_id!='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'","fetch");*/

						$resetKey = md5($this->layout()->loggedUser->{T_USERS_CONST.'_id'}."!@#$%^".$this->layout()->loggedUser->{T_USERS_CONST.'_created'}.time());

						$UpdateArr = array(
							T_USERS_CONST.'_email_update' => $data_to_update[T_USERS_CONST.'_email'],
							T_USERS_CONST.'_reset_key' => $resetKey,
						);

						$is_update=$this->SuperModel->Super_Insert(T_USERS,$UpdateArr,T_USERS_CONST."_id='".$this->layout()->loggedUser->{T_USERS_CONST.'_id'}."'");

						if(is_object($is_update) and $is_update->success){
							/* send email to new email holder to verify account email */
							$verifyLink = APPLICATION_URL."/verify-email/".$resetKey;

							$newMailText = '<p style="color:#191919;font-size: 15px;">Please verify your email address via below link: </p><p style="text-align:center"><a href="'.$verifyLink.'" style="display: inline-block; padding: 11px 30px; margin: 20px 0px 30px; font-size: 15px; color: #fff; background: #010101; text-decoration:none;">Verify Email</a></p>';	
			
							$newMailArr = array(
								"user_name" => ucwords(strtolower($this->layout()->loggedUser->{T_USERS_CONST.'_name'})),
								"user_email" => $data_to_update[T_USERS_CONST.'_email'],
								"message" => $newMailText,
								"subject" => "Verify email address"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$newMailArr);



							/* send email to old email holder to notify that email has been changed */
							$user_os        =   getOS();
							$user_browser   =   getBrowser();
							$user_browser_name = '';
							if(isset($user_browser['name']) and !empty($user_browser['name'])){
								$user_browser_name = $user_browser['name'].' (Version: '.$user_browser['version'].')';
							}	

							$ip = $_SERVER['REMOTE_ADDR'];

							$device_details =   "<strong style='color:#191919'>Browser: </strong>".$user_browser_name."<br /><strong style='color:#191919'>IP: </strong>".$ip."";

							$oldMailText = '<p style="color:#191919;font-size: 15px;">Your email has been updated with '.$data_to_update[T_USERS_CONST.'_email'].'. </p><p><strong style="color:#191919;">Device Details: </strong></p><p>'.$device_details.'</p>';	

							$oldMailArr = array(
								"user_name" => ucwords(strtolower($this->layout()->loggedUser->{T_USERS_CONST.'_name'})),
								"user_email" => $this->layout()->loggedUser->{T_USERS_CONST.'_email'},
								"message" => $oldMailText,
								"subject" => "Email has been updated"
							);	

							$isSend = $this->EmailModel->sendEmail('notification_email',$oldMailArr);

							$this->frontSession['successMsg']=$this->layout()->translator->translate("Email verification link has been sent to the updated email address. Please verify to update it.");
							$isMsgUpdate = 1;

							// no need to update actual email right now
							unset($update_data[T_USERS_CONST.'_email']);

						} else {	
							$this->frontSession['errorMsg'] =$this->layout()->translator->translate("check_info_txt");

						}
					}
						
				
					/*if($files['client_image']['name']!=''){
						$target_file = basename($files['client_image']['name']);
						$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
						if($imageFileType == 'png' || $imageFileType == 'jpg' || $imageFileType == 'jpeg'){
							//Uploader code
							$imagePlugin = $this->Image();
							$is_uploaded=$imagePlugin->universal_upload(array("directory"=>PROFILE_IMAGES_PATH,"files_array"=>$files,"url"=>HTTP_PROFILE_IMAGES_PATH,"crop"=>false));	

							if($is_uploaded->success=='1' && $is_uploaded->media_path!=''){
								$$update_data[T_USERS_CONST.'_image'] = $is_uploaded->media_path;

							} elseif($is_uploaded->error=='1' ){
								$this->frontSession['errorMsg']='Invalid Image.';
								$isPostError=1;
							}

						} else {
							$this->frontSession['errorMsg']='Invalid Image.';
							$isPostError=1;
						}

					} else {
						$$update_data[T_USERS_CONST.'_image'] = $this->loggedUser->yurt90w_client_image;
					}*/

					if($isPostError == 0){
						if(isPhoneNumberValid($update_data[T_USERS_CONST.'_phone'])==false){
							$this->frontSession['errorMsg'] = 'Please enter correct telephone number.';

						} else {
							$isUpdate = $this->UserModel->add($update_data,$this->loggedUser->yurt90w_client_id);
							if($isMsgUpdate == 0){
								$this->frontSession['successMsg'] = 'Profile updated successfully.';
							}
							return $this->redirect()->tourl(APPLICATION_URL.'/update-profile/1');
						}
					}
				}

		  } else {
			//prd($PtformType->getMessages());
			$error_msg = $PtformType->getMessages();
			if(isset($error_msg['client_name']['notAlpha']) and !empty($error_msg['client_name']['notAlpha'])){
				$msg = 'Entered full name contains non alphabetic characters.';
				
			} else if(
					(isset($error_msg['client_phone']['stringLengthTooShort']) and !empty($error_msg['client_phone']['stringLengthTooShort'])) or 
					(isset($error_msg['client_phone']['stringLengthTooLong']) and !empty($error_msg['client_phone']['stringLengthTooLong']))
				){
				$msg = 'Telephone number length should be between 10 to 16 numbers.';
			} else {
				$msg = 'Please check entered information again.';
			}
			$this->frontSession['errorMsg'] = $msg;

		  }

		}

		$view->setVariable('form', $form);
		$view->setVariable('form1', $form1);
		$view->setVariable('paymentform', $paymentform);
		$view->setVariable('show', 'front_memberupdateprofile');	
		$view->setVariable('loggedUser', $this->loggedUser);	
		return $view;

	}

}