<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Backward-compatible facade.
 * All existing routes remain functional while migration to domain controllers is underway.
 * Remove once all route references have been updated to domain-scoped controllers.
 */
class UserApiController extends Controller
{
    public function signin(Request $request) { return (new UserAuthController())->signin($request); }
    public function signup(Request $request) { return (new UserAuthController())->signup($request); }
    public function checkMobileExists(Request $request) { return (new UserAuthController())->checkMobileExists($request); }
    public function logout(Request $request) { return (new UserAuthController())->logout($request); }
    public function change_password(Request $request) { return (new UserAuthController())->change_password($request); }
    public function forgot_password(Request $request) { return (new UserAuthController())->forgot_password($request); }
    public function reset_password(Request $request) { return (new UserAuthController())->reset_password($request); }
    public function verify(Request $request) { return (new UserAuthController())->verify($request); }
    public function details(Request $request) { return (new UserProfileController())->details($request); }
    public function update_profile(Request $request) { return (new UserProfileController())->update_profile($request); }
    public function update_location(Request $request) { return (new UserProfileController())->update_location($request); }
    public function updateKyc(Request $request) { return (new UserProfileController())->updateKyc($request); }
    public function getSubscriptionPlans(Request $request) { return (new UserSubscriptionController())->getSubscriptionPlans($request); }
    public function getSubscriptionStatus(Request $request) { return (new UserSubscriptionController())->getSubscriptionStatus($request); }
    public function purchaseSubscription(Request $request) { return (new UserSubscriptionController())->purchaseSubscription($request); }
    public function getServiceTypes(Request $request) { return (new UserServiceController())->getServiceTypes($request); }
    public function _normalizeVariant(Request $request) { return (new UserServiceController())->_normalizeVariant($request); }
    public function services(Request $request) { return (new UserServiceController())->services($request); }
    public function Hospital_based_location(Request $request) { return (new UserServiceController())->Hospital_based_location($request); }
    public function getPdpStops(Request $request) { return (new UserServiceController())->getPdpStops($request); }
    public function getPdpRoutes(Request $request) { return (new UserServiceController())->getPdpRoutes($request); }
    public function getNearbyStops(Request $request) { return (new UserServiceController())->getNearbyStops($request); }
    public function validateStopLocation(Request $request) { return (new UserServiceController())->validateStopLocation($request); }
    public function estimated_fare(Request $request) { return (new UserServiceController())->estimated_fare($request); }
    public function estimated_fare_shared(Request $request) { return (new UserServiceController())->estimated_fare_shared($request); }
    public function pricing_logic(Request $request) { return (new UserServiceController())->pricing_logic($request); }
    public function rental_package(Request $request) { return (new UserServiceController())->rental_package($request); }
    public function estimate_rental_fare(Request $request) { return (new UserServiceController())->estimate_rental_fare($request); }
    public function rental_service(Request $request) { return (new UserServiceController())->rental_service($request); }
    public function estimated_fare_delivery(Request $request) { return (new UserDeliveryController())->estimated_fare_delivery($request); }
    public function send_delivery_request(Request $request) { return (new UserDeliveryController())->send_delivery_request($request); }
    public function send_request(Request $request) { return (new UserRideController())->send_request($request); }
    public function cancel_request(Request $request) { return (new UserRideController())->cancel_request($request); }
    public function request_status_check(Request $request) { return (new UserRideController())->request_status_check($request); }
    public function rate_provider(Request $request) { return (new UserRideController())->rate_provider($request); }
    public function modifiy_request(Request $request) { return (new UserRideController())->modifiy_request($request); }
    public function show_providers(Request $request) { return (new UserRideController())->show_providers($request); }
    public function confirmCashPayment(Request $request) { return (new UserRideController())->confirmCashPayment($request); }
    public function trips(Request $request) { return (new UserHistoryController())->trips($request); }
    public function trip_details(Request $request) { return (new UserHistoryController())->trip_details($request); }
    public function upcoming_trips(Request $request) { return (new UserHistoryController())->upcoming_trips($request); }
    public function upcoming_trip_details(Request $request) { return (new UserHistoryController())->upcoming_trip_details($request); }
    public function promocodes(Request $request) { return (new UserPromoController())->promocodes($request); }
    public function add_promocode(Request $request) { return (new UserPromoController())->add_promocode($request); }
    public function help_details(Request $request) { return (new UserPromoController())->help_details($request); }
    public function wallet_passbook(Request $request) { return (new UserPromoController())->wallet_passbook($request); }
    public function promo_passbook(Request $request) { return (new UserPromoController())->promo_passbook($request); }
}