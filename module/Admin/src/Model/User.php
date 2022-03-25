<?php

namespace Admin\Model;

use Zend\Db\Sql\Sql,
    Zend\Db\Sql\Where;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Application\Model\AbstractModel;
use Application\Model\Email;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class User extends AbstractTableGateway
{
    public $table = T_CLIENTS;	
    public function __construct(Adapter $adapter)
    { 
		$this->adapter = $adapter;					
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->initialize();
    }
	
	
	/*public function getAdminUser($id=false)
    {
        $id = (int) $id;
		
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();
		$select->columns(array('*'));
		//pr($data);
		
		$where->equalTo('client_type','admin');   
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		
		$resultSet = $res->getResource()->fetch();	
		
		return $resultSet;
		
        $rowset = $this->adapter->select(array('client_type' => 'admin'));
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }
        return $row;
    }
	*/
	public function getAdminUser($id=false)
    {
        $id = (int) $id;
		
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();
		$select->columns(array('*'));
		//pr($data);
		
		$where->equalTo('yurt90w_client_type','admin');
	    
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		
		$resultSet = $res->getResource()->fetch();	
		
		return $resultSet;

        $rowset = $this->adapter->select(array('client_type' => 'admin'));
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }
        return $row;
    }
	public function chkLogin(Admin $admin)
    {
		$sessionContainer = new Container('myNameSpace');
		$data = array(
            'client_email'  => $admin->client_email,
			'client_password'  => $admin->client_password ,
        );
		
		$sql = new Sql($this->adapter); 
		$select = $sql->select(); 
		$select->from(T_CLIENTS); 
		$where = new Where();
		$select->columns(array(T_CLIENT_VAR.'client_id'));
			
		$where->equalTo(T_CLIENT_VAR.'client_email',$data['client_email']);
		$where->equalTo(T_CLIENT_VAR.'client_password',md5($data['client_password']));
	    
		$select->where($where); 
		$select->where("(".T_CLIENT_VAR."client_type = 'admin')");  

		$statement = $sql->prepareStatementForSqlObject($select); 
		$res =  $statement->execute(); 

		$resultSet = $res->getResource()->fetchAll();	
		return $resultSet;
    }

	public function checkAdminEmail($email,$id=false,$is_admin=false)
    {	
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();
		$select->columns(array(T_CLIENT_VAR.'client_id'));
		
		$select->where("(".T_CLIENT_VAR."client_type = 'admin')");
		$where->equalTo(T_CLIENT_VAR.'client_email', strtolower($email));
		$where->equalTo(T_CLIENT_VAR.'client_type', 'admin');
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		
		$resultSet = $res->getResource()->fetchAll();
		return $resultSet;
    }
	
	public function add($data,$id = false,$type=false)
	{
		$password = $data['client_pass'];
		$sql = new Sql($this->adapter);			
		$AbstractModel=new AbstractModel($this->adapter);
		
		try{
			if(isset($data['url'])){
				$getUrl = $data['url'];
				unset($data['url']);
			}
			
			if($id){ 				
				$update = $sql->update();
				$update->table(T_CLIENTS);
				$update->set($data);
				$update->where('client_id='.$id.'');
				$selectString = $sql->getSqlStringForSqlObject($update);
				
				$updated_records = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);	
				$userDetails=$this->getUsers('client_id='.$id.'');				
				
				return (object)array("success"=>true,"error"=>false,"message"=>"Record Successfully Updated","row_affected"=>$updated_records) ;
			}		
		
			$password = time();
			
			$data['client_email'] = $data['client_email'];
			$data['client_first_name'] = $data['client_first_name'];
			$data['client_phone'] = $data['client_phone'];
			$data['client_created'] = date('Y-m-d H:i:s');
			$data['client_status'] = '1';
			$data['client_email_verified'] = '1';
			$data['client_password'] = md5($password);
			
			$insert = $sql->insert(T_CLIENTS);
			$insert->values($data);
			
			$selectString = $sql->getSqlStringForSqlObject($insert);
			$results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
			$insertedId = $this->adapter->getDriver()->getLastGeneratedValue();
			
			$reset_password_key = md5($insertedId."!@#$%^$%&(*_+".time());			
			$pass_key['pass_resetkey'] = $reset_password_key;			
						
			$client_update = $AbstractModel->Super_Insert(T_CLIENTS,$pass_key,"client_id='".$insertedId."'");	
			$email_data=array();
			$email_data['client_email']= $data['client_email'];
			$email_data['client_name'] = $data['client_name'];
			$email_data['client_first_name'] = $data['client_first_name'];
			$email_data['client_last_name'] = $data['client_last_name'];
			$email_data['client_pass'] = $data['client_pass'];
			$email_data['client_email'] = $data['client_email'];
			$email_data['client_type'] = $data['client_type'];
			$email_data['password'] = $password;
			$email_data['key'] = $reset_password_key;
			
			if(isset($User_id) && $User_id!=''){
				$email_data['client_id'] = $User_id;
			}
			
			$Email=new Email($this->adapter);
			$isSend = $Email->sendEmail('registration_by_admin_email',$email_data);
			
			return (object)array("success"=>true,"error"=>false,"message"=>"Record Successfully Inserted","inserted_id"=>$insertedId) ;
 		}

		catch(Zend_Exception  $e) {/* Handle Exception Here  */
			return (object)array("success"=>false,"error"=>true,"message"=>$e->getMessage(),"exception"=>true,"exception_code"=>$e->getCode()) ;

 		}
	}
    
    public function getUsers($where = array(), $columns = array())
    {
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                'user' => $this->table
            ));
            
            if (count($where) > 0) {
                $select->where($where);
            }
            
            if (count($columns) > 0) {
                $select->columns($columns);
            }
         
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $users = $this->resultSetPrototype->initialize($statement->execute())
                ->toArray();
            return $users;

        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

	public function checkoldpassword($client_password,$id=false)
	{	
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();
		$select->columns(array('*'));
		
		$where->equalTo('client_password',$client_password);
		$where->equalTo('client_id', $id);
	
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		$resultSet = $res->getResource()->fetchAll();	
	
		return $resultSet;
 	}

	public function profilecheckoldpassword($client_password,$id=false)
	{	
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();
		$select->columns(array('*'));
		
		$where->equalTo('yurt90w_client_password',$client_password);
		$where->equalTo('yurt90w_client_id', $id);
	
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		$resultSet = $res->getResource()->fetchAll();	
	
		return $resultSet;
 	}
	
	public function checkEmail($email,$id=false)
	{
		$sql = new Sql($this->adapter);
		$select = $sql->select();
		$select->from(T_CLIENTS);
		$where = new Where();

		$select->columns(array('*'));

		$where->equalTo('client_email',$email);

		if($id){
			$where->notequalTo('client_id', $id);
		}
		
		$select->where($where);
		$statement = $sql->prepareStatementForSqlObject($select);
		$res =  $statement->execute();
		
		$resultSet = $res->getResource()->fetchAll();	
		return $resultSet;
 	}

	public function getAccountInfo($user_id,$user_info)
	{
		$AbstractModel=new AbstractModel($this->adapter);
		
		$languages = $AbstractModel->Super_Get(T_CLIENT_LANGUAGE,"clang_client_id='".$user_id."'","fetchAll",array(),array('0'=>array(T_LANGUAGES,'clang_language=lang_id','full',array('*'))));

		$work_history_data =$AbstractModel->Super_Get(T_CLIENT_JOBS,"cg_client_id='".$user_id."'",'fetchAll',array('group'=>'cg_id'));
		$sjoinArray = array(							
			'2'=>array('0'=>T_SECTORS,'1'=>'s_id = cs_sector_id','2'=>'Left','3'=>array("sectors_name" => new Expression("group_concat(s_name)"))),
		);

		$sectors_data = $AbstractModel->Super_Get(T_CLIENT_SECTORS,'cs_client_id ='.$user_id,'fetch',array(),$sjoinArray );

		$sjoinArray = array(							
			'2'=>array('0'=>T_OPT_SUBTYPES,'1'=>'usubtype_option = users_opt_sub_id','2'=>'Left','3'=>array("all_subtypes" => new Expression("group_concat(users_opt_sub_title_en,' ')"))),
		);

		$optTypeData = $AbstractModel->Super_Get(T_CLIENT_SUBTYPES,'usubtype_user_id ='.$user_id,'fetch',array(),$sjoinArray );
		$insurance_data = $AbstractModel->Super_Get(T_CLIENT_INSURANCE,"ci_clientid='".$user_id ."'","fetchAll",array(),array('0'=>array(T_INSURANCE,'ci_name=ins_id','full',array('*'))));

		return array($languages,$work_history_data,$sectors_data,$optTypeData,$insurance_data);

	}

}
