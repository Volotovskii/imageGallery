<div id="login-form" class="modal-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="login">Логин</label>
                        <input type="text" class="form-control" id="login" name="login" aria-describedby="loginHelp" placeholder="Введите логин">
                        <div class="form-control-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Пароль">
                        <div class="form-control-feedback"></div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Запомнить меня</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="registration-form" class="modal-body d-none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <form id="registration">
                    <div class="form-group">
                        <label for="login">Логин</label>
                        <input type="text" class="form-control" id="login" name="login" aria-describedby="loginlHelp" placeholder="Введите логин">
                        <div class="form-control-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Пароль">
                        <div class="form-control-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="repeat-password">Повторение пароля</label>
                        <input type="password" class="form-control" id="repeat-password" name="repeat-password" placeholder="Повторите пароль">
                        <div class="form-control-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </div>
</div>