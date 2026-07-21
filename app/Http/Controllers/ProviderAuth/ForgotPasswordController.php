<?php

namespace App\Http\Controllers\ProviderAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('provider.guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('provider.auth.passwords.email');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('providers');
    }

    public function resetViaOtp(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'country_code' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $mobile = $request->country_code . $request->phone_number;
        $provider = \App\Models\Provider::where('mobile', $mobile)->first();
        
        if (!$provider) {
            return back()->withErrors(['mobile' => 'Aucun compte chauffeur trouvé avec ce numéro.']);
        }

        $provider->password = bcrypt($request->password);
        $provider->save();

        return redirect('/provider/login')->with('flash_success', 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
