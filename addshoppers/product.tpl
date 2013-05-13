<div itemscope itemtype="http://schema.org/Product" style="display: none;">
    <span itemprop="name">{$product->name|escape:'htmlall':'UTF-8'}</span>
    <span itemprop="description">{$product->description|escape:'htmlall':'UTF-8'}</span>
    <img itemprop="image" src="{$link->getImageLink($product->link_rewrite, $cover, 'large_default')}"/>
    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <span itemprop="price">{convertPrice price=$product->price}</span>
    </div>
</div>