<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<title>{{ config('app.name') }}</title>

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#fcdd07">
	<meta name="msapplication-TileColor" content="#fcdd07">
	<meta name="theme-color" content="#fcdd07">
	<base href="{{ url('/') }}/">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" integrity="sha384-v8BU367qNbs/aIZIxuivaU55N5GPF89WBerHoGA4QTcbUjYiLQtKdrfXnqAcXyTv" crossorigin="anonymous">
	<link rel="stylesheet" href="css/bootstrap-colorpicker.min.css">
	<link rel="stylesheet" href="css/app.css?mtime={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
	@yield('content')
	
	@auth
	<form id="logout-form" action="{{ route('logout') }}" method="POST">
		@csrf
		<button class="button"><i class="fas fa-unlock-alt"></i></button>
		<div class="user">{{ auth()->user()->name }}</div>
	</form>
	@else
		@if(request()->is('login'))
			<a href="/" class="button" id="login"><i class="fas fa-home"></i></a>
		@else
			<span class="button" id="login"><i class="fas fa-key"></i></span>
		@endif

		@if(isset($client) && $client->exists())
		<div class="user">{{ $client->name }}</div>
		@endif
	@endauth

	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="js/bootstrap-colorpicker.min.js"></script>
	<script src="js/leaflet.smoothmarkerbouncing.js"></script>
	<script src="js/qr-code.js"></script>
	
	<script type="text/javascript">
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		
		$('#fullScreen').click(function() {
			if($(document.body).hasClass('fullscreen')) {
				if (document.exitFullscreen) {
					document.exitFullscreen();
				} else if (document.webkitExitFullscreen) {
					document.webkitExitFullscreen();
				} else if (document.mozCancelFullScreen) {
					document.mozCancelFullScreen();
				} else if (document.msExitFullscreen) {
					document.msExitFullscreen();
				}
				$(document.body).removeClass('fullscreen');
				map.invalidateSize();
			} else {
				if (document.body.requestFullscreen) {
				  document.body.requestFullscreen();
				} else if (document.body.webkitRequestFullscreen) {
				  document.body.webkitRequestFullscreen();
				} else if (document.body.mozRequestFullScreen) {
				  document.body.mozRequestFullScreen();
				} else if (document.body.msRequestFullscreen) {
				  document.body.msRequestFullscreen();
				}
				$(document.body).addClass('fullscreen');
			}
		});
		
		(function() { 
			// how many milliseconds is a long press?
			var longpress = 2000;
			// holds the start time
			var start;

			$('span#login').on('mousedown touchstart', function() {
				start = new Date().getTime();
			}).on('mouseleave touchcancel', function() {
				start = 0;
			}).on('mouseup touchend', function() {
				if(new Date().getTime() >= start + longpress) {
					location.href = '{{ route('admin.points') }}';
				} else {
				    $(this).trigger('shortclick');
				}
			});
		}());
	</script>
@yield('scripts')
</body>
</html>
