<?php

class {$model} extends Object {
    // any object specific code here relating to a single item
}

class {$model}s extends Table {
    protected $meta = array(
        'columns' => array(
            'name' => array(
                'title'    => 'Name',
                'type'     => 'text',
                'required' => true,
            ),
        ),
    );
}
