<?php

namespace App\Http\Controllers\Resource;

use App\Hospital;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HospitalResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $hospitals = Hospital::orderBy('created_at' , 'desc')->get();
        return view('admin.hospital.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.hospital.create');
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
            'hospital_address' => 'required',
        ]);

        try{

            Hospital::create($request->all());
            return back()->with('flash_success','Hospital Saved Successfully');
        } 
        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Hospital Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $hospital = Hospital::findOrFail($id);
            return view('admin.hospital.edit',compact('hospital'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $this->validate($request, [
            'hospital_address' => 'required',
        ]);

        try {

           $hospital = Hospital::findOrFail($id);

            $hospital->hospital_address = $request->hospital_address;
            $hospital->latitude = $request->latitude;
            $hospital->longitude = $request->longitude;
            $hospital->save();

            return redirect()->route('admin.hospital.index')->with('flash_success', 'Hospital Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Hospital Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Hospital::find($id)->delete();
            return back()->with('message', 'Hospital deleted successfully');
        } 
        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Hospital Not Found');
        }
    }
}
