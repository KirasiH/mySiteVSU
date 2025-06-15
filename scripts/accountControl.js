const buttonOutAc = document.getElementById("buttonOutAccount") ?? document.createElement('div');
const buttonDelAc = document.getElementById("buttonDeleteAccount") ?? document.createElement('div');
const buttonRecAc = document.getElementById("buttonRecreateAccount") ?? document.createElement('div');
const buttonDeleteUser = document.getElementById("buttonDeleteUser") ?? document.createElement('div');
const closebuttonpopupdeleteuser = document.getElementById("closebuttonpopupdeleteuser") ?? document.createElement('div');
const closepopuprecreatoruser = document.getElementById("closepopuprecreatoruser") ?? document.createElement('div');
const buttonconfirmpopuprecreatoruse = document.getElementById("confirmpopuprecreatoruse") ?? document.createElement('div');

const textfieldPasswordForDeleteUser = document.getElementById("textfieldPasswordForDeleteUser") ?? document.createElement('div');

const popupdeleteuser = document.getElementById("popupdeleteuser") ?? document.createElement('div');
const popuprecreatoruser = document.getElementById("popuprecreatoruser") ?? document.createElement('div');

const popuprecreatoruserusername = document.getElementById("popuprecreatoruserusername") ?? document.createElement('div');
const popuprecreatorusernewpassword = document.getElementById("popuprecreatorusernewpassword") ?? document.createElement('div');
const popuprecreatoruserdescription = document.getElementById("popuprecreatoruserdescription") ?? document.createElement('div');
const popuprecreatoruserassword = document.getElementById("popuprecreatoruserassword") ?? document.createElement('div');
const popuprecreatoruserInfo = document.getElementById("popuprecreatoruserInfo") ?? document.createElement('div');

if (buttonRecAc != null){
    buttonRecAc.addEventListener('click', function(e){
        popuprecreatoruser.style.display = 'block';
    });
}

buttonconfirmpopuprecreatoruse.addEventListener('click', function(e){
    const username = popuprecreatoruserusername.value;
    const password = popuprecreatoruserassword.value;
    const about = popuprecreatoruserdescription.value;
    const newpassword = popuprecreatorusernewpassword.value;

    if (username == '' || password == ''){
        popuprecreatoruserInfo.style.display = 'block';
        popuprecreatoruserInfo.textContent = "пустой пороль или имя юзера";
        return;
    }

    const data = {
        action: '-1',
        password: password,
        new_password: newpassword,
        new_username: username,
        new_about: about
    };

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'accountControl.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            if (response.status == -1){
                popuprecreatoruserInfo.textContent =  'Ошибка!';
                popuprecreatoruserInfo.style.display = 'block';
            } if (response.status == 1) {
                popuprecreatoruserInfo.textContent = 'Данные успешно обновлены!';
                popuprecreatoruserInfo.style.display = 'block';
            } else {
                popuprecreatoruserInfo.textContent =  'Ошибка!';
                popuprecreatoruserInfo.style.display = 'block';
            }
        } else {
            console.error('Error:', xhr.statusText);
            alert('Произошла ошибка: ' + xhr.statusText);
        }
    };

    xhr.onerror = function() {
        console.error('Request failed');
        alert('Произошла ошибка сети');
    };

    xhr.send(JSON.stringify(data));

});

closepopuprecreatoruser.addEventListener('click', function(e) {
    popuprecreatoruser.style.display = 'none';
    location.reload(true);
});

closebuttonpopupdeleteuser.addEventListener('click', function(e) {
    popupdeleteuser.style.display = 'none';
});

buttonDeleteUser.addEventListener('click', function(e){
    e.preventDefault(); // Предотвращаем стандартное поведение, если кнопка в форме
    
    // Функция для получения куки по имени
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Получаем пароль из текстового поля
    const passwordInput = document.getElementById('textfieldPasswordForDeleteUser');
    const password = passwordInput.value;
    
    if (!password) {
        alert('Пожалуйста, введите ваш пароль для подтверждения удаления аккаунта');
        return;
    }

    // Создаем объект с данными
    const data = {
        action: '1',
        password: password,
        new_password: "",
        new_username: "",
        new_about: ""
    };
    
    // Отправляем POST-запрос на accountControl.php
    fetch('accountControl.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети');
        }
        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
        if (data.status === 1) {
            location.reload();
        } else {
            alert(data.message || 'Произошла ошибка при удалении аккаунта');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Произошла ошибка при отправке запроса: ' + error.message);
    });
});

buttonDelAc.addEventListener('click', function(e){
    popupdeleteuser.style.display = 'block';
});

buttonOutAc.addEventListener('click', function(e){
    e.preventDefault(); // Предотвращаем стандартное поведение (если кнопка в форме)

    // Получаем токен из куки auth_token
    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    };

    // Формируем JSON-данные
    const requestData = {
        action: '0',
        new_password: '',
        password: '',
        new_username: '',
        new_about: ''
    };
    // Отправляем запрос на accountControl.php
    fetch('accountControl.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети');
        }
        return response.json();
    })
    .then(data => {
        // Сохраняем поле status в переменную
        const status = data.status;
        console.log('Статус:', status); // Для отладки

        // Дополнительные действия в зависимости от статуса
        if (status === 1) {
            // Например, перенаправление или уведомление
            location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отправке запроса');
    });
});

