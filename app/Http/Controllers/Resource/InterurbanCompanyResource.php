<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterurbanCompany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InterurbanCompanyResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = InterurbanCompany::orderBy('created_at', 'desc')->get();
        return view('admin.interurban_companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.interurban_companies.create');
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
            'contact_number' => 'required|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {
            $company = $request->all();

            if ($request->hasFile('logo')) {
                $company['logo'] = $request->logo->store('interurban/logos');
            }

            InterurbanCompany::create($company);

            return redirect()->route('admin.interurban-company.index')->with('flash_success', 'Compagnie créée avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InterurbanCompany  $interurbanCompany
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $company = InterurbanCompany::findOrFail($id);
            return view('admin.interurban_companies.show', compact('company'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Compagnie introuvable');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InterurbanCompany  $interurbanCompany
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $company = InterurbanCompany::findOrFail($id);
            return view('admin.interurban_companies.edit', compact('company'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Compagnie introuvable');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InterurbanCompany  $interurbanCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'type' => 'required|in:BIG,SMALL',
            'contact_number' => 'required|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {
            $company = InterurbanCompany::findOrFail($id);

            $update_data = $request->all();

            if ($request->hasFile('logo')) {
                if ($company->logo) {
                    Storage::delete($company->logo);
                }
                $update_data['logo'] = $request->logo->store('interurban/logos');
            }

            $company->update($update_data);

            return redirect()->route('admin.interurban-company.index')->with('flash_success', 'Compagnie mise à jour avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Compagnie introuvable');
        } catch (Exception $e) {
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InterurbanCompany  $interurbanCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $company = InterurbanCompany::findOrFail($id);
            if ($company->logo) {
                Storage::delete($company->logo);
            }
            $company->delete();
            return back()->with('flash_success', 'Compagnie supprimée avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Compagnie introuvable');
        } catch (Exception $e) {
            return back()->with('flash_error', $e->getMessage());
        }
    }
}
