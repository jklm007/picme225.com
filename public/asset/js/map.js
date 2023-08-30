
function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        mapTypeControl: false,
        zoomControl: true,
        center: {lat: current_latitude, lng: current_longitude},
        zoom: 12,
        styles : [{"elementType":"geometry","stylers":[{"color":"#f5f5f5"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#f5f5f5"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#bdbdbd"}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#e4e8e9"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#7de843"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#dadada"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#c9c9c9"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#9bd0e8"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]}]
    });

    new AutocompleteDirectionsHandler(map);
}
 function calculateAndDisplayRoute(directionsService, directionsDisplay) {

        directionsService.route({
          origin: document.getElementById('from_location').value,
          destination: document.getElementById('hos_address').value,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
/**

 * @constructor
 */

function AutocompleteDirectionsHandler(map) {
    this.map = map;
    this.originPlaceId = null;
    this.destinationPlaceId = null;
    this.travelMode = 'DRIVING';
    var originInput = document.getElementById('origin-input');
    var destinationInput = document.getElementById('destination-input');
    var Rental = document.getElementById('rental_location');
    var ambulance = document.getElementById('from_location');
    var originTrip = document.getElementById('o_trip_tab');
    var destinationTrip = document.getElementById('d_trip_tab');
    var modeSelector = document.getElementById('mode-selector');
    var originLatitude = document.getElementById('origin_latitude');
    var originLongitude = document.getElementById('origin_longitude');
    var destinationLatitude = document.getElementById('destination_latitude');
    var destinationLongitude = document.getElementById('destination_longitude');
    var ambfromLatitude = document.getElementById('amb_from_lat');
    var ambfromLongitude = document.getElementById('amb_from_lng');
  
    var polylineOptionsActual = new google.maps.Polyline({
            strokeColor: '#111',
            strokeOpacity: 0.8,
            strokeWeight: 4
    });

    this.directionsService = new google.maps.DirectionsService;
    this.directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: false, polylineOptions: polylineOptionsActual});
    this.directionsDisplay.setMap(map);

    var originAutocomplete = new google.maps.places.Autocomplete(
            originInput);
    var destinationAutocomplete = new google.maps.places.Autocomplete(
            destinationInput);
    var rentalAutocomplete = new google.maps.places.Autocomplete(
            Rental);
    var ambulanceAutocomplete = new google.maps.places.Autocomplete(
            ambulance);
    var trip_o_Autocomplete = new google.maps.places.Autocomplete(
            originTrip);
    var trip_d_Autocomplete = new google.maps.places.Autocomplete(
            destinationTrip);
   
    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: false, polylineOptions: polylineOptionsActual});
    directionsDisplay.setMap(map);
     var onChangeHandler = function() {
          calculateAndDisplayRoute(directionsService, directionsDisplay);
          var address = document.getElementById('hos_address').value;
    getLatitudeLongitude(showResult, address);
    return false;
        };
document.getElementById('hos_address').addEventListener ('change', onChangeHandler); 



function getLatitudeLongitude(callback, address) {
    // If adress is not supplied, use default value 'Ferrol, Galicia, Spain'
    address = address || 'Ferrol, Galicia, Spain';
    // Initialize the Geocoder
    geocoder = new google.maps.Geocoder();
    if (geocoder) {
        geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                callback(results[0]);
            }
        });
    }
}
function showResult(result) {
    document.getElementById('hospital_lat').value = result.geometry.location.lat();
   document.getElementById('hospital_lng').value = result.geometry.location.lng();    
}

     ambulanceAutocomplete.addListener('place_changed', function(event) {
        var place = ambulanceAutocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                    // window.alert("Autocomplete's returned place contains no geometry");
                    return;
            }
            ambfromLatitude.value = place.geometry.location.lat();
            ambfromLongitude.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                    query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    ambfromLatitude.value = results[0].geometry.location.lat();
                    ambfromLongitude.value = results[0].geometry.location.lng();
                }
            });
        }
        $.ajax({
            url: '/confirm/ride',
            dataType: 'json',
            type: 'GET',
            data: { latitude: ambfromLatitude.value, longitude: ambfromLongitude.value },
            success: function(data) {
                console.log('Accept', data);
               if(data.length > 0){ 
               $.each(data, function(key, value) {   
                $('#hos_address')
                .find('option')
                .remove()
                .end()
                .append("<option>Select option</option>")
                 .append($("<option></option>")
                            .attr("value",value.hospital_address)
                            .text(value.hospital_address)); 
           });
           }else{
              $('#hos_address')
                .find('option')
                .remove()
                .end()
                 .append("<option>Select option</option>");
            }
           }
        });
    });

    originAutocomplete.addListener('place_changed', function(event) {
        var place = originAutocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                    // window.alert("Autocomplete's returned place contains no geometry");
                    return;
            }
            originLatitude.value = place.geometry.location.lat();
            originLongitude.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                    query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    originLatitude.value = results[0].geometry.location.lat();
                    originLongitude.value = results[0].geometry.location.lng();
                }
            });
        }
    });


    destinationAutocomplete.addListener('place_changed', function(event) {
        var place = destinationAutocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                // window.alert("Autocomplete's returned place contains no geometry");
                return;
            }
            destinationLatitude.value = place.geometry.location.lat();
            destinationLongitude.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    destinationLatitude.value = results[0].geometry.location.lat();
                    destinationLongitude.value = results[0].geometry.location.lng();
                }
            });
        }
    });

    trip_o_Autocomplete.addListener('place_changed', function(event) {
        var place = trip_o_Autocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                    // window.alert("Autocomplete's returned place contains no geometry");
                    return;
            }
            trip_o_lat.value = place.geometry.location.lat();
            trip_o_lng.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                    query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    trip_o_lat.value = results[0].geometry.location.lat();
                    trip_o_lng.value = results[0].geometry.location.lng();
                }
            });
        }
    });


    trip_d_Autocomplete.addListener('place_changed', function(event) {
        var place = trip_d_Autocomplete.getPlace();

        if (place.hasOwnProperty('place_id')) {
            if (!place.geometry) {
                // window.alert("Autocomplete's returned place contains no geometry");
                return;
            }
            trip_d_lat.value = place.geometry.location.lat();
            trip_d_lng.value = place.geometry.location.lng();
        } else {
            service.textSearch({
                query: place.name
            }, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    trip_d_lat.value = results[0].geometry.location.lat();
                    trip_d_lng.value = results[0].geometry.location.lng();
                }
            });
        }
    });
     rentalAutocomplete.addListener('place_changed', function(event) {
        var place1 = rentalAutocomplete.getPlace();
        var lat1 = place1.geometry.location.lat();
        var lng1 = place1.geometry.location.lng();
        $("#rental_lat").val(lat1);
        $("#rental_lng").val(lng1);
        //alert(Rental);
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 16,
            center: {lat: lat1, lng: lng1}
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: {lat: lat1, lng: lng1}
        });
        marker.addListener('click', toggleBounce);
    
        
       
    });

    this.setupPlaceChangedListener(originAutocomplete, 'ORIG');
    this.setupPlaceChangedListener(destinationAutocomplete, 'DEST');
   // this.setupPlaceChangedListener(ambulanceAutocomplete, 'ORIG');
   // this.setupPlaceChangedListener(ambulanceAutocomplete, 'DEST');
}

// Sets a listener on a radio button to change the filter type on Places
// Autocomplete.

AutocompleteDirectionsHandler.prototype.setupPlaceChangedListener = function(autocomplete, mode) {
    var me = this;
    autocomplete.bindTo('bounds', this.map);
    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.place_id) {
            // window.alert("Please select an option from the dropdown list.");
            return;
        }
        if (mode === 'ORIG') {
            me.originPlaceId = place.place_id;
        } else {
            me.destinationPlaceId = place.place_id;
        }
        me.route();
    });

};

AutocompleteDirectionsHandler.prototype.route = function() {
    if (!this.originPlaceId || !this.destinationPlaceId) {
        return;
    }
    
    var me = this;

    this.directionsService.route({
        origin: {'placeId': this.originPlaceId},
        destination: {'placeId': this.destinationPlaceId},
        travelMode: this.travelMode
    }, function(response, status) {
        if (status === 'OK') {
            me.directionsDisplay.setDirections(response);
        } else {
            // window.alert('Directions request failed due to ' + status);
        }
    });
};