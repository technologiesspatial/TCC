<?php
namespace AuthAcl\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use AuthAcl\Form\UserForm;
use Zend\Session\Container;

/*use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter*/;

use Zend\Db\Sql\Expression;

class IndexController extends AbstractActionController
{
	private $AbstractModel,$Adapter,$UserModel,$EmailModel,$config_data,$authService;
	
	/* Constructor of index controller */
	public function __construct($AbstractModel,$Adapter,$UserModel,$frontSession,$EmailModel,$config_data,$authService)
	{
		$authService->getAdapter()->setIdentityColumn(new Expression('LOWER('.T_USERS_CONST.'_email)'));
		
    	$this->SuperModel = $AbstractModel;
		$this->Adapter = $Adapter;
		$this->UserModel = $UserModel;		
		$this->frontSession = $frontSession;
		$this->EmailModel = $EmailModel;
		$this->loggedUser = $authService->getIdentity();
		$this->authService = $authService;
		$this->site_configs = $config_data;	
	}
	
	public function makepaymentAction() {
		$status = $this->params()->fromRoute("key");
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$all_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total' =>new Expression('SUM(product_cart_price)'))));
			$shipping_amtdata = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total' =>new Expression('SUM(product_cart_delivery)'))));
		} else {
			$all_cartprice = array_column($_SESSION["cartArr"],'product_cart_price');
			$ship_cartprice = array_column($_SESSION["cartArr"],'product_cart_delivery');
		}
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$joinArr = array(
				'0'=>array('0'=>T_PRODUCTS,'1'=>'product_cart_prodid = product_id','2'=>'Left','3'=>array('product_title','product_isdigital','product_qty','product_status')),
				'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			);
			$my_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		} else {
			$my_carts = $_SESSION["cartArr"];
		}
		if(!empty($my_carts)) {
			foreach($my_carts as $my_carts_key => $my_carts_val) {
				$joinArr = array(
					'0'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Left','3'=>array('store_closed','store_closed_date','store_closed_tilldate','store_acceptorder')),
				);
				$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_title','product_status','product_isdigital'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])),$joinArr);
				if($prod_data["store_closed"] == '1' && $prod_data["store_acceptorder"] == '2') {
					$this->frontSession["errorMsg"]= "Sorry We are closed. You cannot place your order at the moment.";
					return $this->redirect()->tourl(APPLICATION_URL.'/'.$redirectPath);
				}
				$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
				$available_qty = 0;
				if(!empty($colorsize_data)) {
					foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
						$available_qty += $colorsize_data_val["color_qty"];
					}
				}
				if($my_carts_val["product_cart_qty"] > $available_qty && $prod_data["product_isdigital"] != '1') {
					$this->frontSession["errorMsg"]= "Payment failed as ".$prod_data["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
					return $this->redirect()->tourl(APPLICATION_URL.'/'.$redirectPath);
				}
				if($prod_data["product_status"] != '1') {
					$this->frontSession["errorMsg"]= "Payment failed as ".$prod_data["product_title"]." is not available at the moment.";
					return $this->redirect()->tourl(APPLICATION_URL.'/'.$redirectPath);
				}
				$cart_title[] = $my_carts_val["product_title"];
			}
		}
		$cart_title = implode(", ",$cart_title);
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$amount1=$all_carts["total"];
			$amount2 = $shipping_amtdata["total"];
			$amount = round($amount1 + $amount2,2);
		} else {
			$cart_price = array_column($my_carts,"product_cart_price");
			$amount1 = array_sum($cart_price);
			$shipping_price = array_column($my_carts,"product_cart_delivery");
			$amount2 = array_sum($shipping_price);
			$amount = round($amount1 + $amount2,2);
		}
		$_SESSION['total_amount'] = $amount;
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$clientid = $this->loggedUser->{T_CLIENT_VAR."client_id"};
		} else {
			$clientid = $_SESSION["user_Id"];
		}
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$my_carts = $my_carts;
		} else {
			$my_carts = $_SESSION["cartArr"];
		}
		$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array());
		$serial_num = count($all_orders) + 1;
		$unqid = $clientid.time();
		foreach($my_carts as $my_carts_key =>$my_carts_val) {
			$amount=$_SESSION['total_amount'];
			if(!empty($my_carts_val["product_cart_delivery"])) {
				$site_fee = ($this->site_configs["site_commission"] / 100) * ($my_carts_val["product_cart_price"] + $my_carts_val["product_cart_delivery"]);
			} else {
				$site_fee = ($this->site_configs["site_commission"] / 100) * $my_carts_val["product_cart_price"];
			}
			$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$clientid = $this->loggedUser->{T_CLIENT_VAR."client_id"};
			} else {
				$clientid = $_SESSION["user_Id"];
			}
			$orderData = array(
				"order_product" => $my_carts_val["product_cart_prodid"],
				"order_qty" => $my_carts_val["product_cart_qty"],
				"order_baseprice" => ($my_carts_val["product_cart_price"]) / $my_carts_val["product_cart_qty"],
				"order_total" => $my_carts_val['product_cart_price'] + $my_carts_val["product_cart_delivery"],
				"order_clientid" => $clientid,
				"order_date" => date("Y-m-d H:i:s"),
				"order_sitefee" => $site_fee,
				"order_serial" => "51905296".$serial_num,
				"order_discount" => $my_carts_val["product_cart_discount"],
				"order_status" => 5,
				"order_sellerid" => $prod_data["product_clientid"],
				"order_sellerpaid" => 1,
				"order_shipping" => $my_carts_val["product_cart_delivery"],
				"order_color" => $my_carts_val["product_cart_color"],
				"order_size" => $my_carts_val["product_cart_size"],
				"order_address" => $_SESSION["shipping_addr"],
				"order_apartment" => $_SESSION["shipping_apt"],
				"order_city" => $_SESSION["shipping_city"],
				"order_state" => $_SESSION["shipping_state"],
				"order_country" => $_SESSION["shipping_country"],
				"order_zipcode" => $_SESSION["shipping_code"],
				"order_shiprate" => $my_carts_val["product_cart_shiprate"],
				"order_shipname" => $my_carts_val["product_cart_shipname"],
				"order_txnid" => $_REQUEST["tx"],
				"order_note" => $my_carts_val["product_cart_note"],
				"order_unqid" => $unqid
			);
			$this->SuperModel->Super_Insert(T_PRODORDER,$orderData);
			
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_id = '".$my_carts_val["product_cart_id"]."'");	
			} else {
				unset($_SESSION["cartArr"]);
			}
		}
		
		$success_url = SITE_HTTP_URL."/make-payment/1";
		$cancel_url = SITE_HTTP_URL."/make-payment/3";
		$return_url = SITE_HTTP_URL."/make-payment/2";
		$notify_url = SITE_HTTP_URL.'/ipn-hook/'.$unqid;
		switch($status){
			case 1: $MsgType = "successMsg"; $Msg = "Payment successfully done."; $redirectPath = "order-summary"; break;
			case 2: $MsgType = "successMsg"; $Msg = "Payment successfully done."; $redirectPath = "order-summary"; break;
			case 3: $MsgType = "errorMsg"; $Msg =  "Payment cancelled."; $redirectPath = "my-cart"; break;
		}
		
		if($status==1 || $status==2){ /* SUCCESS */ 
			/* NOTIFICATION ENTRY */
			$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$clientid)));
			if(!empty($all_orders)) {
				foreach($all_orders as $all_orders_key => $all_orders_val) {
					$clt_detailz = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$all_orders_val["order_clientid"])));

					$order_detailz = $this->SuperModel->Super_Get(T_PRODORDER,"order_serial =:TID","fetchAll",array('warray'=>array('TID'=>$all_orders_val["order_serial"])));

					if(!empty($order_detailz)) {
						$tab = '<table style="width:100%;margin-top:10px;" cellspacing="0" cellpadding="0"><tr style="background:#000;color:#fff;height:30px;"><th></th><th style="text-align:left">Product Name</th><th style="text-align:left">Quantity</th></tr>';
						foreach($order_detailz as $order_detailz_key => $order_detailz_val) {	
							$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_title','product_photos'),'warray'=>array('PID'=>$order_detailz_val["order_product"])));
							$product_photos = explode(",",$prod_data["product_photos"]); 
							if(!empty($product_photos[0])) { 
								$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$product_photos[0];	
							} 
							$tab .= "<tr><td style='padding-left:10px;padding-top:5px;padding-bottom:5px;'><img src='".$prod_pic."' style='max-width:50px;'></td><td>".$prod_data["product_title"]."</td><td>".$order_detailz_val["order_qty"]."</td></tr>";

						}
						$tab .= '</table>';
					}
					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$message = $this->loggedUser->{T_CLIENT_VAR.'client_name'}." has placed an order with order number ".$all_orders_val["order_serial"].". The details are as follows: <br/><br/>Customer Name: ".$this->loggedUser->{T_CLIENT_VAR."client_name"}."<br/>Shipping address: ".$all_orders_val["order_address"]."<br/>".$all_orders_val["order_shipname"]."<br/>".$tab;

						$client_message = "Your order with order number ".$all_orders_val["order_serial"]." has been placed successfully. The details are as follows: <br/><br/><br/>Shipping address: ".$all_orders_val["order_address"]."<br/>".$all_orders_val["order_shipname"]."<br/>".$tab;

					} else {
						$message = $clt_detailz[T_CLIENT_VAR."client_name"]." has placed an order with order number ".$all_orders_val["order_serial"].". The details are as follows: <br/><br/>Customer Name: ".$clt_detailz[T_CLIENT_VAR."client_name"]."Shipping address: ".$order_detailz["order_address"]."<br/>".$order_detailz["order_shipname"]."<br/>".$tab;

						$client_message = "Your order with order number ".$all_orders_val["order_serial"]." has been placed successfully. The details are as follows: <br/><br/><br/>Shipping address: ".$all_orders_val["order_address"]."<br/>".$all_orders_val["order_shipname"]."<br/>".$tab;
					}

				}
			}
			$mail_const_data4 = array(
				"user_name" => $clt_detailz[T_CLIENT_VAR."client_name"],
				"user_email" => $clt_detailz[T_CLIENT_VAR."client_email"],
				"message" => $client_message,
				"subject" => "Order placed"
			);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data4);

			$mail_const_data3 = array(
					"user_name" => 'Administrator',
					"user_email" => $this->site_configs['site_email'],
					"message" => $message,
					"subject" => "Order placed"
				);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data3);

			$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID","fetchAll",array('group'=>'order_sellerid','warray'=>array('UID'=>$clientid)));
			if(!empty($seller_orders)) {
				foreach($seller_orders as $seller_orders_key => $seller_orders_val) {
					$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$seller_orders_val["order_sellerid"])));

					$clt_detailz = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$seller_orders_val["order_clientid"])));

					$order_detailz = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:TID and order_serial =:SID","fetchAll",array('warray'=>array('SID'=>$seller_orders_val["order_serial"],'TID'=>$seller_orders_val["order_sellerid"])));

					if(!empty($order_detailz)) {
						$tab = '<table style="width:100%;margin-top:10px;" cellspacing="0" cellpadding="0"><tr style="background:#000;color:#fff;height:30px;"><th></th><th style="text-align:left">Product Name</th><th style="text-align:left">Quantity</th></tr>';
						foreach($order_detailz as $order_detailz_key => $order_detailz_val) {	
							$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_title','product_photos'),'warray'=>array('PID'=>$order_detailz_val["order_product"])));
							$product_photos = explode(",",$prod_data["product_photos"]); 
							if(!empty($product_photos[0])) { 
								$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$product_photos[0];	
							} 
							$tab .= "<tr><td style='padding-left:10px;padding-top:5px;padding-bottom:5px;'><img src='".$prod_pic."' style='max-width:50px;'></td><td>".$prod_data["product_title"]."</td><td>".$order_detailz_val["order_qty"]."</td></tr>";

						}
						$tab .= '</table>';
					}
					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$message = $this->loggedUser->{T_CLIENT_VAR.'client_name'}." has placed an order with order number ".$seller_orders_val["order_serial"].". The details are as follows: <br/><br/>Customer Name: ".$this->loggedUser->{T_CLIENT_VAR."client_name"}."<br/>Shipping address: ".$seller_orders_val["order_address"]."<br/>".$seller_orders_val["order_shipname"]."<br/>".$tab;
					} else {
						$message = $clt_detailz[T_CLIENT_VAR."client_name"]." has placed an order with order number ".$seller_orders_val["order_serial"].". The details are as follows: <br/><br/>Customer Name: ".$clt_detailz[T_CLIENT_VAR."client_name"]."Shipping address: ".$seller_orders_val["order_address"]."<br/>".$seller_orders_val["order_shipname"]."<br/>".$tab;
					}
					$mail_const_data2 = array(
						"user_name" => $seller_details[T_CLIENT_VAR.'client_name'],
						"user_email" => $seller_details[T_CLIENT_VAR.'client_email'],
						"message" => $message,
						"subject" => "Order placed"
					);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
				}
			}


			$pending_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$clientid)));
			if(!empty($pending_orders)) {
				foreach($pending_orders as $pending_orders_key => $pending_orders_val) {

					$order_update["order_status"] = 1;
					$this->SuperModel->Super_Insert(T_PRODORDER,$order_update,"order_id = '".$pending_orders_val["order_id"]."'");

					$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$pending_orders_val["order_sellerid"])));
					$clt_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$pending_orders_val["order_clientid"])));

					$order_detailz = $this->SuperModel->Super_Get(T_PRODORDER,"order_id =:TID","fetch",array('warray'=>array('TID'=>$pending_orders_val["order_id"])));

					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_title'),'warray'=>array('PID'=>$pending_orders_val["order_product"])));								

					if($prod_data["product_isdigital"] != '1') {
						$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$pending_orders_val["order_product"],'CID'=>strtolower($pending_orders_val["order_color"]),'SID'=>$pending_orders_val["order_size"])));
						foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
							$available_qty = 0;
							$prodqty = $colorsize_data_val["color_qty"] - $my_carts_val["product_cart_qty"];
							if($prodqty < 1) {
								$avl_data["color_qty"] = 0;
							} else {
								$avl_data["color_qty"] = $prodqty;
							}
							$this->SuperModel->Super_Insert(T_PROQTY,$avl_data,"color_productid = '".$pending_orders_val["order_product"]."' and color_slug = '".strtolower($pending_orders_val["order_color"])."' and color_size = '".$pending_orders_val["order_size"]."'");							
						}
					} else {
						$prod_qty["product_qty"] = $prod_data["product_qty"] - 1;
						$jj = $this->SuperModel->Super_Insert(T_PRODUCTS,$prod_qty,"product_id = '".$pending_orders_val["order_product"]."'");
					}
				}
			}
					
		}
		else if($status==3){  /* CANCELLED */
		    $pending_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$clientid)));
			if(!empty($pending_orders)) {
				foreach($pending_orders as $pending_orders_key => $pending_orders_val) {
					$this->SuperModel->Super_Delete(T_PRODORDER,"order_id = '".$pending_orders_val["order_id"]."'");
				}
			}
			//$this->SuperModel->Super_Delete("user_photo_requests","uphoto_id='".$uphoto_id."'");
		}
		else{
			
			$payVal=1;
			echo "<h1 align='center'>You are redirecting Please wait...</h1>";
			echo '<form action="https://www.paypal.com/cgi-bin/webscr" name="payment_frm" id="payment_frm" method="post">';
			echo '<input type="hidden" name="cmd" value="_xclick">';
			echo '<input name="custom" value="" type="hidden">';
			echo '<input type="hidden" name="upload" value="'.$payVal.'">';
			echo '<input type="hidden" name="business" value="'.$this->site_configs["paypal_email"].'">';
			echo '<input type="hidden" name="amount" value="'.bcdiv($amount,1,2).'">';
			echo '<input type="hidden" name="currency_code" value="'.PRICE_SYMBOL_VALUE.'">';
			echo '<input type="hidden" id="item_name"  name="item_name" value="'.$cart_title.'">';
			echo '<input type="hidden" name="charset" value="utf-8">';
			echo '<input type="hidden" name="rm" value="2" >';
			echo '<input type="hidden" name="notify_url" id="notify_url" value="'.$notify_url.'">';
			echo '<input type="hidden" name="return" id="return" value="'.$return_url.'">';
			echo '<input type="hidden" name="cancel_return" value="'.$cancel_url.'">';
			echo '<input type="submit" value="Paypal" name="btn" style="display:none;" />';
			echo '</form>';
			echo '<script>window.document.payment_frm.submit();</script>';
			exit();
		}
		$this->frontSession[$MsgType]=$Msg;
		return $this->redirect()->tourl(APPLICATION_URL.'/'.$redirectPath);
	}

	/* register-personal-account as user */
	public function registerAction()
	{
		if($this->authService->hasIdentity()) {
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
    	}

		$registerForm = new UserForm($this->layout()->translator);
		$registerForm->user_register();
		$view = new ViewModel();
		$dbAdapter = $this->Adapter;
		//$type = '';
		$request = $this->getRequest();	

		$page_content = $this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>'11')));
    	$view->setVariable('page_content',$page_content);

		if($request->isPost())
		{
			$data = $request->getPost();
			
			$data = decryptPswFields($data); 
			$registerForm->setData($data);
			if($registerForm->isValid())
			{ 
				/* check g-recaptcha-3 */
				$res = post_captcha($data['g-recaptcha-response'], $this->site_configs['recaptcha_secretkey']);
			    if (!$res['success']) {
			        // What happens when the reCAPTCHA is not properly set up
			        // echo 'reCAPTCHA error: Check to make sure your keys match the registered domain and are in the correct locations. You may also want to doublecheck your code for typos or syntax errors.';
			        $this->frontSession['errorMsg'] = $this->layout()->translator->translate("session_expired_txt");

			    } else {
			        // If CAPTCHA is successful...
			        // Paste mail function or whatever else you want to happen here!
			        // echo '<br><p>CAPTCHA was completed successfully!</p><br>';
			        if($registerForm->isValid()) {
						$data = $registerForm->getData();
						if($data["chkbox"] != '1') {
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Please agree with the Collective Coven Privacy Policy & Terms of Use.");
							return $this->redirect()->toUrl(APPLICATION_URL.'/register');
						}
						$data["client_name"] = $data["first_name"].' '.$data["last_name"];
						$data["client_username"] = trim($data["user_name"]);
						if(!preg_match('/^\w{5,}$/', $data["client_username"])) { 
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Please enter username as alphanumeric & minimum 5 characters with first letter as alphabet.");
						} 
						else if(is_numeric(substr($data["client_username"],0,1))) {
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Please enter username as alphanumeric & minimum 5 characters with first letter as alphabet.");
						} else {
						$data["client_email"] = $data["client_email"];
						$data["client_address"] = $data["user_location"];
						$data["client_country"] = $data["user_country"];	
						$data["client_gender"] = $data["user_gender"]; 
					   	unset($data['client_accepted_terms'], /*$data['hiddenRecaptcha'],*/ $data['post_csrf'], $data["first_name"], $data["last_name"], $data["user_name"], $data["email_id"], $data["user_location"], $data["user_gender"], $data["chkbox"],$data["user_country"]);
						$data = GetFormElementsName((array)$data,T_DB_CONST);

						$data[T_USERS_CONST.'_type'] = 'user';
						$data[T_USERS_CONST.'_password'] = md5($data[T_USERS_CONST.'_password']);
						$data[T_USERS_CONST.'_email'] = strtolower($data[T_USERS_CONST.'_email']);								
						$checkEmail = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST.'_email="'.$data[T_USERS_CONST."_email"].'"','fetch');

						if(!empty($checkEmail)){
							$this->frontSession['errorMsg'] = "The email '".$data[T_USERS_CONST."_email"]."' is already registered in our database, click on forgot password link if you have lost your password.";
							return $this->redirect()->toUrl(APPLICATION_URL.'/login');
						}
						$checkName = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST.'_username="'.$data[T_USERS_CONST."_username"].'"','fetch');
						if(!empty($checkName)){
							$this->frontSession['errorMsg'] = "The username '".$data[T_USERS_CONST."_username"]."' is already registered in our database, click on forgot password link if you have lost your password.";
							return $this->redirect()->toUrl(APPLICATION_URL.'/login');
						}

						unset($data[T_USERS_CONST.'_rpassword']);	
						$data["yurt90w_client_birthday"] = date("Y-m-d",strtotime($data["yurt90w_client_birthday"]));
						$data["yurt90w_client_stripe_id"] = '1';	
						$isInsert = $this->UserModel->add($data);

						if($isInsert->success){
							$_SESSION["successmsgt"] = '1';
							return $this->redirect()->toUrl(APPLICATION_URL.'/thank-you/'.myurl_encode($isInsert->inserted_id));

						} else {
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("some_error_occurred_txt");
							return $this->redirect()->toUrl(APPLICATION_URL.'/register');
						}
						}

					} else {			
						$this->frontSession['errorMsg'] = $this->layout()->translator->translate("check_your_information_txt");
					}
			    }

			} else {
				$error_msg = $registerForm->getMessages();
				if(isset($error_msg['client_name']['notAlpha']) and !empty($error_msg['client_name']['notAlpha'])){
					$msg = 'Entered name contains non alphabetic characters.';
				} else {
					$msg = 'Please check information again.';
				}
				$this->frontSession['errorMsg'] = $msg;
			}
			
			
		}

		$this->layout()->setVariable('activePage','signup');		
		$view->setVariable('site_configs',$this->site_configs);
		$view->setVariable('form',$registerForm);
		return $view;
	}
	
	public function orderproductsAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			foreach($posted_data["proorder"] as $proorder_key => $proorder_val) {
				$prod_data["product_order"] = (int) $proorder_key + 1;
				$this->SuperModel->Super_Insert(T_PRODUCTS,$prod_data,"product_id = '".$proorder_val."'");
			}
			echo "success";
		}
		exit();
	}
	
	public function exportinvoiceAction() {
		$invoice_id = $this->params()->fromRoute('key');
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_shippingid','product_isdigital')),
			'1'=>array('0'=>T_CLIENTS,'1'=>'order_clientid = yurt90w_client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name'))
		);
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$user_id = $this->loggedUser->{T_CLIENT_VAR."client_id"};
		} else {
			$user_id = $_SESSION["user_Id"];
		}
		$user_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_postcode"),'warray'=>array('UID'=>$user_id)));
		$invoices_data = $this->SuperModel->Super_Get(T_PRODORDER,"(order_sellerid =:UID || order_clientid =:UID) and order_serial =:TID","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$user_id,'TID'=>$invoice_id)),$joinArr);
		require_once ROOT_PATH . '/public/plugins/MPDF/autoload.php';
        $mpdf = new \Mpdf\Mpdf();
		$setheader = '';
		$setheader  .= '<htmlpageheader name="MyHeader1"></htmlpageheader><htmlpageheader name="MyHeader2">';
		$setheader .='<div class="righttext"><img src="http://thecollectivecoven.com/images/mini-logo.png" style="max-width:100px" /></div></htmlpageheader>'; 
		$setheader .=' <htmlpagefooter name="MyFooter1"><div class="header1" style=" "><div class="text-center">Email '.$this->SITE_CONFIG['site_email'].' </div></div></htmlpagefooter>'; 
		$setheader .='<htmlpagefooter name="MyFooter2"><div class="header2" style=""><div class="text-center">REGISTERED OFFICE</div><div class="text-center">Attention: '.$this->SITE_CONFIG['site_address'].'</div></div></htmlpagefooter>';
		$start_record = reset($invoices_data);
		if($start_record["order_shiprate"] == '1') { $ship_type = "Standard"; } else { $ship_type = "International"; }
		$invoice_data = $this->SuperModel->Super_Get(T_PAGES,'page_id = 18','fetch');
		$pdfhtml= '';
		$pdfhtml .= $setheader;
		$pdfhtml .= $invoice_data['page_content_en'];	
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$start_record["order_sellerid"])));
		if(!empty($invoices_data)) {
			$tablerow = '';
			$total_amount = 0;
			$tablerow .='<table class="table table_content description_div" style="width:100%;font-size:12px;"><thead style="background:#000;color:#fff"><tr style="background:#000;color:#fff"><th style="text-align:left;font-size:12px;color:#fff">Item Ordered</th><th style="text-align:left;font-size:12px;color:#fff">Product Name</th><th style="text-align:left;font-size:12px;color:#fff">Quantity</th><th style="text-align:left;font-size:12px;color:#fff">Amount</th></tr></thead><tbody>';
			foreach($invoices_data as $key => $value){
				$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_postcode"),'warray'=>array('UID'=>$value["order_clientid"])));
				$total_amount += $value["order_total"];
				$shipping_amount += $value["order_shipping"];
				$total_discount += $value["order_discount"];
				if(!empty($value["product_defaultpic"])) { 
					$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$value["product_defaultpic"];		  
				} else {
					$product_photos = explode(",",$value["product_photos"]); 
					if(!empty($product_photos[0])) { 
						$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$product_photos[0];	
					} 
				}
				
				$product_price = $value["order_baseprice"] * $value["order_qty"];
				$subtotal += $product_price;
				$colordata = ''; $sizedata = '';
				$tablerow .= '<tr style="margin-bottom:20px;">';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;"><img src="'.$prod_pic.'" style="max-width:200px;"></td>';
				if(!empty($value["order_color"])) {
					$colorVal = $value['order_color'];
                	$colordata = '<span>Color:</span><span>'.$colorVal.'</span>'; 
                } 
				if(!empty($value["order_size"])) { 
					$sizedata = '<br/><span class="size"> Size: </span><span>'.$value['order_size'].'</span>';
				}				
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;"><b>'.$value["product_title"].'</b><div class="color-and-size">'.$colordata.$sizedata.'</div>'.'</td>';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;">'.$value['order_qty'].'</td>';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;">$'.$product_price.'</td>';
				$tablerow .= '</tr>';
			}			
			$tablerow .= '<tr><td></td><td></td><td>Subtotal</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($subtotal,1,2).'</b></td></tr>';	
			$tablerow .= '<tr><td></td><td></td><td>Discount</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($total_discount,1,2).'</b></td></tr>';
			$tablerow .= '<tr><td></td><td></td><td>Shipping Charges</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($shipping_amount,1,2).'</b></td></tr>';
			$tablerow .= '<tr><td></td><td></td><td>Order Total</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($total_amount,1,2).'</b></td></tr></tbody></table>';
		}
		if(!empty($start_record["order_zipcode"])) {
			$zip_code = $start_record["order_zipcode"];
		} else {
			$zip_code = $client_details[T_CLIENT_VAR."client_postcode"];
		}
		$apartment = ''; $city = ''; $state = ''; $country = '';
		if(!empty($start_record["order_apartment"])) {
			$apartment = $start_record["order_apartment"].', ';
		}
		if(!empty($start_record["order_city"])) {
			$city = $start_record["order_city"];
		}
		if(!empty($start_record["order_state"])) {
			$state = $start_record["order_state"].', ';
		}
		if(!empty($start_record["order_country"])) {
			$country = $start_record["order_country"];
		}
		$invoice_data['page_content_en']=str_ireplace(array('{store_name}','{ship_to}','{order_id}','{order_date}','{buyer_details}','{shipping_profile}','{item_count}','{items}'),array($store_data["store_name"],$apartment.' '.$start_record["order_address"].', '.$city.'<br/>'.$state.$country.'<br/>Zip Code: '.$zip_code,$invoice_id,date("m-d-Y",strtotime($start_record["order_date"])),$start_record[T_CLIENT_VAR."client_name"],$start_record["order_shipname"].' ('.$ship_type.') ',count($invoices_data),$tablerow),$pdfhtml);
		$mpdf->useOddEven = true;
		$mpdf->AddPageByArray([
			'margin-left' => 0,
			'margin-right' => 0,
			'margin-top' => 0,
			'margin-bottom' => 0,
		]);
		$mpdf->setAutoTopMargin = 'stretch';
		$mpdf->setAutoBottomMargin = 'stretch';		
		$mpdf->SetHTMLFooter('<div style="text-align:center;margin-top:20px;">WWW.THECOLLECTIVECOVEN.COM</div>');
		//$mpdf->SetHeader('The Collective Coven');
		$filenamepdf="invoicepdf_".$invoice_id.".pdf";	
		$filenamepath=INVOICE_PATH.'/'.$filenamepdf;
		$stylesheet = file_get_contents(FRONT_CSS.'/design-new.css?date='.rand());
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($invoice_data['page_content_en'],2);
		$mpdf->Output($filenamepdf,"D");
		return $this->redirect()->tourl(APPLICATION_URL.'/seller-orders');
	}
	
	public function pricerangeAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$price_ranges = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID","fetchAll",array('warray'=>array('TID'=>myurl_decode($data["tid"]))));
			$view = new ViewModel();
			$view->setVariable('price_ranges',$price_ranges);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function ordersummaryAction() {
		$_SESSION["logstat"] = '2';
		$view = new ViewModel();
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_shippingid','product_digital','product_isdigital')),
			'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
			'2'=>array('0'=>T_SHIPPROFILES,'1'=>'product_shippingid = shipping_id','2'=>'Left','3'=>array('shipping_time')),
		);
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {	
			$customer_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_clientid =:UID and order_status != 5","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		} else {
			$customer_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_clientid =:UID and order_status != 5","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$_SESSION["user_Id"])),$joinArr);
		}
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
	
	public function generateinvoiceAction() {
		$invoice_id = $this->params()->fromRoute('key');
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_shippingid','product_isdigital')),
			'1'=>array('0'=>T_CLIENTS,'1'=>'order_clientid = yurt90w_client_id','2'=>'Left','3'=>array(T_CLIENT_VAR.'client_name'))
		);
		$invoices_data = $this->SuperModel->Super_Get(T_PRODORDER,"(order_sellerid =:UID || order_clientid =:UID) and order_serial =:TID","fetchAll",array('order'=>'order_id desc','warray'=>array('UID'=>$_SESSION["user_Id"],'TID'=>$invoice_id)),$joinArr);
		require_once ROOT_PATH . '/public/plugins/MPDF/autoload.php';
        $mpdf = new \Mpdf\Mpdf();
		$setheader = '';
		$setheader  .= '<htmlpageheader name="MyHeader1"></htmlpageheader><htmlpageheader name="MyHeader2">';
		$setheader .='<div class="righttext"><img src="http://thecollectivecoven.com/images/mini-logo.png" style="max-width:100px" /></div></htmlpageheader>'; 
		$setheader .=' <htmlpagefooter name="MyFooter1"><div class="header1" style=" "><div class="text-center">Email '.$this->SITE_CONFIG['site_email'].' </div></div></htmlpagefooter>'; 
		$setheader .='<htmlpagefooter name="MyFooter2"><div class="header2" style=""><div class="text-center">REGISTERED OFFICE</div><div class="text-center">Attention: '.$this->SITE_CONFIG['site_address'].'</div></div></htmlpagefooter>';
		$start_record = reset($invoices_data);
		if($start_record["order_shiprate"] == '1') { $ship_type = "Standard"; } else { $ship_type = "International"; }
		$invoice_data = $this->SuperModel->Super_Get(T_PAGES,'page_id = 18','fetch');
		$pdfhtml= '';
		$pdfhtml .= $setheader;
		$pdfhtml .= $invoice_data['page_content_en'];	
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('warray'=>array('UID'=>$start_record["order_sellerid"])));
		if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
			$user_id = $this->loggedUser->{T_CLIENT_VAR."client_id"};
		} else {
			$user_id = $_SESSION["user_Id"];
		}
		$user_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_postcode"),'warray'=>array('UID'=>$user_id)));
		if(!empty($invoices_data)) {
			$tablerow = '';
			$total_amount = 0;
			$tablerow .='<table class="table table_content description_div" style="width:100%;font-size:12px;"><thead style="background:#000;color:#fff"><tr style="background:#000;color:#fff"><th style="text-align:left;font-size:12px;color:#fff">Item Ordered</th><th style="text-align:left;font-size:12px;color:#fff">Product Name</th><th style="text-align:left;font-size:12px;color:#fff">Quantity</th><th style="text-align:left;font-size:12px;color:#fff">Amount</th></tr></thead><tbody>';
			foreach($invoices_data as $key => $value){
				$total_amount += $value["order_total"];
				$shipping_amount += $value["order_shipping"];
				$total_discount += $value["order_discount"];
				if(!empty($value["product_defaultpic"])) { 
					$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$value["product_defaultpic"];		  
				} else {
					$product_photos = explode(",",$value["product_photos"]); 
					if(!empty($product_photos[0])) { 
						$prod_pic = HTTP_PRODUCT_PIC_PATH.'/60/'.$product_photos[0];	
					} 
				} 		
				$product_price = $value["order_baseprice"] * $value["order_qty"];
				$subtotal += $product_price;
				$colordata = ''; $sizedata = '';
				$tablerow .= '<tr style="margin-bottom:20px;">';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;"><img src="'.$prod_pic.'" style="max-width:200px;"></td>';
				if(!empty($value["order_color"])) {
					$colorVal = $value['order_color'];
                	$colordata = '<span>Color:</span><span>'.$colorVal.'</span>'; 
                } 
				if(!empty($value["order_size"])) { 
					$sizedata = '<br/><span class="size"> Size: </span><span>'.$value['order_size'].'</span>';
				}				
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;"><b>'.$value["product_title"].'</b><div class="color-and-size">'.$colordata.$sizedata.'</div>'.'</td>';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;">'.$value['order_qty'].'</td>';
				$tablerow .= '<td style="vertical-align:top;padding-bottom:10px;">$'.$product_price.'</td>';
				$tablerow .= '</tr>';
			}			
			$tablerow .= '<tr><td></td><td></td><td>Subtotal</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($subtotal,1,2).'</b></td></tr>';	
			$tablerow .= '<tr><td></td><td></td><td>Discount</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($total_discount,1,2).'</b></td></tr>';
			$tablerow .= '<tr><td></td><td></td><td>Shipping Charges</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($shipping_amount,1,2).'</b></td></tr>';
			$tablerow .= '<tr><td></td><td></td><td>Order Total</td><td class="righttext" style="text-align:left;"><b>'."$".bcdiv($total_amount,1,2).'</b></td></tr></tbody></table>';
		}	
		$invoice_data['page_content_en']=str_ireplace(array('{store_name}','{ship_to}','{order_id}','{order_date}','{buyer_details}','{shipping_profile}','{item_count}','{items}'),array($store_data["store_name"],$start_record["order_address"].' <br/>Zip Code: '.$user_details[T_CLIENT_VAR."client_postcode"],$invoice_id,date("m-d-Y",strtotime($start_record["order_date"])),$start_record[T_CLIENT_VAR."client_name"],$start_record["order_shipname"].' ('.$ship_type.') ',count($invoices_data),$tablerow),$pdfhtml);
		$mpdf->useOddEven = true;
		$mpdf->AddPageByArray([
			'margin-left' => 0,
			'margin-right' => 0,
			'margin-top' => 0,
			'margin-bottom' => 0,
		]);
		//$mpdf->SetHeader('The Collective Coven');
		$mpdf->setAutoTopMargin = 'stretch';
		$mpdf->setAutoBottomMargin = 'stretch';		
		$mpdf->SetHTMLFooter('<div style="text-align:center;margin-top:20px;">WWW.THECOLLECTIVECOVEN.COM</div>');
		$filenamepdf="invoicepdf_".$invoice_id.".pdf";	
		$filenamepath=INVOICE_PATH.'/'.$filenamepdf;
		$stylesheet = file_get_contents(FRONT_CSS.'/design-new.css?date='.rand());
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($invoice_data['page_content_en'],2);
		$mpdf->Output($filenamepdf,"D");
		return $this->redirect()->tourl(APPLICATION_URL.'/order-summary');
	}
	
	public function digitaldownloadAction() {
		$digital_id = $this->params()->fromRoute('key');
		$joinArr = array(
			'0'=>array('0'=>T_PRODUCTS,'1'=>'order_product = product_id','2'=>'Left','3'=>array('product_digital')),
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_product =:PID and order_clientid =:UID","fetch",array('warray'=>array('PID'=>base64_decode($digital_id),'UID'=>$_SESSION["user_Id"])),$joinArr);
		if(empty($product_data)) {
			$this->frontSession['errorMsg'] = "You cannot access this product.";
			return $this->redirect()->tourl(APPLICATION_URL);
		}
		$file_url = HTTP_DIGITAL_PATH.'/'.$product_data["product_digital"];
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
		readfile($file_url);
		return $this->redirect()->tourl(APPLICATION_URL.'/order-summary');
	}

	/* register-as-a-member as member */
	public function registermemberAction()
	{
		if($this->authService->hasIdentity()) {
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
    	}

		$registerForm = new UserForm($this->layout()->translator);
		$registerForm->member_register();
		$view = new ViewModel();
		$dbAdapter = $this->Adapter;
		//$type = '';
		$request = $this->getRequest();

		$page_content = $this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>'12')));
    	$view->setVariable('page_content',$page_content);

		if($request->isPost())
		{
			$data = $request->getPost();
			$data = decryptPswFields($data);
			$registerForm->setData($data);

			if($registerForm->isValid())
			{
				/* check g-recaptcha-3 */
				$res = post_captcha($data['g-recaptcha-response'], $this->site_configs['recaptcha_secretkey']);
			    if (!$res['success']) {
			        // What happens when the reCAPTCHA is not properly set up
			        // echo 'reCAPTCHA error: Check to make sure your keys match the registered domain and are in the correct locations. You may also want to doublecheck your code for typos or syntax errors.';
			        $this->frontSession['errorMsg'] = $this->layout()->translator->translate("session_expired_txt");

			    } else {
			        // If CAPTCHA is successful...
			        // Paste mail function or whatever else you want to happen here!
			        // echo '<br><p>CAPTCHA was completed successfully!</p><br>';
			        if($registerForm->isValid()) {
						$data = $registerForm->getData();
					   	unset($data['client_accepted_terms'], /*$data['hiddenRecaptcha'],*/ $data['post_csrf']);
						$data = GetFormElementsName((array)$data,T_DB_CONST);

						$data[T_USERS_CONST.'_type'] = 'member';
						$data[T_USERS_CONST.'_password'] = md5($data[T_USERS_CONST.'_password']);
						$data[T_USERS_CONST.'_email'] = strtolower($data[T_USERS_CONST.'_email']);

						$checkEmail = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST.'_email="'.$data[T_USERS_CONST."_email"].'"','fetch');

						if(!empty($checkEmail)){
							$this->frontSession['errorMsg'] = "The email '".$data[T_USERS_CONST."_email"]."' is already registered in our database, click on forgot password link if you have lost your password.";
							return $this->redirect()->toUrl(APPLICATION_URL.'/login');
						}

						unset($data[T_USERS_CONST.'_rpassword']);


						if(isPhoneNumberValid($data[T_USERS_CONST.'_phone'])==false){
							$this->frontSession['errorMsg'] = 'Please enter correct telephone number.';

						} else {

							$isInsert = $this->UserModel->add($data);
							if($isInsert->success){
								return $this->redirect()->toUrl(APPLICATION_URL.'/thank-you/'.myurl_encode($isInsert->inserted_id));

							} else {
								$this->frontSession['errorMsg'] = $this->layout()->translator->translate("some_error_occurred_txt");
								return $this->redirect()->toUrl(APPLICATION_URL.'/register-as-a-member');
							}
						}

					} else {				 
						$this->frontSession['errorMsg'] = $this->layout()->translator->translate("check_your_information_txt");
					}
			    }
			
			} 
			else {
				$error_msg = $registerForm->getMessages();
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

		$this->layout()->setVariable('activePage','signup');		
		$view->setVariable('site_configs',$this->site_configs);
		$view->setVariable('form',$registerForm);
		return $view;
	}
	
	/* User / Member login */
	public function userloginAction()
	{
		if($this->authService->hasIdentity()) {
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		} 
		$form = new UserForm($this->layout()->translator);
		$form->login();
		$messageError = null;
		$request = $this->getRequest();
		$redirect_url = explode("url=%2F",$_SERVER["QUERY_STRING"]);
		if($request->isPost()){
			$data = $request->getPost();
			$data = decryptPswFields($data);
			$form->setData($data);

			/* check g-recaptcha-3 */
			$res = post_captcha($data['g-recaptcha-response'], $this->site_configs['recaptcha_secretkey']);
		    if (!$res['success']) {
		        // What happens when the reCAPTCHA is not properly set up
		        // echo 'reCAPTCHA error: Check to make sure your keys match the registered domain and are in the correct locations. You may also want to doublecheck your code for typos or syntax errors.';
		        $this->frontSession['errorMsg'] = $this->layout()->translator->translate("session_expired_txt");
		        return $this->redirect()->tourl(APPLICATION_URL.'/login');

		    } else {
		        // If CAPTCHA is successful...
		        // Paste mail function or whatever else you want to happen here!
		        // echo '<br><p>CAPTCHA was completed successfully!</p><br>';

				if($form->isValid()) 
				{
					$data = $form->getData();
					unset($data['client_accepted_terms'], /*$data['hiddenRecaptcha'],*/ $data['post_csrf']);
					$data = GetFormElementsName((array)$data,T_DB_CONST);
					if (!filter_var($data[T_USERS_CONST.'_email'], FILTER_VALIDATE_EMAIL)) {
						$user_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_username =:UID","fetch",array('fields'=>T_CLIENT_VAR.'client_email','warray'=>array('UID'=>$data[T_CLIENT_VAR.'client_email'])));
					}
					$authAdapter = $this->authService->getAdapter();
					if (!filter_var($data[T_USERS_CONST.'_email'], FILTER_VALIDATE_EMAIL)) {
						if(!empty($user_details[T_CLIENT_VAR.'client_email'])) {
							$authAdapter->setIdentity(strtolower($user_details[T_CLIENT_VAR.'client_email']));
						} else {
							$authAdapter->setIdentity(strtolower($data[T_USERS_CONST.'_email']));
						}
					} else {
						$authAdapter->setIdentity(strtolower($data[T_USERS_CONST.'_email']));
					}
					$authAdapter->setCredential(md5($data[T_USERS_CONST.'_password']));
					$result = $this->authService->authenticate();	
					if($result->isValid()){
						$data = $authAdapter->getResultRowObject();
						$LoginData = $data;
						$data = (array)$data;
						
						if($data[T_USERS_CONST.'_type'] != 'admin'){
							if($data[T_USERS_CONST.'_email_verified'] == '0'){

								$this->UserModel->sendVerificationEmail($data[T_USERS_CONST.'_email']);

								$this->authService->clearIdentity();
								$this->frontSession['successMsg'] = $this->layout()->translator->translate("verify_email_resent_txt");
								return $this->redirect()->tourl(APPLICATION_URL.'/login');
							
							} else if($data[T_USERS_CONST.'_status'] == '0'){
								$this->authService->clearIdentity();
								$this->frontSession['errorMsg'] = $this->layout()->translator->translate("account_block_txt");
								return $this->redirect()->tourl(APPLICATION_URL.'/login');
							
							} else {
								$this->authService->getStorage()->write($LoginData);
								$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and store_approval = '1'","fetch",array('warray'=>array('UID'=>$LoginData->yurt90w_client_id)));
								$_SESSION["logstat"] = '2';
								if(!empty($_SESSION["cartArr"])) {
									foreach($_SESSION["cartArr"] as $cart_key => $cart_val) {
										$product_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>'product_clientid','warray'=>array('PID'=>$cart_val["product_cart_prodid"])));
										if($LoginData->yurt90w_client_id != $product_details["product_clientid"]) {
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
											$cart_data["product_cart_clientid"] = $LoginData->yurt90w_client_id;
											$this->SuperModel->Super_Insert(T_PRODCART,$cart_data);
										}
									}
									return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
								}								
								if($store_data["store_approval"] == '1') {
									return $this->redirect()->tourl(APPLICATION_URL.'/profile');
								} else {
								if(isset($_GET['url']) && !empty($_GET['url'])){  
									//return $this->redirect()->toUrl(APPLICATION_URL.$_GET['url']);
									return $this->redirect()->tourl(APPLICATION_URL.'/profile');
								} else {
									return $this->redirect()->tourl(APPLICATION_URL.'/profile');
								} }
							}

						} else {
							$this->authService->clearIdentity();
							$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_login_txt");
							return $this->redirect()->tourl(APPLICATION_URL.'/login');
						}

					} else {
						$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_login_txt");
						//return $this->redirect()->tourl(APPLICATION_URL.'/login');
					}
				
				} else {
					$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_login_txt");
				}

			}
			
		}
		if(!empty($redirect_url[1])) {
			$redUrl = APPLICATION_URL.'/login?url=%2F'.$redirect_url[1];
		} else {
			$redUrl = APPLICATION_URL.'/login';
		}
		$this->layout()->setVariable('activePage','login');

		return new ViewModel(array(
			'form'			=>	$form,
			'messageError'	=>	$messageError,
			'pageHeading'	=>	"Log In",
			'site_configs'	=>	$this->site_configs,
			'redUrl' => $redUrl		
		));

    }


	/* User / Member logout */	
	public function logoutAction()
	{
		if ($this->loggedUser->{T_CLIENT_VAR . 'client_signup_type'} == 'social') {
			 if (!empty($_SESSION["fb_accessToken"])) {
				 unset($_SESSION['fb_accessToken']);
			 } else {
			 	if (isset($_SESSION['DEFAULT_AUTH_Demo']->gtoken)) {
                    unset($_SESSION['DEFAULT_AUTH_Demo']->gtoken);
                }
                if (isset($_SESSION['DEFAULT_AUTH_Demo']->loggedUser)) {
                    unset($_SESSION['DEFAULT_AUTH_Demo']->loggedUser);
                }
				unset($_SESSION['gtoken']);
			 }
		}
		$this->authService->clearIdentity();
		
		session_destroy();
		unset($_SESSION);
		//$this->frontSession['successMsg']='You Are Now Logged Out.';		
        return $this->redirect()->tourl(APPLICATION_URL.'/login');
    }

    /* change email id after login then verify new email id link */
    public function verifiedemailAction()
	{ 
		$key = $this->params()->fromRoute('key'); 

		if($key==''){
			return $this->redirect()->toUrl(APPLICATION_URL);
		}

		$user_info = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_reset_key = :KEY and ".T_CLIENT_VAR."client_type!='admin'",'fetch',array("warray"=>array("KEY"=>$key)));
		if(empty($user_info)){
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_account_try_again_txt");
			return $this->redirect()->toUrl(APPLICATION_URL);				
		}

		$data_to_update = array();
		$data_to_update[T_CLIENT_VAR.'client_email'] = $user_info[T_CLIENT_VAR.'client_email_update'];
		$data_to_update[T_CLIENT_VAR.'client_reset_key'] = NULL;
		$data_to_update[T_CLIENT_VAR.'client_email_update'] = NULL;

		$user_update = $this->SuperModel->Super_Insert(T_CLIENTS,$data_to_update,T_CLIENT_VAR."client_id= '".$user_info[T_CLIENT_VAR.'client_id']."'");

		/*$session = new Container(ADMIN_AUTH_NAMESPACE);		
		if(isset($session['adminData']) && !empty($session['adminData'])){

			$admin_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$session['adminData'][T_CLIENT_VAR.'client_id']."'","fetch");
			$session = new Container(ADMIN_AUTH_NAMESPACE);
			$session['adminData']=$admin_data;	
		}*/
		$this->authService->clearIdentity();
		$this->frontSession['successMsg'] = $this->layout()->translator->translate("Email address is verified");	
		return $this->redirect()->toUrl(APPLICATION_URL);	
	}

	
    /* Forgot Password then Reset Password */
	public function userresetpasswordAction()
	{
		if($this->authService->hasIdentity()) {
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
        }

		$key = $this->params()->fromRoute('key');
		$view = new ViewModel();
		$resetForm = new UserForm($this->layout()->translator);
		$resetForm->resetpassword();
		$request = $this->getRequest();
		if(empty($key)){
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_req_txt");	
			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}

		$user_data = $this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_reset_key=:resetkey","fetch",array("warray"=>array("resetkey"=>$key)));

		if(!$user_data){			
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_req_reset_txt");
			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}

		if ($request->isPost())
		{ 
			$data = $request->getPost();
			$data = decryptPswFields($data);
			$resetForm->setData($data);

			/* check g-recaptcha-3 */
			$res = post_captcha($data['g-recaptcha-response'], $this->site_configs['recaptcha_secretkey']);
		    if (!$res['success']) {
		        // What happens when the reCAPTCHA is not properly set up
		        // echo 'reCAPTCHA error: Check to make sure your keys match the registered domain and are in the correct locations. You may also want to doublecheck your code for typos or syntax errors.';
		        $this->frontSession['errorMsg'] = $this->layout()->translator->translate("session_expired_txt");

		    } else {
		        // If CAPTCHA is successful...
		        // Paste mail function or whatever else you want to happen here!
		        // echo '<br><p>CAPTCHA was completed successfully!</p><br>';

				if($resetForm->isValid())
				{
					$data_to_update = $resetForm->getData();
					unset($data_to_update['hiddenRecaptcha']);
					unset($data_to_update['post_csrf']);
					$data_to_update=GetFormElementsName((array)$data_to_update,T_DB_CONST);
					
					if($data_to_update[T_USERS_CONST.'_password'] == $data_to_update[T_USERS_CONST.'_rpassword']){
						unset($data_to_update[T_USERS_CONST.'_rpassword']);

						$userDetails = $this->UserModel->resetPassword($data_to_update, $user_data);	
						$this->frontSession['successMsg'] = $this->layout()->translator->translate("password_changed_txt");	
						return $this->redirect()->toUrl(APPLICATION_URL.'/login');

					} else {
						$this->frontSession['successMsg'] = $this->layout()->translator->translate("mismatch_txt");
						return $this->redirect()->toUrl(APPLICATION_URL.'reset-password/'.$key);
					}
				}
				
			}
		}

		$page_content = array('page_title'=>'Reset Password');
		$view->setVariable('page_content',$page_content);
		$this->layout()->setVariable('pageHeading','Reset Password');
	 	$view->setVariable('resetForm', $resetForm);
		$view->setVariable('pageHeading','Reset Password');
		$view->setVariable('site_configs',$this->site_configs);
		$view->setVariable('key',$key);
		return $view;
	}

	

	/* 	Ajax Call For Checking the Old Password for the Logged User  */

	public function frcheckpasswordAction()

	{

		if(isset($this->loggedUser) && !empty($this->loggedUser)){

		$password = $this->params()->fromQuery('client_old_password'); 

		$request = $this->getRequest(); 

		if (!$request->isXmlHttpRequest() ) {

			return $this->redirect()->tourl(APPLICATION_URL);

		}

		$loggedUserData=(array)$this->loggedUser;

		$pwdWhere=T_USERS_CONST."_password=:userpassword and ".T_USERS_CONST."_id=:userid";

		

		

		$user_info =$this->SuperModel->Super_Get(T_USERS,$pwdWhere,'fetch',array('fields'=>array(T_USERS_CONST.'_id'),'warray'=>array("userpassword"=>md5($password),"userid"=>$loggedUserData[T_USERS_CONST."_id"])));

		

		if(!$user_info)

			echo json_encode("Wrong Password , Please enter your previous password");

		else

			echo json_encode("true");

		}else{

		echo json_encode($this->view->translate("Please login to make changes."));

		}

		exit();

		

	}

	

	public function frchecknewpasswordAction()

	{

		

		$request = $this->getRequest(); 

		if (!$request->isXmlHttpRequest() ) {

			return $this->redirect()->tourl(APPLICATION_URL);

		}

		$loggedUserData=(array)$this->loggedUser;

		$password = $this->params()->fromQuery('client_password');

		

		$pwdWhere=T_USERS_CONST."_password=:userpassword and ".T_USERS_CONST."_id=:userid";

		/*$pwdWhere=T_USERS_CONST."_password='".md5($password)."' and ".T_USERS_CONST."_id=".$loggedUserData[T_USERS_CONST."_id"];*/

		

		$user_info =$this->SuperModel->Super_Get(T_USERS,$pwdWhere,'fetch',array('fields'=>array(T_USERS_CONST.'_id'),'warray'=>array("userpassword"=>md5($password),"userid"=>$loggedUserData[T_USERS_CONST."_id"])));

		

		if($user_info)

			echo json_encode("New password should be different from old password");

		else

			echo json_encode("true");

		exit();

	}

	
	/* User / Member - Forgot Password */
	public function userforgotpasswordAction()
	{
		if($this->authService->hasIdentity()) {
			return $this->redirect()->tourl(APPLICATION_URL.'/profile');
		}

		$form = new UserForm($this->layout()->translator);
		$form->forgotpassword();
		
		$view = new ViewModel();
		$request = $this->getRequest();
		
		if($request->isPost())
		{
			$data = $request->getPost();
			$form->setData($data);

			/* check g-recaptcha-3 */
			$res = post_captcha($data['g-recaptcha-response'], $this->site_configs['recaptcha_secretkey']);
		    if (!$res['success']) {
		        // What happens when the reCAPTCHA is not properly set up
		        // echo 'reCAPTCHA error: Check to make sure your keys match the registered domain and are in the correct locations. You may also want to doublecheck your code for typos or syntax errors.';
		        $this->frontSession['errorMsg'] = $this->layout()->translator->translate("session_expired_txt");

		    } else {
		        // If CAPTCHA is successful...
		        // Paste mail function or whatever else you want to happen here!
		        // echo '<br><p>CAPTCHA was completed successfully!</p><br>';

				if($form->isValid()) {
					$data = $form->getData();
					unset($data["hiddenRecaptcha"],$data["post_csrf"]);

					//	unset($data['submit']);
					$data = GetFormElementsName($data,T_DB_CONST);
					$client_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email =:TID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_email_verified'),'warray'=>array('TID'=>trim(strip_tags(strtolower($data[T_USERS_CONST.'_email']))))));
					$userDetails = $this->UserModel->forgotPassword($data);
					/*if($client_data[T_CLIENT_VAR.'client_email_verified'] == '0') {
						$this->UserModel->sendVerificationEmail(strtolower(trim($data[T_USERS_CONST.'_email'])));
					}*/
					if($userDetails==1){
						$this->frontSession['successMsg'] = $this->layout()->translator->translate("forget_email_txt");
						return $this->redirect()->toUrl(APPLICATION_URL.'/login');

					} else {
						$this->frontSession['successMsg'] = $userDetails['message'];
						return $this->redirect()->toUrl(APPLICATION_URL.'/forgot-password');
					}

				} else {
					$this->frontSession['errorMsg'] = $this->layout()->translator->translate("check_info_txt");
					return $this->redirect()->toUrl(APPLICATION_URL.'/forgot-password');
				}
			}
		}

		$this->layout()->setVariable('activePage','forgotpassword');

		$view->setVariable('form', $form);
		$view->setVariable('pageHeading','Forgot Password');
		$view->setVariable('site_configs',$this->site_configs);
		
		return $view;
	}

	public function thankyouAction(){

		if($this->authService->hasIdentity()) {

			return $this->redirect()->tourl(APPLICATION_URL.'/profile');

		}

		$user = $this->params()->fromRoute('user'); 

		if($user==''){$this->redirect()->tourl(APPLICATION_URL);}

		$user=myurl_decode($user);

		

		$isUserData=$this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id='".$user."' and ".T_USERS_CONST."_isthanku='0' and ".T_USERS_CONST."_type!='admin'","fetch");

		if(empty($isUserData)){

			$this->redirect()->tourl(APPLICATION_URL);

		}

		$this->SuperModel->Super_Insert(T_USERS,array(T_USERS_CONST."_isthanku"=>"1"),T_USERS_CONST."_id='".$user."' and ".T_USERS_CONST."_isthanku='0' and ".T_USERS_CONST."_type!='admin'");

		$homepageData = $this->SuperModel->Super_Get('pages','page_slug="thank-you"','fetch');

		$homepageData['page_content']=str_ireplace(array('{site_link}'),array(APPLICATION_URL),$homepageData['page_content']);

			return new ViewModel(array(

			

			'homepageData'=>$homepageData

			

		));

	}

	

	
	/* Verify email and activate account after registration */
	public function activateAction()
	{
		$key = $this->params()->fromRoute('key'); 

		if($key == ''){
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("invalid_account_try_again_txt");	
			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}

		$activateWhere = T_USERS_CONST."_activation_key = :activationKey";
		$user_info = $this->SuperModel->Super_Get(T_USERS,$activateWhere,'fetch',array("warray"=>array("activationKey"=>$key)));

		if(empty($user_info)){
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("account_already_activated_txt");
			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}

		$data_to_update = array();
		$data_to_update[T_USERS_CONST.'_activation_key'] = '';
		//$data_to_update[T_USERS_CONST.'_reset_status'] = '0';
		$data_to_update[T_USERS_CONST.'_email_verified'] = '1';
		$data_to_update[T_USERS_CONST.'_status'] = '1';
		
		$user_update = $this->SuperModel->Super_Insert(T_USERS,$data_to_update,T_USERS_CONST."_id = '".$user_info[T_USERS_CONST.'_id']."'");

		if(is_object($user_update) && $user_update->success){
			$this->frontSession['successMsg'] = $this->layout()->translator->translate("act_activate_txt");

		} else {
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("try_again_txt");
		}
		return $this->redirect()->toUrl(APPLICATION_URL.'/login');	
	}


	public function checkemailexistAction()

	{

		$request = $this->getRequest(); 

		if (!$request->isXmlHttpRequest() ) {

			return $this->redirect()->tourl(APPLICATION_URL);

		}

		

		$email = $this->params()->fromQuery('client_email');

		

		$isexists = $this->UserModel->checkForgotEmail($email);

		

		if(!$isexists){
			echo json_encode("`$email`"." is not valid");
		}
		else if($isexists && $isexists[T_USERS_CONST.'_type']=='admin'){
			echo json_encode("`$email`"." is not valid");	
		}
		else if($isexists && $isexists[T_USERS_CONST.'_email_verified']==0){
			$this->UserModel->sendVerificationEmail($email);
			echo json_encode("Your account is not verified, please verify your account from the activation link sent on your account email.");		
		}
		else{
			echo json_encode("true");
		}

		exit();

	}
	
	public function usercheckunameAction() {
		$request = $this->getRequest(); 
		if (!$request->isXmlHttpRequest() ) {
			return $this->redirect()->tourl(APPLICATION_URL);
		}

		$user_name = $this->params()->fromQuery('user_name');
		$rev = $this->params()->fromQuery('rev');
		$exclude = strtolower($this->params()->fromQuery('exclude'));
		$user_id = false ;

		if(!empty($exclude) && isset($this->loggedUser)){ 
			$logged_user = (array)$this->loggedUser;
			if(isset($logged_user[T_USERS_CONST.'_id']) && !empty($logged_user[T_USERS_CONST.'_id'])){
				$user_id =$logged_user[T_USERS_CONST.'_id'];
			}
		}

		if(empty($user_id)){
			$user_id = $this->params()->fromQuery('user_id');
		}

		if($exclude)
		{
			$logged_user = (array)$this->loggedUser;
			$user_id =$logged_user[T_USERS_CONST.'_id'];	
			$email = $this->UserModel->checkUname($user_name,$user_id);

		} else {
			$email = $this->UserModel->checkUname($user_name);
		}

		if($email)
		{
			echo json_encode("`$user_name`"."already exists , please enter any other user name.");

		} else {
			echo json_encode("true");
		}

		exit();
	}	
	
	public function chkstoreAction() {
		$request = $this->getRequest(); 
		if (!$request->isXmlHttpRequest() ) {
			return $this->redirect()->tourl(APPLICATION_URL);
		}
		$seller_storename = $this->params()->fromQuery('seller_storename');
		$rev = $this->params()->fromQuery('rev');
		$exclude = strtolower($this->params()->fromQuery('exclude'));
		$user_id = false ;

		if(!empty($this->loggedUser)) {
			$store_data = $this->SuperModel->Super_Get(T_STORE,"LOWER(store_name) =:SID and store_clientid != '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'","fetch",array('warray'=>array('SID'=>strtolower(trim(strip_tags($seller_storename))))));
		}

		if(!empty($store_data))
		{
			echo json_encode("`$seller_storename`"." already exists , please enter any other store name.");

		} else {
			echo json_encode("true");
		}

		exit();
	}
	
	/* Check email on registration / update profile page etc. */
	public function usercheckemailAction()
	{
		$request = $this->getRequest(); 
		if (!$request->isXmlHttpRequest() ) {
			return $this->redirect()->tourl(APPLICATION_URL);
		}

		$email_address = $this->params()->fromQuery('client_email');
		$rev = $this->params()->fromQuery('rev');
		$exclude = strtolower($this->params()->fromQuery('exclude'));
		$user_id = false ;

		if(!empty($exclude) && isset($this->loggedUser)){ 
			$logged_user = (array)$this->loggedUser;
			if(isset($logged_user[T_USERS_CONST.'_id']) && !empty($logged_user[T_USERS_CONST.'_id'])){
				$user_id =$logged_user[T_USERS_CONST.'_id'];
			}
		}

		if(empty($user_id)){
			$user_id = $this->params()->fromQuery('user_id');
		}

		if($exclude)
		{
			$logged_user = (array)$this->loggedUser;
			$user_id =$logged_user[T_USERS_CONST.'_id'];	
			$email = $this->UserModel->checkEmail($email_address,$user_id);

		} else {
			$email = $this->UserModel->checkEmail($email_address);
		}

		if($email)
		{
			echo json_encode("`$email_address`"."already exists , please enter any other email address");

		} else {
			echo json_encode("true");
		}

		exit();
	}

	

	public function	thanksAction(){

		$user_id = $this->params()->fromRoute('user'); 
		if(empty($_SESSION["successmsgt"])) {
			$this->frontSession['errorMsg']='Invalid Request, Please try again.';	

			return $this->redirect()->toUrl(APPLICATION_URL.'/login');
		}
		unset($_SESSION["successmsgt"]);
		if($user_id==''){

			$this->frontSession['errorMsg']='Invalid Request, Please try again.';	

			return $this->redirect()->toUrl(APPLICATION_URL.'/login');

		}

		$user_id=myurl_decode($user_id);

		$getUserData=$this->SuperModel->Super_Get(T_USERS,T_USERS_CONST."_id = '".$user_id."' and ".T_USERS_CONST.'_isthanks="0"');

		

		if(empty($getUserData)){

			$this->frontSession['errorMsg']='Invalid Request, Please try again.';	

			return $this->redirect()->toUrl(APPLICATION_URL.'/login');

		}

		$getUserData=$this->SuperModel->Super_Insert(T_USERS,array(T_USERS_CONST.'_isthanks'=>'1'),T_USERS_CONST."_id = '".$user_id."' and ".T_USERS_CONST.'_isthanks="0"');

	}

	

	public function checkforgotemailAction()

	{

		$request = $this->getRequest(); 

		if (!$request->isXmlHttpRequest() ) {

			return $this->redirect()->tourl(APPLICATION_URL);

		}

		$email_address = $this->params()->fromQuery('user_email');

		$email = $this->UserModel->checkEmail($email_address);

		

		if(!$email)

			echo json_encode("`$email_address`"." does not exists , please enter again");

		else

			echo json_encode("true");

		exit();

	}

	

	public function checkcaptchAction(){

		$request = $this->getRequest(); 

		if (!$request->isXmlHttpRequest() ) {

			return $this->redirect()->tourl(APPLICATION_URL);

		}

		$captcha_code=$this->params()->fromQuery('user_captcha');

		if(empty($_SESSION['captcha'] ) || strcasecmp($_SESSION['captcha'], $captcha_code) != 0){  

			echo json_encode(("The Validation code does not match"));	

		}else{  // Captcha verification is Correct. Final Code Execute here!	 	

			echo json_encode("true");	

		}

 		exit();



	}	

}