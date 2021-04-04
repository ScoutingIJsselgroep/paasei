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
use App\Models\User;
use Carbon\Carbon;
use Validator;
use DB;

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
					'message' => 'Alle eieren zijn gevonden! Jullie tijd: ' . format_seconds($finishedInSeconds) . '. Kijk na 5 april weer op de website om te zien of je in de top 3 gekomen bent!',
					'button' => 'scores',
				]);
			} else {
				return redirect('/')->with([
					'message' => 'Je kunt nu gaan zoeken. Je kunt de link in de mail het hele weekend gebruiken, we onthouden welke eieren je al had gevonden!',
				]);
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
		
		if($code == null) {
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
						'message' => 'Het laatste ei was al gevonden. Jullie tijd: ' . format_seconds($finishedInSeconds) . '. Kijk na 5 april weer op de website om te zien of je in de top 3 gekomen bent!',
						'button' => 'scores',
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
									'message' => 'Dit was het laatste ei! Jullie tijd: ' . format_seconds($finishedInSeconds) . '. Kijk na 5 april weer op de website om te zien of je in de top 3 gekomen bent!',
									'button' => 'scores',
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
			'message' => 'Het lijkt er op dat je de link in de mail nog niet hebt geopend, klik op de link in de mail en scan de code opnieuw, dan weten we straks of je een prijs gewonnen hebt!',
			'button' => 'signup',
		]);
	}

	public function score() {
		$clients = Client::join('client_points', 'client_points.client_id', '=', 'clients.id')
			->groupBy('clients.id')
			->groupBy('clients.name')
			->select(
				'clients.id',
				'clients.name',
				DB::raw('count(client_points.id) as points'),
				DB::raw('min(client_points.created_at) as first_point'),
				DB::raw('max(client_points.created_at) as last_point'),
			)
			->orderByRaw('count(client_points.id) DESC')
			->orderByRaw('max(client_points.created_at)');

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=score.csv');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$csv = fopen('php://output', 'w');
		fputs($csv, "\xEF\xBB\xBF");
		fputcsv($csv, ['id', 'name', 'points', 'first_point', 'last_point']);

		foreach($clients->get() as $client) {
			fputcsv($csv, $client->toArray());
		}
		fclose($csv);
		die();
	}


	public function admin(Request $request) {
		$clients = Client::leftJoin('client_points', 'client_points.client_id', '=', 'clients.id')
			->groupBy('clients.id')
			->select(
				'clients.id',
				DB::raw('min(clients.name) as name'),
				DB::raw('min(clients.email) as email'),
				DB::raw('min(clients.code) as code'),
				DB::raw('count(client_points.id) as points'),
				DB::raw('min(client_points.created_at) as first_point'),
				DB::raw('max(client_points.created_at) as last_point'),
			)
			->orderByRaw('count(client_points.id) DESC')
			->orderByRaw('max(client_points.created_at)');
		
		return view('clients/admin', [
			'clients' => $clients->get(),
		]);
	}
}
