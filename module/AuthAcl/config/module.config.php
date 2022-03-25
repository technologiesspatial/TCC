<?php
/* * * * * * * * * * * * * * * * * * * * * * * * *
* Front application module configuration
* * * * * * * * * * * * * * * * * * * * * * * * */

namespace AuthAcl;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Regex;
use Zend\ServiceManager\Factory\InvokableFactory;

return array(
    'router' =>array(
        'routes' => array(
             'auth_acl' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/authacl[/:action]',
                    'defaults' => array(
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
						'__NAMESPACE__' => 'AuthAcl\Controller',
                    ),
                ),
            ),

			'front_dashboard' => [

                'type'    => Segment::class,

                'options' => [

                    'route'    => '/dashboard',

                    'defaults' => [

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'dashboard',

						'__NAMESPACE__' => 'AuthAcl\Controller',

                    ],

                ],

            ],	

			'front_login' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'userlogin',
                    ),
                ),
            ),			
			'upload_screenshots' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/upload-screenshots',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'uploadscreenshot',
                    ),
                ),
            ),
			'upload_postfile' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/upload-postfile[/:id]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'uploadpostfile',
                    ),
                ),
            ),
            'export_tx' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/export-tx[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'exporttx',
                    ),
                ),
            ),
			'remove_files' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-files',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removefile',
                    ),
                ),
            ),
			'order_summary' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/order-summary',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'ordersummary',
                    ),
                ),
            ),
			'price_range' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/price-range',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'pricerange',
                    ),
                ),
            ),
			'product_updatestat' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-updatestat',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'updateprostat',
                    ),
                ),
            ),
			'order_products' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/order-products',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'orderproducts',
                    ),
                ),
            ),
			'remove_screenshot' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-screenshot',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removescreenshot',
                    ),
                ),
            ),
			'post_price' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/post-price',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'postprice',
                    ),
                ),
            ),
			'manage_products' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/manage-products',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'manageproducts',
                    ),
                ),
            ),
			'launch_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/launch-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'launchproduct',
                    ),
                ),
            ),
			'export_products' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/export-products',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'exportproducts',
                    ),
                ),
            ),
			'export_sample' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/export-sample',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'exportsample',
                    ),
                ),
            ),
			'import_products' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/import-products',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'importproducts',
                    ),
                ),
            ),
			'upload_teampics' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/upload-teampics',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'uploadteampics',
                    ),
                ),
            ),
			'merchant_coupon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/merchant-coupon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'merchantcoupon',
                    ),
                ),
            ),
			'shipping_profiles' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/shipping-profiles',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'shipprofiles',
                    ),
                ),
            ),
			'manage_shipping' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/manage-shipping[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'manageshipping',
                    ),
                ),
            ),
			'view_shipping_profile' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/view-shipping-profile[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'viewshipping',
                    ),
                ),
            ),
			'remove_profile' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-profile',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removeprofile',
                    ),
                ),
            ),
			'post_order' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/post-order',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'postorder',
                    ),
                ),
            ),
			'toggle_role' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/toggle-role',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'togglerole',
                    ),
                ),
            ),
			'chkstore' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/chkstore',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'chkstore',
                    ),
                ),
            ),
			'return_place' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/return-place',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'returnplace',
                    ),
                ),
            ),
			'quick_proedit' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/quickpro-edit',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'quickedit',
                    ),
                ),
            ),
			'publish_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/publish-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'publishproduct',
                    ),
                ),
            ),
			'withdrawal_amount' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/withdrawal-amount',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'withdrawal',
                    ),
                ),
            ),
			'my_wallet' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/my-wallet',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'mywallet',
                    ),
                ),
            ),
			'manage_size' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/manage-size[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'managesize',
                    ),
                ),
            ),
			'removeall_cart' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/removeall-cart',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removeallcart',
                    ),
                ),
            ),
			'order_update' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/order-update',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'updateorder',
                    ),
                ),
            ),
			'my_dashboard' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/dashboard',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'dashboardpage',
                    ),
                ),
            ),
			'addmy_favourite' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/addmy-favourite',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'addmyfavorite',
                    ),
                ),
            ),
			'cart_details' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/cart-details',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'cartdetails',
                    ),
                ),
            ),
			'previous_message' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/previous-messages',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'getpreviousmsg',
                    ),
                ),
            ),
			'load_messages' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/load-messages',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'loadmessages',
                    ),
                ),
            ),
			'getsearch_person' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getsearch-person',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'getsearchperson',
                    ),
                ),
            ),
			'getscroll_msglist' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getscroll-msglist',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'getscrolllist',
                    ),
                ),
            ),
			'messages' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/messages[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'messages',
                    ),
                ),
            ),
			'message' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/message[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'message',
                    ),
                ),
            ),
			'chat_message' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/chat-message',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'chatmessage',
                    ),
                ),
            ),
			'get_messages' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/get-messages',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'getmessages',
                    ),
                ),
            ),
			'reset_coupon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/reset-coupon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'resetcoupon',
                    ),
                ),
            ),
			'review_order' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/review-order',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'revieworder',
                    ),
                ),
            ),
			'make_payment' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/make-payment[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'makepayment',
                    ),
                ),
            ),
			'export_invoice' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/generate-pdf[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'exportinvoice',
                    ),
                ),
            ),
			'export_invoices' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/export-invoice[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'generateinvoice',
                    ),
                ),
            ),
			'seller_orders' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/seller-orders',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'sellerorders',
                    ),
                ),
            ),
			'refund_amount' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/refund-amount',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'refundamount',
                    ),
                ),
            ),
			'stat_overview' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/stat-overview',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'statoverview',
                    ),
                ),
            ),
			'seller_chkorder' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/seller-chkorder',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'sellerchkorder',
                    ),
                ),
            ),
			'customer_orders' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/customer-orders',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'customerorders',
                    ),
                ),
            ),
			'order_chkstat' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/order-chkstat',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'orderchkstat',
                    ),
                ),
            ),
			'get_countries' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/get-countries',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'getcountries',
                    ),
                ),
            ),
			'manage_coupon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/manage-coupon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'managecoupon',
                    ),
                ),
            ),
			'reply_comment' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/reply-comment',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'replycomment',
                    ),
                ),
            ),
			'shipping_rate' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/shipping-rate',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'shipping',
                    ),
                ),
            ),
			'show_propic' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/show-propic',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'showpropic',
                    ),
                ),
            ),
			'make_propic' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/make-propic',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'makepropic',
                    ),
                ),
            ),
			'update_couponstat' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/update-couponstat',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'couponstat',
                    ),
                ),
            ),
            'front_register' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/register',
                    'defaults' => array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'register',
                    ),
                ),
            ),

           // 'front_register_member' => array(
//                'type' => Segment::class,
//                'options' => array(
//                    'route' => '/register-as-a-member',
//                    'defaults' => array(
//                        '__NAMESPACE__' => 'AuthAcl\Controller',
//                        'controller' => Controller\IndexController::class,
//                        'action'     => 'registermember',
//                    ),
//                ),
//            ),

			'front_forgotpassword' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/forgot-password',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'userforgotpassword',						
                    ),
                ),
            ),
			'trash_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/trash-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'trashproduct',						
                    ),
                ),
            ),
			'trash_coupon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/trash-coupon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'trashcoupon',						
                    ),
                ),
            ),
			'getsub_categories' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/getsub-categories',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'subcategories',						
                    ),
                ),
            ),
			'add_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/add-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'addproduct',						
                    ),
                ),
            ),
			'upload_prodpics' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/upload-prodpics',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'uploadproduct',						
                    ),
                ),
            ),
			'remove_selected' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-selected',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removeselected',						
                    ),
                ),
            ),
			'trash_propic' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/trash-propic',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'trashpropic',						
                    ),
                ),
            ),
			'wish_list' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/wish-list',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'wishlist',						
                    ),
                ),
            ),
			'remove_favorite' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/remove-favorite',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removefavorite',						
                    ),
                ),
            ),
			'favourite_shops' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/favourite-shops',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'favoriteshops',						
                    ),
                ),
            ),
			'report_product' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/report-product',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'reportproduct',						
                    ),
                ),
            ),
			'report_comment' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/report-comment',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'reportcomment',						
                    ),
                ),
            ),
			'report_review' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/report-review',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'reportreview',						
                    ),
                ),
            ),
			'product_comment' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/product-comment',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'procomment',						
                    ),
                ),
            ),
			'trash_wishlist' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/trash-wishlist',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'trashwishlist',						
                    ),
                ),
            ),
			'addwish_list' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/addwish-list',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'addwishlist',						
                    ),
                ),
            ),
			'removewish_list' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/removewish-list',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'removewish',						
                    ),
                ),
            ),			
			'edit_product' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/edit-product[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'editproduct',
                    ),
                ),
            ),
			'add_coupon' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/add-coupon',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'addcoupon',						
                    ),
                ),
            ),
			'generate_code' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/generate-code',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'generatecode',						
                    ),
                ),
            ),
			'edit_coupon' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/edit-coupon[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'editcoupon',
                    ),
                ),
            ),	
			'download_digital' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/download-digital[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'downloaddigital',
                    ),
                ),
            ),
			'digital_download' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/digital-download[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'digitaldownload',
                    ),
                ),
            ),
			'front_resetpassword' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/reset-password[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\IndexController::class,
                        'action'     => 'userresetpassword',
                    ),
                ),
            ),
			'front_prodetail' =>array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/view-product[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',					
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'viewproduct',
                    ),
                ),
            ),
			'front_thanks' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/thank-you[/:user]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'thanks',
						'__NAMESPACE__' => 'AuthAcl\Controller',
                    ],
                ],
            ],			

			'front_activate' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/activate[/:key]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'activate',						
                    ),
                ),
            ),
		

			'front_checkforgotemail' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/checkforgotemail[/:user_email]',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checkforgotemail',						
                    ),
                ),
            ),

			'front_logout' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'logout',						
                    ),
                ),
            ),	

			'front_profile' => array(
                /*'type' => Literal::class,*/
                'type'    => Segment::class,
                'options' => array(
                    'route' => '/profile[/:tabid]',
                    'defaults' =>array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'index',                        
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                ),
            ),
			
			'front_changepwd' => array(
                /*'type' => Literal::class,*/
                'type'    => Segment::class,
                'options' => array(
                    'route' => '/change-password[/:tabid]',
                    'defaults' =>array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'index',                        
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                ),
            ),

            /* after login action */
            'front_memberupdateprofile' => array(
                /*'type' => Literal::class,*/
                'type'    => Segment::class,
                'options' => array(
                    'route' => '/update-profile[/:tabid]',
                    'defaults' =>array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'memberupdateprofile',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                ),
            ),

            /* without login action */
            'front_verify_email' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/verify-email[/:key]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'verifiedemail',                     
                    ),
                ),
            ),

            /* after login action */
            'front_updatememberprofileimages' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/updatememberprofileimages',
                    'defaults' =>array(
                        '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'updatememberprofileimages',                       
                    ),
                ),
            ),            

            'front_updateprofileimage' => array(
            	'type' => Literal::class,
            	'options' => array(
            		'route' => '/updateprofileimage',
            		'defaults' =>array(
						'__NAMESPACE__' => 'AuthAcl\Controller',
                		'controller' => Controller\ProfileController::class,
                		'action'     => 'updateprofileimage',						
                	),
            	),
            ),
			'become_seller' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/become-seller',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'becomeseller',						
                    ),
                ),
            ),
			'badge_request' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/badge-request',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'badgerequest',						
                    ),
                ),
            ),			
			'front_checkemail' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/checkemail',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'usercheckemail',						
                    ),
                ),
            ),
			'front_checkuname' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/checkuname',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'usercheckuname',						
                    ),
                ),
            ), 
			
			

			

			

			

			

			

			

			'checkemailexist' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/checkemailexist',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'checkemailexist',						
                    ),
                ),
            ), 


			

			 

			'front_checkpassword' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/checkpassword',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'frcheckpassword',						
                    ),
                ),
            ), 

			
			'front_checknewpassword' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/checknewpassword',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\IndexController::class,
                        'action'     => 'frchecknewpassword',						
                    ),
                ),
            ),    

			  
			'compareoldpassword' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/compareoldpassword',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'compareoldpassword',						
                    ),
                ),
            ),  
/*
			
			'change-password' => array(
                'type' => Literal::class,
                'options' => array(
                    'route' => '/change-password',
                    'defaults' => array(
					    '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\ProfileController::class,
                        'action'     => 'changepassword',						
                    ),
                ),
            ),*/   
		
			'resendverification' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/resendverification',
                    'defaults' => array(
                        'controller' => Controller\IndexController::class,
                        'action'     => 'resendverification',
						'__NAMESPACE__' => 'AuthAcl\Controller',
                    ),
                ),
            ),

			'front_facebook' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/facebook',
                    'defaults' => array(
        			 '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\SocialController::class,
                        'action'     => 'fblogin',      
                    ),
                ),
            ),

			'front_google' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/google',
                    'defaults' => array(
        			 '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\SocialController::class,
                        'action'     => 'googlelogin',      
                    ),
                ),
            ),
			'front_twitter' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/twitter',
                    'defaults' => array(
        			 '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\SocialController::class,
                        'action'     => 'twitterlogin',      
                    ),
                ),
            ),
			'front_twitterhandler' => array(
                'type' => Segment::class,
                'options' => array(
                    'route' => '/twitterhandler',
                    'defaults' => array(
        			 '__NAMESPACE__' => 'AuthAcl\Controller',
                        'controller' => Controller\SocialController::class,
                        'action'     => 'twitterhandler',      
                    ),
                ),
            ),			
        ),
    ),

	 'service_manager' => array(
        'factories' => array(
          	\Zend\Authentication\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\AuthAdapter::class => Service\Factory\AuthAdapterFactory::class,
            Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
            Service\UserManager::class => Service\Factory\UserManagerFactory::class,
        ),
    ),

	'controllers' => array(
        'factories' => array(
			Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class, 
			Controller\ProfileController::class => Controller\Factory\ProfileControllerFactory::class ,	
			Controller\SocialController::class => Controller\Factory\SocialControllerFactory::class ,	
        ),
    ),
	
	'controller_plugins' => array(
		'invokables' => array(
			'Image' => 'Application\Controller\Plugin\Image',
			'ImageCrop' => 'Application\Controller\Plugin\ImageCrop',
		)
	),
	
	'view_helpers' => array(
		  'invokables' =>array(
		   'renderForm' => 'Application\View\Helper\FrontRenderForm',   
		   'GetMessages' => 'Application\View\Helper\GetMessages',   
		  ),
	 ),
 
    'view_manager' => array(
		'template_map' => array(
			 'auth-acl/index/index' => __DIR__ . '/../view/auth-acl/index/index.phtml',
			 'auth-acl/profile' => __DIR__ . '/../view',
		),
		'template_path_stack' => array(
			 __DIR__ . '/../view'
		),
    ),
);
