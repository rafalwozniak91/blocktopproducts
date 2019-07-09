<div class="panel">
	<p>{l s='Before use assign hook to category.tpl' mod='blocktopproducts'}</p>
	<p><code>&#123;hook h="topproducts" id_category=$category->id&#125;</code></p>
</div>


{if $errors}

<div class="alert alert-danger">{$errors}</div>

{/if}

