<?php

/**
 * Process a Stripe token, charge a card
 *
 * @see https://stripe.com/docs/charges
 */

require_once('../../system/common.php');
require_once('../../../../environment.php'); //outside of the root public folder
require_once('../../libs/stripe-php/init.php');

// Set your secret key: remember to change this to your live secret key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_TOKEN']);

// Initialize and check the parameters
$getUserId = $_GET['user_id'];

// 1er versement?
$isFirstPayment = $_GET['versement'] == 1;

// create user object
$user = new User($gDb, $gProfileFields, $getUserId);

$headline = 'Paiements';

$page = new HtmlPage($headline);

// Token is created using Stripe.js or Checkout!
// Get the payment token ID submitted by the form:
$token = $_POST['stripeToken'];
$amount = $_GET['amount'];

$content = '';
$mainMenu = $page->getMenu();

try
{
    // Charge the user's card
    $charge = \Stripe\Charge::create(array(
        'amount' => $amount,
        'currency' => 'cad',
        'description' => 'Paiement d\'inscription : '  . $user->getValue('FIRST_NAME') . ' ' . $user->getValue('LAST_NAME') . ' (' . $user->getValue('usr_login_name') . ')',
        'source' => $token,
        'metadata' => array(
            'coch_user_id' => $getUserId,
            'coch_user_login' => $user->getValue('usr_login_name')
        )
    ));


    $user->saveChangesWithoutRights();

    $currentBalance = $user->getValue('MONTANT_DU');

    $currentBalance = (float)$currentBalance;
    $amount = (float)$amount;

    $difference = $currentBalance - $amount;

//    if ($isFirstPayment)
//    {
//        $user->setFirstPayment();
//    }

    $transactionHistory = $user->getValue('HISTORIQUE_TRANSACTIONS');

//    if (!strlen($transactionHistory)) {
//        // if transaction history is empty, prepend comma separator and
//        $transactionHistory = ',
//';
//    }

    $newTransactionHistory = $transactionHistory . '
{ id: \'' . $charge['id'] . '\', amount: ' . $amount . ' }';

    // Append to transaction history

    $user->setValue('HISTORIQUE_TRANSACTIONS', $newTransactionHistory);

    $user->setValue('MONTANT_DU', $difference);

    if (!strlen($difference) || preg_match('/^0$/i', $difference) ) {
        // Balance is 0
        $displayAmount = '0,00';

        $user->setValue('PAIEMENT_COMPLET', 1);
    } else {
        $displayAmount = substr_replace($difference, ',', -2, 0);
    }

    $user->save();

    $content .= 'Merci! Votre paiement a été effectué avec succès. Votre balance est maintenant de <strong>' . $displayAmount . '&nbsp;$</strong>.';
    $htmlButtons = '
    <a class="btn" href="/adm_program/index.php">'.$gL10n->get('SYS_NEXT').'
        <img src="'. THEME_URL. '/icons/forward.png" alt="'.$gL10n->get('SYS_NEXT').'"
            title="'.$gL10n->get('SYS_NEXT').'" />
    </a>';

}

catch (Exception $e)
{
    $content .= 'Une erreur est surveue lors de votre paiement. Voici ce que nous savons :';

    $content .= '<br/>'.$e;

    $mainMenu->addItem('menu_back', ADMIDIO_URL . FOLDER_MODULES . '/paiements/paiements.php',
                       $gL10n->get('SYS_BACK'), 'back.png');
    $mainMenu->addItem('menu_cancel', '/adm_program/index.php',
                       $gL10n->get('SYS_ABORT'), 'close.png');

    $htmlButtons = '
    <a class="btn" href="javascript:void(0)" onclick="history.back()">
        <img src="'.THEME_URL.'/icons/back.png" alt="'.$gL10n->get('SYS_BACK').'"
            title="'.$gL10n->get('SYS_BACK').'" />'.
        $gL10n->get('SYS_BACK').
    '</a>';
    $htmlButtons .= '
    <a class="btn" href="/adm_program/index.php">
        <img src="'. THEME_URL. '/icons/close.png" alt="'.$gL10n->get('SYS_ABORT').'"
            title="'.$gL10n->get('SYS_ABORT').'" />'.
            $gL10n->get('SYS_ABORT').'
    </a>';
}


// show link to own profile
$mainMenu->addItem('adm_menu_item_my_profile', ADMIDIO_URL . FOLDER_MODULES . '/profile/profile.php',
                   $gL10n->get('PRO_MY_PROFILE'), 'profile.png');

$page->addHtml('
<div class="message">
    <p class="lead">'. $content.'</p>
    '.$htmlButtons.'
</div>
');

$page->show();
