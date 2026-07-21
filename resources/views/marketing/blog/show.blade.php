@extends('user.layout.app')

@section('content')

@section('meta')
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $post['meta_keywords'] }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $post['image'] }}">
    <meta property="og:type" content="article">
@endsection

@include('marketing.partials.tracking-head')
@include('marketing.partials.tracking-body')

<style>
    .article-header {
        background: url('{{ $post['image'] }}') center/cover no-repeat;
        position: relative;
        padding: 100px 0 60px 0;
        margin-top: -50px;
        color: white;
    }
    .article-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8));
    }
    .article-title-box {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }
    .article-title-box h1 {
        font-weight: 800;
        font-size: 40px;
        line-height: 1.3;
        margin-bottom: 20px;
        color: white;
    }
    .article-meta {
        font-size: 14px;
        color: rgba(255,255,255,0.8);
    }
    .article-body {
        max-width: 750px;
        margin: 50px auto;
        font-size: 17px;
        line-height: 1.8;
        color: #444;
        font-family: Georgia, serif;
    }
    .article-body h2 {
        font-weight: bold;
        color: #1a1a3e;
        margin-top: 40px;
        margin-bottom: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .article-body h3 {
        font-weight: bold;
        color: #764ba2;
        margin-top: 30px;
        margin-bottom: 15px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .article-body ul, .article-body ol {
        margin-bottom: 25px;
        padding-left: 20px;
    }
    .article-body li {
        margin-bottom: 10px;
    }
    .article-cta {
        background: #f8f9fa;
        border: 1px solid #764ba2;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        margin-top: 50px;
        margin-bottom: 50px;
    }
    .article-cta h4 {
        margin-top: 0;
        font-weight: bold;
        color: #1a1a3e;
    }
</style>

<div class="article-header">
    <div class="article-overlay"></div>
    <div class="container relative">
        <div class="article-title-box">
            <span class="label label-primary" style="background: #764ba2; margin-bottom: 15px; display: inline-block;">Déplacement Abidjan</span>
            <h1>{{ $post['title'] }}</h1>
            <div class="article-meta">
                <i class="fa fa-calendar"></i> Publié le {{ date('d M, Y', strtotime($post['date'])) }}
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="article-body">
        {!! $post['content'] !!}

        <!-- Inline CTA to convert readers -->
        <div class="article-cta">
            <h4>Besoin d'un chauffeur professionnel immédiatement ?</h4>
            <p style="color: #666; margin-bottom: 20px;">Ne prenez pas de risque. Réservez votre véhicule VIP ou transfert aéroport avec PicMe en quelques secondes, prix fixe garanti.</p>
            <a href="{{ $whatsapp_link }}" target="_blank" class="btn" style="background: #25d366; color: white; border-radius: 25px; padding: 12px 30px; font-weight: bold; font-size: 16px;">
                <i class="fa fa-whatsapp"></i> Réserver sur WhatsApp
            </a>
            <p style="margin-top: 15px; font-size: 13px; margin-bottom: 0;"><a href="{{ url('/airport') }}">Ou découvrir nos formules aéroport</a></p>
        </div>
    </div>
</div>

{{-- JSON-LD SEO ARTICLE SCHEMA --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ $post['title'] }}",
  "image": [
    "{{ $post['image'] }}"
   ],
  "datePublished": "{{ $post['date'] }}",
  "dateModified": "{{ $post['date'] }}",
  "author": [{
      "@type": "Organization",
      "name": "PicMe Transfert VIP",
      "url": "{{ url('/') }}"
    }]
}
</script>

{{-- Tracking JS --}}
<script src="{{ asset('asset/js/marketing-tracking.js') }}"></script>
@endsection
