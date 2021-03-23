<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientPoint;
use App\Models\ClientLocation;
use Carbon\Carbon;

class PointsXController extends Controller {
	public function checkCode(Request $request) {
		sleep(1);
		
		if($request->session()->has('client_id')) {
			$client = Client::find($request->session()->get('client_id'));
			if($client->route) {
				$searchPoint = $client->route->startPoint;
				
				foreach($client->clientPoints as $clientPiont) {
					if($clientPiont->point->code == $request->code) {
						return [
							'client_id' => $client->id,
							'error' => 'Deze code heb je al een keer gevonden',
						];
					}
					$searchPoint = $clientPiont->point->nextPoint;
				}
				
				if($searchPoint && $searchPoint->code == $request->code) {
					$clientPoint = new ClientPoint;
					$clientPoint->client_id = $client->id;
					$clientPoint->point_id = $searchPoint->id;
					$clientPoint->save();
					
					if($searchPoint->nextPoint) {
						return [
							'client_id' => $client->id,
							'found' => [
								'lat' => $searchPoint->lat,
								'lng' => $searchPoint->lng,
							],
							'search' => [
								'lat' => $searchPoint->nextPoint->lat,
								'lng' => $searchPoint->nextPoint->lng,
							]
						];
					} else {
						$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
						
						return [
							'client_id' => $client->id,
							'found' => [
								'lat' => $searchPoint->lat,
								'lng' => $searchPoint->lng,
							],
							'message' => 'Dit was het laatste punt van de route. Jullie tijd: ' . format_seconds($finishedInSeconds),
						];
					}
				}
			}		
		} else {
			$client = Client::where('code', $request->code)->first();
			if($client) {
				$request->session()->put('client_id', $client->id);
				
				if($client->route && $client->route->startPoint) {
					
					$searchPoint = $client->route->startPoint;
				
					foreach($client->clientPoints as $clientPiont) {
						if($clientPiont->point->nextPoint) {
							$searchPoint = $clientPiont->point->nextPoint;
						} else {
							$searchPoint = null;
						}
					}
					if($searchPoint) {
						// todo route geven
						return [
							'client_id' => $client->id,
							'search' => [
								'lat' => $searchPoint->lat,
								'lng' => $searchPoint->lng,
							],
						];
					} else {
						$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
						return [
							'client_id' => $client->id,
							'message' => 'Dit was het laatste punt van de route. Jullie tijd: ' . format_seconds($finishedInSeconds),
						];
					}
				} else {
					return [
						'client_id' => $client->id,
						'error' => 'Er is nog geen startpunt bekend',
					];
				}
			}
		}
		
		return [
			'error' => 'Dit is niet een code die we zoeken',
		];
	}
	public function startCode(Request $request) {
		$client = Client::where('code', $request->code)->first();
		if($client) {
			$request->session()->put('client_id', $client->id);

			if($client->route && $client->route->startPoint) {

				$searchPoint = $client->route->startPoint;

				foreach($client->clientPoints as $clientPiont) {
					if($clientPiont->point->nextPoint) {
						$searchPoint = $clientPiont->point->nextPoint;
					} else {
						$searchPoint = null;
					}
				}
				if($searchPoint) {
					return redirect('/');
				} else {
					$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
					return redirect('/')->with([
						'message' => 'Dit was het laatste punt van de route. Jullie tijd: ' . format_seconds($finishedInSeconds),
					]);
				}
			} else {
				return redirect('/')->with([
					'message' => 'Er is nog geen startpunt bekend',
				]);
			}
		}
	}
	public function updateLocation(Request $request) {
		// client location aanmaken
		if($request->session()->has('client_id')) {
			$client = Client::find($request->session()->get('client_id'));
			if($client->route) {
				$lastLocation = $client->clientLocations()->orderBy('id', 'desc')->first();
				if($lastLocation && $lastLocation->lat == $request->lat && $lastLocation->lng == $request->lng) {
					$lastLocation->touch();
				} else {
					$clientLocation = new ClientLocation;
					$clientLocation->client_id = $client->id;
					$clientLocation->fill($request->all());
					$clientLocation->save();
				}
			}
		}
		return $request->only(['lat', 'lng']) + ['t' => date('Y-m-d H:i:s')];
	}
}
