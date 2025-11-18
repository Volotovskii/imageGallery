

export function addImage(data) {
    console.log('data', data);
    $.ajax({
        type: 'POST',
        //url: '/imageGallery/public/upload',
        //url: '?url=comments/showComments',
        url: '?url=image/addImages',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
                swal({
                    title: "Отлично!",
                    text: response.message[0],
                    icon: "success",
                }).then(() => {
                    // location.reload();
                    //loadImages();
                });
            } else {
                swal({
                    title: "Ошибка!",
                    text: response.errors.join('\n'),
                    icon: "error",
                });
            }
        },
    });
}