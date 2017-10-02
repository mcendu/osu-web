<?php

/**
 *    Copyright 2015-2017 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Payments;

use App\Exceptions\InvalidSignatureException;
use App\Exceptions\ValidationException;
use App\Libraries\OrderCheckout;
use App\Libraries\Payments\CentiliPaymentProcessor;
use App\Models\Store\Order;
use Request;

class CentiliController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['callback']]);
        $this->middleware('check-user-restricted', ['except' => ['callback']]);
        $this->middleware('verify-user', ['except' => ['callback']]);

        parent::__construct();
    }

    public function callback(Request $request)
    {
        $processor = CentiliPaymentProcessor::createFromRequest($request->getFacadeRoot());
        if ($processor->isSkipped()) {
            // skip user_search notification
            return '';
        }

        $processor->run();

        return 'ok';
    }

    public function completed()
    {
        $orderNumber = Request::input('clientid') ?? '';
        $orderId = Order::getOrderId($orderNumber);
        OrderCheckout::complete($orderId);

        return redirect(route('store.invoice.show', ['invoice' => $orderId, 'thanks' => 1]));
    }

    public function failed()
    {
        // FIXME: show a message to the user
        Request::session()->flash('status', 'An error occured while processing the payment.');

        return redirect(route('store.checkout.index'));
    }

    protected function exceptionHandler($exception)
    {
        if ($exception instanceof ValidationException) {
            \Log::error($exception->getMessage());

            return response(['message' => 'A validation error occured while running the transaction'], 406);
        } elseif ($exception instanceof InvalidSignatureException) {
            return response(['message' => $e->getMessage()], 406);
        }

        // manually report
        $this->setShouldntReport(false);
        throw $exception;
    }
}
