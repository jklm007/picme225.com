@extends('provider.layout.app')
@section('body-class', 'light-theme')

@section('content')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="{{ route('provider.profile.index') }}" class="pro-head-link">Profile</a>
        <a href="#" class="pro-head-link active">Manage Documents</a>
        <a href="{{ route('provider.location.index') }}" class="pro-head-link">Update Location</a>
    </div>
</div>

<div class="pro-dashboard-content gray-bg">
    <div class="container">
        <div class="manage-docs pad30">
            <div class="manage-doc-content">
                <div class="manage-doc-section pad50">
                    <div class="manage-doc-section-head row no-margin">
                        <h3 class="manage-doc-tit">
                            Driver's Documents
                        </h3>
                    </div>

                    <div class="manage-doc-section-content">
                        @foreach($DriverDocuments as $Document)
                        <div class="manage-doc-box row no-margin border-top">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-left">
                                    <p class="manage-txt">{{ $Document->name }}</p>
                                    <p class="license">Expires: {{ $Provider->document($Document->id) ? $Provider->document($Document->id)->expires_at : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-center text-center">
                                    <p class="manage-badge {{ $Provider->document($Document->id) ? ($Provider->document($Document->id)->status == 'ASSESSING' ? 'yellow-badge' : 'green-badge') : 'red-badge'}}">
                                        {{ $Provider->document($Document->id) ? $Provider->document($Document->id)->status : 'MISSING' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-right text-right">
                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                        <form action="{{ route('provider.documents.update', $Document->id) }}" method="POST" enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            {{ method_field('PATCH') }}
                                            <div class="form-control" data-trigger="fileinput">
                                                <span class="fileinput-filename"></span>
                                            </div>
                                            <span class="input-group-addon btn btn-default btn-file fileinput-exists btn-submit">
                                                <button>
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </span>
                                            <span class="input-group-addon btn btn-default btn-file">
                                                <span class="fileinput-new upload-link">
                                                    <i class="fa fa-upload upload-icon"></i> Upload
                                                </span>
                                                <span class="fileinput-exists">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                                <input type="file" name="document" accept="application/pdf, image/*">
                                            </span>
                                            <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="manage-doc-section">
                    <div class="manage-doc-section-head row no-margin">
                        <h3 class="manage-doc-tit">
                            Vehicle's Documents
                        </h3>
                    </div>

                    <div class="manage-doc-section-content">
                        @foreach($VehicleDocuments as $Document)
                        <div class="manage-doc-box row no-margin border-top">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-left">
                                    <p class="manage-txt">{{ $Document->name }}</p>
                                    <p class="license">Expires: {{ $Provider->document($Document->id) ? $Provider->document($Document->id)->expires_at : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-center text-center">
                                    <p class="manage-badge {{ $Provider->document($Document->id) ? ($Provider->document($Document->id)->status == 'ASSESSING' ? 'yellow-badge' : 'green-badge') : 'red-badge'}}">
                                        {{ $Provider->document($Document->id) ? $Provider->document($Document->id)->status : 'MISSING' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="manage-doc-box-right text-right">
                                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                        <form action="{{ route('provider.documents.update', $Document->id) }}" method="POST" enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            {{ method_field('PATCH') }}
                                            <div class="form-control" data-trigger="fileinput">
                                                <span class="fileinput-filename"></span>
                                            </div>
                                            <span class="input-group-addon btn btn-default btn-file fileinput-exists btn-submit">
                                                <button>
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </span>
                                            <span class="input-group-addon btn btn-default btn-file">
                                                <span class="fileinput-new upload-link">
                                                    <i class="fa fa-upload upload-icon"></i> Upload
                                                </span>
                                                <span class="fileinput-exists">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                                <input type="file" name="document" accept="application/pdf, image/*">
                                            </span>
                                            <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('styles')
<link href="{{ asset('asset/css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css">
<style type="text/css">
    body, .page-content, .pro-dashboard, .pro-dashboard-content {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    .pro-dashboard-head {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e9ecef !important;
        margin-top: 60px !important;
    }
    .pro-head-link {
        color: #495057 !important;
    }
    .pro-head-link.active {
        color: #000000 !important;
        border-bottom: 2px solid #000000 !important;
        font-weight: bold;
    }
    .manage-docs {
        background: #ffffff !important;
        padding: 30px 15px !important;
    }
    .manage-doc-section {
        background: #ffffff !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 12px;
        padding: 24px !important;
        margin-bottom: 30px !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.01);
    }
    .manage-doc-tit {
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #111111 !important;
        margin-bottom: 20px !important;
    }
    .manage-doc-box {
        background: #f8f9fa !important;
        border: 1px solid #e9ecef !important;
        border-radius: 8px;
        padding: 15px !important;
        margin-bottom: 15px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    .manage-txt {
        font-weight: 600 !important;
        color: #111111 !important;
        margin: 0 !important;
    }
    .license {
        font-size: 11px !important;
        color: #6c757d !important;
        margin: 5px 0 0 0 !important;
    }
    .manage-badge {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        display: inline-block !important;
        margin: 0 !important;
    }
    .yellow-badge {
        background-color: rgba(241, 196, 15, 0.15) !important;
        color: #d4ac0d !important;
        border: 1px solid rgba(241, 196, 15, 0.3) !important;
    }
    .green-badge {
        background-color: rgba(46, 204, 113, 0.15) !important;
        color: #27ae60 !important;
        border: 1px solid rgba(46, 204, 113, 0.3) !important;
    }
    .red-badge {
        background-color: rgba(231, 76, 60, 0.15) !important;
        color: #c0392b !important;
        border: 1px solid rgba(231, 76, 60, 0.3) !important;
    }
    .input-group-addon.btn {
        background: #2ecc71 !important;
        border: 1px solid #2ecc71 !important;
        color: #ffffff !important;
        border-radius: 4px !important;
        height: 38px !important;
        line-height: 18px !important;
        font-weight: 600 !important;
    }
    .input-group-addon.btn.btn-default {
        background: #f8f9fa !important;
        border: 1px solid #ced4da !important;
        color: #495057 !important;
    }
</style>
@endsection

@section('scripts')
