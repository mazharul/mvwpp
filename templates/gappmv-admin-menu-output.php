<div class="wrap">
    <h2>GA popular posts seetings page</h2>
    <form action="options.php" method="POST">
    <?php 

        settings_fields('gappmv_setting_group'); 
        $_pS = "wp-ga-popular-posts";

        setting_fields('gappmv_setting_group');
        do_settings_action($_pS);

        submit_button();

    ?>
    </form>
</div>