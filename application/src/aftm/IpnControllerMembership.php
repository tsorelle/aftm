<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 12/29/2016
 * Time: 7:06 AM
 */
/**
 * Must declare route in \application\bootstrap\app.php
 *
 * Route::register(
 *      '/tops/service/execute/{arg}',
 *      'Concrete\Package\Tops\Controller\ServiceRequestHandler::executeService'
 *      );
 *
 */

namespace Application\Aftm;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Express\Entry\Search\Result\Result;
use Concrete\Core\Express\EntryList;



class IpnControllerMembership extends IpnControllerBase
{

    function getPostValues()
    {
        $result = new \stdClass();
        $result->item_name = $this->getPostValue('item_name');
        $result->business = $this->getPostValue('business');
        $result->item_number = $this->getPostValue('item_number');
        $result->payment_status = $this->getPostValue('payment_status');
        $result->mc_gross = $this->getPostValue('mc_gross');
        $result->payment_currency = $this->getPostValue('mc_currency');
        $result->txn_id = $this->getPostValue('txn_id');
        $result->receiver_email = $this->getPostValue('receiver_email');
        $result->receiver_id = $this->getPostValue('receiver_id');
        $result->quantity = $this->getPostValue('quantity');
        $result->num_cart_items = $this->getPostValue('num_cart_items');
        $result->payment_date = $this->getPostValue('payment_date');
        $result->first_name = $this->getPostValue('first_name');
        $result->last_name = $this->getPostValue('last_name');
        $result->payment_type = $this->getPostValue('payment_type');
        $result->payment_gross = $this->getPostValue('payment_gross');
        $result->payment_fee = $this->getPostValue('payment_fee');
        $result->settle_amount = $this->getPostValue('settle_amount');
        $result->memo = $this->getPostValue('memo');
        $result->payer_email = $this->getPostValue('payer_email');
        $result->payer_phone = $this->getPostValue('contact_phone');
        $result->txn_type = $this->getPostValue('txn_type');
        $result->payer_status = $this->getPostValue('payer_status');
        $result->address_street = $this->getPostValue('address_street');
        $result->address_city = $this->getPostValue('address_city');
        $result->address_state = $this->getPostValue('address_state');
        $result->address_zip = $this->getPostValue('address_zip');
        $result->address_country = $this->getPostValue('address_country');
        $result->address_status = $this->getPostValue('address_status');
        $result->item_number = $this->getPostValue('item_number');
        $result->tax = $this->getPostValue('tax');
        $result->option_name1 = $this->getPostValue('option_name1');
        $result->option_selection1 = $this->getPostValue('option_selection1');
        $result->option_name2 = $this->getPostValue('option_name2');
        $result->option_selection2 = $this->getPostValue('option_selection2');
        $result->for_auction = $this->getPostValue('for_auction');
        $result->invoice = $this->getPostValue('invoice');
        $result->custom = $this->getPostValue('custom');
        $result->notify_version = $this->getPostValue('notify_version');
        $result->verify_sign = $this->getPostValue('verify_sign');
        $result->payer_business_name = $this->getPostValue('payer_business_name');
        $result->payer_id =$this->getPostValue('payer_id');
        $result->mc_currency = $this->getPostValue('mc_currency');
        $result->mc_fee = $this->getPostValue('mc_fee');
        $result->exchange_rate = $this->getPostValue('exchange_rate');
        $result->settle_currency  = $this->getPostValue('settle_currency');
        $result->parent_txn_id  = $this->getPostValue('parent_txn_id');
        $result->pending_reason = $this->getPostValue('pending_reason');
        $result->reason_code = $this->getPostValue('reason_code');

        // subscription specific vars
        $result->subscr_id = $this->getPostValue('subscr_id');
        $result->subscr_date = $this->getPostValue('subscr_date');
        $result->subscr_effective  = $this->getPostValue('subscr_effective');
        $result->period1 = $this->getPostValue('period1');
        $result->period2 = $this->getPostValue('period2');
        $result->period3 = $this->getPostValue('period3');
        $result->amount1 = $this->getPostValue('amount1');
        $result->amount2 = $this->getPostValue('amount2');
        $result->amount3 = $this->getPostValue('amount3');
        $result->mc_amount1 = $this->getPostValue('mc_amount1');
        $result->mc_amount2 = $this->getPostValue('mc_amount2');
        $result->mc_amount3 = $this->getPostValue('mc_amount3');
        $result->recurring = $this->getPostValue('recurring');
        $result->reattempt = $this->getPostValue('reattempt');
        $result->retry_at = $this->getPostValue('retry_at');
        $result->recur_times = $this->getPostValue('recur_times');
        $result->username = $this->getPostValue('username');
        $result->password = $this->getPostValue('password');

        //auction specific vars
        $result->for_auction = $this->getPostValue('for_auction');
        $result->auction_closing_date  = $this->getPostValue('auction_closing_date');
        $result->auction_multi_item  = $this->getPostValue('auction_multi_item');
        $result->auction_buyer_id  = $this->getPostValue('auction_buyer_id');

        return $result;

    }

    function sendNotifications($inputs)
    {
        // TODO: Implement sendNotifications() method.
    }

    function updateData($inputs)
    {
        // TODO: Implement updateData() method.
    }

    function getFormId()
    {
        return "member";
    }

    /**
     * Get transaction details from data
     *
     * @param array $inputs
     * @return \stdClass
     */
    function getDetails($inputs)
    {
        // TODO: Implement getDetails() method.
    }
}