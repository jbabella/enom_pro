<?php
global $per_page;
$per_page = 25;
function pager ($enom_pro)
{
    global $per_page;
?>
    <ul class="pager">
    <li class="previous">
            	   <?php if (@$_GET['start'] >= $per_page) :?>
                   <?php $prev_start = isset( $_GET['start'] ) ? (int) $_GET['start'] - $per_page : $per_page;?>
                        <a data-start="<?php echo $prev_start ?>" href="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import&start=<?php echo $prev_start ?>">&larr; Prev</a>
                    <?php endif;?>
            	</li>
            	<li class="next">
                    <?php if (@$_GET['start'] <= ( count($enom_pro->getAllDomainsPricing()) - $per_page)) :?>
                      <?php $next_start = isset( $_GET['start'] ) ? (int) $_GET['start'] + $per_page : $per_page; ?>
                        <a data-start="<?php echo $next_start ?>" href="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import&start=<?php echo $next_start ?>">Next &rarr;</a>
                    <?php endif;?>	   
            	</li>
            </ul>
<?php 
}
?>
<?php 
/**
 * @var $this enom_pro
 */
// $this = new enom_pro();
if ($this->is_pricing_cached()) :
?>
    <div id="enom_pro_pricing_import_page">
        <?php if (
                isset($_GET['cleared']) ||
                isset($_GET['new']) ||
                isset($_GET['updated']) ||
                isset($_GET['deleted']) ||
                isset($_GET['nochange'])) :
            ?>
            <div class="slideup fixed" data-timeout="3">
                <?php if (isset($_GET['cleared'])) : ?>
                    <div class="alert alert-info">Cache Cleared</div>
                <?php endif; ?>
                <?php if (isset($_GET['new'])):?>
                    <div class="alert alert-success">
                        <p>Created <?php echo (int) $_GET['new']?> new TLD pricing in WHMCS</p>
                    </div>
                <?php endif;?>
                <?php if (isset($_GET['updated'])):?>
                    <div class="alert alert-success">
                        <p>Updated <?php echo (int) $_GET['updated']?> TLD pricing in WHMCS</p>
                    </div>
                <?php endif;?>
                <?php if (isset($_GET['deleted'])):?>
                    <div class="alert alert-error">
                        <p>Deleted <?php echo (int) $_GET['deleted']?> TLD pricing from WHMCS</p>
                    </div>
                <?php endif;?>
                <?php if (isset($_GET['nochange'])):?>
                    <div class="alert alert-error">
                        <p>No Pricing was Selected for Update</p>
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
        <p>Import pricing for all 3 domain order types: register, transfer &amp; renew. Once they are imported, you can 
        bulk edit all 3 pricing tiers, or you can fine tune pricing in directly in whmcs by 
        clicking the edit link: <span class="badge badge-info">WHMCS</span> 
        <a href="#" class="btn btn-mini" onClick="alert('Click the pricing row you want to edit, silly!');return false;">Edit</a></p>
        <div class="alert alert-danger">
            <p><b>USD Only:</b> eNom only supports USD currency, please make sure you set up your pricing accordingly.</p>
        </div>
      <div class="alert">
        <span><b>IMPORTANT:</b> Clicking Save will overwrite any specific order type pricing that have been customized
                (IE: Different prices for register vs. transfer).
                <em>If in doubt, please <a href="#" class="clear_all btn btn-mini" >Clear All Pricing</a> before saving.</em>
            </span><br/>
      </div>
        <div class="alert alert-info">
            <span>If you change price mode from retail/wholesale - please make sure you <a class="btn btn-mini" href="<?php echo enom_pro::MODULE_LINK; ?>&action=clear_price_cache">clear the pricing cache</a></span>
        </div>
        <?php //pager($this);?>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import" id="enom_pro_pricing_import">
            <input type="hidden" name="action" value="save_domain_pricing" />
            <input type="hidden" name="start" value="<?php echo isset($_GET['start']) ? (int) $_GET['start'] : '0';?>" />
            <div id="pricing_meta" class="clearfix">
                <div class="floatright">
                    <a href="#"
                        class="btn ep_tt clear_all" 
                        title="Clears current page pricing data">Clear All Pricing</a>
                    <a href="#" 
                        class="btn restore_all ep_tt"
                        title="Restores all pricing to eNom Values">Restore All eNom Pricing</a>
                </div>
            </div>
            <table class="datatable" id="enom_pro_pricing_table">
                <tr>
                    <th colspan="2">TLD</th>
                    <th>Status</th>
                    <?php foreach (array_keys(array_fill(1, enom_pro::get_addon_setting('pricing_years'), '')) as $key => $year) : ?>
                        <th colspan="1"><?php echo $year; ?> Year<?php if ($year > 1):?>s<?php endif;?>
                            <a href="#"
                                data-year="<?php echo $year?>"
                                class="btn btn-mini toggle_years ep_tt"
                                title="Toggle <?php echo $year; ?> year pricing">
                                    Toggle
                            </a>
                        </th>
                    <?php endforeach;?>
                </tr>
                <?php 
                $offset = isset($_GET['start']) ? (int) $_GET['start'] : 0;
                $domains = array_slice($this->getAllDomainsPricing(), $offset, $per_page);
                foreach ($domains as $tld => $price): ?>
                    <tr>
                        <td><?php echo $tld;?>
                            <?php $whmcs_pricing_for_tld = $this->get_whmcs_domain_pricing($tld);?>
                        </td>
                        <td>
                            <a href="#" 
                            data-tld="<?php echo $tld?>" 
                            class="btn btn-mini toggle_tld ep_tt" 
                            title="Import &amp; Toggle Pricing from eNom for this TLD">&rarr;</a>
                        </td>
                        <?php if (count($whmcs_pricing_for_tld) > 0) : ?>
                        <?php $whmcs_id = $whmcs_pricing_for_tld['id']; ?>
                            <td>
                                <table class="whmcs_actions noborder">
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">WHMCS</span>
                                        </td>
                                        <td>
                                            <a target="_blank" class="ep_lightbox btn btn-mini" 
                                                data-pricing="true"
                                                data-target="configdomains.php?action=editpricing&id=<?php echo $whmcs_id;?>"
                                                data-title="Pricing for .<?php echo $tld;?>"
                                                href="configdomains.php?action=editpricing&id=<?php echo $whmcs_id;?>">Edit</a>
                                        </td>
                                        <td>
                                        <a href="#" 
                                            data-tld="<?php echo $tld?>" 
                                            class="btn btn-mini delete_tld ep_tt" 
                                            title="Delete Pricing from WHMCS">x</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        <?php else:?>
                            <td>
                               <span class="badge small">Not In WHMCS</span>
                            </td>
                        <?php endif;?>
                        <?php foreach (array_keys(array_fill(1, enom_pro::get_addon_setting('pricing_years'), '')) as $key => $year) : ?>
                            <td>
                                <table class="noborder">
                                    <tr>
                                    <td>
                                        <?php 
                                        $enom_price = number_format(($price * $year), 2);
                                        $whmcs_price = isset($whmcs_pricing_for_tld[$year]) ? number_format($whmcs_pricing_for_tld[$year],2) : false;
                                        ?>
                                        <span class="price ep_tt" title="eNom Price"><?php echo '$'. $enom_price;?></span>
                                    </td>
                                        <td>
                                            <span
                                                class="badge toggle_this_val ep_tt"
                                                title="Copy eNom Price ($<?php echo $enom_price;?>)">
                                                &rsaquo;
                                            </span>
                                        </td>
                                        <td colspan="<?php echo ($whmcs_price ? 1 : 2)?>">
                                            <input
                                                data-tld="<?php echo $tld?>"
                                                data-year="<?php echo $year;?>"
                                                data-price="<?php echo $enom_price;?>"
                                                <?php if ($whmcs_price) :?>
                                                  data-whmcs="<?php echo $whmcs_price; ?>"
                                                <?php endif;?>
                                              class="price"
                                                size="6"
                                                type="text"
                                                name="pricing[<?php echo $tld?>][<?php echo $year?>]"
                                                value=""
                                            />
                                        </td>
                                        <?php if ($year == 1):?>
                                            <td>
                                                <a href="#" 
                                                    data-tld="<?php echo $tld?>" 
                                                    class="mult_row ep_tt btn btn-mini" 
                                                    title="Multiply Current Price for Row">X</a>
                                            </td>
                                        <?php endif;?>
                                        <?php if ($whmcs_price) :?>
                                          <td><span class="price ep_tt" title="WHMCS Registration Price">$<?php echo $whmcs_price; ?></span></td>
                                        <?php endif;?>
                                    </tr>
                                </table>                        
                            </td>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
            </table>
            <input type="submit" value="Save" class="btn btn-block btn-success">
        </form>
        <?php pager($this);?>
        <div>
            <?php echo count($this->getAllDomainsPricing()) ?> TLDs.
            Cached from <?php echo $this->get_price_cache_date();?>
            <a class="btn btn-mini" href="<?php echo enom_pro::MODULE_LINK; ?>&action=clear_price_cache">Clear Cache</a>
        </div>
        <br/>
        <a href="<?php echo enom_pro::HELP_URL;?>" target="_blank" class="btn alignright btn-info">Pricing Import Feedback? Suggestions?</a>
    </div>
  <script>
jQuery(function($) {
  $(".pager a").on('click', function () {
    var unsaved = false, $link = $(this);
    $('.price').each(function (k,v) {
      $input = $(v);
      console.log($input.val(),$input.data('whmcs'));
      if ($input.val() != '' && $input.val() != $input.data('whmcs')) {
        unsaved = true;
      }
      if ($input.val() != '' && ! $input.data('whmcs')) {
        unsaved = true;
      }
    });
    if (unsaved) {
      $("input[name=start]").val($link.data('start'));
      $("#enom_pro_pricing_import").trigger('submit');
      return false;
    }
  })
})

  </script>
<?php //End <div id="enom_pro_pricing_import_page">?>
<?php else: ?>
    <div class="alert" id="loading_pricing">
        <h3>Loading <?php echo enom_pro::is_retail_pricing() ? 'retail' : 'wholesale';?> pricing for <?php echo count($this->getTLDs())?> top level domains.</h3>
        <div class="enom_pro_loader"></div>
        <h3 class="hidden a_minute"><em>This may take a minute...</em></h3>
    </div>
    <script>
    jQuery(function($) {
    	setTimeout(function  (){
    		$(".alert .hidden").fadeIn(); 
    	}, 3000);
    	  $.ajax({
    		    url: '<?php echo enom_pro::MODULE_LINK; ?>&action=get_pricing_data',
    		    success: function  (data)
    		    {
    			    if (data == 'success') {
    			        window.location = 'addonmodules.php?module=enom_pro&view=pricing_import';
    			    } else {
    			    	$(".alert h3").html('An error ocurred: ' + data);
    			    	$(".alert .a_minute").add('.enom_pro_loader').hide();     			    	 
    			    }
    		    }
    	  });
    })
    </script>
<?php endif;?>