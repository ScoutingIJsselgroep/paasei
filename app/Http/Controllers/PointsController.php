<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Point;
use App\Models\Client;

class PointsController extends Controller {
	public function admin(Request $request) {
		
		if($request->isMethod('post') && $request->add) {
			$point = new Point;
			$point->fill($request->all());
			$point->save();
			
			return redirect()->back()->with([
				'mapCenter' => [
					$request->lat,
					$request->lng
				]
			]);
		} else if($request->isMethod('post') && $request->edit) {
			$point = Point::find($request->point_id);
			
			if(!$point) {
				return redirect()->back()->with([
					'message' => 'Punt niet gevonden',
				]);
			}
			if($request->code !== null && Point::where('code', '=', $request->code)->where('id', '<>', $point->id)->exists()) {
				return redirect()->back()->with([
					'mapCenter' => [
						$request->lat,
						$request->lng
					],
					'message' => 'QR code is al in gebruik',
				]);
			}
			
			$point->fill($request->all());
			$point->code = trim(str_replace(url('c'), '', $request->get('code', '')), '/');
			$point->save();
			
			return redirect()->back()->with([
				'mapCenter' => [
					$request->lat,
					$request->lng
				]
			]);
		} else if($request->isMethod('post') && $request->delete) {
			$point = Point::find($request->point_id);
			if($point) {
				$point->delete();
			}
			return redirect()->back();
		}
		
		return view('points.admin', [
			'points' => Point::all(),
			// 'clients' => Client::with('clientPoints')->get(),
			'mapFit' => !$request->session()->has('mapCenter'),
			'mapCenter' => $request->session()->get('mapCenter', [51.75294164, 5.89340866]),
		]);
	}
}
