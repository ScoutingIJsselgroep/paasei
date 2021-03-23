<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\ClientPoint;
use App\Models\Point;
use Carbon\Carbon;
use Validator;

/**
 * Description of ClientsController
 *
 * @author Dennis
 */

class ClientsController extends Controller {
	public function add(Request $request) {
		if(!in_array($request->ip(), ['85.145.6.41', '31.187.138.45', '185.94.230.117'])) {
			return abort(401);
		}
		$v = Validator::make($request->all(), [
			'name' => 'required',
			'email' => 'required|email|unique:\App\Models\Client,email',
		]);
        if ($v->fails()) {
            return [
				'errors' => $v->errors(),
			];
        }

		$client = new Client;
		$client->name = $request->name;
		$client->email = $request->email;
		$client->code = Str::random(15);
		if($client->save()) {
			return $client;
		} else {
			return [false];
		}
	}

	public function start(Request $request) {
		$client = Client::where('code', $request->code)->first();
		if($client) {
			$request->session()->put('client_id', $client->id);

			if($client->clientPoints->count() == Point::count()) {
				$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
				return redirect('/')->with([
					'message' => 'Dit was het laatste punt van de route. Jullie tijd: ' . format_seconds($finishedInSeconds),
				]);
			} else {
				if(Carbon::now()->lt('2021-04-02 09:00')) {
					return redirect('/')->with([
						'message' => 'Vanaf 02-04-2021 09:00 kun je beginnen met zoeken!',
					]);
				} else {
					return redirect('/')->with([
						'message' => 'Je kunt nu gaan zoeken!',
					]);
				}
			}
		} else {
			return redirect('/')->with([
				'message' => 'Er is iets mis gegaan',
			]);
		}
	}
	public function search(Request $request) {
		if($request->session()->has('client_id')) {
			$client = Client::find($request->session()->get('client_id'));
		} else {
			$client = new Client;	
		}

		return view('clients.search', [
			'client' => $client,
		]);
	}

	public function check(Request $request, string $code = null) {
		sleep(1);
		
		if($code = null) {
			$code = trim(str_replace(url('c'), '', $request->get('code', '')), '/');
		}

		if($request->session()->has('client_id')) {
			$client = Client::find($request->session()->get('client_id'));
			if($client) {
				$availablePoints = $client->availablePoints();
				$totalPointCount = Point::count();

				if($client->clientPoints->count() == $totalPointCount) {
					$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
					return redirect('/')->with([
						'message' => 'Het laatste ei was al gevonden. Jullie tijd: ' . format_seconds($finishedInSeconds),
					]);
				}

				foreach($availablePoints as $availablePoint) {
					if($availablePoint->code && $availablePoint->code == $code) {
						if($availablePoint->found) {
							return redirect('/')->with([
								'message' => 'Deze code heb je al een keer gevonden, zoek nieuwe eieren op de andere plekken die je ziet op de kaart',
							]);
						} else {
							$clientPoint = new ClientPoint;
							$clientPoint->client_id = $client->id;
							$clientPoint->point_id = $availablePoint->id;
							$clientPoint->save();

							$availablePoints = $client->availablePoints();
							if($client->clientPoints->count() == $totalPointCount) {
								$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
								return redirect('/')->with([
									'message' => 'Dit was het laatste ei! Jullie tijd: ' . format_seconds($finishedInSeconds),
								]);
							}
							if($availablePoints->count() == $totalPointCount) {
								return redirect('/')->with([
									'message' => 'Nog een ei gevonden! Nog ' . ($totalPointCount - $client->clientPoints->count()) . ' te gaan',
								]);
							}
							
							return redirect('/')->with([
								'message' => 'Hoera! Een ei gevonden, nu kun je ook op nieuwe plekken zoeken!',
							]);
						}
					}
				}
				return redirect('/')->with([
					'message' => 'Deze code zoeken we (nog) niet'
				]);
			}
		}
		return redirect('/')->with([
			'message' => 'Het lijkt er op dat je de link in de mail nog niet hebt geopend',
			// todo, button toevoegen
			'button' => 'Ik heb me nog niet aangemeld'
		]);
	}

	public function admin(Request $request) {
		if($request->isMethod('post') && $request->add) {
			$client = new Client;
			$client->fill($request->all());
			$client->route_id = $request->route_id;
			$client->code = Str::random(15);
			$client->save();
			
			return redirect()->back();
		}
		
		if($request->isMethod('post') && $request->edit) {
			$client = Client::find($request->id);
			if($client) {
				$client->fill($request->all());
				if($client->clientPoints()->count() == 0) {
					$client->route_id = $request->route_id;
				}
				$client->save();
			}
			return redirect()->back();
		}
		
		if($request->isMethod('post') && $request->get) {
			$client = Client::find($request->id);
			$time = 'Nog geen route';
			
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
					if($client->clientPoints()->exists()) {
						$busyInSeconds = Carbon::now()->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
						$time = 'Bezig: ' . format_seconds($busyInSeconds);
					} else {
						$time = 'Eerste punt nog niet gevonden';
					}
				} else {
					$finishedInSeconds = Carbon::parse($client->clientPoints()->max('created_at'))->timestamp - Carbon::parse($client->clientPoints()->min('created_at'))->timestamp;
					$time = 'Finish: ' . format_seconds($finishedInSeconds);
				}
			}	
			return [
				'points' => $client->clientPoints,
				'locations' => $client->clientLocations,
				'time' => $time,
			];
		}
	}
}
