@extends('admin.layout.base')

@section('title', 'Provider Documents ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
          @if(Setting::get('demo_mode') == 1)
                <div class="col-md-12" style="height:50px;color:red;">
                    <h1>** Demo Mode : No Permission to Edit and Delete.</h1>
                </div>
             @endif
            <h5 class="mb-1">@lang('admin.provides.type_allocation')</h5>
            <div class="row">
                <div class="col-xs-12">
                    @if($ProviderService->count() > 0)
                    <hr><h6>Allocated Services :  </h6>
                    <table class="table table-striped table-bordered dataTable">
                        <thead>
                            <tr>
                                <th>@lang('admin.provides.service_name')</th>
                                @if($ProviderService[0]->service_type->ambulance == 1)
                                <th>Hospital Name</th>
                                @endif
                                <th>@lang('admin.provides.service_number')</th>
                                <th>@lang('admin.provides.service_model')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ProviderService as $service)
                            <tr>
                                <td>{{ $service->service_type->name }}@if($service->service_type->ambulance)(Ambulance) @endif</td>
                                @if($ProviderService[0]->service_type->ambulance == 1)
                                <td>{{$service->hospital->hospital_address}}</td>
                                @endif
                                <td>{{ $service->service_number }}</td>
                                <td>{{ $service->service_model }}</td>
                                <td>
                                @if( Setting::get('demo_mode') == 0)
                                    <form action="{{ route('admin.provider.document.service', [$Provider->id, $service->id]) }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button class="btn btn-danger btn-large btn-block">Delete</a>
                                    </form>
                                     @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('admin.provides.service_name')</th>
                                @if($ProviderService[0]->service_type->ambulance == 1)
                                <th>Hospital Name</th>
                                @endif
                                <th>@lang('admin.provides.service_number')</th>
                                <th>@lang('admin.provides.service_model')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                    <hr>
                </div>
                <form action="{{ route('admin.provider.document.store', $Provider->id) }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="col-xs-12" style="margin-bottom: 20px;">
                    <div class="col-xs-4">
                        <select class="form-control input" name="service_type" id="service_type" required>
                            @forelse($ServiceTypes->where('ambulance','!=','1') as $Type)
                            <option value="{{ $Type->id }}">{{ $Type->name }}</option>
                            @empty
                            <option>- Please Create a Service Type -</option>
                            @endforelse
                             <optgroup label="AMBULANCE" class="optiongroup">
                                 @foreach($ServiceTypes->where('ambulance','1') as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <input type="text" required name="service_number" class="form-control" placeholder="Number (CY 98769)">
                    </div>
                     <div class="col-xs-4">
                        <input type="text" required name="service_model" class="form-control" placeholder="Model (Audi R8 - Black)">
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-12">
                    <div class="col-xs-4" style="display:none" id="hospital">
                        <select class="form-control" name="hospital_id">
                             @foreach(get_all_hospitals() as $type)
                                <option value="{{$type->id}}">{{$type->hospital_address}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xs-4" style="display:none" id="document">
                         Upload Document
                     <input type="file" name="document_url" accept="application/pdf, image/*">
                    </div>
                    @if( Setting::get('demo_mode') == 0)
                    <div class="col-xs-4">
                        <button class="btn btn-primary btn-block" type="submit">Update</button>
                    </div>
                    @endif
                     <div class="clearfix"></div>
                </div> 
                </form>
            </div>
        </div>

        <div class="box box-block bg-white">
            <h5 class="mb-1">@lang('admin.provides.provider_documents')</h5>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.provides.document_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($Provider->documents as $Index => $Document)
                    <tr>
                        <td>{{ $Index + 1 }}</td>
                        <td>{{ $Document->document->name }}</td>
                        <td>{{ $Document->status }}</td>
                        <td>
                            <div class="input-group-btn">
                            @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.provider.document.edit', [$Provider->id, $Document->id]) }}"><span class="btn btn-success btn-large">View</span></a>
                                <button class="btn btn-danger btn-large" form="form-delete">Delete</button>
                                <form action="{{ route('admin.provider.document.destroy', [$Provider->id, $Document->id]) }}" method="POST" id="form-delete">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.provides.document_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $('#service_type').change(function() {
    var opt = $(this).find(':selected');
    var sel = opt.text();
    var opt_val = opt.closest('optgroup').attr('label');
    
    if(typeof opt_val !== "undefined"){
        $("#hospital").removeAttr("style");
        $("#document").removeAttr("style");
     }else{
        $("#hospital").css("display", "none");
        $("#document").css("display", "none");
     }  
    });
</script>
@endsection