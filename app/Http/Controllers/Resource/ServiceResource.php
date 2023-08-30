<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;

use App\ServiceType;
use App\KmHour;
use App\ServiceTypeRental;

class ServiceResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::all();
        if($request->ajax()) {
            return $services;
        } else {
            return view('admin.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kmhours = KmHour::all();
        return view('admin.service.create',compact('kmhours'));
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
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'minute' => 'required|numeric',
            'distance' => 'required|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR',
            'image' => 'mimes:ico,png'
        ]);

        try {
            $service = $request->all();
            if($request->has('hour')){
            }else{
            $service['hour'] = 0;
            }
            if($request->hasFile('image')) {
                $service['image'] = Helper::upload_picture($request->image);
            }

            $service = ServiceType::create($service);
            for($i=0; $i<count($request->ren_price); $i++){
                 ServiceTypeRental::create([
                            'service_type_id' => $service->id,
                            'km_hour_id' => $request->km_hour_id[$i],
                            'ren_price' => $request->ren_price[$i]
                        ]);
            }
            return back()->with('flash_success','Service Type Saved Successfully');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return ServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $kmhours_service = ServiceTypeRental::where('service_type_id',$id)->with('package')->get();
            $kmhours = KmHour::all();
            $service = ServiceType::findOrFail($id);
            return view('admin.service.edit',compact('service','kmhours','kmhours_service'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $this->validate($request, [
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'mimes:ico,png'
        ]);

        try {

            $service = ServiceType::findOrFail($id);

            if($request->hasFile('image')) {
                if($service->image) {
                    Helper::delete_picture($service->image);
                }
                $service->image = Helper::upload_picture($request->image);
            }

            $service->name = $request->name;
            $service->provider_name = $request->provider_name;
            $service->fixed = $request->fixed;
            $service->price = $request->price;
            $service->minute = $request->minute;
            $service->hour = $request->hour;
            $service->distance = $request->distance;
            $service->calculator = $request->calculator;
            $service->capacity = $request->capacity;
            $service->rental_amount = $request->rental_amount;
            $service->outstation_price = $request->outstation_price;
            $service->ambulance = $request->ambulance? : "0";
            $service->save();

            $service_rental=ServiceTypeRental::where('service_type_id',$service->id)->get();
            if(count($service_rental) != 0){
                ServiceTypeRental::where('service_type_id',$service->id)->delete();
            }
            for($i=0; $i<count($request->ren_price); $i++){
                 ServiceTypeRental::create([
                            'service_type_id' => $service->id,
                            'km_hour_id' => $request->km_hour_id[$i],
                            'ren_price' => $request->ren_price[$i]
                        ]);
            }

            return redirect()->route('admin.service.index')->with('flash_success', 'Service Type Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {
            ServiceType::find($id)->delete();
            ServiceTypeRental::where('service_type_id',$id)->delete();
            return back()->with('message', 'Service Type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }
}