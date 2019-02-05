<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
if (isset($_GET['logout'])) {
	session_destroy();
	header("location:index.php");
}
$host = "localhost";
$db = "diplom";
$charset = "utf8";
$user = "root";
$pass = "";
$pdo = new PDO("mysql:host=$host;dbname=$db; charset=$charset", $user, $pass);
// удалить админа
if (isset($_SESSION['user_id'])) {
	if (isset($_GET['del'],$_GET['id'])) {
	$stmt = $pdo->prepare("DELETE FROM user WHERE id=".$_GET['id']."");
	$stmt->execute();
	$del = $stmt->fetchAll(PDO::FETCH_ASSOC);
	header("location:index.php");
	}
}	
// удалить тему
if (isset($_SESSION['user_id'])) {
	if (isset($_GET['del'],$_GET['category_id'])) {
		// пытался использовать такую конструкцию, при пустых темах не работает ("DELETE category,main FROM category,main WHERE category.id=main.category_id AND category.id=".$_GET['category_id']."");
	$stmt = $pdo->prepare("DELETE FROM category WHERE id=".$_GET['category_id']."");
	$stmt->execute();
	$delCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt = $pdo->prepare("DELETE FROM main WHERE category_id=".$_GET['category_id']."");
	$stmt->execute();
	$delMain = $stmt->fetchAll(PDO::FETCH_ASSOC);
	header("location:index.php");
	}
}	
// авторизация-регистрация
if (isset($_POST['name'],$_POST['password'])) {
	$stmt = $pdo->prepare('SELECT id FROM user WHERE login= ?');
	$stmt->execute([$_POST['name']]);
	$id = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//авторизация
	if (!empty($id)) {
		$stmt = $pdo->prepare('SELECT id FROM user WHERE login= ? AND password= ?');
		$stmt->execute([$_POST['name'],$_POST['password']]);
		$id = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (empty($id)) {
		exit("Ошибка! Неверный пароль");
		}
		foreach ($id as $key => $value) {
			$_SESSION['user_id'] = $value['id'];
		}
	}
	//регистрация
	else {
		$stmt = $pdo->prepare("INSERT INTO user (login, password) VALUES ('".$_POST['name']."','".$_POST['password']."')");
		$stmt->execute();
		$registr = $stmt->fetchAll(PDO::FETCH_ASSOC);
		echo "Вы успешно зарегистрированы";
	}
}
// добавление вопроса
if (isset($_POST['user_question'],$_POST['user_category'],$_POST['user_mail'],$_POST['user_name'])) {
	$stmt = $pdo->prepare("INSERT INTO main (question, category_id, author, email)  VALUES ('".$_POST['user_question']."','".$_POST['user_category']."','".$_POST['user_name']."','".$_POST['user_mail']."')");
	$stmt->execute();
	$question = $stmt->fetchAll(PDO::FETCH_ASSOC);
}	
// получение данных вопросы и ответы
	$stmt = $pdo->prepare("SELECT * FROM main");
	$stmt->execute();
	$table = $stmt->fetchAll(PDO::FETCH_ASSOC);
//  получение категорий
	$stmt = $pdo->prepare("SELECT id,category FROM category");
	$stmt->execute();
	$category = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 	получение даныых из табл юзер
if (isset($_SESSION['user_id'])) {
	$stmt = $pdo->prepare('SELECT id,login,password FROM user');
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}	
// изменение пароля
if (isset($_SESSION['user_id'])) {
	if (isset($_POST['user_change_password'],$_POST['user_id'])) {
		$stmt = $pdo->prepare("UPDATE user SET password=".$_POST['user_change_password']." WHERE id=".$_POST['user_id']."");
		$stmt->execute();
		$upd = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//!!!Добавлять ли header чтобы обновлялась страница?
	}
}	
// добавление нового админа
if (isset($_SESSION['user_id'])) {
	if (isset($_POST['new_login'],$_POST['new_password'])) {
		$stmt = $pdo->prepare("INSERT INTO user (login,password) VALUES (:login,:password)");
		$stmt->execute(["login"=>$_POST['new_login'],"password"=>$_POST['new_password']]);
		$ins = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//!!!Добавлять ли header чтобы обновлялась страница?
	}
}	
// получение данных вопросы и ответы
	$stmt = $pdo->prepare("SELECT answer,category_id,toShow,category FROM main m INNER JOIN category c ON m.category_id=c.id");
	$stmt->execute();
	$describeCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
// добавление новой темы
	if (isset($_POST['new_category'])) {
		var_dump($_POST);
		$stmt = $pdo->prepare("INSERT INTO category (category) VALUES ('".$_POST['new_category']."')");
		$stmt->execute();
		$cat = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//!!!Добавлять ли header чтобы обновлялась страница?
	}
 ?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
	<?php if (!isset($_SESSION['user_id'])) {  ?>	
 	<h2>Интерфейс администратора</h2>
	<form action="" method="POST">
 		<p><label for="name">Введите логин </label><input type="text" name="name" id="name" required></p>
 		<p><label for="password">Введите пароль </label><input type="password" name="password" id="password" required></p>
 		<p><input type="submit" value="Войти/Зарегистрироваться"></p>
 	</form>
 	 <?php } ?>
 	 <?php if (isset($_SESSION['user_id'])) {  ?>
 		<p>Привет, <?php echo $_SESSION['user_id']; ?>!<a href="index.php?logout=true"> Выйти</a></p>
		<h3>Текущие администраторы</h3>
 			<table>
	 		<thead>
				<th>Логин</th>
				<th>Пароль</th>
				<th>Изменить пароль</th>
				<th>Удалить</th>
			</thead>
 		<?php foreach ($users as $users_value) : ?>
 			<tr>
 				<td><?=$users_value['login'] ?></td>
 				<td><?=$users_value['password'] ?></td>
 				<td>
 					<form action="" method="POST">
 						<input name="user_id" type="hidden" value="<?=$users_value['id'] ?>">
 						<input name="user_change_password" type="text" placeholder="введите новый пароль">
 						<input type="submit" value="Изменить пароль">
 					</form>
 				</td>
 				<td><a href="index.php?del=true&id=<?=$users_value['id'] ?>">удалить</a></td>
 			</tr>
 			<?php endforeach; ?>
			</table>
 		
 		<h3>Добавить нового администратора</h3>
 		<form action="index.php" method="POST">
 			<input name="new_login" type="text" placeholder="введите логин">
 			<input name="new_password" type="text" placeholder="введите пароль">
 			<input type="submit" value="создать">
 		</form>
	<h3>Текущие темы</h3>	
		<table>
	 		<thead>
				<th>Текущие темы</th>
				<th>Вопросов в теме</th>
				<th>Опубликовано</th>
				<th>Без ответа</th>
				<th>Удалить</th>
			</thead>
			<?php foreach ($category as $row) { ?>
 			<tr>
	 			<td><?= $row['category'] ?></td>

				<td>
					<?php $i=0; foreach ($describeCategory as $describeCategory_value) {
						if ($describeCategory_value['category'] == $row['category']) {
							$i++;
						}
					} echo $i;?>
				</td>
				<td>
					<?php $i=0; foreach ($describeCategory as $describeCategory_value) {
						if ($describeCategory_value['category'] == $row['category']) {
							if ($describeCategory_value['toShow'] == 1) {
								$i++;
							}
						}
					} echo $i;?>
				</td>
				<td>
					<?php $i=0; foreach ($describeCategory as $describeCategory_value) {
						if ($describeCategory_value['category'] == $row['category']) {
							if (empty($describeCategory_value['answer'])) {
								$i++;
							}
						}
					} echo $i;?>
				</td>
				<td><a href="index.php?del=true&category_id=<?=$row['id'] ?>">удалить</a></td>
 			</tr>
 			<?php } ?>
			</table>
			<p>добавить тему</p>
			<form action="" method="POST">
				<input name="new_category" type="text" placeholder="Название темы">
				<input type="submit" value="Создать тему">
			</form>
			<?php } ?>
	<h3>Вопросы</h3>
 	 <form action="" method="POST">
 	 	<p><label for="user_question">Введите свой вопрос </label><input type="text" name="user_question" id="user_question" required></p>
 	 	<label for="user_category">Выберите категорию </label>
 	 	<select name="user_category" id="user_category">
 	 		<?php foreach ($category as $row): ?>
				<option value="<?= $row['id'] ?>"><?= $row['category'] ?></option>
			<?php endforeach; ?>
 	 	</select>
 	 	<p><label for="user_mail">Введите свой e-mail </label><input type="email" name="user_mail" id="user_mail" required></p>
 	 	<p><label for="user_name">Введите свое имя </label><input type="text" name="user_name" id="user_name" required></p>
 	 	<p><input type="submit" value="Отправить"></p> 	 	
 	 </form>

 	<?php foreach ($category as $row) { ?>
		<h2><?= $row['category'] ?></h2>
		<?php foreach ($table as $value) { ?>
			<?php if (($row['id'] == $value['category_id']) && ($value['toShow'] == 1)): ?>
				<input class="hide" id="<?= $value['id'] ?>" type="checkbox" >
				<label for="<?= $value['id'] ?>"><?= $value['question'] ?></label>
				<div>Здесь будет ответ</div>
				<br>
			<?php endif ?>
		<?php } ?> 	
 	<?php } ?> 
</body>
</html>