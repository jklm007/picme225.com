@extends('admin.layout.base')

@section('title', 'Subscriptions ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Driver Subscriptions (DAO Governance)</h5>
            <a href="{{ route('admin.subscription.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Plan</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plan Name</th>
                        <th>Monthly Price</th>
                        <th>Default Commission</th>
                        <th>Priority</th>
                        <th>Insurance</th>
                        <th>Staking Bonus</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($subscriptions as $index => $plan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $plan->name }}</td>
                        <td>{{ currency($plan->price) }}</td>
                        <td>
                            {{ $plan->commission_value }} 
                            {{ $plan->commission_type == 'percentage' ? '%' : Setting::get('currency', 'FCFA') }}
                        </td>
                        <td>Level {{ $plan->priority }}</td>
                        <td>
                            @if($plan->insurance_included)
                                <span class="tag tag-success">Included</span>
                            @else
                                <span class="tag tag-danger">No</span>
                            @endif
                        </td>
                        <td>{{ $plan->staking_bonus_percentage }}%</td>
                        <td>{{ ucfirst($plan->status) }}</td>
                        <td>
                            <form action="{{ route('admin.subscription.destroy', $plan->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <a href="{{ route('admin.subscription.edit', $plan->id) }}" class="btn btn-info btn-block">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                                <button class="btn btn-danger btn-block" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
