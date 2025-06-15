<?php
    require_once __DIR__ . '/check_auth.php';

    require_once __DIR__ . '/config.php';

    $host = $HOST;
    $dbname = $DBNAME;
    $db_username = $DB_USERNAME;
    $db_password = $DB_PASSWORD;

    $isAuth = validateAuth();
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        exit(json_encode([
            'status' => -1,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]));
    }

    $stmt = $pdo->prepare("
        SELECT 
            stories.*, 
            users.username AS author_name,
            users.about AS author_about
        FROM stories
        LEFT JOIN users ON stories.id_author = users.id
        ORDER BY RAND() 
        LIMIT 15
    ");
    $stmt->execute();
    $storyes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $my_story = [];
    $search_story = $storyes;

    if (!empty($_SESSION['user_id'])) {
        $stmt_my = $pdo->prepare("SELECT * FROM stories WHERE id_author = :id_author");
        $stmt_my->bindParam(':id_author', $_SESSION['user_id']);
        $stmt_my->execute();
        
        $my_story = $stmt_my->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($_GET['search_story'])){
        $name = (string)$_GET['search_story'];

        $users = [];

        try {
            $stmt_my = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt_my->bindParam(':username', $name);
            $stmt_my->execute();
            
            $users = $stmt_my->fetchAll(PDO::FETCH_ASSOC);    

            $search_story = []; // Инициализация пустого массива

            foreach($users as $user) {
                // Получаем истории для текущего автора
                $stmt_stories = $pdo->prepare("SELECT * FROM stories WHERE id_author = :id_author");
                $stmt_stories->bindParam(':id_author', $user['id']);
                $stmt_stories->execute();
                
                $stories = $stmt_stories->fetchAll(PDO::FETCH_ASSOC);
                
                // Добавляем имя автора к каждой истории
                foreach ($stories as $story) {
                    $story['author_name'] = $user['username'];
                    $search_story[] = $story; // Добавляем в массив
                }
            }

            echo json_encode(['success' => 'searching', 'stories' => $search_story]);
            exit;
        }  catch (PDOException $e) {
            exit(json_encode([
                'error' => $e->getMessage()
            ]));
        }
    }

    if (isset($_GET['item_id'])) {
        $itemId = (int)$_GET['item_id'];
        
        // Ищем нужный элемент
        foreach ($storyes as $story) {
            if ($story['id'] === $itemId) {
                header('Content-Type: application/json');
                echo json_encode($story);
                exit;
            }
        }
        
        echo json_encode(['error' => 'Item not found']);
        exit;
    }
?>

<html lang="ru" style="height: 100%;">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Как сбежать от кошки</title>	
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/stylePopup.css">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
	</style>
</head>
<body style="height: 100%;">

    <div id="cookie-popup">
        <p>Мы используем файлы cookie для улучшения работы сайта. </p>
        <div class="cookie-buttons">
          <button id="accept-cookies" class="cookie-btn">Принять</button>
          <button id="reject-cookies" class="cookie-btn">Отклонить</button>
        </div>
      </div>

	<div style="background-color: #E6D5C3;padding: 15px 0 15px 0;">
        <div style="display: flex; align-items: center;">
            <!-- Левый блок -->
            <div style="flex: 1; display: flex; justify-content: flex-start; margin-left: 35px;">
                <a href="#Catalog" style="text-decoration: none; font-size: 22px; cursor: pointer; color: #000000;" id="catalogLink"><strong>Каталог</strong></a>
            </div>
            
            <!-- Центральный блок -->
            <div style="text-align: center;">
                <h1 style="margin: 0 0 1px 0;">Интересные истории</h1>
                <p style="margin: 0;">
                <a href="#AboutAthtor" style="text-decoration: none; display: flex; justify-content: center; font-size: 18px; cursor: pointer; color: #000000;">Об авторе</a>
                </p>
            </div>
            
            <!-- Правый блок -->
            <div style="flex: 1; display: flex; justify-content: flex-end; margin-right: 35px;">
                <?php if (!$isAuth): ?>
                    <a href="#Login" style="text-decoration: none; font-size: 22px; cursor: pointer; color: #000000;" id="registrationLink"><strong>Вход</strong></a>
                    <p style="display: none" id="historyCreator"></p>
                <?php else: ?>
                    <a href="#historyCreator" style="text-decoration: none; font-size: 22px; cursor: pointer; color: #000000; margin: 5px" id="historyCreator"><strong>Написать историю</strong></a>
                    <div onmouseover="menu.style.display='block'" onmouseout="menu.style.display='none'" style="font-size: 22px; color: #000000; margin: 5px">
                        <strong><?php echo $_SESSION['username'] ?? '?'; ?></strong>
                        <div id="menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid gray; padding:5px 0; white-space:nowrap; width:auto;">
                            <div style="padding:0 5px;">
                                <button id="buttonOutAccount" style="display:block; width:100%; font-size:20px; color:#000; text-align:center; padding:8px 10px; border:none; background:none; border-radius:0; cursor:pointer; transition:background 0.2s;"
                                        onmouseover="this.style.background='#f0f0f0'" 
                                        onmouseout="this.style.background='none'">Выйти</button>
                                <div style="border-top:1px solid #eee; margin:2px 0;"></div>
                                <button id="buttonRecreateAccount" style="display:block; width:100%; font-size:20px; color:#000; text-align:center; padding:8px 10px; border:none; background:none; border-radius:0; cursor:pointer; transition:background 0.2s;"
                                        onmouseover="this.style.background='#f0f0f0'" 
                                        onmouseout="this.style.background='none'">Редактировать</button>
                                <div style="border-top:1px solid #eee; margin:2px 0;"></div>
                                <button id="buttonDeleteAccount" style="display:block; width:100%; font-size:20px; color:#000; text-align:center; padding:8px 10px; border:none; background:none; border-radius:0; cursor:pointer; transition:background 0.2s;"
                                        onmouseover="this.style.background='#f0f0f0'" 
                                        onmouseout="this.style.background='none'">Удалить</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
	</div>

	<div style="background-color: #F9F5F0; border: none; outline: none; line-height: 1;">
		<div class="div_content" id="id_div_content" style="max-width: 800px; text-align: left; background-color: 	#EFEAE4; margin: auto; margin-top: 0px; margin-bottom: 0px; padding: 15px;">           
            <div id="creatorStory" style="overflow: auto;">
                <input type="text" id="storyTopickСreator" placeholder="Название истории" style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; font-size:24px; border:1px solid #ddd; border-radius:6px; outline:none;">
                <textarea placeholder="Текст истории" id="storyTextСreator" style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; font-size:22px; border:1px solid #ddd; border-radius:6px; min-height:700px; outline:none; resize:vertical;"></textarea>

                <button id="listStory" style="font-size:22px; float:left; margin-top:10px; padding:10px 20px; background: #E6D5C3; color:white; border:none; border-radius:6px; cursor:pointer; transition:background 0.3s;">
                    <p style="color: black">список историй</p>
                </button>
                
                <button id="postStory" style="font-size:22px; float:right; margin-top:10px; padding:10px 20px; background: #4CAF50; color:white; border:none; border-radius:6px; cursor:pointer; transition:background 0.3s;">
                    <p>Сохранить</p>
                </button>
            </div>

            <div id="storyListContainer">
                <div style="display: flex;">
                    <input id="searchAuthtoInput" type="text" placeholder="Поиск по автору" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px 0 0 4px; outline: none; flex-grow: 1; font-size: 14px;">
                    <button id="searchAuthtoButton" style="background-color:  #E6D5C3; height: 40px; margin-left:10px; color: #333; border: none; padding-top: 0px; padding-bottom: 0px; padding-left: 5px; padding-right: 5px; cursor: pointer; transition: background-color 0.2s;"><p style="font-size: 20px; padding: 0px; margin: 0px;">Найти</p></button>
                </div>
                <?php foreach ($storyes as $story): ?>
                    <div class="storyDivUkosatel" style="margin-bottom: 9px; margin-top: 9px" data-id="<?= $story['id']?>">
                        <h2><?=$story['title']?></h2>
                        <p style="font-size: 18px;"><em>Автор: <?=$story['author_name']?></em></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="storyContentContainer" class="hidden">
                <h2 id="storyTitle" style="text-align: center; margin-top: 5px; margin-bottom: 5px;"></h2>
                <div id="storyText"></div>
                <div>
                    <h6 style="text-align: center; margin-top: 5px; margin-bottom: 5px; font-size: 19px;">об авторе истории</h6>
                    <p style="font-size: 19px; margin: 3px;">
                        <span>Никнейм автора: </span>
                        <span id="aothorStoryUsername"></span>
                    </p>
                    <p style="font-size: 19px; margin: 3px;">
                        <span>Об авторе: </span>
                        <span id="aothorStoryAbout"></span>
                    </p>
                </div>
            </div>

            <div id="searchStoryes">
            </div>

            <div id="MyStoryes">
                <?php foreach ($my_story as $story): ?>
                    <div class="storyDivUkosatel" style="margin-bottom: 9px; margin-top: 9px; display: flex; align-items: center; justify-content: space-between;" data-id="<?= $story['id']?>">
                        <h2 style="margin: 0; padding-right: 15px;"><?=$story['title']?></h2>
                        <button class="deleteStoryes" data-id="<?= $story['id']?>" style="background-color: #ff4444; color: white; border: none; border-radius: 15px; padding: 6px 12px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; min-width: 80px; height: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">Удалить</button>
                    </div>
                <?php endforeach; ?>
            </div>       
		</div>
	</div>

    <div id="popup" style="text-align: center; ">
        <span class="close-btn" id="closebutton">✕</span>

        <div id="StateRegister">
            <form>            
                <input type="text" id="textfieldusernameRegister" style="font-size: 18px;" placeholder="Name" required>
                <input type="email" id="textfieldEmailRegister" style="font-size: 18px;" placeholder="Email" required>
                <textarea id="textaboutuserRegister" style="font-size: 18px; height: 100px; margin: 3px;"></textarea>
                <input type="password" id="textfieldPassword1Register" style="font-size: 18px;" placeholder="Пароль" required>
                <input type="password" id="textfieldPassword2Register" style="font-size: 18px;" placeholder="Повторите пароль" required>
                <button type="submit" id="buttonRegister" style="padding: 3px; background-color: #E6D5C3"><p style="font-size: 18px;">зарегестрироваться</p></button>
            </form>
        </div>

        <div id="StateSingIn">
            <form>
                <input type="text" id="textfieldLoginSignIn" placeholder="Логин" style="font-size: 18px;" required>
                <input type="password" id="textfieldPasswordSignIn" placeholder="Пароль" style="font-size: 18px;" required>
                <button type="submit" id="buttonSingIn" style="padding: 3px; background-color: #E6D5C3"><p style="font-size: 18px;">Вход</p></button>
                <a id="linkRegister" style="font-size: 18px; cursor: pointer;">зарегестрироваться</a>
            </form>
        </div>

        <p style="color: red;" id="infoRegisterAray"></p>
    </div>

    <div id="popupdeleteuser" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.2);width:300px;">
            <!-- Крестик для закрытия -->
            <span id="closebuttonpopupdeleteuser" style="position:absolute;top:5px;right:5px;cursor:pointer;font-size:20px;margin-bottom:30px;margin-right:10px;">✕</span>
            
            <input 
            type="password" 
            id="textfieldPasswordForDeleteUser" 
            style="font-size:18px;padding:8px;margin-bottom:10px;width:100%;box-sizing:border-box;border:1px solid #ccc;border-radius:4px;margin-top:16px;" 
            placeholder="Повторите пароль" 
            required
            >
            <button 
            id="buttonDeleteUser" 
            style="font-size:18px;padding:8px 16px;background-color:#E6D5C3;border:none;border-radius:4px;cursor:pointer;width:100%;"
            >
            Подтвердить удаление
            </button>
        </div>
    </div>

    <div id="popuprecreatoruser" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background-color:white;border-radius:8px;width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;">
                <h3 style="margin:0;font-size:22px;">Изменение характеристик аккаунта</h3>
                <span id="closepopuprecreatoruser" style="font-size:24px;cursor:pointer;color:#888;">✕</span>
            </div>
            
            <div style="padding:20px;">
                <div style="margin-bottom:16px;">
                    <input type="text" id="popuprecreatoruserusername" value="<?php echo $_SESSION['username']; ?>" placeholder="Введите новое имя" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;font-size:22px;">
                </div>
                
                <div style="margin-bottom:16px;">
                    <input type="password" id="popuprecreatorusernewpassword" placeholder="Введите новый пароль или оставте поле пустым" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;font-size:22px;">
                </div>
                
                <div style="margin-bottom:16px;">
                    <textarea id="popuprecreatoruserdescription" placeholder="Расскажите о себе" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;font-size:22px;min-height:80px;resize:vertical;"><?php echo $_SESSION['about'] ?? ''; ?></textarea>
                </div>
            </div>
                    
            <div style="margin-bottom:16px; padding:0 20px;"> <!-- Добавлен padding вместо margin-left/right -->
                <input type="password" id="popuprecreatoruserassword" placeholder="Введите пароль" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;font-size:22px;">
            </div>
            
            <div style="padding:16px 20px;border-top:1px solid #eee;text-align:right;">
                <button id="confirmpopuprecreatoruse" style="background-color: #E6D5C3;color:white;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;font-size:22px;"><p style="color: black">Подтвердить изменения</p></button>
            </div>

            <p id="popuprecreatoruserInfo" style="font-size:16px; color: red; text-align: center; margin: 6px; display: none;"></p>
        </div>
    </div>
	<div id="AboutAthtor" style="background-color: lightgray; height: 100%; padding-top: 3px; width: 100%;">

		<div style="margin-left: 5px; margin-top: 5px; display: flex; width: 100%;">
            
            <div style="flex: 1; margin: 10px; padding-left: 10px;">
                <h2 style="text-align: center; margin: 5px;">Об авторе</h2>
                <div id="firstAuhtor">
                
                    <div>
                        <img src="images/photo_2025-02-22_16-45-26.jpg" alt="Фотография автора" style="width: 150px; height: auto; margin-top: 0; margin-bottom: 0;">
                        <div>
                            <p style="margin: 0;">ФИО автора: Фурсов Иван Павлович</p>
                            <h4 style="margin-top: 15px; margin-bottom: 5px;">Краткая автобиографическая справка</h2>
                            <p style="margin: 0;">Родился в г. Воронеж в 2006 г. Учился с 2017 г. в школе №63 с 5 до 11 класса, до этого в МБОУ лицей №2, <br>
                            занимался спортом - каратэ и театром - студия синяя птица, с 2024 студент ВГУ ФКН ИСИТ.</p>
                            <h4 style="margin-top: 5px; margin-bottom: 0px;">Вы можете связатся со мной по <a href="mailto:krakin06@mail.ru">почте</a> или через <a href="https://vk.com/ivanfursov06">вк</a></h4>
                            <h4 >Так же рекомендую ознакомится с такими ресурсами, как <a href="https://mishka-knizhka.ru/rasskazy-dlya-detej/">рассказы для детей</a>, <a href="https://moi-rasskazy.livejournal.com/">разные истории</a>.</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div style="flex: 1; margin: 10px; padding-right: 10px;">
                <h2 style="text-align: center; margin: 5px;">О сайте</h2>
                <p style="margin-top: 0; padding: 0; text-align: center; font-size: 20px;">Этот сайт создан для тех, кто любит теплые, трогательные и вдохновляющие рассказы.
                    Здесь вы найдете истории о дружбе, любви, маленьких радостях и больших мечтах — всё, что согревает
                    сердце и поднимает настроение. Если у вас есть своя собственная история, которой вы хотите поделиться
                    с миром, просто зарегистрируйтесь и добавьте её в нашу коллекцию. Ваши слова могут стать тем самым лучиком
                    света, который подарит кому-то улыбку или даже изменит чей-то день к лучшему. Мы верим, что добрые
                    истории делают мир ярче, и будем рады, если вы станете частью нашего сообщества. Читайте, вдохновляйтесь,
                    пишите и дарите тепло вместе с нами!</p>
            </div>
            
        </div>
	</div>

    <script src="./scripts/cookie-consent.js"></script>
    <script src="./scripts/script.js"></script>
    <script src="./scripts/sing_up_on_in.js"></script>
    <script src="./scripts/accountControl.js"></script>
</body>
</html>