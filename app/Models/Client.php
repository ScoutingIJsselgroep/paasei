<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Client
 *
 * @author Dennis
 */
class Client extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
		'email',
    ];
	
	public function clientPoints() {
		return $this->hasMany(ClientPoint::class);
	}

    public function availablePoints() {
        $clientPoints = $this->clientPoints()->orderBy('created_at')->with('point')->get();
        $this->setRelation('clientPoints', $clientPoints);

        $points = Point::where('public', '=', 1)->get();
        $new_available_ids = [];
        
        foreach($clientPoints as $clientPoint) {
            $newPoints = Point::where('public', '=', 0)->whereNotIn('id', $new_available_ids)->orderByRaw('
				ASIN(SQRT(
				POWER(SIN((points.lat - abs(' . $clientPoint->point->lat . ')) * pi()/180 / 2),
				2) + COS(points.lat * pi()/180 ) * COS(abs(' . $clientPoint->point->lat . ') *
				pi()/180) * POWER(SIN((points.lng - ' . $clientPoint->point->lng . ') *
				pi()/180 / 2), 2) ))
			')->take(2)->get();
            foreach($newPoints as $point) {
                $new_available_ids[] = $point->id;
                $points->push($point);
            }
            if($points->count() < 2) {
                break;
            }
        }

        foreach($points as $point) {
            if($this->clientPoints->pluck('point_id')->contains($point->id)) {
                $point->found = true;
            }
        }

        return $points;
    }
}
