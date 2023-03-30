jQuery(document).ready(function ($)
{
    if ($(".single-product-cyob").length > 0)
    {
        $("body.single-product .extra-options tr:not(.style)").hide();
        $('body.single-product .extra-options label[for*="book_cover_"]').each(function ()
        {
            var name = $(this).text();
            $(this).prepend("<span>" + name + "</span>")
        });
        $('body.single-product .extra-options label[for*="book_layout_"]').each(function ()
        {
            var name = $(this).text();
            var image = $(this).attr("for").replace("book_layout_", "");
            $(this).prepend("<span>" + name + "</span>");
            $(this).prepend("<img src='/wp-content/uploads/" + image + ".svg' />");
        });
        $("input#book_photos").attr("accept", ".png,.jpg,.jpeg");

        const progress = document.querySelector('#progress');
        const prev = document.querySelector('#build-progress-btns .prev');
        const next = document.querySelector('#build-progress-btns .next');
        const circles = document.querySelectorAll('.circle');

        function scrollToTop()
        {
            window.scrollTo(0, 0);
        }

        getActiveStep = (index) =>
        {
            let activeCircle = $(".circle").eq(index - 1)[0];
            let step = $(activeCircle).data("step");
            return step;
        }

        checkStepValidity = (step) =>
        {
            if (step === "style")
            {
                console.log(document.querySelector('[name="book_cover"]').checkValidity() && document.querySelector('[name="book_title"]').checkValidity());
                return document.querySelector('[name="book_cover"]').checkValidity() && document.querySelector('[name="book_title"]').checkValidity();
            }
            if (step === "build")
            {
                console.log(document.querySelector('[name="book_layout"]').checkValidity() && document.querySelector('[name="book_photos[]"]').checkValidity());
                return document.querySelector('[name="book_layout"]').checkValidity() && document.querySelector('[name="book_photos[]"]').checkValidity();
            }
        }

        changeStepItems = (step) =>
        {
            $("body.single-product .extra-options ." + step).show();
            $("body.single-product .extra-options tr:not(." + step + ")").hide();

            if (step === "complete")
            {
                $('#build-progress-btns .btn.next .complete').show();
                $('#build-progress-btns .btn.next span:not(.complete)').hide();
            }
            else
            {
                $('#build-progress-btns .btn.next .complete').hide();
                $('#build-progress-btns .btn.next span:not(.complete)').show();
            }
        }

        let currentActive = 1;

        next.addEventListener('click', () =>
        {
            if ($('#build-progress-btns .btn.next .complete').is(":visible"))
            {
                $(".single_add_to_cart_button").trigger("click");
            }
            scrollToTop();
            if (checkStepValidity(getActiveStep(currentActive)))
            {
                currentActive++

                if (currentActive > circles.length)
                {
                    currentActive = circles.length;
                }

                update(getActiveStep(currentActive));
            }
        });

        prev.addEventListener('click', () =>
        {
            currentActive--

            // prevents currentActive from going below 1
            if (currentActive < 1)
            {
                currentActive = 1;
            }

            update(getActiveStep(currentActive));
        });

        document.getElementById("book_photos").addEventListener("change", (e) =>
        {
            console.log("photo listener");
            console.log(e);
            if (e.target.files.length == 0)
            {
                $(".single-product-cyob .extra-options .book_photos_upload_link #upload-photos").removeClass("uploaded");
            }
            else
            {
                $(".single-product-cyob .extra-options .book_photos_upload_link #upload-photos").addClass("uploaded");
            }
        });

        function update(step)
        {
            changeStepItems(step);
            circles.forEach((circle, idx) =>
            {
                if (idx < currentActive)
                {
                    circle.classList.add('active');
                } else
                {
                    circle.classList.remove('active')
                }
            });

            const actives = document.querySelectorAll('.active');

            progress.style.width = (actives.length - 1) / (circles.length - 1) * 100 + '%';

            // disables prev when you can't go back further, disables next when there are no more steps
            if (currentActive === 1)
            {
                prev.disabled = true;
            } else if (currentActive === circles.length)
            {
                console.log(currentActive)
                // $(".single_add_to_cart_button").trigger("click");
                // next.disabled = true;
            } else
            {
                prev.disabled = false;
                next.disabled = false;
            }
        };

        $("#upload-photos").click(function (e)
        {
            e.preventDefault();
            $("input#book_photos").trigger("click");
        });

        var book_title = document.getElementById('book_title');
        var charlimit = 7; // char limit per line
        book_title.onkeyup = function ()
        {
            var lines = book_title.value.split('\n');
            for (var i = 0; i < lines.length; i++)
            {
                if (lines[i].length <= charlimit) continue;
                var j = 0; space = charlimit;
                while (j++ <= charlimit)
                {
                    if (lines[i].charAt(j) === ' ') space = j;
                }
                lines[i + 1] = lines[i].substring(space + 1) + (lines[i + 1] || "");
                lines[i] = lines[i].substring(0, space);
            }
            book_title.value = lines.slice(0, 10).join('\n');
        };
    }

    if ($(".single-product-premade").length > 0)
    {
        $(window).resize(function ()
        {
            let imageHeight = $(".single-product-premade .woocommerce-product-gallery .flex-viewport").outerHeight();
            $(".single-product-premade .woocommerce-product-gallery .flex-control-thumbs").height(imageHeight);
        }).resize();
    }

    // $(".single-product-cyob btn.next").click(function ())

    $("body.woocommerce-cart div.shop_table .cart_item .product-quantity input.qty").change(function ()
    {
        $('body.woocommerce-cart div.shop_table button[name="update_cart"]').trigger("click");
    });
});