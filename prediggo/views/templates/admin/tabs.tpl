{*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prediggo SA <info@prediggo.com> / CeboWeb <dev@ceboweb.com>
*  @copyright  2008-2012 Prediggo SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Prediggo SA
*}

<div id="prediggo_configuration">
	<ul>
		<li>
			<a href="#prediggo_presentation">
				{l s='Introduction' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#main_conf">
                <img src="{$path}/img/logoconfig.jpg" alt="fr" title="fr">{l s=' Main Configuration' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#export_conf">
                <img src="{$path}/img/logoconfig.jpg" alt="fr" title="fr">{l s=' Export Configuration' mod='prediggo'}
			</a>
		</li>
        {*<li>
            <a href="#category_conf">
                <img src="{$path}/img/logoconfig.jpg" alt="fr" title="fr">{l s=' Category Configuration' mod='prediggo'}
            </a>
        </li>*}
        <li>
            <a href="#search_conf" style="color:rgb(138, 173, 220)">
                <img src="{$path}/img/logointelligentsearch.jpg" alt="fr" title="fr">{l s=' Search Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#search_autocompletion_conf" style="color:rgb(138, 173, 220)">
                <img src="{$path}/img/logointelligentsearch.jpg" alt="fr" title="fr">{l s=' Autocomplete Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#home_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Home Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#all_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' All Page Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#prod_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Product Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#cat_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Category Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#bask_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Basket Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#cust_reco_config" style="color: rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Customer Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
        <li>
            <a href="#layered_reco_conf" style="color:rgb(175, 24, 23)">
                <img src="{$path}/img/logosemanticmerchandising.jpg" alt="fr" title="fr">{l s=' Layered Recommendations Configuration' mod='prediggo'}
            </a>
        </li>
	</ul>

	<div id="prediggo_presentation">
        <a href="http://www.prediggo.com" target="_blank" title="Prediggo">
            <img src="{$path}/img/logo.png" title="{l s='Prediggo' mod='prediggo'}" alt="{l s='Prediggo' mod='prediggo'}" />
        </a>
        <p><span>{l s='Prediggo is the Agile Alternative for comprehensive Merchandising ' mod='prediggo'}</span></p>

        <p>{l s=' As Prestashop users are constantly looking for agility, we wanted to offer our Onsite Search and Recommendation Engine solutions as a Prestashop module' mod='prediggo'}</p>

        <p><span>{l s=' Both solutions require a fee. Do not hesitate to contact us for a quotation. ' mod='prediggo'}</span></p>

        <ol>
            <li><span>{l s='High performance have been proven by A/B testing' mod='prediggo'}</span></li>
            <div><span>{l s='Our patented semantic technology represents the next generation in e-merchandising. It powers the selling strategies that increase sales by 30&#37; through increased conversion and average order value' mod='prediggo'}</span></div>
            <p></p>
            <li><span>{l s='Prediggo is a flexible solution that is easy-to-use' mod='prediggo'}</span></li>
            <div><span>{l s=' Our intuitive back-office makes it easy to automate and optimise the most complex merchandising  !' mod='prediggo'}</span></div>
            <p></p>
            <li><span>{l s='Only a few days integration needed ' mod='prediggo'}</span></li>
            <div><span>{l s=' Prediggo is the only “complete” solution that does not require integration with your ERP, Cookies or Javascript. Our solutions work on a data export, so they integrate in days, not weeks.  !' mod='prediggo'}</span></div>
            <p></p>
            <li><span>{l s='A superior service ' mod='prediggo'}</span></li>
            <div><span>{l s=' Since we started in 2008, customer service has been one of our core values. So it is no wonder that our customer&#146;s have reported a 100&#37; satisfaction rate in our 2012 and 2013 surveys!' mod='prediggo'}</span></div>
        </ol>
        <ol>
            <p></p>
            <li style="list-style-type:disc">
            {l s='Contact us through our website and ask for a demo ! ' mod='prediggo'} <a href="http://www.prediggo.com/fr/prestashop/?lang=en" id="here" target="_blank" title="{l s='Contact us through our website ' mod='prediggo'}" style="color:blue;text-decoration:underline">{l s='>> HERE <<' mod='prediggo'}</a>
            </li>
            <li style="list-style-type:disc">
                {l s='Or give us a call +41 (0) 21 550 51 35' mod='prediggo'}
            </li>
        </ol>
	</div>
	<div id="main_conf"></div>
	<div id="export_conf"></div>
	<div id="layered_reco_conf"></div>
    <div id="home_reco_config"></div>
    <div id="all_reco_config"></div>
    <div id="prod_reco_config"></div>
    <div id="cat_reco_config"></div>
    <div id="bask_reco_config"></div>
    <div id="cust_reco_config"></div>
    <div id="search_autocompletion_conf"></div>
	<div id="search_conf"></div>
    <div id="category_conf"></div>
</div>