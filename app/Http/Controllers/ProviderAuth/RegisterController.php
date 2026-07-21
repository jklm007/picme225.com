<?php

namespace App\Http\Controllers\ProviderAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;

use Setting;
use Validator;

use App\Models\Provider;
use App\Models\ProviderService;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     * @var string
     */
    protected $redirectTo = '/provider/';

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        // Middleware provider.guest retiré pour permettre l'accès libre à l'inscription
    }

    /**
     * Get a validator for an incoming registration request.
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:providers',
            'password' => 'required|min:6|confirmed',
            'mobile' => 'required|unique:providers,mobile'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     * @param  array  $data
     * @return Provider
     */
    protected function create(array $data)
    {
        $Provider = Provider::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'gender' => isset($data['gender']) ? $data['gender'] : 'MALE',
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
        ]);

        if (isset($data['document_url'])) {
          $document = $data['document_url']->store('provider/hospitaldocuments');
        } else {
            $document = null;
        }

        if(isset($data['service_type_id'])) {
            $provider_service = ProviderService::create([
                'provider_id' => $Provider->id,
                'service_type_id' => $data['service_type_id'],
                'service_number' => $data['service_number'] ?? '',
                'service_model' => $data['service_model'] ?? '',
                'hospital_id' => isset($data['hospital_id']) ? $data['hospital_id'] : null,
                'document_url' => $document,
            ]);
        }

        if (Setting::get('demo_mode', 0) == 1) {
            $Provider->update(['status' => 'approved']);
            if(isset($provider_service)) {
                $provider_service->update([
                    'status' => 'active',
                ]);
            }
        }
        
        return $Provider;
    }

    /**
     * Show the application registration form.
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('provider.auth.register');
    }

    /**
     * Get the guard to be used during registration.
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('provider');
    }
}
