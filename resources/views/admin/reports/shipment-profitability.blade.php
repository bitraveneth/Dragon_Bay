@extends('layouts.app')

@section('content')
<x-logistics-report-table
    title="Shipment Profitability"
    subtitle="Revenue, approved landed cost, and margin per shipment."
    :from="$from"
    :to="$to"
    :rows="$rows"
    type="profitability"
/>
@endsection
