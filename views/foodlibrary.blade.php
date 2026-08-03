@php require_frontend_packages(['datatables']); @endphp

@extends('layout.default')

@section('title', $__t('Food library'))

@section('content')
<div class="row">
	<div class="col">
		<div class="title-related-links">
			<h2 class="title">@yield('title')</h2>
			<div class="float-right @if($embedded) pr-5 @endif">
				<button class="btn btn-outline-dark d-md-none mt-2 order-1 order-md-3"
					type="button"
					data-toggle="collapse"
					data-target="#table-filter-row">
					<i class="fa-solid fa-filter"></i>
				</button>
				<button class="btn btn-outline-dark d-md-none mt-2 order-1 order-md-3"
					type="button"
					data-toggle="collapse"
					data-target="#related-links">
					<i class="fa-solid fa-ellipsis-v"></i>
				</button>
			</div>
			<div class="related-links collapse d-md-flex order-2 width-xs-sm-100"
				id="related-links">
				<a class="btn btn-primary responsive-button m-1 mt-md-0 mb-md-0 float-right"
					href="{{ $U('/product/new') }}">
					{{ $__t('Add') }}
				</a>
			</div>
		</div>
	</div>
</div>

<hr class="my-2">

<div class="row collapse d-md-flex"
	id="table-filter-row">
	<div class="col-12 col-md-6 col-xl-3">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-search"></i></span>
			</div>
			<input type="text"
				id="food-library-search"
				class="form-control"
				placeholder="{{ $__t('Search') }}">
		</div>
	</div>
</div>

<div class="row">
	<div class="col">
		<table id="food-library-table"
			class="table table-sm table-striped nowrap w-100">
			<thead>
				<tr>
					<th>{{ $__t('Name') }}</th>
					<th>{{ $__t('Energy') }}</th>
					<th>{{ $__t('Protein') }}</th>
					<th>{{ $__t('Fat') }}</th>
					<th>{{ $__t('Carbohydrates') }}</th>
					<th>{{ $__t('Nutrition basis') }}</th>
					<th>{{ $__t('Source') }}</th>
				</tr>
			</thead>
			<tbody class="d-none"></tbody>
		</table>
	</div>
</div>
@endsection

@section('viewJsName', 'foodlibrary')
