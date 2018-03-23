
{if $topproducts}

<section class="blocktopproducts row">

<h1 class="blocktopproducts-title text-center">{l s='Top products' mod='blocktopproducts'}</h1>

{foreach from=$topproducts item=topproduct}

	<article class="col-md-6">
		
		<figure class="topproducts">

			<img class="img-responsive" src="{$topproduct.image}" width="420" height="250"/>

			<figurecaption class="topproducts-block">
				<h4 class="topproducts-title">{$topproduct.name}</h4>
				<p class="topproducts-price{if $topproduct.specificPrice.reduction} on-sale{/if}">{convertPrice price=$topproduct.price}{if $topproduct.specificPrice.reduction}<span class="old-price"> {convertPrice price=($topproduct.specificPrice.reduction+$topproduct.price)}</span>{/if}</p>
				<div class="topproducts-desciption">{$topproduct.description_short}</div>
				<span class="topproducts-delivery">{$topproduct.delivery_time}</span>
			</figurecaption>

		</figure>		

	</article>



{/foreach}

</section>

{/if}
