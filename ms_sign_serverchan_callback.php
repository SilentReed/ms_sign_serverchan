<?php
function callback_init() {
    cron::set('sign_serverchan', 'plugins/ms_sign_serverchan/send.php', '0', '0', '0');
}

function callback_remove() {
    cron::del('sign_serverchan');
}
?>