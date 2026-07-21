<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;

use Socialite;
use Setting;
use Exception;
use Validator;

use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderService;
use Illuminate\Support\Facades\Hash;

class SocialLoginController extends Controller
{
	
    public function redirectToFaceBook(){
        return Socialite::driver('facebook')->redirect();
    }
    
    public function providerToFaceBook(){
        return Socialite::driver('facebook')->with(['state' => 'provider'])->redirect();
    }

    public function handleFacebookCallback(Request $request){
        $AccessToken = Socialite::driver('facebook')->getAccessTokenResponse($request->code);
        if($token = $AccessToken['access_token']){
            $facebook = Socialite::driver('facebook')->userFromToken($token);
            $guard = request()->input('state');
            if($guard == 'provider') {
                if($facebook->id){
                    $FacebookSql = Provider::where('social_unique_id',$facebook->id);
                    if($facebook->email !=""){
                        $FacebookSql->orWhere('email',$facebook->email);
                    }
                    $AuthUser = $FacebookSql->first();
                    if($AuthUser){
                        $AuthUser->social_unique_id=$facebook->id;
                        $AuthUser->save();
                        Auth::guard('provider')->loginUsingId($AuthUser->id);
                        return redirect('provider');
                    }else{   
                        $new=new Provider();
                        $new->email=$facebook->email;
                        $name = explode(' ', $facebook->name, 2);
                        $new->first_name=$name[0];
                        $new->last_name= isset($name[1]) ? $name[1] : '';
                        $new->password=bcrypt($facebook->id);
                        $new->social_unique_id=$facebook->id;
                        // $new->mobile=$request->mobile;
                        $new->avatar=$facebook->avatar;
                        $new->login_by="facebook";
                        $new->save();

                        if(Setting::get('demo_mode', 0) == 1) {
                            $new->update(['status' => 'approved']);
                            ProviderService::create([
                                'provider_id' => $new->id,
                                'service_type_id' => '1',
                                'status' => 'active',
                                'service_number' => '4pp03ets',
                                'service_model' => 'Audi R8',
                            ]);
                        }
                        Auth::guard('provider')->loginUsingId($new->id);
                        return redirect('provider');
                    }
                } else {
                    return redirect('provider');
                }
            } else {
                if($facebook->id){
                    $FacebookSql = User::where('social_unique_id',$facebook->id);
                    if($facebook->email !=""){
                        $FacebookSql->orWhere('email',$facebook->email);
                    }
                    $AuthUser = $FacebookSql->first();
                    if($AuthUser){
                        $AuthUser->social_unique_id=$facebook->id;
                        $AuthUser->save();
                        Auth::loginUsingId($AuthUser->id);
                        return redirect('dashboard');
                    }else{   
                        $new=new User();
                        $new->email=$facebook->email;
                        $name = explode(' ', $facebook->name, 2);
                        $new->first_name=$name[0];
                        $new->last_name= isset($name[1]) ? $name[1] : '';
                        $new->password=bcrypt($facebook->id);
                        $new->social_unique_id=$facebook->id;
                        //$new->mobile=$facebook->mobile;
                        $new->picture=$facebook->avatar;
                        $new->login_by="facebook";
                        $new->save();
                        Auth::loginUsingId($new->id);
                        return redirect('dashboard');
                    }
                }else{
                    return redirect('dashboard');
                }
            }
        }else{
           return redirect()->to('register');
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function facebookViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=> 'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
    	
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }

        $user = Socialite::driver('facebook')->stateless();
        $FacebookDrive = $user->userFromToken( $request->accessToken);
       
        try{

        	$FacebookSql = User::where('social_unique_id',$FacebookDrive->id);
            if($FacebookDrive->email !=""){
                $FacebookSql->orWhere('email',$FacebookDrive->email);
            }
            $AuthUser = $FacebookSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$FacebookDrive->id; 
            	$AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                $AuthUser->mobile=$request->mobile?:'';
                $AuthUser->login_by="facebook";
                $AuthUser->save();  
            }else{   
                $AuthUser=new User();
                $AuthUser->email=$FacebookDrive->email;
                $name = explode(' ', $FacebookDrive->name, 2);
                $AuthUser->first_name=$name[0];
                $AuthUser->last_name= isset($name[1]) ? $name[1] : '';
                $AuthUser->password=bcrypt($FacebookDrive->id);
                $AuthUser->social_unique_id=$FacebookDrive->id;
                $AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                $AuthUser->mobile=$request->mobile?:'';
                $AuthUser->picture=$FacebookDrive->avatar;
                $AuthUser->login_by="facebook";
                $AuthUser->save();
            }    
            if($AuthUser){
                $userToken = $AuthUser->token()?:$AuthUser->createToken('socialLogin');
                return response()->json([
                        "status" => true,
                        "token_type" => "Bearer",
                        "access_token" => $userToken->accessToken
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "Invalid credentials!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }

    public function providerToGoogle(){
        return Socialite::driver('google')->with(['state' => 'provider'])->redirect();
    }

    public function handleGoogleCallback(){
        try{
            $google = Socialite::driver('google')->user();
            if($google){
                $guard = request()->input('state');
                if($guard == 'provider') {
                    if($google->id){
                        $GoogleSql = Provider::where('social_unique_id',$google->id);
                        if($google->email !=""){
                            $GoogleSql->orWhere('email',$google->email);
                        }
                        $AuthUser = $GoogleSql->first();
                        if($AuthUser){ 
                            $AuthUser->social_unique_id=$google->id;
                            $AuthUser->save();  
                            Auth::guard('provider')->loginUsingId($AuthUser->id);
                            return redirect()->to('provider');
                        }else{   
                            $new=new Provider();
                            $new->email=$google->email;
                            $name = explode(' ', $google->name, 2);
                            $new->first_name=$name[0];
                            $new->last_name= isset($name[1]) ? $name[1] : '';
                            $new->password=bcrypt($google->id);
                            $new->social_unique_id=$google->id;
                            //$new->mobile=$google->mobile;
                            $new->avatar=$google->avatar;
                            $new->login_by="google";
                            $new->save();

                            if(Setting::get('demo_mode', 0) == 1) {
                                $new->update(['status' => 'approved']);
                                ProviderService::create([
                                    'provider_id' => $new->id,
                                    'service_type_id' => '1',
                                    'status' => 'active',
                                    'service_number' => '4pp03ets',
                                    'service_model' => 'Audi R8',
                                ]);
                            }
                            Auth::guard('provider')->loginUsingId($new->id);
                            return redirect()->route('provider');
                        }
                    }else{
                        return redirect()->route('provider');
                    }
                } else {
                    if($google->id){
                        $GoogleSql = User::where('social_unique_id',$google->id);
                        if($google->email !=""){
                            $GoogleSql->orWhere('email',$google->email);
                        }
                        $AuthUser = $GoogleSql->first();
                        if($AuthUser){ 
                            $AuthUser->social_unique_id=$google->id;
                            $AuthUser->save();  
                            Auth::loginUsingId($AuthUser->id);
                            return redirect()->to('dashboard');
                        }else{   
                            $new=new User();
                            $new->email=$google->email;
                            $name = explode(' ', $google->name, 2);
                            $new->first_name=$name[0];
                            $new->last_name= isset($name[1]) ? $name[1] : '';
                            $new->password=bcrypt($google->id);
                            $new->social_unique_id=$google->id;
                            //$new->mobile=$google->mobile;
                            $new->picture=$google->avatar;
                            $new->login_by="google";
                            $new->save();
                            Auth::loginUsingId($new->id);
                            return redirect()->route('dashboard');
                        }
                    }else{
                        return redirect()->route('dashboard');
                    }
                }
            }else{
               return redirect()->url('register');
            }

        } catch (Exception $e) {
            return back()->with('flash_errors', 'Google driver not found');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
   public function googleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'mobile' => 'nullable|string' // Le mobile est maintenant optionnel
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()], 422);
        }
        
        try {
            // Utiliser Socialite pour valider le token et récupérer les infos de l'utilisateur Google
            $userDriver = Socialite::driver('google')->stateless();
            $googleUser = $userDriver->userFromToken($request->accessToken);
           
            // Rechercher un utilisateur existant par son ID social ou son email
            $authUser = User::where('social_unique_id', $googleUser->id)
                            ->orWhere('email', $googleUser->email)
                            ->first();

            if($authUser){
                // L'UTILISATEUR EXISTE
                $authUser->login_by = "google";
                
                // Si l'utilisateur n'a pas de numéro et qu'aucun n'est fourni, on renvoie une erreur.
                if (empty($authUser->mobile) && !$request->has('mobile')) {
                    return response()->json([
                        'status' => false,
                        'error' => 'mobile_number_required',
                        'message' => 'This account needs a mobile number.'
                    ], 422);
                }

                // Si un nouveau numéro est fourni (après vérification OTP), on le met à jour.
                if ($request->has('mobile')) {
                    $authUser->mobile = $request->mobile;
                }

            } else {
                // NOUVEL UTILISATEUR
                // Pour un nouvel utilisateur, le numéro de téléphone est obligatoire.
                if (!$request->has('mobile') || empty($request->mobile)) {
                    return response()->json([
                        'status' => false,
                        'error' => 'mobile_number_required',
                        'message' => 'A mobile number is required for new users.'
                    ], 422);
                }

                $authUser = new User();
                $authUser->email = $googleUser->email;
                $name = explode(' ', $googleUser->name, 2);
                $authUser->first_name = $name[0];
                $authUser->last_name = isset($name[1]) ? $name[1] : '';
                $authUser->password = Hash::make($googleUser->id);
                $authUser->picture = $googleUser->avatar;
                $authUser->login_by = "google";
                $authUser->mobile = $request->mobile; // On assigne le numéro fourni
            }    

            // Mise à jour des informations communes (ID social, infos de l'appareil)
            $authUser->social_unique_id = $googleUser->id;
            $authUser->device_type = $request->device_type;
            $authUser->device_token = $request->device_token;
            $authUser->device_id = $request->device_id;
            $authUser->save();
            
            // Générer et renvoyer le token d'accès
            $authUser->tokens()->delete(); // Supprimer les anciens tokens pour plus de sécurité
            $token = $authUser->createToken('socialLogin')->accessToken;

            return response()->json([
                "status" => true,
                "token_type" => "Bearer",
                "access_token" => $token
            ]);

        } catch (Exception $e) {
            // Gérer les erreurs (par ex, token Google invalide)
            return response()->json(['status'=>false,'message' => $e->getMessage()], 500);
        }
    }


    public function account_kit(Request $request){

        // Initialize variables
        $app_id = Setting::get('fb_app_id');
        $secret = env('FB_APP_SECRET');
        $version = Setting::get('fb_app_version'); // 'v1.1' for example

        // Method to send Get request to url
        function doCurl($url) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $data = json_decode(curl_exec($ch), true);
          curl_close($ch);
          return $data;
        }

        // Exchange authorization code for access token
        $token_exchange_url = 'https://graph.accountkit.com/'.$version.'/access_token?'.
          'grant_type=authorization_code'.
          '&code='.$request->code.
          "&access_token=AA|$app_id|$secret";

        $data = doCurl($token_exchange_url);
        $user_id = $data['id'];
        $user_access_token = $data['access_token'];
        $refresh_interval = $data['token_refresh_interval_sec'];

        // Get Account Kit information
        $me_endpoint_url = 'https://graph.accountkit.com/'.$version.'/me?'.
          'access_token='.$user_access_token;
        $data = doCurl($me_endpoint_url);

        return $data;

    }
       public function ride_val(Request $request){
      try{
        $routing = get_osrm_routing($request->latitude, $request->longitude, $request->des_latitude, $request->des_longitude);
        
        if (!$routing) {
            return view('ride');
        }

        $meter   = $routing['distance'];
        $seconds = $routing['duration'];
        
        $kilometer1 = ($meter / 1000) * 0.621371;
        $kilometer  = number_format($kilometer1, 2, '.', '');
        $minutes    = round($seconds / 60);
        $time       = $minutes . ' mins';

            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');
            foreach(get_all_service_types() as $service_type){
                $service_name[]=$service_type->name;
                $price = $service_type->fixed;

            if($service_type->calculator == 'MIN') {
                $price += $service_type->minute * $minutes;
            } else if($service_type->calculator == 'HOUR') {
                $price += $service_type->minute * 60;
            } else if($service_type->calculator == 'DISTANCE') {
                $price += ($kilometer * $service_type->price);
            } else if($service_type->calculator == 'DISTANCEMIN') {
                $price += ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
            } else if($service_type->calculator == 'DISTANCEHOUR') {
                $price += ($kilometer * $service_type->price) + ($service_type->minute * $minutes * 60);
            } else {
                $price += ($kilometer * $service_type->price);
            }

            $tax_price = ( $tax_percentage/100 ) * $price;
            $total[] = $price + $tax_price;
                
            }
           // dd($total);
        return view('ride',compact('total','service_name'));
    }catch(Exception $e) {
        return view('ride');
    }
    }

}
