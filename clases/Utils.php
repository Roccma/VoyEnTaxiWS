<?php
ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_NOTICE);

class Utils{

	public function enviarEmail($email,$nombre_completo){

	
	//Create a new PHPMailer instance
	$mail = new PHPMailer;

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;

	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';

	//Set the hostname of the mail server
	$mail->Host = 'smtp.gmail.com';

	//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	$mail->Port = 587;

	//Set the encryption system to use - ssl (deprecated) or tls
	$mail->SMTPSecure = 'tls';

	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;

	//Username to use for SMTP authentication - use full email address for gmail
	$mail->Username = "tipwebphp@gmail.com";

	//Password to use for SMTP authentication
	$mail->Password = "phpwebtip";

	//Set who the message is to be sent from
	$mail->setFrom('tipwebphp@gmail.com', 'Apps Web TIP');

	//Set an alternative reply-to address
	$mail->addReplyTo('tipwebphp@gmail.com', 'Apps Web TIP');

	//Set who the message is to be sent to
	$mail->addAddress($email, $nombre_completo);

	//Set the subject line
	$mail->Subject = 'Welcome';

	$mail->SMTPOptions = array(
		'ssl' => array(
		'verify_peer' => false,
		'verify_peer_name' => false,
		'allow_self_signed' => true));

	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->Body    = 'This is the HTML message body <b>in bold!</b>';

	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';

	
	//send the message, check for errors
	if (!$mail->send()) {
	    //echo "Mailer Error: " . $mail->ErrorInfo;
	    $res=false;
	} else {
	    //echo "Message sent!";
	    $res=true;
	}	


	return $res;

	}	
}
?>