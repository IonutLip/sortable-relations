<?php

namespace Bolt\Extension\Printi\SortableRelations;
//die('asd');
if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}
