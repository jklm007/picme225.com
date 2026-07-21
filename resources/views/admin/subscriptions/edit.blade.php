@extends('admin.layout.base')

@section('title', 'Edit Subscription Plan')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.subscription.index') }}" class="btn btn-default pull-right">
                    <i class="fa fa-angle-left"></i> Back
                </a>

                <h5 style="margin-bottom: 2em;">Edit Subscription Plan: {{ $subscription->name }}</h5>

                <form class="form-horizontal" action="{{ route('admin.subscription.update', $subscription->id) }}"
                    method="POST" role="form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">Plan Name</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ $subscription->name }}" name="name" required
                                id="name" placeholder="e.g., PRO Plan">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="service_id" class="col-xs-12 col-form-label">Service Category</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="service_id" name="service_id">
                                <option value="" {{ is_null($subscription->service_id) ? 'selected' : '' }}>Aucun (Plan par défaut / Free)</option>
                                @foreach($services as $serv)
                                    <option value="{{ $serv->id }}" {{ $subscription->service_id == $serv->id ? 'selected' : '' }}>{{ $serv->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="period" class="col-xs-12 col-form-label">Validity Period</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="period" name="period" required>
                                <option value="DAILY" {{ $subscription->period == 'DAILY' ? 'selected' : '' }}>Daily (JOURNALIER)</option>
                                <option value="WEEKLY" {{ $subscription->period == 'WEEKLY' ? 'selected' : '' }}>Weekly (HEBDOMADAIRE)</option>
                                <option value="MONTHLY" {{ $subscription->period == 'MONTHLY' ? 'selected' : '' }}>Monthly (MENSUEL)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="price" class="col-xs-12 col-form-label">Plan Price
                            ({{ Setting::get('currency', 'FCFA') }})</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ $subscription->price }}" name="price"
                                required id="price" placeholder="10000">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="max_categories" class="col-xs-12 col-form-label">Maximum Vehicle Categories Allowed</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ $subscription->max_categories ?? 1 }}" name="max_categories"
                                required id="max_categories" placeholder="e.g., 1 or 2">
                        </div>
                    </div>

                    <hr>
                    <h6>Global Commission Settings</h6>

                    <div class="form-group row">
                        <label for="commission_type" class="col-xs-12 col-form-label">Default Commission Type</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="commission_type" name="commission_type" required>
                                <option value="percentage" {{ $subscription->commission_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ $subscription->commission_type == 'fixed' ? 'selected' : '' }}>Fixed
                                    Amount ({{ Setting::get('currency', 'FCFA') }})</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="commission_value" class="col-xs-12 col-form-label">Default Commission Value</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01"
                                value="{{ $subscription->commission_value }}" name="commission_value" required
                                id="commission_value" placeholder="10 or 2000">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="fixed_fee" class="col-xs-12 col-form-label">Default Fixed Fee (CFA)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01"
                                value="{{ $subscription->fixed_fee ?? 0 }}" name="fixed_fee" required
                                id="fixed_fee" placeholder="e.g., 100 for GOLD">
                        </div>
                    </div>

                    <hr>
                    <h6>DAO Benefits & Priority</h6>

                    <div class="form-group row">
                        <label for="priority" class="col-xs-12 col-form-label">Dispatch Priority Level (0-10)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" value="{{ $subscription->priority }}" name="priority"
                                required id="priority" placeholder="High values get rides first">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="staking_bonus_percentage" class="col-xs-12 col-form-label">Staking Bonus ECO/CFA
                            (%)</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="number" step="0.01"
                                value="{{ $subscription->staking_bonus_percentage }}" name="staking_bonus_percentage"
                                required id="staking_bonus_percentage" placeholder="Percentage returned to driver">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <input type="checkbox" name="insurance_included" value="1" {{ $subscription->insurance_included ? 'checked' : '' }}>
                            <label>Include Professional Insurance</label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="status" class="col-xs-12 col-form-label">Plan Status</label>
                        <div class="col-xs-10">
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" {{ $subscription->status == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ $subscription->status == 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6>Granular Commissions (per Service Type)</h6>
                    <p class="text-muted">Optional: Define specific commissions for certain categories.</p>

                    @foreach($service_types as $service)
                        <div class="form-group row">
                            <label class="col-xs-12 col-form-label"><strong>{{ $service->name }}</strong></label>
                            <div class="col-xs-5">
                                <select class="form-control" name="service_commissions[{{ $service->id }}][type]">
                                    <option value="percentage" {{ (isset($commission_types[$service->id]) && $commission_types[$service->id] == 'percentage') ? 'selected' : '' }}>Percentage (%)
                                    </option>
                                    <option value="fixed" {{ (isset($commission_types[$service->id]) && $commission_types[$service->id] == 'fixed') ? 'selected' : '' }}>Fixed
                                        ({{ Setting::get('currency', 'FCFA') }})</option>
                                </select>
                            </div>
                            <div class="col-xs-5">
                                <input class="form-control" type="number" step="0.01"
                                    name="service_commissions[{{ $service->id }}][value]"
                                    value="{{ $commissions[$service->id] ?? '' }}" placeholder="Value (Override)">
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group row">
                        <div class="col-xs-10">
                            <button type="submit" class="btn btn-primary">Update Subscription Plan</button>
                            <a href="{{ route('admin.subscription.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection