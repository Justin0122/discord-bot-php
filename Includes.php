<?php

foreach (glob(__DIR__.'/src/Helpers/*.php') as $filename) {
    include $filename;
}

foreach (glob(__DIR__.'/src/Scheduler/*.php') as $filename) {
    include $filename;
}

foreach (glob(__DIR__.'/src/Events/*.php') as $filename) {
    include $filename;
}