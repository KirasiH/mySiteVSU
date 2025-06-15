const br = document.getElementById("registrationLink") ?? document.createElement('div')
const popup = document.getElementById("popup") ?? document.createElement('div');
const closebutton = document.getElementById("closebutton") ?? document.createElement('div');

const lickRegister = document.getElementById("linkRegister") ?? document.createElement('div');

const formRegister = document.getElementById("StateRegister") ?? document.createElement('div');
const formSingIn = document.getElementById("StateSingIn") ?? document.createElement('div');

const textfieldUsernameRegister = document.getElementById("textfieldusernameRegister") ?? document.createElement('div');
const textfildemailRegiser = document.getElementById("textfieldEmailRegister") ?? document.createElement('div');
const textfildpasswor1Register = document.getElementById("textfieldPassword1Register") ?? document.createElement('div');
const textfildpassword2Register = document.getElementById("textfieldPassword2Register") ?? document.createElement('div');
const textaboutuserregister = document.getElementById("textaboutuserRegister") ?? document.createElement('div');

const textfildemailSing = document.getElementById("textfieldLoginSignIn") ?? document.createElement('div');
const textfildpasswordSing = document.getElementById("textfieldPasswordSignIn") ?? document.createElement('div');

const buttonSingIn = document.getElementById("buttonSingIn") ?? document.createElement('div');
const buttonRegister = document.getElementById("buttonRegister") ?? document.createElement('div');

const infoRegisterAray = document.getElementById("infoRegisterAray") ?? document.createElement('div');



function Register(email, username, password, about) {
    data = {
        username: username,
        email: email,
        password: password,
        about: about
    };

    fetch('register.php', {
        method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети: ' + response.status);
        }
        return response.text().then(text => {
            try {
                return text ? JSON.parse(text) : {};
            } catch (e) {
                console.error('Ошибка парсинга JSON:', text);
                throw new Error('Невалидный JSON: ' + text);
            }
        });
    })
    .then(data => {
        if (data.status == 1){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "Успешная регистрация";
        }

        if (data.status == -2){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "ошибка валидации";
        }

        if (data.status == -4){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "неверный метода запроса";
        }

        if (data.status == -1){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "Ошибка, такой юзер существует";
        }
    })
    .catch((error) => {
        infoRegisterAray.style.display = 'block';
        infoRegisterAray.textContent = "ошибка сервера";
    });
}

function SignIn(email, password) {
    data = {
        email: email,
        password: password,
    };

    fetch('auth.php', {
        method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети: ' + response.status);
        }
        return response.text().then(text => {
            try {
                return text ? JSON.parse(text) : {};
            } catch (e) {
                console.error('Ошибка парсинга JSON:', text);
                throw new Error('Невалидный JSON: ' + text);
            }
        });
    })
    .then(data => {
        if (data.status == 1){
            location.reload();
        }
        if (data.status == -2){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "ошибка валидации";
        }
        if (data.status == -3){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "ошибка сервера";
        }
        if (data.status == -4){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "неправильный метод запроса";
        }

        if (data.status == -1){
            infoRegisterAray.style.display = 'block';
            infoRegisterAray.textContent = "проверте данные входа";
        }
    })
    .catch((error) => {
        infoRegisterAray.style.display = 'block';
        infoRegisterAray.textContent = "ошибка сервера";
    });
}

buttonRegister.addEventListener('click', function(e){
    if (!(textfildpasswor1Register.value == textfildpassword2Register.value)){
        infoRegisterAray.style.display = 'block';
        infoRegisterAray.textContent = "Пороли не совпадают";
        return;
    }

    Register(textfildemailRegiser.value, textfieldUsernameRegister.value, textfildpasswor1Register.value, textaboutuserregister.value)
});

buttonSingIn.addEventListener('click', function(e){
    SignIn(textfildemailSing.value, textfildpasswordSing.value)
});

br.addEventListener('click', function(e){
    document.getElementById('popup').style.display = 'block';
});

closebutton.addEventListener('click', function(e) {
    popup.style.display = 'none';
    overlay.style.display = 'none';
});

lickRegister.addEventListener('click', function(e){
    formRegister.style.display = 'block';
    formSingIn.style.display = 'none';
    infoRegisterAray.style.display = 'none';
});




