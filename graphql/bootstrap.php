<?php
// processPostData() in doPOST.php calls refreshPage() + exit when there is no
// formName in POST and no URL query string. Adding a dummy query string makes
// it take the URL-parsing branch instead, which is a no-op for unknown params.
// Provide all keys that updateSessionByUrl() reads so no undefined-key
// warnings are emitted; values of 0 cause no session changes.
$_SERVER['REQUEST_URI'] = '/graphql.php?e=0&t=0&m=0&r=0&s=0';

require_once dirname(__DIR__) . '/includes/config.php';
