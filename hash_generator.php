<?php

// Пароль, который мы хотим захешировать
// $passwordToHash = 'password123';
$passwordToHash = 'pas5683s';

// Генерируем хеш с использованием стандартных настроек твоего PHP
$hash = password_hash($passwordToHash, PASSWORD_DEFAULT);

// Выводим результат на экран
echo "Пароль: " . $passwordToHash . "<br>";
echo "Сгенерированный хеш:<br>";
echo '<textarea rows="4" cols="70">' . $hash . '</textarea>';

echo "<br><br>Проверка (должно быть TRUE):<br>";
var_dump(password_verify($passwordToHash, $hash));

?>