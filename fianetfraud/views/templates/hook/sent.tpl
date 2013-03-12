<br />
<fieldset {if version_compare($smarty.const._PS_VERSION_,'1.5','<')}style="width: 400px"{/if}>
  <legend><img src='{$logo_path}'/>{l s='Certissim Validation' mod='fianetfraud'}</legend>
  <p>{l s='This order has been sent to Certissim and the evaluation is not ready yet. Please wait a while and ask for the score with the link below.' mod='fianetfraud'}</p>
  <p><a href="{$url_get_eval}">{l s='Ask for the score.' mod='fianetfraud'}</a></p>
</fieldset>