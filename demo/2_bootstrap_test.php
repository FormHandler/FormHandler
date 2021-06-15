<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$form = new \FormHandler\Form();
$form->setRenderer(new \FormHandler\Renderer\Bootstrap3Renderer());

$form->textField('email')
    ->setTitle('Email')
    ->setPlaceholder('Email')
    ->setHelpText('Please enter your email address');

$form->passField('password')
    ->setTitle('Password')
    ->setPlaceholder('Password');

$form->textArea('message');

$form->submitButton('submit', 'Submit');

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Bootstrap 101 Template</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <h1>Hello, world!</h1>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>

        <?php echo $form; ?>
        <?php echo $form('email'); ?>
        <?php echo $form('password'); ?>
        <?php echo $form('message'); ?>

        <div class="form-group">
            <label for="exampleInputFile">File input</label>
            <input type="file" id="exampleInputFile">
            <p class="help-block">Example block-level help text here.</p>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox"> Check me out
            </label>
        </div>
        <?php echo $form('submit') ?>
        <?php echo $form->close(); ?>

    </body>
</html>