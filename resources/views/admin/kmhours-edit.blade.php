@extends('admin.layout.base')

@section('title', 'Modifier le Forfait')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f8fafc;
        }
        .premium-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: 50px auto;
            overflow: hidden;
        }
        .premium-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            padding: 40px;
            color: white;
            text-align: center;
        }
        .premium-body {
            padding: 40px;
        }
        .form-label-custom {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            display: block;
        }
        .form-control-premium {
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            padding: 14px 20px;
            font-size: 1rem;
            transition: all 0.2s;
            background: #f8fafc;
            height: auto;
        }
        .form-control-premium:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .btn-update {
            background: #2563eb;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-update:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="premium-card">
            <div class="premium-header">
                <h4 class="font-weight-bold">Éditer le Forfait</h4>
                <p class="mb-0 opacity-75">Modifiez les paramètres du package #{{$KmHour->id}}</p>
            </div>
            
            <div class="premium-body">
                <form action="{{route('admin.kmhour.update', $KmHour->id)}}" method="POST">
                    {{csrf_field()}}
                    
                    <div class="form-group mb-4">
                        <label class="form-label-custom">Distance Incluse (KM)</label>
                        <input class="form-control form-control-premium" type="number" 
                               value="{{ $KmHour->kilometer }}" name="kilometer" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label-custom">Durée Incluse (Heures)</label>
                        <input class="form-control form-control-premium" type="number" 
                               value="{{ $KmHour->hour }}" name="hour" required>
                    </div>

                    <button type="submit" class="btn btn-update">
                        Enregistrer les Modifications
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="{{route('admin.kmhour.index')}}" class="text-muted font-weight-bold">
                            <i class="fa fa-angle-left mr-1"></i> Retour à la liste
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection



