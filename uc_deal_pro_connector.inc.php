<?php
// $Id$

/**
 * @file
 * iDEAL payment module for Ubercart. No extra gateway needed. 
 * Include For iDEAL ING/PB Advanced Connector
 *
 * Development by Qrios | http://www.qrios.nl | c.kodde {at} qrios {dot} nl
 * 
 * 
 */

function uc_ideal_pro_call(&$arg1, $arg2) {

  $url_base = url(NULL, NULL, NULL, TRUE);
  $path_module = drupal_get_path('module', 'uc_ideal_pro_payment');
  ////Set errors on so we can see if there is a PHP error goes wrong
  //ini_set('display_errors',1);
  //error_reporting(E_ALL & ~E_NOTICE);

  //include connector
  require_once($path_module.'/lib/iDEALConnector.php');

  // Initialise connector
  $iDEALConnector = new iDEALConnector();

  //Process directory request
	$response = $iDEALConnector->GetIssuerList();

  //NOT USED?  
  //$errorCode = $response->getErrorCode(); 
  //$consumerMessage = $response->getConsumerMessage(); 

  if ($response->errCode){
    //echo 'error';
    $form_output.=t('Payment through iDEAL gateway not possible.').'<br>';
    $form_output.=t('Error message iDEAL').': ';
    $msg = $response->errMsg;
    $form_output.=("$msg<br>");
  }
  else{
    //Get issuerlist
    $issuerArray = $response->issuerShortList; 
    if(count($issuerArray) == 0){
      $form_output.=t('List with banks not available, payment through iDEAL gateway not possible.');
    }
    else{
      //Directory request succesful and at least 1 issuer
      $form_output.='<form action="'.$url_base.'cart/checkout/ideal_pro_transreq" method="post" name="OrderForm">';
      
      //Create a selection list
      $form_output.='<select name="issuerID"  class="ideal_pro_dirreq_message_field">';
      $form_output.='<option value="0">'.t('Choose your bank...').'</option>';
      //Create an option tag for every issuer
      foreach($issuerArray as $issuer){
        $form_output.=("<option value=\"{$issuer->issuerID}\"> {$issuer->issuerName} </option>");
      }
      $form_output.='</select><br /><input class="ideal_pro_dirreq_message_button" name="Submit" type="submit" value="'.t('Go to my bank').' ->"></form>';
    }
  }
  /*END ThinMPI code for DirReq*/
 
  $url_base = url(NULL, NULL, NULL, TRUE);

  $redirect_declineurl = $url_base.'cart/checkout';
  $redirect_exceptionurl = $url_base.'cart/checkout';
  $redirect_cancelurl = $url_base.'cart/checkout/ideal_pro_cancel';

  $redirect_message1 = t('Please choose the bank you have an account with...');
  $redirect_message2 = t('You will be returned to our shop after completing your IDEAL payment transaction.');

  $orderid = $arg1->order_id;
  $amount = $arg1->order_total * 100;   //amount *100

  $_SESSION['ideal_pro_order_id'] = $arg1->order_id;
  //Fill DirReq form session var
  $_SESSION['ideal_pro_dirreq_form']='
  <div class="ideal_pro_dirreq_message_top">
  '.$redirect_message1.'
  </div>
  <div class="ideal_pro_dirreq_container">
  <div align="right"><img src="https://www.qspeed.nl/httpsimg/lock.gif" alt="Secure Payment by Qrios" /></div>
  <div align="center" class="ideal_pro_dirreq_form">
  '.$form_output.'
  </div>
  <div class="ideal_pro_dirreq_message_bottom">
  '.$redirect_message2.'
  </div>
  </div>'
  ;
  //Fill TransReq session var
  $_SESSION['ideal_pro_transreq_data']= array(
    'orderid' => $arg1->order_id,
    'amount' => $arg1->order_total * 100,   //amount *100
  );

  drupal_goto('cart/checkout/ideal_pro_dirreq');
}


function uc_ideal_pro_transreq_call() {
  if ($_SESSION['ideal_pro_transreq_data']) {

    $path_module = drupal_get_path('module', 'uc_ideal_pro_payment');

    //include connector
    require_once($path_module.'/lib/iDEALConnector.php');
    //Initialise connector
    $iDEALConnector = new iDEALConnector();
    //print_r($iDEALConnector);
    $order_data = $_SESSION['ideal_pro_transreq_data'];
    $orderid = $order_data['orderid'];
    $amount = $order_data['amount'];
    $issuerID = check_plain($_POST['issuerID']);
    $description = $iDEALConnector->config['DESCRIPTION'];
    $entranceCode = $iDEALConnector->config['ENTRANCECODE'];
    $expirationPeriod = $iDEALConnector->config['EXPIRATIONPERIOD'];
    $merchantReturnURL = $iDEALConnector->config['MERCHANTRETURNURL'];

    unset($_SESSION['ideal_pro_transreq_data']);
    
    if(!$issuerID){
      drupal_set_message(t('You have not chosen a bank for IDEAL payment. For security reasons your input is cleared, please try again'));
      drupal_goto('cart/checkout');
    }
    
    //Send TransactionRequest
    $response = $iDEALConnector->RequestTransaction(
      $issuerID,
      $orderid,
      $amount,
      $description,
      $entranceCode,
      $expirationPeriod,
      $merchantReturnURL 
    );
    
    if (!$response->errCode){
  		$transactionID = $response->getTransactionID();
      //transactionID save in dbs
      db_query("INSERT INTO uc_payment_ideal_pro (order_id, description, order_status, transaction_id) VALUES('$orderid','$description','$status','$transactionID')");

  		//Get IssuerURL and decode it
  		$ISSURL = $response->getIssuerAuthenticationURL();
  		$ISSURL = html_entity_decode($ISSURL);

  		//Redirect the browser to the issuer URL
  		header("Location: $ISSURL"); 
  		exit();
      
  	}
    else{
  		//TransactionRequest failed, inform the consumer
  		$msg = $response->consumerMsg;
      watchdog('uc_ideal_pro_payment', $response->errCode.': '.$response->errMsg, WATCHDOG_ERROR);
  		drupal_set_message(t('Something went wrong in processing your IDEAL payment. IDEAL error:').'<br>'.$msg);
      drupal_goto('cart/checkout');
  	}
    
    return($ideal_pro_form );

  }else{
    drupal_goto('cart/');
  }
}


function uc_ideal_pro_statreq_call($arg1, $arg2) {
  $transaction_id= $_GET['trxid'];
  $order_id = $_GET['ec'];

  $path_module = drupal_get_path('module', 'uc_ideal_pro_payment');

  //include connector
  require_once($path_module.'/lib/iDEALConnector.php');
  //Initialise connector
  $iDEALConnector = new iDEALConnector();

	//Create StatusRequest
	$response = $iDEALConnector->RequestTransactionStatus($transaction_id);
  
  //$transID = str_pad($transaction_id, 16, "0"); //Delete??

  if ($response->errCode){
		//StatusRequest failed, let the consumer click to try again
    $msg = $response->consumerMsg;
    watchdog('uc_ideal_pro_payment', $response->errCode.': '.$response->errMsg, WATCHDOG_ERROR);
    drupal_set_message(t('We could not verify the payment status automaticaly, we will check your payment manualy, pleas contact us regarding this. IDEAL error:')).'<br>'.$msg;
    drupal_goto('cart/checkout');
	}
	elseif(!$response->status == 1){
		//Transaction failed, inform the consumer
		drupal_set_message(t('Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again or go back to select another payment method.'), 'ERROR');
    if ($order_id == $_SESSION['ideal_pro_order_id']) { //Check if orer_id is valid
      // This lets us know it's a legitimate access of the review page.
      $_SESSION['do_review'] = TRUE;
      // Ensure the cart we're looking at is the one that payment was attempted for.
      $_SESSION['cart_order'] = uc_cart_get_id();
      drupal_goto('cart/checkout/review');
    }else{
      drupal_goto('cart');
    }
	}else{
		drupal_set_message(t('Thank you for shopping with us, your payment is processed sucessfuly'));

    //Here you should retrieve the order from the database, mark it as "payed"
    $order = uc_order_load($order_id);
    if ($order == FALSE) { //Check if order exist
      watchdog('uc_ideal_pro_payment', t('iDeal payment completion attempted for non-existent order.'), WATCHDOG_ERROR);
      return;
    }
    uc_order_update_status($order->order_id, uc_order_state_default('payment_received'));
    
    //Todo??
    //uc_payment_enter($order_id, 'ideal_pro', $payment_amount, $order->uid, NULL, $comment);
    //uc_cart_complete_sale($order);
    //uc_order_comment_save($order_id, 0, t('iDeal Pro reported a payment of !amount !currency.', array('!amount' => uc_currency_format($payment_amount, FALSE), '!currency' => $payment_currency)), 'admin');
    
    unset($_SESSION['ideal_pro_order_id']);
    // This lets us know it's a legitimate access of the complete page.
    $_SESSION['do_complete'] = TRUE;

    drupal_goto('cart/checkout/complete');
    exit();
  }
}
