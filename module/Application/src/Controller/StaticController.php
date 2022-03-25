<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Front website: Static controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\StaticForm;
use Application\Form\AdvertisementForm;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
class StaticController extends AbstractActionController 
{
    private $AbstractModel,$EmailModel,$FrontMsgsession,$frontSession,$authService;

	/* Constructor of static controller */  
	public function __construct($AbstractModel,$EmailModel,$FrontMsgsession,$frontSession,$config_data,$authService)  
	{	
		$this->EmailModel = $EmailModel;
		$this->SuperModel = $AbstractModel;
		$this->frontSession = $frontSession;
		$this->FrontMsgsession = $FrontMsgsession;
		$this->loggedUser = $authService->getIdentity();
		$this->authService = $authService;
		$this->site_configs = $config_data;
	}
	
	/* CMS page: Privacy Policy */
	public function privacyAction()
    {
		$pageType = "privacy";
		$view = $this->getpagecontent('1',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}

	/* CMS page: Cookies Policy */
	public function cookiespolicyAction()
    {
		$pageType = "cookiespolicy";
		$view = $this->getpagecontent('2',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}
	
	public function fogholderAction() {
		$configs = $this->site_configs;
		$HomePageData = $this->SuperModel->Super_Get(T_HOMEPAGE,"1","fetchall");
		$HomePageData[1]['home_content'] = str_ireplace(array('{img_url}','{site_path}'),array(HTTP_IMG_PATH,APPLICATION_URL),$HomePageData[1]['home_content']);
		$category_list = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"1","fetchAll",array('limit'=>'5','offset'=>'0'));
		/* Banner block data */
		$homePageBannerData = $this->getpagescontent(13);
		$joinArr = array(
			'0' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus',T_CLIENT_VAR."client_stripe_id",T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
			'1' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid','2'=>"Inner",'3'=>array("store_approval")),
			'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
		);
		$recent_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1'","fetchAll",array('order'=>'product_date desc','limit'=>32,'offset'=>0),$joinArr);
		$favorite_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_favstat = '1' and product_delstatus != '1'","fetchAll",array('order'=>'product_date desc','limit'=>32,'offset'=>0),$joinArr);
		$joinArr = array(
			'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_status',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller"))
		);
		$favorite_stores = $this->SuperModel->Super_Get(T_STORE,"store_favorite = '1' and store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('limit'=>20,'fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
		$view = new ViewModel();
		$view->setVariable('HomePageData',$HomePageData);
		$view->setVariable('homePageBannerData',$homePageBannerData);
		$view->setVariable('configs',$configs);
		$view->setVariable('loggedUser',$this->loggedUser);
		$view->setVariable('product_categories',$category_list);
		$view->setVariable('recent_products',$recent_products);
		$view->setVariable('recent_products2',$recent_products2);
		$view->setVariable('favorite_products',$favorite_products);
		$view->setVariable('favorite_products2',$favorite_products2);
		$view->setVariable('favorite_stores',$favorite_stores);
		$view->setTerminal(true);
		return $view;
	}
	
	private function getpagescontent($pageid='',$pageTag='',$viewTemplate=1)
    {	
		$page_content = $this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>$pageid)));
		
		$page_content['page_content_'.$_COOKIE['currentLang']]=str_ireplace(array('{last_updated}','{img_url}','{site_path}'),array(date("d F, Y ",strtotime($page_content['page_updated'])),HTTP_IMG_PATH,APPLICATION_URL),$page_content['page_content_'.$_COOKIE['currentLang']]);

		return $page_content;
	}
	
	public function releasepayoutAction() {
		if(strtotime(date("Y-m-d")) >= strtotime($this->site_configs["autoapproval_date"])) {
			require_once(ROOT_PATH.'/vendor/Payouts-PHP-SDK-master/autoload.php');
			$joinArr = array(
				'0'=> array('0'=>T_CLIENTS,'1'=>'order_sellerid=yurt90w_client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_paypal_email',T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email")),
			);	
			$clients_data = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array('group'=>'order_sellerid'),$joinArr);
			$clientId = "AV793uzzxutmhd4XB2faoxE0jQSPQb2pdzyxSzEw8YO92w7nLa9dJgiNmt9ZnqvqyZsMb0QEEtRlrP1o";
			$clientSecret = "EMN8dOTZF8AWo73T7wwBIG6MIq1j996LtlecZDXCtTmzHgcE05yS18sZTcy6UF57smW4GE4CAw1CmItF";
			
			$mail_const_data2 = array(
				"user_name" => "Dev Auth",
				"user_email" => "developauth82@gmail.com",
				"message" => "Cron executed for payouts.",
				"subject" => "Cron executed for payouts."
			);	
			//$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
			
			//$clientId = "AZ9fpYJGKoHFKY28JbV8QeZW9fv3WUl5Mz1wGgORxkikIbz75lDg3w3HXQPASqDLhGmp70wC1vqgMFvF";
			//$clientSecret = "EFjRSRTLZvSTT3zBgI14Ki95KfuUI-5Gb52XfkhlDsSBkZNY49826jcLEdEE-7JBAXm-T9Y2E7U3mFpG
			//prd($clients_data);
			if(!empty($clients_data)) {
				foreach($clients_data as $clients_data_key => $clients_data_val) {
					if(!empty($clients_data_val[T_CLIENT_VAR."client_paypal_email"])) {
						$tx_data = $this->SuperModel->Super_Get(T_PRODORDER,"order_sellerid =:UID","fetchAll",array('fields'=>array('order_sitefee','order_total','order_date'),'warray'=>array('UID'=>$clients_data_val["order_sellerid"])));
						foreach($tx_data as $tx_data_key => $tx_data_val) {
							$release_date = date("Y-m-d",strtotime("+7 days", strtotime($tx_data_val["order_date"]))); 
							if(strtotime(date("Y-m-d")) < strtotime($release_date)) {
								$pending_funds += $tx_data_val["order_total"] - $tx_data_val["order_sitefee"];
							} else {
								$available_funds += $tx_data_val["order_total"] - $tx_data_val["order_sitefee"];
							}
						}
						$withdrawal_data = $this->SuperModel->Super_Get(T_WITHDRAWAL,"withdrawal_clientid =:UID and withdrawal_type = '1'","fetch",array('fields'=>array('total'=>new Expression('SUM(withdrawal_amount)')),'warray'=>array('UID'=>$clients_data_val["order_sellerid"])));
						if(!empty($withdrawal_data["total"])) {
							$total_withdrawal = $withdrawal_data["total"];
						} else {
							$total_withdrawal = '0';
						}
						$available_funds = $available_funds - ($total_withdrawal);
						$available_funds = bcdiv($available_funds,1,2);
						try {
							$ch = curl_init();

							curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v1/oauth2/token");
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
							curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v1/payments/payouts");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

							$array = array('sender_batch_header' => array(
									"sender_batch_id" => time(),
									"email_subject" => "You have a payout!",
									"email_message" => "You have received a payout."
								),
								'items' => array(array(
										"recipient_type" => "EMAIL",
										"amount" => array(
											"value" => $available_funds,
											"currency" => "USD"
										),
										"note" => "Thanks for the payout!",
										"sender_item_id" => time(),
										"receiver" => $clients_data_val[T_CLIENT_VAR."client_paypal_email"]
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
								$withtx_data["withdrawal_clientid"] = $clients_data_val["order_sellerid"];
								$withtx_data["withdrawal_amount"] = $available_funds;
								$withtx_data["withdrawal_date"] = date("Y-m-d H:i:s");
								$withtx_data["withdrawal_transferid"] = $getPayoutResult->batch_header->payout_batch_id;
								$withtx_data["withdrawal_type"] = '1';
								$withtx_data["withdrawal_resp"] = $getPayoutResult->batch_header->payout_batch_id;
								//$withtx_data["withdrawal_resp"] = json_encode($getPayoutResult);
								$this->SuperModel->Super_Insert(T_WITHDRAWAL,$withtx_data);
							}
							if (curl_errno($ch)) {
								echo 'Error:' . curl_error($ch);
							}
							curl_close($ch);
						} catch(\Exception $e) {
							prd($e->getMessage());
						}
						$notify_data["notification_type"] = '7';
						$notify_data["notification_by"] = '1';
						$notify_data["notification_to"] = $clients_data_val["order_sellerid"];
						$notify_data["notification_readstatus"] = '0';
						$notify_data["notification_date"] = date("Y-m-d H:i:s");
						$notify_data["notification_status"] = '0';
						$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data);
						$mail_const_data2 = array(
							  "user_name" => $clients_data_val[T_CLIENT_VAR.'client_name'],
							  "user_email" => $clients_data_val[T_CLIENT_VAR.'client_email'],
							  "message" => "Amount $".$withdraw_data["withdrawal_amount"]." has been successfully released.",
							  "subject" => "Amount $".$withdraw_data["withdrawal_amount"]." has been released."
						);	
						$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
						
						$mail_const_data2 = array(
							  "user_name" => "Dev Auth",
							  "user_email" => "developauth82@gmail.com",
							  "message" => "Amount $".$withdraw_data["withdrawal_amount"]." has been successfully released to ".$clients_data_val[T_CLIENT_VAR.'client_name'].".",
							  "subject" => "Amount $".$withdraw_data["withdrawal_amount"]." has been released."
						);	
						$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
						
						$con_data["config_value"] = date("Y-m-d",strtotime("+14 days", strtotime($this->site_configs["autoapproval_date"])));
						$this->SuperModel->Super_Insert(T_CONFIG,$con_data,"config_key = 'autoapproval_date'");
					}
				}
			}
		}
		prd("cron executed.");
	}
	
	public function processpaymentAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$all_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total' =>new Expression('SUM(product_cart_price)'))));
				$shipping_amtdata = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetch",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'}),'fields'=>array('total' =>new Expression('SUM(product_cart_delivery)'))));
			} else {
				$all_cartprice = array_column($_SESSION["cartArr"],'product_cart_price');
				$ship_cartprice = array_column($_SESSION["cartArr"],'product_cart_delivery');
			}
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$joinArr = array(
					'0'=>array('0'=>T_PRODUCTS,'1'=>'product_cart_prodid = product_id','2'=>'Left','3'=>array('product_title','product_isdigital','product_qty')),
					'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				);
				$my_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
			} else {
				$my_carts = $_SESSION["cartArr"];
			}
			if(!empty($my_carts)) {
				foreach($my_carts as $my_carts_key => $my_carts_val) {
					if(empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$joinArr2 = array(
							'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
						);
						$prod_details = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:TID","fetch",array('warray'=>array('TID'=>$my_carts_val["product_cart_prodid"]),'fields'=>array('product_title','product_isdigital','product_qty')),$joinArr2);
						$my_carts_val["product_title"] = $prod_details["product_title"];
						$my_carts_val["category_feild"] = $prod_details["category_feild"];
						$my_carts_val["product_isdigital"] = $prod_details["product_isdigital"];
						$my_carts_val["product_qty"] = $prod_details["product_qty"];
					}
					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_isdigital'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
					$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
					$available_qty = 0; $net_qty = 0;
					if($prod_data["product_isdigital"] != '1') {
					if(!empty($colorsize_data)) {
						foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
							$available_qty = $colorsize_data_val["color_qty"];
						}
					} } else {
						$available_qty = $prod_data["product_qty"];
					}
					if($my_carts_val["product_cart_qty"] > $available_qty && $prod_data["product_isdigital"] != '1') {
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
					$_SESSION["product_cartzid"] = 	$my_carts_val["product_cart_prodid"];
					if($my_carts_val["product_cart_prodid"] == $_SESSION["product_cartzid"]) {
						$net_qty += $my_carts_val["product_cart_qty"];
					}
					if($net_qty > $available_qty && $prod_data["product_isdigital"] != '1') {
						$sendData['response_code'] = 'error';
						$sendData["message"] = "Payment failed as this much of quanity is not available for ".$my_carts_val["product_title"].".";
						$sendData["status"] = 'Q';
						echo json_encode($sendData);
						exit();
					}
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
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$amount=bcdiv($all_carts["total"],1,2);
			} else {
				$cart_price = array_column($my_carts,"product_cart_price");
				$amount = array_sum($cart_price);
			}
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
				if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
					$my_carts = $my_carts;
				} else {
					$my_carts = $_SESSION["cartArr"];
				}
				$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array());
				$serial_num = count($all_orders) + 1;
				foreach($my_carts as $my_carts_key =>$my_carts_val) {
					if(!empty($my_carts_val["product_cart_delivery"])) {
							$site_fee = ($this->site_configs["site_commission"] / 100) * ($my_carts_val["product_cart_price"] + $my_carts_val["product_cart_delivery"]);
						} else {
							$site_fee = ($this->site_configs["site_commission"] / 100) * $my_carts_val["product_cart_price"];
						}
					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
					$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$prod_data["product_clientid"])));
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
							"order_status" => 1,
							"order_sellerid" => $prod_data["product_clientid"],
							"order_sellerpaid" => 1,
							"order_shipping" => $my_carts_val["product_cart_delivery"],
							"order_color" => $my_carts_val["product_cart_color"],
							"order_size" => $my_carts_val["product_cart_size"],
							"order_address" => $_SESSION["shipping_addr"],
							"order_shiprate" => $my_carts_val["product_cart_shiprate"],
							"order_shipname" => $my_carts_val["product_cart_shipname"],
							"order_txnid" => $payrequest["TRANSACTIONID"],
							"order_note" => $my_carts_val["product_cart_note"]
					);					
					$jj = $this->SuperModel->Super_Insert(T_PRODORDER,$orderData);
					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$message = $this->loggedUser->{T_CLIENT_VAR.'client_name'}." has placed an order with order number 51905296".$serial_num;
					} else {
						$clt_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$_SESSION["user_Id"])));
						$message = $clt_details[T_CLIENT_VAR."client_name"]." has placed an order with order number 51905296".$serial_num;
					}
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

					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$payer_name = $this->loggedUser->{T_CLIENT_VAR."client_name"};
						$payer_email = $this->loggedUser->{T_CLIENT_VAR."client_email"};
					} else {
						$payer_name = $clt_details[T_CLIENT_VAR."client_name"];
						$payer_email = $clt_details[T_CLIENT_VAR."client_email"];
					}

					$mail_const_data4 = array(
						"user_name" => $payer_name,
						"user_email" => $payer_email,
						"message" => "Your order with order number 51905296".$serial_num." has been placed successfully.",
						"subject" => "Order placed"
					);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data4);
					if($prod_data["product_isdigital"] != '1') {
						$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
						foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
							$available_qty = 0;
							$prodqty = $colorsize_data_val["color_qty"] - $my_carts_val["product_cart_qty"];
							if($prodqty < 1) {
								$avl_data["color_qty"] = 0;
							} else {
								$avl_data["color_qty"] = $prodqty;
							}
							$this->SuperModel->Super_Insert(T_PROQTY,$avl_data,"color_productid = '".$my_carts_val["product_cart_prodid"]."' and color_slug = '".strtolower($my_carts_val["product_cart_color"])."' and color_size = '".$my_carts_val["product_cart_size"]."'");							
						}	
					} else {
						$prod_qty["product_qty"] = $prod_data["product_qty"] - 1;
						$jj = $this->SuperModel->Super_Insert(T_PRODUCTS,$prod_qty,"product_id = '".$my_carts_val["product_cart_prodid"]."'");
					}
					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_id = '".$my_carts_val["product_cart_id"]."'");	
					} else {
						unset($_SESSION["cartArr"]);
					}
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
	
	public function productzoneAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
					'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
			//$product_categories = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1' and LOWER(product_title) like :CID","fetchAll",array('warray'=>array('CID'=>'%'.trim(strtolower($data["prod_txt"])).'%','UID'=>$data["keycode"])),$joinArr);
			$prodct_data = $this->layout()->AbstractModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_clientid =:UID and LOWER(product_title) like :CID","fetchAll",array('order'=>'product_order asc','warray'=>array('CID'=>'%'.trim(strtolower($data["prod_txt"])).'%','UID'=>$data["keycode"])),$joinArr);
			$templevel=0;   
			$newkey=0;
			/*$grouparr[$templevel]="";
  			foreach ($product_categories as $key => $val) {
   				if ($templevel==$val['product_category']){
     				$grouparr[$templevel][$newkey]=$val;
   				} else {
     				$grouparr[$val['category_feild']][$newkey]=$val;
   				}
     			$newkey++;       
  			}
			$grouparr = array_filter(array_map('array_filter', $grouparr));*/
			$view = new ViewModel();
			//$view->setVariable('product_categories', $grouparr);
			$view->setVariable('prodct_data', $prodct_data);
			$view->setVariable('store_by', $data["keycode"]);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function removecartAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			
			if($data["uid"] == '2') {
			
				if(!empty($_SESSION["cartArr"][$data["tid"]])) {
					unset($_SESSION["cartArr"][$data["tid"]]);	
					sort($_SESSION["cartArr"]);
					echo "success";
					exit();
				} else {
					echo "error";
					exit();
				}
			} else {
				$cart_data = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_id =:TID and product_cart_clientid =:UID","fetch",array('warray'=>array('TID'=>base64_decode($data["tid"]),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				if(empty($cart_data)) {
					echo "error";
					exit();
				} else {
					$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_id ='".base64_decode($data["tid"])."' and product_cart_clientid ='".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
					echo "success";
					exit();
				} 
			}
		}
	}
	
	public function pricedetailsAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$qty_details = $this->SuperModel->Super_Get(T_PROQTY,"color_title =:TID and color_size =:YID and color_productid =:PID","fetch",array('warray'=>array('TID'=>$data["color"],'YID'=>$data["tid"],'PID'=>$data["prod"])));
			$view = new ViewModel();
			$view->setVariable('qty_details',$qty_details);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function pricedetailAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:TID","fetch",array('warray'=>array('PID'=>base64_decode($data["pid"]),'TID'=>strtolower($data["tid"]))));
			if(!empty($data["sid"])) {
				$color_size = $data["sid"];
			} else {
				$color_size = $colorsize_data["color_size"];
			}
			$best_offers = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["pid"]),'CID'=>strtolower($data["tid"]),'SID'=>$color_size)));
			if(count($best_offers) > 1) {
				unset($best_offers[0]);
			}
			$view = new ViewModel();
			if(count($best_offers) > 1) {
				$view->setVariable('best_offers',$best_offers);
			}
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function shipdetailsAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if(!empty($data["ship_name"]) && !empty($data["ship_addr"]) && !empty($data["ship_code"]) && !empty($data["ship_phone"])) {
				if(empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
					if(empty($data["ship_mail"])) {
						echo "error";
						exit();
					}
				}
				if (ctype_alpha(str_replace(' ', '', $data["ship_name"])) === false) {
					$sendData['response_code'] = 'fixphone';
					echo json_encode($sendData);
					exit();
				}
				if(!preg_match('/^[- +()]*[0-9][- +()0-9]*$/', $data["ship_phone"])) {
					$sendData['response_code'] = 'fixphone';
					echo json_encode($sendData);
					exit();
				}
				if(strlen($data["ship_name"]) > 200) {
					$sendData['response_code'] = 'error';
					echo json_encode($sendData);
					exit();
				}
				if(strlen($data["ship_code"]) > 10) {
					$sendData['response_code'] = 'error';
					echo json_encode($sendData);
					exit();
				}
				if(strlen($data["ship_phone"]) > 20) {
					$sendData['response_code'] = 'fixphone';
					echo json_encode($sendData);
					exit();
				}
				$client_data[T_CLIENT_VAR.'client_name'] = $data["ship_name"];
				$client_data[T_CLIENT_VAR.'client_address'] = $data["ship_addr"];
				$client_data[T_CLIENT_VAR.'client_apartment'] = $data["ship_apt"];
				$client_data[T_CLIENT_VAR.'client_postcode'] = $data["ship_code"];
				$client_data[T_CLIENT_VAR.'client_city'] = $data["ship_city"];
				$client_data[T_CLIENT_VAR.'client_state'] = $data["ship_state"];
				$client_data[T_CLIENT_VAR.'client_phone'] = $data["ship_phone"];
				//$data["ship_country"] = 'United States';
				$client_data[T_CLIENT_VAR.'client_country'] = $data["ship_country"];
				if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
					$this->SuperModel->Super_Insert(T_CLIENTS,$client_data,T_CLIENT_VAR."client_id = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'");
				} else {
					$data["ship_mail"] = strtolower(trim($data["ship_mail"]));
					$check_mail = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_email =:TID","fetch",array('fields'=>array(T_CLIENT_VAR."client_id"),'warray'=>array('TID'=>$data["ship_mail"])));
					$client_data[T_CLIENT_VAR."client_email"] = $data["ship_mail"];
					$pwd = generatePassword(random_str(2));
					$client_data[T_CLIENT_VAR."client_password"] = md5($pwd);
					if(empty($check_mail)) {
						$client_data[T_CLIENT_VAR."client_created"] = date("Y-m-d H:i:s");
						$inserted = $this->SuperModel->Super_Insert(T_CLIENTS,$client_data);
						$_SESSION["user_Id"] = $inserted->inserted_id;
						$clt_data[T_CLIENT_VAR."client_activation_key"] = md5($inserted->inserted_id."!@#$%^$%&(*_+".time());
						$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$inserted->inserted_id."'");
						$mail_const_data2 = array(
							"user_name" => $data["ship_name"],
							"user_email" => $data["ship_mail"],
							"client_activation_key" => $clt_data[T_CLIENT_VAR."client_activation_key"],
							"pass" => $pwd
						);
						$isSend = $this->EmailModel->sendEmail('account_details',$mail_const_data2);
					} else {
						$_SESSION["user_Id"] = $check_mail[T_CLIENT_VAR."client_id"];
					}
				}
				
				$address = $client_data[T_CLIENT_VAR.'client_address'];
				$country_name = $client_data[T_CLIENT_VAR.'client_country'];
				$_SESSION["shipping_addr"] = $data["ship_addr"];
				$_SESSION["shipping_country"] = $data["ship_country"];
				$_SESSION["shipping_apt"] = $data["ship_apt"];
				$_SESSION["shipping_city"] = $data["ship_city"];
				$_SESSION["shipping_state"] = $data["ship_state"];
				$_SESSION["shipping_code"] = $data["ship_code"];
				if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
					$product_cart = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:TID","fetchAll",array('warray'=>array('TID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
				} else {
					$product_cart = $_SESSION["cartArr"];
				}
				$_SESSION["shipping_profiles"] = array();
				$_SESSION["shipping_prods"] = array();
				$total_shipping = 0; $total_charge = 0; $total_time = 0; $addqty_price = 0; $shipping_charge = 0;
				if(!empty($product_cart)) {
					foreach($product_cart as $product_cart_key => $product_cart_val) {
						$joinArr = array(
							'0'=>array('0'=>T_CLIENTS,'1'=>'product_clientid = yurt90w_client_id','2'=>'Inner','3'=>array(T_CLIENT_VAR.'client_address',T_CLIENT_VAR.'client_country')),
							'1'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Inner','3'=>array('store_closed','store_closed_date','store_closed_tilldate','store_acceptorder')),
						);
						$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>$product_cart_val["product_cart_prodid"])),$joinArr);
						if($product_data["store_closed"] == '1' && $product_data["store_acceptorder"] == '2') {
							$sendData['response_code'] = 'closed';
							$sendData['message'] = 'We are closed. You cannot place your order at the moment.';
							echo json_encode($sendData);
							exit();
						}
						if($product_data["product_delstatus"] == '1') {
							$sendData['response_code'] = 'no_product';
							$sendData['message'] = $product_data["product_title"].' cannot be ordered as it has been deleted.';
							echo json_encode($sendData);
							exit();
						}
						if(!empty($product_data["product_clientid"])) {
							$country_data = $this->SuperModel->Super_Get(T_COUNTRIES,"LOWER(country_name_en) =:CID","fetch",array('warray'=>array('CID'=>strtolower($country_name))));
							
							$country_name2 = $product_data[T_CLIENT_VAR.'client_country'];
							
							$shipping_data = $this->SuperModel->Super_Get(T_SHIPPROFILES,"shipping_id = :TID","fetch",array('warray'=>array('TID'=>$product_data["product_shippingid"])));
							$shipping_countries = explode(",",$shipping_data["shipping_countries"]);
							//$key = array_search($country_name, array_column($shipping_data, 'country_name_en'));
							/*if(isset($key) && !empty($shipping_data[$key]["shipping_rate"])) {*/
							if (in_array($country_data["country_id"], $shipping_countries)) {
								if ($country_data["country_name_en"] == $country_name2)
								{
									if($product_data["product_isdigital"] != '1') {
										if($shipping_data["shipping_free"] == '1') {
											$shipping_charge = 0;
										} else {
											if(!in_array($product_data["product_id"],$_SESSION["shipping_prods"]) && in_array($product_data["product_shippingid"],$_SESSION["shipping_profiles"])) {
												$add_qty = $product_cart_val["product_cart_qty"];
											} else {
												$add_qty = $product_cart_val["product_cart_qty"] - 1;
											}
											$addqty_price = 0;
											for($i = 0;$i < $add_qty;$i++) {
												$addqty_price += $shipping_data["shipping_addrate"];
											}
											if(in_array($product_data["product_shippingid"],$_SESSION["shipping_profiles"])) {
												$shipping_charge = $addqty_price;
											} else {
												$shipping_charge = $shipping_data["shipping_rate"] + $addqty_price;
											}
											$_SESSION["shipping_profiles"][] = $product_data["product_shippingid"];
											$_SESSION["shipping_prods"][] = $product_data["product_id"];
										}
									} else {
										$shipping_charge = 0;
									}
									$cart_data["product_cart_shiprate"] = '1';
								} else {
									if($product_data["product_isdigital"] != '1') {
										if(!in_array($product_data["product_id"],$_SESSION["shipping_prods"]) && in_array($product_data["product_shippingid"],$_SESSION["shipping_profiles"])) {
											$add_qty = $product_cart_val["product_cart_qty"];
										} else {
											$add_qty = $product_cart_val["product_cart_qty"] - 1;
										}
										$addqty_price = 0;
										for($i = 0;$i < $add_qty;$i++) {
											$addqty_price += $shipping_data["shipping_addrate"];
										}
										if(in_array($product_data["product_shippingid"],$_SESSION["shipping_profiles"])) {
											$shipping_charge = $addqty_price;
										} else {
											$shipping_charge = $shipping_data["shipping_globalrate"] + $addqty_price;
										}
										$_SESSION["shipping_profiles"][] = $product_data["product_shippingid"];
										$_SESSION["shipping_prods"][] = $product_data["product_id"];
									} else {
										$shipping_charge = 0;
									}
									$cart_data["product_cart_shiprate"] = '2';
								}
								$cart_data["product_cart_shipname"] = $shipping_data["shipping_name"];
								$processing_time = $processing_days;
							} else {
								if($product_data["product_isdigital"] != '1') {
									$sendData['response_code'] = 'no_shipping';
									$sendData['message'] = $product_data["product_title"].' cannot be shipped in the selected country.';
									echo json_encode($sendData);
									exit();
								}
							}
						}
						if(!empty($shipping_charge)) {
							$total_charge += $product_cart_val["product_cart_price"];
							$total_shipping += $shipping_charge;
							$total_time += $processing_time;
							$cart_data["product_cart_delivery"] = $shipping_charge;
							if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
								$this->SuperModel->Super_Insert(T_PRODCART,$cart_data,"product_cart_id = '".$product_cart_val["product_cart_id"]."'");
							} else {
								$_SESSION["cartArr"][$product_cart_key]["product_cart_delivery"] = $shipping_charge;
								$_SESSION["cartArr"][$product_cart_key]["product_cart_shipname"] = $shipping_data["shipping_name"];
								$_SESSION["cartArr"][$product_cart_key]["product_cart_shiprate"] = $cart_data["product_cart_shiprate"];
							}
						} else {
							$total_charge += $product_cart_val["product_cart_price"];
							$total_shipping += $shipping_charge;
							$cart_data["product_cart_delivery"] = $shipping_charge;
							if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
								$this->SuperModel->Super_Insert(T_PRODCART,$cart_data,"product_cart_id = '".$product_cart_val["product_cart_id"]."'");
							} else {
								$_SESSION["cartArr"][$product_cart_key]["product_cart_delivery"] = $shipping_charge;
								$_SESSION["cartArr"][$product_cart_key]["product_cart_shipname"] = $shipping_data["shipping_name"];
								$_SESSION["cartArr"][$product_cart_key]["product_cart_shiprate"] = $cart_data["product_cart_shiprate"];
							}
						}
					}
				}
				$sendData['response_code'] = 'success';
				$sendData['product_charge'] = bcdiv($total_charge,1,2);
				$sendData['total_charge'] = bcdiv($total_charge+$total_shipping,1,2);
				$sendData['shipping_charge'] = bcdiv($total_shipping,1,2);
				echo json_encode($sendData);
				exit();
			} else {
				echo "error";
				exit();
			}
		}
	}
	
	public function cartpriceAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if($data["uid"] == '1') {
				$cart_data = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($data["tid"]))));
			} else {
				$cart_data = $_SESSION["cartArr"];
			}
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["pid"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			}
			if(empty($cart_data)) {
				echo "empty";
				exit();
			}
			if($product_data["product_isdigital"] != '1') {
				$data["qty"] = strip_tags($data["qty"]);
			} else {
				$data["qty"] = 1;
			}
			if($data["qty"] < 1) {
				echo "invalid_qty";
				exit();
			}
			if (!is_numeric($data["qty"]))
			{
				echo "invalid_number";
				exit();
			}
			$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>base64_decode($data["pid"]),'CID'=>strtolower($data["color"]),'SID'=>$data["size"])));
			$available_qty = 0;
			$break_cond = '';
			if($product_data["product_isdigital"] != '1') {
			if(!empty($colorsize_data)) {
				foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
					//$available_qty += $colorsize_data_val["color_qty"];
					if($break_cond == '') {
						if(($colorsize_data_val["color_qtyfrom"] <= $data["qty"]) && ($data["qty"] <= $colorsize_data_val["color_qtyto"])) {
								$available_qty = $colorsize_data_val["color_qty"];
								$product_price = (float) $colorsize_data_val["color_price"];
								$break_cond = 1;
						} else if($data["qty"] > $colorsize_data_val["color_qtyfrom"] && !($colorsize_data_val["color_qtyfrom"] <= $data["qty"]) && ($data["qty"] <= $colorsize_data_val["color_qtyto"])) {
							$available_qty = $colorsize_data_val["color_qty"];
							$product_price = (float) $colorsize_data_val["color_price"];
							$break_cond = 1;
						}
					}
				}
			} } else {
				$available_qty = $product_data["product_qty"];
			}
			if(empty($product_price)) {
				/*$endArr = end($colorsize_data);
				$available_qty = $endArr["color_qty"];
				$product_price = (float) $endArr["color_price"];*/
				foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
						if($data["qty"] >= $colorsize_data_val["color_qtyfrom"]) {
							$available_qty = $colorsize_data_val["color_qty"];
							$product_price = (float) $colorsize_data_val["color_price"];
							$break_cond = 1;
						}
					}
			}
			if($data["qty"] > $available_qty && $product_data["product_isdigital"] != '1') {
				echo "qty_restricted";
				exit();
			}
			if($product_data["product_isdigital"] != '1') {
				$product_price = $product_price * $data["qty"];
			} else {
				$product_price = $product_data["product_price"];
			}
			$coupon_discount = ''; $inv_coupon = ''; $val_copon = '';
			if(!empty($data["coupon_code"])) {
				$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_code =:TID and coupon_status = '1'","fetch",array('warray'=>array('TID'=>$data["coupon_code"])));
				$coupon_arr = explode(",",$coupon_data["coupon_product"]);
				if(empty($coupon_data)) {
					if(!empty($data["chkcoupon"])) { $inv_coupon = 1; }
				}
				else if(strtotime($coupon_data["coupon_end_date"]) < strtotime(date("Y-m-d")))
				{	
					if(!empty($data["chkcoupon"])) { $inv_coupon = 1; }
				} elseif(strtotime($coupon_data["coupon_start_date"]) > strtotime(date("Y-m-d")))
				{	
					if(!empty($data["chkcoupon"])) { $inv_coupon = 1; }
				} else {
					if($coupon_data["coupon_type"] == '2' || in_array($product_data["product_id"], $coupon_arr)) {
							$coupon_discount = bcdiv(($coupon_data["coupon_discount"] / 100) * $product_price,1,2);
							$product_price = $product_price - $coupon_discount;
							if(!empty($data["chkcoupon"])) {
								$val_copon = '1';
							}
					}
				}
			}
			$cart_record["product_cart_price"] = bcdiv($product_price,1,2);
			$cart_record["product_cart_qty"] = $data["qty"];
			$cart_record["product_cart_coupon"] = $data["coupon_code"];
			if(!empty($coupon_discount)) {
				$cart_record["product_cart_discount"] = bcdiv($coupon_discount,1,2);
			} else {
				$cart_record["product_cart_discount"] = 0;
			}
			if($data["uid"] == '1') {
				$this->SuperModel->Super_Insert(T_PRODCART,$cart_record,"product_cart_id = '".base64_decode($data["tid"])."'");
			} else {
				$_SESSION["cartArr"][$data["tid"]]["product_cart_price"] = $cart_record["product_cart_price"];
				$_SESSION["cartArr"][$data["tid"]]["product_cart_qty"] = $cart_record["product_cart_qty"];
				$_SESSION["cartArr"][$data["tid"]]["product_cart_coupon"] = $cart_record["product_cart_coupon"];
				$_SESSION["cartArr"][$data["tid"]]["product_cart_discount"] = $cart_record["product_cart_discount"];
			}
			if($data["uid"] == '1') {
				$joinArr = array(
					'0'=>array('0'=>T_PRODUCTS,'1'=>'product_cart_prodid = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_globalrate')),
					'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
					'2'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Inner','3'=>array('store_name')),
				);
				$all_carts = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
			} else {
				if(!empty($_SESSION["cartArr"])) {
					foreach($_SESSION["cartArr"] as $cart_arr_key => $cart_arr_val) {
						$joinArr = array(
							'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
							'1'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Inner','3'=>array('store_name','store_approval')),
						);
						$productz_data[] = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id = :TID","fetch",array('fields'=>array('product_title','product_price','product_photos','product_defaultpic','product_clientid','product_globalrate'),'warray'=>array('TID'=>$cart_arr_val["product_cart_prodid"])),$joinArr);
						$productz_data[$cart_arr_key]["product_cart_prodid"] = $cart_arr_val["product_cart_prodid"];
						$productz_data[$cart_arr_key]["product_cart_price"] = $cart_arr_val["product_cart_price"];
						$productz_data[$cart_arr_key]["product_cart_qty"] = $cart_arr_val["product_cart_qty"];
						$productz_data[$cart_arr_key]["product_cart_coupon"] = $cart_arr_val["product_cart_coupon"];
						$productz_data[$cart_arr_key]["product_cart_date"] = $cart_arr_val["product_cart_date"];
						$productz_data[$cart_arr_key]["product_cart_discount"] = $cart_arr_val["product_cart_discount"];
						$productz_data[$cart_arr_key]["product_cart_color"] = $cart_arr_val["product_cart_color"];
						$productz_data[$cart_arr_key]["product_cart_size"] = $cart_arr_val["product_cart_size"];
					}
					$all_carts = $productz_data;
				}
			}
			$view = new ViewModel();
			$view->setVariable('all_carts',$all_carts);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setVariable('inv_coupon',$inv_coupon);
			$view->setVariable('val_copon',$val_copon);
			$view->setVariable('coupon_apply',$data["chkcoupon"]);
			$view->setVariable('uid',$data["uid"]);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function sublistAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
				'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus',T_CLIENT_VAR."client_stripe_id",'yurt90w_client_country',T_CLIENT_VAR."client_bestseller")),
				'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
			);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1' and product_subcategory =:TID","fetchAll",array('order'=>'product_order asc','warray'=>array('TID'=>base64_decode($data["cat"]),'UID'=>$data["keycode"])),$joinArr);
			$view = new ViewModel();
			$view->setVariable('category_title', $product_data[0]["category_feild"]);
			$view->setVariable('product_data', $product_data);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function categorylistAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			if($data["cat"] == 'all') {
				$joinArr = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_country',T_CLIENT_VAR.'client_bestseller')),
					'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
				$product_categories = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1' and yurt90w_client_stripe_id != ''","fetchAll",array('fields'=>'product_category','group'=>'product_category','warray'=>array('UID'=>$data["keycode"])),$joinArr);
				$product_categories = array_map("unserialize", array_unique(array_map("serialize", $product_categories)));
				$product_data = $this->layout()->AbstractModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_clientid = '".$data["keycode"]."'","fetchAll",array('order'=>'product_order asc'),$joinArr);
				$view = new ViewModel();
				$view->setVariable('product_categories', $product_categories);
				$view->setVariable('product_data', $product_data);
				$view->setVariable('store_by', $data["keycode"]);
				$view->setVariable('cattype',$data["cat"]);
				$view->setVariable('loggedUser',$this->loggedUser);
				$view->setTerminal(true);
				return $view;
			} else {
				$joinArr = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_country',T_CLIENT_VAR.'client_bestseller')),
					'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1' and product_category =:TID and yurt90w_client_stripe_id != ''","fetchAll",array('order'=>'product_order asc','warray'=>array('TID'=>base64_decode($data["cat"]),'UID'=>$data["keycode"])),$joinArr);
				$view = new ViewModel();
				$view->setVariable('category_title', $product_data[0]["category_feild"]);
				$view->setVariable('product_data', $product_data);
				$view->setVariable('loggedUser', $this->loggedUser);
				$view->setTerminal(true);
				return $view;
			}
		}
	}
	
	/* Seller Profile page */
	public function sellerprofileAction() {
		$key = $this->params()->fromRoute('key');
		$key = str_replace("-"," ",$key);
		$joinArr1 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = '.T_CLIENT_VAR.'client_id', '2' => 'Inner', '3' => array(T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR.'client_status','yurt90w_client_stripe_id',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller"))
		);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_name =:UID and store_approval = '1' and yurt90w_client_status = '1'","fetch",array('fields'=>array('store_name','store_banner','store_description','store_id','store_clientid','store_verification','store_logo','store_title','store_headline','store_policy','store_closed','store_closed_date','store_closed_tilldate'),'warray'=>array('UID'=>$key)),$joinArr1);
		if(empty($store_data)) {
			$this->frontSession['errorMsg'] = "No such seller exists.";
			return $this->redirect()->tourl(APPLICATION_URL);
		}
		$fav_data = '';
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$fav_data = $this->layout()->AbstractModel->Super_Get(T_FAVOURITE,"favourite_storeid = '".$store_data["store_id"]."' and favourite_by = '".$this->loggedUser->{T_CLIENT_VAR.'client_id'}."'","fetch",array('fields'=>'favourite_id'));
		}
		$joinArr = array(
			'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
			'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
			'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1' and product_delstatus != '1' and yurt90w_client_stripe_id != ''","fetchAll",array('order'=>'product_order asc','fields'=>'*','warray'=>array('UID'=>$store_data["store_clientid"])),$joinArr);
		$rating_data = $this->SuperModel->Super_Get(T_REVIEWS,"review_to =:UID","fetch",array('warray'=>array('UID'=>$store_data["store_clientid"]),'fields'=>array('total_reviews'=>new Expression('COUNT(review_starrating)'),'avg_review'=>new Expression('AVG(review_starrating)'),'sum_review'=>new Expression('SUM(review_starrating)'),'review_date')));
		$total_rating = 5 * $rating_data["total_reviews"];
		if(!empty($total_rating)) {
			$review_percentile = ($rating_data["sum_review"] / $total_rating) * 100;
		}
		$ago_var = time_elapser_str($rating_data["review_date"]);
		$joinArr = array(
			'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
		);
		$product_categories = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:UID and product_status = '1'","fetchAll",array('fields'=>'product_category','group'=>'product_category','warray'=>array('UID'=>$store_data["store_clientid"])),$joinArr);
		$product_categories = array_map("unserialize", array_unique(array_map("serialize", $product_categories)));
		$ip = get_client_ip();
		if(!empty($ip)) {
			$view_data["storeview_ownerid"] = $store_data["store_clientid"];
			$view_data["storeview_ip"] = $ip;
			$view_data["storeview_storeid"] = $store_data["store_id"];
			$view_data["storeview_date"] = date("Y-m-d H:i:s");
			$this->SuperModel->Super_Insert(T_STOREVIEWS,$view_data);
		}
		$store_members = $this->SuperModel->Super_Get(T_STORE_MEMBERS,"member_storeid =:TID","fetchAll",array('warray'=>array('TID'=>$store_data["store_id"])));
		$joinArr4 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'review_from = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR.'client_stripe_id')),
		);
		$review_record = $this->SuperModel->Super_Get(T_REVIEWS,"review_to =:TID","fetchAll",array('order'=>'review_id desc','warray'=>array('TID'=>$store_data["store_clientid"])),$joinArr4);
		$view = new ViewModel();
		$view->setVariable('store_data',$store_data);
		$view->setVariable('product_data',$product_data);
		$view->setVariable('product_categories',$product_categories);
		$view->setVariable('total_products',count($product_data));
		$view->setVariable('average_rating',$rating_data["avg_review"]);
		$view->setVariable('total_reviews',$rating_data["total_reviews"]);
		$view->setVariable('loggedUser',$this->loggedUser);
		$view->setVariable('fav_data',$fav_data);
		if(!empty($review_percentile)) {
			$view->setVariable("review_percentile",$review_percentile);
		}
		$view->setVariable("ago_var",$ago_var);
		$view->setVariable("store_members",$store_members);
		$view->setVariable("review_record",$review_record);
		return $view;
	}
	
	/* CMS page: About Us */
	public function aboutAction()
    {
		$page_id=3;
		// $getTeamList = $this->SuperModel->Super_Get(T_TEAM_LIST,"1","fetchall");
		$page=$this->SuperModel->Super_Get(T_PAGE_CONTENT, 'page_content_page_id=:pageids','fetchAll',array("warray"=>array("pageids"=>$page_id)));
		$pagenew=array();
		foreach($page as $key=>$value){
		    $pagenew[$value['page_content_section_key']]=$value['section_content'];
		}
		
		$view = $this->getpagecontent('3','about');
		$this->layout()->setVariable('pageType','about');
		$view ->setVariable('page',$pagenew);
		$view ->setVariable('configs',$this->site_configs);
		// $view->setVariable('getTeamList',$getTeamList);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}

	/* Send enquiry email to administrator */
	public function contactAction() 
	{
		$form = new StaticForm();
		$form->contact();
		$this->layout()->setVariable('pageType','contact');
		$view = $this->getpagecontent('4','contact',0);
		$page=$this->SuperModel->Super_Get(T_PAGE_CONTENT, 'page_content_page_id=:pageids','fetchAll',array("warray"=>array("pageids"=>4)));
		$pagenew=array();
		foreach($page as $key=>$value){
		    $pagenew[$value['page_content_section_key']]=$value['section_content'];
		}
		$view->setVariable('site_configs',$this->site_configs);
		if($this->getRequest()->isPost()){
			$data = $this->params()->fromPost();
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
					$Formdata = $form->getData();
					$Formdata["user_name"] = strip_tags($Formdata["user_name"]);
					$Formdata["user_email"] = strip_tags($Formdata["user_email"]);
					$Formdata["user_message"] = strip_tags($Formdata["user_message"]);
					if(strlen($Formdata["user_message"]) > 600) {
						$this->frontSession['errorMsg'] = "Please do not enter more words in the message than mentioned.";
                        return $this->redirect()->toUrl(APPLICATION_URL . '/contact-us');
					}
					if (!filter_var($Formdata["user_email"], FILTER_VALIDATE_EMAIL)) {
                        $this->frontSession['errorMsg'] = "You have entered an invalid email address.";
                        return $this->redirect()->toUrl(APPLICATION_URL . '/contact-us');
                    }
					$this->EmailModel->sendEmail('contact_us_admin',$Formdata);
					$this->frontSession['successMsg'] = $this->layout()->translator->translate("thank_you_txt");
					return $this->redirect()->toUrl(APPLICATION_URL.'/contact-us');
					
				} else {
					$error_msg = $form->getMessages();
					if(isset($error_msg['user_name']['notAlpha']) and !empty($error_msg['user_name']['notAlpha'])){
						$msg = 'Entered name contains non alphabetic characters.';
					} else {
						$msg = 'Please check entered information again.';
					}
					$this->frontSession['errorMsg'] = $msg;
			
				}
			}
		}
		$view->setVariable('page',$pagenew);
		$view ->setVariable('configs',$this->site_configs);
		$view->setVariable('form',$form);
		return $view;
	}
	
	/* CMS page: Advertise with us */
	public function advertisewithusAction()
    {
		$pageType = "advertisewithus";
		$view = $this->getpagecontent('6',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}

	/* CMS page: Advertise with us request */
	public function advertisewithusrequestAction()
    {
    	$pageType = "advertisewithus";
		$this->layout()->setVariable('pageType',$pageType);

		/* Advertisement form with post functionality */
		$adsForm = new AdvertisementForm($this->layout()->translator);
		$adsForm->advertisement_request();
		$view = new ViewModel();
		// $dbAdapter = $this->Adapter;
		//$type = '';
		$request = $this->getRequest();

		if($request->isPost())
		{
			$data = $request->getPost();
			$adsForm->setData($data);

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
		        if($adsForm->isValid()) {
					$data = $adsForm->getData();

					$return_data = array('status'=>'200', 'message'=>'ok');

				   	unset($data['client_accepted_terms'], /*$data['hiddenRecaptcha'],*/ $data['post_csrf']);
					// $data = GetFormElementsName((array)$data,T_DB_CONST);

					$saveData = array();
					$saveData[T_ADVERTISEMENTS_CONST.'user_name'] = $data['user_name'];
					$saveData[T_ADVERTISEMENTS_CONST.'company_name'] = $data['company_name'];
					$saveData[T_ADVERTISEMENTS_CONST.'phone_number'] = $data['phone_number'];
					$saveData[T_ADVERTISEMENTS_CONST.'email'] = $data['email'];
					$saveData[T_ADVERTISEMENTS_CONST.'ads_position'] = $data['ads_position'];
					$saveData[T_ADVERTISEMENTS_CONST.'bid_price'] = $data['bid_price'];
					$saveData[T_ADVERTISEMENTS_CONST.'created'] = date('Y-m-d H:i:s');

					$isInsert = $this->SuperModel->Super_Insert(T_ADVERTISEMENTS, $saveData);

					/*pr($saveData);
					prd($isInsert);*/

					// Mail to user
					$messageText = '<p style="color:#2c3848;font-style: italic;font-size: 15px;">Thank you for posting request of advertisement. Our team will check your request and contact you soon by email.</p>';
					$mail_const_data = array(
							"user_name" => ucwords(strtolower($data['user_name'])),
							"user_email" => strtolower($data['email']),
							"message" => $messageText,
							"subject" => "Thank you for posting request of advertisement."
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data);

					// Mail to admistrator
					$messageText2 = '<p style="color:#2c3848;font-style: italic;font-size: 15px;">A new user has submitted request for advertisement. Details are as follows:</p>';

					$messageText2 .= 'User Name: '. $data['user_name'].'<br>';
					$messageText2 .= 'Company Name: '. $data['company_name'].'<br>';
					$messageText2 .= 'Phone Number: '. $data['phone_number'].'<br>';
					$messageText2 .= 'Email: '. $data['email'].'<br>';
					$messageText2 .= 'Ads Position: '. $data['ads_position'].'<br>';
					$messageText2 .= 'Bid Price: '. $data['bid_price'].'<br>';

					$mail_const_data2 = array(
							"user_name" => 'Administrator',
							"user_email" => $this->site_configs['site_email'],
							"message" => $messageText2,
							"subject" => "Advertisement posted"
						);	
					$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);

					/*$checkEmail = $this->SuperModel->Super_Get(T_USERS,T_ADVERTISEMENTS_CONST.'_email="'.$data[T_ADVERTISEMENTS_CONST."_email"].'"','fetch');*/

					if($isInsert->success){
						/*$this->frontSession['successMsg'] = $this->layout()->translator->translate("thank_you_txt");
						return $this->redirect()->toUrl(APPLICATION_URL.'/advertise-with-us');*/

						$return_data['status'] = '200';
						$return_data['message'] = $this->layout()->translator->translate("thank_you_txt");
						echo json_encode($return_data);
						exit;

					} else {
						/*$this->frontSession['errorMsg'] = $this->layout()->translator->translate("some_error_occurred_txt");
						return $this->redirect()->toUrl(APPLICATION_URL.'/advertise-with-us');*/

						$return_data['status'] = '400';
						$return_data['message'] = $this->layout()->translator->translate("some_error_occurred_txt");
						echo json_encode($return_data);
						exit;
					}

				} else {				 
					// $this->frontSession['errorMsg'] = $this->layout()->translator->translate("check_your_information_txt");
					$return_data['status'] = '400';
					$return_data['message'] = $this->layout()->translator->translate("check_your_information_txt");
					echo json_encode($return_data);
					exit;
				}
		    }
			
		}

		$view->setVariable('pageTitle','ADVERTISEMENT FORM');
		$view->setVariable('site_configs',$this->site_configs);
		$view->setVariable('form',$adsForm);
		// $view->setTemplate('application/static/advertisewithusrequest.phtml');
		return $view;
	}

	/* CMS page: Acceptable Use Policy */
	public function acceptableusepolicyAction()
    {
		$pageType = "acceptableusepolicy";
		$view = $this->getpagecontent('7',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}

	/* CMS page: Terms and Conditions for Clubs */
	public function termsandconditionsAction()
    {
		$pageType = "terms";
		$view = $this->getpagecontent('8',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}
	public function securityAction()
    {
		$pageType = "security";
		$view = $this->getpagecontent('9',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}
	


	public function alltestimonialsAction()
	{
		$this->layout()->setVariable('pageType','testimonials');
	}	

	public function faqAction()
    {
		$view = new ViewModel();
		$faqData=$this->SuperModel->Super_Get(T_FAQ,"1","fetchall",array());

	
		$view->setVariable('faqData',$faqData); 
		$view->setVariable('SuperModel',$this->SuperModel); 
		
		$this->layout()->setVariable('pageType','faq');
		return $view;
	}
	
	public function colordetailsAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $request->getPost();
			$get_sizes = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID and color_title =:CID","fetchAll",array('warray'=>array('TID'=>$data["prod"],'CID'=>$data["tid"])));
			$fixed_sizes = array_column($get_sizes,"color_size");
			$price = $get_sizes[0]["color_price"];
			$qty = $get_sizes[0]["color_qty"]; 
			$view = new ViewModel();
			$view->setVariable('sizes',$fixed_sizes);
			$view->setVariable('price',$price);
			$view->setVariable('qty',$qty);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	function productdetailAction(){
		$key = $this->params()->fromRoute('key');
		$product = explode("~",$key);
		$product_key = $product[1];
		$joinArr = array(
			'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id', '2' => 'Left', '3' => array('category_feild')),
			'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus',T_CLIENT_VAR."client_stripe_id",T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
			'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval','store_closed','store_closed_date','store_closed_tilldate')),
			'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
		);
		$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_status = '1' and store_approval = '1' and product_delstatus != '1' and yurt90w_client_stripe_id != ''","fetch",array('warray'=>array('PID'=>base64_decode($product_key))),$joinArr);
		if(empty($product_data)) {
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Product is not available at the moment.");
			return $this->redirect()->toUrl(APPLICATION_URL);
		}
		$joinArr1 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR."client_stripe_id")),
		);
		$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID and yurt90w_client_stripe_id != ''","fetch",array('fields'=>array('store_name','store_banner','store_closed','store_closed_date','store_closed_tilldate','store_description','store_title'),'warray'=>array('UID'=>$product_data["product_clientid"])),$joinArr1);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$wish_data = $this->SuperModel->Super_Get(T_WISHLIST,"wish_list_productid =:PID and wish_list_clientid =:UID","fetch",array('warray'=>array('PID'=>base64_decode($product_key),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
			$report_data = $this->SuperModel->Super_Get(T_PRODREPORT,"product_report_pid =:PID and product_report_uid=:UID","fetch",array('warray'=>array('PID'=>base64_decode($product_key),'UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})));
		}
		$familiar_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_clientid =:CID and product_id != :TID and product_status = '1' and product_delstatus != '1' and yurt90w_client_stripe_id != ''","fetchAll",array('limit'=>8,'warray'=>array('CID'=>$product_data["product_clientid"],'TID'=>base64_decode($product_key))),$joinArr);
		
		$other_conjurings = $this->SuperModel->Super_Get(T_PRODUCTS,"product_category !=:CID and product_id != :TID and product_status = '1' and product_delstatus != '1' and yurt90w_client_stripe_id != ''","fetchAll",array('limit'=>8,'warray'=>array('CID'=>$product_data["product_category"],'TID'=>base64_decode($product_key))),$joinArr);
		
		$joinArr2 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'procomment_uid = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR."client_stripe_id")),
		);
		$comment_record = $this->SuperModel->Super_Get(T_PRODCOMMENT,"procomment_pid =:PID and yurt90w_client_stripe_id != ''","fetchAll",array('limit'=>'5','order'=>'procomment_date desc','warray'=>array('PID'=>base64_decode($product_key))),$joinArr2);
		$joinArr4 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'review_from = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image',T_CLIENT_VAR.'client_stripe_id')),
		);
		$review_record = $this->SuperModel->Super_Get(T_REVIEWS,"review_prodid =:PID","fetchAll",array('order'=>'review_id desc','warray'=>array('PID'=>base64_decode($product_key))),$joinArr4);
		$review_rating = $this->SuperModel->Super_Get(T_REVIEWS,"review_prodid =:PID","fetch",array('warray'=>array('PID'=>base64_decode($product_key)),'fields'=>array('avgreview'=>new Expression('AVG(review_starrating)'))));
		$colors_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID","fetchAll",array('warray'=>array('TID'=>base64_decode($product_key))));
		$colors_arr = array();
		$sizes_arr = array();
		$total_qty = 0; $used_qty = 0;
		if($product_data["product_isdigital"] != '1') {
			if(!empty($colors_data)) {
				foreach($colors_data as $colors_data_key => $colors_data_val) {
					$colors_arr[] = $colors_data_val["color_title"];
					$sizes_arr[] = $colors_data_val["color_size"];
					$prices_arr[] = $colors_data_val["color_price"];
					$total_qty += $colors_data_val["color_qty"]; 
					$qty_arr[$colors_data_val["color_title"]] += $colors_data_val["color_qty"];
				}
			}
			$get_sizes = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID and color_title =:CID","fetchAll",array('warray'=>array('TID'=>base64_decode($product_key),'CID'=>$colors_arr[0])));
			$fixed_sizes = array_column($get_sizes,"color_size");
			$available_qty = $total_qty;
			$product_balance = $prices_arr[0];
			$qty_balance = $get_sizes[0]["color_qty"];
		} else {
			$available_qty = $product_data["product_qty"];
			$product_balance = $product_data["product_price"];
		}
		$colors_arr = array_unique($colors_arr);
		$sizes_arr = array_unique($sizes_arr);
		usort($sizes_arr, "sizecmp");
		$view = new ViewModel();
		$view->setVariable('product_data',$product_data);
		$view->setVariable('store_data',$store_data);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$view->setVariable('loggedUser',$this->loggedUser);
		}
		if(!empty($wish_data)) {
			$view->setVariable('wish_data',$wish_data);
		}
		if(!empty($report_data)) {
			$view->setVariable("report_data",$report_data);
		}
		if(!empty($familiar_products)) {
			$view->setVariable('familiar_products',$familiar_products);
		}
		if(!empty($other_conjurings)) {
			$view->setVariable('other_conjurings',$other_conjurings);
		}
		if(!empty($comment_record)) {
			$view->setVariable('comment_record',$comment_record);
		}
		if(!empty($review_record)) {
			$view->setVariable('review_record',$review_record);
		}
		if(!empty($review_rating)) {
			$view->setVariable('avg_rating',$review_rating["avgreview"]);
		}
		if(!empty($colors_arr)) {
			$view->setVariable('colors_arr',$colors_arr);
		}
		if(!empty($sizes_arr)) {
			$view->setVariable('sizes_arr',$sizes_arr);
		}
		if(!empty($qty_arr)) {
			$view->setVariable('qty_arr',$qty_arr);
		}
		$view->setVariable('available_qty',$available_qty);
		$view->setVariable('digital_text',$this->site_configs["digital_text"]);
		$view->setVariable('product_balance',$product_balance);
		$view->setVariable('qty_balance',$qty_balance);
		$view->setVariable('fixed_sizes',$fixed_sizes);
		return $view;
	}

    function getpagecontent($pageid,$pageTag,$viewTemplate=1)
    {	
		$page_content=$this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>$pageid)));
		
		$page_content['page_content_'.$_COOKIE['currentLang']] = str_ireplace(array('{last_updated}','{img_url}','{site_images}','{site_path}','{last_updated_on}'),array(date("d F, Y ",strtotime($page_content['page_updated'])),HTTP_IMG_PATH,HTTP_IMG_PATH,APPLICATION_URL, date("d-m-Y",strtotime($page_content['page_updated'])) ),$page_content['page_content_'.$_COOKIE['currentLang']]);

		// Pass form variable to view
		$view = new ViewModel();
		$this->layout()->setVariable('pageTag',$pageTag);
		$this->layout()->setVariable('pageHeading',$page_content['page_title_'.$_COOKIE['currentLang']]);
		if($viewTemplate==1){
			$view->setTemplate('application/static/index.phtml');
		}
		$view->setVariable('pageHeading',$page_content['page_title_'.$_COOKIE['currentLang']]);
		$view->setVariable('page_content',$page_content);

		$this->layout()->setVariable("metatitle",$page_content['page_meta_title']);
		$this->layout()->setVariable("metaKeyword",$page_content['page_meta_keyword']);
		$this->layout()->setVariable("metaDescription",$page_content['page_meta_desc']);

		$view->setVariable('pageTag',$pageTag);
		return $view;
	}
	
	public function lasttestamentAction() {
		$pageType = "testament";
		$view = $this->getpagecontent('16',$pageType);
		$this->layout()->setVariable('pageType',$pageType);
		$view->setTemplate('application/static/index.phtml');
		return $view;
	}
	
	public function howitworksAction()
    {
		
		$page_id=14;
		$page=$this->SuperModel->Super_Get(T_PAGE_CONTENT, 'page_content_page_id=:pageids','fetchAll',array("warray"=>array("pageids"=>$page_id)));
		$pagenew=array();
		foreach($page as $key=>$value){
		    $pagenew[$value['page_content_section_key']]=$value['section_content'];
		}
		$first_section = $this->SuperModel->Super_Get(T_CONTENT,"content_id = '1'","fetch");
		$second_section = $this->SuperModel->Super_Get(T_CONTENT,"content_id = '2'","fetch");
		$third_section = $this->SuperModel->Super_Get(T_CONTENT,"content_id = '3'","fetch");
		$fourth_section = $this->SuperModel->Super_Get(T_CONTENT,"content_id = '4'","fetch");
		$photo_gallery = $this->SuperModel->Super_Get(T_PHOTO_GALLERY,"1","fetchAll");
		$view = $this->getpagecontent('14','howitworks');
		$this->layout()->setVariable('pageType','howitworks');
		$view->setVariable('page',$pagenew);
		$view->setVariable('first_section',$first_section);
		$view->setVariable('second_section',$second_section);
		$view->setVariable('third_section',$third_section);
		$view->setVariable('fourth_section',$fourth_section);
		$view->setVariable('photo_gallery',$photo_gallery);
		$view->setTemplate('application/static/howitworks.phtml');
		return $view;
		
		
		
	}

	public function blogAction()
    {
		/*require_once ROOT_PATH."/vendor/google/src/Google_Client.php";
		require_once ROOT_PATH."/vendor/google/src/contrib/Google_CalendarService.php";
		require_once ROOT_PATH."/vendor/google/src/contrib/Google_Oauth2Service.php";
		require_once ROOT_PATH."/vendor/google/src/auth/Google_AssertionCredentials.php";
		$client_id = '978141620833-h7as775rp090pc76qtc0sfvc8ba8ddru.apps.googleusercontent.com'; //change this
    	$Email_address = 'facilarent@facilarent.iam.gserviceaccount.com'; //change this
    	$key_file_location = ROOT_PATH."/vendor/facilarent-5b0e5ecbeede.json"; //change this
		$client = new\Google_Client();
    	$client->setApplicationName("Facilarent");
    	$key = file_get_contents($key_file_location);
		$scopes = "https://www.googleapis.com/auth/calendar";
    	$cred = new\Google_AssertionCredentials(
        $Email_address, array($scopes), $key
    	);
    	$client->setAssertionCredentials($cred);
		prd($client);
		if ($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($cred);
		}
    	$service = new Google_Service_Calendar($client);
    	$calendarList = $service->calendarList->listCalendarList();
 		prd($calendarList);*/
		
		$joinArr = array(
			'0' => array('0' => T_BLOG_CATEGORY, '1' => 'blog_catid = blog_category_id', '2' => 'Left', '3' => array('blog_category_title')),
		);	
		$page = $this->params()->fromRoute('page',1);
		$view = new ViewModel();	
		$blog_data=$this->SuperModel->Super_Get(T_BLOG,"blog_status = '1'","fetchAll",array('order'=>'blog_date desc'),$joinArr);
		$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($blog_data));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(3);
		$view->setVariable('paginator',$paginator);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$view->setVariable('loggedUser',$this->loggedUser->{T_CLIENT_VAR.'client_id'});
		} else {
			$view->setVariable('loggedUser','0');
		}
		$view->setVariable('page',$page);
		$view->setVariable('configs',$this->site_configs);
		return $view;		
	}

	
	public function blogdetailAction(){
		$blog = $this->params()->fromRoute('blog');
		$blog = explode("~",$blog);
		$views_data["blog_view_blogid"] = base64_decode($blog[1]);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$views_data["blog_view_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
		} else {
			$views_data["blog_view_clientid"] = '0';
		}
		$views_data["blog_view_date"] = date("Y-m-d H:i:s");
		$this->SuperModel->Super_Insert(T_BLOG_VIEWS,$views_data);
		$blog_views = $this->SuperModel->Super_Get(T_BLOG_VIEWS,"blog_view_blogid =:TID","fetchAll",array('warray'=>array('TID'=>base64_decode($blog[1]))));
		$joinArr = array(
			'0' => array('0' => T_BLOG_CATEGORY, '1' => 'blog_catid = blog_category_id', '2' => 'Left', '3' => array('blog_category_title')),
		);
		$blog_detail = $this->SuperModel->Super_Get(T_BLOG,"blog_id =:TID and blog_status = '1'","fetch",array('warray'=>array('TID'=>base64_decode($blog[1]))),$joinArr);
		if(empty($blog_detail)) {
		    $this->frontSession['errorMsg'] = "No such blog found.";
			return $this->redirect()->tourl(APPLICATION_URL);    
		}
		$joinArr2 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'comment_clientid = ' . T_CLIENT_VAR . 'client_id' . '', '2' => 'Left', '3' => array(T_CLIENT_VAR . 'client_name', T_CLIENT_VAR . 'client_image'))
		);
		$all_comments = $this->SuperModel->Super_Get(T_BLOG_COMMENT,"comment_blogid =:TID and comment_status = '1'","fetchAll",array('order'=>'comment_date desc','warray'=>array('TID'=>base64_decode($blog[1]))),$joinArr2);
		$view = new ViewModel();
		$view->setVariable('blog_detail',$blog_detail);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$view->setVariable('loggedUser',$this->loggedUser->{T_CLIENT_VAR.'client_id'});
		} else {
			$view->setVariable('loggedUser','0');
		}
		$view->setVariable('blog_views',count($blog_views));
		$view->setVariable('all_comments',$all_comments);
		return $view;	
	}

	

}




