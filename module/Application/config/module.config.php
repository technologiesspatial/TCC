<?php
/* * * * * * * * * * * * * * * * * * * * * * * * *
* Front application module configuration
* * * * * * * * * * * * * * * * * * * * * * * * */
namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Regex;
use Zend\ServiceManager\Factory\InvokableFactory;
use Application\Route\StaticRoute;

return [
    'router' => [
        'routes' => [

            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

			'front_subscribeletter' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/subscribe',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'subscribeletter',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            'fog_holder' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/fog-holder',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'fogholder',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            'optimize_pics' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/optimize-pics',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'optimizepics',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			'check_unload' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/check-unload',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checkunload',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            'ipn_hook' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/ipn-hook[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'ipnhook',
                    ),
                ),
            ),
            'release_payout' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/release-payout',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'releasepayout',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            'productmore_text' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/productmore-text',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'productmoretxt',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            'sellermore_text' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/sellermore-text',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'sellermoretxt',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			'charge_plan' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/charge-plan',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'chargeplan',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'check_out_page' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/check-out',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checkout',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

			'front_checksubscriber' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getsubscriber',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checksubscriber',						
                    ),
                ),
            ), 
			'viewmore_category' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/viewmore-category',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'viewmorecategory',						
                    ),
                ),
            ),
			'product_categorylist' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-categorylist',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'categorylist',						
                    ),
                ),
            ),
			'product_sublist' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-sublist',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'sublist',						
                    ),
                ),
            ),
			'color_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/color-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'colordetails',						
                    ),
                ),
            ),
			'getprice_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getprice-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'pricedetails',						
                    ),
                ),
            ),
			'price_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/price-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'pricedetail',						
                    ),
                ),
            ),
			'search_productzone' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/search-productzone',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'productzone',						
                    ),
                ),
            ),
			'getscroll_comments' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getscroll-comments',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'getscrollcomments',						
                    ),
                ),
            ),
			'newsletter_spotted' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/newsletter-spotted',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'letterspotted',						
                    ),
                ),
            ),
			'subscribe_letter' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/subscribe-letter',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'subscribesletter',						
                    ),
                ),
            ),
			'post_newsletter' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/post-newsletter',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'newsletter',						
                    ),
                ),
            ),
			'post_comment' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/post-comment',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'postcomment',						
                    ),
                ),
            ),
			'read_notify' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/read-notify',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'readnotification',						
                    ),
                ),
            ), 
			
			'errorpage' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/errorpage',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'errorpage',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'front_aboutus' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/about-us',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'about',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'front_contactus' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/contact-us',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'contact',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ], 

            /* need to update controller and view */
            'front_privacypolicy' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/privacy-policy',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'privacy',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
	
	
	 		'front_howitworks' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/how-it-works',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'howitworks',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			'front_testament' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/last-testament',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'lasttestament',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			/*'front_blog' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/blog[/:page]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
                    ),
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'blog',						
                    ),
                ),
            ),


            'front_blog_detail' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/blog-detail[/:blog]',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'blogdetail',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],*/


            'front_profile' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/profile-page',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'profilepage',						
                    ),
                ),
            ), 

            'change_password' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/change-password-page',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'changepassword',						
                    ),
                ),
            ), 

            'front_seller' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/seller-page',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'sellerpage',						
                    ),
                ),
            ), 

            'front_shipping_rate' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/shipping-rate1',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'shippingrate',						
                    ),
                ),
            ), 

            'front_manage_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/manage-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'manageproduct',						
                    ),
                ),
            ), 

            

            
            'front_upload_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/upload-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'uploadproduct',						
                    ),
                ),
            ), 

            'front_manage_coupons' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/manage-coupons',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'managecoupons',						
                    ),
                ),
            ), 

            'front_manage_coupons_new' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/manage-coupons-new',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'managecouponsnew',						
                    ),
                ),
            ),
			'front_productby_category' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/productby-category[/:page]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
                    ),
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'productbycategory',						
                    ),
                ),
            ),
			'front_sort_products' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/sort-products[/:page]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
                    ),
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'sortproducts',						
                    ),
                ),
            ),
			'front_filter_products' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/filter-products[/:page]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
                    ),
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'filterproducts',						
                    ),
                ),
            ),
			'front_product_listing' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/product-listing[/:page]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
                    ),
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'productlisting',						
                    ),
                ),
            ),
            'front_product_list' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-list',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'productlist',						
                    ),
                ),
            ), 


            'front_product_detail' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-detail',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'productdetail',						
                    ),
                ),
            ), 
			
			
			'product_detail' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/product[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',					
                        'controller' => Controller\StaticController::class,
                        'action'     => 'productdetail',
                    ),
                ),
            ),
	
			'getcart_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getcart-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'getcartdetails',						
                    ),
                ),
            ),
	
			'show_sizes' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/show-sizes',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'showsizes',						
                    ),
                ),
            ),
	
			'pick_quantity' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/pick-quantity',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'pickquantity',						
                    ),
                ),
            ),
	
			'show_price' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/show-price',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'showprice',						
                    ),
                ),
            ),
	
			'addmy_cart' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/addmy-cart',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'addmycart',						
                    ),
                ),
            ),
	
			'my_cart' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/my-cart',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'shoppingcart',						
                    ),
                ),
            ),
			'ship_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/ship-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'shipdetails',						
                    ),
                ),
            ),
			'process_payment' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/process-payment',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'processpayment',
                    ),
                ),
            ),
			'remove_cart' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-cart',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'removecart',						
                    ),
                ),
            ),
			'cart_price' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/cart-price',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\StaticController::class,
                        'action'     => 'cartprice',						
                    ),
                ),
            ),
            'front_customer_order' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/customer-order',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'customerorder',						
                    ),
                ),
            ), 

            'front_seller_order' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/seller-order',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'sellerorder',						
                    ),
                ),
            ), 

            'front_shopping_cart' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/shopping-cart',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'shoppingcart',						
                    ),
                ),
            ), 
			
			'messaging' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/messaging',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'messaging',						
                    ),
                ),
            ), 
            'messagepage' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/message-page',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'messagepage',						
                    ),
                ),
            ),
			'quickedit' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/quick-edit',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'quickedit',						
                    ),
                ),
            ), 
			'wickedby_letter' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wickedby-letter',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'wickedletter',						
                    ),
                ),
            ),
			'wickedby_category' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wickedby-category',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'wickedcategory',						
                    ),
                ),
            ),
			'wickedby_sorting' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wickedby-sorting',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'wickedsort',						
                    ),
                ),
            ),
			'seller_profile' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/shop[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',					
                        'controller' => Controller\StaticController::class,
                        'action'     => 'sellerprofile',
                    ),
                ),
            ),
			'sellerprofile' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/seller-profiler',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'sellerprofile',						
                    ),
                ),
            ), 
            'wallet' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wallet',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'wallet',						
                    ),
                ),
            ), 
			
            /* ----------------------------------- above correct ---------------------------------*/

            
            /* need to update controller and view */
	
            /* 'front_cookiespolicy' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/cookies-policy',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'cookiespolicy',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ], */

			

             

           /*  'front_advertisewithus' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/advertise-with-us',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'advertisewithus',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ], */

            /* 'advertise-with-us-request' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/advertise-with-us-request',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'advertisewithusrequest',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ], */

            /* need to update controller and view */
            'front_acceptableusepolicy' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/acceptable-use-policy',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'acceptableusepolicy',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            /* need to update controller and view */
            'front_termsandconditions' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/terms-and-conditions',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'termsandconditions',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            /*  */



            /*  */
            
            'front_security' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/security-page',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'security',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],




            /* need to update controller and view */
            'front_termsofwebsiteuse' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/terms-of-website-use',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'termsofwebsiteuse',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            /* need to update controller and view */
            'front_termandconditionsforadvertisers' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/term-and-conditions-for-advertisers',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'termandconditionsforadvertisers',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],


            /* START - THIS IS NOT CORRECT */
            'register-personal-account' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/register-personal-account',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'signup',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'register-as-a-member' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/register-as-a-member',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'signup',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            /* END - THIS IS NOT CORRECT */
		

			
            'front_faq' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/faq',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'faq',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	

			'front_testimonials' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/testimonials',
                    'defaults' => [
                        'controller' => Controller\StaticController::class,
                        'action'     => 'alltestimonials',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	

			/*'login' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'login',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	*/

			

			'signup' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/signup',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'signup',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	

			'myaccount' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/myaccount',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'myaccount',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	
			
			'booknow' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/booknow',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'booknow',
						'__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],	

            /* new */
            'profilepage' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/profile-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'profilepage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],  

            /* new */
            'profilepage2' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/profile-page2',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'profilepage2',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'how_it_work' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/how-it-work',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'howitwork',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'features' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/Features-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'features',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'signup' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/signup-page1',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'signup',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			

            'areyoupage' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/are-you-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'areyoupage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            
            'providepage1' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/provide-page1',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'providepage1',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'providepage2' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/provide-page2',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'providepage2',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'selectplan' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/select-plan',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'selectplanpage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            
            'solution_page' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/solution-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'solutionpage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'pricing_page' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/pricing-page1',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'pricingpage1',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],

            'profilepage' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/profile-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'profilepage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
            
            'subscriptionpage' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/subscription-page',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'subscriptionpage',
                        '__NAMESPACE__' => 'Application\Controller',
                    ],
                ],
            ],
			
			'comingsoon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/coming-soon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'comingsoon',						
                    ),
                ),
            ),
			
			 'wickedshop' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wicked-shops',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'wickedshop',						
                    ),
                ),
            ), 
            'dashboardpage' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/dashboard-page',
                    'defaults' => array(
					    '__NAMESPACE__' => 'Application\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'dashboardpage',						
                    ),
                ),
            ),

        ],

    ],

	
	//Call ControllerFactory 
	'controllers' => [
        'factories' => [
			Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
			Controller\StaticController::class => Controller\Factory\StaticControllerFactory::class,
			
        ],
    ],

	//Call Plugin
	'controller_plugins' => array(
		'invokables' => array(			
			'Image' => 'Application\Controller\Plugin\Image',
			'ImageCrop' => 'Application\Controller\Plugin\ImageCrop',	
			'translator' => 'Application\Controller\Plugin\TranslatePlugin',		
		)
	),
	
	'view_helpers' => array(
		  'invokables' => array(   
		   'renderForm' => 'Application\View\Helper\RenderForm',   
		   'GetMessages' => 'Application\View\Helper\GetMessages',   
		  ),
	 ),

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack' => [
           __DIR__ . '/../view',
        ],
		'template_map' => [
			'layout/layout' 		  => __DIR__ . '/../view/layout/layout.phtml',
			'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
		],
    ],
];