{*
* 2012-2013 Incentivibe
*
*  @author Incentivibe
*  @copyright  2012-2013 Incentivibe
*}

<script>
	$(document).ready(function() {
		$('input[name=setup-btn]').on("click", function() {
			window.location = "https://www.incentivibe.com/kg_admin/setup?auth_token={$iv_auth_token|escape:'htmlall':'UTF-8'}";
		});

		$('input[name=analytics-btn]').on("click", function() {
			window.location = "https://www.incentivibe.com/kg_admin/analytics?auth_token={$iv_auth_token|escape:'htmlall':'UTF-8'}";
		});

		$('input[name=export-btn]').on("click", function() {
			window.location = "https://www.incentivibe.com/kg_admin/export?auth_token={$iv_auth_token|escape:'htmlall':'UTF-8'}";
		});
	});
</script>

<style>

.start-free-trial-btn, .enter-contest-button {
  display: block;
  width: auto;
  padding: 0 10px;
  height: 46px;
  font-size: 24px;
  font-family: 'Novecentowide-Medium', sans-serif;
  cursor: pointer;
  color: #fff;
  border: none;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  -ms-border-radius: 5px;
  border-radius: 5px;
  float: left;
  background-image: -webkit-gradient(linear, left 0, left 100%, from(#fd903e), to(#ec6e19));
  background-image: -webkit-linear-gradient(top, #fd903e, 0%, #ec6e19, 100%);
  background-image: -moz-linear-gradient(top, #fd903e 0, #ec6e19 100%);
  background-image: linear-gradient(to bottom, #fd903e 0, #ec6e19 100%);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fd903e',endColorstr='#ec6e19',GradientType=0);
}

.start-free-trial-btn:active, .enter-contest-button:active {
  outline: 0;
  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
  box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
}

</style>

<div class='incentivibe-wrap'>
	<div class="incentivibe-header-wrap">
			<div class="incentivibe-header">
				<h1 class="incentivibe-logo">
					<a href="http://www.incentivibe.com/"></a>
				</h1>
				<h2 class="incentivibe-heading" >{l s='Virally grow subscribers & fans with $500 Prize for $25' mod='incentivibe'}</h2>
				<div class="clear"></div>
			</div>
	</div>
	<div class="layout">
		<div class="row top-banner">
			<div class="wrapper">
				<div class="container">
					<div class="incentivible-content-wrap">
			<div class="incentivibe-content">

				<!-- Setup -->
				<p style="font-size: 19px;">
					{l s='Manage your account settings through the Incentivibe admin panel. There, you can purchase a subscription, modify the widget type, change the prize, and theme the widget to your liking.' mod='incentivibe'}
				</p>
				<div class="clear"></div>
				<span class="start-free-trial">
					<input type="button" name="setup-btn" class="start-free-trial-btn" value="{l s='Manage Account Settings' mod='incentivibe'}">
				</span>
				<div class="clear"></div>

				<!-- Analytics -->
				<p style="font-size: 19px;">
					{l s='View analytics and track projected growth on email leads, Facebook likes, and Twitter follows.' mod='incentivibe'}
				</p>
				<div class="clear"></div>
				<span class="start-free-trial">
					<input type="button" name="analytics-btn" class="start-free-trial-btn" value="{l s='View Analytics'  mod='incentivibe'}">
				</span>
				<div class="clear"></div>

				<!-- Export -->
				<p style="font-size: 19px;">
					{l s='Export your email leads in CSV format (supported by Excel).' mod='incentivibe'}
				</p>
				<div class="clear"></div>
				<span class="start-free-trial">
					<input type="button" name="export-btn" class="start-free-trial-btn" value="{l s='Export Email'  mod='incentivibe'}">
				</span>
				<div class="clear"></div>

			</div>
		</div>
				</div>
			</div>
		</div>

		<!--End [ clients ]-->
	</div>
	
</div>
