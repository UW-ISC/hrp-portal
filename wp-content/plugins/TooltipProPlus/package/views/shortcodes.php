<?php
$shortcodes        = $currentPlugin->getOption( 'plugin-shortcodes', array() );
$shortcodes_action = $currentPlugin->getOption( 'plugin-shortcodes-action' );
if ( empty( $shortcodes ) && empty( $shortcodes_action ) ) {
    return;
}
?>

<style>
    @CHARSET "UTF-8";
    .suggest-user input[type=text] {width: 150px; margin-left: 0.5em;}
    th.cm-narrow, td.cm-narrow {width: 100px; text-align: center !important;}
    .cm-user-related-questions table {margin-bottom: 1em;}

    .cm-shortcode-desc {margin: 2em 0;}
    .cm-shortcode-desc header {background: #f0f0f0; padding: 0.5em; display: flex;}
    .cm-shortcode-desc header h4 {font-size: 150%; flex: 0 0 1; margin: 0; padding: 0;}
    .cm-shortcode-desc span {flex: 1; text-align: right;}
    .cm-shortcode-desc-inner {margin: 0 2em;}
    .cm-shortcode-desc-inner h5 {font-size: 150%; font-weight: normal; border-bottom: 1px dashed #c0c0c0; padding-bottom: 0.2em; margin: 1em 0;}
    .cm-shortcode-desc-inner ul li {margin-left: 2em; list-style-type: disc;}
    .cm-shortcode-desc-inner p {margin: 1em 0;}

    @media (max-width: 1300px) {
        .cm .box {width: 100% !important;}
    }

    @media (max-width: 640px) {
        .cm-shortcode-desc header {flex-direction: column;}
        .cm-shortcode-desc header h4 {margin-bottom: 0.5em;}
        .cm-shortcode-desc header span {text-align: left;}
    }
</style>

<section id="" class="cm">
    <div class="box padding">
        <div  class="postbox">
            <h3>
                <span>Available Shortcodes</span>
                <?php if ( $this->getUserguideUrl() ): ?>
                    <strong class="label-title-link"> <a class="label-title-link-class"  target="_blank" href="<?php echo $this->getUserguideUrl(); ?>">View Plugin Documentation >></a></strong>
                <?php endif; ?>
            </h3>
            <div class="inside">
                <?php echo $shortcodes; ?>
                <?php
                if ( !empty( $shortcodes_action ) ) {
                    echo do_action( $shortcodes_action );
                }
                ?>
            </div>
        </div>
    </div>
</section>