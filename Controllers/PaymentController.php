<?php

namespace App\Http\Controllers\StallParticipant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\EkpayLibrary;
use App\Models\StallPartcipantPanel\ { StallPayment, SpProfile };
use App\Models\EventManagementSystem\SpStallAssign;
use App\Http\Validations\StallParticipant\StallPaymentOnlineValidation;

class StallPaymentController extends Controller
{
    /**
     * Stall Participant Panel Application form
     */
    private $profile_id;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $profile = SpProfile::whereUserId(user_id())->first();

        if(!empty($profile)) {
            $this->profile_id = $profile->id;
        }
    }

    /**
     * Online Pending Payment
     */
    public function onlinePaymentPending(Request $request)
    {   
        $validationResult = StallPaymentOnlineValidation:: validate($request);
        if (!$validationResult['success']) {
            return response($validationResult);
        }

        DB::beginTransaction();

        $transaction_no = strtoupper(uniqid());

        try {
			$model = StallPayment::find($request->id);
			$model->transaction_no          	= $transaction_no;
			$model->status          			= 1;
			$model->pay_status              	= 'pending';
			$model->update();

			save_log([
				'data_id' => $model->id,
				'table_name' => 'stall_payments',
			]);

            DB::commit();

			$ekpay_amount = $request->amount;
	
			// $user_id = (int) user_id();

			$profile = SpProfile::whereUserId(user_id())->first();

            $pay_info = [];
			$pay_info['s_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/success';
            $pay_info['f_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/decline';
            $pay_info['c_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/cancel';
            $pay_info['cust_id']        = (int)user_id();
            $pay_info['cust_name']      = $profile->contact_person_name;
            $pay_info['cust_mobo_no']   = $profile->contact_no;
            $pay_info['cust_email']     = $profile->email;
            $pay_info['cust_mail_addr'] = $profile->address_en;
            $pay_info['trnx_id']        = $transaction_no;
            $pay_info['trnx_amt']       = $ekpay_amount;
            $pay_info['trnx_currency']  = 'BDT';
            $pay_info['ord_det']        = date('Y-m-d');

            $ekpay_payment = new EkpayLibrary();
            return $ekpay_payment->ekpay_payment($pay_info);

        } catch (\Exception $ex) {

            DB::rollback();

            return response([
                'success' => false,
                'message' => 'Failed to save data.',
                'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
            ]);
        }

        return response([
            'success' => true,
            'message' => 'Data save successfully',
            'data'    => []
        ]);
    }

    /**
     * stall payment sucess method
    */
	public function success(Request $request) 
    {
		$trnsId = $request->transId;

        if (!empty($trnsId)) {

            $paymentList = StallPayment::where('transaction_no', $trnsId)->get();
			
            if (!$paymentList) {

				return response([
					'success' => false,
					'message' => 'Data not found.'
				]);
			}

			DB::beginTransaction();

			try {
				foreach ($paymentList as $payment) {

					if ($payment && $payment->status == 1) {

							$model = StallPayment::find($payment->id);
							$model->status = 2;
							$model->pay_status = 'success';
							$model->update();
	
							$stallMain = SpStallAssign::find($model->sp_stall_id);
							$dueAmt = $stallMain->total_cost - $payment->amount;

							if ($dueAmt > 0) {
								$stallMain->payment_status = 1;
								$stallMain->total_cost = $dueAmt;
							} else {
								$stallMain->payment_status = 2;
								$stallMain->total_cost = 0;
							}

							$stallMain->update();
							
					} else {

						return response([
							'success' => false,
							'message' => 'Invalid Transaction Number.'
						]);
					}
				}
				// sendNotification(url, buttonId, receiver Id, sender Id, 'custom', 'message', [sms,email,web], [1=sender,2=receiver])
				// $menuUrl = '/production-service/sales-distribution-admin/delivery-order-payment';
				// $msg = 'A payment has been completed under the transaction no. '.$trnsId;
				// $notification = NotificationSender::sendNotification($menuUrl, 0, 0, (int) user_id(), 'custom', $msg, [3], [2]);

				DB::commit();

				return response([
					'success' => 2,
					'message' => 'Payment paid successfully.'
					// 'notification' => json_decode($tification, true)
				]);

			} catch (\Exception $ex) {

				DB::rollback();
                
				return response([
					'success' => false,
					'message' => 'Payment failed..',
					'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
				]);
			}

        }
    }

    /**
     * stall payment decline Method
    */
    public function decline(Request $request)
    {
        $trnsId =	$request->transId;

        if(!empty($trnsId)) {

            $paymentList = StallPayment::where('transaction_no', $trnsId)->get();

			if (!$paymentList) {
				return response([
					'success' => false,
					'message' => 'Data not found.'
				]);
			}

			DB::beginTransaction();

			try {
				foreach ($paymentList as $payment) {

					if ($payment && $payment->status == 1) {
							$model = StallPayment::find($payment->id);
							$model->status     = 2;
							$model->update();

					} else {
						return response([
							'success' => false,
							'message' => 'Invalid Transaction Number.'
						]);
					}
				}

				DB::commit();

				return response([
					'success' => 2,
					'message' => 'Payment declined.'
				]);

			} catch (\Exception $ex) {

				DB::rollback();
                
				return response([
					'success' => false,
					'message' => 'Payment failed..',
					'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
				]);
			}

        }
    } 

    /**
     * stall payment cancal method
    */
    public function cancel(Request $request)
    {
        $trnsId = $request->transId;

        if (!empty($trnsId)) {

            $paymentList = StallPayment::where('transaction_no', $trnsId)->get();
			
            if (!$paymentList) {
				return response([
					'success' => false,
					'message' => 'Data not found.'
				]);
			}

			DB::beginTransaction();

			try {
				foreach ($paymentList as $payment) {

					if ($payment && $payment->status == 1) {
							$model = StallPayment::find($payment->id);
							$model->status     = 2;
							$model->update();

					} else {
						return response([
							'success' => false,
							'message' => 'Invalid Transaction Number.'
						]);
					}
				}

				DB::commit();

				return response([
					'success' => 2,
					'message' => 'Payment Canceled.'
				]);

			} catch (\Exception $ex) {

				DB::rollback();

				return response([
					'success' => false,
					'message' => 'Payment failed..',
					'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
				]);
			}

        }
    }

    /**
     * get stall data
     */
    public function getIndex(Request $request)
    {
        $stallAssign = SpStallAssign::with('details')
                    ->where('payment_status', 1)
                    ->where('created_by', $user_id)
                    ->get();
        
        if (!$stallAssign) {
            return response([
                'success' => false,
                'message' => 'Data not found.'
            ]);
        }

        return response([
            'success' => true,
            'message' => 'Data Fetch successfully',
            'data'    => $stallAssign
        ]); 
    }

    /**
     * Online Payment function for stall payment
     */ 
    public function onlinePayment(Request $request)
    {
        // return $request->all();
		$validationResult = StallPaymentOnlineValidation::validate($request);

        if (!$validationResult['success']) {
            return response($validationResult);
        }

        $transaction_no = strtoupper(uniqid());
		// $payment_list =  json_decode($request->paymentList, true);
		$payment_list =  $request->paymentList;

		foreach ($payment_list as $payment) {

			$pendingOrder = StallPayment::where('sp_stall_id', $payment['id'])
			                ->where('pay_status', 'pending')->first();

			if ($pendingOrder) {

				return response([
					'success' => false,
					'message' => 'Already have a pending order'
				]);
			}
		}

        try {

			DB::beginTransaction();

			foreach ($payment_list as $payment) {

				$model                         		= new StallPayment();
				$model->user_id                		= (int)user_id();
				$model->sp_stall_id 	            = $payment['id'];
				// $model->delivery_order_id 			= $payment['delivery_order_id'];

				if ($request->is_partial) {

					$model->is_partial   = $request->is_partial;
					$amount = $request->partial_amount;

				} else {

					$amount = $payment['due_amount'];
				}

				$model->amount              		= $amount;
				$model->payment_type_id         	= 1;
				$model->transaction_no          	= $transaction_no;
				$model->status          			= 1;
				$model->mac_addr                	= strtok(exec("getmac"), ' ');
				$model->pay_status              	= 'pending';
				$model->save();

				save_log([
					'data_id' => $model->id,
					'table_name' => 'stall_payments',
				]);
			}

            DB::commit();

            // for bypass payment
            // if ($request->is_bypass == 1) {
            //     $ekpay_payment = new EkpayLibrary();
            //     return $ekpay_payment->DeliveryOrderPaymentSuccess($transaction_no);
            // }

			if ($request->is_partial) {

				$ekpay_amount = $request->partial_amount;

			} else {

				$ekpay_amount = $request->amount;
			}

            $profile = SpProfile::whereUserId(user_id())->first();

            $pay_info = [];
			$pay_info['s_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/success';
            $pay_info['f_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/decline';
            $pay_info['c_uri']          = config('app.base_url.project_url').'stall-participant/stall-payment/cancel';
            $pay_info['cust_id']        = (int)user_id();
            $pay_info['cust_name']      = $profile->contact_person_name;
            $pay_info['cust_mobo_no']   = $profile->contact_no;
            $pay_info['cust_email']     = $profile->email;
            $pay_info['cust_mail_addr'] = $profile->address_en;
            $pay_info['trnx_id']        = $transaction_no;
            $pay_info['trnx_amt']       = $ekpay_amount;
            $pay_info['trnx_currency']  = 'BDT';
            $pay_info['ord_det']        = date('Y-m-d');

            $ekpay_payment = new EkpayLibrary();

            return $ekpay_payment->ekpay_payment($pay_info);


        } catch (\Exception $ex) {

            DB::rollback();

            return response([
                'success' => false,
                'message' => 'Failed to save data.',
                'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
            ]);
        }

        return response([
            'success' => true,
            'message' => 'Data save successfully',
            'data'    => []
        ]);
    }
}

