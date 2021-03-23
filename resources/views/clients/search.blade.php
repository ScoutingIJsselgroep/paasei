@extends('layouts.master')

@section('content')
	<div id="map" class="full-map"></div>
	<div id="fullScreen" class="button"><i class="fas fa-compress"></i><i class="fas fa-expand"></i></div>

	<div id="scanButton" class="button"><i class="fas fa-qrcode"></i></div>
	<a href="/" id="refreshButton" class="button"><i class="fas fa-redo-alt"></i></a>

	<div id="qrScanner" class="overlay">
		<video muted playsinline autoplay></video>
		<div class="button close"><i class="fa fa-times"></i></div>
		<form action="{{ route('clients.check') }}" method="POST" id="checkForm">
			@csrf
			<input id="code" name="code" type="hidden">
		</form>
	</div>
	<div id="status" class="overlay">
		<div id="load" class="text-center"><i class="fas fa-spinner fa-pulse"></i></div>
		<div class="button close"><i class="fa fa-times"></i></div>
		<div id="message"></div>
	</div>
@endsection

@section('scripts')
<script type="text/javascript">
	var map = L.map('map').setView([52.20142, 6.20114], 13);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '<a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);

	L.SVGIcon = L.DivIcon.extend({
		options: {
			className: 'base-icon',
			html: '',
			color: '#ff0000',
			// borderColor: '#bf0000',
			iconSize: [34, 50],
			shadowSize: [52, 50]
		},
		initialize: function(options) {
			options = L.Util.setOptions(this, options);
			if (!options.borderColor) { 
				options.borderColor = options.color;
			}
		},
		createIcon: function(el, old) {
			return $('<div class="svg-marker ' + this.options.className + '" style="color: ' + this.options.borderColor + '; margin-left: -17px; margin-top: -50px; width: 34px; height: 50px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="34px" height="50px" viewBox="-10 -10 330 490" xml:space="preserve"><g><path fill="' + this.options.color + '" stroke="' + this.options.borderColor + '" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" stroke-alignment="center" d="M159.75,0.401C71.522,0.401,0,71.923,0,160.151c0,41.685,19.502,80,69.75,129.75   c50.5,50,84.725,142.523,90,190c5.292-47.477,39.623-140,90.281-190C300.438,240.15,320,201.836,320,160.151   C320,71.923,248.254,0.401,159.75,0.401z"/></g></svg>' + this.options.html + '</div>')[0];
		},
		createShadow(el, old) {
			return $('<div class="svg-marker-shadow ' + this.options.className + '-shadow" style="margin-left: -17px; margin-top: -50px; width: 52px; height: 50px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="52px" height="50px" viewBox="-10 -10 510 490" xml:space="preserve"><filter id="blurShadow"><feGaussianBlur in="SourceGraphic" stdDeviation="15" /></filter><g transform="rotate(10 -50 -100) translate(300 194) skewX(-40) scale(1 0.5)"><path filter="url(#blurShadow)" fill="rgba(0,0,0,0.5)" d="M159.75,0.401C71.522,0.401,0,71.923,0,160.151c0,41.685,19.502,80,69.75,129.75   c50.5,50,84.725,142.523,90,190c5.292-47.477,39.623-140,90.281-190C300.438,240.15,320,201.836,320,160.151   C320,71.923,248.254,0.401,159.75,0.401z"/></g></svg></div>')[0];
		}
	});
	L.svgIcon = function(options) {
		return new L.SVGIcon(options);
	};

	L.EiIcon = L.DivIcon.extend({
		options: {
			className: 'base-icon',
			html: '',
			color: '#ff0000',
			// borderColor: '#bf0000',
			iconSize: [34, 58],
			shadowSize: [52, 50]
		},
		initialize: function(options) {
			options = L.Util.setOptions(this, options);
			if (!options.borderColor) { 
				options.borderColor = options.color;
			}
		},
		createIcon: function(el, old) {
			// return $('<div class="svg-marker ' + this.options.className + '" style="color: ' + this.options.borderColor + '; margin-left: -17px; margin-top: -50px; width: 34px; height: 50px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="34px" height="50px" viewBox="-10 -10 330 490" xml:space="preserve"><g><path fill="' + this.options.color + '" stroke="' + this.options.borderColor + '" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" stroke-alignment="center" d="M159.75,0.401C71.522,0.401,0,71.923,0,160.151c0,41.685,19.502,80,69.75,129.75   c50.5,50,84.725,142.523,90,190c5.292-47.477,39.623-140,90.281-190C300.438,240.15,320,201.836,320,160.151   C320,71.923,248.254,0.401,159.75,0.401z"/></g></svg>' + this.options.html + '</div>')[0];

			return $('<div class="svg-marker ei-marker ' + this.options.className + '" style="color: ' + this.options.borderColor + '; margin-left: -17px; margin-top: -58px; width: 34px; height: 58px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="34px" height="60px" viewBox="-10 -10 330 590"><g><path fill="' + this.options.color + '" stroke="' + this.options.borderColor + '" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" stroke-alignment="center" d="M316.176,226.464c-5.299-31.656-15.027-68.599-30.025-103.412c-3.377-7.105-6.891-14.065-10.49-20.85   C253.066,59.615,211.594,4.535,161.034,3.778c-0.177,0-0.354-0.006-0.53-0.005c-0.173-0.001-0.347,0.005-0.521,0.005   c-50.076,0.755-91.152,55.837-113.529,98.425c-14.091,26.813-26.849,56.357-35.187,86.841c-2.639,10.349-4.852,20.474-6.656,30.174   c-5.284,31.967-4.745,64.312,5.034,95.055c9.524,26.035,25.395,50.083,45.285,71.366c16.545,17.159,34.585,33.167,49.435,51.542   c22.751,28.153,38.072,61.377,47.219,96.251c4.026,13.746,7.032,28.286,8.92,43.84c2.149-17.873,5.761-34.406,10.686-49.938   c9.387-32.643,24.368-63.646,45.998-90.153c7.96-9.755,16.829-18.84,25.897-27.786c6.148-6.909,12.554-13.817,19.202-20.782   C304.399,344.495,329.42,288.012,316.176,226.464z"/></g></svg>' + this.options.html + '</div>')[0];

		},
		createShadow(el, old) {
			return $('<div class="svg-marker-shadow ei-marker-shadow ' + this.options.className + '-shadow" style="margin-left: -17px; margin-top: -50px; width: 52px; height: 50px;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="52px" height="50px" viewBox="-10 -10 510 490" xml:space="preserve"><filter id="blurShadow"><feGaussianBlur in="SourceGraphic" stdDeviation="15" /></filter><g transform="rotate(10 -50 -100) translate(300 194) skewX(-40) scale(1 0.5)"><path filter="url(#blurShadow)" fill="rgba(0,0,0,0.5)" d="M159.75,0.401C71.522,0.401,0,71.923,0,160.151c0,41.685,19.502,80,69.75,129.75   c50.5,50,84.725,142.523,90,190c5.292-47.477,39.623-140,90.281-190C300.438,240.15,320,201.836,320,160.151   C320,71.923,248.254,0.401,159.75,0.401z"/></g></svg></div>')[0];
		}
	});
	L.eiIcon = function(options) {
		return new L.EiIcon(options);
	};
	
	var markerIcon = L.icon({
		iconUrl: 'img/marker.png',
		iconSize: [30, 43],
		iconAnchor: [15, 43],
		
		shadowUrl: 'img/marker-shadow.png',
		shadowSize: [57, 38], // size of the shadow
		shadowAnchor: [15, 34],  // the same for the shadow
		
		popupAnchor: [0, -43] // point from which the popup should open relative to the iconAnchor
	});
	
	var markerStepsIcon = L.icon({
		iconUrl: 'img/marker-steps.png',
		iconSize: [42, 45],
		iconAnchor: [21, 45],
		
		shadowUrl: 'img/marker-shadow.png',
		shadowSize: [57, 38], // size of the shadow
		shadowAnchor: [15, 34],  // the same for the shadow
		
		popupAnchor: [0, -43] // point from which the popup should open relative to the iconAnchor
	});
	
	var foundCodes = [];
	var foundLine = L.polyline([], {
		color: '#ee66cc',
		weight: 2,
		dashArray: '3 4'
	}).addTo(map);
	var searchCode = false;
	
	var searchLine = L.polyline([], {
		color: '#ff6600',
		weight: 2,
		dashArray: '3 4'
	}).addTo(map);
	
	
	var positionHistory = [];
	var positionHistoryLine = L.polyline(positionHistory, {
		color: '#cc0000',
		weight: 2
	}).addTo(map);
	
	@foreach($client->availablePoints() as $point)
		marker = L.marker([{{ $point->lat }}, {{ $point->lng }}], {
			icon: L.eiIcon({
				html: '<i class="{{ $point->icon }}"></i>',
				color: '{{ $point->color }}',
				borderColor: '{{ $point->second_color }}',
				draggable: true
			}),
			point_id: '{{ $point->id }}',
			opacity: '{{ $point->found ? 0.3 : 1.0 }}'
		}).addTo(map).on('click', function() {
			// todo
		});
	@endforeach

	@if($client && $client->route)
		@php
			$searchPoint = $client->route->startPoint;
		@endphp
		
		@foreach($client->clientPoints as $clientPiont)
			foundCodes.push([{{ $clientPiont->point->lat }}, {{ $clientPiont->point->lng }}]);
			L.marker([{{ $clientPiont->point->lat }}, {{ $clientPiont->point->lng }}], {
				icon: markerIcon
			}).addTo(map);
			
			@php
				$searchPoint = $clientPiont->point->nextPoint;
			@endphp
		@endforeach
		foundLine.setLatLngs(foundCodes);
		
		@if($searchPoint)
			searchCode = [{{ $searchPoint->lat }}, {{ $searchPoint->lng }}];
			// weergeven als er wat weer te geven valt
			//searchMarker.setLatLng(searchCode).addTo(map);
			searchLine.setLatLngs([searchCode]);
		@endif
	@endif
	
	if (navigator.geolocation) {
		var positionAccuracy = L.circle([52.20142, 6.20114], {
			radius: 1,
			opacity: 0.8,
			color: '#dfe6fa',
			fill: true,
			fillColor: '#dfe6fa',
			fillOpacity: 0.3
		}).addTo(map);
		var positionMarker = L.marker([52.20142, 6.20114], {
			icon: markerStepsIcon
		}).addTo(map);

		function currentPosition(position) {
			positionAccuracy.setLatLng([position.coords.latitude, position.coords.longitude]).setRadius(position.coords.accuracy);
			positionMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
			if(!positionHistory.length || positionHistory[positionHistory.length - 1][0] !== position.coords.latitude || positionHistory[positionHistory.length - 1][1] !== position.coords.longitude) {
				positionHistory.push([position.coords.latitude, position.coords.longitude]);
				positionHistoryLine.setLatLngs(positionHistory);
				if(searchCode) {
					searchLine.setLatLngs([searchCode, [position.coords.latitude, position.coords.longitude]]);
				}
			}
		}
		navigator.geolocation.watchPosition(currentPosition);
	}
	
	@if(session()->has('message'))
	$('#status #message').text('{{ session()->get('message') }}');
	$('#status').addClass('active');
	@endif
</script>
<script type="module">
	import QrScanner from "./js/qr-scanner.js";
	QrScanner.WORKER_PATH = './js/qr-scanner-worker.min.js';

	const video = $('#qrScanner video')[0];
	const scanner = new QrScanner(video, result => setResult(result));

	function setResult(result) {
		scanner.stop();
		//$('#qrScanner').removeClass('active');
		//$('#status').addClass('load');
		
		$('#checkForm #code').val(result);
		$('#checkForm').submit();
		
	}
	
	/*searchMarker.on('click', function() {
	    $('#status').removeClass('active');
		$('#qrScanner').addClass('active');
		scanner.start();
		map.panInside(this.getLatLng(), {
			paddingTopLeft: [25, window.innerWidth + 65],
			paddingBottomRight: [25, 5]
		});
	});*/
	
	$('#qrScanner .button.close').click(function() {
		scanner.stop();
		$('#qrScanner').removeClass('active');
	});
	
	$('#status .button.close').click(function() {
		$('#status').removeClass('active');
	});
	
	$('#scanButton').click(function() {
	    $('#status').removeClass('active');
		$('#qrScanner').addClass('active');
		scanner.start();
	});
	$('#login').on('shortclick', function() {
	    $('#status').removeClass('active');
		$('#qrScanner').addClass('active');
		scanner.start();
	});
	map.invalidateSize();
</script>
@endsection