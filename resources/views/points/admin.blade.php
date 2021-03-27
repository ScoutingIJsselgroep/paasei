@extends('layouts.master')

@section('content')
	<div id="map" class="full-map"></div>
	<div id="fullScreen" class="button"><i class="fas fa-compress"></i><i class="fas fa-expand"></i></div>
	<div id="centerMap" class="button"><i class="far fa-compass"></i></div>
	<div id="addPoint" class="button"><i class="fas fa-map-marker-alt"></i></div>
	
	<div id="qrScanner" class="overlay">
		<video muted playsinline autoplay></video>
		<div class="button close"><i class="fa fa-times"></i></div>
	</div>
	<div id="status" class="overlay">
		<div id="load" class="text-center"><i class="fas fa-spinner fa-pulse"></i></div>
		<div class="button close"><i class="fa fa-times"></i></div>
		<div id="message"></div>
	</div>
	
	<form id="pointForm" class="overlay form-overlay" method="post">
		@csrf
		<div class="button close"><i class="fa fa-times"></i></div>
		<h2 class="add">Nieuw punt toevoegen</h2>
		<h2 class="edit">Punt bewerken</h2>

		<div class="form-group row">
			<label for="color" class="d-none d-sm-block col-sm-2 col-form-label">Icoon</label>
			<div class="col-sm-10">

				<label>
					<input type="radio" name="icon" value="icon-egg-1" id="icon-egg-1" checked>
					<i class="icon-egg-1"></i>
					&nbsp;
				</label>
				<label>
					<input type="radio" name="icon" value="icon-egg-2" id="icon-egg-2">
					<i class="icon-egg-2"></i>
					&nbsp;
				</label>
				<label>
					<input type="radio" name="icon" value="icon-egg-3" id="icon-egg-3">
					<i class="icon-egg-3"></i>
					&nbsp;
				</label>
				<label>
					<input type="radio" name="icon" value="icon-egg-4" id="icon-egg-4">
					<i class="icon-egg-4"></i>
					&nbsp;
				</label>
				<label>
					<input type="radio" name="icon" value="icon-egg-5" id="icon-egg-5">
					<i class="icon-egg-5"></i>
				</label>
			</div>
		</div>

		<div class="form-group row">
			<label for="color" class="d-none d-sm-block col-sm-2 col-form-label">Kleur</label>
			<div class="col-sm-10">
				<div class="input-group">
					<input type="text" class="form-control color" id="pointColor" name="color" value="#ff0000" required placeholder="Kleur" pattern="^#[A-Fa-f0-9]{6}$" readonly>
					<label for="pointColor" class="input-group-append">
					    <span class="input-group-text" id="pointColorPreview"><i class="fas fa-palette"></i></span>
					</label>
				</div>
			</div>
		</div>
		<div class="form-group row">
			<label for="second_color" class="d-none d-sm-block col-sm-2 col-form-label">Kleur ei</label>
			<div class="col-sm-10">
				<div class="input-group">
					<input type="text" class="form-control color" id="pointSecondColor" name="second_color" value="#0000ff" required placeholder="Kleur" pattern="^#[A-Fa-f0-9]{6}$" readonly>
					<label for="pointSecondColor" class="input-group-append">
					    <span class="input-group-text" id="pointSecondColorPreview"><i class="fas fa-palette"></i></span>
					</label>
				</div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="color" class="d-none d-sm-block col-sm-2 col-form-label">Openbaar</label>
			<div class="col-sm-10">
				<input type="hidden" name="public" value="0">
				<label>
					<input type="checkbox" name="public" value="1" id="public">
					Ei is zichtbaar als gestart wordt
				</label>
			</div>
		</div>

		<div class="form-group row">
			<label for="qr_code" class="d-none d-sm-block col-sm-2 col-form-label">QR code</label>
			<div class="col-sm-10">
				<div class="input-group">
					<label for="qr_code" class="input-group-prepend">
					    <span class="input-group-text" id="qr_code_label"><i class="fas fa-qrcode"></i></span>
					</label>
					<input type="text" class="form-control" id="qr_code" name="code" placeholder="QR code">
					<div class="input-group-append">
						<span class="btn btn-outline-secondary" id="qr_code_clear"><i class="fa fa-times"></i></span>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" id="lat" name="lat">
		<input type="hidden" id="lng" name="lng">


		<input type="hidden" id="org_lat">
		<input type="hidden" id="org_lng">
		<input type="hidden" id="point_id" name="point_id">

		<button class="btn btn-primary add" name="add" value="1"><i class="fas fa-save"></i> Toevoegen</button>

		<button class="btn btn-primary edit" name="edit" value="1"><i class="fas fa-save"></i> Bewerken</button>
		<button class="btn btn-primary edit" name="delete" value="1"><i class="far fa-trash-alt"></i> Verwijderen</button>
	</form>
@endsection

@section('scripts')
<script type="text/javascript">
	var centerMode = 'points';
	$('#centerMap').click(function() {
		if(centerMode == 'points') {
			if(positionHistory.length) {
				map.panTo(positionHistory[positionHistory.length - 1]);
			}
			centerMode = 'current';
		} else if(centerMode == 'current') {
			var latLngsAndCurrent = latLngs;
			if(positionHistory.length) {
				latLngsAndCurrent.push(positionHistory[positionHistory.length - 1]);
			}
			map.fitBounds(L.latLngBounds(latLngs));
			centerMode = 'pointsAndCurrent';
		} else if(centerMode == 'pointsAndCurrent') {
			map.fitBounds(L.latLngBounds(latLngs));
			centerMode = 'points';
		}
		
	});
	$('#addPoint').click(function() {
		if(editMarker != newMarker) {
			newMarker.addTo(map).setLatLng(map.getCenter()).bounce(1);
			map.panBy([0, $('form#pointForm').height() / -2]);
		}
		editMarker = newMarker;

		$('form#pointForm .edit').hide();
		$('form#pointForm .add').show();
		$('form#pointForm #icon-egg-' + Math.ceil(Math.random() * 5)).prop('checked', true);
		var color = randomColor(),
			borderColor = randomColor();
		$('form#pointForm #pointColor').val(color);
		$('form#pointForm #pointSecondColor').val(borderColor);
		$('form#pointForm #pointColor').colorpicker('setValue', color);
		$('form#pointForm #pointSecondColor').colorpicker('setValue', borderColor);

		$('form#pointForm #public').prop('checked', false);
		
		$('form#pointForm #lat').val(map.getCenter().lat);
		$('form#pointForm #lng').val(map.getCenter().lng);
		$('form#pointForm #org_lat').val('');
		$('form#pointForm #org_lng').val('');
		$('form#pointForm #point_id').val('');
		$('form#pointForm #qr_code').val('');

		$('form#pointForm').addClass('active');
	});
	$('form#pointForm .close').click(function() {
		$('form#pointForm').removeClass('active');
		if(editMarker == newMarker) {
			newMarker.remove();
		} else {
			editMarker.setLatLng([$('#org_lat').val(), $('#org_lng').val()]);
			editMarker.dragging.disable();
		}
		editMarker.stopBouncing();
		editMarker = null;
	});
	
	$('#pointColor').colorpicker({
		useAlpha: false,
		format: 'hex'
	}).on('colorpickerCreate colorpickerChange', function(e) {
		var borderColor = $(this).colorpicker('color').api('hsl');
		if(borderColor.api('lightness') < 50) {
			borderColor._color.color[2] += (100 - borderColor._color.color[2]) * 0.25;
		} else {
			borderColor._color.color[2] -= borderColor._color.color[2] * 0.25;
		}
		
        $('#pointColorPreview').css({
			'background-color': $(this).colorpicker('getValue'),
			'border-color': borderColor.toHexString(),
			'color': borderColor.toHexString()
		});
		if(editMarker) {
			editMarker.setIcon(L.eiIcon({
				color: $('#pointColor').val(),
				borderColor: $('#pointSecondColor').val(),
				html: '<i class="' + $('form#pointForm input[name=icon]:checked').val() + '"></i>'
			}));
		}
	});
	$('#pointSecondColor').colorpicker({
		useAlpha: false,
		format: 'hex'
	}).on('colorpickerCreate colorpickerChange', function(e) {
		var borderColor = $(this).colorpicker('color').api('hsl');
		if(borderColor.api('lightness') < 50) {
			borderColor._color.color[2] += (100 - borderColor._color.color[2]) * 0.25;
		} else {
			borderColor._color.color[2] -= borderColor._color.color[2] * 0.25;
		}
		
        $('#pointSecondColorPreview').css({
			'background-color': $(this).colorpicker('getValue'),
			'border-color': borderColor.toHexString(),
			'color': borderColor.toHexString()
		});
		if(editMarker) {
			editMarker.setIcon(L.eiIcon({
				color: $('#pointColor').val(),
				borderColor: $('#pointSecondColor').val(),
				html: '<i class="' + $('form#pointForm input[name=icon]:checked').val() + '"></i>'
			}));
		}
	});
	$('form#pointForm input[name=icon]').on('click change', function() {
		editMarker.setIcon(L.eiIcon({
			color: $('#pointColor').val(),
			borderColor: $('#pointSecondColor').val(),
			html: '<i class="' + $('form#pointForm input[name=icon]:checked').val() + '"></i>'
		}));
	});
	
	var map = L.map('map'),
		storedPosition = false;
	if(localStorage.getItem('zoom')) {
		storedPosition = true;
		map.setView([localStorage.getItem('lat'), localStorage.getItem('lng')], localStorage.getItem('zoom'));
	} else {
		map.setView({!! json_encode($mapCenter) !!}, 13);
	}
	
	map.on('moveend', function() {
		localStorage.setItem('lat', map.getCenter().lat);
		localStorage.setItem('lng', map.getCenter().lng);
		localStorage.setItem('zoom', map.getZoom());
	});

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

	var newMarker = L.marker([52.20142, 6.20114], {
		icon: L.eiIcon({
			color: '#ff0000',
			borderColor: '#0000ff',
			html: '<i class="icon-egg-1"></i>'
		}),
		draggable: true
	}).on('dragend', function() {
		$('#lat').val(this.getLatLng().lat);
		$('#lng').val(this.getLatLng().lng);
	}).on('drag', function() {
		
	});
	
	var positionHistory = [];
	if (navigator.geolocation) {
		var positionAccuracy = L.circle([52.20142, 6.20114], {
			radius: 1,
			opacity: 0.8,
			color: '#dfe6fa',
			fill: true,
			fillColor: '#dfe6fa',
			fillOpacity: 0.3
		}).addTo(map);

		function currentPosition(position) {
			positionAccuracy.setLatLng([position.coords.latitude, position.coords.longitude]).setRadius(position.coords.accuracy);
			if(!positionHistory.length || positionHistory[positionHistory.length - 1][0] !== position.coords.latitude || positionHistory[positionHistory.length - 1][1] !== position.coords.longitude) {
				positionHistory.push([position.coords.latitude, position.coords.longitude]);
			}
		}
		navigator.geolocation.watchPosition(currentPosition);
	}
	
	var editMarker
		markers = [],
		latLngs = [];
		
	@foreach($points as $point)
		latLngs.push([{{ $point->lat }}, {{ $point->lng }}]);
		marker = L.marker([{{ $point->lat }}, {{ $point->lng }}], {
			icon: L.eiIcon({
				html: '<i class="{{ $point->icon }}"></i>{!! (empty($point->code) ? '' : '<i class="fas fa-qrcode status"></i>') . ($point->public ? '<i class="fas fa-lock-open public"></i>' : '') !!}',
				color: '{{ $point->color }}',
				borderColor: '{{ $point->second_color }}',
				draggable: true
			}),
			point_id: '{{ $point->id }}',
			code: '{{ $point->code }}',
			icon_class: '{{ $point->icon }}',
			public: {{ $point->public }},
			
			color: '{{ $point->color }}',
			second_color: '{{ $point->second_color }}',

			opacity:{!! (empty($point->code) ? '1.0' : '0.5') !!}
		}).addTo(map).on('click', function() {
			if(editMarker != this) {
				if(editMarker) {
					editMarker.setLatLng([$('#org_lat').val(), $('#org_lng').val()]);
					editMarker.dragging.disable();
					editMarker.stopBouncing();
				}
				this.bounce(2);
			}
			editMarker = this;

			$('form#pointForm .add').hide();
			$('form#pointForm .edit').show();
			$('form#pointForm #' + this.options.icon_class).prop('checked', true);
			$('form#pointForm #pointColor').val(this.options.color);
			$('form#pointForm #pointSecondColor').val(this.options.second_color);
			$('form#pointForm #pointColor').colorpicker('setValue', this.options.color);
			$('form#pointForm #pointSecondColor').colorpicker('setValue', this.options.second_color);
			$('form#pointForm #public').prop('checked', this.options.public);
			
			$('form#pointForm #lat').val(this.getLatLng().lat);
			$('form#pointForm #lng').val(this.getLatLng().lng);
			$('form#pointForm #org_lat').val(this.getLatLng().lat);
			$('form#pointForm #org_lng').val(this.getLatLng().lng);
			$('form#pointForm #point_id').val(this.options.point_id);
			$('form#pointForm #qr_code').val(this.options.code);
			
			$('form#pointForm').addClass('active');

			map.panInside(this.getLatLng(), {
				paddingTopLeft: [25, $('form#pointForm').height() + 65],
				paddingBottomRight: [25, 5]
			});
		}).on('dragend', function() {
			$('form#pointForm #lat').val(this.getLatLng().lat);
			$('form#pointForm #lng').val(this.getLatLng().lng);
		}).on('dragstart', function() {
			this.stopBouncing();
		}).on('drag', function() {
			
		}).on('bounceend', function() {
			if(editMarker == this) {
				this.dragging.enable();
			}
		});
		marker.dragging.disable();
		markers.push(marker);
	@endforeach
	
	
	@if($mapFit)
	if(!storedPosition && latLngs.length) {
		map.fitBounds(L.latLngBounds(latLngs));
	}
	@endif
	
	@if(session()->has('message'))
	$('#status #message').text('{{ session()->get('message') }}');
	$('#status').addClass('active');
	@endif
	$('#status .button.close').click(function() {
		$('#status').removeClass('active');
	});

</script>
<script type="module">
	import QrScanner from "./js/qr-scanner.js";
	QrScanner.WORKER_PATH = './js/qr-scanner-worker.min.js';

	const video = $('#qrScanner video')[0];
	const scanner = new QrScanner(video, result => setResult(result));

	function setResult(result) {
		scanner.stop();
		$('#qrScanner').removeClass('active');
		
		$('#qr_code').val(result);
		$('form#pointForm').addClass('active');
	}
	
	$('#qr_code_label').on('click', function() {
		$('form#pointForm').removeClass('active');
		$('#qrScanner').addClass('active');
		scanner.start();
		map.panInside(editMarker.getLatLng(), {
			paddingTopLeft: [25, window.innerWidth + 65],
			paddingBottomRight: [25, 5]
		});
	});
	$('#qr_code_clear').on('click', function() {
		$('#qr_code').val('');
	});
	
	$('#qrScanner .button.close').click(function() {
		scanner.stop();
		$('#qrScanner').removeClass('active');
		$('form#pointForm').addClass('active');
	});
	map.invalidateSize();
</script>
@endsection