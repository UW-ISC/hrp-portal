<?php
$addons = $currentPlugin->getOption( 'plugin-addons', array() );
if ( empty( $addons ) ) {
	return;
}
?>

<style>
	.cm .box.postbox.addonbox{
		width: 300px;
		height: 250px;
		margin: 20px 40px 5px 0px;
		display: flex;
		flex-direction: column;
	}

	.cm .box.postbox.addonbox .inside {
		display: flex;
		flex-direction: column;
		flex-grow: 2;
		justify-content: space-between;
	}

	.cm .box.postbox.addonbox .inside span {
		flex-grow: 2;
	}

	.cm .box.postbox.addonbox .inside .buttons {
		display: flex;
		justify-content: space-between;
	}

	.cm .box.addonbox .button-success, .cm .box.addonbox .button-success:focus {
		text-align: center;
		padding: 6px 12px;
		font-size: 14px;
		line-height: 1.42857143;
		border: 5px solid #FFF;
		-webkit-transition: all ease .15s;
		transition: all ease .15s;
		width: auto;
		display: block;
		color: #fff;
		text-decoration: none;
		background-color: #66c1ed;
		border-color: #66c1ed;
	}

	.cm .box.addonbox .button-success.buy, .cm .box.addonbox .button-success.buy:focus {
		background-color: #87b87f;
		border-color: #87b87f;
	}


	.cm .box.addonbox h3 {
		line-height: 36px;
 	    font-weight: 600;
	}


</style>
<section id="" class="cm">
	<?php foreach ( $addons as $value ) : ?>
		<div class="box postbox addonbox">
			<h3><span><?php echo esc_attr( $value[ 'title' ] ); ?></span></h3>
			<div class="inside">
				<span><?php echo esc_attr( $value[ 'description' ] ); ?></span>
				<div class="buttons">
					<?php if ($value[ 'link' ]  != "") { ?>
					<a class="button-success" href="<?php echo esc_attr( $value[ 'link' ] ); ?>" target="_blank">More Details</a>
					<?php } ?>
					<?php if ($value[ 'link_buy' ]  != "") { ?>
					<a class="button-success buy" href="<?php echo esc_attr( $value[ 'link_buy' ] ); ?>" target="_blank">Buy Now</a>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	endforeach;
	?>
</section>