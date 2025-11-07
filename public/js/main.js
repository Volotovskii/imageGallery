import { switchModalAuth } from './auth.js'
import { addImage } from './addImages.js'
import { loadComments, sendMessage, deleteComment } from './showComments.js';
import { loadImages, loadBucketImage, deletetImage } from './showImages.js';


document.addEventListener('DOMContentLoaded', () => {

    loadImages(); //видно всем
    //Вход регистарция
    const openAuthModalButton = document.getElementById('openAuthModal');
    if (openAuthModalButton) {
        openAuthModalButton.addEventListener('click', function (e) {
            e.preventDefault();
            switchModalAuth();
        });
    } 
    // else {
    //     loadImages();
    // }


    // добавления фотографии
    const addImageForm = document.getElementById('addImage');
    if (addImageForm) {
        addImageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = new FormData(this);
            loadImages(); // по условию?
            addImage(data);
            loadImages(); // по условию?
        });
    }



    // Модальное окно для фотографии и коментария
    const imageGallery = $('#image-gallery');
    if (imageGallery) {
        imageGallery.on('click', 'a.image-gallery-item', function (event) {
            event.preventDefault();

            // Получаем src кликнутого изображения из атрибута data-full-src или src
            const clickedImageSrc = $(this).find('img').attr('src');
            const clickedImageId = $(this).data('id'); // id уникальное изобрж. для комента

            console.log('Клик по изображению, id:', clickedImageId);

            // Устанавливаем src для изображения внутри модального окна
            $('#imagemodal .modal-image-display').attr('src', clickedImageSrc);

            // установлю id для отправки сообщения TODO обдумать 
            $('#messageInput').attr('image-id', clickedImageId || fallbackImageId);


            // очищаем предыдущие ошибки в момент открытия окна
            // const errorContainer = document.getElementById('errorMessages');
            // errorContainer.innerHTML = '';
            const modalContainer = $('.modal-content'); // Или $('#imagemodal .modal-body')
            const errorContainer = modalContainer.find('#errorMessages');
            errorContainer.html(''); // .html('') работает как .innerHTML = ''

            const owner = (this).closest('.owner'); // опрделяем владельца для кнпоки удаления? и для кноппок коментария?
            console.log('Элемент owner найден:', owner);


            //показываем кнопку удаления фотографии
            loadBucketImage(owner, clickedImageId);

            //грузим все коменты к фотографии + удаление если автор = true
            loadComments(clickedImageId);


            //слушаем удаление коммента
            $('#comments-show').on('click', '.delete-comment-btn', function () {
                const commentId = $(this).data('comment-id');
                console.log('Кнопка удаления комментария нажата', commentId);

                // функц удаления
                deleteComment(commentId, clickedImageId);
            });


            //слушаем удаление фотографии
            $('#buckets-image').on('click', '.delete-image-btn', function () {
                console.log('Кнопка удаления фотографии нажата');

                deletetImage(clickedImageId);

            });


            $('#imagemodal').modal('show');

        });


        // Обработка кнопки "Отправить"
        const sendMessageButton = document.getElementById('sendMessageButton');
        if (sendMessageButton) { // Всегда проверяйте существование элемента
            sendMessageButton.addEventListener('click', () => {
                sendMessage(); // Вызываем функцию sendMessage, определенную выше
            });
        }

    }


});