<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;
use App\Helpers\Helper;
use Exception;
use Storage;

class RentalController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicles = MarketplaceListing::where('type', 'RENTAL')->orderBy('created_at', 'desc')->get();
        return view('admin.location.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.location.create');
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
            'title' => 'required|max:255',
            'brand' => 'required|max:255',
            'model' => 'required|max:255',
            'price' => 'required|numeric',
            'plate_number' => 'required',
            'location_city' => 'required',
            'cover_image' => 'required|mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {
            $vehicle = new MarketplaceListing();
            $vehicle->fill($request->all());
            $vehicle->user_id = \Auth::guard('admin')->user()->id;
            $vehicle->type = 'RENTAL';
            $vehicle->status = 'ACTIVE';

            if($request->hasFile('cover_image')) {
                $vehicle->cover_image = Helper::upload_picture($request->file('cover_image'));
            }

            $vehicle->save();

            return redirect()->route('admin.location.index')->with('flash_success', 'Véhicule ajouté avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vehicle = MarketplaceListing::findOrFail($id);
        return view('admin.location.edit', compact('vehicle'));
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
            'title' => 'required|max:255',
            'brand' => 'required|max:255',
            'model' => 'required|max:255',
            'price' => 'required|numeric',
            'location_city' => 'required',
            'cover_image' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {
            $vehicle = MarketplaceListing::findOrFail($id);
            $vehicle->fill($request->all());

            if($request->hasFile('cover_image')) {
                $vehicle->cover_image = Helper::upload_picture($request->file('cover_image'));
            }

            $vehicle->save();

            return redirect()->route('admin.location.index')->with('flash_success', 'Véhicule mis à jour');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la mise à jour');
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
            MarketplaceListing::find($id)->delete();
            return back()->with('message', 'Véhicule supprimé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Display a listing of bookings.
     *
     * @return \Illuminate\Http\Response
     */
    public function bookings()
    {
        $bookings = RentalBooking::with(['listing', 'user'])->orderBy('created_at', 'desc')->get();
        return view('admin.location.bookings', compact('bookings'));
    }
}
