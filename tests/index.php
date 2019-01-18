<?php
/**
* Тест валидации.
*/

use Evas\Validate\Field;
use Evas\Validate\Fieldset;

include 'autoload.php';

// custom field classes
// кастомные классы полей

class IdField extends Field
{
    public $type = 'int';
    public $typeChecked = true;
    public $pattern = '/^\d+$/';
}

class EmailField extends Field
{
    public $min = 8;
    public $max = 60;
    public $pattern = '/^.{2,}@.{2,}\..{2,}$/';
}

class PasswordField extends Field
{
    public $min = 6;
    public $max = 30;
    public $lengthError = 'Пароль должен быть длиной от 6 до 30 символов';
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>evas-validate tests</title>
    <style type="text/css">
        * {box-sizing: border-box; margin: 0; padding: 0; vertical-align: top;}
        body {background: #eee; font-family: 'Open Sans';}
        body, input, button {font-size: 15px;}
        .container {margin: 0 auto; max-width: 300px;}
        .errors {background: #faa; border-radius: 20px; color: #800; margin: 20px 0; padding: 10px 15px;}
        form {align-items: flex-start; background: #fff; border-radius: 20px; box-shadow: 0 2px 10px 1px #00000020; display: flex; flex-direction: column; justify-content: flex-start; margin: 20px 0; max-width: 300px; padding: 20px;}
        p, input, button {margin: 5px 0;}
        input {background: #fff; border: solid #aaa 1px; border-radius: 4px; padding: 6px 10px; width: 100%;}
        button {background: #64d; border: none; border-radius: 4px; color: #fff; cursor: pointer; padding: 7px 11px;}
        button:hover {background: #53c;}
    </style>
</head>
<body>

<div class="container">

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // make fieldset
    // создаем набор полей
    $fieldset = new Fieldset([
        'id' => new IdField(['required' => true]),
        'email' => new EmailField(['required' => true]),
        'password' => new PasswordField(['equal' => 'password_repeat']),
    ]);

    // id to int
    // приведение id к типу
    // $id = &$_POST['id'];
    // if ($id) {
    //     $id = intval($id);
    // }

    // валидация полей
    if (!$fieldset->isValid($_POST, true)) { ?>

        <div class="errors">
        <?php foreach ($fieldset->errors as $name => $error): ?>
            <p class="error"><?= $error ?></p>
        <?php endforeach; ?>
        </div>

    <?php } else {
        // do something with data
        // что-то делаем с данными
    }
}
?>

    <form method="POST">
        <p>Вход</p>
        <input type="number" name="id" placeholder="id" value="<?= $_POST['id'] ?? null ?>">
        <input type="text" name="email" placeholder="Email" value="<?= $_POST['email'] ?? null ?>">
        <input type="password" name="password" placeholder="Пароль" value="<?= $_POST['password'] ?? null ?>">
        <input type="password" name="password_repeat" placeholder="Повторите пароль" value="<?= $_POST['password_repeat'] ?? null ?>">
        <button type="submit">Войти</button>
    </form>


</div><!-- end of .container -->

</body>
</html>
