<?php

require_once "init.php";
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/clientfunctions.php';
require_once __DIR__ . '/../../../includes/adminfunctions.php';

require_once "vendor/autoload.php";
$loggedUserID = null;
$userDetails = getclientsdetails();
if (isset($userDetails['model']) && count($userDetails['model']->getUserIds()) > 0) {
    $loggedUserID =$userDetails['model']->getUserIds()[0];

} 
define('IS_ADMIN_AREA', $_REQUEST['adminarea'] === '1');
$authenticatedAdmin = \WHMCS\User\Admin::getAuthenticatedUser();

$gatewayModuleName = 'payplus';
$gatewayParams = getGatewayVariables($gatewayModuleName);
// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$requestedUserID = null;
if ($_REQUEST['more_info']) {
    $requestedUserID = openssl_decrypt(base64_decode($_REQUEST['more_info']), ENCRYPTION_ALGORITHM, PASSPHRASE);
}

$fourDigits = $_REQUEST['four_digits'];
$expiryDate = $_REQUEST['expiry_month'] . $_REQUEST['expiry_year'];
$invoiceID = $_REQUEST['invoiceid'];
if (!$_REQUEST['token_uid'] || !$_REQUEST['terminal_uid']) {
    die;
}

$tokenData = $_REQUEST['token_uid'] . TOKEN_TERMINAL_SEPARATOR . $_REQUEST['terminal_uid'];
if (
    $requestedUserID !== null
    && (
        $requestedUserID == $loggedUserID
        || $authenticatedAdmin !== null
    )
) {
    createCardPayMethod(
        $requestedUserID,
        $gatewayModuleName,
        $fourDigits,
        $expiryDate,
        null,
        null,
        null,
        $tokenData
    );
} else {
    // Something's wrong here. Redirect them somewhere
}

if (IS_ADMIN_AREA === true) {
    redirSystemURL(['userid' => $requestedUserID], "/admin/clientssummary.php");
}
if ($invoiceID) {
    redirSystemURL('', "/invoice/" . $invoiceID . "/pay");
}
redirSystemURL('', "/account/paymentmethods");
