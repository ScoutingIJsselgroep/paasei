@extends('layouts.master')

@section('content')
<div id="codes"></div>
@endsection

@section('scripts')
<script type="text/javascript">
	
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

