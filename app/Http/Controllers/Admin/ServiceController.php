<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceType;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::all();
        return view('admin.service.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.service.create');
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
            'image' => 'mimes:ico,png,jpeg,jpg'
        ]);

        try {
            $service = $request->all();
            
            // Handle status checkbox
            $service['status'] = $request->has('status') ? 1 : 0;

            if($request->hasFile('image')) {
                $service['image'] = $request->image->store('service/images');
            }

            Service::create($service);

            return redirect()->route('admin.service.index')->with('flash_success', 'Service Created Successfully');

        } catch (Exception $e) {
            return back()->with('flash_error', 'Service Not Found');
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
            $service = Service::findOrFail($id);
            return view('admin.service.edit',compact('service'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Not Found');
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
            'name' => 'required|max:255',
            'image' => 'mimes:ico,png,jpeg,jpg'
        ]);

        try {
            $service = Service::findOrFail($id);

            if($request->hasFile('image')) {
                if($service->image) {
                    // Storage::delete($service->image);
                }
                $service->image = $request->image->store('service/images');
            }

            $service->name = $request->name;
            $service->status = $request->has('status') ? 1 : 0;
            $service->save();

            return redirect()->route('admin.service.index')->with('flash_success', 'Service Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Not Found');
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
            Service::find($id)->delete();
            return back()->with('message', 'Service deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Service Not Found');
        }
    }
}
