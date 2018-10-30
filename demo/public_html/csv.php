<?php
	define( 'PASSWORD', 'demopassword' ); // For accesssing this script

	require_once '../conf/remotedb-settings.php';
	require_once '../../RemoteDb/RemoteDbCsvReader.php';

	$remoteDbCsvReader = new RemoteDbCsvReader(DB_PATH, DB_TABLE, CUSTOM_ENTRY_FIELDS, CUSTOM_REQUEST_FIELDS);
	$remoteDbCsvReader->output(PASSWORD);