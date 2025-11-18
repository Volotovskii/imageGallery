let globalCsrfToken = '';
//loadCsrfToken();

// Разделить на доп файл crfs
export function switchModalAuth() {
    console.log('switchModalAuth');

    //Открываем заполнение модального окна
    fetch('?url=auth/loginOpen', {
            method: 'POST'
        }
    )
        .then(response => response.text())
        .then(html => {
            console.log(html);
            document.getElementById('modal-body').innerHTML = html;
            //document.getElementById('authModal').style.display = 'block';

            tabLoginRegistr();
            //Регистрация Вход
            // Загружаем токен после вставки HTML
            loadCsrfToken();
            tabForm();


        });
}

//Функция для загрузки токена через AJAX
function loadCsrfToken() {

    fetch('?url=auth/get-csrf-token', {
        method: 'POST',
    }) // Используем новый маршрут
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.token) {
                globalCsrfToken = data.token;
                console.log('data.token', data);
                // Вставляем токен в формы, если они уже существуют в DOM
                updateToken('loginForm', globalCsrfToken);
                updateToken('registration', globalCsrfToken);
            } else {
                console.error('Токен не получен от сервера.');
            }
        })
        .catch(error => {
            console.error('Ошибка при загрузке CSRF токена:', error);
        });
}






//Функция для обновления скрытого поля токена в конкретной форме
function updateToken(formId, tokenValue) {
    const form = document.getElementById(formId);
    if (form) {
        let tokenInput = form.querySelector('input[name="token"]');
        if (!tokenInput) {
            // Если поля нет, создаем его
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'token';
            form.appendChild(tokenInput);
        }
        tokenInput.value = tokenValue;
    }
}



// Переключение вход \ выход 
function tabLoginRegistr() {
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function (event) {
            event.preventDefault();

            // Убираем активный класс
            tabs.forEach(t => t.classList.remove('tab_active'));
            this.classList.add('tab_active');

            // Показываем нужную форму
            const target = this.dataset.tab;
            const forms = document.querySelectorAll('.modal-body');
            //console.log('forms',forms);
            forms.forEach(form => form.classList.add('d-none'));

            if (target === 'client') {
                document.getElementById('login-form').classList.remove('d-none');
                document.getElementById('login-form').classList.add('d-block');
            } else if (target === 'registr') {
                document.getElementById('registration-form').classList.remove('d-none');
                document.getElementById('registration-form').classList.add('d-block');
            }
        });
    });
}

function tabForm() {

    //updateToken('loginForm', localStorage.getItem('authToken'));
    const loginForm = document.getElementById('loginForm');
    //const user_vk = document.getElementById('');






    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        //console.log('formData', formData); ?url=auth/action_loginOpen
        fetch('?url=auth/login', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                //console.log('data', data);
                $('.has-danger').removeClass('has-danger');
                $('.form-control-feedback').text('');

                if (data.success) {
                    console.log('Вход успешен');
                    $('#authModal').modal('hide');
                    window.location.href = '/imageGallery/public/';
                } else {
                    //TODO Ошбики
                    $('.has-danger').removeClass('has-danger');
                    $('.form-control-feedback').text('');
                    console.log('Ошибки входа: ', data.errors);
                    const formID = 'loginForm';

                    if (data.errors) {
                        data.errors.forEach(function (data, index) {
                            var field = Object.getOwnPropertyNames(data);

                            var value = data[field];
                            var div = $("#" + formID).find("#" + field[0]).closest('div');

                            div.addClass('has-danger');
                            div.children('.form-control-feedback').text(value);
                        });
                    }


                }

            })

    })




    $('#registration').submit(function (e) {
        e.preventDefault();
        var data = new FormData(this);
        //console.log('data', data);
        $.ajax({
            type: 'POST',
            //url: '/imageGallery/public/register',
            url: '?url=auth/register',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                swal({
                    title: "Отлично!",
                    text: "Пользователь успешно зарегистрирован!",
                    icon: "success",
                }).then(() => {
                    location.reload();
                });
            },
            error: function (response, status, error) {

                $('.has-danger').removeClass('has-danger');
                $('.form-control-feedback').text('');

                const formID = 'registration';

                var errors = response.responseJSON;
                //console.log('errors', errors);
                if (errors.errors) {
                    errors.errors.forEach(function (data, index) {
                        var field = Object.getOwnPropertyNames(data);

                        var value = data[field];
                        var div = $("#" + formID).find("#" + field[0]).closest('div');

                        div.addClass('has-danger');
                        div.children('.form-control-feedback').text(value);
                    });
                }
            }
        });
    });




}