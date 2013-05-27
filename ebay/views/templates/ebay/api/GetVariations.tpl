<Variations>
	<VariationSpecificsSet>
	{foreach from=$variations_list key=group item=variations}
		<NameValueList>
			<Name>{$group}</Name>
			{foreach from=$variations key=attr item=val}
				<Value>{$attr}</Value>
			{/foreach}
		</NameValueList>
	{/foreach}
	</VariationSpecificsSet>
	{foreach from=$variations key=variation_key item=variation}
		<Variation>
			<SKU>prestashop-{$variation_key}</SKU>
			{if $price_update}
				<StartPrice>{$variation.price}</StartPrice>
			{/if}
			<Quantity>{$variation.quantity}</Quantity>
			<VariationSpecifics>
				{foreach from=$variation item=v}
					<NameValueList>
						<Name>{$v.name}</Name>
						<Value>{$v.value}</Value>
					</NameValueList>
				{/foreach}
			</VariationSpecifics>
		</Variation>
	{/foreach}
	<Pictures>
	{foreach from=$variations_pictures item=variations_pictures_list}
		{foreach from=$variations_pictures_list item=picture}
			{if isset($picture.name)}
				<VariationSpecificName>{$picture.name}</VariationSpecificName>
			{/if}
			<VariationSpecificPictureSet>
				<VariationSpecificValue>{$picture.value}</VariationSpecificValue>
				<PictureURL>{$picture.url}</PictureURL>
			</VariationSpecificPictureSet>
		{/foreach}
	{/foreach}
	</Pictures>
</Variations>
