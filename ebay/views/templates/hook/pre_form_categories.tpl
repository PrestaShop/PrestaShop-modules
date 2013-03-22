<p class="center">
     <b>{l s='Your categories have been configured' mod='ebay'}</b>
</p>
<form action="index.php?{if $isOneDotFive}controller={$controller}{else}tab={$tab}{/if}&configure={$configure}&token={$token}&tab_module={$tab_module}&module_name={$module_name}&id_tab=2&section=category" method="post" class="form">
     <p class="center">
          <input class="button" name="submitSave" type="submit" value="{l s='See Categories' mod='ebay'}" />
     </p>
</form>