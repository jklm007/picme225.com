@extends('user.layout.app')

@section('content')
<style>
    .blog-header {
        background: linear-gradient(135deg, #1a1a3e 0%, #764ba2 100%);
        padding: 50px 0;
        color: white;
        text-align: center;
        margin-top: -50px;
        padding-top: 100px;
    }
    .blog-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        margin-bottom: 30px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .blog-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .blog-content {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .blog-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.4;
    }
    .blog-desc {
        color: #666;
        font-size: 14px;
        margin-bottom: 15px;
        flex: 1;
    }
    .blog-meta {
        font-size: 12px;
        color: #999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #eee;
        padding-top: 10px;
    }
    .blog-btn {
        color: #764ba2;
        font-weight: bold;
        text-decoration: none;
    }
    .blog-btn:hover {
        color: #1a1a3e;
    }
</style>

<div class="blog-header">
    <div class="container">
        <h1 style="font-weight: bold; font-size: 36px; margin-bottom: 10px;">Le Blog PicMe</h1>
        <p style="font-size: 16px; opacity: 0.9;">Toutes nos astuces et guides pour vos trajets et location de véhicules en Côte d'Ivoire.</p>
    </div>
</div>

<div class="container" style="padding: 50px 0;">
    <div class="row">
        @foreach($posts as $post)
        <div class="col-md-4 col-sm-6">
            <div class="blog-card">
                <a href="{{ url('/blog/'.$post['slug']) }}">
                    <img class="blog-img" src="{{ $post['image'] }}" alt="{{ $post['title'] }}">
                </a>
                <div class="blog-content">
                    <a href="{{ url('/blog/'.$post['slug']) }}" style="text-decoration: none;">
                        <h2 class="blog-title">{{ $post['title'] }}</h2>
                    </a>
                    <p class="blog-desc">{{ \Illuminate\Support\Str::limit($post['meta_description'], 120) }}</p>
                    <div class="blog-meta">
                        <span><i class="fa fa-calendar"></i> {{ date('d M, Y', strtotime($post['date'])) }}</span>
                        <a href="{{ url('/blog/'.$post['slug']) }}" class="blog-btn">Lire l'article <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
