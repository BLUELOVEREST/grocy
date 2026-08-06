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
				<button type="button"
					class="btn btn-outline-secondary"
					id="food-library-search-button"
					title="{{ $__t('Search') }}">
					<i class="fa-solid fa-search"></i>
				</button>
			</div>
			<input type="text"
				id="food-library-search"
				class="form-control"
				placeholder="{{ $__t('Search') }}">
			<div class="input-group-append">
				<button type="button"
					class="btn btn-outline-secondary"
					id="food-library-external-search-button"
					title="{{ $__t('Search Boohee') }}">
					{{ $__t('Boohee') }}
				</button>
			</div>
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
					<th>{{ $__t('Aliases') }}</th>
					<th>{{ $__t('Energy') }}</th>
					<th>{{ $__t('Protein') }}</th>
					<th>{{ $__t('Carbs') }}</th>
					<th>{{ $__t('Fat') }}</th>
					<th>{{ $__t('Nutrition basis') }}</th>
					<th>{{ $__t('Source') }}</th>
					<th>{{ $__t('Actions') }}</th>
				</tr>
			</thead>
			<tbody class="d-none"></tbody>
		</table>
	</div>
</div>

<div class="modal fade"
	id="food-library-aliases-modal"
	tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ $__t('Aliases') }}</h5>
				<button type="button"
					class="close"
					data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<input type="hidden"
					id="food-library-aliases-product-id">
				<div class="form-group">
					<label for="food-library-aliases-input">{{ $__t('Aliases') }}</label>
					<textarea class="form-control"
						id="food-library-aliases-input"
						rows="5"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button"
					class="btn btn-secondary"
					data-dismiss="modal">{{ $__t('Cancel') }}</button>
				<button type="button"
					class="btn btn-primary"
					id="food-library-aliases-save">{{ $__t('Save') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('viewJsName', 'foodlibrary')
