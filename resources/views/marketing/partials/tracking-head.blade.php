{{-- Google Tag Manager - head --}}
{{-- Replace GTM-XXXXXXX with your real GTM container ID --}}
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ config("services.gtm.id", "GTM-XXXXXXX") }}');</script>
<!-- End Google Tag Manager -->

{{-- GA4 --}}
{{-- Replace G-XXXXXXXXXX with your real GA4 Measurement ID --}}
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.id', 'G-XXXXXXXXXX') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config("services.ga4.id", "G-XXXXXXXXXX") }}', {
        'page_title': '{{ $title ?? "PicMe" }}',
        'page_location': window.location.href,
        'custom_map': {
            'dimension1': 'service_type',
            'dimension2': 'utm_source'
        },
        'service_type': '{{ $service_type ?? "" }}',
        'utm_source': '{{ $utm_source ?? "direct" }}'
    });
</script>
