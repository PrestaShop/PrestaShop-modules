{*
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*}

{literal}
<script>
    var abtesting = '{/literal}{$abtesting}{literal}';
    $(document).ready(function () {
        if ($("body").attr("id") == "category") {
            var renderCategory = $.trim('{/literal}{$renderCategory}{literal}');
                $("{/literal}{$hookCategoryConfiguration->selectorCategory}{literal}").{/literal}{$hookCategoryConfiguration->selectorPositionCategory}{literal}(renderCategory);
                if ($("#aereco").length && $("{/literal}{$hookCategoryConfiguration->selectorCategory}{literal}").length) {
                    $("#aereco").show();
            }
        } 
        if ($("body").attr("id") == "search") {
            var renderSearch = $.trim('{/literal}{$renderSearch}{literal}');
                $("{/literal}{$hookSearchConfiguration->selectorSearch}{literal}").{/literal}{$hookSearchConfiguration->selectorPositionSearch}{literal}(renderSearch);
            if ($("#aereco").length && $("{/literal}{$hookSearchConfiguration->selectorSearch}{literal}").length) {
                $("#aereco").show();
            }
        }

        $('.ae-area a').on('click', function() {
            aenow = new Date().getTime();
            createCookie('aelastreco', (aenow+"."+$(this).parents(".ae-area").attr("class").split(" ")[1].split("-")[1]+"."+$(this).attr("rel")), 1);
        });
        
    });
</script>
{/literal}