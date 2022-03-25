<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Admin menu/sub-menu in left panel
* * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Admin\View\Helper;
use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

class NavHelper extends AbstractHelper {
	public function __invoke($translator) 
	{
		$return_nav=$this->getNavArray($translator);
		return $return_nav;
	}
	
	public function getNavArray($translator){
		
		$session = new Container(ADMIN_AUTH_NAMESPACE);

			$pages = array (
			/* Dashboard */
			array(
				'label' => $translator->translate("dash_head_txt"),
				'icon'=>'md-view-dashboard',
				'data_icon'=>'v',
				'module' => 'admin',
				'controller' => 'index',
				'action' => 'dashboard',
				'uri' => '/dashboard',	
			),
			
			array(
				'label'=>	$translator->translate("config_txt"),
				'icon'=>'md-settings',
				'data_icon'=>'/',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
				'pages'		=>	array(
					array(
						'label'=>$translator->translate("site_txt"),
						'module' => 'admin',
						'controller' => 'static',
 						'action' => 'index',
						'route'=>'/settings',
						'subutype'=>'1',
  					),
					array(
						'label'=>$translator->translate("social_txt"),
						'module' => 'admin',
						'controller' => 'static',
 						'action' => 'index',
						'route'=>'/social',
						'subutype'=>'3',
  					),
					array(
						'label'=>"Payment",
						'module' => 'admin',
						'controller' => 'static',
 						'action' => 'index',
						'route'=>'/config-payment',
						'subutype'=>'2',
  					),
				)
			),
			array(
				'label' => $translator->translate("static_content_txt"),
				'icon'=>'md-google-pages',
				'data_icon'=>'F',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
 				'pages' =>array(
					array(
							'label' =>  $translator->translate("email_tmp_txt"),
							'module' => 'admin',
							'controller' => 'static',
							'action' => 'emailtemplate',
							'route' => '/emailtemplate',
							'pages' =>array(
								array(
									'module' => 'admin',
									'controller' => 'static',
									'action' => 'editemailtemplate',
								),
							),
					),	
					array(
						'label' => $translator->translate("pages_head_txt"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'pages',
						'route' => '/pages',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'editpages',
							),
						),
					),	
					array(
						'label' => $translator->translate("Manage Homepage"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'homepage',
						'route' => '/homepage',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'homepage',
							),
							
						),
					),
					array(
						'label' => $translator->translate("Manage How It Works"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'howitworks',
						'route' => '/how-it-works-page',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'howitworks',
							),
							
						),
					),
					array(
						'label' => $translator->translate("Photo Gallery"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'photogallery',
						'route' => '/photo-gallery',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'photogallery',
							),
							
						),
					),
					array(
						'label' => $translator->translate("Manage Wicked Shop"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'wickedshop',
						'route' => '/manage-wickedshop',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'wickedshop',
							),
							
						),
					),
					/*array(
						'label' => $translator->translate("Manage Blog Categories"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'blogcategory',
						'route' => '/blog-categories',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'blogcategory',
							),
							
						),
					),
					array(
						'label' => $translator->translate("Manage Blogs"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'blogs',
						'route' => '/blogs',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'blogs',
							),
							
						),
					),
					array(
						'label' => $translator->translate("Manage Blog Comments"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'blogcomments',
						'route' => '/blog-comments',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'blogcomments',
							),
							
						),
					),*/
					array(
						'label' => $translator->translate("faq_txt"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'faqslist',
						'route' => '/faqs',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'managefaqs',
							),
							
						),
					),
					array(
						'label' => 'Newsletter Subscribers',
						'icon' =>'fa fa-list',
						'data_icon'=>'F',
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'subscriberslist',
						'route' => '/subscribers',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'subscriberslist',
							),
							
						),	
					),
     			)
			),	

			array(
				'label' => 'Users/Members',
				'icon' =>'fa fa-users',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'user',
				'action' => 'index',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
				'pages' =>array(
					array(
						"label"=>"All Users",
						"route"=>"/users",
						'module' => 'admin',
						'controller' => 'user',
						'action' => 'index',
						'pages' =>array(
							array(
							  'label' => $translator->translate("View Account Info"),
								'module' => 'admin',
								'controller' => 'user',
								'action' => 'account',
								'route' => '/view-account',
							),
						),
					),
				),	
			),
			array(
				'label' => $translator->translate("Coupons"),
				'icon'=>'md-google-pages',
				'data_icon'=>'F',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
 				'pages' =>array(
					array(
						'label' => $translator->translate("Coupons"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'coupons',
						'route' => '/coupons',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'coupons',
							),		
						),
					),
				)
			),
			array(
				'label' => $translator->translate("Products"),
				'icon'=>'md-google-pages',
				'data_icon'=>'F',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
 				'pages' =>array(
					array(
						'label' => $translator->translate("Product Categories"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'categorylist',
						'route' => '/category',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'managecategory',
							),		
						),
					),
					array(
						'label' => $translator->translate("Product Sub Categories"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'subcategorylist',
						'route' => '/subcategories',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'subcategorylist',
							),		
						),
					),
					array(
						'label' => $translator->translate("Manage Products"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'productlist',
						'route' => '/products',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'productlist',
							),
							
						),
					),
					/*array(
						'label' => $translator->translate("Reported Products"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'reportedproducts',
						'route' => '/reported-products',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'reportedproducts',
							),
							
						),
					),*/
     			)
			),
			array(
				'label' => 'View Seller Application',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'sellerrequests',
				'uri' => '/seller-applications',	
			),
			array(
				'label' => 'View Verification Badge Request ',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'verificationbadges',
				'uri' => '/badge-requests',	
			),
			/*array(
				'label' => 'Reported Comments ',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'commentreports',
				'uri' => '/comment-reports',	
			),*/
			array(
				'label' => 'View Orders ',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'vieworders',
				'uri' => '/view-orders',	
			),
			array(
				'label' => 'Review Rating ',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'viewrating',
				'uri' => '/review-rating',	
			),
			/*array(
				'label' => 'Reported Reviews ',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'reviewreports',
				'uri' => '/review-reports',	
			),*/
			array(
				'label' => 'Payouts',
				'icon' =>'fa fa-list',
				'data_icon'=>'F',
				'module' => 'admin',
				'controller' => 'static',
				'action' => 'withdrawrequests',
				'uri' => '/withdrawal-requests',	
			),
			array(
				'label' => $translator->translate("Refund Requests"),
				'icon'=>'fa fa-list',
				'data_icon'=>'F',
				'uri' => 'javascript:void(0)',
				'route' => 'default',
 				'pages' =>array(
					array(
						'label' => $translator->translate("Pending Requests"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'refundrequests',
						'route' => '/refund-requests',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'refundrequests',
							),		
						),
					),
					array(
						'label' => $translator->translate("Completed Requests"),
						'module' => 'admin',
						'controller' => 'static',
						'action' => 'completedrefunds',
						'route' => '/completed-refunds',
						'pages' =>array(
							array(
								'module' => 'admin',
								'controller' => 'static',
								'action' => 'completedrefunds',
							),		
						),
					),	
				)
			)
		);
		return $pages;
	}
}