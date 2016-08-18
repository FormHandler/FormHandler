FormHandler
======

This FormHandler is a PHP solution to generate form fields and validate them.
Making forms is in general a time-taking job. In this package we try to offer
a solution so that making forms is easy. 

This package is set up so that you [todo]

Some assumption you have to know before using this package:

- All HTML attributes are written in html with a double quote ("). This means
  that if you set an attribute, you have to escape the double quotes to keep the 
  html valid.

- Usage of brackets in names is supported, but only if you supply a name within 
  the brackets. So, this is WRONG:
<?php
// this is wrong, there is no way for us to know the key..
$form -> textField("address[]" );
$form -> textField("address[]" );
?>

This is the correct way:
<?php
// this is wrong, there is no way for us to know the key..
$form -> textField("address[0]" );
$form -> textField("address[1]" );
?>
  
  
--==--
nabaztag.txt
--==--
nabaztag.txt
--==--
nabaztag.txt
--==--
nabaztag.txt