<section id="" class="cm">
    <div class="left box padding">
        <div class="postbox">
			<?php
			echo $this->licensingApi->license_page();
			?>
        </div>

        <div  class="postbox">
            <h3><span>How to Find your License Key</span></h3>
            <div class="inside">
                <div class="cminds-licensing-instructions">
                    Your license key should be a string of 32 characters (letters and digits). You have two options to locate your license key:
                    <ol>
                        <li>
                            <p>
                                <strong>Customer Dashboard</strong> - You can get your license keys by logging in the <a target="_blank" href="https://www.cminds.com/guest-login/">CreativeMinds Customer Area</a>. <br>
                                If you don't have an account yet, you need first <a target="_blank" href="https://www.cminds.com/guest-registration/">register</a> using the e-mail you've used for the purchase. <br>
                                Your license key will be available under the Licenses or Purchases tabs.
                            </p>
                            <img width="600"  title="Cminds Customer Area screenshot" alt="Example Cminds Customer Area screenshot" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ) ?>../cminds_user_area.png">
                        </li>
                        <li>
                            <p>
                                <strong>Receipt </strong> -  You can get the license key for your product from the receipt we've sent you by email after your purchase. <br>
                            </p>
                            <img width="600" title="Example Cminds receipt with license key" alt="Example Cminds receipt" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ) ?>../cminds_receipt.png">
                        </li>

                        <li>
                    <p>
                        <strong>Customer Support </strong> - If there's <strong>no license key</strong> please <a href="https://www.cminds.com/wordpress-plugin-customer-support-ticket/"  target="_blank" class="">Open a Support Ticket</a>.
                    </p>
                        </li>
                    </ol>
              </div>
            </div>
        </div>
    </div>

    <div class="right box padding">
        <div id="pages" class="pages postbox">

			<?php
			echo $this->licensingApi->update_page();
			?>

        </div>

		<div  class="postbox">
            <h3><span>CreativeMinds Licensing Options</span></h3>
            <div class="inside">
				<form method="post">
					<p>
						<label>
							<input type="hidden" name="cminds_server_connect" value="0" />
							<input type="checkbox" name="cminds_server_connect" <?php checked( '1', $this->getSetting( 'cminds_server_connect', '1' ) ); ?> value="1" />
							<span>Connect to CreativeMinds server (license activation / deactivation, update checks, special offers)</span>
						</label>
					</p>
					<p>
						<label>
							<input type="hidden" name="cminds_license_notices_display" value="0" />
							<input type="checkbox" name="cminds_license_notices_display" <?php checked( '1', $this->getSetting( 'cminds_license_notices_display', '1' ) ); ?> value="1" />
							<span>Display licensing related notices on the admin notices area</span>
						</label>
					</p>
					<p>
						<label>
							<input type="hidden" name="cminds_debug_notices_display" value="0" />
							<input type="checkbox" name="cminds_debug_notices_display" <?php checked( '1', $this->getSetting( 'cminds_debug_notices_display', '0' ) ); ?> value="1" />
							<span>Display debug notices</span>
						</label>
					</p>
					<p>
						<label>
							<input type="hidden" name="cminds_ads_box_display" value="0" />
							<input type="checkbox" name="cminds_ads_box_display" <?php checked( '1', $this->getSetting( 'cminds_ads_box_display', '1' ) ); ?> value="1" />
							<span>Display ads box</span>
						</label>
					</p>
					<p><input type="submit" name="cminds_licensing_settings" id="submit" class="button button-primary" value="Save &amp; Activate"></p>
				</form>
            </div>
        </div>

        <div id="pages" class="pages postbox">
            <h3><span>How to Manage your CreativeMinds Products</span></h3>

            <div class="inside">
                <div style="height:10px;"></div>
                <p><a href="https://www.cminds.com/guest-account/"  target="_blank" class="buttonblue">Open CreativeMinds Customer Dashboard </a>
                <hr>
                <div class="label-video">
                    <iframe src="https://player.vimeo.com/video/134692135" width="600" height="337" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                </div>
                <div class="label-video">
                    <iframe src="https://player.vimeo.com/video/134159857" width="600" height="337" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                </div>
            </div>
        </div>

	</div>
</section>
