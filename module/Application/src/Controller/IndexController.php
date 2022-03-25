<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Front website: Index controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Db\Sql\Expression;
use Application\Form\StaticForm;

class IndexController extends AbstractActionController
{
	private $AbstractModel, $EmailModel, $Adapter,$front_Session,$authService,$config_data;

	/* Constructor of the controller */
	public function __construct($AbstractModel,$EmailModel,$Adapter,$front_Session,AuthenticationServiceInterface $authService,$config_data)
    {
		$this->SuperModel = $AbstractModel;
		$this->frontSession = $front_Session;
		$this->EmailModel = $EmailModel;
		$this->Adapter = $Adapter;
		$this->loggedUser = $authService->getIdentity();
		$this->authService = $authService;
		$this->SITE_CONFIG = $config_data;
    }
	
	public function checkunloadAction() {
		if($this->getRequest()->isPost()){
			$posted_data = $this->getRequest()->getPost();
			$chat_data["chat_by"] = '7';
			$chat_data["chat_to"] = '8';
			$chat_data["chat_text"] = 'Test';
			$chat_data["chat_date"] = date("Y-m-d H:i:s");
			$this->SuperModel->Super_Insert(T_CHAT,$chat_data);
		}
	}
	
	public function optimizepicsAction() {
	    require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Exception.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/ResultMeta.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Result.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Source.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify/Client.php");
		require_once(ROOT_PATH."/vendor/tinify-php-master/lib/Tinify.php");
		\Tinify\setKey(TINY_KEY);
	    $joinArr = array(
			'0' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus',T_CLIENT_VAR."client_stripe_id",T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
			'1' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid','2'=>"Inner",'3'=>array("store_approval")),
			'2'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
		);
	    $favorite_products = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_delstatus != '1'","fetchAll",array('fields'=>array('product_defaultpic','product_photos'),'order'=>'product_id desc','limit'=>10,'offset'=>0),$joinArr);
        $favorite_products = array_column($favorite_products,"product_photos");
        foreach($favorite_products as $favorite_products_key => $favorite_products_val) {
            /*if(!empty($favorite_products_val["product_defaultpic"])) {
                if(file_exists(PRODUCT_PIC_PATH."/".$favorite_products_val["product_defaultpic"])) {	
				    $source = \Tinify\fromFile(PRODUCT_PIC_PATH."/".$favorite_products_val["product_defaultpic"]);
				    $source->toFile(PRODUCT_PIC_PATH."/".$favorite_products_val["product_defaultpic"]);
				}
            }*/
            $photos = explode(",",$favorite_products_val);
            foreach($photos as $photos_key => $photos_val) {
                $nameArr[] = $photos_val;
            }
        }
        foreach($nameArr as $photos_key => $photos_val) {
			if(file_exists(PRODUCT_PIC_PATH."/thumb/".$photos_val)) {	
			    $source = \Tinify\fromFile(PRODUCT_PIC_PATH."/thumb/".$photos_val);
				$source->toFile(PRODUCT_PIC_PATH."/thumb/".$photos_val);
			}
			if(file_exists(PRODUCT_PIC_PATH."/".$photos_val)) {	
			    $source = \Tinify\fromFile(PRODUCT_PIC_PATH."/".$photos_val);
				$source->toFile(PRODUCT_PIC_PATH."/".$photos_val);
			}
        }
        pr($nameArr);    
	    prd($favorite_products);
	}
	
	public function productmoretxtAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_id =:TID","fetch",array('fields'=>'product_description','warray'=>array('TID'=>myurl_decode($posted_data["tid"]))));
			$view = new ViewModel();
			$view->setVariable('product_text',strip_tags($product_data["product_description"]));
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function sellermoretxtAction() {
		if($this->getRequest()->isPost()) {
			$posted_data = $this->getRequest()->getPost();
			$store_data = $this->SuperModel->Super_Get(T_STORE,"store_clientid =:UID","fetch",array('fields'=>'store_description','warray'=>array('UID'=>myurl_decode($posted_data["tid"]))));
			$view = new ViewModel();
			$view->setVariable('store_text',strip_tags($store_data["store_description"]));
			$view->setTerminal(true);
			return $view;
		}
	}

    /* Home page */
	public function indexAction()
	{
		$this->layout()->setVariable('pageType',"home");
		$configs = $this->SITE_CONFIG;
		$HomePageData = $this->SuperModel->Super_Get(T_HOMEPAGE,"1","fetchall");
		$HomePageData[1]['home_content'] = str_ireplace(array('{img_url}','{site_path}'),array(HTTP_IMG_PATH,APPLICATION_URL),$HomePageData[1]['home_content']);
		$category_list = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"1","fetchAll",array('limit'=>'5','offset'=>'0'));
		/* Banner block data */
		$homePageBannerData = $this->getpagecontent(13);
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
		/*$url = "https://api.curator.io/restricted/feeds/f2cdf55b-555d-4b42-be54-633b0ef1047d/posts?limit=12&hasPoweredBy=false&version=1.0";
		//$url = 'https://www.instagram.com/thecollectivecoven/?__a=1';
		$ch = curl_init();
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Set the url
		curl_setopt($ch, CURLOPT_URL,$url);
		// Execute
		$result=curl_exec($ch);
		// Closing
		curl_close($ch);
		$response = json_decode($result, true);*/
		return new ViewModel(array(
			'HomePageData'=>$HomePageData,
			'homePageBannerData'=>$homePageBannerData,
			'configs'=>$configs,
			'loggedUser'=>$this->loggedUser,
			'product_categories'=>$category_list,
			'recent_products'=>$recent_products,
			'recent_products2'=>$recent_products2,
			'favorite_products'=>$favorite_products,
			'favorite_products2'=>$favorite_products2,
			'favorite_stores' => $favorite_stores
        ));
	}
	
	public function showsizesAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $request->getPost();
			$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:TID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["pid"]),'TID'=>strtolower($data["tid"]))));
			$sizes_arr = array_column($colorsize_data,"color_size");
			$sizes_arr = array_unique($sizes_arr);
			usort($sizes_arr, "sizecmp");
			$price = $colorsize_data[0]["color_price"];
			$view = new ViewModel();
			$view->setVariable('sizes_arr', $sizes_arr);
			$view->setVariable('price',$price);
			$view->setVariable('prod_id',base64_decode($data["pid"]));
			$view->setVariable('prod_qty',$colorsize_data[0]["color_qty"]);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function pickquantityAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $request->getPost();
			$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:TID and color_size =:SID","fetch",array('warray'=>array('PID'=>base64_decode($data["pid"]),'TID'=>strtolower($data["cid"]),'SID'=>$data["sid"])));
			$price = $colorsize_data["color_price"];
			$sendData['price'] = $price;
			$sendData['qty'] = $colorsize_data["color_qty"];
			$sendData["status"] = 'S';
			echo json_encode($sendData);
			exit();
		}
	}
	
	public function showpriceAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('warray'=>array('PID'=>base64_decode($data["tid"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				$data["qty"] = strip_tags($data["qty"]);
				if (!is_numeric($data["qty"]) && $product_data["product_isdigital"] != '1')
				{
					echo "invalid_number";
					exit();
				}
				if($product_data["product_isdigital"] == '1') {
					$data["qty"] = 1;
				}
				$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID","fetchAll",array('order'=>'color_id asc','warray'=>array('PID'=>base64_decode($data["tid"]),'CID'=>strtolower($data["color"]),'SID'=>$data["size"])));
				$available_qty = 0;
				$break_cond = '';
				if(!empty($colorsize_data)) {
					foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
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
				}
				if(empty($available_qty)) {
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
				if($product_data["product_isdigital"] == '1') {
					$product_price = $product_data["product_price"];
				} else {
					$product_price = $product_price * $data["qty"];
				}
				if(!empty($data["coupon_code"])) {
					$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_code =:TID and coupon_status = '1'","fetch",array('warray'=>array('TID'=>$data["coupon_code"])));
					$coupon_arr = explode(",",$coupon_data["coupon_product"]);
					if(empty($coupon_data)) { echo "invalid_coupon"; exit(); }
					else if(strtotime($coupon_data["coupon_end_date"]) < strtotime(date("Y-m-d")))
					{
						echo "invalid_coupon"; exit();
					} elseif(strtotime($coupon_data["coupon_start_date"]) > strtotime(date("Y-m-d")))
					{
						echo "invalid_coupon"; exit();
					} else {
						if($coupon_data["coupon_type"] == '2' || in_array($product_data["product_id"], $coupon_arr)) {
							$coupon_discount = bcdiv(($coupon_data["coupon_discount"] / 100) * $product_price,1,2);
							$product_price = $product_price - $coupon_discount;
						}
					}
				}
				echo bcdiv($product_price,1,2);
				exit();
			}
		}
	}
	
	public function addmycartAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
			);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID and product_status = '1' and store_approval = '1'","fetch",array('warray'=>array('PID'=>base64_decode($data["tid"]))),$joinArr);
			if(empty($product_data)) {
				echo "error";
				exit();
			} else {
				if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'}) && $this->loggedUser->{T_CLIENT_VAR.'client_id'} == $product_data["product_clientid"])
				{
					echo "restricted";
					exit();
				}		
				if($data["qty"] < 1 && $product_data["product_isdigital"] != '1') {
					echo "invalid_qty";
					exit();
				}
				$available_qty = 0;
				if($product_data["product_isdigital"] != '1') {
				$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:TID and color_slug =:CID and color_size =:SID","fetchAll",array('warray'=>array('TID'=>base64_decode($data["tid"]),'CID'=>strtolower($data["color"]),'SID'=>$data["size"])));
				if(!empty($colorsize_data)) {
					foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
						$available_qty += $colorsize_data_val["color_qty"];
					}
				} } else {
					$data["qty"] = 1;
					$available_qty = $product_data["product_qty"];
				}
				if($data["qty"] > $available_qty && $product_data["product_isdigital"] != '1') {
					echo "qtyerror";
					exit();
				}
				if($product_data["product_isdigital"] == '1') {
					$product_price = $product_data["product_price"];
				} else {
					$priceqty_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_size =:SID and color_title =:CID","fetchAll",array('warray'=>array('PID'=>base64_decode($data["tid"]),'SID'=>$data["size"],'CID'=>$data["color"])));
					if(!empty($priceqty_data)) {
						foreach($priceqty_data as $priceqty_data_key => $priceqty_data_val) {
							/* i commented if($data["qty"] <= $priceqty_data_val["color_qtyto"]) {
								$product_prices[] =  $priceqty_data_val["color_price"];
							}*/
							if($break_cond == '') {
								if(($priceqty_data_val["color_qtyfrom"] <= $data["qty"]) && ($data["qty"] <= $priceqty_data_val["color_qtyto"])) {
									$available_qty = $priceqty_data_val["color_qty"];
									$product_price = (float) $priceqty_data_val["color_price"];
									$break_cond = 1;
								} else if($data["qty"] > $priceqty_data_val["color_qtyfrom"] && !($priceqty_data_val["color_qtyfrom"] <= $data["qty"]) && ($data["qty"] <= $priceqty_data_val["color_qtyto"])) {
									$available_qty = $priceqty_data_val["color_qty"];
									$product_price = (float) $priceqty_data_val["color_price"];
									$break_cond = 1;
								}
							}
						}
					}
					if(empty($product_price)) {
						foreach($priceqty_data as $colorsize_data_key => $colorsize_data_val) {
							if($data["qty"] >= $colorsize_data_val["color_qtyfrom"]) {
								$available_qty = $colorsize_data_val["color_qty"];
								$product_price = (float) $colorsize_data_val["color_price"];
								$break_cond = 1;
							}
						}
					}
					/*if(empty($product_price)) {
						$product_price = (float) end($priceqty_data)["color_price"];
					}*/
					$product_price = $product_price * $data["qty"];
					// i commented $product_price = $product_prices[0] * $data["qty"];
					//$product_price = $product_data["product_price"] * $data["qty"];
				}
				$coupon_discount = 0;
				if(!empty($data["coupon_code"])) {
					$coupon_data = $this->SuperModel->Super_Get(T_COUPONS,"coupon_code =:TID and coupon_status = '1'","fetch",array('warray'=>array('TID'=>$data["coupon_code"])));
					$coupon_arr = explode(",",$coupon_data["coupon_product"]);
					if(empty($coupon_data)) {
						echo "invalid_coupon";
						exit();
					}
					else if(strtotime($coupon_data["coupon_end_date"]) < strtotime(date("Y-m-d")))
					{
						echo "invalid_coupon";
						exit();
					} elseif(strtotime($coupon_data["coupon_start_date"]) > strtotime(date("Y-m-d")))
					{
						echo "invalid_coupon";
						exit();
					} else {
						if($coupon_data["coupon_type"] == '2' || in_array($product_data["product_id"], $coupon_arr)) {
							$coupon_discount = bcdiv(($coupon_data["coupon_discount"] / 100) * $product_price,1,2);
							$product_price = $product_price - $coupon_discount;
						}
					}
				}
				$cart_data["product_cart_prodid"] = $product_data["product_id"];
				$cart_data["product_cart_price"] = $product_price;
				$cart_data["product_cart_qty"] = $data["qty"];
				$cart_data["product_cart_coupon"] = $data["coupon_code"];
				$cart_data["product_cart_date"] = date("Y-m-d");
				$cart_data["product_cart_discount"] = $coupon_discount;
				$cart_data["product_cart_color"] = $data["color"];
				$cart_data["product_cart_size"] = $data["size"];
				$cart_data["product_cart_note"] = strip_tags($data["note"]);
				if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
					$prodcart_data = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_prodid =:PID and product_cart_clientid =:UID and product_cart_color =:CID and product_cart_size =:SID","fetch",array('warray'=>array('PID'=>$product_data["product_id"],'UID'=>$this->loggedUser->{T_CLIENT_VAR."client_id"},'CID'=>$cart_data["product_cart_color"],'SID'=>$cart_data["product_cart_size"])));
					if(!empty($prodcart_data)) {
						$cart_dataz["product_cart_qty"] = $data["qty"] + $prodcart_data["product_cart_qty"];
						if($available_qty < $cart_dataz["product_cart_qty"]) {
							echo "qtyfull";
							exit();
						}
						$cart_dataz["product_cart_price"] = $cart_data["product_cart_price"] + $prodcart_data["product_cart_price"];
						$cart_dataz["product_cart_note"] = $cart_data["product_cart_note"];
						$this->SuperModel->Super_Insert(T_PRODCART,$cart_dataz,"product_cart_prodid = '".$product_data["product_id"]."'");
					} else {
						$cart_data["product_cart_clientid"] = $this->loggedUser->{T_CLIENT_VAR.'client_id'};
						$this->SuperModel->Super_Insert(T_PRODCART,$cart_data);
					}
				} else {
					$prod_arrs = array_column($_SESSION["cartArr"],"product_cart_prodid");
					$prodcolor_arrs = array_column($_SESSION["cartArr"],"product_cart_color");
					$prodsize_arrs = array_column($_SESSION["cartArr"],"product_cart_size");
					$prodcart_arr = array();
					if(!empty($prod_arrs)) {
						foreach($prod_arrs as $prod_arrs_key => $prod_arrs_val) {
							$prodcart_arr[$prod_arrs_val] = $prod_arrs_key;
						}
					}
					if(!empty($prodsize_arrs)) {
						foreach($prodsize_arrs as $prodsize_arrs_key => $prodsize_arrs_val) {
							$prodsize_arr[$prodsize_arrs_val] = $prodsize_arrs_key;
						}
					}
					if(!empty($prodcolor_arrs)) {
						foreach($prodcolor_arrs as $prodcolor_arrs_key => $prodcolor_arrs_val) {
							$prodcolor_arr[$prodcolor_arrs_val] = $prodcolor_arrs_key;
						}
					}
					if(isset($prodcart_arr[$cart_data["product_cart_prodid"]]) && isset($prodsize_arr[$cart_data["product_cart_size"]]) && isset($prodcolor_arr[$cart_data["product_cart_color"]])){
						$cart_dataz["product_cart_qty"] = $data["qty"] + $_SESSION["cartArr"][$prodcart_arr[$cart_data["product_cart_prodid"]]]["product_cart_qty"];
						if($available_qty < $cart_dataz["product_cart_qty"]) {
							echo "qtyfull";
							exit();
						}
						$cart_dataz["product_cart_price"] = $cart_data["product_cart_price"] + $_SESSION["cartArr"][$prodcart_arr[$cart_data["product_cart_prodid"]]]["product_cart_price"];
						$_SESSION["cartArr"][$prodcart_arr[$cart_data["product_cart_prodid"]]]["product_cart_qty"] = $cart_dataz["product_cart_qty"];
						$_SESSION["cartArr"][$prodcart_arr[$cart_data["product_cart_prodid"]]]["product_cart_price"] = $cart_dataz["product_cart_price"];
						$_SESSION["cartArr"][$prodcart_arr[$cart_data["product_cart_prodid"]]]["product_cart_note"] = $cart_dataz["product_cart_note"];
					} else {
						$_SESSION["cartArr"][] = $cart_data;
					}
				}
				echo "success";
				exit();
			}
		}
	}
	
	public function shoppingcartAction() {
		require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
		\Stripe\Stripe::setApiKey($this->SITE_CONFIG['site_secret_key']);
		if(!empty($this->loggedUser->{T_CLIENT_VAR.'client_id'})) {
			$joinArr = array(
				'0'=>array('0'=>T_PRODUCTS,'1'=>'product_cart_prodid = product_id','2'=>'Left','3'=>array('product_title','product_price','product_photos','product_defaultpic','product_clientid','product_globalrate','product_isdigital','product_qty')),
				'1'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				'2'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Inner','3'=>array('store_id','store_name','store_approval')),
			);
			$cart_data = $this->SuperModel->Super_Get(T_PRODCART,"product_cart_clientid =:UID and product_delstatus != '1'","fetchAll",array('warray'=>array('UID'=>$this->loggedUser->{T_CLIENT_VAR.'client_id'})),$joinArr);
		} else {
			if(!empty($_SESSION["cartArr"])) {
				foreach($_SESSION["cartArr"] as $cart_arr_key => $cart_arr_val) {
					$joinArr = array(
						'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
						'1'=>array('0'=>T_STORE,'1'=>'product_clientid = store_clientid','2'=>'Inner','3'=>array('store_name','store_approval')),
					);
					$productz_data[] = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id = :TID","fetch",array('fields'=>array('product_title','product_price','product_photos','product_defaultpic','product_clientid','product_globalrate','product_isdigital','product_qty'),'warray'=>array('TID'=>$cart_arr_val["product_cart_prodid"])),$joinArr);
					$productz_data[$cart_arr_key]["product_cart_prodid"] = $cart_arr_val["product_cart_prodid"];
					$productz_data[$cart_arr_key]["product_cart_price"] = $cart_arr_val["product_cart_price"];
					$productz_data[$cart_arr_key]["product_cart_qty"] = $cart_arr_val["product_cart_qty"];
					$productz_data[$cart_arr_key]["product_cart_coupon"] = $cart_arr_val["product_cart_coupon"];
					$productz_data[$cart_arr_key]["product_cart_date"] = $cart_arr_val["product_cart_date"];
					$productz_data[$cart_arr_key]["product_cart_discount"] = $cart_arr_val["product_cart_discount"];
					$productz_data[$cart_arr_key]["product_cart_color"] = $cart_arr_val["product_cart_color"];
					$productz_data[$cart_arr_key]["product_cart_size"] = $cart_arr_val["product_cart_size"];
					$productz_data[$cart_arr_key]["product_cart_delivery"] = $cart_arr_val["product_cart_delivery"];
				}
				$cart_data = $productz_data;
			}
		}
		if(empty($cart_data)) {
			$this->frontSession['errorMsg'] = $this->layout()->translator->translate("Cart is empty. Please add products in the cart.");
			return $this->redirect()->toUrl(APPLICATION_URL);
		}
		$this->SuperModel->Super_Delete(T_PRODORDER,"order_clientid = '".$this->loggedUser->{T_CLIENT_VAR."client_id"}."' and order_status = 5");
		$conshop_data = end($cart_data);
		$continue_link = APPLICATION_URL.'/shop/'.str_replace(" ","-",$conshop_data['store_name']);
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
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
					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_isdigital','product_qty'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
					$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetchAll",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
					$available_qty = 0;
					$break_cond = '';
					if($prod_data["product_isdigital"] != '1') {
					if(!empty($colorsize_data)) {
						foreach($colorsize_data as $colorsize_data_key => $colorsize_data_val) {
							if($break_cond == '') {
								if(($colorsize_data_val["color_qtyfrom"] <= $data["qty"]) && ($data["qty"] <= $colorsize_data_val["color_qtyto"])) {
									$available_qty = $colorsize_data_val["color_qty"];
									$product_price = (float) $colorsize_data_val["color_price"];
									$break_cond = 1;
								} else if($data["qty"] <= $colorsize_data_val["color_qtyfrom"]) {
									$available_qty = $colorsize_data_val["color_qty"];
									$product_price = (float) $colorsize_data_val["color_price"];
									$break_cond = 1;
								}
							}
						}
					} } else {
						$available_qty = $prod_data["product_qty"];
					}
					if($my_carts_val["product_cart_qty"] > $available_qty && $prod_data["product_isdigital"] != '1') {
						$sendData['response_code'] = 'error';
						$sendData["message"] = "Payment failed as ".$my_carts_val["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
						$sendData["status"] = 'Q';
						if($prod_data["product_isdigital"] != '1') {
							$this->frontSession['errorMsg'] = "Payment failed as ".$my_carts_val["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
						} else {
							$this->frontSession['errorMsg'] = "Payment failed as ".$my_carts_val["product_title"]." is out of stock.";
						}
						return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
						echo json_encode($sendData);
						exit();
					}
					$cart_title[] = $my_carts_val["product_title"];
					$_SESSION["product_cartzid"] = 	$my_carts_val["product_cart_prodid"];
					if($my_carts_val["product_cart_prodid"] == $_SESSION["product_cartzid"]) {
						$net_qty += $my_carts_val["product_cart_qty"];
					}
					if($net_qty > $available_qty && $prod_data["product_isdigital"] != '1') {
						$sendData['response_code'] = 'error';
						$sendData["message"] = "Payment failed as ".$my_carts_val["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
						$sendData["status"] = 'Q';
						if($prod_data["product_isdigital"] != '1') {
							$this->frontSession['errorMsg'] = "Payment failed as ".$my_carts_val["product_title"]." ( ".$my_carts_val["product_cart_color"]." color - ".$my_carts_val["product_cart_size"]." size) is out of stock.";
						} else {
							$this->frontSession['errorMsg'] = "Payment failed as ".$my_carts_val["product_title"]." is out of stock.";
						}
						return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
						echo json_encode($sendData);
						exit();
					}
				}
			}
			if(empty($my_carts)) {
				$this->frontSession['errorMsg'] = 'Please add products to the cart.';
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			if(strlen($data["credit_name"]) > 200) {
				$this->frontSession['errorMsg'] = 'Please enter the correct credit name.';
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			if(strlen($data["credit_number"]) > 19) {
				$this->frontSession['errorMsg'] = 'Please enter the correct credit number.';
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			if(strlen($data["credit_cvv"]) > 4) {
				$this->frontSession['errorMsg'] = 'Please enter the correct cvv code.';
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			}
			$cart_title = implode(", ",$cart_title);
			if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
				$total_amt = $all_carts["total"] + $shipping_amtdata["total"];
				$amount=bcdiv($total_amt,1,2);
			} else {
				$ship_amt = array_sum($ship_cartprice);
				$amount= array_sum($all_cartprice) + $ship_amt;
			}
			$credit_card = strip_tags(str_replace(" ","",$data["credit_number"]));
			$exp_month = strip_tags(str_replace(" ","",$data["credit_month"]));
			$exp_year = strip_tags(str_replace(" ","",$data["credit_year"]));
			$exp_date = $exp_month.$exp_year;
			$cvv = strip_tags(str_replace(" ","",$data["credit_cvv"]));
			$user_name = strip_tags(str_replace(" ","",$data["credit_name"]));
			try {
				$charge = \Stripe\Charge::create([
					'amount'   => $amount * 100,
					'currency' => 'USD',
					'source'=>  $data["token"],
				]);
				$response_body = accessProtected($charge,'_lastResponse');
				if($response_body->code == '200') {
					$resp_arr = json_decode($response_body->body);
					$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"1","fetchAll",array('group'=>'order_serial'));
					$serial_num = count($all_orders) + 2;
					foreach($my_carts as $my_carts_key =>$my_carts_val) {
						if(!empty($my_carts_val["product_cart_delivery"])) {
							$site_fee = ($this->SITE_CONFIG["site_commission"] / 100) * ($my_carts_val["product_cart_price"] + $my_carts_val["product_cart_delivery"]);
						} else {
							$site_fee = ($this->SITE_CONFIG["site_commission"] / 100) * $my_carts_val["product_cart_price"];
						}
						$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_isdigital','product_qty'),'warray'=>array('PID'=>$my_carts_val["product_cart_prodid"])));
						$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$prod_data["product_clientid"])));
						if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
							$clientid = $this->loggedUser->{T_CLIENT_VAR."client_id"};
						} else {
							$clientid = $_SESSION["user_Id"];
						}
						$orderData = array(
							"order_product" => $my_carts_val["product_cart_prodid"],
							"order_qty" => $my_carts_val["product_cart_qty"],
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
							"order_txnid" => $resp_arr->id,
							"order_note" => $my_carts_val["product_cart_note"]
						);					
						$this->SuperModel->Super_Insert(T_PRODORDER,$orderData);
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
							"user_email" => $this->SITE_CONFIG['site_email'],
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
							$colorsize_data = $this->SuperModel->Super_Get(T_PROQTY,"color_productid =:PID and color_slug =:CID and color_size =:SID ","fetch",array('warray'=>array('PID'=>$my_carts_val["product_cart_prodid"],'CID'=>strtolower($my_carts_val["product_cart_color"]),'SID'=>$my_carts_val["product_cart_size"])));
							$available_qty = 0;
							$prodqty = $colorsize_data["color_qty"] - $my_carts_val["product_cart_qty"];
							if($prodqty < 1) {
								$avl_data["color_qty"] = 0;
							} else {
								$avl_data["color_qty"] = $prodqty;
							}
							$this->SuperModel->Super_Insert(T_PROQTY,$avl_data,"color_productid = '".$my_carts_val["product_cart_prodid"]."' and color_slug = '".strtolower($my_carts_val["product_cart_color"])."' and color_size = '".$my_carts_val["product_cart_size"]."'");
						} else {
							$prod_qty["product_qty"] = $prod_data["product_qty"] - 1;
							$jj = $this->SuperModel->Super_Insert(T_PRODUCTS,$prod_qty,"product_id = '".$my_carts_val["product_cart_prodid"]."'");
						}
						$this->SuperModel->Super_Delete(T_PRODCART,"product_cart_id = '".$my_carts_val["product_cart_id"]."'");
						
					}	
					if(!empty($this->loggedUser->{T_CLIENT_VAR."client_id"})) {
						$this->frontSession['successMsg'] = 'You have successfully paid the amount for order number 51905296'.$serial_num.'.';
						return $this->redirect()->tourl(APPLICATION_URL.'/order-summary');
					} else {
						unset($_SESSION["cartArr"]);
						$this->frontSession['successMsg'] = 'You have successfully paid the amount for order number 51905296'.$serial_num.'. Please verify your email address and login to view your placed order.';
						return $this->redirect()->tourl(APPLICATION_URL.'/order-summary');
					}
				} else {
					$this->frontSession['errorMsg'] = 'Payment failed. Please try again';
					return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
				}
			} catch(\Stripe_CardError $e) {
				prd($e->getMessage());
			} catch (\Stripe_InvalidRequestError $e) {
				prd($e->getMessage());
			} catch (\Stripe_AuthenticationError $e) {
				prd($e->getMessage());
			} catch (\Stripe_Error $e) {
				prd($e->getMessage());
			} catch(\Stripe\Exception\CardException $e) {
				prd($e->getMessage());
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			} catch (\Stripe\Exception\RateLimitException $e) {
				prd($e->getMessage());
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			} catch (\Stripe\Exception\InvalidRequestException $e) {
				prd($e->getMessage());
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			  // Invalid parameters were supplied to Stripe's API
			} catch (\Stripe\Exception\AuthenticationException $e) {
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			  // Authentication with Stripe's API failed
			  // (maybe you changed API keys recently)
			} catch (\Stripe\Exception\ApiConnectionException $e) {	
				prd($e->getMessage());
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			  // Network communication with Stripe failed
			} catch (\Stripe\Exception\ApiErrorException $e) { 	
				prd($e->getMessage());
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			  // Display a very generic error to the user, and maybe send
			  // yourself an email
			} catch (\Exception $e) {
				$this->frontSession['errorMsg'] = $e->getMessage();
				return $this->redirect()->tourl(APPLICATION_URL.'/my-cart');
			  // Something else happened, completely unrelated to Stripe
			}
		}
		$all_countries = $this->SuperModel->Super_Get(T_COUNTRIES,"1","fetchAll");
		$view = new ViewModel();
		$view->setVariable('cart_data',$cart_data);
		$view->setVariable('loggedUser',$this->loggedUser);
		$view->setVariable('site_configs',$this->SITE_CONFIG);
		$view->setVariable('all_countries',$all_countries);
		$view->setVariable('continue_link',$continue_link);
		return $view;
	}
	
	public function getcartdetailsAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$data = $request->getPost();
			$joinArr = array(
				'0'=>array('0'=>T_CATEGORY_LIST,'1'=>'product_category = category_id','2'=>'Left','3'=>array('category_feild')),
				'1' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_clientid','store_approval','store_closed','store_closed_date','store_closed_tilldate','store_acceptorder')),
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
	
	public function chargeplanAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			if(!empty($posted_data["plan"])) {
				require_once(ROOT_PATH.'/vendor/stripe-php-master/init.php');
				\Stripe\Stripe::setApiKey($this->SITE_CONFIG["site_secret_key"]);
				$subscribed_users = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_planstatus = '1' and yurt90w_client_nextbilling =:TID","fetchAll",array('fields'=>array(T_CLIENT_VAR."client_id",T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email",T_CLIENT_VAR."client_customerid",T_CLIENT_VAR."client_planstatus",T_CLIENT_VAR."client_couponcode",T_CLIENT_VAR."client_coupon"),'warray'=>array('TID'=>date("Y-m-d"))));
				if(!empty($subscribed_users)) {
					foreach($subscribed_users as $subscribed_users_key => $subscribed_users_val) {
						$plan_amount = $this->SITE_CONFIG["plan_price"];
						$coupon_amount = $plan_amount;
						if(!empty($subscribed_users_val[T_CLIENT_VAR."client_couponcode"]) && $subscribed_users_val[T_CLIENT_VAR."client_coupon"] == '1') {
							$coupon_data = $this->SuperModel->Super_Get(T_MERCHANTCOUPON,"LOWER(merchantcoupon_code) =:TID","fetch",array('warray'=>array('TID'=>strtolower($subscribed_users_val[T_CLIENT_VAR."client_couponcode"]))));
							$coupon_discount = ($plan_amount * $coupon_data["merchantcoupon_discount"]) / 100;
							$coupon_amount = $plan_amount - $coupon_discount;
						}
						try {
							$plan_amount = (float) ($coupon_amount * 100);
							$charge = \Stripe\Charge::create([
							  'customer' => $subscribed_users_val[T_CLIENT_VAR."client_customerid"],
							  'amount'   => $plan_amount,
							  'currency' => 'USD'	
							]);
						} catch(\Stripe\Exception\CardException $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: ".$e->getError()->message,
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						} catch (Stripe_InvalidRequestError $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: Invalid card details.",
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						  // Invalid parameters were supplied to Stripe's API
						} catch (Stripe_AuthenticationError $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: Authentication failed.",
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						  // Authentication with Stripe's API failed
						  // (maybe you changed API keys recently)
						} catch (Stripe_ApiConnectionError $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: Network communication with Stripe failed.",
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						  // Network communication with Stripe failed
						} catch (Stripe_Error $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: Error occurred while transaction, please check your details.",
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						  // Display a very generic error to the user, and maybe send
						  // yourself an email
						} catch (Exception $e) {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription.<br/>Reason: Error occurred while charging your card.",
								"subject" => "Charge failed"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						  // Something else happened, completely unrelated to Stripe
						}
						if(!empty($charge['id']) && !empty($charge["status"]) && $charge["status"] == 'succeeded'){
							$data["store_approval"] = '1';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_customerid"] = $subscribed_users_val[T_CLIENT_VAR."client_customerid"];
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '1';
							$clt_data[T_CLIENT_VAR."client_nextbilling"] = date('Y-m-d', strtotime("+1 month"));
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "You have been charged for the monthly plan subscription.",
								"subject" => "You have been charged"
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
						} else {
							$data["store_approval"] = '2';
							$data["store_verifydeclinetxt"] = 'Charge for monthly subscription failed.';
							$isInsert = $this->SuperModel->Super_Insert(T_STORE,$data,"store_clientid = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");	
					
							$clt_data[T_CLIENT_VAR."client_planstatus"] = '2';
							$this->SuperModel->Super_Insert(T_CLIENTS,$clt_data,T_CLIENT_VAR."client_id = '".$subscribed_users_val[T_CLIENT_VAR."client_id"]."'");
							
							$mail_const_data2 = array(
								"user_name" => $subscribed_users_val[T_CLIENT_VAR."client_name"],
								"user_email" => $subscribed_users_val[T_CLIENT_VAR."client_email"],
								"message" => "Charge failed for the monthly subscription."
							);	
							$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
							exit();
						}
					}
				}
			}
		}
		exit();
	}
	
	public function viewmorecategoryAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			$category_list = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"1","fetchAll",array('limit'=>'5','offset'=>$posted_data["catnum"]));
			if(!empty($category_list)) {
			  $view = new ViewModel();
			  $view->setVariable('product_categories',$category_list);
			  $view->setTerminal(true);
			  return $view;	
			} else {
				echo "finished";
				exit();
			}
		}
	}
	
	public function getscrollcommentsAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["prod"]))));
			if(empty($product_data)) {
				echo "error";
				exit();
			}
			$joinArr2 = array(
			'0' => array('0' => T_CLIENTS, '1' => 'procomment_uid = '.T_CLIENT_VAR.'client_id', '2' => 'Left', '3' => array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_image')),
		);
			$comment_record = $this->SuperModel->Super_Get(T_PRODCOMMENT,"procomment_pid =:PID","fetchAll",array('offset'=>strip_tags($posted_data["start"]),'limit'=>'5','order'=>'procomment_date desc','warray'=>array('PID'=>base64_decode($posted_data["prod"]))),$joinArr2);
			$view = new ViewModel();
			$view->setVariable('comment_record',$comment_record);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setTerminal(true);
			return $view;	
		}
	}
	
	public function productbycategoryAction() {
		$page = $this->params()->fromRoute('page',1);
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			$joinArr2 = array(
				'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
				'1' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
			);
			$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_category = '".$posted_data["order"]."'","fetchAll",'',$joinArr2);
			$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($product_data));
			$paginator->setCurrentPageNumber($page);
			$paginator->setItemCountPerPage(9);
			$view = new ViewModel();
			$view->setVariable('paginator',$paginator);
			$view->setVariable('product_data', $product_data);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('page',$page);
			$view->setTerminal(true);
			return $view;
		} else {
			return $this->redirect()->tourl(APPLICATION_URL.'/product-listing/'.$page);
		}
	}
	
	public function productlistingAction() {
		$view = new ViewModel();
		$page = $this->params()->fromRoute('page',1);
		$category_listing = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_status = '1'","fetchAll");
		$category_arr = array();
		if(!empty($category_listing)) {
			foreach($category_listing as $category_listing_key => $category_listing_val) {
				$category_arr[$category_listing_val["category_id"]] = $category_listing_val["category_feild"];
			}
		}
		$kwhere = '';
		if(!empty($_GET["tag"])) {
			$tags = $_GET["tag"];
			$kwhere = " and (product_title like :KID or product_description like :KID or find_in_set('$tags',product_tags) <> 0)";
		}
		if(isset($_GET["search_txt"]) && !empty($_GET["search_txt"])) {
			$_GET["search_txt"] = trim(strip_tags($_GET["search_txt"]));
			if(strlen($_GET["search_txt"]) > 100) {
				$this->frontSession['errorMsg']="You cannot enter more than 100 characters in the search.";
				return $this->redirect()->tourl(APPLICATION_URL);
			}
			if(!empty($_GET["search_txt"])) {
				$joinArr2 = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus',T_CLIENT_VAR."client_stripe_id",'yurt90w_client_country',T_CLIENT_VAR."client_bestseller")),
					'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
					'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))	
				);
				if(!empty($_GET["tag"])) {
					$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1' and (product_title like :CID or product_description like :CID )".$kwhere,"fetchAll",array('warray'=>array('CID'=>'%'.$_GET["search_txt"].'%','KID'=>'%'.$_GET["tag"].'%'),'order'=>'product_id desc'),$joinArr2);
				} else {
					$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1' and (product_title like :CID or product_description like :CID )","fetchAll",array('warray'=>array('CID'=>'%'.$_GET["search_txt"].'%'),'order'=>'product_id desc'),$joinArr2);
				}
			} else {
				$product_data = array();
			}
		} else if(isset($_GET["category"]) && !empty($_GET["category"])) {
			$_GET["category"] = strip_tags($_GET["category"]);
			$categoryId = explode("~",$_GET["category"]);
			$category_data = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($categoryId[1]))));
			if(!empty($_GET["category"])) {
				$joinArr2 = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id','yurt90w_client_country',T_CLIENT_VAR.'client_bestseller')),
					'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
					'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1' and product_category =:CID".$kwhere,"fetchAll",array('warray'=>array('CID'=>base64_decode($categoryId[1])),'order'=>'product_id desc'),$joinArr2);
				$view->setVariable('current_category', base64_decode($categoryId[1]));
			} else {
				$product_data = array();
			}
			$category_title = $category_data["category_feild"];
			$category_desc = $category_data["category_description"];
		} else if(isset($_GET["subcategory"]) && !empty($_GET["subcategory"])) {
			$_GET["subcategory"] = strip_tags($_GET["subcategory"]);
			$subcategoryId = explode("~",$_GET["subcategory"]);
			if(!empty($_GET["subcategory"])) {
				$joinArr2 = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id','yurt90w_client_country',T_CLIENT_VAR.'client_bestseller')),
					'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
					'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1' and product_subcategory =:CID".$kwhere,"fetchAll",array('warray'=>array('CID'=>base64_decode($subcategoryId[1])),'order'=>'product_id desc'),$joinArr2);
				$view->setVariable('current_subcategory', base64_decode($subcategoryId[1]));
				$category_record = $this->SuperModel->Super_Get(T_SUBCATEGORY_LIST,"subcategory_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($subcategoryId[1]))));
				$cate_list = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_id =:TID","fetch",array('warray'=>array('TID'=>$category_record["subcategory_categoryid"])));
				$_GET["category"] = str_replace(" ","",str_replace("&","",$cate_list["category_feild"]))."~".str_replace("=","",base64_encode($category_record["subcategory_categoryid"]));
				$category_title = $cate_list["category_feild"];
			} else {
				$product_data = array();
			}
			
		}
		else {
			$joinArr2 = array(
				'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
				'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id','yurt90w_client_country',T_CLIENT_VAR.'client_bestseller')),
				'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
				'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
			);
			if(!empty($_GET["tag"])) {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1'".$kwhere,"fetchAll",array('warray'=>array('KID'=>'%'.$_GET["tag"].'%'),'order'=>'product_order desc, product_id desc'),$joinArr2);
			} else {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and store_approval = '1' and product_delstatus != '1'","fetchAll",array('order'=>'product_id desc'),$joinArr2);
			}
		}
		$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($product_data));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(9);
		$view->setVariable('paginator',$paginator);
		$view->setVariable('product_data', $product_data);
		$view->setVariable('categories', $category_arr);
		$view->setVariable('loggedUser', $this->loggedUser);
		$view->setVariable('page',$page);
		if(!empty($category_title)) {
			$view->setVariable('category_title',$category_title);
		}
		if(!empty($_GET["search_txt"])) {
			$view->setVariable('search_term',$_GET["search_txt"]);
		}
		if(!empty($_GET["tag"])) {
			$view->setVariable("selected_tag",$_GET["tag"]);
		}
		if(!empty($category_desc)) {
			$view->setVariable("category_desc",$category_desc);
		}
		return $view;
	}
	
	public function filterproductsAction() {
		$page = $this->params()->fromRoute('page',1);
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			if(!empty($posted_data)) {
				$proSearch = "product_status = '1' and product_delstatus != '1' and store_approval = '1'";
				if(!empty($posted_data["my_range"])) {
					$range = explode(";",$posted_data["my_range"]);
					$proSearch.= " and product_price > '".$range[0]."' and product_price < '".$range[1]."'";
				}
				if(!empty($posted_data["location"])) {
					$latlong = $this->SuperModel->postData($posted_data["location"]);
					//$store_data = $this->SuperModel->findstore($latlong["latitude"],$latlong["longitude"]);
					$store_data = $this->SuperModel->Super_Get(T_STORE,"store_location like '%".trim(addslashes($posted_data["location"]))."%'","fetchAll",array('fields'=>'store_clientid'));
					if(!empty($store_data)) {
						$proSearch .= " AND ( ";
						$loopwhere = '';							
						foreach($store_data as $store_data_key => $store_data_val) {
							$loopwhere.= "(product_clientid = '".$store_data_val["store_clientid"]."') OR ";
						}
						$proSearch .= trim($loopwhere,'OR ');
						$proSearch .= " ) ";	
					} else {
						$proSearch .= " AND (product_clientid = '0')";
					}
				}
				if(!empty($posted_data["seller_name"])) {
					//$client_data = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_name like '%".$posted_data["seller_name"]."%' and ".T_CLIENT_VAR."client_status = '1'","fetchAll",array('fields'=>T_CLIENT_VAR.'client_id'));
					/* newly commented $store_record = $this->SuperModel->Super_Get(T_STORE,"store_name like '%".trim(addslashes($posted_data["seller_name"]))."%'","fetchAll",array('fields'=>'store_clientid'));
					if(!empty($store_record)) {
						$proSearch .= " AND ( ";
						$loopwhere2 = '';	
						foreach($store_record as $client_data_key => $client_data_val) {
							$loopwhere2.= "(product_clientid = '".$client_data_val['store_clientid']."') OR ";
						}
						$proSearch .= trim($loopwhere2,'OR ');
						$proSearch .= " ) ";
					} else {
						$proSearch .= " AND (product_clientid = '0')";
					}*/
					$psname = $posted_data["seller_name"];
					$proSearch .= " AND (product_title like '%".$posted_data["seller_name"]."%' or product_description like '%".$posted_data["seller_name"]."%' or find_in_set('$psname',product_tags) <> 0)";
				}
				if(!empty($posted_data["category"])) {
					$proSearch .= " AND product_category = '".$posted_data["category"]."'";
				}
				if(!empty($posted_data["subcategory"])) {
					$proSearch .= " AND product_subcategory = '".$posted_data["subcategory"]."'";
				}
				$joinArr2 = array(
					'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
					'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_bestseller',T_CLIENT_VAR.'client_country')),
					'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
					'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free'))
				);
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,$proSearch,"fetchAll",array('order'=>'product_id desc'),$joinArr2);
				$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($product_data));
				$paginator->setCurrentPageNumber($page);
				$paginator->setItemCountPerPage(9);
				$view = new ViewModel();
				$view->setVariable('paginator',$paginator);
				$view->setVariable('product_data', $product_data);
				$view->setVariable('loggedUser', $this->loggedUser);
				$view->setVariable('page',$page);
				$view->setTerminal(true);
				return $view;
			}
		} else {
			return $this->redirect()->tourl(APPLICATION_URL.'/product-listing/'.$page);
		}
	}
	
	public function sortproductsAction() {
		$page = $this->params()->fromRoute('page',1);
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			$joinArr2 = array(
				'0' => array('0' => T_CATEGORY_LIST, '1' => 'product_category = category_id' . '', '2' => 'Left', '3' => array('category_feild')),
				'1' => array('0' => T_CLIENTS, '1' => 'product_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_planstatus','yurt90w_client_stripe_id',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),
				'2' => array('0' => T_STORE, '1' => 'product_clientid = store_clientid' . '', '2' => 'Inner', '3' => array('store_approval')),
				'3'	=> array('0' => T_SHIPPROFILES, '1' => 'product_shippingid = shipping_id', '2' => 'Inner', '3' => array('shipping_free')),
			);
			if($posted_data["order"] == '1') {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_delstatus != '1' and store_approval = '1'","fetchAll",array('order'=>'product_price desc'),$joinArr2);
			} else if($posted_data["order"] == '2') {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_delstatus != '1' and store_approval = '1'","fetchAll",array('order'=>'product_price asc'),$joinArr2);
			} else if($posted_data["order"] == '3') {
				$product_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_status = '1' and product_delstatus != '1' and store_approval = '1'","fetchAll",array('order'=>'product_id desc'),$joinArr2);
			}
			$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($product_data));
			$paginator->setCurrentPageNumber($page);
			$paginator->setItemCountPerPage(150);
			$view = new ViewModel();
			$view->setVariable('paginator',$paginator);
			$view->setVariable('product_data', $product_data);
			$view->setVariable('loggedUser', $this->loggedUser);
			$view->setVariable('page',$page);
			$view->setTerminal(true);
			return $view;
		} else {
			return $this->redirect()->tourl(APPLICATION_URL.'/product-listing/'.$page);
		}
	}
	
	public function postcommentAction() {
		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {
			$posted_data = $this->getRequest()->getPost();
			if(empty($posted_data["comment"])) {
				echo "error";
				 exit();
			}
			if(empty($posted_data["blog"])) {
				echo "error";
				 exit();
			}
			$blog_details = $this->SuperModel->Super_Get(T_BLOG,"blog_id =:TID","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["blog"]))));
			if(empty($blog_details)) {
				echo "error";
				 exit();
			}
			$data["comment_text"] = strip_tags($posted_data["comment"],'<br/>');
			$data["comment_clientid"] = $this->loggedUser->yurt90w_client_id;
			$data["comment_date"] = date("Y-m-d H:i:s");
			$data["comment_blogid"] = base64_decode($posted_data["blog"]);
			$ins = $this->SuperModel->Super_Insert(T_BLOG_COMMENT,$data);
			
			$joinArr2 = array(
				'0' => array('0' => T_CLIENTS, '1' => 'comment_clientid = ' . T_CLIENT_VAR . 'client_id' . '', '2' => 'Left', '3' => array(T_CLIENT_VAR . 'client_name', T_CLIENT_VAR . 'client_image'))
			);
			$last_comment = $this->SuperModel->Super_Get(T_BLOG_COMMENT,"comment_blogid =:TID and comment_id = '".$ins->inserted_id."'","fetch",array('warray'=>array('TID'=>base64_decode($posted_data["blog"]))),$joinArr2);
			$view = new ViewModel();
			$view->setVariable('last_comment',$last_comment);
			$view->setTerminal(true);
			return $view;	
		} 
	}
	
	public function subscribesletterAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$posted_data = $this->getRequest()->getPost();
			if (!filter_var($posted_data["nwletter_email"], FILTER_VALIDATE_EMAIL)) {
				echo "error";
				exit();
			}
			$subscriber_data = $this->SuperModel->Super_Get(T_NEWSSUBSCRIBERS,"LOWER(newsletter_sub_email)=:subscribe_email","fetch",array('warray'=>array("subscribe_email"=>strtolower($posted_data["nwletter_email"]))));
			if(!empty($subscriber_data)) {
				echo "already_exists";
				exit();
			}
			$newsletter_data["newsletter_sub_name"] = trim(strip_tags($posted_data["nwletter_name"]));
			$newsletter_data["newsletter_sub_email"] = trim(strtolower(strip_tags($posted_data["nwletter_email"])));
			$newsletter_data["newsletter_sub_date"] = date("Y-m-d H:i:s");
			$newsletter_insert = $this->SuperModel->Super_Insert(T_NEWSSUBSCRIBERS,$newsletter_data);
			if(is_object($newsletter_insert) && $newsletter_insert->success){
				$is_Notify=$this->SuperModel->Super_Insert(T_NOTIFICATION,array("notification_type"=>"0","notification_to"=>"1","notification_date"=>date("Y-m-d H:i:s"),"notification_subscriberid"=>$newsletter_insert->inserted_id));
				$this->EmailModel->SendEmail("news_letter_user",array("user_email"=>$newsletter_data["newsletter_sub_email"]));
				echo "success";
			} else {
				echo "error";
			}
		}
		exit();
	}
	
	public function letterspottedAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$_SESSION["nwletter"] = '1';
			setcookie("nwletter", "1", time() + (86400 * 30 * 60), "/");
		}
		exit();
	}

	public function newsletterAction() {
		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {
			$posted_data = $this->getRequest()->getPost();
			$posted_data["news_email"] = strip_tags($posted_data["news_email"]);
			 if (!filter_var($posted_data["news_email"], FILTER_VALIDATE_EMAIL)) {
				 echo "error";
				 exit();
			 }
			$subscriber_data = $this->SuperModel->Super_Get(T_NEWSSUBSCRIBERS,"LOWER(newsletter_sub_email)=:subscribe_email","fetch",array('warray'=>array("subscribe_email"=>strtolower($posted_data["news_email"]))));
			if(!empty($subscriber_data)) {
				echo "already_exists";
				exit();
			}
			$newsletter_data["newsletter_sub_name"] = strtolower(strip_tags($posted_data["news_name"]));
			$newsletter_data["newsletter_sub_email"] = strtolower(strip_tags($posted_data["news_email"]));
			$newsletter_data["newsletter_sub_date"] = date("Y-m-d H:i:s");
			$newsletter_insert = $this->SuperModel->Super_Insert(T_NEWSSUBSCRIBERS,$newsletter_data);
			if(is_object($newsletter_insert) && $newsletter_insert->success){
				$is_Notify=$this->SuperModel->Super_Insert(T_NOTIFICATION,array("notification_type"=>"0","notification_to"=>"1","notification_date"=>date("Y-m-d H:i:s"),"notification_subscriberid"=>$newsletter_insert->inserted_id));
				$this->EmailModel->SendEmail("news_letter_user",array("user_email"=>$newsletter_data["newsletter_sub_email"]));
				echo "success";
			} else {
				echo "error";
			}
		}
		exit();
	}
	
	public function readnotificationAction() {
		$request = $this->getRequest(); 
		if ($request->isXmlHttpRequest() ) {
			$posted_data = $this->getRequest()->getPost();
			if(empty($this->loggedUser)) {
				echo "non_logged";
				exit();
			} else {
				$notify_data["notification_readstatus"] = '1';
				$this->SuperModel->Super_Insert(T_NOTIFICATION,$notify_data,"notification_to = '".$this->loggedUser->yurt90w_client_id."'");
				echo "success";
				exit();
			}
		}
	}
	
	public function checksubscriberAction()

	{

		$request = $this->getRequest(); 

		if ($request->isXmlHttpRequest() ) {

		$subscribe_email = $this->params()->fromQuery('subscribe_email');

		

		$SubcriberData = $this->SuperModel->Super_Get(T_NEWSSUBSCRIBERS, "LOWER(newsletter_sub_email)=:subscribe_email","fetch",array("warray"=>array("subscribe_email"=>strtolower($subscribe_email))));

				

	if($SubcriberData)

		{

			echo json_encode("`$subscribe_email`"." already subscribed, please enter any other email address");

		}

		else{

			echo json_encode("true");

		}

		exit();

		}

		return $this->redirect()->tourl(APPLICATION_URL);

	}

	

	public function subscribeletterAction(){

		$form = new StaticForm(); 

		$form->newsletter();

		$request = $this->getRequest();        

		if ($request->isPost()) {

            $data = $request->getPost();

			 $form->setData($data);

			if ($form->isValid())

			{

				$data = $form->getData();

				

				if(!empty($data['subscribe_email'])){

					$subscribe_email=$data['subscribe_email'];

					$SubcriberData = $this->SuperModel->Super_Get(T_NEWSSUBSCRIBERS, "LOWER(newsletter_sub_email)=:subscribe_email","fetch",array("warray"=>array("subscribe_email"=>strtolower($subscribe_email))));

					

					

					

					if(empty($SubcriberData)){

					$is_Insrted=$this->SuperModel->Super_Insert(T_NEWSSUBSCRIBERS,array("newsletter_sub_email"=>$data['subscribe_email'],"newsletter_sub_date"=>date("Y-m-d H:i:s")));

					/* send notification to admin that user is sbscribed */

					if(is_object($is_Insrted) && $is_Insrted->success){

					$is_Notify=$this->SuperModel->Super_Insert(T_NOTIFICATION,array("notification_type"=>"0","notification_to"=>"1","notification_date"=>date("Y-m-d H:i:s"),"notification_subscriberid"=>$is_Insrted->inserted_id));

					$this->EmailModel->SendEmail("news_letter_user",array("user_email"=>$subscribe_email));

					$this->frontSession['successMsg']="You have successfully subscribed with us.";

					}else{

						$this->frontSession['errorMsg']="Some error occurred";

					}

					}else{

					$this->frontSession['errorMsg']="`$subscribe_email`"." already subscribed, please enter any other email address";

					}

				}

				

				if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=''){

						return $this->redirect()->tourl($_SERVER['HTTP_REFERER']);

					}else{

					return $this->redirect()->tourl(APPLICATION_URL);

				}

			}else{

				$this->frontSession['errorMsg']="Please fill in the information correctly.";

			}

			if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=''){

			return $this->redirect()->tourl($_SERVER['HTTP_REFERER']);

			}else{

			return $this->redirect()->tourl(APPLICATION_URL);

			}

		}

		

	}

	
	private function getpagecontent($pageid='',$pageTag='',$viewTemplate=1)
    {	
		$page_content = $this->SuperModel->Super_Get(T_PAGES,"page_id=:page","fetch",array("warray"=>array("page"=>$pageid)));
		
		$page_content['page_content_'.$_COOKIE['currentLang']]=str_ireplace(array('{last_updated}','{img_url}','{site_path}'),array(date("d F, Y ",strtotime($page_content['page_updated'])),HTTP_IMG_PATH,APPLICATION_URL),$page_content['page_content_'.$_COOKIE['currentLang']]);

		return $page_content;

		// Pass form variable to view
		// $view = new ViewModel();
		// $this->layout()->setVariable('pageTag',$pageTag);
		// $this->layout()->setVariable('pageHeading',$page_content['page_title_'.$_COOKIE['currentLang']]);
		// if($viewTemplate==1){
			// $view->setTemplate('application/static/index.phtml');
		// }
		// $view->setVariable('pageHeading',$page_content['page_title_'.$_COOKIE['currentLang']]);
		// $view->setVariable('page_content',$page_content);
		// $view->setVariable('pageTag',$pageTag);
		// return $view;
	}
	

	
	public function profilepageAction(){}
	public function changepasswordAction(){}
	public function sellerpageAction(){}


	public function shippingrateAction(){}
	public function manageproductAction(){}
	public function uploadproductAction(){}

	public function managecouponsAction(){}
	public function managecouponsnewAction(){}

	public function productlistAction(){}

	public function productdetailAction(){}

	public function customerorderAction(){}
	public function sellerorderAction(){}
	
	public function messagingAction(){}
	public function messagepageAction(){}
	public function sellerprofileAction(){}
	public function walletAction(){}
	public function comingsoonAction(){}
	public function dashboardpageAction(){}
	public function quickeditAction(){}
	public function wickedshopAction(){
		$joinArr = array(
			'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_status','yurt90w_client_stripe_id',T_CLIENT_VAR."client_country",T_CLIENT_VAR."client_bestseller")),	
		);
		$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
		$category_listing = $this->SuperModel->Super_Get(T_CATEGORY_LIST,"category_status = '1'","fetchAll");
		$category_arr = array();
		if(!empty($category_listing)) {
			foreach($category_listing as $category_listing_key => $category_listing_val) {
				$category_arr[$category_listing_val["category_id"]] = $category_listing_val["category_feild"];
			}
		}
		$wicked_data = $this->SuperModel->Super_Get(T_WICKED,"wicked_id = '1'","fetch");
		$view = new ViewModel();
		$view->setVariable('wicked_shops',$wicked_shops);
		$view->setVariable('categories',$category_arr);
		$view->setVariable('loggedUser',$this->loggedUser);
		$view->setVariable('wicked_data',$wicked_data);
		return $view;
	}
	
	public function wickedsortAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_status','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_bestseller'))
			);
			if($data["sorts"] != '') {
				if($data["sorts"] == '1') {					
					$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)")),'order'=>'avg_review desc'),$joinArr);
				} else if($data["sorts"] == '2') {
					$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)")),'order'=>'avg_review asc'),$joinArr);
				}
				
			} else {
				$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
			}
			$view = new ViewModel();
			$view->setVariable('wicked_shops',$wicked_shops);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function wickedcategoryAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_status','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_bestseller'))
			);
			if($data["category"] != '') {
				$products_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_category =:CID","fetchAll",array('warray'=>array('CID'=>$data["category"]),'fields'=>'product_clientid','group'=>'product_clientid'));
				$product_clients = array_column($products_data, 'product_clientid');
				$wicked_shops = array(); $swhere = '';
				if(!empty($product_clients)) {
					foreach($product_clients as $product_clients_key => $product_clients_val) {
						if($product_clients_key == (count($product_clients) - 1)) {
							$swhere .= "store_clientid = '".$product_clients_val."'";
						} else {
							$swhere .= "store_clientid = '".$product_clients_val."' or ";
						}
					}
				} else {
					$swhere = 'store_clientid = 0';
				}
				$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1' and ".$swhere."","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
			} else {
				$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
			}
			$view = new ViewModel();
			$view->setVariable('wicked_shops',$wicked_shops);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}
	
	public function wickedletterAction() {
		$request = $this->getRequest();
		if($request->isXmlHttpRequest()) {
			$data = $request->getPost();
			$joinArr = array(
				'0' => array('0' => T_CLIENTS, '1' => 'store_clientid = yurt90w_client_id' . '', '2' => 'Inner', '3' => array('yurt90w_client_status','yurt90w_client_stripe_id',T_CLIENT_VAR.'client_bestseller'))
			);
			$wicked_shops = $this->SuperModel->Super_Get(T_STORE,"store_approval = '1' and yurt90w_client_status = '1' and store_name like '".$data["alpha"]."%'","fetchAll",array('fields'=>array('*','total_products' => new Expression("(SELECT COUNT('product_id') FROM yurt90w_products WHERE product_clientid = store_clientid)"),'avg_review'=>new Expression("(SELECT AVG(review_starrating) FROM ".T_REVIEWS." where review_to = store_clientid)"),'review_date'=>new Expression("(SELECT MAX(review_date) FROM ".T_REVIEWS." where review_to = store_clientid)"),'total_reviews'=>new Expression("(SELECT COUNT(review_id) FROM ".T_REVIEWS." where review_to = store_clientid)"),'favorite'=>new Expression("(SELECT COUNT(favourite_id) FROM yurt90w_favourite where favourite_clientid = store_clientid)"))),$joinArr);
			
			$view = new ViewModel();
			$view->setVariable('wicked_shops',$wicked_shops);
			$view->setVariable('loggedUser',$this->loggedUser);
			$view->setTerminal(true);
			return $view;
		}
	}

	public function checkoutAction(){}
    
    public function ipnhookAction() {
		$unq_id = $this->params()->fromRoute('key');
		//$client_id = myurl_decode($client_id);
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
		  $keyval = explode ('=', $keyval);
		  if (count($keyval) == 2)
			 $myPost[$keyval[0]] = urldecode($keyval[1]);
		}
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
		   $get_magic_quotes_exists = true;
		} 
		foreach ($myPost as $key => $value) {  
			
		   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
				$value = urlencode(stripslashes($value)); 
		   } else {
				$value = urlencode($value);
		   }
		   $req .= "&$key=$value";
		}
		if(empty($unq_id)) {
			echo "failed";
			exit();
		}
		$ipn_record = $this->SuperModel->Super_Get(T_PRODORDER,"order_unqid =:TID","fetch",array('warray'=>array('TID'=>$unq_id)));
		$client_id = $ipn_record["order_clientid"];
		$client_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('warray'=>array('UID'=>$client_id)));
		if(empty($client_details)) {
			//echo "failed";
			//exit();
		}
		// STEP 2: Post IPN data back to paypal to validate
		
		$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		// In wamp like environments that do not come bundled with root authority certificates,
		// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
		// of the certificate as shown below.
		// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
		$res = curl_exec($ch);
		if (curl_errno($ch) != 0) // cURL error
		{
			$mail_const_data2 = array(
				"user_name" => "Mark Klusner",
				"user_email" => "developauth82@gmail.com",
				"message" => date('Y-m-d H:i'). "Can't connect to PayPal to validate IPN message: " . curl_error($ch),
				"subject" => "Order placed"
			);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
			curl_close($ch);
			exit;
		} else {
				// Log the entire HTTP response if debug is switched on.
				curl_close($ch);
		}	
		$tokens = explode("\r\n\r\n", trim($res));
		$res = trim(end($tokens));
		// STEP 3: Inspect IPN validation result and act accordingly
		
		if (strcmp ($res, "VERIFIED") == 0 || $myPost["payment_status"] == 'Completed') {
			
			// check whether the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment

			// assign posted variables to local variables
			$all_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID and order_unqid =:TID","fetchAll",array('warray'=>array('UID'=>$client_id,'TID'=>$unq_id)));
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
					"user_email" => $this->SITE_CONFIG['site_email'],
					"message" => $message,
					"subject" => "Order placed"
				);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data3);
			
			$seller_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID and order_unqid =:TID","fetchAll",array('group'=>'order_sellerid','warray'=>array('UID'=>$client_id,'TID'=>$unq_id)));
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
			
			$pending_orders = $this->SuperModel->Super_Get(T_PRODORDER,"order_status = '5' and order_clientid =:UID and order_unqid =:TID","fetchAll",array('warray'=>array('UID'=>$client_id,'TID'=>$unq_id)));
			if(!empty($pending_orders)) {
				foreach($pending_orders as $pending_orders_key => $pending_orders_val) {

					$order_update["order_status"] = 1;
					$this->SuperModel->Super_Insert(T_PRODORDER,$order_update,"order_id = '".$pending_orders_val["order_id"]."'");

					$seller_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR.'client_name',T_CLIENT_VAR.'client_email'),'warray'=>array('UID'=>$pending_orders_val["order_sellerid"])));
					$clt_details = $this->SuperModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id =:UID","fetch",array('fields'=>array(T_CLIENT_VAR."client_name",T_CLIENT_VAR."client_email"),'warray'=>array('UID'=>$pending_orders_val["order_clientid"])));

					$prod_data = $this->SuperModel->Super_Get(T_PRODUCTS,"product_id =:PID","fetch",array('fields'=>array('product_clientid','product_title'),'warray'=>array('PID'=>$pending_orders_val["order_product"])));
					
					$order_detailz = $this->SuperModel->Super_Get(T_PRODORDER,"order_id =:TID","fetch",array('warray'=>array('TID'=>$pending_orders_val["order_id"])));

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
			
			echo "success";
			exit();

			// <---- HERE you can do your INSERT to the database

		} else if (strcmp ($res, "INVALID") == 0) {
			$mail_const_data2 = array(
				"user_name" => "Mark Klusner",
				"user_email" => "developauth82@gmail.com",
				"message" => 'Order successfully placed',
				"subject" => "Order placed"
			);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
			// log for manual investigation
		} else {
			$mail_const_data2 = array(
				"user_name" => "Mark Klusner",
				"user_email" => "developauth82@gmail.com",
				"message" => 'Here comes your order',
				"subject" => "Order placed"
			);	
			$isSend = $this->EmailModel->sendEmail('notification_email',$mail_const_data2);
		}
	}

}

