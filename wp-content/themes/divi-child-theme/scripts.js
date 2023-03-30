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
                console.log(document.querySelector('[name="book_cover"]').checkValidity() && document.querySelector('[name="book_title"]').checkValidity());
            return document.querySelector('[name="book_cover"]').checkValidity() && document.querySelector('[name="book_title"]').checkValidity();
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
        }

        let currentActive = 1;

        next.addEventListener('click', () =>
        {
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
            else
            {
                $(".single_add_to_cart_button").trigger("click");
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
    }

    if ($(".single-product-premade").length > 0)
    {
        $(window).resize(function ()
        {
            let imageHeight = $(".single-product-premade .woocommerce-product-gallery .flex-viewport").outerHeight();
            $(".single-product-premade .woocommerce-product-gallery .flex-control-thumbs").height(imageHeight);
        }).resize();
    }
});