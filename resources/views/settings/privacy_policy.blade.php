@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.privacy_policy')}}</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.privacy_policy')}}</li>
                </ol>
            </div>
        

        </div>
     <div class="container-fluid">
       <div class="card"> 
         <div class="card-body">

            <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>
            <div class="error_top"></div>

            <div class="terms-cond restaurant_payout_create row">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.privacy_policy')}}</legend>

                        <div class="form-group width-100">
                            <textarea class="form-control col-7" name="privacy_policy" id="privacy_policy"></textarea>
                        </div>


                    </fieldset>

                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn" >
            <button type="button" class="btn btn-primary  edit-form-btn" ><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
            <a href="{!! route('settings.privacyPolicy') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
        </div>
        </div>

        
       </div>
   </div>
    </div>

@endsection

@section('scripts')
    <script>

        var database = firebase.firestore();
        var photo ="";
        var ref = database.collection('settings').doc('global');
        $(document).ready(function () {
            try {
                jQuery("#overlay").show();
                ref.get().then(async function (snapshots) {
                    var globalSetting = snapshots.data();

                    if(globalSetting.privacyPolicy)
                    {
                        $('#privacy_policy').summernote("code", globalSetting.privacyPolicy);
                    }
                });
                jQuery("#overlay").hide();
            } catch (error) {
                jQuery("#overlay").hide();
            }
            $('#privacy_policy').summernote({
                height: 400,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['forecolor', ['forecolor']],
                    ['backcolor', ['backcolor']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ]
            });

            $(".edit-form-btn").click(function(){

                var privacy_policy =  $('#privacy_policy').summernote('code');

                if(privacy_policy == ''){
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.privacy_policy_error')}}</p>");
                    window.scrollTo(0, 0);
                }else{
                    database.collection('settings').doc('global').update({'privacyPolicy':privacy_policy}).then(function(result) {
                        window.location.href = '{{ route("settings.privacyPolicy")}}';
                    })
                }
            })
        });

    </script>
@endsection
