
{if $topproducts}

<section class="blocktopproducts row">

<div class="row top_title">
	<h4><span>{l s='Top products' mod='blocktopproducts'}</span></h4>
</div>

{foreach from=$topproducts item=topproduct}

	<article class="col-md-6">
		
		<figure class="topproducts">

			<a href="{$topproduct.link}"><img class="img-responsive lazyProducts" data-original="{$topproduct.image}" width="420" height="250"/></a>

			<figurecaption class="topproducts-block">
				<a href="{$topproduct.link}"><h4 class="topproducts-title">{$topproduct.name}</h4></a>
				<p class="topproducts-price">
				<span class="price{if $topproduct.specific_prices.reduction} price-on-sale{/if}">{if !$priceDisplay}{convertPrice price=$topproduct.price}{else}{convertPrice price=$topproduct.price_tax_exc}{/if}</span>
				{if $topproduct.specific_prices.reduction}<span class="price old-price">{displayWtPrice p=$topproduct.price_without_reduction}</span>{/if} 
				</p>
				<div class="topproducts-desciption">{$topproduct.description_short}</div>
				<p class="topproducts-delivery"><i class="fa fa-truck"></i> <span class="topproducts-delivery__label">{l s='Delivery time:' mod='blocktopproducts'}: </span><span class="topproducts-delivery__time">{$topproduct.delivery_time}</span></p> 
			</figurecaption>

		</figure>		
		
	</article>



{/foreach}

</section>

{/if}
