<?php
namespace App\Library;
class EkpayLibrary
{
    private $mer_reg_id = "agri_test";
    private $mer_pas_key = "Agri@tst18";
    private $domain = "https://sandbox.ekpay.gov.bd/ekpaypg/v1?sToken=";
    // private $mer_reg_id = "mins_agri";
    // private $mer_pas_key = "MinS@aGr3321";
    // private $domain = "https://pg.ekpay.gov.bd/ekpaypg/v1?sToken=";
    private $ipn_channel = "0";
    private $ipn_email = 'mamunur6286@gmail.com';
    // private $ipn_email = 'ipn@ekpay.gov.bd';
    private $ipn_uri = "http://ekpay.gov.bd/v1/ipn/SendIpn";

    public function ekpay_payment($pay_info = [])
    {
        date_default_timezone_set('Asia/Dhaka');

        $ekp_arrya = array();
        $ekp_array["mer_info"]= array("mer_reg_id"=>$this->mer_reg_id, "mer_pas_key"=>$this->mer_pas_key);

        $ekp_array["req_timestamp"]=  date('Y-m-d H:i:s').' GMT+6';

        $ekp_array["feed_uri"]= array("s_uri"=>$pay_info['s_uri'], 
                                        "f_uri"=>$pay_info['f_uri'],
                                        "c_uri"=>$pay_info['c_uri']
                                    );

        $ekp_array["cust_info"]= array("cust_id"=>$pay_info['cust_id'], 
                                        "cust_name"=>$pay_info['cust_name'],
                                        "cust_mobo_no"=>$pay_info['cust_mobo_no'],
                                        "cust_email"=>$pay_info['cust_email'],
                                        "cust_mail_addr"=>$pay_info['cust_mail_addr']
                                    );

        $ekp_array["trns_info"]= array("trnx_id"=>$pay_info['trnx_id'], 
                                        "trnx_amt"=>$pay_info['trnx_amt'],
                                        "trnx_currency"=>"BDT",
                                        "ord_id"=>$pay_info['ord_id'],
                                        "ord_det"=>$pay_info['ord_det']
                                    );

        $ekp_array["ipn_info"]= array("ipn_channel"=>$this->ipn_channel,
                                        "ipn_email"=>$this->ipn_email,
                                        "ipn_uri"=>$this->ipn_uri
                                    );

        $MAC = exec("getmac"); 
	    $MAC = strtok($MAC, ' ');
        $ekp_array["mac_addr"]= '1.1.1.1';
        // $ekp_array["mac_addr"]= '114.130.119.102';


        // $adminUrl="https://pg.ekpay.gov.bd/ekpaypg/v1/merchant-api";
        $adminUrl="https://sandbox.ekpay.gov.bd/ekpaypg/v1/merchant-api";

        try{
            $ch = curl_init();
            $data_string = json_encode($ekp_array);
            $ch = curl_init($adminUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
            
            $result = curl_exec($ch);
            $result=  json_decode($result);
            curl_close($ch);
            // return var_dump($result);
            if(isset($result->secure_token)){
                $sToken = $result->secure_token;
                $trnsID = $ekp_array["trns_info"]["trnx_id"];
                return response([
                    'success' => true,
                    'message' => 'Token found success',
                    'url'    => $this->domain.$sToken.'&trnsID='.$trnsID,
                    // 'status'    => $result,
                    // 'data'    => $ekp_array
                ]);

            } else {
                return response([
                    'success' => false,
                    'message' => 'Ekpay url not found.Please click Bypass payment.',
                    'url'    => 'https://ekpay.gov.bd/',
                    // 'status'    => $result,
                    // 'data'    => $ekp_array
                ]);
            }

        } catch (\Exception $ex) {
            return response([
                'success' => false,
                'message' => 'token not found.',
                'errors'  => env('APP_ENV') !== 'production' ? $ex->getMessage() : ""
            ]);
        }
    }

}