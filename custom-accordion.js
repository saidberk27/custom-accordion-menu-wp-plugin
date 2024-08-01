jQuery(document).ready(function ($) {
    $.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'get_all_product_details'
        },
        success: function (response) {
            if (response.success && response.data) {
                response.data.forEach(function (product) {
                    var productItem = $('.product-item[data-product-id="' + product.id + '"]');
                    var attributeIcons = '';

                    if (product.attributes && product.attributes.alerjen) {
                        var alerjenler = product.attributes.alerjen[0].split('|').map(function (item) {
                            return item.trim();
                        });

                        alerjenler.forEach(function (alerjen) {
                            var iconPath = getAttributeIconPath(alerjen);
                            if (iconPath) {
                                attributeIcons += '<div class="attribute-icon-container">' +
                                    '<img src="' + iconPath + '" alt="' + alerjen + '" class="attribute-icon">' + '</div>';
                            }
                        });
                    }
                    productItem.find('.product-allergens').html(attributeIcons);
                });
            } else {
                console.error("Ürün attribute bilgileri alınırken bir hata oluştu.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error: " + status + ": " + error);
        }
    });


    $('.custom-accordion .accordion-header').click(function () {
        $(this).next('.accordion-content').slideToggle();
    });

    $('.product-item').click(function () {
        var productId = $(this).data('product-id');

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
                console.log("Ajax response:", response); // Tüm yanıtı konsola yazdır
                if (response.success && response.data) {
                    $('#popup-title').text(response.data.title || '');
                    $('#popup-description').html(response.data.description || '');
                    $('#popup-price').html(response.data.price || '');

                    // Attribute ikonlarını ekle
                    var attributeIcons = '';
                    if (response.data.attributes && response.data.attributes.alerjen) {
                        console.log("Alerjenler:", response.data.attributes.alerjen);

                        // Alerjen string'ini '|' karakterine göre ayır ve boşlukları temizle
                        var alerjenler = response.data.attributes.alerjen[0].split('|').map(function (item) {
                            return item.trim();
                        });

                        alerjenler.forEach(function (alerjen) {
                            var iconPath = getAttributeIconPath(alerjen);
                            console.log("Alerjen:", alerjen, "Icon Path:", iconPath);
                            if (iconPath) {
                                attributeIcons += '<div class="attribute-icon-container">' +
                                    '<img src="' + iconPath + '" alt="' + alerjen + '" class="attribute-icon">' + '</div>';
                            }
                        });
                    }
                    console.log("Attribute Icons HTML:", attributeIcons);
                    $('#popup-attributes').html(attributeIcons);

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
                            $('.circular-progress').css('background', `conic-gradient(#e21833 ${progress * 3.6}deg, #ededed 0deg)`);
                            $('.inner').text(progress + '%');
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

    // Attribute'a göre ikon yolunu döndüren yardımcı fonksiyon
    function getAttributeIconPath(attribute) {
        var iconPaths = {
            'aci': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Aci.png',
            'mantar': 'https://caddeparklounge.com/wp-content/uploads/2024/08/mantar-250x250-1.png',
            'alkol': 'https://caddeparklounge.com/wp-content/uploads/2024/08/alerjen_alkol.png',
            'gluten': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Gluten.png',
            'hardal': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Hardal.png',
            'kereviz': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Kereviz.png',
            'susam': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Susam.png',
            'sut': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Sut.png',
            'yumurta': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Yumurta.png',
            'kabuk': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Kabuklu_Deniz_Urunu-250x250-1.png',
            'kukurt': 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Kukurt.png',
            'soya': 'https://caddeparklounge.com/wp-content/uploads/2024/08/alerjen_soya.png'
        };
        return iconPaths[attribute.toLowerCase()] || null;
    }
});