@extends('layouts.app')

@section('content')
<x-logistics-report-table
    title="Air vs Sea Performance"
    subtitle="Shipment count, chargeable weight, revenue, cost, and margin by shipment mode."
    :from="$from"
    :to="$to"
    :rows="$rows"
    type="mode"
/>
@endsection
