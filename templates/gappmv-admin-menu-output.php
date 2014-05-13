<!--<div class="wrap">
    <h2>GA popular posts seetings page</h2>
    <form action="options.php" method="POST">
    <?php 

        settings_fields('gappmv_setting_group'); 
        $_pS = "wp-ga-popular-posts";

        //setting_fields('gappmv_setting_group');
        do_settings_sections($_pS);

        submit_button();

    ?>
    </form>
</div>-->

<div class="wrap">
    <h2>GA popular posts seetings page</h2>

    <form name="wpmv_gapp" method="post" action="">
        <input type="hidden" name="gappmv_login_type" value="oauth" />

        <p>
        In order to use the plugin, you have to login and authenticate your google account.
        </p>
        

        <p class='submit'>
            <input type="submit" name="SubmitLogin" value="Authenticate me.">
        </p>


    </form>
</div>