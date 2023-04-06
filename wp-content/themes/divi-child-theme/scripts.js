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

    $("body.woocommerce-cart div.shop_table .cart_item .product-quantity input.qty").change(function ()
    {
        $('body.woocommerce-cart div.shop_table button[name="update_cart"]').trigger("click");
    });

    const createPDF = (payload, endpoint, pdfName, successFn, el = null, autoDownload = true) =>
    {
        payload = typeof payload === "object" ? JSON.stringify(payload) : payload;
        $.ajax({
            type: "POST",
            url: thryvSettings.ajaxurl,
            data:
                "&payload=" +
                encodeURIComponent(payload) +
                "&endpoint=" +
                endpoint +
                "&action=create_pdf",
            success: function (response)
            {
                // TODO: Move pdf creation to PHP, Receive status & link from ajax response
                // console.log(response);
                if (response.success)
                {
                    // convert to response base64 encoding
                    var binaryString = window.atob(response.data);
                    var binaryLen = binaryString.length;
                    var bytes = new Uint8Array(binaryLen);

                    for (var i = 0; i < binaryLen; i++)
                    {
                        var ascii = binaryString.charCodeAt(i);
                        bytes[i] = ascii;
                    }

                    // create a download anchor tag
                    var downloadLink =
                        el == null
                            ? document.createElement("a")
                            : document.querySelector(el);
                    downloadLink.target = "_blank";
                    downloadLink.download = pdfName + ".pdf";

                    // convert downloaded data to a Blob
                    var blob = new Blob([bytes], {
                        type: "application/pdf",
                    });

                    // create an object URL from the Blob
                    var URL = window.URL || window.webkitURL;
                    var downloadUrl = URL.createObjectURL(blob);

                    // set object URL as the anchor's href
                    downloadLink.href = downloadUrl;

                    // add element to body if none exists
                    if (el == null)
                    {
                        document.body.appendChild(downloadLink);
                    }

                    // fire a click event on the anchor if auto download enabled
                    if (autoDownload)
                    {
                        downloadLink.click();
                    }

                    successFn(downloadLink.href);
                } else
                {
                    console.log(response);
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
            url: thryvSettings.ajaxurl + "?action=add_tmpimgs",
            data: form_data,
            processData: false,
            contentType: false,
        })
            .done(function (response)
            {
                // console.log(response);
                let filename = response["data"]["file"];
                let link = window.location.origin + "/media/" + filename;
                callback(link, filename);
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
            url: thryvSettings.ajaxurl,
            data: "action=purge_tmpimgs",
            success: function (response)
            {
                // console.log(response);
                callback();
            },
            error: function (response)
            {
                console.log(response);
            },
        });
    };

    $("#create-pdf").click(function (e)
    {
        addTmpImgs(
            "#invoice-form-generator",
            function (link)
            {
                // TODO: For each link/filename add to array for images & convert array to JSON
                let payload = {
                    title: "Photobook Example",
                    fontSize: 10,
                    textColor: "#333333",
                    data: {
                        img1: "https://placekitten.com/800/838",
                        img2: "https://placekitten.com/800/1000",
                        img3: "https://placekitten.com/800/1000",
                        img4: "https://placekitten.com/800/1000",
                        img5: "https://placekitten.com/800/1000",
                        img6: "https://placekitten.com/800/716",
                        img7: "https://placekitten.com/800/718",
                        img8: "https://placekitten.com/800/452",
                        img9: "https://placekitten.com/800/434",
                        img10: "https://placekitten.com/800/905",
                        img11: "https://placekitten.com/800/1000",
                        img12: "https://placekitten.com/800/1000",
                        img13: "https://placekitten.com/800/1000",
                        img14: "https://placekitten.com/800/875",
                        img15: "https://placekitten.com/800/875",
                        img16: "https://placekitten.com/800/781",
                        img17: "https://placekitten.com/800/783",
                        img18: "https://placekitten.com/800/1000",
                        img19: "https://placekitten.com/800/505",
                        img20: "https://placekitten.com/800/504",
                        img21: "https://placekitten.com/800/1000",
                        img22: "https://placekitten.com/800/1000",
                        img23: "https://placekitten.com/800/770",
                        img24: "https://placekitten.com/800/996",
                        img25: "https://placekitten.com/800/1000",
                        img26: "https://placekitten.com/800/1000",
                        img27: "https://placekitten.com/800/733",
                        img28: "https://placekitten.com/800/735",
                        img29: "https://placekitten.com/800/1000",
                        img30: "https://placekitten.com/800/1000",
                        img31: "https://placekitten.com/800/804"
                    }
                };
                console.log(payload);
                createPDF(
                    JSON.stringify(payload),
                    "https://app.useanvil.com/api/v1/fill/BtCm6RuGVTqsizG9w9oT.pdf",
                    "pdfSample",
                    function ()
                    {
                        purgeTmpImgs();
                    }
                );
            }
        );
    });
});