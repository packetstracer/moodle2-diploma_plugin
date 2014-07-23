<?php

$handlers = array (
		'user_enrolled' => array (
				'handlerfile'      => '/mod/diploma/historylib.php',
				'handlerfunction'  => 'history::user_enrolled',
				'schedule'         => 'instant',
				'internal'         => 1,
		),

		'user_unenrolled' => array (
				'handlerfile'      => '/mod/diploma/historylib.php',
				'handlerfunction'  => 'history::user_unenrolled',
				'schedule'         => 'instant',
				'internal'         => 1,
		),
);