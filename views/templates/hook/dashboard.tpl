<section id="dashtopproducts" class="panel">
	<div class="panel-heading">
		<i class="icon-bar-chart"></i>
		{l s='Top Products' mod='blocktopproducts'}
	</div>

	<div class="table-responsive">
		
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>{l s='Category path' mod='blocktopproducts'}</th>
					<th>{l s='Products count' mod='blocktopproducts'}</th>
				</tr>
			</thead>
			<tbody>
				

			{foreach from=$topProductsCategories item=topCategory}
				<tr class="danger">
					<td>{$topCategory.id_category}</td>
					<td>{$topCategory.category_path}</td>
					<td>{$topCategory.products_count}</td>
				</tr>
			{/foreach}

			</tbody>
		</table>
	</div>

</section>
