
// Загрузка изображений на странице
export function loadImages() {
    console.log("loadImages");
    $.ajax({
        type: 'GET',
        //url: '/imageGallery/public/show_images', ?url=auth/login
        url: '?url=image/showImages',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                
                const imageGallery = $('#image-gallery');
                imageGallery.empty();
                console.log('Загрузили фотографии loadImages: ', response);

                response.images.forEach(function (image) {
                    // распознаем фотки пользовтеля
                    const ovner = image.is_owner ? 'owner' : '';
                    console.log(image);
                    const imageHtml = `
                        <div class="view ${ovner}">
                                     <a href="#" class = "image-gallery-item" data-id="${image.id}">
                                        <img src="../public/images/${encodeURIComponent(image.unique_name)}"  alt="Изображение" >
                                     </a>  
                                     </div>
                                    `;

                    imageGallery.append(imageHtml);

                });

            } else {
                console.error('Ошибка при загрузке изображений:', response.errors);
            }

        },
        error: function (xhr) {

            console.error('Ошибка при загрузке изображений:', xhr.responseText);
        }
    });
}



/**
 Отправляет AJAX-запрос на удаление фотографии.
 @param {number}  clickedImageId - ID удаляемой фотографии.
 */
export function deletetImage(clickedImageId) {
    console.log('Удаляем фотографию id: ', clickedImageId);

    // подтверждение
    Swal.fire({
        title: "Вы уверены, что хотите удалить эту фотографию?",
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
                type: 'POST', // DELETE? url: '?url=image/delImage',
                //url: '/imageGallery/public/load_bucket_image',
                url: '?url=image/delImage',
                dataType: 'json',
                data: { image_id: clickedImageId },
                // data: { _token: 'CSRF_ТОКЕН' }, //CSRF-токен TODO
                success: function (response) {
                    if (response.success) {
                        console.log('Удалили фотографию: ', response);
                        Swal.fire(
                            'Удалено!',
                            'Ваша фотография была удалена.',
                            'success'
                        ).then(() => {
                            //  удалить элемент из DOM, или коменты полностью TODO
                            // $('#' + imageId).remove();
                            $('#imagemodal').modal('hide'); // закрываем мод. окно TODO
                            loadImages();
                        });
                    } else {
                        // Обработка ошибки, если удаление не удалось на сервере
                        Swal.fire(
                            'Ошибка!',
                            response.message || 'Не удалось удалить фотографию.',
                            'error'
                        );
                    }
                },
                //Скрыть?
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


/**
 Кнопка удаления для image.
 @param {owner}  owner - Есть класс такой в view.
 @param {number}  clickedImageId - ID фотографии.
 */
export function loadBucketImage(owner, clickedImageId) {

    // для очистки кнопки при открытии модального окна
    const modalBody = $('#buckets-image');
    modalBody.empty();


    if (owner) {
        const loadBucket = $('#buckets-image'); // куда insert

        const BucketImage = `
                        <button type="button" class="delete-image-btn btn" data-image-id = "${clickedImageId}" id="bucket" title="Удалить">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                            </svg>
                        </button>
                                    `;

        loadBucket.append(BucketImage);
    }

}

