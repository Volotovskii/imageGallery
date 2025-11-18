
/**
 Отправляет fetch-запрос на добавления комментария к фотографии.
 @param TODO Получаем ID изображения из data-атрибута самой кнопки.
 */
export function sendMessage() {
    const message = document.getElementById('messageInput').value;
    
    // Берём у img id для связки
    // Получаем ID изображения из data-атрибута самой кнопки
    const imageIdToSend = $('#messageInput').attr('image-id');

    console.log('Текст комментария: ', message,' . Для фотографии id: ',imageIdToSend);

    // Отправка сообщения на сервер url: '?url=comments/addComments',
    fetch('?url=comments/addComments', {
        method: 'POST',
        body: JSON.stringify({ message: message, id: imageIdToSend }),
    })
        .then(response => response.json())
        .then(data => {
            const errorContainer = document.getElementById('errorMessages');
            const messageInput = document.getElementById('messageInput');

            // Очищаем предыдущие ошибки перед показом новых или сообщений об успехе
            // перенести в открытие модального окна? или отдельная функция по id ошибкам
            errorContainer.innerHTML = '';

            if (data.success) {
                console.log('Фотография добавлена');
                messageInput.value = ''; // очищаем текст после true отправки
                loadComments(imageIdToSend);
            } else if (data.errors) {

                console.log('Ошибка:', data);

                const errorList = document.createElement('ul');
                errorList.classList.add('list-unstyled', 'text-danger');

                data.errors.forEach(errorMessage => {
                    const listItem = document.createElement('li');
                    listItem.textContent = errorMessage;
                    errorList.appendChild(listItem);
                });

                errorContainer.appendChild(errorList);
            }

        })
        .catch((error) => console.error('Ошибка:', error));
}


/**
 Отправляет AJAX-запрос на отображения комментариев.
 @param {number}  clickedImageId - ID image для которого грузим комментарии.
 */
export function loadComments(clickedImageId) {
    console.log('Комментарии загружены для фотографии id:', clickedImageId);
    $.ajax({
        type: 'GET',
        //url: '/imageGallery/public/show_comments',
        url: '?url=comments/showComments',
        dataType: 'json',
        data: { image_id: clickedImageId },
        success: function (response) {
            if (response.success) {
                const commentsShow = $('#comments-show'); // куда insert
                commentsShow.empty();
                console.log('Комментарии', response);
                response.comments.forEach(function (comment) {

                    // удаления комента ставим просто крестик
                    const deleteHtml = comment.is_owner
                        ? `<span 
                                class="delete-comment-btn" 
                                data-comment-id="${comment.id}" 
                                style="cursor: pointer; color: red; margin-left: 10px; font-weight: bold;"
                            >&times;
                            </span>`
                        : '';

                    //текст комента + кнопка удаления
                    const commentHtml = `
                        <div class="card card-body mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${comment.author}:</strong> 
                                    <div class="comment-text">${comment.text}</div>
                                </div>
                                ${deleteHtml}
                            </div>
                            <small class="text-muted">${comment.created_at}</small>
                        </div>
                    `;


                    commentsShow.append(commentHtml);
                });
            } else {
                console.error('Ошибка при загрузке комментария:', response.errors);
            }
        },
        error: function (xhr,error) {
            console.error('Ошибка при загрузке комментария:', error.responseText);
        }
    });
}


/**
 Отправляет AJAX-запрос на удаление комментария.
 @param {number}  commentId - ID удаляемого комментария.
 @param {number } imageId - ID image в котором удаляем.
 */
export function deleteComment(commentId, imageId) {
    // Если ID комментария не предоставлен, выходим
    if (!commentId) {
        console.error("ID комментария не предоставлен для удаления.");
        return;
    }

    // Для перезагрузки списка комментариев, нам нужен ID изображения.
    Swal.fire({
        title: "Вы уверены, что хотите удалить этот комментарий?",
        text: "Вы не сможете отменить это действие!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Да, удалить!",
        cancelButtonText: "Отменить"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST', // DELETE ? 
                url: '?url=comments/deleteComment',
                dataType: 'json',
                data: JSON.stringify({ comment_id: commentId }),
                // data: { _token: 'CSRF_ТОКЕН' }, //CSRF-токен
                success: function (response) {
                    if (response.success) {
                        console.log('Комментарий удалён', response);
                        Swal.fire(
                            'Удалено!',
                            'Ваш комментарий был удален.',
                            'success'
                        ).then(() => {
                            //  удалить элемент из DOM, или коменты полностью TODO
                            // $('#' + imageId).remove();
                            loadComments(imageId);
                        });
                    } else {
                        // Обработка ошибки, если удаление не удалось на сервере
                        Swal.fire(
                            'Ошибка!',
                            response.message || 'Не удалось удалить комментарий.',
                            'error'
                        );
                    }
                },
                error: function (xhr, status, error) {
                    //Для ajax
                    Swal.fire(
                        'Ошибка!',
                        'Произошла ошибка при отправке запроса: ' + error,
                        'error'
                    );
                }
            });

        }
    });



}