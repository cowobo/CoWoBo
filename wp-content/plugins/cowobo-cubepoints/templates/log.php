<ul class="log">
    <?php
    foreach($log as $logitem) :
    ?>
        <li class="<?php echo $logitem->type; ?>">

            <div class="log-entry">
                <span class="points-tag">+<?php echo $logitem->points; ?></span>
                <p>
                    <?php do_action('cowobo_logs_description', $logitem->type, $logitem->uid, $logitem->points, $logitem->data); ?>
                    <span class='time-since'><?php echo cp_relativeTime($logitem->timestamp); ?></span>
                </p>
            </div>

        </li>

    <?php
    endforeach;
    ?>
</ul>