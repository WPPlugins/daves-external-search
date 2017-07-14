<div class="wrap">
<h2>Dave's External Search Options</h2>

<p>To view Flickr search results in your Dave's External Search widget, you need to <a href="http://www.flickr.com/services/api/keys/apply/" target="_blank">register for a Flickr API key</a>. This is a string of numbers and letters that uniquely identifies an application to the Flickr site.</p>

<p>Once you have an API key from Flickr, enter it here:</p>

<form method="post" action="">

<?php
if ( function_exists('wp_nonce_field') )
	wp_nonce_field('daves-external-search-config');
?>

<table class="form-table"><tbody>

<!-- Maximum results -->
<tr valign="top">
<th scope="row">Flickr API Key</th>

<td><input type="text" name="daves-external-search_flickr_api_key" id="daves-external-search_flickr_api_key" value="<?php echo $flickrAPIKey; ?>" class="regular-text code" /><span class="setting-description"></span></td>
</tr>

<!-- Submit buttons -->
<tr valign="top">
<td colspan="2"><div style="border-top: 1px solid #333;margin-top: 15px;padding: 5px;"><input type="submit" name="daves-external-search_submit" id="daves-external-search_submit" value="Save Changes" /></div></td>
</tr>

</tbody></table>

</form>
