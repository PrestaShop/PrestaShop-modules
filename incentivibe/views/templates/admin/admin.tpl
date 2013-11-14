{*
* 2012-2013 Incentivibe
*
*  @author Incentivibe
*  @copyright  2012-2013 Incentivibe
*}

<script>
$( document ).ready(function() {
  var loginFailed = "{$iv_login_failed|escape:'htmlall':'UTF-8'}";

	if (loginFailed) {
		$( ".incentivibe-login" ).slideToggle("slow");
		$( ".incentivibe-signup" ).slideToggle("slow");
	}

	$( ".right_signin" ).click(function() {
		$( ".incentivibe-signup" ).slideToggle("slow");
		$( ".incentivibe-login" ).slideToggle("slow");
	});
});
</script>

<div class='incentivibe-wrap'>
	<div class="incentivibe-header-wrap">
			<div class="incentivibe-header">
				<h1 class="incentivibe-logo">
					<a href="http://www.incentivibe.com/"></a>
				</h1>
				<h2 class="incentivibe-heading" >{l s='Virally grow subscribers and fans with $500 Prize for $25' mod='incentivibe'}</h2>
				<div class="clear"></div>
			</div>
	</div>
	<div class="layout">
		<div class="row top-banner">
			<div class="wrapper">
				<div class="container">
					<div class="incentivible-content-wrap">
            <div class="incentivibe-content">
      				<div class="incentivibe-left">
      					<div class="incentivibe-content-top">
      						<h1 class="incentivibe-main-title pink">{l s='How does it work?' mod='incentivibe'}</h1>
      						{l s='Incentivibe groups businesses together so they can offer one big contest prizes (e.g. $500 visa) on their website while sharing a fraction of the prize cost (e.g. $25) with other businesses.' mod='incentivibe'}
      					</div>
      					<div class="incentivibe-video">
      						<iframe width="490" height="315" src="//www.youtube-nocookie.com/embed/DDCYb9ffUGI?rel=0&showinfo=0&theme=light&iv_load_policy=3&modestbranding=1" frameborder="0" allowfullscreen></iframe>
      					</div>
      				</div>
      				<div class="incentivibe-right">
      					{if isset($iv_auth_token)}
      						<div class="incentivibe-content-top">
      							<h1 class="incentivibe-main-title pink">{l s='Signup for FREE in 5 Seconds!' mod='incentivibe'}</h1>
      							{l s='No credit card required to join' mod='incentivibe'}
      						</div>
      						<div class="incentivibe-signup">
      							<div class="incentivibe-form">
      								{if isset($register_errors)}
      									<div class="errors">
      										<ul>
      										{foreach from=$register_errors item=error}
      											<li>{$error|escape:'htmlall':'UTF-8'}</li>
      										{/foreach}
      										</ul>
      									</div>
      								{/if}

      								<form action="{$incentivibe_form_link|escape:'htmlall':'UTF-8'}" id="incentivibe-signup-form" method="post">
      									<p>
      										<label>{l s='Email' mod='incentivibe'}</label>
      										<input id="user_email" name="user_email" size="30" placeholder="{l s='Email' mod='incentivibe'}" type="email" value="">
      									</p>

      									<p>
      										<label>{l s='Full Name' mod='incentivibe'}</label>
      										<input id="user_full_name" name="user_full_name" placeholder="{l s='Full Name' mod='incentivibe'}" size="30" type="text">
      									</p>

      									<p>
      										<label>{l s='Company Name' mod='incentivibe'}</label>
      										<input id="user_company_name" name="user_company_name" placeholder="{l s='Company Name' mod='incentivibe'}" size="30" type="text">
      									</p>

      									<p>
      										<label>{l s='Password' mod='incentivibe'}</label>
      										<input id="user_password" name="user_password" size="30" placeholder="{l s='Password' mod='incentivibe'}" type="password">
      									</p>

      									<input type="hidden" name="user_platform_id" id="user_platform" value="{$platform_id|escape:'htmlall':'UTF-8'}">
      									<input id="plan_id" name="user_plan_id" type="hidden" value="2">
      									<input id="giveaway_url" name="user_shop" type="hidden" value="{$user_shop|escape:'htmlall':'UTF-8'}">
      									<p>
      										<label>&nbsp;</label>
      										<input id="incentivibe_signup" class="btn-submit" name="incentivibe_signup" type="submit" value="{l s='Sign Up For Free 14-Day Trial' mod='incentivibe'}"> <a class="right_signin" href="JavaScript:void(0);">{l s='Sign In' mod='incentivibe'}</a><span class="right_span">{l s='or' mod='incentivibe'}</span>
      									</p>
      									{*<p class="subscript"><label></label>{l s='No credit card required to join' mod='incentivibe'}</p>*}
      								</form>
      							</div>
      						</div>
      					{/if}
      					{if isset($iv_auth_token)}
      						<div class="incentivibe-login" style="display:none">
      							<div class="incentivibe-form">
      								{if isset($login_errors)}
      									<div class="errors">
      										<ul>
      										{foreach from=$login_errors item=error}
      											<li>{$error|escape:'htmlall':'UTF-8'}</li>
      										{/foreach}
      										</ul>
      									</div>
      								{/if}

      								<form action="{$incentivibe_form_link|escape:'htmlall':'UTF-8'}" id="incentivibe-login-form" method="post">
      									<p>
      										<label for="user_email">{l s='Email' mod='incentivibe'}</label>
      										<input id="user_email" name="user_email" size="30" placeholder="{l s='Email' mod='incentivibe'}" type="email" value="">
      									</p>
      									<p>
      										<label for="user_password">{l s='Password' mod='incentivibe'}</label>
      										<input id="user_password" name="user_password" placeholder="{l s='Password' mod='incentivibe'}" size="30" type="password">
      									</p>
      									<p>
      										<label>&nbsp; </label>
      										<input name="incentivibe_login" class="btn-submit" type="submit" value="{l s='Sign In' mod='incentivibe'}">
      										<a class="right_signin" href="JavaScript:void(0);">{l s='Sign up' mod='incentivibe'}</a><span class="right_span">{l s='or' mod='incentivibe'}</span>
      									</p>
      								</form>
      							</div>
      						</div>
      					{/if}
      				</div>
				<div class="clear"></div>
			</div>
		</div>
				</div>
			</div>
		</div>

		<!--Start [ national media part ]-->
		<div class="row national-media">
		  <div class="wrapper">
		    <div class="container">
		          <p>{l s='Featured in award winning marketing & national media' mod='incentivibe'}</p>
		            <div class="national-media-group">
		              <a href="javascript:void(0)"><span class="national-media-image national-media-image1" alt=""></span></a>
		              <a href="javascript:void(0)"><span class="national-media-image national-media-image4" alt=""></span></a>
		              <a href="javascript:void(0)"><span class="national-media-image national-media-image5" alt=""></span></a>
		              <a href="javascript:void(0)"><span class="national-media-image national-media-image6" alt=""></span></a>
		            </div>
		        </div>
		    </div>
		</div>
		<!--End [ national media part ]-->


		<!--Start [ shared contents ]-->
		<div class="row shared-contents" id="how-it-works">
		  <div class="wrapper">
		    <div class="container">
		          <h1 class="main-title">{l s='How can you offer $500 contest prizes for only $25?' mod='incentivibe'} </h1>
		            <!--start[row]-->
		            <div class="row shared-row">
		              <div class="col-md-4">
		                  <div class="shared-col1-inner">
		                      <span class="shared-explanation shared-explanation1" width="101" height="92" alt=""></span>
		                        <h2>{l s='Prize cost is shared' mod='incentivibe'}</h2>
		                        <p>{l s='Just like you, there are other businesses in the prize pool who pay $25 to co-sponsor the cost of a $500 prize.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                  <div class="shared-col2-inner">
		                      <span class="shared-explanation shared-explanation2" width="101" height="92" alt=""></span>
		                        <h2>{l s='Big Targeted prizes' mod='incentivibe'}</h2>
		                        <p>{l s='Attract & convert your visitors into leads with iPads, Vacations, Paypal Cash & more from our list of prizes to match your targeted audience.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                  <div class="shared-col3-inner">
		                      <span class="shared-explanation shared-explanation3" width="101" height="92" alt=""></span>
		                        <h2>{l s='Your Branding & No data Sharing' mod='incentivibe'}</h2>
		                        <p>
		                          {l s='You’ll get Incentivibe’s free contest tool to put on your website with your branding.  Your visitor’s data will not be shared with other businesses' mod='incentivibe'}
		                        </p>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		            <!--end[row]-->

		            <!--start[row]-->
		            <div class="row shared-row">
		              <div class="col-md-4">
		                  <div class="shared-col1-inner">
		                      <span class="shared-explanation shared-explanation4" width="101" height="92" alt=""></span>
		                        <h2>{l s='Run Contest anytime, anywhere' mod='incentivibe'}</h2>
		                        <p>{l s='Contests run every month. You’ll get a code to copy-paste to get the tool on your website, facebook page, blog etc to collect emails, fans, surveys and more.' mod='incentivibe'} </p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                  <div class="shared-col2-inner">
		                      <span class="shared-explanation shared-explanation5" width="101" height="92" alt=""></span>
		                        <h2>{l s='Equal odds of winning' mod='incentivibe'} </h2>
		                        <p>{l s='To ensure fair odds for all businesses, one nominee is chosen from each co-sponsoring business, and then a winner is selected from all those nominees.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                  <div class="shared-col3-inner">
		                      <span class="shared-explanation shared-explanation6" width="101" height="92" alt=""></span>
		                        <h2>{l s='Winner Announcement' mod='incentivibe'}</h2>
		                        <p>{l s='To ensure transparency, Incentivibe sends an email to all contestants with winner’s name. Business name from which a winner was chosen is not disclosed to contestants.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		            <!--end[row]-->

		            <div class="faq-title-outer">
		              <div class="faq-title-inner"><span></span></div>
		            </div>

		        </div>
		    </div>
		</div>
		<!--End [ shared contents ]-->




		<!--Start [ features ]-->
		<div class="row features" id="features-info">
			<div class="wrapper">
				<div class="container">
		        	<h1 class="main-title">{l s='Free features for viral growth' mod='incentivibe'} </h1>
		            <!--start[row]-->
		            <div class="row features-row">
		            	<div class="col-md-4">
		                	<div class="features-col1-inner">
		                    	<span class="feature feature1" width="101" height="81" alt=""></span>
		                        <h2>{l s='700% email subscribers' mod='incentivibe' }</h2>
		                        <p>{l s='Run Incentivibe contests on your landing pages, websites or on social media and collect on average 700% more email subscribers.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                	<div class="features-col2-inner">
		                    	<span class="feature feature2" width="101" height="81" alt=""></span>
		                        <h2>{l s='500% more Fans & Buzz' mod='incentivibe'}</h2>
		                        <p>{l s='Get 500% more of your visitors to become your fan or follower or share or tweet your custom marketing message to create marketing buzz.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                	<div class="features-col3-inner">
		                    	<span class="feature feature3" width="101" height="81" alt=""></span>
		                        <h2>{l s='Legal & Prize delivery included' mod='incentivibe'}</h2>
		                        <p>{l s='Our free contest tool includes all necessary legal terms so you don’t have to draft them. We will also deliver the prize to the winner. You will NOT be held liable.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		            <!--end[row]-->

		            <!--start[row]-->
		            <div class="row features-row">
		            	<div class="col-md-4">
		                	<div class="features-col1-inner">
		                    	<span class="feature feature4" width="101" height="81" alt=""></span>
		                        <h2>{l s='Micro-insight Surveys' mod='incentivibe'}</h2>
		                        <p>{l s='Ask your visitors to answer any survey question of your choice and provide email to enter contest.  For eg, “when do you intend upon purchasing” or “what topic of articles do you prefer”' mod='incentivibe'}.</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                	<div class="features-col2-inner">
		                    	<span class="feature feature5" width="101" height="81" alt=""></span>
		                        <h2>{l s='Targeted list based on survey responses' mod='incentivibe'}</h2>
		                        <p>{l s='See targeted email list sorted by survey answers (e.g. which visitors want to buy today) so you can send them customized offers to increase sales.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="col-md-4">
		                	<div class="features-col3-inner">
		                    	<span class="feature xfeature6" width="101" height="81" alt="" style="margin-top: 100px;"></span>
		                        <h2>{l s='Integration with leading tools' mod='incentivibe'}</h2>
		                        <p>{l s='Incentivibe integrates contests and your data with Mailchimp, Aweber, WordPress, custom websites and many more.' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		            <!--end[row]-->

		            <div class="faq-title-outer">
		            	<div class="faq-title-inner"><span></span></div>
		            </div>
		        </div>
		    </div>
		</div>
		<!--End [ features ]-->



		<!--Start [ simple pricing ]-->
		<div class="row simple-pricing" id="pricing">
		  <div class="wrapper">
		    <div class="container">
		          <h1 class="main-title">{l s='One Simple Pricing: $25 Per Monthly Contest' mod='incentivibe'}</h1>
		            <div class="row">
		                <div class="col-md-6">
		                    <div class="pricing-col1-inner">
		                        <a href="#" class="price-badge"></a>
		                        <p class="contribution">{l s='Our pricing is simply $25 because we pools other businesses in the prize pool who pay $25 to co-sponsor the cost of a $500 prize. Our price includes:' mod='incentivibe'}</p>
		                        <ul class="contr-points">
		                            <h5 class="price-sub-title">{l s='Free Services:' mod='incentivibe'}</h5>
		                            <li>{l s='Free Contest tool' mod='incentivibe'}</li>
		                            <li>{l s='Free Contest terms' mod='incentivibe'}</li>
		                            <li>{l s='Free Winner Announcement' mod='incentivibe'}</li>
		                            <li>{l s='Free prize Delivery' mod='incentivibe'}</li>
		                        </ul>
		                        <ul class="contr-points">
		                            <h5 class="price-sub-title">{l s='No Obligation:' mod='incentivibe'}</h5>

		                            <li>{l s='No Contract' mod='incentivibe'}</li>
		                            <li>{l s='Money Back Guarantee' mod='incentivibe'}</li>
		                            <li>{l s='Dont pay until you upgrade' mod='incentivibe'}</li>
		                            <li>{l s='Your contestants will be eligible to win even if you do not updgrade' mod='incentivibe'}</li>
		                        </ul>

		                        <div class="clear"></div>
		                        <div class="all-feature-link">
                              <a href="#features-info" class="">
                                {l s='See all features' mod='incentivibe'}
                              </a>
                            </div>
		                    </div>
		                </div>

		                <div class="col-md-6">
		                    <div class="pricing-col2-inner">
		                        <h1 class="price-title">{l s='Our Prizes' mod='incentivibe'}</h1>
		                        <p class="contribution">{l s='Price includes any of the $500 contest prizes of your choice.' mod='incentivibe'}</p>
		                        <div class="contest-price-row">
		                          <a href="javascript:void(0)"><span class="prize prize1" width="161" height="89" alt=""></span></a>
		                          <a href="javascript:void(0)"><span class="prize prize2" width="161" height="89" alt=""></span></a>
		                        </div>
		                        <div class="contest-price-row">
		                          <a href="javascript:void(0)"><span class="prize prize3" width="161" height="89" alt=""></span></a>
		                          <a href="javascript:void(0)"><span class="prize prize4" width="161" height="89" alt=""></span></a>
		                        </div>
		                        <div class="contest-price-row last">
		                          <a href="javascript:void(0)"><span class="prize prize5" width="161" height="89" alt=""></span></a>
		                          <a href="javascript:void(0)"><span class="prize prize6" width="161" height="89" alt=""></span></a>
		                        </div>
		                        <p class="many-more">… {l s='and many more' mod='incentivibe'}</p>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		        </div>
		    </div>
		</div>
		<!--End [ simple pricing ]-->



		<!--Start [ clients ]-->
		<div class="row clients" id="testimonials">
			<div class="wrapper">
				<div class="container">
		        	<h1 class="main-title">{l s='Clients love us whether they are small or featured in Forbes or New York Times' mod='incentivibe'}</h1>
		            <h2 class="sub-head">{l s='(Testimonials)' mod='incentivibe'}</h2>
		            <!-- <div class="all-feature-link"><a href="#" class="">Read all testimonials</a></div> -->
		            <!--start[clients row]-->
		            <div class="row">
		            	<div class="col-md-4">
		                	<div class="clients-col1-inner">
		                    	<div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client1"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='LOVE THIS. We got 12x more subscribers when using incentivibe. Well worth it.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Jeremiah R." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client2"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='Incentivibe has been a wonderful addition to our store! Our email subscriptions have jumped 300% and that is all due to Incentivibe.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Frank K." mod='incentivibe'} </div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client3"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='Simplest way to gain social engagement on Facebook, total likes +600%, total reach +900% in a week time. Amazing.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Stacy C." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client4"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='Our company, Splashtop, wanted to increase our email subscribers and used Incentivibe as one of our strategies. Our newsletter subscription increased by 80%. I’d definitely use them again, and recommend it for all businesses that are budget conscious.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Joannah M." mod='incentivibe'}<br>~ {l s='Featured in NY times' mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                    </div>
		                </div>

		                <div class="col-md-4">
		                	<div class="clients-col2-inner">
		                    	<div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client5"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='We are psyched to work with them and love the increased performance they bring to our list building strategies.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Steve K." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client6"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='This app is doing wonders for my Facebook Likes and the astounding number of email addresses I have collected.' mod='incentivibe'} <span></span></p>
		                                <div class="client-name">{l s="Francis L." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client7"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='Thank you, Incentivibe. I cannot express how much I love this app. Our newsletter subscriptions went up 500% since we started the campaign. I would recommend Incentivibe to any store that wants to increase sales and subscribers.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Eric M." mod='incentivibe'} </div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                    </div>
		                </div>

		                <div class="col-md-4">
		                	<div class="clients-col3-inner">
		                    	<div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client8"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='This has been one of the best apps we have used so far. I have saved over $15,000 in advertising costs.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Victoria L." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client9"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='This is by far the best application that I have added to my website.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Tony F." mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clients-box">
		                        	<span width="61" height="61" alt="" class="img-circle client10"></span>
		                            <div class="clients-desc-space">
		                            	<p>{l s='The Incentivibe model was cost-effective, and was very easy to implement and monitor. After seeing a 40% increase in our Facebook Page interaction, and a 25% increase in our blog traffic, we are excited to get started on our next contest.' mod='incentivibe'}<span></span></p>
		                                <div class="client-name">{l s="Steph G." mod='incentivibe'}<br>~ {l s='Featured in Forbes' mod='incentivibe'}</div>
		                            </div>
		                            <div class="clear"></div>
		                        </div>
		                    </div>
		                </div>
		                <div class="clear"></div>
		            </div>
		            <!--end[clients row]-->
		        </div>
		    </div>
		</div>
		<!--End [ clients ]-->
	</div>

</div>
