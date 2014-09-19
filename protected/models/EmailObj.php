<?php

class EmailObj extends FactoryObj
{
    public $error = "";
    
    public function __construct($emailid=null) 
    {
        parent::__construct("emailid","emails",$emailid);
    }
    
    public function send_response_to_post($email_to,$email_text,$property) 
    {
        # Append the header to email body
        ob_start();
?>This email is in response to post #<?php echo $property->propertyid;?>. The description of the property is as follows:

<?php echo $property->description; ?>


As of <?php echo StdLib::format_date($property->date_updated,"normal");?>

-------------------------------------------------------------------------------------------------
<?php
        $body = ob_get_contents();
        ob_end_clean();
        
        $email_text = $body . $email_text;
        $email_subject    = "CU Property Response Email: Post #".$property->propertyid;
        
        # Load user to get their email address
        $user = new UserObj(Yii::app()->user->name);
        $email_from = array(
            $user->name => $user->email
        );
        
        $mail = new Mail;
        if($mail->send_mail($email_from,$email_to,$email_subject,$email_text)) {
            $this->emailfrom = array_pop(array_values($email_from));
            $this->propertyid = $property->propertyid;
            $this->date_sent = date("Y-m-d H:i:s");
            $this->save();
        }
    }
}