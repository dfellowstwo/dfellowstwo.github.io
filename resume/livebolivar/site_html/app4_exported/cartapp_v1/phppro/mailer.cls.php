<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*
* @version $Revision: 2244 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

class Mailer {

	var $to;				// array
	var $from;
	var $cc = '';
	var $bcc = '';
	var $subject = '';
	var $message = '';
	var $config = false;

	function Mailer ( $from = '' ) {

		global $myPage;
		if( $myPage->sdrive ) {

			// use sdrive config if available
			$this->config['service'] = 'smtp';
			$this->config['smtp']['auth'] = 'false';
			$this->config['smtp']['user'] = '';
			$this->config['smtp']['password'] = '';
			$this->config['smtp']['host'] = $myPage->sdrive['smtp_server'];
			$this->config['smtp']['port'] = $myPage->sdrive['smtp_port'];
			$this->config['smtp']['secure'] = '';
			print_r($this->config);

		} else {

			// check if the user config exists
			$handle = @fopen( 'user.config.php', 'r', 1 );
			if( $handle ) {
				fclose( $handle );
				include 'user.config.php';
				if( isset( $user_config['mailer'] ) ) {
					$this->config = & $user_config['mailer'];
				}
			}			
		}
		
		$this->from = $from;
	}


	// accepts array or comma seperated list as input
	function SetRecipients ( $recipients ) {

		$this->to = $recipients;

	}


	function SetSubject ( $subject ) {

		// strip new lines
		$this->subject = str_replace( "\n", " ", $subject );
	}


	function SetMessage ( $message ) {
		$this->message = wordwrap( $message, 70 );
	}


	// returns false on failure
	function Send ( ) {

		// no user config or using localhost -> use php mailer.
		if( ! $this->config || $this->config['service'] == 'localhost' ) {
			return $this->_SendPHP();
		}
		return $this->_SendSMTP();

	}


	/************************** private functions ****************************/


	function _AddMimeHeaders ( &$headers ) {

		$headers .= 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\n";

	    // spamassassin is more sensitive about html messages than text message, so
	    // message must comply better with RFC. Especially missing message-id hurts.
		$headers .= 'Message-ID: <' . time() . '.' . strlen( $this->message )
                             . '@' . str_replace( 'www.', '', $_SERVER['HTTP_HOST'])
                             . '>' .  "\n";
	}

	function _SendPHP ( ) {
		
		// transform array into a comma seperated list
		if( is_array( $this->to ) ) {
			$this->to = implode( ',' , $this->to );
		}

		$headers = 'To: ' . $this->to . "\n";
	    $headers .= 'Date: ' .  date("D, j M Y G:i:s O") . "\n"; // Sat, 7 Jun 2001 12:35:58 -0700

		if( !empty( $this->from ) ) {
			$headers .= 'From: ' . $this->from . "\n";
		}
		if( !empty( $this->cc ) ) {
			$headers .= 'Cc: ' . $this->cc . "\n";
		}
		if( !empty( $this->bcc ) ) {
			$headers .= 'Bcc: ' . $this->bcc . "\n";
		}

		// check what kind of message body we have
		if( strpos( $this->message, '<html' ) !== false ) {
			$this->_AddMimeHeaders( $headers );
		}

		if( empty( $this->from ) )
			$result = mail( $this->to, $this->subject, $this->message, $headers );
		else
			$result = mail( $this->to, $this->subject, $this->message, $headers, '-f ' . $this->from );

		if( ! $result ) writeErrorLog( 'Sending mail message failed.' );

		return $result;
	}


	function _SendSMTP ( ) {

		include 'class.phpmailer.php';
		$config = &$this->config['smtp'];

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = $config['host'];
		$mail->Port = $config['port'];
		$mail->SMTPSecure = $config['secure']; 
		$mail->SMTPAuth = $config['auth'];
		$mail->Username = $config['user'];
		$mail->Password = $config['password'];
		$mail->Subject = $this->subject;
		$mail->MsgHTML( $this->message );

		if( is_array( $this->to ) ) {
			foreach( $this->to as $adr ) {
				$mail->AddAddress( $adr );
			}
		} else {
			$mail->AddAddress( $this->to );
		}
		
		if( ! empty( $this->from ) ) {
			
			// accept formats name@domain.ext or   Name <name@domain.ext>
			$pos = strpos( $this->from, '<' );
			
			if( $pos === false ) {
				global $myPage;
				$name = $myPage->getConfigS('shopname');
				if( $name )			$mail->SetFrom( $this->from, $name );
				else 				$mail->SetFrom( $this->from );
			} else {
				$name = substr( $this->from, 0, $pos );
				$addr = str_replace( '>', '', substr( $this->from, $pos + 1 ) );
				$mail->SetFrom( $addr, $name );
			}
		}

		if( !empty( $this->cc ) ) {
			$mail->SetCC( $this->cc );
		}
		if( !empty( $this->bcc ) ) {
			$mail->SetBCC( $this->bcc );
		}

		if( ! $mail->Send() )  {
			if( ! $result ) writeErrorLog( $mail->ErrorInfo );
			return false;
		}
		return true; 
	}
}

?>
