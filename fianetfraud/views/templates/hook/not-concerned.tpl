<br />
<fieldset {if version_compare($smarty.const._PS_VERSION_,'1.5','<')}style="width: 400px"{/if}>
  <legend><img src='{$logo_path}'/>{l s='Certissim Validation' mod='fianetfraud'}</legend>
  <p>{$txt}</p>
  {if $paid}
    <p><a href={$url_send_order}>{l s='Send the order to Certissim' mod='fianetfraud'}</a></p>
  {/if}
</fieldset>