<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Aloha\Twilio\Twilio;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NjbusController extends Controller
{
    public function getWindow(Carbon $carbon)
    {
        $time = Carbon::createFromTime($carbon->hour, $carbon->minute, $carbon->second);
        switch (true) {
            case ($time->between(Carbon::createFromTime(15, 0, 0), Carbon::createFromTime(19, 30, 0))):
                $window = 0;
                break;
            case ($time->between(Carbon::createFromTime(6, 0, 0), Carbon::createFromTime(15, 0, 0))):
                $window = 1;
                break;
            case ($time->between(Carbon::createFromTime(19, 31, 0), Carbon::createFromTime(22, 0, 0))):
                $window = 2;
                break;
            case ($time->between(Carbon::createFromTime(22, 1, 0), Carbon::createFromTime(23, 59, 59)) || $time->between(Carbon::createFromTime(0, 0, 0), Carbon::createFromTime(0, 59, 59)) ):
                $window = 3;
                break;
            case ($time->between(Carbon::createFromTime(1, 0, 0), Carbon::createFromTime(3, 30, 0))):
                $window = 4;
                break;
            default:
                $window = null;
                break;
        }
        return $window;
    }

    public function getGate($bus, $window) {
        return config('routes.'.$bus.'.gate.'.$window);
    }

    public function incomingRequest(Request $request)
    {
        $twilio = new Twilio(config('twilio.sid'), config('twilio.token'), config('twilio.fromNumber'));
        $bus = str_replace([' ', '&'], ['', ''], strtoupper(trim($request->input('Body'))));
        $from = $request->input('From');
        if($bus == '') {
            return response('Missing input.', 417);
        }
        $window = self::getWindow(Carbon::now('America/New_York'));
        $gate = self::getGate($bus, $window);
        if(!$gate) {
            $twilio->message($from, "NJ Bus ".$bus." doesn't exist. Double check your input.");
            return response('Ok', 404);
        }
        if(!$window && $gate) {
            $twilio->message($from, 'NJ Bus '.$bus.' may not be running now. Gate assignments are posted downstairs in the north wing of PABT.');
            return response('', 428);
        }
        $twilio->message($from, "Bus ".$bus." is currently departing from gate ".$gate);
        return response('Ok', 200);
    }
}
