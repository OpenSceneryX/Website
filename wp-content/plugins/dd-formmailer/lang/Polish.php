<?php

// Dagon Design Form Mailer v5
// http://www.dagondesign.com
// Polish version by Pawel Lipiec
// http://polskiblogger.pl/

/* Jezeli wiadomosci z formulaza sa blednie kodowane
Znajdz ta linie kodu:
	
	$msg .= 'Content-Type: text/plain; charset="iso-8859-1"' . PHP_EOL;

i zamien ja na:
	
	$msg .= 'Content-Type: text/plain; charset="UTF-8"' . PHP_EOL;
*/

// Polish Language Settings


define('DDFM_SUBMITBUTTON', 'Wy&#347;lij');

define('DDFM_CREDITS', 'Autor skryptu');

define('DDFM_CONFIRMPASS', 'Potwierd&#378;');

define('DDFM_REQUIREDTAG', '*');

define('DDFM_ERRORMSG', 'B&#322;&#281;dy!');

define('DDFM_MAXCHARLIMIT', 'limit znak&#243;w dla');

define('DDFM_MISSINGFIELD', 'Wype&#322;nij wymagane pola ');

define('DDFM_INVALIDINPUT', 'Nie prawid&#322;owy wpis');

define('DDFM_INVALIDEMAIL', 'B&#322;&#281;dny adres e-mail');

define('DDFM_INVALIDURL', 'Nie poprawny adres URL');

define('DDFM_NOMATCH', 'Nie psauj&#261;ce pola');

define('DDFM_MISSINGVER', 'Podaj kod weryfikacyjny');

define('DDFM_NOVERGEN', 'Nie wygenerowano kodu weryfikacyjnego');

define('DDFM_INVALIDVER', 'Niepprawny kod weryfikacyjny');

define('DDFM_MISSINGFILE', 'Wype&#322;nij wymagane pola');

define('DDFM_FILETOOBIG', 'Zbyt du&#380;y plik:');

define('DDFM_ATTACHED', 'Do&#322;&#261;czone pliki');

define('DDFM_INVALIDEXT', 'Niepoprawny rodzaj pliku:');

define('DDFM_UPLOADERR', 'B&#322;&#261;d uploadu:');

define('DDFM_SERVERERR', '<p>B&#322;&#261;d wysy&#322;ania wiadomo&#347;ci!</p>');

define('DDFM_GDERROR', '<p>Nie wykryto bibliotek GD! GD jest konieczne aby wygenerowa&#263; kod weryfikacyjny.</p>');


?>