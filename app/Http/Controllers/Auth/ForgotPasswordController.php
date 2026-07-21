<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

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
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('user.auth.passwords.email');
    }

    public function resetViaOtp(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'country_code' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $mobile = $request->country_code . $request->phone_number;
        $user = \App\Models\User::where('mobile', $mobile)->first();
        
        if (!$user) {
            return back()->withErrors(['mobile' => 'Aucun compte trouvé avec ce numéro.']);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return redirect('/login')->with('flash_success', 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
