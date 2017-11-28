<section id="" class="cm">
	<div class="cmlp-left cmlp-box cmlp-padding">
		<div  class="postbox">
			<h3><span>CreativeMinds Support </span></h3>
			<div class="cmlp-inside">
				<p>Before submitting a bug please make sure you have the latest version of the plugin. You can <a href="https://www.cminds.com/guest-registration/" target="_blank">Register</a> and Login to our <a href="https://www.cminds.com/guest-account/" target="_blank">customer dashboard</a>, which will enable to download the latest version of the plugin.</p>
				<p><a href="https://www.cminds.com/wordpress-plugin-customer-support-ticket/"  target="_blank" class="buttonblue">Open a Support Ticket</a>  </p>

				<hr/>
				<h4>Using the Customer Dashboard Tutorial</h4>
				<div class="label-video">
					<iframe src="https://player.vimeo.com/video/134490629" width="500" height="280" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>
				<p>
					<a href="https://www.cminds.com/guest-account/"  target="_blank" class="buttonblue">Access Customers Dashboard</a>
				</p>

				<hr/>
				<h4>Share your Appreciation</h4>
				<p>Please consider sharing your experience by leaving a review. It helps us to continue our efforts in promoting this plugin.</p>
				<a target="_blank" href="<?php echo $this->getOption( 'plugin-review-url' ); ?>">
					<div class="btn button">
						<div class="dashicons dashicons-share-alt2"></div><span>Submit a review</span>
					</div>
				</a>

			</div>
		</div>

		<div  class="postbox">
			<h3><span>About CreativeMinds</span></h3>
			<div class="cmlp-inside">
				<p><a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/about/' ); ?>" target="_blank">The CreativeMinds team</a> specializes in creating cutting-edge WordPress Plugins and Magento® & Ecommerce Extensions, aimed to satisfy the growing needs of website administrators, designers and developers worldwide.</p>
				<p>CreativeMinds offers <a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/hire-us/' ); ?>"  target="_blank">Custom WordPress Plugins</a> to suit your specific requirements and make your WordPress website stand out above the rest! Our team of expert developers can add <a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/hire-us/' ); ?>"  target="_blank">custom features</a> to modify our existing plugins in a way that best suits your needs, or create a totally unique plugin from scratch! <a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/contact/' ); ?>"  target="_blank">Contact us</a> to hear more</p>
				<hr/>
				<h4>Follow CreativeMinds</h4>
				Twitter: <a href="https://twitter.com/CMPLUGINS" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @CMPLUGINS</a>
				<script>!function ( d, s, id ) {
                        var js, fjs = d.getElementsByTagName( s )[0], p = /^http:/.test( d.location ) ? 'http' : 'https';
                        if ( !d.getElementById( id ) ) {
                            js = d.createElement( s );
                            js.id = id;
                            js.src = p + '://platform.twitter.com/widgets.js';
                            fjs.parentNode.insertBefore( js, fjs );
                        }
                    }( document, 'script', 'twitter-wjs' );
				</script>
				<br />
				Google: <div class="g-follow" data-annotation="none" data-height="24" data-href="https://plus.google.com/108513627228464018583" data-rel="publisher"></div>

				<script type="text/javascript">
                    ( function () {
                        var po = document.createElement( 'script' );
                        po.type = 'text/javascript';
                        po.async = true;
                        po.src = 'https://apis.google.com/js/platform.js';
                        var s = document.getElementsByTagName( 'script' )[0];
                        s.parentNode.insertBefore( po, s );
                    } )();
				</script>

				<div id="fb-root"></div>
				<script>( function ( d, s, id ) {
                        var js, fjs = d.getElementsByTagName( s )[0];
                        if ( d.getElementById( id ) )
                            return;
                        js = d.createElement( s );
                        js.id = id;
                        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=459655384109264";
                        fjs.parentNode.insertBefore( js, fjs );
                    }( document, 'script', 'facebook-jssdk' ) );</script>

				Facebook: <div class="fb-follow" data-href="https://www.facebook.com/cmplugins" data-layout="standard" data-show-faces="false"></div>
				<hr>

				<!-- Begin MailChimp Signup Form -->
				<div id="mc_embed_signup">
					<form action="//cminds.us3.list-manage.com/subscribe/post?u=f48254f757fafba2669ae5918&amp;id=142732cbf9" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
						<div id="mc_embed_signup_scroll">
							<h4 for="mce-EMAIL">Subscribe to CM Newsletter</h4>
							<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
							<input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="btn button">
							<span style="display:inline-block; position: relative"><div class="cmf_field_help" title="We only send newsletters a couple of times a year. They include great deals, promo codes and information about our new plugins!"></div></span>
							<!-- real people should not fill this in and expect good things - do not remove this or risk fsorm bot signups-->
							<div style="position: absolute; left: -5000px;"><input type="text" name="b_f48254f757fafba2669ae5918_142732cbf9" tabindex="-1" value=""></div>
							<div class="clear"></div>
						</div>
					</form>
				</div>
				<!--End mc_embed_signup-->
				<hr />
				<h4><span>Join CM Affiliate Program</span></h4>
				<p>Earn money by referring your site visitor to CreativeMinds plugins store</p>
				<p>
					<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/referral-program/' ); ?>"  target="_blank" class="buttonblue">Affiliate Program</a>
				</p>


			</div>
		</div>

		<div  class="postbox">
			<h3><span>System Information</span></h3>
			<div class="cmlp-inside">
				<?php echo $this->displayServerInformationTab(); ?>
			</div>
		</div>
	</div>

	<div class="cmlp-right cmlp-box cmlp-padding">
		<div id="pages" class="pages postbox">
			<h3>
				<span>Plugin Documentation</span>
				<?php if ( $this->getUserguideUrl() ): ?>
					<strong class="label-title-link"> <a class="label-title-link-class"  target="_blank" href="<?php echo $this->getUserguideUrl(); ?>">View Plugin Documentation >></a></strong>
				<?php endif; ?>
			</h3>

			<div class="cmlp-inside">
				<h4>Plugin User Guide</h4>
				<p>For more detailed explanations please visit the plugin <a href="<?php echo $this->addAffiliateCode( $this->getUserguideUrl() ); ?>"  target="_blank">online documentation</a>. We also have a <a href="<?php echo $this->addAffiliateCode( $this->getOption( 'plugin-store-url' ) ); ?>"  target="_blank">detailed product page</a> for this plugin which includes demos and <a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/cm-plugins-video-library/' ); ?>"  target="_blank">video tutorials</a>.</p>
				<hr/>
				<h4>CSS Customizations</h4>
				<p>To easily customize the CSS using live WYSIWYG you can use <a href="https://wordpress.org/plugins/yellow-pencil-visual-theme-customizer/"><strong>Visual CSS Style Editor</strong></a> plugin.</p>
				<hr/>
				<?php
				$videos	 = $this->getOption( 'plugin-guide-videos' );
				$height	 = 280;
				$width	 = $height * 1.78125;

				if ( !empty( $videos ) && is_array( $videos ) ) :
					?>
					<?php foreach ( $videos as $key => $video ) : ?>
					<h4><?php echo $video[ 'title' ]; ?></h4>
						<div class="label-video">
							<iframe src="https://player.vimeo.com/video/<?php echo $video[ 'video_id' ]; ?>?title=0&byline=0&portrait=0" width="<?php echo $width; ?>" height="<?php echo $height; ?>" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<h4>How to Update your Plugin Tutorial</h4>
					<div class="label-video">
					<iframe src="https://player.vimeo.com/video/134692135" width="500" height="280" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>
			</div>
		</div>

		<div id="buy" class="buy postbox">
			<h3> <span>Buy CreativeMidns bundle of all CreativeMinds WordPress plugins</span></h3>
			<div class="plugins">
				<div class="list">
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/cm-wordpress-plugins-yearly-membership/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>WPmembership.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$199</span>
						</div>

						<div class="plugins-body item">
							<p><strong>BEST VALUE:</strong> Get all CreativeMinds products for a great discount! Offer includes unlimited updates and expert support.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/cm-wordpress-plugins-yearly-membership/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="buy" class="buy postbox">
			<h3> <span>Selected CreativeMinds Plugins</span></h3>
			<div class="plugins">

				<div class="list">


					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="https://wordpress.org/plugins/enhanced-tooltipglossary/" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>tooltip.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>FREE</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Tooltip Glossary</strong> - The best glossary managment tool for WordPress. Free Edition</p>
						</div>

						<div class="plugins-action item">
							<a class="button-download" href="https://wordpress.org/plugins/enhanced-tooltipglossary/" target="_blank" >Download</a>
						</div>
					</div>

					<!-- CM Tooltip Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/tooltipglossary' ); ?>" target="_blank">
								<img class="img" width="80" src="<?php echo plugin_dir_url( __FILE__ ); ?>tooltip.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>From $29</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Tooltip Glossary</strong> - The best glossary managment tool for WordPress</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/tooltipglossary' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!-- CM Answers Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/answers' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>answers.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Answers</strong> - Questions and Answers discussion forum.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/answers' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!-- Download Manager Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/downloadsmanager' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>downloads.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Download Manager</strong> - The ultimate tool for managing uploads and downloads.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/downloadsmanager' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!--  Pop Up Manager Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/cm-pop-up-banners-plugin-for-wordpress/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>popup.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$29</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Pop Up Manager</strong> - Easily publish your  events and products using PopUp Banners.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/cm-pop-up-banners-plugin-for-wordpress/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!--  Business Directory  Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-business-directory-plugin-for-wordpress/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>businessdir.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Business Directory</strong> - Supports the management of a business listing.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-business-directory-plugin-for-wordpress/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!--  Video Lessons  Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>videolessons.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Video Lessons Manager</strong> - Manage video lessons and allow users and admin to track progress.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-video-lessons-manager-plugin-for-wordpress/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>


					<!--  FAQ  Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/faq-plugin-for-wordpress-by-creativeminds' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>faq.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$29</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM FAQ</strong> - Build powerful frequently answered question (FAQ) knowledge base.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/faq-plugin-for-wordpress-by-creativeminds' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!--  Search and Replace  Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-on-demand-search-and-replace-plugin-for-wordpress/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>searchreplace.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$29</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Search and Replace</strong> - On demand search and replace tool allows you to easily replace texts & html.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-on-demand-search-and-replace-plugin-for-wordpress/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<!--  Cm Map Location Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/maps-routes-manager-plugin-for-wordpress-by-creativeminds/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>routes.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Map Route Manager</strong> - Generate a catalog of map routes and trails.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/maps-routes-manager-plugin-for-wordpress-by-creativeminds/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

						<!--  Cm Booking Calendar Plugin -->
					<div class="plugins-table">
						<div class="plugins-img item">
							<a href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/schedule-appointments-manage-bookings-plugin-wordpress/' ); ?>" target="_blank">
								<img class="img" src="<?php echo plugin_dir_url( __FILE__ ); ?>appointments_icon.png">
							</a>
						</div>

						<div class="plugins-price item">
							<span>$39</span>
						</div>

						<div class="plugins-body item">
							<p><strong>CM Booking Calendar</strong> - Customers can easily schedule appointments and pay for them directly through your website.</p>
						</div>

						<div class="plugins-action item">
							<a class="button-success" href="<?php echo $this->addAffiliateCode( 'https://www.cminds.com/wordpress-plugins-library/schedule-appointments-manage-bookings-plugin-wordpress/' ); ?>" target="_blank" >More Info</a>
						</div>
					</div>

					<hr/>

					<a href="<?php echo $this->getStoreUrl(); ?>"  target="_blank" class="buttonorange">View All Plugins</a>
					<a href="<?php echo $this->getStoreUrl( array( 'category' => 'Bundle' ) ); ?>"  target="_blank" class="buttonblue">View Bundles</a>
					<a href="<?php echo $this->getStoreUrl( array( 'category' => 'Add-On' ) ); ?>"  target="_blank" class="buttonblue">View AddOns</a>
					<a href="<?php echo $this->getStoreUrl( array( 'category' => 'Service' ) ); ?>" target="_blank" class="buttonblue">View Services</a>
				</div>
			</div>
		</div>
	</div>
</section>
