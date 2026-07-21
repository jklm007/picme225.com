<?php

namespace App\Http\Controllers\Resource;

use App\Models\User;
use App\Models\UserRequests;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Setting;

class UserResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at' , 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|unique:users,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $user = $request->all();

            $user['payment_mode'] = 'CASH';
            $user['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }

            $user = User::create($user);

            return back()->with('flash_success','User Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.user-details', compact('user'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $plans = \App\Models\SubscriptionPlan::where('target', 'user')->where('status', 1)->get();
            return view('admin.users.edit',compact('user', 'plans'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $user = User::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->mobile = $request->mobile;
            
            if ($request->has('subscription_plan_id')) {
                $user->subscription_plan_id = $request->subscription_plan_id;
                if ($request->subscription_plan_id) {
                    $plan = \App\Models\SubscriptionPlan::find($request->subscription_plan_id);
                    if ($plan) {
                        $days = 30;
                        if ($plan->period == 'DAILY') $days = 1;
                        if ($plan->period == 'WEEKLY') $days = 7;
                        if ($plan->period == 'YEARLY') $days = 365;
                        $user->subscription_expires_at = now()->addDays($days);
                    }
                } else {
                    $user->subscription_expires_at = null;
                }
            }
            
            $user->save();

            return redirect()->route('admin.user.index')->with('flash_success', 'User Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {

            User::find($id)->delete();
            return back()->with('message', 'User deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    /**
     * Display a listing of KYC requests.
     */
    public function kyc_requests()
    {
        $users = User::whereIn('kyc_status', ['PENDING', 'APPROVED', 'REJECTED'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
        return view('admin.users.kyc', compact('users'));
    }

    /**
     * Approve KYC request.
     */
    public function approve_kyc($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->kyc_status = 'APPROVED';
            $user->kyc_verified_at = \Carbon\Carbon::now();
            $user->save();

            return back()->with('flash_success', 'KYC Approved for ' . $user->first_name);
        } catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Reject KYC request.
     */
    public function reject_kyc(Request $request, $id)
    {
        $this->validate($request, [
            'reason' => 'required|string|max:255',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->kyc_status = 'REJECTED';
            $user->kyc_rejected_reason = $request->reason;
            $user->save();

            return back()->with('flash_success', 'KYC Rejected for ' . $user->first_name);
        } catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.user_id',$id)
                    ->RequestHistory()
                    ->get();

            return view('admin.request.index', compact('requests'));
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }

}
