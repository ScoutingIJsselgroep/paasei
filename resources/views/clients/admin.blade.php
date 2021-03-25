@extends('layouts.master')

@section('content')
<table>
	<tr>
		<td>#</td>
		<td>Naam</td>
		<td>E-mail</td>
		<td>link</td>
		<td>Gevonden</td>
		<td>Eerste</td>
		<td>Laatste</td>
	</tr>
	@foreach($clients as $client)
	<tr>
		<td>{{ $client->id }}</td>
		<td>{{ $client->name }}</td>
		<td>{{ $client->email }}</td>
		<td><a href="{{ route('clients.start', ['code' => $client->code]) }}" target="_blank">{{ $client->code }}</a></td>
		<td>{{ $client->points }}</td>
		<td>{{ $client->first_point }}</td>
		<td>{{ $client->last_point }}</td>
	</tr>
	@endforeach
</table>

@endsection

@section('scripts')
@endsection