@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{ trans('lang.moving_vehicle_details') }}</h3>

        </div>



        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                <li class="breadcrumb-item"><a href="{!! route('moving-vehicle') !!}">{{ trans('lang.moving_vehicles')

                        }}</a>

                </li>

                <li class="breadcrumb-item active">

                    {{ $id == '' ? trans('lang.moving_vehicle_add') : trans('lang.moving_vehicle_edit') }}</li>

            </ol>

        </div>

    </div>

    <div class="container-fluid">

        <div class="card pb-4">



            <div class="card-body">



                <div class="error_top"></div>



                <div class="row restaurant_payout_create">

                    <div class="restaurant_payout_create-inner">

                        <fieldset>

                            <legend>{{ trans('lang.moving_vehicle_details') }}</legend>



                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.name') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <input type="text" class="form-control name">

                                    <div class="form-text text-muted">

                                        {{ trans('lang.name_help') }}

                                    </div>

                                </div>

                            </div>



                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.kmCharge') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <input type="number" class="form-control kmCharge">

                                    <div class="form-text text-muted">

                                        {{ trans('lang.kmCharge_help') }}

                                    </div>

                                </div>

                            </div>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.length') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <input type="number" class="form-control length">

                                    <div class="form-text text-muted">

                                        {{ trans('lang.length_help') }}

                                    </div>

                                </div>

                            </div>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.width') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <input type="number" class="form-control width">

                                    <div class="form-text text-muted">

                                        {{ trans('lang.width_help') }}

                                    </div>

                                </div>

                            </div>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.height') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <input type="number" class="form-control height">

                                    <div class="form-text text-muted">

                                        {{ trans('lang.height_help') }}

                                    </div>

                                </div>

                            </div>

                            <!-- Champ pour costPerFloor -->
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.costPerFloor') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="number" class="form-control costPerFloor">
                                    <div class="form-text text-muted">{{ trans('lang.costPerFloor_help') }}</div>
                                </div>
                            </div>

                            <!-- Champ pour costPerCubeMeter -->
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.costPerCubeMeter') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="number" class="form-control costPerCubeMeter">
                                    <div class="form-text text-muted">{{ trans('lang.costPerCubeMeter_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">

                                <label class="col-3 control-label">{{ trans('lang.image') }}</label>

                                <div class="col-7">

                                    <input type="file" onChange="handleFileSelect(event)" class="form-control image"

                                        id="image">

                                    <div class="placeholder_img_thumb user_image"></div>

                                    <div id="uploding_image"></div>

                                    <div class="form-text text-muted w-50">

                                        {{ trans('lang.image_help') }}

                                    </div>

                                </div>

                            </div>



                            <div class="form-group row width-50">

                                <div class="form-check">

                                    <input type="checkbox" class="active" id="active">

                                    <label class="col-3 control-label" for="active">{{ trans('lang.enable') }}</label>

                                </div>

                            </div>



                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.description') }}<span

                                        class="required-field"></span></label>

                                <div class="col-7">

                                    <textarea rows="6" id="description" class="description form-control"></textarea>

                                    <div class="form-text text-muted">

                                        {{ trans('lang.description_help') }}

                                    </div>

                                </div>

                            </div>



                        </fieldset>

                    </div>

                </div>



                <div class="form-group col-12 text-center btm-btn">

                    <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i>

                        {{ trans('lang.save') }}</button>

                    <a href="{!! route('moving-vehicle') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{

                        trans('lang.cancel') }}</a>

                </div>



            </div>



        </div>

    </div>



</div>

@endsection



@section('scripts')

<script>

    var database = firebase.firestore();



    var requestId = "{{ $id }}";

    var id = (requestId == '') ? database.collection("tmp").doc().id : requestId;

    var storageRef = firebase.storage().ref('images');

    var storage = firebase.storage();

    var photo = "";

    var fileName = "";

    var ImageFile = '';

    var placeholderImage = "{{ asset('/images/default_user.png') }}";

    $(document).ready(function () {



        $('.moving_menu').addClass('active');

        $(document).ready(function () {



            $('.moving_sub_menu li').each(function () {

                var url = $(this).find('a').attr('href');

                if (url == document.referrer) {

                    $(this).find('a').addClass('active');

                    $('.moving_menu').addClass('active').attr('aria-expanded', true);

                }

                $('.moving_sub_menu').addClass('in').attr('aria-expanded', true);

            });

            if (requestId != '') {

                jQuery("#overlay").show();

                var ref = database.collection('moving_vehicle').where("id", "==", id);

                ref.get().then(async function (snapshots) {

                    var data = snapshots.docs[0].data();

                    console.log(data);

                    $(".name").val(data.name);

                    $(".kmCharge").val(data.kmCharge);

                    $(".length").val(data.length);

                    $('.width').val(data.width);

                    $('.height').val(data.height);

                    $('.costPerFloor').val(data.costPerFloor);

                    $('.costPerCubeMeter').val(data.costPerCubeMeter);

                    $('.description').val(data.description);

                    if (data.image != '' && data.image != null) {

                        photo = data.image;

                        ImageFile = data.image;

                        $(".user_image").append(

                            '<img class="rounded" style="width:50px" src="' + data

                                .image + '" alt="image">');

                    } else {

                        $(".user_image").append(

                            '<img class="rounded" style="width:50px" src="' +

                            placeholderImage + '" alt="image">');

                    }

                    if (data.enable) {

                        $('.active').prop('checked', true);

                    }

                    jQuery("#overlay").hide();

                })

            }

        });

    });



    $(".edit-form-btn").click(function () {



        var name = $(".name").val();

        var kmCharge = $(".kmCharge").val();

        var length = $(".length").val();

        var height = $(".height").val();

        var width = $(".width").val();

        var description = $(".description").val();

        var costPerFloor = $(".costPerFloor").val();

        var costPerCubeMeter = $(".costPerCubeMeter").val();

        var enable = false;

        if ($(".active").is(':checked')) {

            enable = true;

        }



        if (name == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.name_help') }}</p>");

            window.scrollTo(0, 0);

        }

        else if (kmCharge == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.kmCharge_help') }}</p>");

            window.scrollTo(0, 0);

        } else if (length == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.length_help') }}</p>");

            window.scrollTo(0, 0);

        } else if (height == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.height_help') }}</p>");

            window.scrollTo(0, 0);

        } else if (width == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.width_help') }}</p>");

            window.scrollTo(0, 0);

        } else if (costPerCubeMeter == '') {
            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.costPerCubeMeter_help') }}</p>");

            window.scrollTo(0, 0);
        } else if (costPerFloor == '') {
            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.costPerFloor_help') }}</p>");

            window.scrollTo(0, 0);
        } else if (description == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{ trans('lang.description_help') }}</p>");

            window.scrollTo(0, 0);

        } else {

            requestId == '' ?

                (storeImageData().then(IMG => {

                    database.collection('moving_vehicle').doc(id).set({

                        'id': id,

                        'name': name,

                        'length': length,

                        'height': height,

                        'width': width,

                        'kmCharge': kmCharge,

                        'costPerCubeMeter': costPerCubeMeter,

                        'costPerFloor': costPerFloor,

                        'enable': enable,

                        'description': description,

                        'image': IMG,

                    }).then(function (result) {

                        window.location.href = '{{ route('moving-vehicle') }}';

                    }).catch(function (error) {

                        $(".error_top").show();

                        $(".error_top").html("");

                        $(".error_top").append("<p>" + error + "</p>");

                    })

                }).catch(function (error) {

                    $(".error_top").show();

                    $(".error_top").html("");

                    $(".error_top").append("<p>" + error + "</p>");

                }))



                :

                (storeImageData().then(IMG => {



                    database.collection('moving_vehicle').doc(id).update({

                        'name': name,

                        'length': length,

                        'height': height,

                        'width': width,

                        'kmCharge': kmCharge,
                        
                        'costPerCubeMeter': costPerCubeMeter,

                        'costPerFloor': costPerFloor,

                        'enable': enable,

                        'description': description,

                        'image': IMG,

                    }).then(function (result) {

                        window.location.href = '{{ route('moving-vehicle') }}';

                    }).catch(function (error) {

                        $(".error_top").show();

                        $(".error_top").html("");

                        $(".error_top").append("<p>" + error + "</p>");

                    })

                }).catch(function (error) {

                    $(".error_top").show();

                    $(".error_top").html("");

                    $(".error_top").append("<p>" + error + "</p>");

                }))



        }

    })



    async function storeImageData() {

        var newPhoto = '';

        try {

            if (ImageFile != "" && photo != ImageFile) {

                var OldImageUrlRef = await storage.refFromURL(ImageFile);

                imageBucket = OldImageUrlRef.bucket;

                var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";



                if (imageBucket == envBucket) {



                    await OldImageUrlRef.delete().then(() => {

                        console.log("Old file deleted!")

                    }).catch((error) => {

                        console.log("ERR File delete ===", error);

                    });

                } else {

                    console.log('Bucket not matched');

                }



            }

            if (photo != ImageFile) {

                photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")

                var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {

                    contentType: 'image/jpg'

                });

                var downloadURL = await uploadTask.ref.getDownloadURL();

                newPhoto = downloadURL;

                photo = downloadURL;

            } else {

                newPhoto = photo;

            }

        } catch (error) {

            console.log("ERR ===", error);

        }

        return newPhoto;

    }



    function handleFileSelect(evt) {

        var f = evt.target.files[0];

        var reader = new FileReader();

        reader.onload = (function (theFile) {



            return function (e) {



                var filePayload = e.target.result;

                var val = f.name;

                var ext = val.split('.')[1];

                var docName = val.split('fakepath')[1];

                var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                var timestamp = Number(new Date());

                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                photo = filePayload;

                fileName = filename;

                $(".user_image").empty();

                $(".user_image").append(

                    '<span class="image-item" id="photo_user"><span class="remove-btn" data-id="user-remove" data-img="' +

                    photo +

                    '"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' +

                    photo + '" alt="image"></span>');



            };

        })(f);

        reader.readAsDataURL(f);

    }



    $(document).on("click", ".remove-btn", function () {

        $(".image-item").remove();

        $('#image').val('');

    });

</script>

@endsection

