<?php

use FormHandler\Field\TextField;
use FormHandler\Form;

require dirname(__DIR__) . '/vendor/autoload.php';

// Container for our message
$message = '';

// Create a new form
$form = new Form();

// Create a new field with a custom validator
$form->textField('terms')->setPlaceholder('agree?')->addValidator(function (TextField $field) {
    if ($field->getValue() == "agree") {
        return true;
    } else {
        return 'You have to enter "agree"';
    }
});

// Create a submit button
$form->submitButton('submit', 'Submit');

// When the form was submitted
if ($form->isSubmitted($reason)) {
    // When the form was marked as valid.
    if ($form->isValid()) {
        // Here, the form was correctly filled in.
        $message = "Thanks! I also agree!";
    } else {
        // Form was not correctly filled in.
        $message = "Form was not valid. Reason:\n " . implode("\n", $form->getValidationErrors());
    }
} else {
    // The form was not submitted. Also display why
    $message = "Form is not submitted. Reason: $reason";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>This is pretty easy!</h1>
<?php echo $form; ?>
<?php echo $form('terms'); ?><br/>

<?php echo $form('submit') ?>
<?php echo $form->close(); ?>

<b style='color:red;'>
    <?php if (!empty($message)) {
        echo nl2br(htmlentities($message));
    } ?>
</b>

</body>
</html>

