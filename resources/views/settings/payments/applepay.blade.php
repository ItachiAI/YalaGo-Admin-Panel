@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            <ul class="nav nav-tabs card-header-tabs align-items-end">
                <li class="nav-item">
                    <a class="nav-link stripe_active_label" href="{!! url('settings/payments/stripe') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_stripe')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cod_active_label" href="{!! url('settings/payments/cod') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_cod_short')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active apple_pay_active_label"
                        href="{!! url('settings/payments/applepay') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_apple_pay')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link razorpay_active_label" href="{!! url('settings/payments/razorpay') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_razorpay')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link paypal_active_label" href="{!! url('settings/payments/paypal') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paypal')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link paytm_active_label" href="{!! url('settings/payments/paytm') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paytm')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link wallet_active_label" href="{!! url('settings/payments/wallet') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_wallet')}}<span
                            class="badge ml-2"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link payfast_active_label" href="{!! url('settings/payments/payfast') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.payfast')}}<span class="badge ml-2"></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link paystack_active_label" href="{!! url('settings/payments/paystack') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paystack_lable')}}<span
                            class="badge ml-2"></span></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link flutterWave_active_label" href="{!! url('settings/payments/flutterwave') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.flutterWave')}}<span
                            class="badge ml-2"></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mercadopago_active_label" href="{!! url('settings/payments/mercadopago') !!}"><i
                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.mercadopago')}}<span
                            class="badge ml-2"></span></a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                Processing...</div>
            <div class="row restaurant_payout_create">
                <div class="restaurant_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_setting_apple_pay')}}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_apple_pay" id="enable_apple_pay">
                            <label class="col-3 control-label"
                                for="enable_apple_pay">{{trans('lang.app_setting_enable_apple_pay')}}</label>
                            <div class="form-text text-muted">
                                {!! trans('lang.app_settings_enable_apple_pay_help') !!}
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_stripe_secret')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control stripe_secret">
                                <div class="form-text text-muted">
                                    {!! trans('lang.app_setting_stripe_secret_help') !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label
                                class="col-3 control-label">{{trans('lang.app_setting_apple_pay_merchant_id')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control apple_pay_merchant_id">
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i
                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    var database = firebase.firestore();
    var stripeData = database.collection('settings').doc('stripeSettings');
    var codData = database.collection('settings').doc('CODSettings');
    var ref = database.collection('settings').doc('applePay');
    var razorpayData = database.collection('settings').doc('razorpaySettings');
    var paypalData = database.collection('settings').doc('paypalSettings');
    var paytmData = database.collection('settings').doc('PaytmSettings');
    var walletData = database.collection('settings').doc('walletSettings');
    var payFastSettings = database.collection('settings').doc('payFastSettings');

    var payStackSettings = database.collection('settings').doc('payStack');
    var flutterWaveSettings = database.collection('settings').doc('flutterWave');
    var MercadopagoSettings = database.collection('settings').doc('MercadoPago');

    $(document).ready(function () {

        jQuery("#data-table_processing").show();

        $(".edit-form-btn").click(function () {

            var isApplePayEnabled = $(".enable_apple_pay").is(":checked");
            var merchantId = $(".apple_pay_merchant_id").val();
            var stripeSecret = $(".stripe_secret").val();
            database.collection('settings').doc("applePay").update({ 'isEnabled': isApplePayEnabled, 'merchantId': merchantId, 'stripeSecret': stripeSecret }).then(function (result) {
                window.location.href = '{{ url("settings/payments/applepay")}}';
            });
        })

    })

</script>

@endsection