<?php

if (! empty($greeting)) {
    echo $greeting, "\n\n";
} else {
    echo ($level == 'error'
            ? trans( 'notifications.greeting_error' )
            : trans( 'notifications.greeting_hello' ) ), "\n\n";
}

if (! empty($introLines)) {
    echo implode("\n", $introLines), "\n\n";
}

if (isset($actionText)) {
    echo "{$actionText}: {$actionUrl}", "\n\n";
}

if (! empty($outroLines)) {
    echo implode("\n", $outroLines), "\n\n";
}

echo trans( 'notifications.regards' ), ",\n";
echo setting( 'instance_title' ), "\n";
