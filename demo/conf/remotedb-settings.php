<?php

	define( 'DB_PATH', "../data/data.db" );
	
	define( 'DB_TABLE', 'data' );

	define( 'CUSTOM_ENTRY_FIELDS', [
			 't' => ['timestamp', 'NUMERIC', 0],
			 'm' => ['message', 'TEXT', 'default message'],
	]);

	define( 'CUSTOM_REQUEST_FIELDS', [
			 'static' => ['static', 'TEXT', ""],
	]);

