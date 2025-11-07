<head>
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>
    <main>

        <!-- Всталвляем фотки все в image-gallery -->
        <div class="container mt-5">
            <h1>Моя Галерея Изображений</h1>
            <div class="row" id="image-gallery">

            </div>
        </div>




    </main>



    <!-- Модальное окно для просмотра изображения и комментариев   aria-hidden="true" aria-modal="true" inert-->
    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"  >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"> <!-- modal-lg -->
            <div class="modal-content">


                <div class="modal-body text-center">

                    <!-- Удаляем изображение -->
                    <div id="buckets-image"></div>

                    <!-- Изображение будет загружено сюда -->
                    <img src="" class="modal-image-display img-fluid" alt="Изображение в модальном окне">
                    <h6>Комментарии:</h6>
                    <div class="comments-section text-start" id="comments-show">

                        <h6>Комментарии:</h6>

                    </div>
                </div>
                <div class="modal-footer">

                    <textarea class="form-control" id="messageInput" rows="3"></textarea>
                    <!-- <button type="button" class="btn btn-success" onclick="sendMessage()">Отправить</button> -->
                     <button type="button" class="btn btn-success" id="sendMessageButton">Отправить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>

                </div>
                <div id="errorMessages" class="mt-2 mb-2"></div>
            </div>
        </div>
    </div>


</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</html>