<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\Session\Container;

class FrontGetMessages extends AbstractHelper
{
    public function __invoke()
    {
		
		
	   $Msgsession = new Container(DEFAULT_AUTH_NAMESPACE);
	
	   if(isset($Msgsession['defaultMsg'])){ ?>
       <script type="text/javascript">
		$(document).ready(function(){
		$.notify({
			icon: 'fa fa-info-circle',
			message: "<strong>General Alert !</strong> <?php echo $Msgsession['defaultMsg']?>"
		
		},{
			type: 'warning',
			timer: 4000
		});
		});
		</script>
       <?	   
			
			unset($Msgsession['defaultMsg']);
	   }
	   if(isset($Msgsession['infoMsg'])){ ?>
	   <script type="text/javascript">
		$(document).ready(function(){
		$.notify({
			icon: 'fa fa-info-circle',
			message: "<strong>Info Alert !</strong> <?php echo $Msgsession['infoMsg']?>"
		
		},{
			type: 'info',
			timer: 4000
		});
		});
		</script>

	   <?	   
			
			unset($Msgsession['infoMsg']);
	   }
	   if(isset($Msgsession['successMsg'])){ ?>
	    <script type="text/javascript">
		$(document).ready(function(){
		$.notify({
			icon: 'fa fa-check-circle',
			message: "<strong>Success Alert !</strong> <?php echo $Msgsession['successMsg']?>"
		
		},{
			type: 'success',
			timer: 4000
		});
		});
		</script>
	   <? 	   
			
			unset($Msgsession['successMsg']);
	   }
	   if(isset($Msgsession['errorMsg'])){ ?>
    	<script type="text/javascript">
		$(document).ready(function(){
		$.notify({
			icon: 'fa fa-info-circle',
			message: "<strong>Error Alert !</strong> <?php echo $Msgsession['errorMsg']?>"
		
		},{
			type: 'danger',
			timer: 30000000000
		});
		});
		</script>
	   <?   
			
			unset($Msgsession['errorMsg']);
	   } 
    }
}
