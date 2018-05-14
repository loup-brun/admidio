<?php
/**
 ***********************************************************************************************
 * Payments page
 *
 * @copyright 2017 Louis-Olivier Brassard pour Corsaire-Chaparral
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *
 ***********************************************************************************************
 */
require_once('../../system/common.php');
require_once('../../../../environment.php'); //outiside of the root public folder

// Initialize and check the parameters
$getUserId = admFuncVariableIsValid($_GET, 'user_id', 'int', array('defaultValue' => (int) $gCurrentUser->getValue('usr_id')));

// create user object
$user = new User($gDb, $gProfileFields, $getUserId);

$headline = 'Paiements';

$page = new HtmlPage($headline);

// main menu of the page
$mainMenu = $page->getMenu();

// add Stripe elements js
//$page->addHtml('<script src="https://js.stripe.com/v3/"></script>');
$page->addHtml('
<div class="panel panel-default" id="user_data_panel">
    <div class="panel-heading">'.$gL10n->get('SYS_PAYMENT_DATA').'</div>
    <div class="panel-body row">
        <div class="col-sm-8">
');

        $amount = $user->getValue('MONTANT_DU');
        $mustPay = true;

        if (!strlen($amount) || preg_match('/^0$/i', $amount) ) {
            $mustPay = false;
            $displayAmount = '0,00';
        } else {

            $displayAmount = substr_replace($amount, ',', -2, 0);
        }


        $page->addHtml('Vous devez un montant de <strong>' . $displayAmount . ' $</strong>.');

        $page->addHtml('
        </div>
    </div>
    ');

    if ($mustPay) {
        /**
         * Louis: commented out premier_versement
         * do not give choice of doing 1/2 payment
         */

        /*if ($user->getValue('PREMIER_VERSEMENT') != '1')
        {
            // Set a round (i.e. no decimals) amount
            $half_amount = round($amount / 2);

            $page->addHtml('
            <div class="panel-body row">
                <div class="col-sm-9">
                    <p>Vous pouvez payer en deux versements. Cliquez sur le bouton ci-dessous pour payer la première moitié du montant qui vous a été facturé.</p>
                </div>
                <div class="col-xs-12">
                    <form action="'.ADMIDIO_URL.FOLDER_MODULES.'/paiements/payout.php?amount='.intval($half_amount).'&user_id='.intval($getUserId).'&versement=1" method="post" id="payment-form">
                    <script
                        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                        data-key="'.$_ENV['STRIPE_PUBLIC_TOKEN'].'"
                        data-amount="'.intval($half_amount).'"
                        data-name="Club d\'athlétisme Corsaire-Chaparral Inc."
                        data-description="Paiement de facture"
                        data-image="https://scontent-yyz1-1.xx.fbcdn.net/v/t1.0-9/22008097_1674911119217205_327082911360321962_n.png?oh=9bed210873f629fd470d2902db76e884&oe=5A856602"
                        data-locale="auto"
                        data-currency="cad"
                        data-label="Payer en 2 versements">
                      </script>
                    </form>
                </div>
            </div>
            ');
        } else {
            $page->addHtml('
            <div class="panel-body">
                <em>Vous avez déjà effectué votre premier versement. Veuillez payer la totalité restante de votre facture.</em>
            </div>
            ');
        }*/
        $page->addHtml('
        <div class="panel-body row">
            <div class="col-sm-9">
                <p>Vous pouvez payer la totalité de la somme due en cliquant sur le bouton ci-dessous.</p>
            </div>
            <div class="col-xs-12">
                <form action="'.ADMIDIO_URL.FOLDER_MODULES.'/paiements/payout.php?amount='.intval($amount).'&user_id='.intval($getUserId).'" method="post" id="payment-form">
                <script
                    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                    data-key="'.$_ENV['STRIPE_PUBLIC_TOKEN'].'"
                    data-amount="'.intval($amount).'"
                    data-name="Club d\'athlétisme Corsaire-Chaparral Inc."
                    data-description="Paiement de facture"
                    data-image="'.THEME_URL.'/images/coch-logo-stripe.png"
                    data-locale="auto"
                    data-currency="cad"
                    data-label="Payer la totalité">
                  </script>
                </form>
            </div>
        </div>
        ');
    } else {
        $page->addHtml('
        <div class="panel-body row">
            <div class="col-sm-8">
            ');

            $page->addHtml('<p>Vous pourrez effectuer un paiement lorsqu\'un montant vous sera assigné.</p>');

            $page->addHtml('
            </div>
        </div>
        ');
    }

$page->addHtml('
</div>
');


// show link to own profile
$mainMenu->addItem('adm_menu_item_home', ADMIDIO_URL . '/adm_program/index.php',
                   $gL10n->get('SYS_HOMEPAGE'), 'home.png');

if($gValidLogin)
{
    // show link to own profile
    $mainMenu->addItem('adm_menu_item_my_profile', ADMIDIO_URL . FOLDER_MODULES . '/profile/profile.php',
                       $gL10n->get('PRO_MY_PROFILE'), 'profile.png');
}

//$page->addJavascriptFile('https://js.stripe.com/v3/');
//$page->addJavascriptFile('adm_program/modules/paiements/paiements.js');

$page->show();
