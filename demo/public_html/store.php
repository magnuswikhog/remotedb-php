<?php

	define( 'PASSWORD', 'demopassword' ); // For accesssing this script

	require_once '../conf/remotedb-settings.php';
	require_once '../../RemoteDb/RemoteDbWriter.php';

	$remoteDbWriter = new RemoteDbWriter(DB_PATH, DB_TABLE, CUSTOM_ENTRY_FIELDS, CUSTOM_REQUEST_FIELDS);
	$remoteDbWriter->store(PASSWORD, true);