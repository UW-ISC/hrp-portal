(function ($) {
    $(function () {
        // Create the backdrop
        var backdrop = $('<div>').attr('class','wdt-update-backdrop');

        // Create the modal and its elements dynamically
        var modal = $('<div>').attr('id', 'wdt-update-modal');

        var closeBtn = $('<span>').addClass('wdt-update-modal-close').html('<i class="wpdt-icon-times-full"></i>');

        var title = $('<h2>').addClass('wdt-update-modal-title').text('Update version:  ' + wpdatatables_update_info.version);

        var logo = $('<div>').addClass('wpdt-update-modal-logo');

        var updateList = constructUpdateList(wpdatatables_update_info);

        // Create navigation buttons
        var prevButton = document.createElement("a");
        prevButton.className = "wdt-update-modal-prev";
        prevButton.innerHTML = "<i class='wpdt-icon-chevron-left'></i>";

        var nextButton = document.createElement("a");
        nextButton.className = "wdt-update-modal-next";
        nextButton.innerHTML = "<i class='wpdt-icon-chevron-right'></i>";

        // Append elements to the modal
        modal.append(closeBtn, logo, title, prevButton, nextButton, updateList);

        // Append the modal and backdrop to the body
        $('body').append(backdrop, modal);

        // Show the backdrop and modal on page load
        backdrop.show();
        modal.show();

        showSlides(1);

        // Close the modal and backdrop when the close button is clicked
        closeBtn.on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    'action': 'wdtHideUpdateModal'
                },
                dataType: "json",
                async: !0,
                success: function (e) {
                    backdrop.hide();
                    modal.hide();
                }
            });

        });
        // Navigation buttons actions
        $('.wdt-update-modal-next').on('click', function (e) {
            e.preventDefault();
            moveSlides(1);

        });
        $('.wdt-update-modal-prev').on('click', function (e) {
            e.preventDefault();
            moveSlides(-1);

        });

        // Function to generate HTML for a specific type of update
        function generateUpdateList(type, data) {
            var listHtml = '';

            if (data && data.length > 0) {
                listHtml += '<li class="wdt-update-modal-slides"><strong>' + type + ':</strong><ul>';
                data.forEach(function (item, index) {
                    listHtml += '<li>' + item.text ;
                    if (item.link !== '') {
                        listHtml += ' <a href="' + item.link + '" target="_blank" rel="nofollow">More in docs.</a>';
                    }
                    listHtml += '</li>';

                });
                listHtml += '</ul></li>';
            }

            return listHtml;
        }

        // Construct the main unordered list
        function constructUpdateList(updateData) {
            var html = '<ul>';
            if (updateData.features.length)
                html += generateUpdateList('Features', updateData.features);
            if (updateData.improvements.length)
                html += generateUpdateList('Improvements', updateData.improvements);
            if (updateData.bugfixes.length)
                html += generateUpdateList('Bug Fixes', updateData.bugfixes);

            html += '</ul>';

            return html;
        }

        // JavaScript's logic for slide functionality
        var slideIndex = 1;

        function moveSlides(n) {
            showSlides(slideIndex += n);
        }

        function showSlides(n) {
            var i;
            var slides = document.getElementsByClassName("wdt-update-modal-slides");

            if (n > slides.length) {
                slideIndex = 3;
            }
            if (n == slides.length) {
                $('.wdt-update-modal-next').addClass('wdt-remove')
            } else {
                $('.wdt-update-modal-next').removeClass('wdt-remove')
            }

            if (n == 1) {
                $('.wdt-update-modal-prev').addClass('wdt-remove')
            } else {
                $('.wdt-update-modal-prev').removeClass('wdt-remove')
            }

            if (n < 1) {
                slideIndex = 1;
            }

            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            if (!slideIndex) slideIndex = n;

            slides[slideIndex - 1].style.display = "block";
        }
    })
})(jQuery);