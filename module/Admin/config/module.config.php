<?php
namespace Admin;
use Zend\Router\Http\Segment;

return array(
    // The following section is new and should be added to your file:
    'router' => array(
        'routes' => array(
		
			'adminlogin' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/login',
                    'defaults' => array(
						'module' => BACKEND,
                    	'controller' => Controller\IndexController::class,
                    	'action'     => 'login',
						'__NAMESPACE__' => 'Admin\Controller',
            		),
            	),
				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			'admin_changeemail' => array(
		   'type'    => Segment::class,
			'options' => array(
				'route' =>  '/'.BACKEND.'/admin-change-email',		
				'defaults' => array(
					'module' => BACKEND,
					'__NAMESPACE__' => 'Admin\Controller',
					'controller' => Controller\ProfileController::class,
					'action'     => 'checkchangeemail',						
				),
			),
		), 
		
		'export_payouts' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/export-payouts[/:tid]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'exportpayouts',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),
    
        ),

		'admin_profile_change_email' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/admin-update-email',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\ProfileController::class,
					'action'     => 'changeemail',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_pre_messages' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/pre-messages',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\IndexController::class,
					'action'     => 'prevmessages',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_post_message' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/post-message',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\IndexController::class,
					'action'     => 'postmessage',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_export-user' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/' . BACKEND . '/export-user[/:sdfsd]',
				'constraints' => array(
					'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',
				),
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\IndexController::class,
					'action'     => 'exportuser',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_approve_withdrawal' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/approve-withdrawal',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\StaticController::class,
					'action'     => 'approvewithdrawal',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_decline_withdrawal' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/decline-withdrawal',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\StaticController::class,
					'action'     => 'declinewithdrawal',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		'admin_loadmore_messages' => array(
			'type'    => Segment::class,
			'options' => array(
				'route'    => '/'.BACKEND.'/loadmore-messages',
				'defaults' => array(
					'module' => BACKEND,
					'controller' => Controller\IndexController::class,
					'action'     => 'loadmoremessages',
					'__NAMESPACE__' => 'Admin\Controller',
				),
			),
		),
		    'admin_verify_email' => array(
				'type' => Segment::class,
				'options' => array(
					'route' => '/'.BACKEND.'/verify-email[/:key]',
					'defaults' => array(
						'__NAMESPACE__' => 'Admin\Controller',
						'controller' => Controller\IndexController::class,
						'action'     => 'verifiedemail',						
					),
				),
			),
		
			
			'config' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/settings',
                    'defaults' => array(
						'module' => BACKEND,
                    	'controller' => Controller\StaticController::class,
                    	'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
						'type'=>1
            		),
            	),

				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			
			'manage_wickedshop' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-wickedshop',
                    'defaults' => array(
						'module' => BACKEND,
                    	'controller' => Controller\StaticController::class,
                    	'action'     => 'wickedshop',
						'__NAMESPACE__' => 'Admin\Controller',
						'type'=>1
            		),
            	),

				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			
			
			'social-config' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/social',
                    'defaults' => array(
						'module' => BACKEND,
                    	'controller' => Controller\StaticController::class,
                    	'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
						'type'=>3					
            		),
            	),

				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			
			'payment-config' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/config-payment',
                    'defaults' => array(
						'module' => BACKEND,
                    	'controller' => Controller\StaticController::class,
                    	'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
						'type'=>2
            		),
            	),

				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			
			'static' => array(
                'type'    => Segment::class,
                'options' => array(                   
					 'route'    => '/'.BACKEND.'/static[/:type]',
					 'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  
                     ),
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
            		),
            	),
				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
            ),
			
			'logindashboard' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND,
                    'defaults' => array(
						'module' => '/'.BACKEND,
                        'controller' => Controller\IndexController::class,
                        'action'     => 'dashboard',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
				
            ),
			
			'logindashboard1' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/',
                    'defaults' => array(
						'module' => '/'.BACKEND,
                        'controller' => Controller\IndexController::class,
                        'action'     => 'dashboard',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>
							'/[:controller[/:action]]',
							'constraints' => array(
								'controller' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
								'action' =>
								'[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				),
				
            ),
			
			
			'dashboard' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/dashboard',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\IndexController::class,
                        'action'     => 'dashboard',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'forgotpassword' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/forgot-password',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\IndexController::class,

                        'action'     => 'forgotpassword',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'resetpassword' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/reset-password[/:key]',

					'constraints' => array(

                         'emailtemp_key'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\IndexController::class,

                        'action'     => 'resetpassword',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'adminlogout' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/admin-logout',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\IndexController::class,

                        'action'     => 'logout',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			/* admin Photos categories { */

			'admin_checknewcategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/checknewcategory',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'checkexistcategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'withdrawal_requests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/withdrawal-requests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'withdrawrequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'completed_refunds' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/completed-refunds',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'completedrefunds',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'getcompleted_refunds' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getcompleted-refunds',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getcompletedrefunds',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'refund_requests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/refund-requests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'refundrequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'getrefund_requests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getrefund-requests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getrefundrequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'order_info' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/order-info',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'orderinfo',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'approve_refund' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/approve-refund',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'approverefund',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'seller_applications' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/seller-applications',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'sellerapplications',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'badge_requests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/badge-requests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'verificationbadges',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'review_rating' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/review-rating',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'viewrating',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'getwithdraw_requests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getwithdraw-requests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getwithdrawrequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'getreviews' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/get-reviews',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getallrating',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'view_orders' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/view-orders',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'vieworders',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'getorders' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getorders',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getallorders',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'comment_reports' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/comment-reports',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'commentreports',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'admin_photocategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/photos-category',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'photocategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_getcategorieslist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getphotocategory[/:type]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getphotocategorieslist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			'admin_add_photos' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/manage-photos[/:id]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addphotos',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_photoslist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/photos-list[/:id]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'allphotos',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_photos_media'=>array(

			 'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/photos-media',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'photosmedia',//[/:assignment]

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

			

			),

			'admin_remove_gallery'=>array(

				'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/remove-gallery',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'photosmediaremove',//[/:assignment]

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

			

			

				),

			/* admin Photos categories End } */

			/* nw subscribers { */

			'admin_subscribers' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/subscribers[/:subscriber]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'subscriberslist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_getsubscribers' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getsubscribers[/:subscriber]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getsubscriberslist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			/* end subscribers list } */

			

			

			

			

			

			'emailtemplate' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/emailtemplate',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'emailtemplate',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'getemailtemplate' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getemailtemplate',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getemailtemplate',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'getsellerrequests' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getsellerrequests',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getsellerrequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'getverificationbadges' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getverificationbadges',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getbadgerequests',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'accept_badge' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/accept-badge',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'acceptbadgerequest',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'decline_badge' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/decline-badge',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'declinebadgerequest',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),	
			'accept_store' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/accept-store',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'acceptstorerequest',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),
			'decline_store' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/decline-store',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'declinestorerequest',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),
                ),
            ),	
			'editemailtemplate' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/editemailtemplate[/:emailtemp_key][/:emailtemp_langid][/:emailtemp_proid]',

					'constraints' => array(

                         'emailtemp_key'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'editemailtemplate',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			/*Admin general  { */

			'admin_readnotify'=>array(

					 'type' => Segment::class,

					 'options' =>array(

                    'route' => '/'.BACKEND.'/readnotify',

                    'defaults' =>array(

						'module' => BACKEND,

					    '__NAMESPACE__' => 'Admin\Controller',

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'readnotify',						

                    ),

                ),

			),

			'adminprofile' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/profile[/:tabtype]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'index',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            	'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

			),

			

			'adminpassword' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/change-password',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'changepassword',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'adminimage' => array(

            	'type'    => Segment::class,

            	'options' => array(

            		'route'    => '/'.BACKEND.'/profile-image',

                	'defaults' => array(

						'module' => BACKEND,

                    	'controller' => Controller\ProfileController::class,

                    	'action'     => 'image',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'user/usercheckemail' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/user/usercheckemail[/:user_id]',

					'constraints' => array(

                         'user_id'     => '[0-9]*',                   

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\UserController::class,

                        'action'     => 'usercheckemail',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

		

			'profile/checkemail' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/profile/checkemail',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkemail',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'profile/checkusername' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/profile/checkusername',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkusername',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			'checkotherusername' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/checkotherusername',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkotherusername',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'checkotheremail' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/checkotheremail',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkotheremail',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'profile/checkpassword' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/profile/checkpassword',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkpassword',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'profile/checkoldpass' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/profile/checkoldpass',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'checkoldpass',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			/*Admin general  } */

			

			

			

			/* Master List {*/

			'admin_masterlist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/master-list/:mastertype',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\MasterController::class,

                        'action'     => 'index',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_getmasterlist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getmasterlist/:mastertype',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\MasterController::class,

                        'action'     => 'getmasterlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			'admin_manage_masterlist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/managemasterlist[/:mastertype][/:id]',

					'constraints' => array(

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\MasterController::class,

                        'action'     => 'managemasterlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			/* Master List } */

			

			

			'admin_add_photocategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/manage-photocategory[/:id]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addphotocategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_remove_photocategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/remove-photo-category',

					'constraints' => array(

                     ),

                    

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removephotoscategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			

			/* Team Module { */

			'admin_addteam' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/manage-team[/:member]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addteam',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_viewseller' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/view-seller[/:seller]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'viewseller',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
	
			'admin_exportseller' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/export-seller-applications',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'exportseller',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			'admin_team' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/team',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'teamlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'admin_getteam' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getteam',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getteamlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'admin_removeteam' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removeteam',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removeteamlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

				

			/* }*/


			/*-----------------------///////////////---------------------------------------------*/

			/* Users Module {*/

			/* List of users */
			'admin_users' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/users[/:user_id]',					
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
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

			/* Fetch users list records by ajax */
            'admin_getusers' => array(
                'type'    => Segment::class,
                'options' => array(
                     'route'    => '/'.BACKEND.'/getusers[/:user_id]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  
                     ),
					'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\UserController::class,
                        'action'     => 'getuserslist',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			'admin_chat' => array(
                'type'    => Segment::class,
                'options' => array(
					'route'	=> '/'.BACKEND.'/chat[/:user_id]',	
					'defaults' => array(
						'module'		=> BACKEND,
						'controller'	=> Controller\UserController::class,
						'action'		=> 'chat',
						'__NAMESPACE__'	=> 'Admin\Controller',
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
            /* View user account information */
			'admin_cancel_coupon' => array(
                'type'    => Segment::class,
                'options' => array(
					'route'	=> '/'.BACKEND.'/cancel-coupon[/:user_id]',	
					'defaults' => array(
						'module'		=> BACKEND,
						'controller'	=> Controller\UserController::class,
						'action'		=> 'cancelcoupon',
						'__NAMESPACE__'	=> 'Admin\Controller',
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
			'admin_account' => array(
                'type'    => Segment::class,
                'options' => array(
					'route'	=> '/'.BACKEND.'/view-account[/:user_id]',	
					'defaults' => array(
						'module'		=> BACKEND,
						'controller'	=> Controller\UserController::class,
						'action'		=> 'account',
						'__NAMESPACE__'	=> 'Admin\Controller',
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

			/* Auto login into user account from admin panel */
			'admin_access_account' => array(
                'type'    => Segment::class,
                'options' => array(
					'route'	=> '/'.BACKEND.'/access-account[/:user_id]',
					'constraints' => array(
						  'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                
					),
					'defaults' => array(
						'module'		=> BACKEND,
						'controller'	=> Controller\UserController::class,
						'action'		=> 'accessaccount',
						'__NAMESPACE__'	=> 'Admin\Controller',
					),
				),
				'may_terminate' => true,
				'child_routes'  => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/',
							'defaults' => array(),
						),
					),
				),
            ),

			/* Delete users */
			'admin_removeusers' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-users',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\UserController::class,
								'action'     => 'removeuserlist',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),

            /* Change status of user account */
			'ajaxsetstatus' => array(
                'type'    => 'segment',
                 'options' => array(
                     'route'    =>  '/'.BACKEND.'/ajaxsetstatus[/:type][/:id][/:status]',
                     'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
						 'id'     => '[0-9]+',   
						 'status'     => '[0-9]+',                   
                     ),
                     'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => Controller\AjaxController::class,
                        'action' => 'setstatus'
                     ),
                 ),
             ),
			/* }*/			
			'ajaxsetstorestatus' => array(
                'type'    => 'segment',
                 'options' => array(
                     'route'    =>  '/'.BACKEND.'/ajaxsetstorestatus[/:type][/:id][/:status]',
                     'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
						 'id'     => '[0-9]+',   
						 'status'     => '[0-9]+',                   
                     ),
                     'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => Controller\AjaxController::class,
                        'action' => 'setstorestatus'
                     ),
                 ),
             ),
			'ajaxsetfavstatus' => array(
                'type'    => 'segment',
                 'options' => array(
                     'route'    =>  '/'.BACKEND.'/ajaxsetfavstatus[/:type][/:id][/:status]',
                     'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
						 'id'     => '[0-9]+',   
						 'status'     => '[0-9]+',                   
                     ),
                     'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => Controller\AjaxController::class,
                        'action' => 'setfavstatus'
                     ),
                 ),
             ),
			'ajaxsetbeststatus' => array(
                'type'    => 'segment',
                 'options' => array(
                     'route'    =>  '/'.BACKEND.'/ajaxsetbeststatus[/:type][/:id][/:status]',
                     'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',   
						 'id'     => '[0-9]+',   
						 'status'     => '[0-9]+',                   
                     ),
                     'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => Controller\AjaxController::class,
                        'action' => 'setbeststatus'
                     ),
                 ),
             ),
			/* Members Module {*/

			/* List of members */
			'admin_members' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/members[/:user_id]',					
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\MemberController::class,
                        'action'     => 'index',
						'__NAMESPACE__' => 'Admin\Controller',
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

			/* Fetch users list records by ajax */
            'admin_getmembers' => array(
                'type'    => Segment::class,
                'options' => array(
                     'route'    => '/'.BACKEND.'/getmembers[/:user_id]',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  
                     ),
					'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\MemberController::class,
                        'action'     => 'getmemberslist',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),

            /* View user account information */
			'admin_memberaccount' => array(
                'type'    => Segment::class,
                'options' => array(
					'route'	=> '/'.BACKEND.'/view-member-account[/:user_id]',	
					'defaults' => array(
						'module'		=> BACKEND,
						'controller'	=> Controller\MemberController::class,
						'action'		=> 'memberaccount',
						'__NAMESPACE__'	=> 'Admin\Controller',
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

            /* Delete members */
			'admin_removemembers' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-members',
					'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\MemberController::class,
								'action'     => 'removememberlist',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),

           
			/* }*/








			

			/* Pages Module {*/

			'admin_addpages' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/addpages',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addpages',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_pages' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/pages',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'pages',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_getpages' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getpages[/:page]',

					'constraints' => array(

                         'page'     => '[0-9]+',                   

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getpages',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'admin_editpages' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/editpages[/:id]',

					'constraints' => array(

                        // 'id'     => '[0-9]+',                   

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'editpages',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_removepages' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removepages',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removepages',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			/* }*/

			

			

			
			
			

			

			

			'admin_errorpage' => array(

                'type'    => Segment::class,

                'options' => array(                   

					'route'    => '/'.BACKEND.'/errorpage',					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\IndexController::class,

                        'action'     => 'errorpage',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),	

			

			'admin_checkemail' => array(

               'type'    => Segment::class,

                'options' => array(

                    'route' =>  '/'.BACKEND.'/checkemail',		

                    'defaults' => array(

						'module' => BACKEND,

					    '__NAMESPACE__' => 'Admin\Controller',

                        'controller' => Controller\IndexController::class,

                        'action'     => 'checkemail',						

                    ),

                ),

            ), 

			'admin_editorupload' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/upload-media',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\IndexController::class,

                        'action'     => 'uploadfiles',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			'admin_uploadavatar' => array(

               'type'    => Segment::class,

                'options' => array(

                    'route' =>  '/'.BACKEND.'/uploadavatar',		

                    'defaults' => array(

						'module' => BACKEND,

					    '__NAMESPACE__' => 'Admin\Controller',

                        'controller' => Controller\ProfileController::class,

                        'action'     => 'uploadavatar',						

                    ),

                ),

            ), 

			

			/* FAQ Module { */

			'admin_faqs' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/faqs',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'faqslist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'admin_getfaqs' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getfaqslist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getfaqs',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'managefaqs' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/managefaqs[/:faq]',

					'constraints' => array(

                         'emailtemp_key'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'managefaqs',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_removefaqs' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removefaqslist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removefaqs',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'admin_removefaqsheading' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/remove-faq-category',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removefaqsheading',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			

			'admin_faq_heading' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/faq-category',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'faqheading',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_getfaqheading' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/get-faq-category',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getfaqheading',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			'admin_addfaqheading' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/manage-faq-category[/:faq]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addfaqheading',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			

			

			/* } */
	/* Sub Category Module */
	'admin_subcategories' => array(

		'type'    => Segment::class,

		'options' => array(

			'route'    => '/'.BACKEND.'/subcategories',

			'defaults' => array(

				'module' => BACKEND,

				'controller' => Controller\StaticController::class,

				'action'     => 'subcategorylist',

				'__NAMESPACE__' => 'Admin\Controller',

			),

		),

		'may_terminate' => true,

		'child_routes'  => array(

			'default' => array(

				'type' => 'Segment',

				'options' => array(

					'route' =>

					'/[:controller[/:action]]',

					'constraints' => array(

						'controller' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

						'action' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

					),

					'defaults' => array(),

				),

			),

		),

	),
	
	'admin_getsubcategory' => array(

		'type'    => Segment::class,

		'options' => array(

			'route'    => '/'.BACKEND.'/getsubcategorylist',

			'defaults' => array(

				'module' => BACKEND,

				'controller' => Controller\StaticController::class,

				'action'     => 'getsubcategory',

				'__NAMESPACE__' => 'Admin\Controller',

			),

		),

		'may_terminate' => true,

		'child_routes'  => array(

			'default' => array(

				'type' => 'Segment',

				'options' => array(

					'route' =>

					'/[:controller[/:action]]',

					'constraints' => array(

						'controller' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

						'action' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

					),

					'defaults' => array(),

				),

			),

		),

	),
	'managesubcategory' => array(

		'type'    => Segment::class,

		'options' => array(

			'route'    => '/'.BACKEND.'/managesubcategory[/:subcategory]',

			'constraints' => array(

				 'emailtemp_key'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

			 ),

			'defaults' => array(

				'module' => BACKEND,

				'controller' => Controller\StaticController::class,

				'action'     => 'managesubcategory',

				'__NAMESPACE__' => 'Admin\Controller',

			),

		),

		'may_terminate' => true,

		'child_routes'  => array(

			'default' => array(

				'type' => 'Segment',

				'options' => array(

					'route' =>

					'/[:controller[/:action]]',

					'constraints' => array(

						'controller' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

						'action' =>

						'[a-zA-Z][a-zA-Z0-9_-]*',

					),

					'defaults' => array(),

				),

			),

		),

	),
		/*   Category  Module  */
	
	'admin_category' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/category',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'categorylist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

	
	
			'admin_getcategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getcategorylist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getcategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

			'managecategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/managecategory[/:category]',

					'constraints' => array(

                         'emailtemp_key'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'managecategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_removecategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removecategorylist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removecategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'admin_removesubcategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removesubcategorylist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removesubcategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
	
		/*   Category  Module  */

			/* Slider  Module { */

			'admin_slider' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/slider',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'slider',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			'admin_getslider' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getslider[/:slidetype]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getslider',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			

		

			

			'admin_addslider' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/addslider[/:slider_id]',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'addslider',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),

			 'admin_removeslider' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/removeslider',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'removeslider',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			/* }*/
						
			/* Blog Module */
			/*'admin_blogcomments' => array(
                'type'    => Segment::class,
                'options' => array(
                     'route'    => '/'.BACKEND.'/blog-comments',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  
                     ),
					'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'blogcomments',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			
			'admin_blogs' => array(
                'type'    => Segment::class,
                'options' => array(
                     'route'    => '/'.BACKEND.'/blogs',
					'constraints' => array(
                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  
                     ),
					'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'blogs',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			
			'admin_getblogcomments' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getblogcomments',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getblogcomments',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'admin_getblogs' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getblogs',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getblogs',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_addblog' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-blog[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'addblog',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),*/
			'admin_viewproduct' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/view-product[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'viewproduct',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			'admin_removeproduct' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-product',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'removeproduct',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_removerating' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-rating',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'removerating',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_acceptproduct' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/accept-product',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'acceptproduct',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_addprofavorite' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/addprod-favorite',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'addprodfav',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_declineprofavorite' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/declineprod-favorite',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'removeprodfav',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_declineproduct' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/decline-product',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'declineproduct',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			'admin_removeblog' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-blog',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'removeblog',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			
			'admin_removecomment' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-comment',
					 'defaults' => array(
								'module' => BACKEND,
								'controller' => Controller\StaticController::class,
								'action'     => 'removecomment',
				  				 '__NAMESPACE__' => 'Admin\Controller',
							),
						),
            ),
			
			/* Product  Module */
			'admin_coupons' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/coupons',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'coupons',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'admin_getcoupons' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getcoupons',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getcoupons',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'admin_managecoupon' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-coupon[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'managecoupon',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			'admin_products' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/products',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'productlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'reported_products' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/reported-products',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'reportedproducts',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'review_reports' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/review-reports',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'reviewreports',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			'admin_getreportedproducts' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getreportedproducts',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getreportedproducts',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'admin_getreportedreviews' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getreportedreviews',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getreportedreviews',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'admin_getreportedcomments' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getreportedcomments',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getreportedcomments',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			'admin_getproducts' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getproductlist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getproducts',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
	
			'admin_getfavproductlist' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getfavproductlist',

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getfavproductlist',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

				'may_terminate' => true,

				'child_routes'  => array(

					'default' => array(

						'type' => 'Segment',

						'options' => array(

							'route' =>

							'/[:controller[/:action]]',

							'constraints' => array(

								'controller' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

								'action' =>

								'[a-zA-Z][a-zA-Z0-9_-]*',

							),

							'defaults' => array(),

						),

					),

				),

            ),
			
			/* Blog Catgories Module */
			'admin_blogcategories' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/blog-categories',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'blogcategory',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_getblogcategory' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/getblog-categorylist',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getblogcategories',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_addblogcategory' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-blog-category[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'addblogcategory',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),

			/* Photo gallery Module */
			'admin_photogallery' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/photo-gallery',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'photogallery',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_getphotogallery' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/get-photogallery',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'getphotogallery',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
	
			'admin_managephotogallery' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-photo-gallery[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'managephotogallery',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
	
			'admin_removephotogallery' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/removephoto-gallery',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'removephotogallery',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),	
			
			/* How It Works Page Module */
			'admin_howitwoks' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/how-it-works-page',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'howitworks',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
			
			'admin_gethowitworks' => array(

                'type'    => Segment::class,

                'options' => array(

                    'route'    => '/'.BACKEND.'/get-howitworks',

					

                    'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'gethowitworks',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),
	
			'admin_managehowitworks' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-how-it-works[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'managehowworks',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			

			/* Home Page  Module { */

			'admin_homepage' => array(

                'type'    => Segment::class,

                'options' => array(

                     'route'    => '/'.BACKEND.'/homepage',

					'constraints' => array(

                         'type'     => '[a-zA-Z][a-zA-Z0-9_-]*',                  

                     ),

					'defaults' => array(

						'module' => BACKEND,

                        'controller' => Controller\StaticController::class,

                        'action'     => 'homepage',

						'__NAMESPACE__' => 'Admin\Controller',

                    ),

                ),

            ),

			

			/* } */


			/* * * * * * * * * * * * * * * * * * 
			* Start Video category module 
			* * * * * * * * * * * * * * * * * */

			/* List of video categories */
			'admin_videocategorylist' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/video-category',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\VideocategoryController::class,
                        'action'     => 'videocategorylist',
						'__NAMESPACE__' => 'Admin\Controller',
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


			/* Fetch records of video categories */
			'admin_getvideocategorylistrecords' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/get-video-category-list-records',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\VideocategoryController::class,
                        'action'     => 'getvideocategorylistrecords',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			
			/* Add video category */
			'admin_addvideocategory' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/manage-video-category[/:id]',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\VideocategoryController::class,
                        'action'     => 'addvideocategory',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),		

            /* Remove video category */
			'admin_removevideocategory' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/remove-video-category',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\VideocategoryController::class,
                        'action'     => 'removevideocategory',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),	

            /* * * * * * * * * * * * * * * * * * 
			* End Video category module 
			* * * * * * * * * * * * * * * * * */	
			
			'admin_removeblogcategory' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/'.BACKEND.'/removeblog-categories',
                    'defaults' => array(
						'module' => BACKEND,
                        'controller' => Controller\StaticController::class,
                        'action'     => 'removeblogcategory',
						'__NAMESPACE__' => 'Admin\Controller',
                    ),
                ),
            ),
			
			

        ),

    ),

 	

	'view_manager' => array(
		'template_path_stack' => array(
			'admin' => __DIR__ . '/../view',
		),
	),


	'controller_plugins' => [
		'invokables' => [
			'Image' => 'Application\Controller\Plugin\Image',
			'ImageCrop' => 'Application\Controller\Plugin\ImageCrop',
			'CsvExport' => 'Application\Controller\Plugin\CsvExport',
    		'CsvImport' => 'Application\Controller\Plugin\CsvImport',
		],
		'aliases' => [


 		],
	],
	

	'view_helpers' => array(
		'invokables' => array(	
			'NavHelper' => 'Admin\View\Helper\NavHelper',		 
		),
	),	

	'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
			Controller\StaticController::class => Controller\Factory\StaticControllerFactory::class,
			Controller\ProfileController::class => Controller\Factory\ProfileControllerFactory::class,
			Controller\AjaxController::class => Controller\Factory\AjaxControllerFactory::class,
			Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
			Controller\MemberController::class => Controller\Factory\MemberControllerFactory::class,
			Controller\VideocategoryController::class => Controller\Factory\VideocategoryControllerFactory::class,
        ],
    ],
);