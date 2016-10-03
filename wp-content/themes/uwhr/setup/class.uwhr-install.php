<?php
/**
 * Theme Install
 *
 * Install the theme
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Install {

    function __construct() {
        add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );
        add_action( 'wp_head', array( $this, 'google_analytics' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'login_styles' ) );

        // Remove some weird WP things
        remove_action( 'wp_head', 'rsd_link');
        remove_action( 'wp_head', 'wp_generator');
        remove_action( 'wp_head', 'wlwmanifest_link');
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
    }

    /**
     * Theme Setup
     *
     * Add theme support for various parts of the WP site
     * Set a default header image
     * Update image option sizes
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function theme_setup() {
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'post-formats', array( 'link' ) );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'html5', array( 'caption', 'gallery' ) );

        $args = array(
            'width'         => 1600,
            'height'        => 300,
            'default-image' => get_template_directory_uri() . '/assets/images/defaults/header.jpg',
        );
        add_theme_support( 'custom-header', $args );

        update_option( 'thumbnail_size_w', 100 );
        update_option( 'thumbnail_size_h', 100 );
        update_option( 'medium_size_w', 600 );
        update_option( 'large_size_w', 900 );
    }

    /**
     * Google Analytics
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function google_analytics() {
    ?>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-5714863-19', 'auto');
            ga('send', 'pageview');
        </script>
    <?php
    }

    /**
     * Login Styles
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.6.0
     * @package UWHR
     */
    function login_styles() {
        ?>
            <style type="text/css">
                body.login { background-color: #ece9e2 !important; }

                .login form {
                    background-color: #ece9e2 !important;
                    border: none !important;
                    -webkit-box-shadow: none !important;
                    box-shadow: none !important;
                    margin-top: 0 !important;
                }

                .login h1 a {
                    background: url('<?php echo get_template_directory_uri() ?>/assets/svg/uwhr-sprite.svg') no-repeat -10px 0 transparent !important;
                    bottom: 0 !important;
                    left: 1rem !important;
                    height: 3.7rem !important;
                    width: 18rem !important;
                    background-position: -14px -74px !important;
                    -webkit-background-size: 600px !important;
                    -moz-background-size: 600px !important;
                    -o-background-size: 600px !important;
                    background-size: 600px !important;
                    transition: none !important;
                }

                .login h1 a:hover,
                .login h1 a:focus {
                    background-position: -14px 0 !important;
                }

                .login #login_error, .login .message {
                    border-left: 4px solid #85754d !important;
                }

                .login label {
                    font-family: 'Encode Sans Compressed', sans-serif !important;
                    color: #3D3D3D !important;
                    font-size: 16px !important;
                }

                .login #backtoblog a,
                .login #nav a,
                .login h1 a {
                    text-decoration: none !important;
                    color: #3D3D3D !important;
                }

                .login form .input,
                .login input[type=text] {
                    border-radius: 4px !important;
                    border-color: #85754d !important;
                    border-width: 2px !important;
                }

                .login form .input:hover,
                .login form .input:focus,
                .login input[type=text]:hover,
                .login input[type=text]:focus {
                    -webkit-box-shadow: 0 0 2px rgba(113, 100, 65,.8) !important;
                    box-shadow: 0 0 2px rgba(113, 100, 65,.8) !important;
                }

                .wp-core-ui .button-primary {
                    background: #85754d !important;
                    border-color: #85754d !important;
                    -webkit-box-shadow: none !important;
                    box-shadow: none !important;
                    color: #fff !important;
                    text-decoration: none !important;
                    text-shadow: none !important;
                }
                .wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover {
                    background: #716441 !important;
                    border-color: #716441 !important;
                    color: #fff !important;
                }

                @keyframes popin {
                  0% { bottom: 0px; }
                  100% { bottom: -130px; }
                }

                @keyframes popout {
                  0% { bottom: -130px; }
                  100% { bottom: 0px; }
                }

                .dubs {
                    position: absolute;
                    bottom: -130px;
                    right: 10px;
                    background: url('<?php echo get_template_directory_uri() ?>/assets/images/login.png') no-repeat 0 0 transparent;
                    background-size: contain;
                    height: 160px;
                    width: 160px;
                    transition: all 200ms ease-in-out;
                    cursor: pointer;

                    animation: popin 300ms 1 cubic-bezier(.64,.22,.27,.83);
                }

                .dubs.bob { bottom: -120px; }

                .dubs:hover {
                    animation: popout 300ms 1 cubic-bezier(.33,.07,.5,1.31) forwards;
                }

                .dubs.clicked {
                    background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/login-active.png');
                }

                .dubs .howl {
                    position: absolute;
                    bottom: 140px;
                    right: 155px;

                    font-family: 'Encode Sans Compressed', sans-serif;
                    color: #85754d;
                    font-size: 22px;
                    font-weight: 900;

                    display: none;
                }

                .dubs .howl.active {
                    display: block;
                }
            </style>

            <script type="text/javascript">
                window.onload = function() {
                    var dubs = document.createElement('div');
                    var howl = document.createElement('p');

                    dubs.className = 'dubs';
                    howl.className = 'howl';

                    document.body.appendChild(dubs);
                    dubs.appendChild(howl);

                    var interval = setInterval(function(){
                        dubs.className += ' bob';
                        setTimeout(function() {
                            dubs.className = 'dubs';
                        }, 150);
                    }, 3000);

                    dubs.addEventListener('click', function(e) {
                        clearInterval(interval);

                        var that = this;
                        that.className += ' clicked';

                        howl.innerHTML = '';

                        setTimeout(function() {
                            howl.className += ' active';
                            printLetterByLetter(howl, 'Hoooowl!', 100);
                        }, 100);

                        setTimeout(function() {
                            that.className = 'dubs';
                            howl.className = 'howl';
                        }, 2000);
                    });

                    function printLetterByLetter(destination, message, speed){
                        var i = 0;
                        var interval = setInterval(function(){
                            destination.innerHTML += message.charAt(i);
                            i++;
                            if (i > message.length){

                            }
                        }, speed);
                    }
                };
            </script>
        <?php
    }
}
