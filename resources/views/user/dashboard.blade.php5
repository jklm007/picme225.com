<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
    }
    #map {
      height: 100vh;
      width: 100%;
      position: absolute;
      top: 0;
      left: 0;
      z-index: -1;
    }
    .drawer {
      width: 250px;
      height: 100%;
      position: fixed;
      top: 0;
      left: -250px;
      background-color: #1ededb;
      padding-top: 20px;
      z-index: 1000;
      transition: 0.3s;
    }
    .drawer a {
      padding: 12px 16px;
      text-decoration: none;
      font-size: 16px;
      color: white;
      display: block;
    }
    .drawer a:hover {
      color: #f1f1f1;
    }
    .drawer .close-btn {
      position: absolute;
      top: 10px;
      right: 25px;
      font-size: 36px;
    }
    .top-bar, .bottom-bar {
      position: fixed;
      width: 100%;
      z-index: 999;
      overflow-x: auto;
      white-space: nowrap;
    }
    .top-bar {
      top: 0;
      background-color: #2388ED;
      z-index: 2;
      display: flex;
      align-items: center;
      padding: 0px;
    }
    .top-bar a, .top-bar button {
      color: gold;
      padding: 1px 10px;
      text-decoration: none;
      border: none;
      background: none;
      cursor: pointer;
      font-size: 16px;
    }
    .bottom-bar {
      bottom: 0;
      background-color: rgba(105, 245, 112, 0.8);
      padding: 1px 0;
      display: flex;
      flex-wrap: nowrap;
      overflow-x: auto;
    }
    .bar-item {
      display: inline-block;
      color: white;
      padding: -10px 0px;
      text-align: center;
      padding-left: 2;
    }
    .bar-item .nav-link.active {
      background-color: #43de74d0;
    }
    .navbar-brand {
      position: fixed;
      padding: 10px;
    }
    .full-primary-btn.fare-btn {
      position: fixed;
      bottom: 1px;
      left: 50%;
      transform: translateX(-50%);
      background-size: cover;
      width: 100%;
      background-color: #01DFA5;
      border: none;
      height: 30px;
      font-size: 20px;
    }
    .open-drawer-btn {
      font-size: 30px;
      cursor: pointer;
      color: gold;
      padding: 0px 60px;
    }
    .open-drawer-btn img {
      width: 30px;
      height: 30px;
    }
    .user-img {
      text-align: center;
      margin-bottom: 20px;
    }
    .user-img .pro-img {
      background-size: cover;
      background-position: center;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin: 0 auto;
    }
    .user-img h4 {
      color: white;
      margin-top: 10px;
    }
    .tab-content > .tab-pane {
      display: none;
    }
    .tab-content > .active {
      display: block;
    }
    .car-radio {
      display: inline-block;
      margin: 5px;
    }
    .car-radio-inner {
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 4px;
      display: flex;
      align-items: center;
    }
    .car-radio-inner .img {
      flex: 1;
      margin-right: 10px;
    }
    .car-radio-inner .img img {
      max-width: 50px;
      max-height: 50px;
    }
    .car-radio-inner .name {
      flex: 2;
      text-align: left;
      padding-left: 10px;
    }
    .form-overlay {
      position: absolute;
      top: 110px;
      left: 10%;
      width: 80%;
      z-index: 3;
      padding: 10px;
      background: rgba(255, 255, 255, 0.7);
    }
    .dash-form {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div id="drawer" class="drawer">
    <a href="javascript:void(0)" class="close-btn" onclick="closeDrawer()">&times;</a>
    <div class="user-img">
      <?php $profile_image = img(Auth::user()->picture); ?>
      <div class="pro-img" style="background-image: url({{$profile_image}});"></div>
      <h4>{{Auth::user()->first_name}} {{Auth::user()->last_name}}</h4>
    </div>
    <a href="{{url('trips')}}">@lang('user.my_trips')</a>
    <a href="{{url('upcoming/trips')}}">@lang('user.upcoming_trips')</a>
    <a href="{{url('profile')}}">@lang('user.profile.profile')</a>
    <a href="{{url('change/password')}}">@lang('user.profile.change_password')</a>
    <a href="{{url('/payment')}}">@lang('user.payment')</a>
    <a href="{{url('/promotions')}}">@lang('user.promotion')</a>
    <a href="{{url('/wallet')}}">@lang('user.my_wallet') <span class="pull-right">{{currency(Auth::user()->wallet_balance)}}</span></a>
    <a href="{{ url('/logout')}}"
       onclick="event.preventDefault();
       document.getElementById('logout-form').submit();">@lang('user.profile.logout')</a>
    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
      {{ csrf_field() }}
    </form>
  </div>
  @include('common.notify')

  <div class="top-bar">
    <a class="navbar-brand" href="/dashboard">
      <img src="{{Setting::get('site_icon')}}" width="50" height="45" class="d-inline-block align-top" alt="">
    </a>
    <span class="open-drawer-btn" onclick="openDrawer()">&#9776;</span>
    <div class="bar-item"><a data-toggle="tab" href="#home" class="nav-link">Point-to-point</a></div>
    <div class="bar-item"><a data-toggle="tab" href="#rental" class="nav-link">Rental</a></div>
    <div class="bar-item"><a data-toggle="tab" href="#ambulance" class="nav-link">Ambulance</a></div>
    <div class="bar-item"><a data-toggle="tab" href="#out_station" class="nav-link">Out Station</a></div>
  </div>

  <form action="{{url('confirm/ride')}}" method="GET" onkeypress="return disableEnterKey(event);">
    <div class="form-overlay">
      <div class="tab-content">
        <div id="home" class="tab-pane fade show active">
          <div class="input-group dash-form">
            <input type="text" class="form-control" id="origin-input" name="s_address" placeholder="Enter pickup location">
          </div>
          <div class="input-group dash-form">
            <input type="text" class="form-control" id="destination-input" name="d_address" placeholder="Enter drop location">
          </div>
        </div>
        <!-- Autres onglets (rental, ambulance, out_station) -->
      </div>
    </div>

    <div class="bottom-bar">
      <!-- Contenu des onglets (home-content, rental-content, etc.) -->
    </div>
  </form>

  <div id="map"></div>

  <script type="text/javascript">
    var current_latitude = 5.3599517; // Latitude d'Abidjan
    var current_longitude = -4.0082563; // Longitude d'Abidjan
    var userMarker = null; // Marqueur pour la position actuelle
  </script>

  <script type="text/javascript">
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(success, fail);
    } else {
      console.log('Sorry, your browser does not support geolocation services');
      initMap();
    }

    function success(position) {
      document.getElementById('long').value = position.coords.longitude;
      document.getElementById('lat').value = position.coords.latitude;

      if (position.coords.longitude != "" && position.coords.latitude != "") {
        current_longitude = position.coords.longitude;
        current_latitude = position.coords.latitude;
      }
      initMap();
    }

    function fail() {
      console.log('Unable to get your location');
      initMap();
    }
  </script>

  <script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key') }}&libraries=places&callback=initMap" async defer></script>

  <script type="text/javascript">
    let map;
    let directionsService;
    let directionsRenderer;
    let userMarker = null;

    function initMap() {
      map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: current_latitude, lng: current_longitude },
        zoom: 12
      });

      // Ajouter un marqueur pour la position actuelle
      if (current_latitude && current_longitude) {
        userMarker = new google.maps.Marker({
          position: { lat: current_latitude, lng: current_longitude },
          map: map,
          title: 'Votre position actuelle',
          icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        });
      }

      const originInput = document.getElementById('origin-input');
      const destinationInput = document.getElementById('destination-input');

      const originAutocomplete = new google.maps.places.Autocomplete(originInput);
      originAutocomplete.setFields(['place_id', 'geometry', 'name']);

      const destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);
      destinationAutocomplete.setFields(['place_id', 'geometry', 'name']);

      directionsService = new google.maps.DirectionsService();
      directionsRenderer = new google.maps.DirectionsRenderer();
      directionsRenderer.setMap(map);

      // Ajouter la position actuelle comme première option dans l'autocomplétion
      originAutocomplete.addListener('place_changed', () => {
        const place = originAutocomplete.getPlace();
        if (!place.geometry) {
          alert("No details available for input: '" + place.name + "'");
          return;
        }
        document.getElementById('origin_latitude').value = place.geometry.location.lat();
        document.getElementById('origin_longitude').value = place.geometry.location.lng();
        calculateAndDisplayRoute();
      });

      destinationAutocomplete.addListener('place_changed', () => {
        const place = destinationAutocomplete.getPlace();
        if (!place.geometry) {
          alert("No details available for input: '" + place.name + "'");
          return;
        }
        document.getElementById('destination_latitude').value = place.geometry.location.lat();
        document.getElementById('destination_longitude').value = place.geometry.location.lng();
        calculateAndDisplayRoute();
      });
    }

    function calculateAndDisplayRoute() {
      const originLat = parseFloat(document.getElementById('origin_latitude').value);
      const originLng = parseFloat(document.getElementById('origin_longitude').value);
      const destinationLat = parseFloat(document.getElementById('destination_latitude').value);
      const destinationLng = parseFloat(document.getElementById('destination_longitude').value);

      if (isNaN(originLat) || isNaN(originLng) || isNaN(destinationLat) || isNaN(destinationLng)) {
        return;
      }

      directionsService.route({
        origin: { lat: originLat, lng: originLng },
        destination: { lat: destinationLat, lng: destinationLng },
        travelMode: google.maps.TravelMode.DRIVING
      }, (response, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
          directionsRenderer.setDirections(response);
          const route = response.routes[0].legs[0];
          alert(`Distance: ${route.distance.text}, Duration: ${route.duration.text}`);
        } else {
          window.alert('Directions request failed due to ' + status);
        }
      });
    }

    function openDrawer() {
      document.getElementById("drawer").style.left = "0";
    }

    function closeDrawer() {
      document.getElementById("drawer").style.left = "-250px";
    }

    function disableEnterKey(e) {
      if (e.keyCode === 13) {
        return false;
      }
    }

    $(document).ready(function() {
      $('.nav-link').on('click', function() {
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('show active');
        $($(this).attr('href')).addClass('show active');
      });
    });

    google.maps.event.addDomListener(window, 'load', initMap);
  </script>
</body>
</html>
