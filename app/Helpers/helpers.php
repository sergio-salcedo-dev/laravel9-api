<?php

if (!function_exists('isDevEnvironment')) {
    function isDevEnvironment(): bool
    {
        return App::environment('local', 'testing');
    }
}
