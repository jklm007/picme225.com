@extends('admin.layout.base')

@section('title', 'Create Subscription Plan')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.subscription.index') }}" class="btn btn-default pull-right">
                    <i class="fa fa-angle-left"></i> Back
                </a>

                <h5 style="margin-bottom: 2em;">Create Subscription Plan (DAO)</h5>

                <form class="form-horizontal" action="{{ route('admin.subscription.store') }}" method="POST" role="form">
                    {{ csrf_field() }}

                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">Plan Name</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name"
                                placeholder="e.g., PRO Plan">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="service_id" class="col-xs-12 col-form-label">Service Category</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="service_id" name="service_id">
                                <option value="">Aucun (Plan par défaut / Free)</option>
                                @foreach($services as $serv)
                                    <option value="{{ $serv->id }}" {{ old('service_id') == $serv->id ? 'selected' : '' }}>{{ $serv->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="period" class="col-xs-12 col-form-label">Validity Period</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="period" name="period" required>
                                <option value="DAILY" {{ old('period') == 'DAILY' ? 'selected' : '' }}>Daily (JOURNALIER)</option>
                                <option value="WEEKLY" {{ old('period') == 'WEEKLY' ? 'selected' : '' }}>Weekly (HEBDOMADAIRE)</option>
                                <option value="MONTHLY" {{ old('period') == 'MONTHLY' ? 'selected' : 'selected' }}>Monthly (MENSUEL)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="price" class="col-xs-12 col-form-label">Plan Price
                            ({{ Setting::get('currency', 'FCFA') }})</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('price') }}" name="price" required
                                id="price" placeholder="10000">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="max_categories" class="col-xs-12 col-form-label">Maximum Vehicle Categories Allowed</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('max_categories', 1) }}" name="max_categories"
                                required id="max_categories" placeholder="e.g., 1 or 2">
                        </div>
                    </div>

                    <hr>
                    <h6>Global Commission Settings</h6>

                    <div class="form-group row">
                        <label for="commission_type" class="col-xs-12 col-form-label">Default Commission Type</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="commission_type" name="commission_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ({{ Setting::get('currency', 'FCFA') }})</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="commission_value" class="col-xs-12 col-form-label">Default Commission Value</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01" value="{{ old('commission_value') }}"
                                name="commission_value" required id="commission_value" placeholder="10 or 2000">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="fixed_fee" class="col-xs-12 col-form-label">Default Fixed Fee (CFA)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01" value="{{ old('fixed_fee', 0) }}"
                                name="fixed_fee" required id="fixed_fee" placeholder="e.g., 100 for GOLD">
                        </div>
                    </div>

                    <hr>
                    <h6>DAO Benefits & Priority</h6>

                    <div class="form-group row">
                        <label for="priority" class="col-xs-12 col-form-label">Dispatch Priority Level (0-10)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ old('priority', 0) }}" name="priority"
                                required id="priority" placeholder="High values get rides first">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="staking_bonus_percentage" class="col-xs-12 col-form-label">Staking Bonus ECO/CFA
                            (%)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01"
                                value="{{ old('staking_bonus_percentage', 0) }}" name="staking_bonus_percentage" required
                                id="staking_bonus_percentage" placeholder="Percentage of commission returned to driver">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <input type="checkbox" name="insurance_included" value="1" checked>
                            <label>Include Professional Insurance</label>
                        </div>
                    </div>

                    <hr>
                    <h6>Granular Commissions (per Service Type)</h6>
                    <p class="text-muted">Optional: Define specific commissions for certain categories. If left empty, the
                        global commission above will be used.</p>

                    @foreach($service_types as $service)
                        <div class="form-group row">
                            <label class="col-xs-12 col-form-label"><strong>{{ $service->name }}</strong></label>
                            <div class="col-xs-5">
                                <select class="form-control" name="service_commissions[{{ $service->id }}][type]">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed ({{ Setting::get('currency', 'FCFA') }})</option>
                                </select>
                            </div>
                            <div class="col-xs-5">
                                <input class="form-control" type="number" step="0.01"
                                    name="service_commissions[{{ $service->id }}][value]" placeholder="Value (Override)">
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <button type="submit" class="btn btn-primary">Create Subscription Plan</button>
                            <a href="{{ route('admin.subscription.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection