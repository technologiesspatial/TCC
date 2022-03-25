<?php
namespace Application\Model;

use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
/*use Zend\Mime\Mime;*/

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Model\AbstractModel;

class Email extends AbstractModel
{
	private $AbstractModel;
	public $table = T_EMAIL;	

	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
		$this->initialize();
	}
	
	public function sendEmail($type = false, $data = false){
		
		$site_config = $this->getConfig();
		/*$logoLink=HTTP_IMG_PATH.'/logo/'.$site_config['site_logo'];*/
		$site_title=$site_config['site_name'];
		$year=date('Y'); 
		$SenderName = $site_config['site_name']; 
		$SenderEmail = "no-reply@thecollectivecoven.com";
		$email_where="emailtemp_key='".$type."'";

		$template=$this->Super_Get(T_EMAIL,$email_where,"fetch");

		$logoLink=HTTP_IMG_PATH.'/logo.png';

		$ext = getFileExtension($site_config['site_logo']);
		$logo_url = "https://www.thecollectivecoven.com/images/logo.png";

		global $subjectArr;

		$templateData=str_ireplace(array("{site_logo}","{site_link}","{site_name}","{date}","{site_email}","{site_images}","{year}"),array($logo_url,APPLICATION_URL,$site_title,date('Y'),$site_config['site_email'],HTTP_IMG_PATH,date('Y')),$template['emailtemp_content_en']);

		switch($type){
			case 'registration_email':
			
				$ReceiverEmail	= $data['client_email'];
				$ReceiverName	= $data['client_name'];
				$verification_link=SITE_HTTP_URL."/activate/".$data['client_activation_key'];
				
				$MESSAGE=str_ireplace(array("{verification_link}","{user_name}"),array($verification_link,$data['client_name']),$templateData);
				
			break;
				
			case 'account_details':
				$ReceiverEmail	= $data['user_email'];
				$ReceiverName	= $data['user_name'];
				$verification_link = SITE_HTTP_URL."/activate/".$data['client_activation_key'];
				$MESSAGE = str_ireplace(array("{verification_link}","{user_name}","{password}"),array($verification_link,$data['client_name'],$data["pass"]),$templateData);
			break;	
				
			case 'user_password_update':
					$ReceiverName = $data["user_name"]; $ReceiverEmail = $data["user_email"];
					$ip = $_SERVER['REMOTE_ADDR'];
			
					$finf_geo_details = getBrowser();
					$browser        =   $finf_geo_details['userAgent']."<br>".$finf_geo_details['device'].'<br>';
					$os = getOS();
					$MESSAGE=str_ireplace(array("{user_name}","{ip}","{browser}","{os}"),array($ReceiverName,$ip,$browser,$os),$templateData);
					
			break;
			
			case 'news_letter_user':
				$ReceiverName = $data["user_email"]; $ReceiverEmail = strtolower($data["user_email"]);
				$MESSAGE=str_ireplace(array("{user_email}","{user_name}"),array($ReceiverEmail,$ReceiverEmail),$templateData);
			break;
			
			case 'admin_password_update' : /* Reset password email {	*/
			$admin_info = $this->Super_Get(T_CLIENTS,T_CLIENT_VAR.'client_type="admin"','fetch');
			$ReceiverName = $admin_info[T_CLIENT_VAR."client_name"]; 
			$ReceiverEmail = $admin_info[T_CLIENT_VAR."client_email"]; 
			$ip = $_SERVER['REMOTE_ADDR'];
			
			$finf_geo_details = getBrowser();
			$browser        =   $finf_geo_details['userAgent']."<br>".$finf_geo_details['device'].'<br>';
			$os = getOS();
			//$ReceiverEmail=$site_config['site_mail_address'];
			$MESSAGE=str_ireplace(array("{ip}","{browser}","{os}"),array($ip,$browser,$os),$templateData);
					
					
				break ;
				case 'reset_password' :
				
					$ReceiverName = $data["user_name"]; $ReceiverEmail = strtolower($data["user_email"]);
					$resetLink	= SITE_HTTP_URL."/reset-password/".$data['pass_resetkey'];
					if(isset($data['user_type']) && $data['user_type']=='admin'){
						$resetLink	= SITE_HTTP_URL."/".BACKEND."/reset-password/".$data['pass_resetkey'];
					}
					$MESSAGE=str_ireplace(array("{user_name}","{user_email}","{reset_link}"),array($ReceiverName,$ReceiverEmail,$resetLink),$templateData);
				break ;
				
				case 'contact_us_admin': 
				$ReceiverName = $site_config['site_name']; 
				$ReceiverEmail=$site_config['site_email'];
				$MESSAGE=str_ireplace(array("{guest_name}","{guest_email}","{guest_subject}","{guest_message}"),array($data['user_name'],$data['user_email'],$data['user_subject'],$data['user_message']),$templateData);
				break ;

			case 'notification_email':
				$ReceiverName = $data["user_name"]; 
				$ReceiverEmail = $data["user_email"];
				
				$MESSAGE=str_ireplace(
					array("{user_name}","{message}"),
					array($ReceiverName,$data['message']),
					$templateData
				);
				break;

			}

		$filename = $attachment='';
		if(!empty($data["subject"])) {
			$subject = $data["subject"];
		} else {
			$subject = $template['emailtemp_subject_en'];
		}
		$this->sendMail($MESSAGE,$template['emailtemp_subject_en'],$SenderEmail,$ReceiverEmail,$SenderName,$ReceiverName,$attachment,$filename,$site_config);		

		return (object)array("error"=>false , "success"=>true , "message"=>" Mail Successfully Sent");

	}

	function sendMail($htmlBody,$subject,$from, $to,$FromName,$ToName,$attachment=false,$filename=false,$site_config)
	{
		
		$htmlBody = '<!DOCTYPE html><html ><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'.$FromName.'</title></head><body>'.$htmlBody.'</body></html>';

		$htmlBody .= "<div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div><div style='display:none;'>".$site_config['meta_description_'.$_COOKIE['currentLang']]."</div>";

		$htmlPart = new MimePart($htmlBody);
		$htmlPart->type = "text/html";
		$htmlPart->charset = "UTF-8";
		
		$textPart = new MimePart(strip_tags($htmlBody,"<a>"));
		$textPart->type = "text/plain";
		$textPart->charset = "UTF-8";
	
	    $attachmentfile='';
						
		$body = new MimeMessage();
		
		if($attachment!=''){
			$content  = new MimeMessage();
			$content->setParts(array($textPart, $htmlPart));
       		$contentPart = new MimePart($content->generateMessage());
			
			$attachmentfile = new MimePart(file_get_contents($attachment));
			$attachmentfile->type = 'application/pdf';
			$attachmentfile->filename = $filename;
			$attachmentfile->encoding    = Mime::ENCODING_BASE64;
			$attachmentfile->disposition = Mime::DISPOSITION_ATTACHMENT;
			$body->setParts(array($htmlPart,$attachmentfile));

		} else {
			$body->setParts(array($textPart, $htmlPart));
		}

		$message = new MailMessage();
		$from = "administrator@thecollectivecoven.com";	//$site_config['site_email'];
		$message->setFrom($from,$FromName);
		$message->addTo($to,$FromName);
		$message->setSubject($subject);
		$message->setEncoding("UTF-8");
		$message->setBody($body);	
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');	
		$transport = new SmtpTransport();
		$options   = new SmtpOptions(
			array(
				'name'              => SMTP_NAME,
				'host'              => SMTP_HOST,
				'connection_class'  => SMTP_CONNECTION_CLASS,
				'port'              => SMTP_PORT,
				'connection_config' => array(
					'username' =>  SMTP_USEREMAIL,
					'password' => SMTP_PASSWORD,
					'ssl' => SMTP_ENCRYPTION
				),
			)
		);
		
		if(!TEST){ 
			$transport->setOptions($options);
			try{	
				$reply=$transport->send($message);	
			}catch (\Exception $e) {
				prd($e->getMessage());
				throw new \Exception($e->getPrevious()->getMessage());
			}
		}
			
	}
	
	public function getTemplate($type)
    {
		try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                T_EMAIL => $this->table
            ));
            
            if (count($type) > 0) {
				$select->where(array('emailtemp_key' =>$type));
            }

            $select->columns(array('emailtemp_title','emailtemp_subject','emailtemp_content'));            
            $statement = $sql->prepareStatementForSqlObject($select);
            $email_data = $this->resultSetPrototype->initialize($statement->execute())->toArray();
		
            return $email_data[0];
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

	public function getConfig()
	{
		$config_qry = " SELECT * FROM  ".T_CONFIG;      
		$results = $this->adapter->query($config_qry)->execute();
		$configuration=$results->getResource()->fetchAll();		
		
		foreach($configuration as $key=>$config){
			$config_data[$config['config_key']]= $config['config_value'] ;
			$config_groups[$config['config_group']][$config['config_key']]=$config['config_value'];	
		}
		
		return $config_data;
	}
}