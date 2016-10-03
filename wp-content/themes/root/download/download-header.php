<?php $menus = uwhr_get_header_menus(); ?>

<header class="uwhr-headers" id="top">
    <div class="uwhr-global-header">
        <div class="row">
            <div class="col-xs-12 uwhr-global-header-wrapper">
                <div class="pull-right">
                    <?php echo $menus['global']; ?>
                    <?php global $UWHR; $UWHR->Search->UI->render_search_form( 'slim' ); ?>
               </div>
           </div>
       </div>
   </div>

    <div class="uwhr-header">
        <div class="row">
            <div class="col-xs-12">
                <h1 class="text-hide">University of Washington Human Resources</h1>
                <a class="uwhr-logo text-hide" href="<?php echo network_site_url(); ?>" title="University of Washington Human Resources Home">University of Washington Human Resources Home</a>

                <?php
                    echo $menus['dropdown'];
                    echo $menus['mobile-btn'];
                ?>
            </div>
        </div>
    </div>

    <?php echo $menus['mobile']; ?>

</header>
