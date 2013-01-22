<div class="tab">
    <h3>Activities</h3>

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

    span.points-tag{
        float: left;
        height: 24px;
        line-height: 24px;
        position: relative;
        font-size: 11px;

        width: 18px;
        margin: 5px 10px 0 13px;
        padding: 0 10px 0 12px;
        background: #2b832c;
        color: #fff;
        text-decoration: none;
        -moz-border-radius-bottomright: 4px;
        -webkit-border-bottom-right-radius: 4px;
        border-bottom-right-radius: 4px;
        -moz-border-radius-topright: 4px;
        -webkit-border-top-right-radius: 4px;
        border-top-right-radius: 4px;
    }
    span.points-tag:before{
        content:"";
        float:left;
        position:absolute;
        top:0;
        left:-12px;
        width:0;
        height:0;
        border-color:transparent #2b832c transparent transparent;
        border-style:solid;
        border-width:12px 12px 12px 0;
    }
    span.points-tag:after{
        content:"";
        position:absolute;
        top:10px;
        left:0;
        float:left;
        width:4px;
        height:4px;
        -moz-border-radius:2px;
        -webkit-border-radius:2px;
        border-radius:2px;
        background:#fff;
        -moz-box-shadow:-1px -1px 2px #004977;
        -webkit-box-shadow:-1px -1px 2px #004977;
        box-shadow:-1px -1px 2px #004977;
    }

</style>