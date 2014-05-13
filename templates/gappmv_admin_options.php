<div class="wrap" >

<?php

if(sizeof($accounts) != 0)
{
  $current_account_id = isset($_POST['gappmv_account_id']) ? $_POST['gappmv_account_id'] : get_option('gappmv_account_id') !== false ? get_option('gappmv_account_id') : '';
}

?>

    <form action="" method="post">

        <p>
            
            <span>Available Accounts:</span>

            <span>
                
            <?php
           
              if(sizeof($accounts) == 0)
              {
                echo '<span id="gappmv_account_id">No accounts available.</span>';
              }
              else
              {
                echo '<select id="gappmv_account_id" name="gappmv_account_id">';
                foreach($accounts as $account_id => $account_name)
                {
                  echo '<option value="' . $account_id . '" ' . ($current_account_id == $account_id ? 'selected' : '') . '>' . $account_name . '</option>';
                }
                echo '</select>';
              }
          
            ?>

            </span>
        </p>

        <p class="submit">
          <input type="submit" name="SubmitOptions" value="Save Changes" />
        </p>


    </form>

</div>