@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.vehicle_edit')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a
                            href="{!! route('vehicle-type') !!}">{{trans('lang.vehicle_type_table')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.vehicle_edit')}}</li>
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
                            <legend>{{trans('lang.vehicle_type')}}</legend>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.vehicle_name')}}<span
                                            class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="text" class="form-control vehicle_name">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.vehicle_name_help") }}
                                    </div>
                                </div>
                            </div>


                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="vehicle_active" id="active">
                                    <label class="col-3 control-label" for="active">{{trans('lang.enable')}}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{
                        trans('lang.save')}}
                    </button>
                    <a href="{!! route('vehicle-type') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                        trans('lang.cancel')}}</a>
                </div>

            </div>

        </div>
    </div>
</div>

</div>
</div>
@endsection

@section('scripts')


<script>

    var id = "{{$id}}";
    var database = firebase.firestore();
    var ref = database.collection('vehicle_type').where("id", "==", id);

    $(document).ready(function () {

        $('.vehicle_type_menu').addClass('active');

        jQuery("#overlay").show();
        ref.get().then(async function (snapshots) {
            var data = snapshots.docs[0].data();

            $(".vehicle_name").val(data.name);

            if (data.enable) {
                $('.vehicle_active').prop('checked', true);
            }

            jQuery("#overlay").hide();
        });
    });

    $(".edit-form-btn").click(function () {
        
        var name = $(".vehicle_name").val();
        var enable = false;

        if ($(".vehicle_active").is(':checked')) {
            enable = true;
        }

        if (name == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.vehicle_name_error')}}</p>");
            window.scrollTo(0, 0);
        } else {
            jQuery("#overlay").show();

            database.collection('vehicle_type').doc(id).update({
                'name': name,
                'enable': enable,
            }).then(function (result) {
                jQuery("#overlay").hide();

                window.location.href = '{{ route("vehicle-type")}}';
            }).catch(function (error) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + error + "</p>");
            });
        }
    });
</script>
@endsection
