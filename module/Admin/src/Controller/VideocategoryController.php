<?php
/* * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Video Category controller
* * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container;
use Admin\Form\VideocategoryForm;
use Zend\Db\Sql\Expression;
use Admin\Form\MasterForm;


class VideocategoryController extends AbstractActionController
{    

	private $AbstractModel,$Adapter,$EmailModel;

	/* Constructor of the controller */
    public function __construct($AbstractModel,Adapter $Adapter,$adminMsgsession,$EmailModel,$config_data)
    {
        $this->SuperModel = $AbstractModel;
		$this->Adapter = $Adapter;
		$this->EmailModel = $EmailModel;
		$this->adminMsgsession = $adminMsgsession;	
		$this->view = new ViewModel();
		$this->SITE_CONFIG = $config_data;
    }

	/* Invoke common things eg. session, database adaptor etc. */
	public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $session = $container->get(SessionContainer::class);
        $db = $container->get(DbAdapter::class);
	}
	
	/* List page to show added video categories */
	public function videocategorylistAction()
	{
		$this->layout()->setVariable('pageHeading',$this->layout()->translator->translate("Video Category"));
		return new ViewModel(array('page_icon'=>'fa fa-file-text-o','pageHeading'=>$this->layout()->translator->translate("page_head_txt")));
	}

	/* Ajax request - Fetch records of added video categories */
	public function getvideocategorylistrecordsAction()
	{
		$dbAdapter = $this->Adapter;
		$aColumns = array('vc_id','vc_name','vc_created');
		$sIndexColumn = 'vc_id';
		$sTable = T_VIDEO_CATEGORY;

		/* Table Setting */
		{
			/* Paging */
			$sLimit = "";
			if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
			{
				$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
			}
			
			/* Ordering */
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
			for ($i=0 ; $i<count($aColumns) ; $i++)
			{
				if (isset($_GET['bSearchable_'.$i]) and $_GET['bSearchable_'.$i] == "true" and $_GET['sSearch_'.$i] != '')
				{
					if($sWhere == ""){
						$sWhere = "WHERE ";
					} else {
						$sWhere .= " AND ";
					}
					$sWhere .= "".$aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
				}
			}

		}
		/* End Table Setting */

		$sQuery = " SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM   $sTable $sWhere $sOrder $sLimit"; 
		$results = $dbAdapter->query($sQuery)->execute();
		$qry = $results->getResource()->fetchAll();

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

		$j = 1;
		foreach($qry as $row1)
		{
			$row = array();
 			$row[] = $j;

			$row[] = '<input class="elem_ids checkboxes" type="checkbox" name="'.$sTable.'['.$row1[$sIndexColumn].']"  value="'.$row1[$sIndexColumn].'"><label for="checkbox4"></label>';

  			$row[] = nl2br($row1['vc_name']);			

			$row[] = date('d M, Y h:i a',strtotime($row1['vc_created']));

			$row[] = '<a href="'.ADMIN_APPLICATION_URL.'/manage-video-category/'.myurl_encode($row1['vc_id']).'"><span class="btn btn-sm btn-icon btn-primary btn-round waves-effect waves-classic"><i class="icon md-edit"></i></span></a>';

  			$output['aaData'][] = $row;

			$j++;
		}	

		echo json_encode($output);
		exit();
	}


	/* Add new video category */
	public function addvideocategoryAction()
	{
		$this->layout()->setVariable('backUrl', 'video-category');
		$id = $this->params()->fromRoute("id");
		$page_title = $this->layout()->translator->translate("add_video_category_txt");
		$form = new VideocategoryForm($this->layout()->translator);
		$form->addvideocategory();

		if($id != ''){
			$page_title = $this->layout()->translator->translate("edit_video_category_txt");
			$id = myurl_decode($id);
			$masterData = $this->SuperModel->Super_Get(T_VIDEO_CATEGORY, "vc_id=:categoryid","fetch",array("warray"=>array("categoryid"=>$id)));
			if(empty($masterData)){
				$this->adminMsgsession['infoMsg']  = $this->layout()->translator->translate("no_record_found");
				return $this->redirect()->toUrl(ADMIN_APPLICATION_URL.'/video-category');		
			}
			$form->populateValues(array("vc_name"=>$masterData["vc_name"]));
		}

	   	$ismatch_data = array();
		$this->layout()->setVariable('pageHeading',$page_title);
		$request = $this->getRequest();	

        if($request->isPost()) {

			$form->setData($request->getPost());

			if($form->isValid()){
				$Formdata = $form->getData();
				$ismatch_data = array();

				if($id == ''){
					// add case
					$ismatch_data = $this->SuperModel->Super_Get(T_VIDEO_CATEGORY, "LOWER(vc_name)=:catname","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata["vc_name"]))))));

				} else {
					// update case
					$ismatch_data = $this->SuperModel->Super_Get(T_VIDEO_CATEGORY, "LOWER(vc_name)=:catname and vc_id!=:categoryid","fetch",array("warray"=>array("catname"=>addslashes(strtolower(trim($Formdata["vc_name"]))),"categoryid"=>$id)));
				}

				if(empty($ismatch_data)){

					$posted_data = array();
					$posted_data['vc_name'] = $Formdata['vc_name'];
					$posted_data['vc_created'] = date('Y-m-d h:i:s');

					if($id != ''){
						$is_updated = $this->SuperModel->Super_Insert(T_VIDEO_CATEGORY,$posted_data,"vc_id='".$id."'");
						if(is_object($is_updated) && $is_updated->success){
							$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("updated_video_category_txt");
						} else {
							$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("some_error_occurred_txt");
							return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/video-category');		
						}

					} else {

						$is_Inserted = $this->SuperModel->Super_Insert(T_VIDEO_CATEGORY,$posted_data);
						if(is_object($is_Inserted) && $is_Inserted->success){
							$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("added_video_category_txt");
						} else {
							$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("some_error_occurred_txt");
						}
					}
						return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/video-category');

				} else {

					$this->adminMsgsession['errorMsg'] = $this->layout()->translator->translate("video_category_already_exists");
				}	

			} else {

			}

		}

		$view = new ViewModel();
		$view->setVariable('form',$form);
		return $view;
	}

	/* Delete video category */
	public function removevideocategoryAction()
	{
		$request = $this->getRequest();
		if($request->isPost()) {
			$del = $request->getPost(T_VIDEO_CATEGORY);
			foreach($del as $key=>$ids)
			{  
				$isdeleted = $this->SuperModel->Super_Delete(T_VIDEO_CATEGORY,'vc_id ="'.$ids.'"');	 
			} 
		}
		$this->adminMsgsession['successMsg'] = $this->layout()->translator->translate("deleted_video_category_txt");
		return $this->redirect()->tourl(ADMIN_APPLICATION_URL.'/video-category');
	}

}