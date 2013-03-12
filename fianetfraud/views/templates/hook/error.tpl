<br />
<fieldset {if version_compare($smarty.const._PS_VERSION_,'1.5','<')}style="width: 400px"{/if}>
  <legend><img src='{$logo_path}'/>{l s='Certissim Validation' mod='fianetfraud'}</legend>
  <p>
    {l s='An error has been encounterd while analysing the order: ' mod='fianetfraud'}{$error}
  </p>
  <p>
    <a href="{$url_vcd}" target="_blank">{l s='You may fix it there.' mod='fianetfraud'}</a>
  </p>
  <p>
    {l s='You have already fixed this order?' mod='fianetfraud'}
    <a href="{$url_update}">{l s='Checkout the score.' mod='fianetfraud'}</a>
  </p>
</fieldset>