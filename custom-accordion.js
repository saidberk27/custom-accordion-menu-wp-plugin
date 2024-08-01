jQuery(document).ready(function ($) {
    $('.custom-accordion .accordion-header').click(function () {
        $(this).next('.accordion-content').slideToggle();
    });

    $('.product-item').click(function () {
        var productId = $(this).data('product-id');

        // Pop-up'ı hemen aç
        $('#product-popup').fadeIn();
        $('.circular-progress').show();
        $('#popup-image').hide();

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_product_details',
                product_id: productId
            },
            success: function (response) {
                if (response.success && response.data) {
                    $('#popup-title').text(response.data.title || '');
                    $('#popup-description').html(response.data.description || '');
                    $('#popup-price').html(response.data.price || '');

                    // Resmi yükle ve progress bar'ı güncelle
                    var img = new Image();
                    img.onload = function () {
                        $('.circular-progress').hide();
                        $('#popup-image').attr('src', response.data.image).fadeIn();
                    };
                    img.src = response.data.image || '';

                    var progress = 0;
                    var progressInterval = setInterval(function () {
                        progress += 10;
                        if (progress > 100) {
                            clearInterval(progressInterval);
                        } else {
                            $('.circular-progress').css('background', `conic-gradient(#e21833 ${progress * 3.6}deg, #ededed 0deg)`); $('.inner').text(progress + '%');
                        }
                    }, 100);
                } else {
                    console.error("Invalid response format");
                    $('#product-popup').fadeOut();
                    alert("Ürün bilgileri yüklenirken bir hata oluştu. Lütfen tekrar deneyin.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error: " + status + ": " + error);
                $('#product-popup').fadeOut();
                alert("Ürün bilgileri yüklenirken bir hata oluştu. Lütfen tekrar deneyin.");
            }
        });
    });

    $('.close-popup').click(function () {
        $('#product-popup').fadeOut();
    });

    $(window).click(function (event) {
        if ($(event.target).hasClass('product-popup')) {
            $('#product-popup').fadeOut();
        }
    });
});