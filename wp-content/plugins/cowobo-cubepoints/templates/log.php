<div class="tab">
    <h3>Activities</h3>

    <ul class="log">
        <?php
        foreach($log as $logitem) :
        ?>
            <li class="<?php echo $logitem->type; ?>">

                <div class="log-entry">
                    <span class='points'>+<?php echo $logitem->points; ?></span>
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
</div>

<style>
    ul.log {
        list-style: none;
    }
    div.log-entry {
        color: #888;
        line-height: 220%;
    }
    div.log-entry p {
        margin: 5px 0;
    }
    div.log-entry span.time-since {
        color: #aaa;
        font-weight: bold;
    }
    div.log-entry span.points {
        width:30px;
        display: block;
        height: 30px;
        border-radius: 15px;
        vertical-align: middle;
        text-align: center;
        float: left;
        background-color: #659417;
        color: white;
        margin-right: 10px;
    }

</style>