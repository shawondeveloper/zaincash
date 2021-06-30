<?php
namespace codignwithshawon\zaincash\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ZainCashController extends Controller
{
    public function index(){
        return view('zaincash::request');
    }

    public function payRequest(Request $request){
      $amount = $request->input('amount');
      try {
            $zc = new ZainCash([
              'msisdn' => env('ZC_MSISDN'),
              'secret' => env('ZC_SECRET'),
              'merchantid'=> env('ZC_MERCHANTID'),
              'production_cred'=> ( env('ZC_ENV_PRODUCTION') ),
              'language'=>env('ZC_LANGUAGE'), // 'en' or 'ar'
              'redirection_url'=>\route('request')
          
            ]);
            $zc->charge(
              $amount,
              'Product purchase or something',
              'Order_00001'
            );
          
          } catch (Exception $e) {
              echo $e->getMessage();
          }
    }

    public function redirectRequest(){
      try {
        if (isset($_GET['token'])){
      
          $zc = new ZainCash([
            'msisdn' => env('ZC_MSISDN'),
            'secret' => env('ZC_SECRET'),
            'merchantid'=> env('ZC_MERCHANTID'),
            'production_cred'=> ( env('ZC_ENV_PRODUCTION') ),
            'language'=>env('ZC_LANGUAGE'),
            'redirection_url'=>\route('pay')
          ]);
          $result = $zc->decode( $_GET['token'] );
          if ($result['status']=='success'){
              // do something (ex: show sucess message)
              return view('zaincash::redirect');
          }
          else{
            // do something (ex: show errors)
            return 'Not Success';
          }
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }
      
  }
}
