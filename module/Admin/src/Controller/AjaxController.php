<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Ajax controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class AjaxController extends AbstractActionController
{
	/* Constructor of the controller */
	public function __construct($Adapter,$AbstractModel,$Email)
    {
		$this->Adapter = $Adapter;
		$this->AbstractModel = $AbstractModel;
		$this->EmailModel = $Email;	
    }
	
	public function setbeststatusAction() {
		$dbAdapter = $this->Adapter;
		$params =  $this->params()->fromRoute();
		$sql = new Sql($dbAdapter);
		$data = array(		
			T_CLIENTS => array(
				'table'      	=> T_CLIENTS,
				'field_id'      => T_CLIENT_VAR."client_id",
				'field_status'  => T_CLIENT_VAR."client_bestseller")
		);
		if(isset($data[$params['type']]))
		{
			$update_data[$data[$params['type']]['field_status']] = $params['status'];
			$updated = $this->AbstractModel->Super_Insert($data[$params['type']]['table'],
			$update_data, $data[$params['type']]['field_id']."=".$params['id']);	
			
			echo json_encode(array("success" => true,"error" => false,"message" => $this->layout()->translator->translate("status_key_txt")));

		} else {

			echo json_encode(array("success" => false,"error" => true,"exception" => false,"message" => "Table Not Defined for the Current Request" ));
		}
		exit();
	}
	
	public function setfavstatusAction() {
		$dbAdapter = $this->Adapter;
		$params =  $this->params()->fromRoute();
		$sql = new Sql($dbAdapter);
		$data = array(		
			T_STORE => array(
				'table'      	=> T_STORE,
				'field_id'      => "store_id",
				'field_status'  => 'store_favorite'),
				
			T_BLOG => array(
				'table'      	=> T_BLOG,
				'field_id'      => "blog_id",
				'field_status'  => 'blog_status'),	
			
			T_PRODUCTS => array(
				'table'      	=> T_PRODUCTS,
				'field_id'      => "product_id",
				'field_status'  => 'product_status'),	
			
			T_BLOG_COMMENT => array(
				'table'      	=> T_BLOG_COMMENT,
				'field_id'      => "comment_id",
				'field_status'  => 'comment_status'),	
			
			T_SLIDER=>array(
				'table'      	=> T_SLIDER,
				'field_id'      => "slider_id",
				'field_status'  => 'slider_status'),
		);

		if(isset($data[$params['type']]))
		{
			$update_data[$data[$params['type']]['field_status']] = $params['status'];
			$updated = $this->AbstractModel->Super_Insert($data[$params['type']]['table'],
			$update_data, $data[$params['type']]['field_id']."=".$params['id']);	
			if(empty($params['status'])) {
				$store_details = $this->AbstractModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$params['id'])));
				$product_data = $this->AbstractModel->Super_Get(T_PRODUCTS,"product_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$store_details['store_clientid'])));
				if(!empty($product_data)) {
					foreach($product_data as $product_data_key => $product_data_val) {
						$this->AbstractModel->Super_Delete(T_PRODCART,"product_cart_prodid = '".$product_data_val["product_id"]."'");
					}	
				}				
			}
			
			echo json_encode(array("success" => true,"error" => false,"message" => $this->layout()->translator->translate("status_key_txt")));

		} else {

			echo json_encode(array("success" => false,"error" => true,"exception" => false,"message" => "Table Not Defined for the Current Request" ));
		}
		exit();
	}
	
	public function setstorestatusAction() {
		$dbAdapter = $this->Adapter;
		$params =  $this->params()->fromRoute();
		$sql = new Sql($dbAdapter);
		$data = array(		
			T_STORE => array(
				'table'      	=> T_STORE,
				'field_id'      => "store_id",
				'field_status'  => 'store_approval'),
				
			T_BLOG => array(
				'table'      	=> T_BLOG,
				'field_id'      => "blog_id",
				'field_status'  => 'blog_status'),	
			
			T_PRODUCTS => array(
				'table'      	=> T_PRODUCTS,
				'field_id'      => "product_id",
				'field_status'  => 'product_status'),	
			
			T_BLOG_COMMENT => array(
				'table'      	=> T_BLOG_COMMENT,
				'field_id'      => "comment_id",
				'field_status'  => 'comment_status'),	
			
			T_SLIDER=>array(
				'table'      	=> T_SLIDER,
				'field_id'      => "slider_id",
				'field_status'  => 'slider_status'),
		);

		if(isset($data[$params['type']]))
		{
			$update_data[$data[$params['type']]['field_status']] = $params['status'];
			$updated = $this->AbstractModel->Super_Insert($data[$params['type']]['table'],
			$update_data, $data[$params['type']]['field_id']."=".$params['id']);	
			if(empty($params['status'])) {
				$store_details = $this->AbstractModel->Super_Get(T_STORE,"store_id =:TID","fetch",array('warray'=>array('TID'=>$params['id'])));
				$product_data = $this->AbstractModel->Super_Get(T_PRODUCTS,"product_clientid =:UID","fetchAll",array('warray'=>array('UID'=>$store_details['store_clientid'])));
				if(!empty($product_data)) {
					foreach($product_data as $product_data_key => $product_data_val) {
						$this->AbstractModel->Super_Delete(T_PRODCART,"product_cart_prodid = '".$product_data_val["product_id"]."'");
					}	
				}				
			}
			
			echo json_encode(array("success" => true,"error" => false,"message" => $this->layout()->translator->translate("status_key_txt")));

		} else {

			echo json_encode(array("success" => false,"error" => true,"exception" => false,"message" => "Table Not Defined for the Current Request" ));
		}
		exit();
	}
	
    /* Change status of various modules */
	public function setstatusAction() 
	{
		$dbAdapter = $this->Adapter;
		$params =  $this->params()->fromRoute();
		$sql = new Sql($dbAdapter);
		$data = array(		
			T_CLIENTS => array(
				'table'      	=> T_CLIENTS,
				'field_id'      => T_CLIENT_VAR."client_id",
				'field_status'  => T_CLIENT_VAR.'client_status'),
				
			T_BLOG => array(
				'table'      	=> T_BLOG,
				'field_id'      => "blog_id",
				'field_status'  => 'blog_status'),	
			
			T_PRODUCTS => array(
				'table'      	=> T_PRODUCTS,
				'field_id'      => "product_id",
				'field_status'  => 'product_status'),	
			
			T_BLOG_COMMENT => array(
				'table'      	=> T_BLOG_COMMENT,
				'field_id'      => "comment_id",
				'field_status'  => 'comment_status'),	
			
			T_SLIDER=>array(
				'table'      	=> T_SLIDER,
				'field_id'      => "slider_id",
				'field_status'  => 'slider_status'),
		);

		if(isset($data[$params['type']]))
		{
			$update_data[$data[$params['type']]['field_status']] = $params['status'];
			$updated = $this->AbstractModel->Super_Insert($data[$params['type']]['table'],
				$update_data, $data[$params['type']]['field_id']."=".$params['id']);						
			
			echo json_encode(array("success" => true,"error" => false,"message" => $this->layout()->translator->translate("status_key_txt")));

		} else {

			echo json_encode(array("success" => false,"error" => true,"exception" => false,"message" => "Table Not Defined for the Current Request" ));
		}
		exit();
	}
}