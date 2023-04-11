jQuery(document).ready(function ($)
{
    const isDevEnv = window.location.origin.includes("localhost") ? true : false;
    const ADOBE_KEY = isDevEnv ? "82268229efe242b09c12045b880009c7" : "ae0891e08ac249d999d65c5f1532d50b";

    /** Cart & Checkout Pages */
    if ($("body").hasClass("woocommerce-cart") || $("body").hasClass("woocommerce-checkout"))
        if (window.AdobeDC) initPreviewPDF();
        else
        {
            document.addEventListener("adobe_dc_view_sdk.ready", () => initPreviewPDF());
        }

    function initPreviewPDF()
    {
        console.log('initialize pdf previews');

        $(".product-preview-button").click(function (e, index)
        {
            e.preventDefault();
            let id = "cyob-preview-" + index;
            let pdf = $(this).closest(".cart_item").find(".variation-book_preview_link span").last().text();
            console.log(pdf);
            $(this).attr("id", id);
            var adobeDCView = new AdobeDC.View({ clientId: ADOBE_KEY });
            adobeDCView.previewFile({
                content: { location: { url: pdf } },
                metaData: { fileName: "BookSample.pdf", hasReadOnlyAccess: true }
            }, {
                embedMode: "LIGHT_BOX", defaultViewMode: "TWO_COLUMN", showDownloadPDF: false,
                showPrintPDF: false
            });
        });
    }

    /** Create Your Own Book Single Product Page */
    if ($(".single-product-cyob").length > 0)
    {
        var updateList = function ()
        {
            var input = document.getElementById('book_photos');
            var children = "";
            for (var i = 0; i < input.files.length; ++i)
            {
                children += input.files.item(i).name + ',';
            }
            console.log(children);
        }

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
            var imageBase = isDevEnv ? "/wp-content/uploads/" : "/memori/wp-content/uploads/";
            $(this).prepend("<span>" + name + "</span>");
            $(this).prepend("<img src='" + imageBase + image + ".svg' />");
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
                $("#build-progress-btns .btn.next").attr("disabled", "disabled");
            }
            else
            {
                $('#build-progress-btns .btn.next .complete').hide();
                $('#build-progress-btns .btn.next span:not(.complete)').show();
                $("#build-progress-btns .btn.next").removeAttr("disabled", "disabled");
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
                if (getActiveStep(currentActive) === "build")
                {
                    updateList();
                    $(".book_preview.complete").addClass("loading");
                    addTmpImgs("#cyob-form", function (links)
                    {
                        let image_links = JSON.stringify(links);
                        console.log(image_links);
                        links.h1 = {
                            fontFamily: "Arvo",
                            textColor: "#000000",
                            alignment: "center",
                            fontWeight: "bold",
                            value: $("#book_title").val().toUpperCase().split(/\r?\n/)
                        };
                        links.h2 = {
                            fontFamily: "Arvo",
                            fontSize: 42,
                            textColor: "#000000",
                            alignment: "center",
                            fontWeight: "bold",
                            value: $("#book_title_h2").val().toUpperCase()
                        };

                        let pdfType = $('[name="book_layout"]:checked').val();
                        let pdfAPIURL = pdfType === "dynamic" ? "https://app.useanvil.com/api/v1/fill/p65v2UYaoFFOyLlC6uyK.pdf" : "https://app.useanvil.com/api/v1/fill/KTzk7OdqbBTwuXlZNqSO.pdf";
                        let pdfTitle = pdfType.charAt(0).toUpperCase() + pdfType.slice(1) + "PhotobookSample";

                        let payload = {
                            title: pdfTitle,
                            fontFamily: "Arvo",
                            textColor: "#000000",
                            data: links
                        };
                        console.log(payload);

                        var disableAddToCart = setInterval(() =>
                        {
                            if (getActiveStep(currentActive) === "complete" && $("#book_preview_link").val() == "")
                            {
                                $("#build-progress-btns .btn.next").attr("disabled", "disabled");
                            }
                        }, 50);

                        createPDF(
                            JSON.stringify(payload),
                            pdfAPIURL,
                            pdfTitle,
                            function (pdfLink, pdfName)
                            {
                                clearInterval(disableAddToCart);
                                $("#build-progress-btns .btn.next").removeAttr("disabled");
                                purgeTmpImgs();
                                $("#book_preview_link").val(pdfLink);

                                if (window.AdobeDC)
                                {
                                    var adobeDCView = new AdobeDC.View({ clientId: ADOBE_KEY, divId: "book-preview" });
                                    adobeDCView.previewFile({
                                        content: { location: { url: pdfLink } },
                                        metaData: { fileName: pdfName + ".pdf", hasReadOnlyAccess: true }
                                    }, {
                                        defaultViewMode: "TWO_COLUMN_FIT_PAGE", showAnnotationTools: false, showDownloadPDF: false,
                                        showPrintPDF: false
                                    });

                                    const eventOptions = {
                                        //Pass the PDF analytics events to receive.
                                        //If no event is passed in listenOn, then all PDF analytics events will be received.
                                        listenOn: [AdobeDC.View.Enum.PDFAnalyticsEvents.PAGE_VIEW, AdobeDC.View.Enum.PDFAnalyticsEvents.DOCUMENT_OPEN],
                                        enablePDFAnalytics: true
                                    }

                                    adobeDCView.registerCallback(
                                        AdobeDC.View.Enum.CallbackType.EVENT_LISTENER,
                                        function (event)
                                        {
                                            // console.log("pdf opened");
                                            $(".book_preview.complete").removeClass("loading");
                                            // console.log("Type " + event.type);
                                            // console.log("Data " + event.data);
                                        }, eventOptions
                                    );
                                }
                            }
                        );
                    });
                }
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

    /** Premade Single Product Page */
    if ($(".single-product-premade").length > 0)
    {
        $(window).resize(function ()
        {
            let imageHeight = $(".single-product-premade .woocommerce-product-gallery .flex-viewport").outerHeight();
            $(".single-product-premade .woocommerce-product-gallery .flex-control-thumbs").height(imageHeight);
        }).resize();
    }

    $("body.woocommerce-cart div.shop_table .cart_item .product-quantity input.qty").change(function ()
    {
        $('body.woocommerce-cart div.shop_table button[name="update_cart"]').trigger("click");
    });

    const wpAjaxURL = isDevEnv ? window.location.origin + "/wp-admin/admin-ajax.php" : window.location.origin + "/memori/wp-admin/admin-ajax.php";

    const createPDF = (payload, endpoint, pdfName, successFn) =>
    {
        payload = typeof payload === "object" ? JSON.stringify(payload) : payload;
        $.ajax({
            type: "POST",
            url: wpAjaxURL,
            data:
                "&payload=" + encodeURIComponent(payload) +
                "&endpoint=" + endpoint +
                "&pdfName=" + pdfName +
                "&action=create_pdf",
            success: function (response)
            {
                console.log(response);
                if (response.success)
                {
                    successFn(response.data, pdfName);
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                console.log(
                    Object.entries(jqXHR) + " | " + textStatus + " | " + errorThrown
                );
            },
        });
    };

    const addTmpImgs = (form, callback) =>
    {
        // can be form data or element name
        let form_data =
            typeof form === "object"
                ? form
                : new FormData(document.querySelector(form));
        $.ajax({
            type: "POST",
            url: wpAjaxURL + "?action=add_tmpimgs",
            data: form_data,
            processData: false,
            contentType: false,
            timeout: 300000,
        })
            .done(function (response)
            {
                console.log(response);
                callback(response?.data);
            })
            .fail(function (response)
            {
                console.log(response);
            });
    };

    const purgeTmpImgs = (callback = function () { }) =>
    {
        $.ajax({
            type: "POST",
            url: wpAjaxURL,
            data: "action=purge_tmpimgs",
            success: function (response)
            {
                console.log(response);
                callback();
            },
            error: function (response)
            {
                console.log(response);
            },
        });
    };
});