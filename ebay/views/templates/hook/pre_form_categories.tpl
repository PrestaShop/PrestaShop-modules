<p class="center">
     <b>{l s='Your categories have already been configured.' mod='ebay'}</b>
</p>
<form action="index.php?{if $isOneDotFive}controller={Tools::getValue('controller')}{else}tab={Tools::getValue('tab')}{/if}&configure={Tools::getValue('configure')}&token={Tools::getValue('token')}&tab_module={Tools::getValue('tab_module')}&module_name={Tools::getValue('module_name')}&id_tab=2&section=category" method="post" class="form">
     <p class="center">
          <input class="button" name="submitSave" type="submit" value="{l s='See Categories' mod='ebay'}" />
     </p>
</form>