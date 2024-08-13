<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Click Coordinates</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css')}}">
    <style>
        #container {
            position: relative;
            /*width: fit-content;*/
            width: 100%;
        }
        #overlayCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            border-style: dashed;
            border-width: 1px;
            border-color: green;
        }
        #clickableImage {
            top: 0;
            left: 0;
            width: 100%;
            border-style: dashed;
            border-width: 1px;
            border-color: red;
        }
        #clickableImage img {
            height: 100%;
            width: 100%;
            object-fit: contain;
        }
    </style>
    <script>

    </script>
</head>

<body>
<div id="container">
    <canvas id="overlayCanvas"></canvas>

    <div id="clickableImage"></div>

    <p class="d-none">Area coordinates: <span id="areaCoords">None</span></p>
    <p class="d-none">Current dimensions: <span id="dimensions">Waiting for measurement...</span></p>
    <form id="coord_form" class="">
        <div>
            <div class="row">
                <div class="col-12 d-flex justify-content-center">
                    {{--                    <label for="" class="mb-1 my-auto mr-2"></label>--}}
                    {{--                    <div class="col-1"></div>--}}
                    <button type="submit" class="btn btn-primary btn-sm mt-1" id="coordinatesSaveButton">Save Dimensions</button>
                </div>
            </div>
            <br />
            <div class="row">
{{--                <input type="hidden" name="orientation" id="orientation" value="{{$orientation}}">--}}
                <input type="hidden" name="record_type" id="record_type" value="drawing_title">
                <div class="col-2 my-auto d-none">
                    <label>X:</label>
                    <input type="number" name="coordinateX" id="coordinateX" size="4" readonly/>
                </div>
                <div class="col-2 my-auto d-none">
                    <label>Y:</label>
                    <input type="number" name="coordinateY" id="coordinateY" size="4" readonly/>
                </div>
                <div class="col-2 my-auto d-none">
                    <label class="row">L:</label>
                    <input id="boxlength" name="boxlength" type="number" size="4" readonly/>
                </div>
                <div class="col-2 my-auto d-none">
                    <label>B:</label>
                    <input id="boxbreadth" name="boxbreadth" type="number" size="4" readonly/>
                </div>
            </div>
            <div class="row">
                <p class="text-danger d-none" id="already-submitted">Coordinates already submitted</p>
            </div>
        </div>
    </form>
    <div class="row col-12 mx-auto mt-1 d-none">
        <div class="col-3">
            <button type="button" class="mx-auto btn btn-red btn-sm mt-1" id="resetAllButton"><span class="mx-auto">Reset Changes</span></button>
        </div>
        <div class="col-3">
            <button type="button" class="mx-auto btn btn-blue-8 btn-sm mt-1" id="finishCoordinates">Finish</button>
        </div>
    </div>
</div>
<input type="text" class="d-none" id="storage_url" value="{{\Storage::url('')}}">
</body>

<script>
    $(function() {
        let image = document.getElementById('clickableImage');
        const coordsDisplay = document.getElementById('coords');
        const areaCoordsDisplay = document.getElementById('areaCoords');
        const canvas = document.getElementById('overlayCanvas');
        const dimensionsDisplay = document.getElementById('dimensions');
        const storageUrl = document.getElementById('storage_url');

        /*function updateDimensions() {
            const width = image.offsetWidth;
            const height = image.offsetHeight;
            dimensionsDisplay.textContent = `${width}px by ${height}px`;

            canvas.width = width;
            canvas.height = height;
        }*/

        $('#pendingCoordsModal').on('shown.bs.modal', function(e) {
            updateDimensions();
        });

        $('#coordinatesSaveButton').click(function(e) {
            e.preventDefault();
            /*console.log($('#coordinateX').val(),
                $('#coordinateY').val(),
                $('#boxlength').val(),
                $('#boxbreadth').val());*/

            let fileId =  @json($ocrFile->id);
            $.ajax({
                url: `/ocr-files/${fileId}/saveCoordinates`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {
                    'coords': {
                        'x': $('#coordinateX').val(),
                        'y': $('#coordinateY').val(),
                        'h': $('#boxlength').val(),
                        'w': $('#boxbreadth').val(),
                    }
                },
                success: function (response) {
                    console.log(response);
                    console.log(document.getElementById('record_type').value);
                    /*if(document.getElementById('record_type').value == 'drawing_number') {
                        document.getElementById('coord_form').classList.add('d-none');
                        //document.getElementById('save-pending-ocr-request').disabled = false;
                        if (response.success) {
                            $('#success-popup').modal('show');
                            setTimeout(function () {
                                $('#success-popup').modal('hide');
                            }, 1000);
                        } else {
                            $('#liveToast').toast('show');
                            $('.message').html('Something went Wrong !');
                        }
                    }*/

                    document.getElementById('coord_form').reset();
                    document.getElementById('coordinate_title').textContent = 'Drawing Number';
                    document.getElementById('record_type').value = 'drawing_number';
                },
                error: function (error) {
                    document.getElementById('coord_form').reset();
                    console.error('AJAX Error:', error);
                    $('#liveToast').toast('show');
                    $('.message').html('Something went Wrong !');
                }
            });
        });
        let orientationData = @json($ocrFile->ocr_data);
        console.log('orientationData', orientationData);

        function changeImgAttr(orientationSupplied) {
            if(orientationSupplied) {
                $('#clickableImage').attr('src', orientationData['img_path']);
                console.log(document.getElementById('clickableImage').width, document.getElementById('clickableImage').height);
                updateDimensions();
            }
        }

        function hideSaveCoordinateButton() {
            if(orientationData['coordinates_submitted']) {
                $('#already-submitted').removeClass('d-none');
            }else {
                $('#already-submitted').addClass('d-none');
            }
        }
        hideSaveCoordinateButton();

        /*function switchCurrentOrientation() {
            currentOrientation = orientations[currentIndexInOrientation];
            $('#orientation').val(currentOrientation);
            addOrHideButtons();
            changeImgAttr(currentOrientation);
            hideSaveCoordinateButton();
        }*/
        //addOrHideButtons();
/*
        $('#previousOrientationButton').click(function() {
            let tempindexDec = currentIndexInOrientation - 1;
            currentIndexInOrientation = tempindexDec < 0 ? 0 : tempindexDec;
            console.log(currentIndexInOrientation);
            switchCurrentOrientation();
        });

        $('#nextOrientationButton').click(function() {
            console.log('current in', currentIndexInOrientation);
            console.log('orientation length', orientations.length);
            let tempindexInc = currentIndexInOrientation + 1;
            currentIndexInOrientation = tempindexInc >= orientations.length-1 ? orientations.length-1 : tempindexInc;
            console.log('current out', currentIndexInOrientation);
            switchCurrentOrientation();
        });

        $('#skipCurrentPageButton').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: `/projects/${projectId}/files/${fileId}/skipPage`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {
                    'pageNo': currentPage,
                    'orientation': $('#orientation').val(),
                },
                success: function (response) {
                    console.log('skip response', response);
                    if (response.success) {
                        //console.log( JSON.parse(response.payload));
                        orientationData[currentOrientation] = JSON.parse(response.payload)[currentOrientation];
                        currentPage = parseInt(orientationData[currentOrientation]['pages'].indexOf(orientationData[currentOrientation]['img_path']))+1;
                        isLastPage = (totalPages ===currentPage);
                        $('.modal').modal('hide');
                        $('#success-popup').modal('show');
                        setTimeout(function () {
                            $('#success-popup').modal('hide');
                            location.reload();
                        }, 1000);
                        let imgUrl = (storageUrl.value)+orientationData[currentOrientation]['img_path'];
                        //console.log('imgUrl', imgUrl);
                        $('#clickableImage').attr('src', imgUrl);
                        updateDimensions();
                        $('#liveToast').parent().css({ 'z-index': '99999' });
                        $('#liveToast').toast('show');
                        if(!isLastPage) {
                            $('#toast-icon').removeClass("fa-close");
                            $('#toast-icon').addClass("fa-check");
                        }
                        let toastMessage = (isLastPage ? "Reached last page, can't skip further !" : "Page skipped successfully !");
                        $('.message').html(toastMessage);
                    } else {
                        $('#liveToast').toast('show');
                        $('.message').html('Something went Wrong !');
                    }

                    //$('#nextOrientationButton').click();
                },
                error: function (error) {
                    document.getElementById('coord_form').reset();
                    console.error('AJAX Error:', error);
                    $('#liveToast').toast('show');
                    $('.message').html('Something went Wrong !');
                }
            });
        });
        */

        $('#finishCoordinates').click(function(e) {
            e.preventDefault();
            let fileId =  @json($ocrFile->id);
            $('.modal').modal('hide');
            $('.ocr-finish-modal-'+fileId).click();
            $('.dismissFooterButton').addClass('d-none');
            $('.goBackForCoordsButton').removeClass('d-none');
        });

        $('.goBackForCoordsButton').click(function(e) {
            e.preventDefault();
            let fileId =  @json($ocrFile->id);
            $('.ocr-pending-modal-'+fileId).click();
        });

        //document.getElementById('save-pending-ocr-request').disabled = true;
        //let image = document.getElementById('clickableImage');
        //const dimensionsDisplay = document.getElementById('dimensions');

        //let canvas = document.getElementById('overlayCanvas');
        const ctx = canvas.getContext('2d');
        console.log('image height width', image.width, image.height);
        canvas.width = image.width;
        canvas.height = image.height;

        let isDrawing = false;
        let startX = 0;
        let startY = 0;

        canvas.addEventListener('mousedown', function (e) {
            isDrawing = true;
            startX = e.offsetX;
            startY = e.offsetY;
        });

        canvas.addEventListener('mousemove', function (e) {
            if (isDrawing === true) {
                drawRect(startX, startY, e.offsetX, e.offsetY);
            }
        });

        canvas.addEventListener('mouseup', function (e) {
            if (isDrawing === true) {
                drawRect(startX, startY, e.offsetX, e.offsetY);
                isDrawing = false;

                const rect = image.getBoundingClientRect(); // Get the displayed bounds of the image
                const scaleX = image.naturalWidth / rect.width; // Determine the scale factor for width
                const scaleY = image.naturalHeight / rect.height; // Determine the scale factor for height

                const newStartX = Math.round(startX * scaleX);
                const newStartY = Math.round(startY * scaleY);
                const newEndX = Math.round(e.offsetX * scaleX);
                const newEndY = Math.round(e.offsetY * scaleY);

                areaCoordsDisplay.textContent = `Top-Left(${newStartX}, ${newStartY}), Bottom-Right(${newEndX}, ${newEndY})`;
                $('#coordinateX').val(newStartX);
                $('#coordinateY').val(newStartY);
                $('#boxlength').val(newEndY - newStartY);
                $('#boxbreadth').val(newEndX - newStartX);
            }
        });

        function drawRect(x1, y1, x2, y2) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
            ctx.rect(x1, y1, x2 - x1, y2 - y1);
            ctx.strokeStyle = 'red';
            ctx.stroke();
        }

        function updateDimensions() {
            console.log('update dimensions', image.offsetWidth, image.offsetHeight);
            const width = image.offsetWidth;
            const height = image.offsetHeight;
            dimensionsDisplay.textContent = `${width}px by ${height}px`;

            canvas.width = width;
            canvas.height = height;
        }

        // Update dimensions on page load
        updateDimensions();

        window.addEventListener('resize', updateDimensions);

        image.addEventListener('click', function (event) {
            const rect = image.getBoundingClientRect();
            const scaleX = image.naturalWidth / rect.width;
            const scaleY = image.naturalHeight / rect.height;

            // Calculate click coordinates relative to the natural size of the image
            const x = Math.round((event.clientX - rect.left) * scaleX);
            const y = Math.round((event.clientY - rect.top) * scaleY);

            coordsDisplay.textContent = `(${x}, ${y})`;

            console.log('sending data to parent');
            window.parent.postMessage(`(${x}, ${y})`, 'http://main.local.geekydev.com/');  // Replace 'http://parent-domain.com' with your parent domain
        });

        if(image.complete) {
            console.log('image render complete');
            updateDimensions();
        }

        let imgUrl = @json($ocrFilesPath);

        $.loadImage = function (opts) {
            var loadedImage = new Image();
            if (typeof opts !== "object" || opts === null){
                window.console && console.log("loadImage(): Please pass valid options");
                return;
            }
            typeof opts.beforeLoad === 'function' && opts.beforeLoad({imgUrl:opts.imgUrl, customData:opts.customData});
            $(loadedImage).one('load', function(){
                var oData = {success:true,url:opts.imgUrl,imageElem:loadedImage, customData:opts.customData};
                typeof opts.complete === 'function' && opts.complete(oData);
                typeof opts.success === 'function' && opts.success(oData);
            }).one('error', function(){
                var oData = {success:false,url:opts.imgUrl,imageElem:loadedImage, customData:opts.customData};
                typeof opts.complete === 'function' && opts.complete(oData);
                typeof opts.error === 'function' && opts.error(oData);
            }).attr('src', opts.imgUrl).each(function(){
                if(this.complete){  //cached image
                    $(this).trigger('load');
                }
            });
        };

        // loader and then show img

        let oOpts, jqImageContainer, fBeforeLoadingCallback, fCompleteCallback,
            fSuccessCallback, fErrorCallback;

        fBeforeLoadingCallback = function (oEvent) {
            $("<div class=\'mx-auto loaderIcon text-center'><div class='spinner-border'></div> Loading...</div>").appendTo(oEvent.customData.jqContainer);
            $("#coordinatesSaveButton").attr('disabled', true);
            $("#finishCoordinates").attr('disabled', true);
        };
        fCompleteCallback  = function (oEvent) {
            oEvent.customData.jqContainer.find(".spinner-border:first").remove();
        };
        fSuccessCallback  = function (oEvent) {
            oEvent.customData.jqContainer.html(oEvent.imageElem);
            //console.log('updated Dimensions');
            updateDimensions();
            image = document.getElementById('clickableImage').getElementsByTagName('img')[0];
            $("#coordinatesSaveButton").attr('disabled', false);
            // $("#finishCoordinates").attr('disabled', false);
            console.log('Image', image);
        };
        fErrorCallback = function (oEvent) {
            oEvent.customData.jqContainer.html("ERROR");
        };

        jqImageContainer = $("#clickableImage");

        oOpts = {
            imgUrl     : imgUrl,
            beforeLoad : fBeforeLoadingCallback,
            complete   : fCompleteCallback,
            success    : fSuccessCallback,
            error      : fErrorCallback,
            customData : {
                jqContainer : jqImageContainer
            }
        };
        $.loadImage(oOpts);
    });
</script>

</html>
