
    /**
     * Helper for fallback distance calculation (Haversine)
     */
    private function calculateFallbackDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Estimated Fare for Delivery Service (Multi-stop)
     */
    public function estimated_fare_delivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'stops' => 'required|array|min:1',
            'stops.*.latitude' => 'required|numeric',
            'stops.*.longitude' => 'required|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $service_type = ServiceType::findOrFail($request->service_type);
            
            // Prepare OSRM Waypoints (Source -> Stops)
            // Source is handled as first point in getOSRMDistance
            // Last point in stops is destination
            
            $stops = $request->stops;
            $destination = array_pop($stops); // Last stop is destination
            
            // Format remaining stops as waypoints for OSRM
            $waypoints = [];
            foreach ($stops as $stop) {
                $waypoints[] = ['lat' => $stop['latitude'], 'lng' => $stop['longitude']];
            }

            try {
                $routeData = $this->getOSRMDistance(
                    $request->s_latitude,
                    $request->s_longitude,
                    $destination['latitude'],
                    $destination['longitude'],
                    $waypoints
                );
                $kilometer = round($routeData['distance'] / 1000, 2);
                $seconds = $routeData['duration'];
            } catch (\Exception $e) {
                // Fallback: Calculate simple sum of segments
                $kilometer = 0;
                $prevLat = $request->s_latitude;
                $prevLng = $request->s_longitude;
                
                // Add back the popped destination to loop properly
                $allPoints = $request->stops; 
                
                foreach ($allPoints as $point) {
                    $kilometer += $this->calculateFallbackDistance($prevLat, $prevLng, $point['latitude'], $point['longitude']);
                    $prevLat = $point['latitude'];
                    $prevLng = $point['longitude'];
                }
                $seconds = ($kilometer / 30) * 3600; // Slower average for delivery
            }

            $price = $service_type->fixed;
            $minutes = round($seconds / 60, 2);
            
            // Standard constraints
            if ($service_type->calculator == 'MIN') {
                $price += $service_type->minute * $minutes;
            } else if ($service_type->calculator == 'HOUR') {
                $price += $service_type->minute * 60;
            } else if ($service_type->calculator == 'DISTANCE') {
                $price += ($kilometer * $service_type->price);
            } else if ($service_type->calculator == 'DISTANCEMIN') {
                $price += ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
            } else if ($service_type->calculator == 'DISTANCEHOUR') {
                $price += ($kilometer * $service_type->price) + ($service_type->minute * $minutes * 60);
            } else {
                $price += ($kilometer * $service_type->price);
            }

            // Per stop Fee (Setting)
            $stopFee = (float) Setting::get('delivery_stop_fee', 0);
            $price += $stopFee * count($request->stops);

            // Taxes
            $tax_percentage = (float) Setting::get('tax_percentage', 0);
            $tax_price = ($tax_percentage / 100) * $price;
            $total = $price + $tax_price;

            return response()->json([
                'estimated_fare' => round($total, 2),
                'distance' => $kilometer,
                'time' => gmdate("H:i:s", $seconds),
                'stop_count' => count($request->stops),
                'tax_price' => $tax_price,
                'base_price' => $service_type->fixed,
                'wallet_balance' => Auth::user()->wallet_balance
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send Delivery Request
     */
    public function send_delivery_request(Request $request)
    {
         $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            's_address' => 'required',
            'service_type' => 'required|numeric|exists:service_types,id',
            'stops' => 'required|array|min:1', // Destination is included here
            'payment_mode' => 'required',
            'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,'.Auth::user()->id],
        ]);

        try {
            // Re-use logic from send_request but adapted for delivery
            // We'll create a UserRequest with special metadata
            
            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
            $UserRequest->user_id = Auth::user()->id;
            $UserRequest->provider_id = 0; // Broadcast
            $UserRequest->current_provider_id = 0;
            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->payment_mode = $request->payment_mode;
            $UserRequest->status = 'SEARCHING';
            
            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;
            $UserRequest->s_address = $request->s_address;
            
            // Use last stop as main destination
            $stops = $request->stops;
            $destination = end($stops);
            
            $UserRequest->d_latitude = $destination['latitude'];
            $UserRequest->d_longitude = $destination['longitude'];
            $UserRequest->d_address = $destination['address'] ?? 'Destination';
            
            // Save Delivery Metadata
            $deliveryMeta = [
                'sender_name' => $request->sender_name ?? Auth::user()->first_name,
                'sender_phone' => $request->sender_phone ?? Auth::user()->mobile,
                'package_description' => $request->package_description ?? '',
                'stops_count' => count($stops)
            ];
            $UserRequest->delivery_meta = json_encode($deliveryMeta);
            $UserRequest->stops_data = json_encode($stops);
            
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->use_wallet = $request->use_wallet ? 1 : 0;
            
            // Calculate Estimation again for verification (optional but safer)
            // ... (Simplified for this snippet, assume price sent or recalculated)
            
            $UserRequest->save();

            // Find Providers and Notify (Reuse SendPushNotification logic)
            // ...
            
            return response()->json([
                'message' => 'Delivery Request Sent Successfully',
                'request_id' => $UserRequest->id,
                'current_provider' => $UserRequest->current_provider_id,
            ]);

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }
