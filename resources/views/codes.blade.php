@extends('layouts.master')

@section('content')
<div id="codes"></div>
@endsection

@section('scripts')
<script type="text/javascript">
	function randomCharacters(length) {
		var result = '',
			characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
			charactersLength = characters.length,
			length = length > 0 ? length : (5 + Math.ceil(Math.random() * 3));
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	function randomColor() {
		var result = '#',
			characters = '0123456789abcdef',
			charactersLength = characters.length;
		for(var i = 0; i < 6; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}
	for(var i = 0; i < 16; i++) {
		var div = $('<div class="c-' + (i%5) + '" style="color:' + randomColor() + ';"><div style="color:' + randomColor() + ';"></div></div>');
		$('#codes').append(div);
		
		new QRCode(div[0], {
			text: 'https://ei.ijssel.group/c/' + randomCharacters(),
			width: 166,
			height: 166,
			colorDark : "#000000",
			colorLight : "#ffffff",
			correctLevel : QRCode.CorrectLevel.H
		});

	}
</script>
@endsection

