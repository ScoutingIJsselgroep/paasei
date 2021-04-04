@extends('layouts.master')

@section('content')
<table>
	<tr>
		<td>Rang</td>
		<td>Naam</td>
		<td>Gevonden</td>
		<td>Eerste</td>
		<td>Laatste</td>
		<td>Tijd</td>
	</tr>
	@foreach($clients as $client)
	@php
		if($client->first_point) {
			$first_point = \Carbon\Carbon::parse($client->first_point);
		} else {
			$first_point = null;
		}
		if($client->last_point) {
			$last_point = \Carbon\Carbon::parse($client->last_point);
		} else {
			$last_point = null;
		}
	@endphp
	<tr title="{{ $client->id }}" style="border-top:1px solid #333;">
		<td>{{ ($loop->index + 1) }}</td>
		<td>
			{{ $client->name }}<br />
			{{ $client->email }}<br />
			<a href="{{ route('clients.start', ['code' => $client->code]) }}" target="_blank">{{ $client->code }}</a> ({{ $client->id }})
		</td>
		<td>{{ $client->points }}</td>
		<td>{{ $first_point ? $first_point->format('d-m H:i:s') : '' }}</td>
		<td>{{ $last_point ? $last_point->format('d-m H:i:s') : '' }}</td>
		<td>{{ $client->points ? format_seconds($last_point->timestamp - $first_point->timestamp) : '' }}</td>
	</tr>
	@endforeach
</table>

@endsection

@section('scripts')
@endsection