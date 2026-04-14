@extends('layouts.app')

@section('content')
<x-logistics-report-table
    title="KG vs CBM Usage"
    subtitle="Actual weight, CBM, volumetric weight, and chargeable weight."
    :from="$from"
    :to="$to"
    :rows="$rows"
    type="weight"
/>
@endsection
