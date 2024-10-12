@extends('layouts.app')

@section('content')
@php
$type = 'all';
@endphp
<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">
                @if(request()->is('drivers/approved'))
                @php $type = 'approved'; @endphp
                {{trans('lang.approved_drivers')}}
                @elseif(request()->is('drivers/pending'))
                @php $type = 'pending'; @endphp
                {{trans('lang.approval_pending_drivers')}}
                @else
                {{trans('lang.all_drivers')}}
                @endif
            </h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.driver_table')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive m-t-10">

                            <table id="driverTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">

                                <thead>

                                    <tr>
                                        <?php if (in_array('driver.delete', json_decode(@session('user_permissions')))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                        class="do_not_delete" href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                            </th>
                                        <?php } ?>

                                        <th>{{trans('lang.extra_image')}}</th>

                                        <th>{{trans('lang.user_name')}}</th>
                                        <th>{{trans('lang.email')}}</th>
                                        <th>{{trans('lang.phone')}}</th>
                                        <th>{{trans('lang.document_plural')}}</th>
                                        <th>{{trans('lang.date')}}</th>
                                        <th>{{trans('lang.service')}}</th>
                                        <th>{{trans('lang.vehicle_type')}}</th>
                                        <th>{{trans('lang.dashboard_total_orders')}}</th>
                                        <th>{{trans('lang.actions')}}</th>

                                    </tr>

                                </thead>

                                <tbody id="append_list1">

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>
</div>


@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();

    var type = "{{$type}}";
    var ref = database.collection('driver_users').orderBy('createdAt', 'desc');
    if (type == 'pending') {
        ref = database.collection('driver_users').where("documentVerification", "==", false).orderBy('createdAt', 'desc');
    } else if (type == 'approved') {
        ref = database.collection('driver_users').where("documentVerification", "==", true).orderBy('createdAt', 'desc');
    }
    var placeholderImage = "{{ asset('/images/default_user.png') }}";
    var deleteMsg = "{{trans('lang.delete_alert')}}";
    var deleteSelectedRecordMsg = "{{trans('lang.selected_delete_alert')}}";

    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('driver.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    var append_list = '';

    $(document).ready(function () {
        jQuery("#overlay").show();

        const table = $('#driverTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['', '', 'fullName', 'email', 'phone', '', 'createdAt', 'serviceName', 'vehicleType', 'totalRide', ''] : ['', 'fullName', 'email', 'phone', '', 'createdAt', 'service', 'vehicleType', 'totalRide', '']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#overlay').show();
                }

                ref.get().then(async function (querySnapshot) {

                    if (querySnapshot.empty) {
                        console.error("No data found in Firestore.");
                        $('#overlay').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];
                    let serviceNames = {};


                    // Fetch driver names
                    const servicDocs = await database.collection('service').get();
                    servicDocs.forEach(doc => {
                        serviceNames[doc.id] = doc.data().title;
                    });

                    await Promise.all(querySnapshot.docs.map(async (doc) => {

                        childData = doc.data();

                        childData.id = doc.id; // Ensure the document ID is included in the data              
                        childData.serviceName = serviceNames[childData.serviceId] || '';
                        if (childData.hasOwnProperty('vehicleInformation') && childData.vehicleInformation.vehicleType) {
                            childData.vehicleType = childData.vehicleInformation.vehicleType;
                        }
                        childData.phone = '+' + (childData.countryCode.includes('+') ? childData.countryCode.slice(1) : childData.countryCode) + '-' + childData.phoneNumber;

                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt")) {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var createdAt = date + ' ' + time;

                            if (
                                (childData.fullName && childData.fullName.toLowerCase().toString().includes(searchValue)) ||
                                (childData.serviceName && childData.serviceName.toLowerCase().toString().includes(searchValue)) ||
                                (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                (childData.phone && childData.phone.toString().includes(searchValue)) ||
                                (childData.email && childData.email.toString().toLowerCase().includes(searchValue)) ||
                                (childData.vehicleType && childData.vehicleType.toString().toLowerCase().includes(searchValue))

                            ) {

                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'createdAt') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        }

                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });


                    const totalRecords = filteredRecords.length;

                    const paginatedRecords = filteredRecords.slice(start, start + length);

                    await Promise.all(paginatedRecords.map(async (childData) => {
                        if (childData.id) {
                            const totalOrderSnapShot = await database.collection('orders').where('driverId', '==', childData.id).get();
                            const rides = totalOrderSnapShot.size;

                            const totalIntercityOrderSnapShot = await database.collection('orders_intercity').where('driverId', '==', childData.id).get();
                            const intercity = totalIntercityOrderSnapShot.size;

                            childData.total_rides = rides + intercity;
                        } else {
                            childData.total_rides = 0;
                        }
                        var getData = await buildHTML(childData);
                        records.push(getData);
                    }));

                    $('#overlay').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, // Number of records after filtering (if any)
                        data: records // The actual data to display in the table
                    });
                }).catch(function (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#overlay').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [] // No data due to error
                    });
                });
            },
            order: (checkDeletePermission) ? [[6, 'desc']] : [[5, 'desc']],
            columnDefs: [
                {
                    targets: (checkDeletePermission) ? 6 : 5,
                    type: 'date',
                    render: function (data) {
                        return data;
                    }
                },
                { orderable: false, targets: (checkDeletePermission) ? [0, 1, 5, 9, 10] : [0, 4, 8, 9] },
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": "" // Remove default loader
            },

        });


        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#overlay').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#overlay').show();
                table.search('').draw();
            }
        }, 300));


    });

    async function buildHTML(val) {
        var html = [];

        newdate = '';
        var id = val.id;
        var route1 = '{{route("drivers.edit", ":id")}}';
        route1 = route1.replace(':id', id);

        var driverView = '{{route("drivers.view", ":id")}}';
        driverView = driverView.replace(':id', id);
        if (checkDeletePermission) {
            html.push('<input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label>');
        }
        if (val.profilePic == '' || val.profilePic == null) {
            html.push('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
        } else {
            html.push('<img class="rounded" style="width:50px" src="' + val.profilePic + '" alt="image">');
        }

        html.push('<a href="' + driverView + '">' + val.fullName + '</a>');
        html.push(shortEmail(val.email));
        if(val.countryCode.includes('+')){
          val.countryCode = val.countryCode.slice(1);
        }
        else
        {
            val.countryCode = val.countryCode;
        }
        html.push('+' + shortNumber(val.countryCode,val.phoneNumber));

        var driverDocView = '{{route("drivers.document", ":id")}}';
        driverDocView = driverDocView.replace(':id', id);

        html.push('<span class="action-btn"><a href="' + driverDocView + '"><i class="fa fa-file"></i></a>');


        if (val.hasOwnProperty("createdAt") && val.createdAt != null && val.createdAt != '') {
            var date = val.createdAt.toDate().toDateString();
            var time = val.createdAt.toDate().toLocaleTimeString('en-US');
            html.push('<span class="dt-time">' + date + ' ' + time + '</span>');
        } else {
            html.push('');
        }

        html.push(val.serviceName);

        var trroute1 = '';
        trroute1 = trroute1.replace(':id', 'driverId=' + id);

        if (val.hasOwnProperty('vehicleInformation') && val.vehicleInformation.vehicleType) {
            html.push(val.vehicleInformation.vehicleType);
        } else {
            html.push('');
        }

        html.push(val.total_rides);
        var actionHtml = '';
        actionHtml = actionHtml + '<span class="action-btn"><a href="' + driverView + '"><i class="fa fa-eye"></i></a><a href="' + route1 + '"><i class="fa fa-edit"></i></a>';

        if (checkDeletePermission) {
            actionHtml = actionHtml + '<a id="' + val.id + '" name="driver-delete" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        actionHtml += '</span>';
        html.push(actionHtml);
        return html;
    }


    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('users').doc(id).update({
                'isActive': true
            }).then(function (result) {
            });
        } else {
            database.collection('users').doc(id).update({
                'isActive': false
            }).then(function (result) {
            });
        }
    });

    $("#is_active").click(function () {
        $("#driverTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function () {
        if ($('#driverTable .is_open:checked').length) {
            jQuery("#overlay").show();

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {

                jQuery("#overlay").show();

                $('#driverTable .is_open:checked').each(function () {

                    var dataId = $(this).attr('dataId');

                    database.collection('driver_users').doc(dataId).delete().then(function () {
                        deleteUserData(dataId);
                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);

                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    async function deleteUserData(userId) {
        //delete user from authentication
        var dataObject = { "data": { "uid": userId } };
        var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
        jQuery.ajax({
            url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
            method: 'POST',
            crossDomain: true,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(dataObject),
            success: function (data) {
                console.log('Delete user success:', data.result);
            },
            error: function (xhr, status, error) {
                var responseText = JSON.parse(xhr.responseText);
                console.log('Delete user error:', responseText.error);
            }
        });
    }

    $(document).on("click", "a[name='driver-delete']", function (e) {

        if (confirm(deleteMsg)) {
            jQuery("#overlay").show();
            var id = this.id;
            jQuery("#overlay").show();
            database.collection('driver_users').doc(id).delete().then(function (result) {
                setTimeout(function () {
                    window.location.href = '{{ url()->current() }}';
                }, 7000);
            });
        } else {
            return false;
        }
    });


    async function deleteDriverData(driverId) {

        await database.collection('order_transactions').where('driverId', '==', driverId).get().then(async function (snapshotsOrderTransacation) {
            if (snapshotsOrderTransacation.docs.length > 0) {
                snapshotsOrderTransacation.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('order_transactions').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });

        await database.collection('driver_payouts').where('driverID', '==', driverId).get().then(async function (snapshotsItem) {

            if (snapshotsItem.docs.length > 0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('driver_payouts').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });

    }

</script>

@endsection