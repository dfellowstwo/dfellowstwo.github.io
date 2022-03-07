<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* PayPal API error handling.
*
* Displays error parameters.
*
* Called by DoDirectPaymentReceipt.php, TransactionDetails.php,
* GetExpressCheckoutDetails.php and DoExpressCheckoutPayment.php.
*
* @version $Revision: 2244 $
* @author Cees de Gruijter
* @category SCC
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/
?>

<html>
<head>
<title><?php echo _T( 'PayPal PHP API Response'); ?></title>
<link href="sdk.css" rel="stylesheet" type="text/css"/>
</head>

<body alink=#0000FF vlink=#0000FF>

<center>

<table width="700" cellspacing="10">
	<tr>
		<td colspan="2" class="header"><?php echo _T('PayPal has returned an error!'); ?></td>
	</tr>

<?php  //it will print if any URL errors
	if(isset($_SESSION['curl_error_no'])) {
			$errorCode = $_SESSION['curl_error_no'] ;
			$errorMessage = $_SESSION['curl_error_msg'] ;
			unset($_SESSION['curl_error_no']);
?>
	<tr>
		<td><?php echo _T('Error Number:'); ?></td>
		<td><?php echo $errorCode ?></td>
	</tr>
	<tr>
		<td><?php echo _T('Error Message:'); ?></td>
		<td><?php echo $errorMessage ?></td>
	</tr>

<?php } else {

/* If there is no URL Errors, Construct the HTML page with
   Response Error parameters.
   */
?>
	<tr>
		<td><?php echo _T('Ack:'); ?></td>
		<td><?php echo $myPage->resArray['ACK']; ?></td>
	</tr>
	<tr>
		<td><?php echo _T('Correlation ID:'); ?></td>
		<td><?php echo $myPage->resArray['CORRELATIONID'] ?></td>
	</tr>
	<tr>
		<td><?php echo _T('Version:'); ?></td>
		<td><?php echo $myPage->resArray['VERSION']?></td>
	</tr>
<?php
	$count = 0;
	while (isset($myPage->resArray["L_SHORTMESSAGE" . $count])) {
		  $errorCode    = $myPage->resArray["L_ERRORCODE" . $count];
		  $shortMessage = $myPage->resArray["L_SHORTMESSAGE" . $count];
		  $longMessage  = $myPage->resArray["L_LONGMESSAGE" . $count];
		  $count = $count + 1;
?>
	<tr>
		<td><?php echo _T('Error Number:'); ?></td>
		<td><?php echo $errorCode ?></td>
	</tr>
	<tr>
		<td><?php echo _T('Short Message:'); ?></td>
		<td><?php echo $shortMessage ?></td>
	</tr>
	<tr>
		<td><?php echo _T('Long Message:'); ?></td>
		<td><?php echo $longMessage ?></td>
	</tr>

<?php }//end while
}// end else
?>
	</table>
</center>
<br>
<a class="home" id="CallsLink" href="javascript:history.back();"><font color=blue>&lt;&lt; <?php echo _T('back'); ?></font></a>
</body>
</html>
