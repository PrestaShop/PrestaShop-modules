<br />
<fieldset {if version_compare($smarty.const._PS_VERSION_,'1.5','<')}style="width: 400px"{/if}>
  <legend><img src='{$logo_path}'/>{l s='Certissim Validation' mod='fianetfraud'}</legend>
  <a href="{$url_vcd}" target="_blank">{l s='See Detail' mod='fianetfraud'}</a>
  <p>
    {l s='Order has been evaluated: ' mod='fianetfraud'}<img src="{$path_to_picto}" alt="{$score}"/>
  </p>
  <p>
    {l s='More information: ' mod='fianetfraud'}{$detail}
  </p>
  <p>
    <a href="{$url_checkout}">{l s='Update the score.' mod='fianetfraud'}</a>
  </p>
</fieldset>