<?php

namespace AuthAcl\Model;



use Zend\Db\Sql\Sql,

    Zend\Db\Sql\Where;

use Zend\Db\TableGateway\AbstractTableGateway;

use Zend\Db\Adapter\Adapter;

use Zend\Db\ResultSet\ResultSet;



use Zend\Authentication\AuthenticationService;

use Zend\Authentication\Storage\StorageInterface;

use Zend\Authentication\Storage\Session as SessionStorage;



use Zend\Session\Container;



use Application\Model\AbstractModel;

use Application\Model\Email;



class User extends AbstractTableGateway

{



    public $table = T_CLIENTS;

    

    public function __construct(Adapter $adapter)

    { 

		$this->adapter = $adapter;					

        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);

        $this->initialize();		

    }

	

	public function add($data,$id = false){

		$sql = new Sql($this->adapter);			

		$AbstractModel=new AbstractModel($this->adapter);

		

		try{

			//Profile Update

			if($id){

				$update = $sql->update();

				$update->table(T_CLIENTS);

				$update->set($data);

				$update->where(T_CLIENT_VAR.'client_id='.$id.'');

				$selectString = $sql->getSqlStringForSqlObject($update);

				

				$updated_records = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);	

				$userDetails=$this->getUsers(T_CLIENT_VAR.'client_id='.$id.'');

							

				if(isset($userDetails) && !empty($userDetails) && $userDetails[0]!=''){

					$Front_User_Session = new Container(DEFAULT_AUTH_NAMESPACE);							

					$Front_User_Session->offsetSet('loggedUser',$userDetails[0]);	

					

				}else if($user_info[0]=='' || empty($user_info)){

					return $this->redirect()->toRoute(APPLICATION_URL.'front-logout');

				}

						

				return (object)array("success"=>true,"error"=>false,"message"=>"Record Successfully Updated","row_affected"=>$updated_records) ;

			}		

			

			$data[T_CLIENT_VAR.'client_created'] =date('Y-m-d H:i:s');

			

			if($id==false){   

				$insert = $sql->insert(T_CLIENTS);  

				$insert->values($data);  

					

				$selectString = $sql->getSqlStringForSqlObject($insert);

				$results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

				

				$insertedId = $this->adapter->getDriver()->getLastGeneratedValue();

				

				$reset_password_key = md5($insertedId."!@#$%^$%&(*_+".time());			

				$pass_key[T_CLIENT_VAR.'client_activation_key'] = $reset_password_key;	

				//$pass_key[T_CLIENT_VAR.'client_pass_resetkey_valid'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d').'+1 day'));			

				$user_update = $AbstractModel->Super_Insert(T_CLIENTS,$pass_key,T_CLIENT_VAR."client_id='".$insertedId."'");	

				

				$email_data=array();

				$email_data['client_name'] = $data[T_CLIENT_VAR.'client_name'];

				$email_data['client_email'] =  $data[T_CLIENT_VAR.'client_email'];

				$email_data['client_activation_key'] = $reset_password_key;

				

				$Email = new Email($this->adapter);

				$isSend = $Email->sendEmail('registration_email',$email_data);

			}

			

			return (object)array("success"=>true,"error"=>false,"message"=>"Record Successfully Inserted","inserted_id"=>$insertedId) ;

 		}

		catch(Zend_Exception  $e) {/* Handle Exception Here  */

			return (object)array("success"=>false,"error"=>true,"message"=>$e->getMessage(),"exception"=>true,"exception_code"=>$e->getCode()) ;

 		}

		catch(\Exception  $e) {

			

return (object)array("success"=>false,"error"=>true,"message"=>$e->getMessage(),"exception"=>true,"exception_code"=>$e->getCode()) ;

		}

	}


	/* resend verification email to user */
	public function sendVerificationEmail($email = '')
	{
		$userDetails = $this->getUsers(array(T_CLIENT_VAR.'client_email' => $email));

		if(isset($userDetails[0][T_CLIENT_VAR.'client_email']) and !empty($userDetails[0][T_CLIENT_VAR.'client_email']))
		{
			$email_data = array();
			$email_data['client_name'] = $userDetails[0][T_CLIENT_VAR.'client_name'];
			$email_data['client_email'] =  $userDetails[0][T_CLIENT_VAR.'client_email'];
			$email_data['client_activation_key'] = $userDetails[0][T_CLIENT_VAR.'client_activation_key'];
			
			$Email = new Email($this->adapter);
			$isSend = $Email->sendEmail('registration_email',$email_data);
		}
	}



	

    public function getUsers($where = array(), $columns = array())

    {

		try {

            $sql = new Sql($this->getAdapter());

            $select = $sql->select()->from(array(

                'user' => $this->table

            ));

            

            if (is_array($where) and count($where) > 0) {

                $select->where($where);

            }

            

            if (is_array($columns) and count($columns) > 0) {

                $select->columns($columns);

            }

            

            $statement = $sql->prepareStatementForSqlObject($select);

			

            $T_CLIENTS = $this->resultSetPrototype->initialize($statement->execute())

                ->toArray();

			

            return $T_CLIENTS;

        } catch (\Exception $e) {

            throw new \Exception($e->getPrevious()->getMessage());

        }

    }

	

	public function chkLogin($data)

    {

		$AbstractModel=new AbstractModel($this->adapter);	

		

		$resultSet =$AbstractModel->Super_Get(T_CLIENTS,"user_type!='user_admin' and user_type!='user_subadmin' and ((user_email='".mysql_escape_string(trim($data['user_email']))."') and user_status='1' and user_assword='".md5($data['user_password'])."')","fetch");	

		return $resultSet;

    }

	

	public function chkLoginId($data)

    {

		$AbstractModel=new AbstractModel($this->adapter);				

		$resultSet =$AbstractModel->Super_Get(T_CLIENTS,"user_type!='user_admin' and (user_email='".$data['user_email']."') ","fetch");		

		return $resultSet;

    }

	public function checkUname($uname,$id=false){
		$AbstractModel=new AbstractModel($this->adapter);		

		if($id===false){

		$query =$AbstractModel->Super_Get(T_CLIENTS,"LOWER(".T_CLIENT_VAR."client_username)=:clientemail","fetch",array('fields'=>array(T_CLIENT_VAR.'client_type',T_CLIENT_VAR.'client_id'),'warray'=>array("clientemail"=>strtolower($uname))));

		}else{

		$query =$AbstractModel->Super_Get(T_CLIENTS,"LOWER(".T_CLIENT_VAR."client_username)=:clientemail and ".T_CLIENT_VAR."client_id!=:clientid","fetch",array('fields'=>array(T_CLIENT_VAR.'client_type',T_CLIENT_VAR.'client_id'),'warray'=>array("clientemail"=>strtolower($uname),"clientid"=>$id)));

		}

		return  $query;
	}

	public function checkEmail($email,$id=false){

		

		$AbstractModel=new AbstractModel($this->adapter);		

		if($id===false){

		$query =$AbstractModel->Super_Get(T_CLIENTS,"LOWER(".T_CLIENT_VAR."client_email)=:clientemail","fetch",array('fields'=>array(T_CLIENT_VAR.'client_type',T_CLIENT_VAR.'client_id'),'warray'=>array("clientemail"=>strtolower($email))));


		}else{

		$query =$AbstractModel->Super_Get(T_CLIENTS,"LOWER(".T_CLIENT_VAR."client_email)=:clientemail and ".T_CLIENT_VAR."client_id!=:clientid","fetch",array('fields'=>array(T_CLIENT_VAR.'client_type',T_CLIENT_VAR.'client_id'),'warray'=>array("clientemail"=>strtolower($email),"clientid"=>$id)));

		}

		return  $query;

	 } 

	 

	 

	

	

	

	public function checkForgotEmail($email){	

		$AbstractModel=new AbstractModel($this->adapter);		

		$query =$AbstractModel->Super_Get(T_CLIENTS,"LOWER(".T_CLIENT_VAR."client_email)=:clientemail","fetch",array('fields'=>array(T_CLIENT_VAR.'client_type',T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_email_verified'),"warray"=>array("clientemail"=>strtolower($email))));

		

		return  $query;	

 	} 

	

	

	

	

	public function forgotPassword($data_to_update){

		$AbstractModel=new AbstractModel($this->adapter);

		

		$Email=new Email($this->adapter);

		$data_to_update[T_USERS_CONST.'_email'] = strtolower($data_to_update[T_USERS_CONST.'_email']);

		$user_data =$AbstractModel->Super_Get(T_USERS,T_USERS_CONST."_email='".$data_to_update[T_USERS_CONST.'_email']."' and ".T_USERS_CONST."_type!='admin'","fetch",$extra=array('fields'=>array(T_USERS_CONST.'_email',T_USERS_CONST.'_status',T_USERS_CONST.'_id',T_USERS_CONST.'_name',T_USERS_CONST.'_created',T_USERS_CONST.'_type',T_USERS_CONST.'_email_verified',T_USERS_CONST.'_signup_type')));

		

		if($user_data){

			//$updateData[T_CLIENT_VAR.'client_reset_status']	= '1';

			if($user_data[T_USERS_CONST.'_type']=='admin'){

				return array("error"=>"1","message"=>"Email is not valid.");

			}

			else if($user_data[T_USERS_CONST.'_email_verified']==0){

				$this->sendVerificationEmail($data_to_update[T_USERS_CONST.'_email']);

				return array("success"=>"1","message"=>"System has resent verification email of your account. Please check your email to verify it.");

			}

			elseif($user_data[T_USERS_CONST.'_signup_type']!='normal'){

				return array("error"=>"1","message"=>"This feature is not available for social users");

			}

			else{

			$updateData[T_USERS_CONST.'_reset_key'] = md5($user_data[T_USERS_CONST.'_id']."!@#$%^".$user_data[T_USERS_CONST.'_created'].time());

			$update = $AbstractModel->Super_Insert(T_USERS,$updateData,T_USERS_CONST."_id='".$user_data[T_USERS_CONST.'_id']."'");

			

			$email_data = array();

			$email_data['pass_resetkey']	= $updateData[T_USERS_CONST.'_reset_key'];

			$email_data['user_name']	= $user_data[T_USERS_CONST.'_name'];

			$email_data['user_email']= $user_data[T_USERS_CONST.'_email'];

			

			$send_mail = $Email->sendEmail('reset_password',$email_data);

			

			return 1;

			}

		}

		else{

			return array("error"=>"1","message"=>"Please make sure your email is registered with us, Check your email to reset the password.");

		}

	}

	

	public function resetPassword($data_to_update,$user_data){			

		$AbstractModel=new AbstractModel($this->adapter);

		

		$data_array = array(

			T_CLIENT_VAR.'client_password' =>	md5($data_to_update[T_CLIENT_VAR.'client_password']),

			T_CLIENT_VAR.'client_reset_key'	=>	'',

		);

		

		$user_update =$AbstractModel->Super_Insert(T_CLIENTS,$data_array,T_CLIENT_VAR."client_id='".$user_data[T_CLIENT_VAR.'client_id']."'");

		return $user_update;	

	}

	

	public function resendLink($user_id)

	{

		$AbstractModel=new AbstractModel($this->adapter);

		

		$checkUserData = $AbstractModel->Super_Get(T_CLIENTS,T_CLIENT_VAR.'client_id="'.$user_id.'"',"fetch",array('fields'=>array(T_CLIENT_VAR.'client_id',T_CLIENT_VAR.'client_email',T_CLIENT_VAR.'client_name')));

		$reset_password_key = md5($checkUserData[T_CLIENT_VAR.'client_id']."!@#$%^$%&(*_+".time());		

		$pass_key[T_CLIENT_VAR.'client_pass_resetkey'] = $reset_password_key;	

		$pass_key[T_CLIENT_VAR.'client_pass_resetkey_valid'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d').'+1 day'));

		$user_update = $AbstractModel->Super_Insert(T_CLIENTS,$pass_key,T_CLIENT_VAR."client_id='".$checkUserData[T_CLIENT_VAR.'client_id']."'");

		

		$email_data=array();

		$email_data[T_CLIENT_VAR.'client_name'] = $checkUserData[T_CLIENT_VAR.'client_name'];

		$email_data[T_CLIENT_VAR.'client_email'] =  $checkUserData[T_CLIENT_VAR.'client_email'];

		$email_data[T_CLIENT_VAR.'client_key'] = $reset_password_key;

		

		return array($email_data,$checkUserData);

	}

}